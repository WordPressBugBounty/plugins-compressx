
jQuery('#cx_enable_auto_optimize').click(function()
{
    var json = {};
    var value = '0';
    if(jQuery('#cx_enable_auto_optimize').prop('checked'))
    {
        value = '1';
    }
    else {
        value = '0';
    }
    json['auto_optimize']=value;
    var setting_data=JSON.stringify(json);

    var ajax_data = {
        'action': 'compressx_set_general_settings',
        'setting': setting_data,
    };
    compressx_post_request(ajax_data, function (data)
    {
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
});

jQuery('#cx_convert_to_webp').click(function()
{
    var value = '0';
    if(jQuery('#cx_convert_to_webp').prop('checked'))
    {
        value = '1';
        var descript = 'Are you sure to enable WebP as output format? This will convert your images to WebP format.';
    }
    else {
        value = '0';
        var descript = 'Are you sure to disable WebP as output format?';
    }

    var ret = confirm(descript);
    if(ret === true)
    {
        var json = {};


        json['convert_to_webp']=value;
        var setting_data=JSON.stringify(json);

        compressx_set_general_settings(setting_data);
    }
});

jQuery('#cx_convert_to_avif').click(function()
{
    var value = '0';
    if(jQuery('#cx_convert_to_avif').prop('checked'))
    {
        if(jQuery('#cx_converter_method_imagick').prop('checked'))
        {
            if(compressx_alert.imagick_avif)
            {
                var descript = 'We detect that you use ImageMagick 6.x, this version has a known bug that can cause AVIF conversion timeout. Enabling AVIF conversion with this version is not recommended. Are you sure you wish to proceed?';
            }
            else
            {
                var descript = 'Are you sure to enable AVIF as output format? This will convert your images to AVIF format.';
            }
        }
        else
        {
            var descript = 'Are you sure to enable AVIF as output format? This will convert your images to AVIF format.';
        }

        value = '1';
    }
    else {
        value = '0';
        var descript = 'Are you sure to disable AVIF as output format?';
    }

    var ret = confirm(descript);
    if(ret === true)
    {
        var json = {};


        json['convert_to_avif']=value;
        var setting_data=JSON.stringify(json);

        compressx_set_general_settings(setting_data);
    }
});

function compressx_set_general_settings(setting_data)
{
    var ajax_data = {
        'action': 'compressx_set_general_settings',
        'setting': setting_data,
    };
    compressx_post_request(ajax_data, function (data)
    {
        jQuery('#cx_save_convert_format').removeClass("hidden");
        setTimeout(function ()
        {
            jQuery('#cx_save_convert_format').addClass( 'hidden' );
        }, 3000);
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}

//cx_close_custom_compression
jQuery('#cx_close_custom_compression').click(function()
{
    jQuery('#cx_compressing_strategy_custom').hide();
});

jQuery('#cx_save_custom_quality').click(function()
{
    var json = {};
    json['quality']='custom';
    json['quality_webp']=jQuery('#cx_quality_webp').val();
    json['quality_avif']=jQuery('#cx_quality_avif').val();

    var setting_data=JSON.stringify(json);

    var ajax_data = {
        'action': 'compressx_set_general_settings',
        'setting': setting_data,
    };

    jQuery('#cx_save_custom_quality').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#cx_save_custom_quality_progress').show();
    compressx_post_request(ajax_data, function (data)
    {
        jQuery('#cx_save_custom_quality_progress').hide();
        jQuery('#cx_compressing_strategy_custom').hide();
        jQuery('#cx_save_custom_quality').css({'pointer-events': 'auto', 'opacity': '1'});

        jQuery('#cx_save_compression_level').removeClass("hidden");
        setTimeout(function ()
        {
            jQuery('#cx_save_compression_level').addClass( 'hidden' );
        }, 3000);

    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#cx_save_custom_quality_progress').hide();
        jQuery('#cx_save_custom_quality').css({'pointer-events': 'auto', 'opacity': '1'});
    });

});

jQuery('#cx_radioCustom').click(function()
{
    jQuery('#cx_compressing_strategy_custom').show();

    var json = {};
    json['quality']='custom';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);
});

jQuery('#cx_radioLossless').click(function()
{
    jQuery('#cx_compressing_strategy_custom').hide();
    var json = {};
    json['quality']='lossless';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);
});

jQuery('#cx_radioLossMinus').click(function()
{
    var json = {};
    json['quality']='lossy_minus';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);

    jQuery('#cx_compressing_strategy_custom').hide();
});

jQuery('#cx_radioLossy').click(function()
{
    var json = {};
    json['quality']='lossy';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);

    jQuery('#cx_compressing_strategy_custom').hide();
});

