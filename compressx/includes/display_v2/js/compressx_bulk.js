var cx_cancel_bulk_optimization=false;
var cx_pause_bulk_optimization=false;
var cx_bulk_task_running = false;

jQuery('#cx-v2-start-now').on('click', function ()
{
    compressx_start_bulk_optimization();
});

jQuery('#cx-v2-cancel-bulk').on('click', function ()
{
    if(cx_bulk_task_running)
    {
        var descript = 'Are you sure to cancel this progressing?';

        var ret = confirm(descript);
        if(ret === true)
        {
            cx_cancel_bulk_optimization=true;
            jQuery('#cx-v2-cancel-bulk').css({'pointer-events': 'none', 'opacity': '0.4'});
        }
    }
    else
    {
        jQuery('#cx-v2-bulk-ready').show();
        jQuery('#cx-v2-bulk-progress').hide();
    }

});

jQuery('#cx-v2-pause-bulk').on('click', function ()
{
    var descript = 'Are you sure to pause this progressing?';

    var ret = confirm(descript);
    if(ret === true)
    {
        cx_pause_bulk_optimization=true;
        jQuery('#cx-v2-pause-bulk').css({'pointer-events': 'none', 'opacity': '0.4'});
    }
});

//
jQuery('#cx_bulk_success_hide').on('click', function ()
{
    jQuery('#cx_bulk_success').hide();
    jQuery('#cx-v2-bulk-ready').show();
    jQuery('#cx-v2-bulk-progress').hide();
});

jQuery('#cx_bulk_error_hide').on('click', function ()
{
    jQuery('#cx_bulk_error').hide();
    jQuery('#cx-v2-bulk-ready').show();
    jQuery('#cx-v2-bulk-progress').hide();
});

jQuery('#cx-v2-log-copy').on('click', function() {
    var logContent = jQuery('#cx-v2-live-log').text();
    if (logContent)
    {
        // Check if Clipboard API is available
        if (navigator.clipboard && navigator.clipboard.writeText)
        {
            navigator.clipboard.writeText(logContent).then(function()
            {
                alert('Log copied to clipboard!');
            }).catch(function() {
                // Fallback if clipboard API fails
            });
        }
        else
        {
            // Use fallback for non-HTTPS or unsupported browsers
        }
    }
});

jQuery('#cx-v2-log-download').on('click', function()
{
    location.href =ajaxurl+'?_wpnonce='+compressx_ajax_object.ajax_nonce+'&action=compressx_download_task_log';
});

var currentLogFilename='';

jQuery('.cx-log-details').on('click', function(e)
{
    e.preventDefault();

    var logFilename = jQuery(this).data('log');

    compressx_open_log_details(logFilename);
});

function compressx_open_log_details(logFilename)
{
    var ajax_data = {
        action: 'compressx_open_log',
        filename: logFilename
    };

    compressx_post_request(ajax_data, function(response)
    {
        try {
            response = JSON.parse(response);
            if (response.result === 'success')
            {
                currentLogFilename = logFilename;
                jQuery('#cx-v2-log-content').val(response.data);
                jQuery('#cx-v2-log-created-date').text(response.create || '--');
                jQuery('#cx-v2-log-modal').show();
                jQuery('html, body').animate({
                    scrollTop: jQuery('#cx-v2-log-modal').offset().top - 100
                }, 500);
            } else {
                alert('Failed to open log: ' + (response.error || 'Unknown error'));
            }
        } catch(err) {
            alert('An error occurred while opening log.');
        }
    }, function(XMLHttpRequest, textStatus, errorThrown) {
        var error_message = compressx_output_ajaxerror('opening log', textStatus, errorThrown);
        alert(error_message);
    });
}

jQuery('.cx-log-download').on('click', function(e)
{
    e.preventDefault();

    var logFilename = jQuery(this).data('log');

    var download_url = ajaxurl +
        '?action=compressx_download_log&log=' +
        logFilename +
        '&nonce=' + compressx_ajax_object.ajax_nonce;

    window.location.href = download_url;
});

