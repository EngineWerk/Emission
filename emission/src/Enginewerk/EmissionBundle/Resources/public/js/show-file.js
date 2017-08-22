$(document).ready( function(){

    $('a.show_file').live('click', function(event){
        getFileData($(this));
        openFilePreviewWindow();
        event.preventDefault();
    });
    
    $('.window button.close').live('click', function(event){
        closeFilePreviewWindow();
        event.preventDefault();
    });
    
    $('#showFileContainer a.preview_file').live('click', function(event){
        showFilepreviewFile($(this), $(this).parent());
        event.preventDefault();
    });

    var filePreviewWindowOpened = false;

    function openFilePreviewWindow() {

        console.info('Window height', $(window).height());
        console.info('Window width', $(window).width());
        
        $('#filePreviewWindow div.window').css('width', $(window).width() - 50 + 'px');
        
        $('#filePreviewWindow').attr('style', 'display:block');
        $('#filePreviewWindow').css('height', $(window).height()+ 'px');
        $('#filePreviewWindow').css('width', $(window).width()+ 'px');
        $('#filePreviewWindow div.container').css('height', ($(window).height() - 50) + 'px');
        
        filePreviewWindowOpened = true;
        $(document).keyup(closeOnEscapeKeyPress);
    }

    function closeFilePreviewWindow() {

        $('#filePreviewWindow').attr('style', 'display:none');
        filePreviewWindowOpened = false;
        $(document).unbind('keyup', closeOnEscapeKeyPress);
        insertHtmlIntoWindowContent('<div></div>');
    }
    
    function closeOnEscapeKeyPress(event) {
        if (event.keyCode === 27) { // escape
            if (filePreviewWindowOpened) {
                closeFilePreviewWindow();
            }
        }
    }

    function getFileData(clickedObj) {
        cursorBusy();

        $.ajax({
            url: clickedObj.attr('data-show-file-content-href')
            }).done(function ( json ) {

                var app = new AppResponse(json);

                if(app.status.isSuccess()) {
                    //callbackOnSuccess(imageId, context);                        
                    insertHtmlIntoWindowContent(app.data);
                } else {
                    if(app.status.isError() && app.message) {
                        insertHtmlIntoWindowContent(app.data);
                    } else {
                        log('Wystąpił nieobsługiwalny błąd.');                    
                        //callBackOnError('unknown error', context);
                    }
                }

                cursorNormal();
        });
    }

    function insertHtmlIntoWindowContent(html) {
        $('#filePreviewWindow div.content:first').html(html);
    }
});

function showFilepreviewFile(clickedObj, showContentBlock) {

    var url = clickedObj.attr('href');
    var showFileContentBlockHeight = showContentBlock.height();
    console.log(showFileContentBlockHeight);
    console.log(showContentBlock.parent().height());

    clickedObj.parent().append('<iframe id="iframePreview" src="' + url + '" style="width:100%;border:1px solid grey;margin-top:30px;"></iframe>');
   
    $('#iframePreview').height( showContentBlock.parent().height() - showFileContentBlockHeight - 68);
    
    setTimeout(function(){
        $('#iframePreview').contents().find('img').css('max-width', $('#iframePreview').width() + 'px');
    }, 500);
}