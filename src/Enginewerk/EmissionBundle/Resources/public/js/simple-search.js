$(function(){
    
    var listFiltered = false;
    var fileList = $('#filesTable tr');
    
    $(document).keyup(function(event) {
        
        if (event.keyCode === 27) { // escape
            if (listFiltered) {
                resetFilter();
            }
        }
    });
    
    $('#simpleSearchInput').change(function(){
        info('Simple search', 'onChange');
        filter();
    });
    $('#simpleSearchInput').keyup(function(){
        info('Simple search', 'KeyUp');
        filter();
    });
    
    filter = function(){
        var query = $('#simpleSearchInput').val();
        info('Simple search', query);
        
        fileList.each(function() {
            
            found = $(this).attr('data-search').toLowerCase().search(query);

            if (found >= 0) {
                info('found', $(this).attr('data-search'));
                info('query', query);
                $(this).css('display', 'table-row');
            } else {
                $(this).css('display', 'none');
            }
        });
        
        listFiltered = true;
    };
    
    resetFilter = function(){
        
        $('#simpleSearchInput').val('');
        
        fileList.each(function(){
            $(this).css('display', 'table-row');
        });
        
        listFiltered = false;
    };
    
    decorate = function(row, decorate) {
        log(decorate);
        if (decorate && row) {
            var text = row.children('td:first').html();
            info('text', text);
            row.children('td:first').html(text.replace(decorate, '<b>' + decorate + '</b>'));
        }
    };
});

var simpleSearchKeyDownControll = function(e) {
    if (e.keyCode === 114 || (e.ctrlKey && e.keyCode === 70)) { // ctrl + f
        $('#emissionSimpleSearchContainer').toggle();
        if ($('#emissionSimpleSearchContainer').attr('style') === 'display: block;') {
            $('#simpleSearchInput').focus();
        } else {
            $('#simpleSearchInput').blur();
        }
        e.preventDefault();
    }
};

function simpleSearchEnable() {
    window.addEventListener("keydown", simpleSearchKeyDownControll);
}

function simpleSearchDisable() {
    window.removeEventListener("keydown", simpleSearchKeyDownControll);
}


