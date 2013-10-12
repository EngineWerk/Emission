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
});
