
function compressx_post_request(ajax_data, callback, error_callback, time_out)
{
    if(typeof time_out === 'undefined')    time_out = 30000;
    ajax_data.nonce=compressx_ajax_object.ajax_nonce;
    jQuery.ajax({
        type: "post",
        url: compressx_ajax_object.ajax_url,
        data: ajax_data,
        success: function (data) {
            callback(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error_callback(XMLHttpRequest, textStatus, errorThrown);
        },
        timeout: time_out
    });
}

function compressx_output_ajaxerror(action, textStatus, errorThrown){
    //action = 'trying to establish communication with your server';
    var error_msg = "compressx_request: "+ textStatus + "(" + errorThrown + "): an error occurred when " + action + ". " +
        "It could be because the request did not reach or the server did not respond. Please try again later.";
    return error_msg;
}

function compressx_ajax_data_transfer(data_type)
{
    var json = {};
    jQuery('input:checkbox[option='+data_type+']').each(function() {
        var value = '0';
        var key = jQuery(this).prop('name');
        if(jQuery(this).prop('checked')) {
            value = '1';
        }
        else {
            value = '0';
        }
        json[key]=value;
    });
    jQuery('input:radio[option='+data_type+']').each(function() {
        if(jQuery(this).prop('checked'))
        {
            var key = jQuery(this).prop('name');
            var value = jQuery(this).prop('value');
            json[key]=value;
        }
    });
    jQuery('input:text[option='+data_type+']').each(function(){
        var obj = {};
        var key = jQuery(this).prop('name');
        var value = jQuery(this).val();
        json[key]=value;
    });
    jQuery('textarea[option='+data_type+']').each(function(){
        var obj = {};
        var key = jQuery(this).prop('name');
        var value = jQuery(this).val();
        json[key]=value;
    });
    jQuery('input:password[option='+data_type+']').each(function(){
        var obj = {};
        var key = jQuery(this).prop('name');
        var value = jQuery(this).val();
        json[key]=value;
    });
    jQuery('select[option='+data_type+']').each(function(){
        var obj = {};
        var key = jQuery(this).prop('name');
        var value = jQuery(this).val();
        json[key]=value;
    });
    return JSON.stringify(json);
}