jQuery('.cx-log-delete').on('click', function(e)
{
    e.preventDefault();

    var logFilename = jQuery(this).data('log');
    var $element= jQuery(this);
    if (!confirm('Are you sure you want to delete this log file?'))
    {
        return;
    }

    var ajax_data = {
        action: 'compressx_delete_log',
        filename: logFilename
    };

    compressx_post_request(ajax_data, function(response) {
        try {
            response = JSON.parse(response);
            if (response.result === 'success') {
                $element.closest('tr').fadeOut(function() {
                    jQuery(this).remove();
                });
            } else {
                alert('Failed to delete log: ' + (response.error || 'Unknown error'));
            }
        } catch(err) {
            alert('An error occurred while deleting log.');
        }
    }, function(XMLHttpRequest, textStatus, errorThrown) {
        var error_message = compressx_output_ajaxerror('deleting log', textStatus, errorThrown);
        alert(error_message);
    });
});

jQuery('#cx-v2-download-current-log').on('click', function()
{
    //currentLogFilename
    if (currentLogFilename === "")
    {
        return;
    }

    var download_url = ajaxurl +
        '?action=compressx_download_log&log=' +
        currentLogFilename +
        '&nonce=' + compressx_ajax_object.ajax_nonce;

    window.location.href = download_url;}
);

jQuery('#cx-v2-close-log').on('click', function(e)
{
    jQuery('#cx-v2-log-modal').hide();
    currentLogFilename = '';
});

function compressx_start_bulk_optimization()
{
    var force="0";

    if(jQuery('#cx-v2-force-reprocess').prop('checked'))
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
    jQuery('#cx-v2-cancel-bulk').css({'pointer-events': 'auto', 'opacity': '1'});
    cx_pause_bulk_optimization=false;
    jQuery('#cx-v2-pause-bulk').css({'pointer-events': 'auto', 'opacity': '1'});
    jQuery('#cx-v2-bulk-ready').hide();
    jQuery('#cx-v2-bulk-progress').show();
    compressx_reset_bulk_progress();
    cx_bulk_task_running=true;

    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_bulk_optimization)
        {
            compressx_bulk_optimization_canceled();
            return;
        }

        if(cx_pause_bulk_optimization)
        {
            compressx_bulk_optimization_paused();
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            if(jsonarray.finished==true)
            {
                compressx_update_bulk_scan_progress(jsonarray.progress_text,jsonarray.progress_percent);
                compressx_init_bulk_optimization_task();
            }
            else
            {
                compressx_update_bulk_scan_progress(jsonarray.progress_text,jsonarray.progress_percent);
                compressx_scanning_images(jsonarray.offset);
            }
        }
        else
        {
            compressx_progress_error(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message = compressx_output_ajaxerror('scanning images', textStatus, errorThrown);
        compressx_progress_error(error_message);
    });
}

function compressx_scanning_images(offset)
{
    var force="0";

    if(jQuery('#cx-v2-force-reprocess').prop('checked'))
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
            compressx_bulk_optimization_canceled();
            return;
        }

        if(cx_pause_bulk_optimization)
        {
            compressx_bulk_optimization_paused();
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            if(jsonarray.finished==true)
            {
                compressx_update_bulk_scan_progress(jsonarray.progress_text,jsonarray.progress_percent);
                compressx_init_bulk_optimization_task();
            }
            else
            {
                compressx_update_bulk_scan_progress(jsonarray.progress_text,jsonarray.progress_percent);
                compressx_scanning_images(jsonarray.offset);
            }
        }
        else
        {
            compressx_progress_error(jsonarray.error);
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message = compressx_output_ajaxerror('scanning images', textStatus, errorThrown);
        compressx_progress_error(error_message);
    });
}


