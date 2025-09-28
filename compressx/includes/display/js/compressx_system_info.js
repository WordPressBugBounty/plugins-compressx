

jQuery('#compressx_download_debug_info').click(function()
{
    cx_download_website_info();
});

function cx_download_website_info()
{
    location.href =ajaxurl+'?_wpnonce='+compressx_ajax_object.ajax_nonce+'&action=compressx_create_debug_package';
}

jQuery('#compressx_debug_submit').click(function()
{
    cx_debug_submit();
});

function cx_debug_submit()
{
    var user_mail = jQuery('#compressx_user_mail').val();
    var comment = jQuery('#compressx_debug_comment').val();

    var ajax_data = {
        'action': 'compressx_send_debug_info',
        'user_mail': user_mail,
        'comment':comment
    };
    compressx_post_request(ajax_data, function (data) {
        try {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === "success")
            {
                jQuery('#compressx_send_success_text').removeClass("hidden");
                setTimeout(function ()
                {
                    jQuery('#compressx_send_success_text').addClass( 'hidden' );
                }, 3000);
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err) {
            alert(err);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown) {
        var error_message = compressx_output_ajaxerror('sending debug information', textStatus, errorThrown);
        alert(error_message);
    });
}