jQuery('#cx_radioLossyPlus').click(function()
{
    var json = {};
    json['quality']='lossy_plus';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);

    jQuery('#cx_compressing_strategy_custom').hide();
});

jQuery('#cx_radioLossySuper').click(function()
{
    var json = {};
    json['quality']='lossy_super';
    var setting_data=JSON.stringify(json);

    compressx_save_compression_settings(setting_data);

    jQuery('#cx_compressing_strategy_custom').hide();
});
//radioCustom
function compressx_save_compression_settings(setting_data)
{
    var ajax_data = {
        'action': 'compressx_set_general_settings',
        'setting': setting_data,
    };
    compressx_post_request(ajax_data, function (data)
    {
        jQuery('#cx_save_compression_level').removeClass("hidden");
        setTimeout(function ()
        {
            jQuery('#cx_save_compression_level').addClass( 'hidden' );
        }, 3000);

    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}


var cx_cancel_bulk_optimization=false;

jQuery('#compressx_close_progress').click(function()
{
    jQuery('#compressx_bulk_progress_2').hide();
});

jQuery('#compressx_start_bulk_optimization').click(function()
{
    compressx_start_bulk_optimization();
});

jQuery('#compressx_cancel_bulk_optimization').click(function()
{
    var descript = 'Are you sure to cancel this progressing?';

    var ret = confirm(descript);
    if(ret === true)
    {
        cx_cancel_bulk_optimization=true;
        jQuery('#compressx_cancel_bulk_optimization').css({'pointer-events': 'none', 'opacity': '0.4'});
    }
});

function compressx_bulk_optimization_canceled(message)
{
    jQuery('#compressx_bulk_progress_part1').hide();
    jQuery('#compressx_bulk_progress_part2').show();
    jQuery('#compressx_bulk_progress_2_notice').html(message);
    //
    jQuery('#compressx_cancel_bulk_optimization').hide();
    jQuery('#compressx_start_bulk_optimization').show();

    jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
    jQuery('#compressx_cancel_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});

    compressx_update_overview();
}

function compressx_init_progress()
{
    jQuery('#compressx_bulk_progress_2').show();
    jQuery('#compressx_bulk_progress_part1').show();
    jQuery('#compressx_bulk_progress_part2').hide();
    jQuery('#compressx_bulk_progress_2_bar').width("0%");
    jQuery('#compressx_bulk_progress_step1').removeClass("cx-completed");
    jQuery('#compressx_bulk_progress_step2').removeClass("cx-completed");
    jQuery('#compressx_bulk_progress_step3').removeClass("cx-completed");
    jQuery('#compressx_bulk_progress_step1').addClass("cx-active");
    jQuery('#compressx_bulk_progress_step3').removeClass("cx-active");
    jQuery('#compressx_bulk_progress_sub_text').html("");
    jQuery('#compressx_bulk_progress_2_text').html("Processing...");
}

function compressx_progress_finish(message)
{
    jQuery('#compressx_bulk_progress_part1').hide();
    jQuery('#compressx_bulk_progress_part2').show();
    jQuery('#compressx_bulk_progress_2_notice').html(message);
}

function compressx_show_review_box(jsonarray)
{
    if(jsonarray.show_review)
    {
        jQuery('#cx_rating_box').show();
        jQuery('#cx_size_of_opt_images').html(jsonarray.opt_size);
    }
}

function compressx_start_bulk_optimization()
{
    var force="0";

    if(jQuery('#cx_force_optimization').prop('checked'))
    {
        force = '1';
    }
    else {
        force = '0';
    }

    var ajax_data = {
        'action': 'compressx_start_scan_unoptimized_image',
        'force':force
    };

    cx_cancel_bulk_optimization=false;

    jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_cancel_bulk_optimization').show();
    jQuery('#compressx_start_bulk_optimization').hide();
    //
    compressx_init_progress();

    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_bulk_optimization)
        {
            compressx_bulk_optimization_canceled("The process has been canceled");
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            if(jsonarray.finished==true)
            {
                jQuery('#compressx_bulk_progress_step1').addClass("cx-completed");
                jQuery('#compressx_bulk_progress_step1').removeClass("cx-active");
                jQuery('#compressx_bulk_progress_2_text').html(jsonarray.progress);
                compressx_init_bulk_optimization_task();
            }
            else
            {
                jQuery('#compressx_bulk_progress_2_text').html(jsonarray.progress);
                compressx_scanning_images(jsonarray.offset);
            }
        }
        else
        {
            compressx_progress_finish(jsonarray.error);

            jQuery('#compressx_cancel_bulk_optimization').hide();
            jQuery('#compressx_start_bulk_optimization').show();

            jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_cancel_bulk_optimization').hide();
        jQuery('#compressx_start_bulk_optimization').show();
        jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('scanning images', textStatus, errorThrown);
        compressx_progress_finish(error_message);
    });
}

function compressx_scanning_images(offset)
{
    var force="0";

    if(jQuery('#cx_force_optimization').prop('checked'))
    {
        force = '1';
    }
    else {
        force = '0';
    }

    var ajax_data = {
        'action': 'compressx_start_scan_unoptimized_image',
        'force':force,
        'offset':offset
    };

    cx_cancel_bulk_optimization=false;

    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_bulk_optimization)
        {
            compressx_bulk_optimization_canceled("The process has been canceled");
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            if(jsonarray.finished==true)
            {
                jQuery('#compressx_bulk_progress_step1').addClass("cx-completed");
                jQuery('#compressx_bulk_progress_step1').removeClass("cx-active");
                jQuery('#compressx_bulk_progress_2_text').html(jsonarray.progress);
                compressx_init_bulk_optimization_task();
            }
            else
            {
                jQuery('#compressx_bulk_progress_2_text').html(jsonarray.progress);
                compressx_scanning_images(jsonarray.offset);
            }
        }
        else
        {
            compressx_progress_finish(jsonarray.error);

            jQuery('#compressx_cancel_bulk_optimization').hide();
            jQuery('#compressx_start_bulk_optimization').show();

            jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_cancel_bulk_optimization').hide();
        jQuery('#compressx_start_bulk_optimization').show();
        jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('scanning images', textStatus, errorThrown);
        compressx_progress_finish(error_message);
    });
}

