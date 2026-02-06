jQuery('#cx_log_list').on("click",'#cx_log_search_by_date',function()
{
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();

    compressx_get_logs_list(start_date,start_time,end_date,end_time);

});

jQuery('#cx_log_list').on("click",'.first-page',function() {
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();
    compressx_get_logs_list(start_date,start_time,end_date,end_time,'first');
});

jQuery('#cx_log_list').on("click",'.prev-page',function() {
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();
    var page=parseInt(jQuery(this).attr('value'));
    compressx_get_logs_list(start_date,start_time,end_date,end_time,page-1);
});

jQuery('#cx_log_list').on("click",'.next-page',function() {
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();
    var page=parseInt(jQuery(this).attr('value'));
    compressx_get_logs_list(start_date,start_time,end_date,end_time,page+1);
});

jQuery('#cx_log_list').on("click",'.last-page',function() {
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();
    compressx_get_logs_list(start_date,start_time,end_date,end_time,'last');
});

jQuery('#cx_log_list').on("keypress", '.current-page', function() {
    var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
    var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

    var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
    var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();
    if(event.keyCode === 13){
        var page = jQuery(this).val();
        compressx_get_logs_list(start_date,start_time,end_date,end_time,page);
    }
});

var currentLogFilename="";

function compressx_get_logs_list(start_date,start_time,end_date,end_time,page=0)
{
    var ajax_data = {
        'action':'compressx_get_logs_list',
        'start_date':start_date,
        'start_time':start_time,
        'end_date':end_date,
        'end_time':end_time,
        'page':page
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === "success")
            {
                jQuery('#cx_log_list').html(jsonarray.html);
                jQuery('#cx_log_list').find('#cx_log_start_date').val(start_date);
                jQuery('#cx_log_list').find('#cx_log_start_time').val(start_time);
                jQuery('#cx_log_list').find('#cx_log_end_date').val(end_date);
                jQuery('#cx_log_list').find('#cx_log_end_time').val(end_time);
            }
            else
            {
                alert(jsonarray.error);
            }
        }
        catch(err)
        {
            alert(err);
        }
    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message =compressx_output_ajaxerror('get logs list', textStatus, errorThrown);
        alert(error_message);
    });
}

jQuery('#cx_close_log').click(function()
{
    jQuery('#cx_log_detail_section').hide();
});

jQuery('#cx_log_list').on("click",'.cs-log-detail',function()
{
    var file_name = jQuery(this).closest('td').data('id');
    compressx_open_log(file_name);
});

function compressx_open_log(file_name)
{
    var ajax_data = {
        'action':'compressx_open_log',
        'filename':file_name
    };

    jQuery('#cx_log_name').html(file_name);
    jQuery('#cx_read_optimize_log_content').html("");
    jQuery('#cx_log_detail_section').show();
    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === "success")
            {
                currentLogFilename = file_name;
                jQuery('#cx_read_optimize_log_content').val(jsonarray.data);
                jQuery('#cx_log_created_date').text(jsonarray.create || '--');
                jQuery('#cx_log_detail_section').show();
                jQuery('html, body').animate({
                    scrollTop: jQuery('#cx_log_detail_section').offset().top - 100
                }, 500);

                //jQuery('#cx_read_optimize_log_content').html(jsonarray.html);
                //jQuery('html, body').animate({scrollTop: jQuery("#cx_log_scroll_test").offset().top}, 'slow');
            }
            else
            {
                jQuery('#cx_read_optimize_log_content').html(jsonarray.error);
            }
        }
        catch(err)
        {
            alert(err);
            var div = "Reading the log failed. Please try again.";
            jQuery('#cx_read_optimize_log_content').html(div);
        }
    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message =compressx_output_ajaxerror('open log', textStatus, errorThrown);
        alert(error_message);
    });
}

jQuery('#cx_close_log').on('click', function() {
    jQuery('#cx_log_detail_section').hide();
    currentLogFilename = '';
});

jQuery('#cx_download_current_log').on('click', function() {
    if (!currentLogFilename) {
        alert('No log file is currently open');
        return;
    }

    compressx_download_log(currentLogFilename)
});