function compressx_init_bulk_optimization_task()
{
    var force="0";

    if(jQuery('#cx-v2-force-reprocess').prop('checked'))
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

    compressx_post_request(ajax_data, function (data)
    {
        if(cx_cancel_bulk_optimization)
        {
            compressx_bulk_optimization_canceled();
            return;
        }

        if(cx_pause_bulk_optimization)
        {
            compressx_bulk_optimization_paused();
            return;
        }

        var jsonarray = jQuery.parseJSON(data);

        if (jsonarray.result === 'success')
        {
            compressx_get_task_log();
            compressx_run_optimize();
        }
        else
        {
            if(jsonarray.no_unoptimized_images)
            {
                compressx_progress_finish(jsonarray.error,"Optimization Status:");
            }
            else
            {
                compressx_progress_error(jsonarray.error);
            }
        }
    }, function (XMLHttpRequest, textStatus, errorThrown)
    {
        var error_message = compressx_output_ajaxerror('start bulk optimization', textStatus, errorThrown);
        compressx_progress_error(error_message);
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
                compressx_progress_error(jsonarray.error);
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
                //progress_text,progress_percent,sub_progress_text,sub_progress_percent,optimized,errors,remaining

                compressx_update_bulk_progress(jsonarray.progress_text,jsonarray.progress_percent,jsonarray.sub_progress_text,jsonarray.sub_progress_percent,jsonarray.optimized,jsonarray.errors,jsonarray.remaining);
                if(jsonarray.continue)
                {
                    setTimeout(function ()
                    {
                        compressx_get_optimize_task_status();
                    }, 1000);
                }
                else if(jsonarray.finished)
                {
                    jQuery(document).trigger('compressx:hasUpdateImageData');
                    compressx_progress_finish(jsonarray.message);
                    compressx_show_review_box(jsonarray);
                }
                else
                {
                    if(cx_cancel_bulk_optimization)
                    {
                        compressx_bulk_optimization_canceled(jsonarray.message);
                        return;
                    }

                    if(cx_pause_bulk_optimization)
                    {
                        compressx_bulk_optimization_paused();
                        return;
                    }

                    jQuery(document).trigger('compressx:hasUpdateImageData');
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

                    if(cx_pause_bulk_optimization)
                    {
                        compressx_bulk_optimization_paused();
                        return;
                    }

                    compressx_run_optimize();
                }
                else
                {
                    compressx_progress_error(jsonarray.error);
                }
            }
        }
        catch(err)
        {
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

function compressx_get_task_log(offset)
{
    if(!cx_bulk_task_running)
        return;

    var ajax_data = {
        'action': 'compressx_get_task_log',
        'offset':offset
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = jQuery.parseJSON(data);
            if (jsonarray.result === 'success')
            {
                var $logContainer = jQuery('#cx-v2-live-log');

                $logContainer.append(jsonarray.content);

                if (jQuery('#cx-v2-log-autoscroll').is(':checked'))
                {
                    $logContainer.scrollTop($logContainer[0].scrollHeight);
                }

                compressx_limit_log_lines($logContainer, 5000);

                setTimeout(function ()
                {
                    compressx_get_task_log(jsonarray.offset);
                }, 3000);
            }
            else
            {
                //
            }
        }
        catch(err)
        {
            setTimeout(function ()
            {
                compressx_get_task_log(0);
            }, 3000);
        }

    }, function(XMLHttpRequest, textStatus, errorThrown)
    {
        setTimeout(function ()
        {
            compressx_get_task_log(0);
        }, 3000);
    });
}

function compressx_limit_log_lines($container,maxLines)
{
    var lines = $container.text().split('\n');

    if (lines.length > maxLines)
    {
        var newLines = lines.slice(lines.length - maxLines);
        $container.text(newLines.join('\n'));
    }
}

