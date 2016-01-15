var deleteFunction = function(id){
    var input = "#" + id + " input";
     if ($("#" + id).css('backgroundColor') === 'rgb(255, 255, 255)'){
        $("#" + id).css({"backgroundColor": "#ff0000" });
        $(input).attr('checked', true);
        $(input).checked;
    } else {
        $("#" + id).css({"backgroundColor": "#fff" });
        $(input).attr('checked', false);
    }
};

function confirmMsg(delMsg){
    return confirm(delMsg);
}