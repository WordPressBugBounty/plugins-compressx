
var cx_cancel_custom_bulk_optimization=false;

jQuery('#compressx_start_custom_bulk_optimization').click(function()
{
    compressx_start_custom_bulk_optimization();
});

jQuery('#compressx_cancel_custom_bulk_optimization').click(function()
{
    var descript = 'Are you sure to cancel this progressing?';

    var ret = confirm(descript);
    if(ret === true)
    {
        cx_cancel_custom_bulk_optimization=true;
        jQuery('#compressx_cancel_custom_bulk_optimization').css({'pointer-events': 'none', 'opacity': '0.4'});
    }
});

function compressx_custom_bulk_optimization_canceled()
{
    jQuery('#cx_custom_bulk_progress_text').hide();

    jQuery('#compressx_cancel_custom_bulk_optimization').hide();
    jQuery('#compressx_start_custom_bulk_optimization').show();

    jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
    jQuery('#compressx_cancel_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});

    compressx_get_dir_info();
}

function compressx_start_custom_bulk_optimization()
{
    var ajax_data = {
        'action': 'compressx_start_scan_custom_images',
    };
    jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#cx_custom_bulk_progress_text').show();
    jQuery('#cx_custom_bulk_progress_text').html("Processing...");
    jQuery('#cx_custom_overview').hide();

    jQuery('#compressx_cancel_custom_bulk_optimization').show();
    jQuery('#compressx_start_custom_bulk_optimization').hide();
    cx_cancel_custom_bulk_optimization=false;

    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_custom_bulk_optimization)
        {
            compressx_custom_bulk_optimization_canceled();
            alert("The process has been canceled");
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            if(jsonarray.finished==true)
            {
                jQuery('#cx_custom_bulk_progress_text').html(jsonarray.progress);
                compressx_init_custom_bulk_optimization_task();
            }
        }
        else
        {
            jQuery('#compressx_cancel_custom_bulk_optimization').hide();
            jQuery('#compressx_start_custom_bulk_optimization').show();

            jQuery('#cx_custom_bulk_progress_text').hide();
            jQuery('#cx_custom_overview').show();
            jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            alert(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_cancel_custom_bulk_optimization').hide();
        jQuery('#compressx_start_custom_bulk_optimization').show();

        jQuery('#cx_custom_bulk_progress_text').hide();
        jQuery('#cx_custom_overview').show();
        jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('scan images', textStatus, errorThrown);
        alert(error_message);
    });
}

function compressx_init_custom_bulk_optimization_task()
{
    var force="0";

    if(jQuery('#cx_custom_force_optimization').prop('checked'))
    {
        force = '1';
    }
    else {
        force = '0';
    }

    var ajax_data = {
        'action': 'compressx_init_custom_bulk_optimization_task',
        'force':force
    };
    jQuery('#compressx_bulk_progress_step2').addClass("cx-active");
    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_custom_bulk_optimization)
        {
            compressx_custom_bulk_optimization_canceled();
            alert("The process has been canceled");
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            compressx_run_custom_optimize();
        }
        else
        {
            jQuery('#compressx_cancel_custom_bulk_optimization').hide();
            jQuery('#compressx_start_custom_bulk_optimization').show();

            jQuery('#cx_custom_bulk_progress_text').hide();
            jQuery('#cx_custom_overview').show();
            jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            alert(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_cancel_custom_bulk_optimization').hide();
        jQuery('#compressx_start_custom_bulk_optimization').show();

        jQuery('#cx_custom_bulk_progress_text').hide();
        jQuery('#cx_custom_overview').show();
        jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('start bulk optimization', textStatus, errorThrown);
        alert(error_message);
    });
}