function compressx_reset_bulk_progress()
{
    jQuery('#cx-v2-progress-status').html("Ready");

    jQuery('#cx-v2-scan-status').html("0 images scanned");
    jQuery('#cx-v2-scan-progress-bar').width("0%");

    jQuery('#cx-v2-overall-progress-text').html("0 images processed");
    jQuery('#cx-v2-overall-progress-bar').width("0%");

    jQuery('#cx-v2-job-current').html("None");
    jQuery('#cx-v2-job-progress-bar').width("0%");

    jQuery('#cx-v2-stat-optimized').html("0");
    jQuery('#cx-v2-stat-errors').html("0");
    jQuery('#cx-v2-stat-remaining').html("0");

    jQuery('#cx_bulk_warning').show();
    jQuery('#cx_bulk_success').hide();
    jQuery('#cx_bulk_error').hide();
    jQuery('#cx-v2-live-log').html("");
}

function compressx_bulk_optimization_canceled(message)
{
    //jQuery('#cx-v2-bulk-ready').show();
    //jQuery('#cx-v2-bulk-progress').hide();
    jQuery('#cx-v2-cancel-bulk').css({'pointer-events': 'auto', 'opacity': '1'});

    jQuery('#cx_bulk_warning').hide();

    jQuery('#cx_bulk_success').show();
    jQuery('#cx_bulk_success_title').html("The process has been canceled");
    jQuery('#cx_bulk_success_message').html(message);
    cx_bulk_task_running=false;
}

function compressx_bulk_optimization_paused()
{
    cx_pause_bulk_optimization=false;
    jQuery('#cx-v2-pause-bulk').css({'pointer-events': 'auto', 'opacity': '1'});
}

function compressx_update_bulk_scan_progress(progress_text,progress_percent)
{
    jQuery('#cx-v2-progress-status').html("Scanning");

    jQuery('#cx-v2-scan-status').html(progress_text);
    jQuery('#cx-v2-scan-progress-bar').width(progress_percent+"%");
}

function compressx_update_bulk_progress(progress_text,progress_percent,sub_progress_text,sub_progress_percent,optimized,errors,remaining)
{

    jQuery('#cx-v2-progress-status').html("Running");

    jQuery('#cx-v2-overall-progress-text').html(progress_text);
    jQuery('#cx-v2-overall-progress-bar').width(progress_percent+"%");

    jQuery('#cx-v2-job-current').html(sub_progress_text);
    jQuery('#cx-v2-job-progress-bar').width(sub_progress_percent+"%");

    jQuery('#cx-v2-stat-optimized').html(optimized);
    jQuery('#cx-v2-stat-errors').html(errors);
    jQuery('#cx-v2-stat-remaining').html(remaining);
}

function compressx_progress_finish(message,title="Optimization Success")
{
    //jQuery('#cx-v2-bulk-ready').show();
    //jQuery('#cx-v2-bulk-progress').hide();

    jQuery('#cx_bulk_warning').hide();

    jQuery('#cx_bulk_success').show();
    jQuery('#cx_bulk_success_title').html(title);
    jQuery('#cx_bulk_success_message').html(message);
    cx_bulk_task_running=false;
}

function compressx_progress_error(message)
{
    //jQuery('#cx-v2-bulk-ready').show();
    //jQuery('#cx-v2-bulk-progress').hide();

    jQuery('#cx_bulk_warning').hide();

    jQuery('#cx_bulk_error').show();
    jQuery('#cx_bulk_error_title').html("Error: Optimization Failed");
    jQuery('#cx_bulk_error_message').html(message);
    cx_bulk_task_running=false;
}

function compressx_show_review_box(jsonarray)
{
    if(jsonarray.show_review)
    {
        jQuery('#cx_rating_box').show();
        jQuery('#cx_size_of_opt_images').html(jsonarray.opt_size);
    }
}

window.addEventListener('click', function(e)
{
    if(cx_bulk_task_running && e.target.matches('a')){
        if(!confirm("Are you sure you want to leave this page?"))
        {
            e.preventDefault();
        }
    }
});

window.addEventListener("beforeunload", function (e) {
    if (cx_bulk_task_running)
    {
        e.preventDefault();
        e.returnValue = "Leaving this page will interrupt the optimization task.";
        return "Leaving this page will interrupt the optimization task.";
    }
});