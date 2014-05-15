$(function(){
    var r = new Resumable({
        target: url, 
        targetChunkTestUrl: urlChunkTest,
        chunkRetryInterval: 2,
        simultaneousUploads: 1,
        method : 'POST',
        chunkSize : maxChunkSize,
        forceChunkSize : true,
        testChunks : true,
        query: postdata,
        fileParameterName : 'form[uploadedFile]',
    });

    // Expose to window scope
    window.resumable = r;

    // Resumable.js isn't supported, fall back on a different method
    if(!r.support) {
        alert('File upload not supported');
    }

    r.assignBrowse(document.getElementById('browse'));
    r.assignDrop(document.getElementById('emissionApplicationContainer'));

    r.on('fileAdded', function(resumable, event) {
        cursorBusy();

        var file = resumable.file;
        resumable.pause(); // Pasue particular file to compute hash

        var blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice,
        chunkSize = maxChunkSize,                               // read in chunks of 2MB
        chunks = Math.ceil(file.size / chunkSize),
        currentChunk = 0,
        spark = new SparkMD5.ArrayBuffer(),
        frOnload = function(e) {
            log("read chunk");
            info("e.target.result", e.target.result);
            log(e.target.result);
            spark.append(e.target.result);                 // append array buffer
            log("read chunk-x");
            var md5 = SparkMD5.ArrayBuffer.hash(e.target.result);
            log("read chunk " + md5);
            currentChunk++;

            if (currentChunk < chunks) {
                loadNext();
            } else {
                resumable.uniqueIdentifier = spark.end();
                cursorNormal();

                var fileNameHash = resumable.uniqueIdentifier;
                info(file.name, fileNameHash);
                pendingFilesNumber++;

                resumable.pause(); // Resume upload of particular file
                r.upload();

                if($('#fhash-' + fileNameHash + '').length === 0 ) {
                    
                    var tableRow = '<tr id="fhash-' + fileNameHash + '" data-search="' + file.name + '" > \n' + 
                    '<td>' + 
                        '<div class="fileName">' + file.name + '</div>' +
                        '<div class="fileUploadedBy">' + file.uploaded_by + '</div>' +
                        '<div class="fileSize">' + bytesToSize(file.size, 2) + '</div>' +
                    '</td>' +
                    '<td class="fileOptions">' + 
                    '</td> \n' + 
                '</tr>';

                    $('#filesTable tbody').prepend(tableRow);
                }
            }
        },
        frOnerror = function (e) {
            info('MD5 computation error', e);
            cursorNormal();
        };

        function loadNext() {
            var fileReader = new FileReader();
            fileReader.onload = frOnload;
            fileReader.onerror = frOnerror;

            var start = currentChunk * chunkSize,
                end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;

            fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
        };

        loadNext();

    });

    r.on('uploadStart', function(){
        cursorBusy();
    });

    r.on('fileSuccess', function(Resumable, jsonTextResponse) {

        info('Total files: ', r.files.length);

        try {
            var app = new AppResponse(null, jsonTextResponse);
        } catch (error) {
            log('JSON parse error: ' + error);
            log(jsonTextResponse);
        }

        if(app) {
            if(app.status.isSuccess()) {
                if(app.data) {
                    var file = app.data;
                    var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.download_url);

                    var fileNameHash = Resumable.uniqueIdentifier;
                    info(file.name, fileNameHash);

                    var tableRow = '<tr data-file-id="' + file.id + '" id="fhash-' + fileNameHash + '" data-search="' + file.name + '" > \n' + 
                    '<td>' + 
                        '<div class="fileName">' + file.name + '</div>' +
                        '<div class="fileUploadedBy">' + file.uploaded_by + '</div>' +
                        '<div class="fileSize">' + bytesToSize(file.size, 2) + '</div>' +
                    '</td>' +
                    '<td class="fileOptions">' + 
                        '<a href="' + file.show_url + '" data-show-file-content-href="' + file.show_url.replace('/f/', '/fc/') + '" class="show_file">show</a> ' +
                        '<a href="' + file.download_url + '" class="fileOptionsDownloadLink">save</a> ' + 
                        '<a href="' + file.open_url + '" class="fileOptionsOpenLink">open</a> - ' +
                        '<a href="' + file.delete_url + '" class="remove-file">remove</a>' + 
                    '</td> \n' + 
                '</tr>';

                    $('#fhash-' + fileNameHash).replaceWith(tableRow);

                    pendingFilesNumber--;

                    if(pendingFilesNumber === 0) {

                        cursorNormal();
                        setTimeout(function() {

                            $('#dropbox_progress div.progressHolder').fadeTo(200, 0.01, function(){
                                setTimeout(function(){
                                    $('#dropbox_progress').find('.progress').width(0);
                                    setTimeout(function(){
                                        $('#dropbox_progress div.progressHolder').fadeTo(100, 1);
                                    }, 200);
                                }, 300);
                            });
                         }, 100);
                    }

                }

            } else {
                if(app.status.isError()) {
                    if(app.message) {
                        alert('Error occured: ' + app.message);
                    } else {
                        alert('Unexpectred error occured');
                    }
                } else if (app.message) { 
                    alert(app.message);
                } else {
                    alert('Unexpectred error occured');
                }
            }
        } else {
            alert('Unexpectred error occured');
        }

        cursorNormal();
    });

    r.on('fileError', function(Resumable, jsonTextResponse){
        log('fileError');

        var app = new AppResponse(null, jsonTextResponse);
        var fileDOMObject = $('#fhash-' + Resumable.uniqueIdentifier);

        fileDOMObject.fadeOut(200, function(){
            fileDOMObject.remove();
        });

        cursorNormal();
        //log(Resumable.file);
        alert('File: ' + Resumable.file.name + "\nMessage:\n\n" + app.message);
    });

    r.on('progress', function() {
        $('#dropbox_progress').find('.progress').width($('#dropbox_progress').find('.progressHolder').width() * r.progress());
    });

    var computeHashes = function (resumableFile, offset, fileReader) {
        var round = resumableFile.resumableObj.getOpt('forceChunkSize') ? Math.ceil : Math.floor,
          chunkSize = resumableFile.getOpt('chunkSize'),
          numChunks = Math.max(round(resumableFile.file.size / chunkSize), 1),
          forceChunkSize = resumableFile.getOpt('forceChunkSize'),
          startByte,
          endByte,
          func = (resumableFile.file.slice ? 'slice' : (resumableFile.file.mozSlice ? 'mozSlice' : (resumableFile.file.webkitSlice ? 'webkitSlice' : 'slice'))),
          bytes;

        resumableFile.hashes = resumableFile.hashes || [];
        fileReader = fileReader || new FileReader();
        offset = offset || 0;

        if (resumableFile.resumableObj.cancelled === false) {
          startByte = offset * chunkSize;
          endByte = Math.min(resumableFile.file.size, (offset + 1) * chunkSize);

          if (resumableFile.file.size - endByte < chunkSize && !forceChunkSize) {
            endByte = resumableFile.file.size;
          }
          bytes  = resumableFile.file[func](startByte, endByte);

          fileReader.onloadend = function (e) {
            var spark = SparkMD5.ArrayBuffer.hash(e.target.result);
            log(spark);
            resumableFile.hashes.push(spark);

            if (numChunks > offset + 1) {
              computeHashes(resumableFile, offset + 1, fileReader);
            }
          };

          fileReader.readAsArrayBuffer(bytes);
        }
      };
});