function compressx_run_custom_optimize()
{
    var ajax_data = {
        'action': 'compressx_run_custom_optimize',
    };

    compressx_post_request(ajax_data, function (data)
    {
        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            compressx_get_custom_optimize_task_status();
        }
        else
        {
            jQuery('#compressx_cancel_custom_bulk_optimization').hide();
            jQuery('#compressx_start_custom_bulk_optimization').show();

            jQuery('#cx_custom_bulk_progress_text').hide();
            jQuery('#cx_custom_overview').show();
            jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            alert(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        compressx_get_custom_optimize_task_status();
    });
}

function compressx_get_custom_optimize_task_status()
{
    var ajax_data = {
        'action': 'compressx_get_custom_opt_progress'
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === 'success')
            {
                jQuery('#cx_custom_bulk_progress_text').html(jsonarray.log);
                if(jsonarray.continue)
                {
                    setTimeout(function ()
                    {
                        compressx_get_custom_optimize_task_status();
                    }, 1000);
                }
                else if(jsonarray.finished)
                {
                    compressx_get_dir_info();
                    jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#cx_custom_bulk_progress_text').hide();
                    jQuery('#cx_custom_overview').show();

                    jQuery('#compressx_cancel_custom_bulk_optimization').hide();
                    jQuery('#compressx_start_custom_bulk_optimization').show();

                    compressx_custom_show_review_box(jsonarray);
                }
                else
                {
                    if(cx_cancel_custom_bulk_optimization)
                    {
                        compressx_custom_bulk_optimization_canceled();
                        alert("The process has been canceled");
                        return;
                    }
                    compressx_run_custom_optimize();
                }
            }
            else if (jsonarray.result === 'failed')
            {
                if(jsonarray.timeout)
                {
                    if(cx_cancel_custom_bulk_optimization)
                    {
                        compressx_custom_bulk_optimization_canceled();
                        alert("The process has been canceled");
                        return;
                    }
                    compressx_run_custom_optimize();
                }
                else
                {
                    alert(jsonarray.error);
                    jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#cx_custom_bulk_progress_text').hide();

                    jQuery('#compressx_cancel_custom_bulk_optimization').hide();
                    jQuery('#compressx_start_custom_bulk_optimization').show();

                    jQuery('#cx_custom_overview').show();
                }
            }
        }
        catch(err)
        {
            alert(err);
            jQuery('#compressx_start_custom_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#cx_custom_bulk_progress_text').hide();

            jQuery('#compressx_cancel_custom_bulk_optimization').hide();
            jQuery('#compressx_start_custom_bulk_optimization').show();

            jQuery('#cx_custom_overview').show();
        }

    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        setTimeout(function ()
        {
            compressx_get_custom_optimize_task_status();
        }, 1000);
    });
}

function compressx_custom_show_review_box(jsonarray)
{
    if(jsonarray.show_review)
    {
        jQuery('#cx_rating_box').show();
        jQuery('#cx_size_of_opt_images').html(jsonarray.opt_size);
    }
}

function compressx_get_dir_info()
{
    var ajax_data = {
        'action': 'compressx_get_dir_info',
    };
    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            if (jsonarray.result === 'success')
            {
                jQuery('#cx_custom_founds').html(jsonarray.found);
                jQuery('#cx_custom_total_saving').html(jsonarray.saved);
                jQuery('#cx_custom_processed').html(jsonarray.processed);
            }
        }
        catch (err)
        {
            //alert(err);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}

function compressx_init_custom_exclude_tree()
{
    jQuery('#compressx_custom_include_js_tree').on('activate_node.jstree', function (event, data) {
    }).jstree({
        "core": {
            "check_callback": true,
            "multiple": true,
            "data": function (node_id, callback) {
                var tree_node = {
                    'node': node_id,
                    'path': compressx_uploads_root.custom_path
                };
                var ajax_data = {
                    'action': 'compressx_get_custom_tree_dir_ex',
                    'tree_node': tree_node,
                };
                ajax_data.nonce=compressx_ajax_object.ajax_nonce;
                jQuery.ajax({
                    type: "post",
                    url: compressx_ajax_object.ajax_url,
                    data: ajax_data,
                    success: function (data) {
                        var jsonarray = jQuery.parseJSON(data);
                        callback.call(this, jsonarray.nodes);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        //alert("error");
                    },
                    timeout: 30000
                });
            },
            'themes': {
                'stripes': true
            }
        },
        "plugins": ["sort"],
        "sort": function(a, b) {
            a1 = this.get_node(a);
            b1 = this.get_node(b);
            if (a1.icon === b1.icon) {
                return (a1.text.toLowerCase() > b1.text.toLowerCase()) ? 1 : -1;
            } else {
                return (a1.icon > b1.icon) ? 1 : -1;
            }
        },
    });
}

jQuery(document).ready(function ()
{
    compressx_init_custom_exclude_tree();
});

jQuery('#compressx_custom_include_js_tree').on('click', '.cx-add-custom-include-tree', function()
{
    var id=jQuery(this).data("id");
    var ajax_data = {
        'action': 'compressx_add_include_folders',
        'id': id,
    };

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            if (jsonarray.result === 'success')
            {
                jQuery('#cx_custom_founds').html(jsonarray.found);
                jQuery('#cx_custom_total_saving').html(jsonarray.saved);
                jQuery('#cx_custom_processed').html(jsonarray.processed);
                jQuery('#compressx_include_dir_node').html(jsonarray.html);
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });

});

jQuery('#compressx_include_dir_node').on('click', '.cx-remove-custom-include-tree', function()
{
    var id=jQuery(this).data("id");
    var node=jQuery(this).parent();
    var ajax_data = {
        'action': 'compressx_remove_include_folders',
        'id': id,
    };
    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            if (jsonarray.result === 'success')
            {
                jQuery('#cx_custom_founds').html(jsonarray.found);
                jQuery('#cx_custom_total_saving').html(jsonarray.saved);
                jQuery('#cx_custom_processed').html(jsonarray.processed);
                node.remove();
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });

});