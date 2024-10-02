jQuery('#cx_edit_apiserver').click(function()
{
    jQuery('#cx_apiserver').hide();
    jQuery('#cx_apiserver_edit').show();
});

jQuery('#cx_apiserver_edit_btn').click(function()
{
    jQuery('#cx_apiserver_edit_progress').show();
    var api_server=jQuery('#cx_apiserver_select').val();
    var ajax_data = {
        'action': 'compressx_set_api_server',
        'api_server': api_server,
    };
    jQuery('#cx_apiserver_edit_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
    compressx_post_request(ajax_data, function (data)
    {
        jQuery('#cx_apiserver_edit_progress').hide();
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            jQuery('#cx_apiserver_edit_btn').css({'pointer-events': 'auto', 'opacity': '1'});
            if (jsonarray.result === 'success')
            {
                //
                jQuery('#cx_apiserver_text').html(jsonarray.html);
                jQuery('#cx_apiserver').show();
                jQuery('#cx_apiserver_edit').hide();
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
            jQuery('#cx_apiserver_edit_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#cx_apiserver_edit_progress').hide();
        jQuery('#cx_apiserver_edit_btn').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('set api server', textStatus, errorThrown);
        alert(error_message);
    });
});

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
                jQuery('#cx_read_optimize_log_content').html(jsonarray.html);
                jQuery('html, body').animate({scrollTop: jQuery("#cx_log_scroll_test").offset().top}, 'slow');
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

jQuery('#cx_log_list').on("click",'.cs-log-download',function()
{
    var file_name = jQuery(this).closest('td').data('id');
    compressx_download_log(file_name);
});

function compressx_download_log(file_name)
{
    location.href =ajaxurl+'?_wpnonce='+compressx_ajax_object.ajax_nonce+'&action=compressx_download_log&log='+file_name;
}

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
                jQuery('#cx_log_list').html(jsonarray.html);
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
                jQuery('#cx_log_list').html(jsonarray.html);
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