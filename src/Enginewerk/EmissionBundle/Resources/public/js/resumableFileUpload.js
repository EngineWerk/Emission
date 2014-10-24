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
        fileParameterName : 'form[uploadedFile]'
    });

    // Expose to window scope
    window.resumable = r;

    // Resumable.js isn't supported, fall back on a different method
    if(!r.support) {
        alert('File upload not supported');
    }

    r.assignBrowse(document.getElementById('browse'));
    r.assignDrop(document.getElementById('dropbox'));

    r.on('fileAdded', function(resumableFile) {
        info('File added to queue' , resumableFile.file.name);
        cursorBusy();

        var file = resumableFile.file;

        var fhash = SparkMD5.hash(Math.random().toString());
        var tableRow = '<tr id="fhash-' + fhash + '" data-search="' + file.name + '" > \n' +
                    '<td>' +
                        '<div class="status"></div>' +
                        '<div class="fileName">' + file.name + '</div>' +
                        '<div class="fileUploadedBy">&nbsp;' + appUserName + '&nbsp;</div>' +
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
        
        var fileReaderOnload = function(e) {
            info('Loaded chunk for', file.name);
            
            spark.append(e.target.result);                 // append array buffer
            var md5 = SparkMD5.ArrayBuffer.hash(e.target.result);
            $('div.status:first', fileRow).html('Processed: ' + Math.round((currentChunk * 100 / chunks)) + '% &nbsp;');
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
        };

        var fileReaderOnerror = function (e) {
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
                        '<div class="fileUploadedBy">&nbsp;' + file.uploaded_by + '&nbsp;</div>' +
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

        for (var file in r.files) {
            console.info(r.files[file].fileName + ' : upload progress ', r.files[file].progress());
            updateFileProgress(r.files[file].uniqueIdentifier, r.files[file].progress());
        }
    });

    function updateFileProgress(fileId, progress) {
        $('div.status:first', $('#fhash-' + fileId)).html('Uploaded: ' + Math.round((progress * 100)) + '% &nbsp;');
    }

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