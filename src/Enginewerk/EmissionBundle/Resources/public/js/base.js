// Serialization utility
var serialize = function(obj, re) {
    var result = [];
    $.each(obj, function(i, val) {
        if ((re && re.test(i)) || !re)
            result.push(i + ': ' + (typeof val == 'object' ? val.join 
                ? '\'' + val.join(', ') + '\'' : serialize(val) : '\'' + val + '\''));
    });
    
    return '{' + result.join(', ') + '}';
};

function log(data) {
    if(console) {
        console.log(data);
    }
}

/**
 * Arguments: number to round, number of decimal places
 */
function roundNumber(rnum, rlength) { 
    if(rlength == undefined)
        rlength = 1;
    
    return Math.round(rnum * Math.pow(10,rlength)) / Math.pow(10,rlength);
}

function cursorBusy() {
    //$.prettyLoader.show();
    $('body, html').css('cursor','wait');
    //if(clickedObject != undefined)
    //    clickedObject.css('cursor','wait')
}

function cursorNormal() {
    //$.prettyLoader.hide();
    $('body, html').css('cursor','default');
    //if(clickedObject != undefined)
    //clickedObject.css('cursor','default')
}

$(document).ready( function(){

    //$.prettyLoader();
    
    $('.remove-file').live('click',function(event){
        
        var clickedObj = $(this);
        
        // Jeśli wciśnięty klawisz alt to nie wyświetlamy potwierdzenia usunięcia.
        if(event.altKey !== true) {
        
            var r = confirm('Usunąć "' + clickedObj.attr('data-filename') + '" ?')
            if (r !== true)
            {
                return false;
            }
        }
   
        cursorBusy();

        $.ajax({
            url: clickedObj.attr('href')
            }).done(function ( data ) {

                rsp = jQuery.parseJSON( data );

                if(rsp.status === 'Success') {
                    //callbackOnSuccess(imageId, context);                        
                    clickedObj.parent().parent().fadeOut(200, function(){
                        clickedObj.parent().parent().remove();
                    });
                } else {
                    if(rsp.status === 'Error') {
                       if(rsp.data.code === 23000) {
                           //callBackOnError(rsp.message, context);
                       } else {
                           log(rsp.message);
                           //callBackOnError(rsp.message, context);
                       }
                    } else {
                        log('Wystąpił nieobsługiwalny błąd.');                    
                        //callBackOnError('unknown error', context);
                    }
                }
                
                cursorNormal();
        });

        event.preventDefault();
    });
         
});    