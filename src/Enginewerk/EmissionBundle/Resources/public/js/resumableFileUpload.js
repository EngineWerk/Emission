$(function () {
    
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
    if (!r.support) {
        alert('File upload not supported');
    }
    
    function handleFileAdded (resumableFile, event) {

        info('File added to queue' , resumableFile.file.name);
        cursorBusy();

        var file = resumableFile.file;
        
        var tableRow = '<tr id="fhash-' + Math.random() + '" data-search="' + file.name + '" > \n' + 
                    '<td>' + 
                        '<div class="status"></div>' +
                        '<div class="fileName">' + file.name + '</div>' +
                        '<div class="fileUploadedBy">' + appUserName + '</div>' +
                        '<div class="fileSize">' + bytesToSize(file.size, 2) + '</div>' +
                    '</td>' +
                    '<td class="fileOptions">' + 
                    '</td> \n' + 
                '</tr>';
        
        var fileRow = $(tableRow);
        $('#filesTable tbody').prepend(fileRow);
        
        resumableFile.pause(); // Pasue particular file to compute hash

        var blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
        var chunkSize = maxChunkSize;
        var chunks = Math.ceil(file.size / chunkSize);
        var currentChunk = 1;
        var spark = new SparkMD5.ArrayBuffer();
        
        fileReaderOnload = function(e) {
            info('Loaded chunk for', file.name);
            
            spark.append(e.target.result);                 // append array buffer
            var md5 = SparkMD5.ArrayBuffer.hash(e.target.result);
            $('div.status:first', fileRow).html('Processed: ' + Math.round((currentChunk * 100 / chunks), 0) + '% &nbsp;');
            info('Chunk "' + currentChunk + '" MD5 hash for file ' + file.name + ':', md5);

            currentChunk++;
            if (currentChunk <= chunks) {
                loadNext();
            } else {
                resumableFile.uniqueIdentifier = spark.end();
                cursorNormal();

                var fileNameHash = resumableFile.uniqueIdentifier;
                info(file.name, fileNameHash);
                pendingFilesNumber++;

                resumableFile.pause(); // Resume upload of particular file
                r.upload();

                if($('#fhash-' + fileNameHash + '').length === 0 ) {
                    fileRow.attr('id', 'fhash-' + fileNameHash);
                } else {
                    fileRow.remove();
                }
            }
        },
        fileReaderOnerror = function (e) {
            info('MD5 computation error', e);
            cursorNormal();
        };

        function loadNext() {
            
            info('Processing chunk for ' + file.name + ':', currentChunk + ' out of ' + chunks);
            var fileReader = new FileReader();
            fileReader.onload = fileReaderOnload;
            fileReader.onerror = fileReaderOnerror;

            var start = (currentChunk - 1)* chunkSize,
                end = ((start + chunkSize) >= file.size) ? file.size : start + chunkSize;

            fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
            
        };

        $('div.status:first', fileRow).html('Processing... &nbsp;');
        loadNext();
   
    };
    
    function handleFileUploadSuccess (Resumable, jsonTextResponse) {

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
                    updateFileRowView(app.data, Resumable.uniqueIdentifier);
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
                    
        } else {
            alert('Unexpectred error occured');
        }

        cursorNormal();
    };
    
    function updateFileRowView (file, fileNameHash) {
        info(file.name, fileNameHash);
        var tableRow = 
            '<tr data-file-id="' + file.id + '" id="fhash-' + fileNameHash + '" data-search="' + file.name + '" > \n' + 
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
    }

    function handleFileUploadError (Resumable, jsonTextResponse) {
        var app = new AppResponse(null, jsonTextResponse);
        var fileDOMObject = $('#fhash-' + Resumable.uniqueIdentifier);

        fileDOMObject.fadeOut(200, function(){
            fileDOMObject.remove();
        });

        cursorNormal();

        alert('File: ' + Resumable.file.name + "\nMessage:\n\n" + app.message);
    }
    
    function updateProgressBar () {
        $('#dropbox_progress').find('.progress').width($('#dropbox_progress').find('.progressHolder').width() * r.progress());
    }
    
    r.assignBrowse(document.getElementById('browse'));
    r.assignDrop(document.getElementById('dropbox'));

    r.on('fileAdded', function (resumableFile, event) {
        handleFileAdded(resumableFile, event);
    });
    
    r.on('uploadStart', function () {
        cursorBusy();
    });

    r.on('fileSuccess', function (Resumable, jsonTextResponse) {
        handleFileUploadSuccess(Resumable, jsonTextResponse);
    });
    
    r.on('fileError', function (Resumable, jsonTextResponse) {
        handleFileUploadError(Resumable, jsonTextResponse);
    });

    r.on('progress', function () {
        updateProgressBar();
    });
});