function compressx_download_log(file_name)
{
    location.href =ajaxurl+'?_wpnonce='+compressx_ajax_object.ajax_nonce+'&action=compressx_download_log&log='+file_name;
}

jQuery('#cx_log_list').on("click",'.cs-log-download',function()
{
    var file_name = jQuery(this).closest('td').data('id');
    compressx_download_log(file_name);
});

jQuery('#cx_log_list').on("click",'.cs-log-delete',function()
{
    var file_name = jQuery(this).closest('td').data('id');

    var descript = 'Are you sure to delete this log file?';

    var ret = confirm(descript);
    if(ret === true)
    {
        compressx_delete_log(file_name);
    }

});

function compressx_delete_log(file_name)
{
    var ajax_data = {
        'action':'compressx_delete_log',
        'filename':file_name
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === "success")
            {
                var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
                var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

                var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
                var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();

                compressx_get_logs_list(start_date,start_time,end_date,end_time);
            }
            else
            {
                alert(jsonarray.error);
            }
        }
        catch(err)
        {
            alert(err);
        }
    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message =compressx_output_ajaxerror('delete log', textStatus, errorThrown);
        alert(error_message);
    });
}

jQuery('#cx_empty_log').click(function()
{
    var descript = 'Are you sure to delete All log file?';

    var ret = confirm(descript);
    if(ret === true)
    {
        compressx_delete_all_log();
    }
});

function compressx_delete_all_log()
{
    var ajax_data = {
        'action':'compressx_delete_all_log',
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === "success")
            {
                var start_date = jQuery('#cx_log_list').find('#cx_log_start_date').val();
                var start_time = jQuery('#cx_log_list').find('#cx_log_start_time').val();

                var end_date = jQuery('#cx_log_list').find('#cx_log_end_date').val();
                var end_time = jQuery('#cx_log_list').find('#cx_log_end_time').val();

                compressx_get_logs_list(start_date,start_time,end_date,end_time);
            }
            else
            {
                alert(jsonarray.error);
            }
        }
        catch(err)
        {
            alert(err);
        }
    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message =compressx_output_ajaxerror('delete log', textStatus, errorThrown);
        alert(error_message);
    });
}

jQuery(document).ready(function($) {
    $('#compressx_show_debug_form').on('click', function() {
        $('#compressx_debug_form_section').show();
        $('html, body').animate({
            scrollTop: $('#compressx_debug_form_section').offset().top - 100
        }, 500);
    });

    $('#compressx_debug_submit').on('click', function() {
        var user_mail = $('#compressx_user_mail').val().trim();
        var comment = $('#compressx_debug_comment').val().trim();

        if (!user_mail) {
            alert("Please enter your email address.");
            $('#compressx_user_mail').focus();
            return;
        }

        var $button = $(this);
        var originalText = $button.text();
        $button.text("Sending...").prop('disabled', true);

        var ajax_data = {
            action: 'compressx_send_debug_info',
            user_mail: user_mail,
            comment: comment,
        };

        compressx_post_request(ajax_data, function(data) {
            try {
                var response = jQuery.parseJSON(data);
                if (response && response.result === 'success') {
                    alert("Debug information sent successfully!");
                    $('#compressx_debug_form_section').hide();
                    $('#compressx_user_mail').val('');
                    $('#compressx_debug_comment').val('');
                } else {
                    alert(response.error || "Failed to send debug information.");
                }
            } catch(err) {
                alert("Failed to send debug information.");
            }
            $button.text(originalText).prop('disabled', false);
        }, function(XMLHttpRequest, textStatus, errorThrown) {
            alert("Failed to send debug information.");
            $button.text(originalText).prop('disabled', false);
        });
    });

    $('#compressx_download_debug_info').on('click', function()
    {
        cx_download_website_info();
    });

    function cx_download_website_info()
    {
        location.href =ajaxurl+'?_wpnonce='+compressx_ajax_object.ajax_nonce+'&action=compressx_create_debug_package';
    }
});