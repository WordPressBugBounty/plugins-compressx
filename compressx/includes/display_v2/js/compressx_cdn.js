jQuery(document).ready(function($) {
    $('#compressx_save_cdn').click(function() {
        compressx_save_cdn_setting();
    });

    $('#compressx_purge_cache').click(function() {
        compressx_purge_cache();
    });

    $('#compressx_cdn_provider').on('change', function() {
        var provider = $(this).val();
        if (provider === 'cloudflare') {
            $('#cloudflare_container').show();
            $('#bunnycdn_container').hide();
        } else if (provider === 'bunnycdn') {
            $('#cloudflare_container').hide();
            $('#bunnycdn_container').show();
        }
    });

    $(document).trigger('compressx_cdn_v2_js_ready');
});

function compressx_save_cdn_setting() {
    var json = {};
    var option_type = 'cf_cdn';
    var action_name = 'compressx_save_cdn';

    var save_override = jQuery(document).triggerHandler('compressx_cdn_v2_save_override');
    if (save_override) {
        return save_override;
    }

    var setting_data = compressx_ajax_data_transfer(option_type);
    var json1 = JSON.parse(setting_data);

    jQuery.extend(json1, json);
    setting_data = JSON.stringify(json1);

    var ajax_data = {
        'action': action_name,
        'setting': setting_data,
    };

    jQuery('#compressx_save_cdn').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_save_cdn_progress').show();

    compressx_post_request(ajax_data, function (data) {
        try {
            var jsonarray = jQuery.parseJSON(data);
            jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_cdn_progress').hide();

            if (jsonarray.result === 'success') {
                jQuery('#compressx_save_cdn_text').removeClass("hidden");
                setTimeout(function () {
                    jQuery('#compressx_save_cdn_text').addClass('hidden');
                }, 3000);
            } else {
                alert(jsonarray.error);
            }
        } catch (err) {
            alert(err);
            jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
            jQuery('#compressx_save_cdn_progress').hide();
        }
    }, function (XMLHttpRequest, textStatus, errorThrown) {
        jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
        jQuery('#compressx_save_cdn_progress').hide();
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });
}

function compressx_purge_cache() {
    var json = {};
    var setting_data = compressx_ajax_data_transfer('cf_cdn');
    var json1 = JSON.parse(setting_data);

    jQuery.extend(json1, json);
    setting_data = JSON.stringify(json1);

    var ajax_data = {
        'action': 'compressx_purge_cache',
        'setting': setting_data,
    };

    jQuery('#compressx_purge_cache').css({'pointer-events': 'none', 'opacity': '0.4'});
    jQuery('#compressx_purge_cache_progress').show();

    compressx_post_request(ajax_data, function (data) {
        jQuery('#compressx_purge_cache_progress').hide();
        try {
            var jsonarray = jQuery.parseJSON(data);
            jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});

            if (jsonarray.result === 'success') {
                jQuery('#compressx_purge_cache_text').removeClass("hidden");
                setTimeout(function () {
                    jQuery('#compressx_purge_cache_text').addClass('hidden');
                }, 3000);
            } else {
                alert(jsonarray.error);
            }
        } catch (err) {
            alert(err);
            jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});
        }
    }, function (XMLHttpRequest, textStatus, errorThrown) {
        jQuery('#compressx_purge_cache_progress').hide();
        jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});
        var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
        alert(error_message);
    });
}
