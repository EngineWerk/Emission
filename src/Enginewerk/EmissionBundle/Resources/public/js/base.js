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

 var bytesToSize = (function() {

    'use strict';

    var base = 1024,
        sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    return function(bytes, precision) {

      var i = parseInt(Math.floor(Math.log(bytes) / Math.log(base)), 10);

      return (bytes / Math.pow(base, i)).toFixed(precision || 0) + ' ' + sizes[i];
    };

  }());
  
$(document).ready( function(){

    //$.prettyLoader();
    
    $('.remove-file').live('click',function(event){
        
        var clickedObj = $(this);
        
        // Jeśli wciśnięty klawisz alt to nie wyświetlamy potwierdzenia usunięcia.
        if(event.altKey !== true && $.jStorage.get('app.settings.ask_before_delete', 'yes') ===  'yes' ) {
        
            var r = confirm('Usunąć "' + clickedObj.attr('data-filename') + '" ?')
            if (r !== true)
            {
                return false;
            }
        }
   
        cursorBusy();

        $.ajax({
            url: clickedObj.attr('href'),
            }).done(function ( rsp, textStatus  ) {

                if(rsp.status === 'Success') {
                    //callbackOnSuccess(imageId, context);                        
                    clickedObj.parent().parent().fadeOut(200, function(){
                        clickedObj.parent().parent().remove();
                    });
                } else {
                    if(rsp.status === 'Error' && rsp.message) {
                        alert(rsp.message);
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