function compressx_init_bulk_optimization_task()
{
    //
    var force="0";

    if(jQuery('#cx_force_optimization').prop('checked'))
    {
        force = '1';
    }
    else {
        force = '0';
    }
    var ajax_data = {
        'action': 'compressx_init_bulk_optimization_task',
        'force':force
    };
    jQuery('#compressx_bulk_progress_step2').addClass("cx-active");
    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_bulk_optimization)
        {
            compressx_bulk_optimization_canceled("The process has been canceled");
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            compressx_run_optimize();
        }
        else
        {
            jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_cancel_bulk_optimization').hide();
            jQuery('#compressx_start_bulk_optimization').show();

            compressx_progress_finish(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_cancel_bulk_optimization').hide();
        jQuery('#compressx_start_bulk_optimization').show();

        jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('start bulk optimization', textStatus, errorThrown);
        compressx_progress_finish(error_message);
    });
}

function compressx_run_optimize()
{
    var ajax_data = {
        'action': 'compressx_run_optimize',
    };

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            if (jsonarray.result === 'success')
            {
                setTimeout(function ()
                {
                    compressx_get_optimize_task_status();
                }, 1000);
            }
            else
            {
                compressx_progress_finish(jsonarray.error);

                jQuery('#compressx_cancel_bulk_optimization').hide();
                jQuery('#compressx_start_bulk_optimization').show();
                jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
                alert(jsonarray.error);
            }
        }
        catch(err)
        {
            setTimeout(function ()
            {
                compressx_get_optimize_task_status();
            }, 1000);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        compressx_get_optimize_task_status();
    });
}

