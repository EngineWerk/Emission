// IMAGE PASTE
// We start by checking if the browser supports the 
// Clipboard object. If not, we need to create a 
// contenteditable element that catches all pasted data 

$(document).ready( function(){
    
    var pasteCatcher = document.createElement("div");

    // Firefox allows images to be pasted into contenteditable elements
    pasteCatcher.setAttribute("contenteditable", "");

    // We can hide the element and append it to the body,
    pasteCatcher.style.opacity = 0;
    pasteCatcher.style.position = 'fixed';
    document.body.insertBefore(pasteCatcher, document.body.firstChild);

    // Add the paste event listener
    window.addEventListener("paste", pasteHandler);
    
    function promptForNameAndUploadfile(file)
    {
        var d = new Date();
        var defaultFilename = 'Screenshot ' + d.toString();
        var userFilename = null;
        
        if($.jStorage.get('app.settings.prompt_for_screenshot_filename', 'yes') === 'yes') {
            var userFilename = prompt("Please enter file name", defaultFilename );
        } else {
            userFilename = defaultFilename;
        }
        
        if(userFilename === null) {
            return false;
        }
        
        if(userFilename !== '') {
            file.name = userFilename;
        } else {
            file.name = defaultFilename;
        }
        
        window.resumable.addFile(file);
    }

    /* Handle paste events */
    function pasteHandler(e) {
        
        // Focus on paste Catcher - helps in FF
        pasteCatcher.focus();
        
       if (e.clipboardData.items) {
          // Get the items from the clipboard
          var items = e.clipboardData.items;
          if (items) {
             // Loop through all items, looking for any kind of image
             for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf("image") !== -1) {
                   // We need to represent the image as a file,
                   promptForNameAndUploadfile(items[i].getAsFile());
                }
             }
          }
       // If we can't handle clipboard data directly (Firefox), 
       // we need to read what was pasted from the contenteditable element
       } else {
          // This is a cheap trick to make sure we read the data
          // AFTER it has been inserted.
          setTimeout(checkInput, 1);
       }
    }

    /* Parse the input in the paste catcher element */
    function checkInput() {
       // Store the pasted content in a variable
       var child = pasteCatcher.childNodes[0];

       // Clear the inner html to make sure we're always
       // getting the latest inserted content
       pasteCatcher.innerHTML = "";

       if (child) {
          // If the user pastes an image, the src attribute
          // will represent the image as a base64 encoded string.
          if (child.tagName === "IMG") {
             createImage(child.src);
          }
       }
    }


    /* Creates a new image from a given source */
    function createImage(source) {
       var pastedImage = new Image();
       pastedImage.onload = function() {

            // atob to base64_decode the data-URI
            var image_data = atob(pastedImage.src.split(',')[1]);
            // Use typed arrays to convert the binary data to a Blob
            var arraybuffer = new ArrayBuffer(image_data.length);
            var view = new Uint8Array(arraybuffer);
            for (var i=0; i<image_data.length; i++) {
                view[i] = image_data.charCodeAt(i) & 0xff;
            }

            try {
                // This is the recommended method:
                var blob = new Blob([arraybuffer], {type: 'application/octet-stream'});
            } catch (e) {
                // The BlobBuilder API has been deprecated in favour of Blob, but older
                // browsers don't know about the Blob constructor
                // IE10 also supports BlobBuilder, but since the `Blob` constructor
                //  also works, there's no need to add `MSBlobBuilder`.
                var bb = new (window.WebKitBlobBuilder || window.MozBlobBuilder);
                bb.append(arraybuffer);
                var blob = bb.getBlob('application/octet-stream'); // <-- Here's the Blob    
            }

            promptForNameAndUploadfile(blob);
       };

       pastedImage.src = source;
    }

});