function compressx_get_optimize_task_status()
{
    var ajax_data = {
        'action': 'compressx_get_opt_progress'
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === 'success')
            {
                jQuery('#compressx_bulk_progress_sub_text').html(jsonarray.sub_log);
                jQuery('#compressx_bulk_progress_2_text').html(jsonarray.log);
                jQuery('#compressx_bulk_progress_2_bar').width(jsonarray.percent+"%");
                //jQuery('#cx_overview').html(jsonarray.overview_html);
                if(jsonarray.continue)
                {
                    setTimeout(function ()
                    {
                        compressx_get_optimize_task_status();
                    }, 1000);
                }
                else if(jsonarray.finished)
                {
                    jQuery('#compressx_bulk_progress_step2').addClass("cx-completed");
                    jQuery('#compressx_bulk_progress_step3').addClass("cx-completed");
                    jQuery('#compressx_bulk_progress_step2').removeClass("cx-active");
                    jQuery('#compressx_bulk_progress_step3').addClass("cx-active");

                    jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#compressx_cancel_bulk_optimization').hide();
                    jQuery('#compressx_start_bulk_optimization').show();

                    compressx_progress_finish(jsonarray.message);
                    compressx_show_review_box(jsonarray);
                    compressx_update_overview();
                }
                else
                {
                    if(cx_cancel_bulk_optimization)
                    {
                        compressx_bulk_optimization_canceled(jsonarray.message);
                        return;
                    }
                    compressx_run_optimize();
                }
            }
            else if (jsonarray.result === 'failed')
            {
                if(jsonarray.timeout)
                {
                    if(cx_cancel_bulk_optimization)
                    {
                        compressx_bulk_optimization_canceled(jsonarray.message);
                        return;
                    }
                    compressx_run_optimize();
                }
                else
                {
                    jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
                    compressx_progress_finish(jsonarray.error);

                    jQuery('#compressx_cancel_bulk_optimization').hide();
                    jQuery('#compressx_start_bulk_optimization').show();
                }
            }
        }
        catch(err)
        {
            //jQuery('#compressx_start_bulk_optimization').css({'pointer-events': 'auto', 'opacity': '1'});
            //compressx_progress_finish(err);

            //jQuery('#compressx_cancel_bulk_optimization').hide();
            //jQuery('#compressx_start_bulk_optimization').show();
            setTimeout(function ()
            {
                compressx_get_optimize_task_status();
            }, 1000);
        }

    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        setTimeout(function ()
        {
            compressx_get_optimize_task_status();
        }, 1000);
    });
}

function compressx_update_overview()
{
    var ajax_data = {
        'action': 'compressx_start_stats',
    };

    compressx_post_request(ajax_data, function (response)
    {
        if (response.success)
        {
            if (response.data.status === 'cached')
            {
                const data = response.data;
                if (data.cached.space_saved_webp_percent !== undefined)
                {
                    jQuery('#cx_webp_saved').html(data.cached.space_saved_webp_percent+"%");
                    jQuery('#cx_avif_saved').html(data.cached.space_saved_avif_percent+"%");
                    jQuery('#cx_conversion_webp_percent').html(data.cached.conversion_webp_percent+"%<span class=\"cx-percent-sign\"> images</span>");
                    jQuery('#cx_conversion_avif_percent').html(data.cached.conversion_avif_percent+"%<span class=\"cx-percent-sign\"> images</span>");
                }
            }
            else {
                compressx_get_stats();
            }
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}

function compressx_start_statsloop()
{
    var ajax_data = {
        'action': 'compressx_continue_stats',
    };

    compressx_post_request(ajax_data, function (response)
    {
        if (response.success)
        {
            setTimeout(compressx_get_stats(), 2000);
        } else {
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}

function compressx_get_stats()
{
    var ajax_data = {
        'action': 'compressx_get_stats',
    };

    compressx_post_request(ajax_data, function (response)
    {
        if (!response.success || !response.data) return;

        const data = response.data;

        if (data.status === 'done') {
            jQuery('#cx_webp_saved').html(data.space_saved_webp_percent+"%");
            jQuery('#cx_avif_saved').html(data.space_saved_avif_percent+"%");

            jQuery('#cx_conversion_webp_percent').html(data.conversion_webp_percent+"%<span class=\"cx-percent-sign\"> images</span>");
            jQuery('#cx_conversion_avif_percent').html(data.conversion_avif_percent+"%<span class=\"cx-percent-sign\"> images</span>");
        } else if (data.status === 'in_progress') {
            compressx_start_statsloop();
        } else if (data.status === 'executing') {
            setTimeout(compressx_get_stats, 2000);
        } else if (data.status === 'not_started') {
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
}

jQuery('#cx_show_more_size').click(function()
{
    jQuery('.cx-thumbnail-size').show();
    jQuery('#cx_show_more_size').hide();
});

jQuery('#compressx_save_size').click(function()
{
    compressx_save_size_setting();
});

function compressx_save_size_setting()
{
    var json = {};

    var setting_data = compressx_ajax_data_transfer('size_setting');
    var json1 = JSON.parse(setting_data);

    jQuery.extend(json1, json);
    setting_data=JSON.stringify(json1);

    var ajax_data = {
        'action': 'compressx_save_size_setting',
        'setting': setting_data,
    };
    jQuery('#compressx_save_size').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_save_size_progress').show();
    //
    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            jQuery('#compressx_save_size').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_size_progress').hide();
            if (jsonarray.result === 'success')
            {
                jQuery('#compressx_save_size_text').removeClass("hidden");
                setTimeout(function ()
                {
                    jQuery('#compressx_save_size_text').addClass( 'hidden' );
                }, 3000);
            }
        }
        catch (err)
        {
            alert(err);
            jQuery('#compressx_save_size').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_size_progress').hide();
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_save_size').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#compressx_save_size_progress').hide();
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });
}


jQuery('#cx_delete_file').click(function()
{
    compressx_delete_files();
});

function compressx_delete_files()
{
    var confirm_text=jQuery('#cx_confirm_delete_file').val();
    if(confirm_text!="Delete")
    {
        alert('Please type the word "Delete" to confirm the deletion. Note that "Delete" is case sensitive.');
        return;
    }

    var ajax_data = {
        'action': 'compressx_delete_files',
    };
    jQuery('#cx_delete_file').css({'pointer-events': 'none', 'opacity': '0.4'});

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            jQuery('#cx_delete_file').css({'pointer-events': 'auto', 'opacity': '1'});
            if (jsonarray.result === 'success')
            {
                jQuery('#cx_delete_file_success').removeClass("hidden");
                setTimeout(function ()
                {
                    jQuery('#cx_delete_file_success').addClass( 'hidden' );
                }, 3000);
                compressx_update_overview();
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
            jQuery('#cx_delete_file').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#cx_delete_file').css({'pointer-events': 'auto', 'opacity': '1'});
    });
}


jQuery('#compressx_save_others').click(function()
{
    compressx_save_others();
});

function compressx_save_others()
{
    var json = {};

    var setting_data = compressx_ajax_data_transfer('others_setting');
    var json1 = JSON.parse(setting_data);

    jQuery.extend(json1, json);
    setting_data=JSON.stringify(json1);

    var ajax_data = {
        'action': 'compressx_save_others_setting',
        'setting': setting_data,
    };
    jQuery('#compressx_save_others').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_save_others_progress').show();

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            jQuery('#compressx_save_others').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_others_progress').hide();
            if (jsonarray.result === 'success')
            {
                jQuery('#compressx_save_others_text').removeClass("hidden");
                setTimeout(function ()
                {
                    jQuery('#compressx_save_others_text').addClass( 'hidden' );
                }, 3000);
            }

        }
        catch (err)
        {
            alert(err);
            jQuery('#compressx_save_others').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_others_progress').hide();
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_save_others').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#compressx_save_others_progress').hide();
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });
}

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

jQuery('#cx_hide_notice').click(function()
{
    jQuery('#cx_notice').hide();
    var ajax_data = {
        'action': 'compressx_hide_notice',
    };
    compressx_post_request(ajax_data, function (data)
    {
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
    });
});


function compressx_init_exclude_tree()
{
    jQuery('#compressx_exclude_js_tree').on('activate_node.jstree', function (event, data) {
    }).jstree({
        "core": {
            "check_callback": true,
            "multiple": true,
            "data": function (node_id, callback) {
                var tree_node = {
                    'node': node_id,
                    'path': compressx_uploads_root.path
                };
                var ajax_data = {
                    'action': 'compressx_get_custom_tree_dir',
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
    compressx_init_exclude_tree();
    compressx_update_overview();
});

jQuery('#compressx_add_exclude_folders').click(function()
{
    compressx_add_exclude_folders();
});

jQuery('#compressx_exclude_js_tree').on('click', '.cx-add-custom-exclude-tree', function()
{
    var id=jQuery(this).data("id");
    var node=jQuery(this).parent();

    var select_folders = jQuery('#compressx_exclude_dir_node').find('.cx-remove-custom-exclude-tree');
    var json = {};
    var bfind=false;

    jQuery.each(select_folders, function ()
    {
        var value=jQuery(this).data("id");
       if(value==id)
       {
           bfind=true;
       }
    });

    if(bfind)
        return;

    var ajax_data = {
        'action': 'compressx_add_exclude_folder',
        'id': id,
    };

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);

            if (jsonarray.result === 'success')
            {
                jQuery('#compressx_exclude_dir_node').find('ul:first').append(jsonarray.html);
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
            jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });

});

jQuery('#compressx_exclude_dir_node').on('click', '.cx-remove-custom-exclude-tree', function()
{
    var id=jQuery(this).data("id");
    var node=jQuery(this).parent();
    node.remove();
});

function compressx_add_exclude_folders()
{
    var select_folders = jQuery('#compressx_exclude_dir_node').find('.cx-remove-custom-exclude-tree');
    var json = {};
    jQuery.each(select_folders, function ()
    {
        var value=jQuery(this).data("id");
        //var value = select_item.data( 'id' );
        json[value]=value;
    });

    var exclude_node=JSON.stringify(json);
    var ajax_data = {
        'action': 'compressx_add_exclude_folders',
        'excludes': exclude_node,
    };
    jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_save_exclude_progress').show();

    compressx_post_request(ajax_data, function (data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            jQuery('#compressx_save_exclude_progress').hide();
            jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'auto', 'opacity': '1'});
            if (jsonarray.result === 'success')
            {
                jQuery('#compressx_save_exclude_text').removeClass("hidden");
                setTimeout(function ()
                {
                    jQuery('#compressx_save_exclude_text').addClass( 'hidden' );
                }, 3000);

                jQuery('#compressx_exclude_dir_node').html(jsonarray.html);
            }
            else {
                alert(jsonarray.error);
            }
        }
        catch (err)
        {
            alert(err);
            jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_exclude_progress').hide();
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        jQuery('#compressx_add_exclude_folders').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#compressx_add_exclude_folders').hide();
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });
}