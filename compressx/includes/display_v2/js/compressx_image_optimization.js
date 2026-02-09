jQuery('#cx_hide_big_update').click(function()
{
    jQuery('#cx_big_update').hide();

    var ajax_data = {
        'action': 'compressx_hide_big_update_v2',
    };

    compressx_post_request(ajax_data, function(data)
    {
    }, function(XMLHttpRequest, textStatus, errorThrown) {
    });
});

jQuery('#cx_show_size_threshold_tip').on('click', function()
{
    jQuery('#cx_size_threshold_tip').slideToggle(150);
});

jQuery('input:radio[name=compression_mode]').click(function(e)
{
    if(jQuery(this).prop('checked'))
    {
        //var $saveButton = jQuery("#cx-v2-save-settings");

        let value = jQuery(this).prop('value');
        if(value==='smart')
        {
            //$saveButton.css({ 'pointer-events': 'none', 'opacity': '0.4' });

            jQuery("#compressx_general_quality_setting").hide();
            jQuery("#compressx_smart_quality_setting").show();
            jQuery("#cx_compression_mode_smart").closest('label').addClass('compressx-v2-bg-white');
            jQuery("#cx_compression_mode_general").closest('label').removeClass('compressx-v2-bg-white');
        }
        else if(value==='general')
        {
            //$saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });

            jQuery("#compressx_general_quality_setting").show();
            jQuery("#compressx_smart_quality_setting").hide();
            jQuery("#cx_compression_mode_general").closest('label').addClass('compressx-v2-bg-white');
            jQuery("#cx_compression_mode_smart").closest('label').removeClass('compressx-v2-bg-white');
        }
    }
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
    if(ret === false)
    {
        if(jQuery('#cx_convert_to_webp').prop('checked'))
        {
            jQuery('#cx_convert_to_webp').prop('checked', false);
        }
        else
        {
            jQuery('#cx_convert_to_webp').prop('checked', true);
        }
    }
    else
    {
        if(jQuery('#cx_convert_to_webp').prop('checked'))
        {
            jQuery('#cx_convert_to_webp').parent('label').addClass('compressx-v2-bg-white');
        }
        else
        {
            jQuery('#cx_convert_to_webp').parent('label').removeClass('compressx-v2-bg-white');
        }
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
    if(ret === false)
    {
        if(jQuery('#cx_convert_to_avif').prop('checked'))
        {
            jQuery('#cx_convert_to_avif').prop('checked', false);
        }
        else
        {
            jQuery('#cx_convert_to_avif').prop('checked', true);
        }
    }
    else
    {
        if(jQuery('#cx_convert_to_avif').prop('checked'))
        {
            jQuery('#cx_convert_to_avif').parent('label').addClass('compressx-v2-bg-white');
        }
        else
        {
            jQuery('#cx_convert_to_avif').parent('label').removeClass('compressx-v2-bg-white');
        }
    }
});

jQuery('#cx_converter_method_gd').click(function()
{
    if(jQuery('#cx_converter_method_gd').prop('checked'))
    {
        jQuery('#cx_converter_method_gd').parent('label').addClass('compressx-v2-bg-white');
    }
    else
    {
        jQuery('#cx_converter_method_gd').parent('label').removeClass('compressx-v2-bg-white');
    }

    if(compressx_alert.support_gd_webp)
    {
        jQuery('#cx_convert_to_webp').prop('disabled', false);
        jQuery('#cx_webp_status').removeClass("compressx-v2-text-red-600");
        jQuery('#cx_webp_status').addClass("compressx-v2-text-green-600");
        jQuery('#cx_webp_status').html("Supported");
    }
    else
    {
        jQuery('#cx_convert_to_webp').prop('disabled', true);
        jQuery('#cx_webp_status').addClass("compressx-v2-text-red-600");
        jQuery('#cx_webp_status').removeClass("compressx-v2-text-green-600");
        jQuery('#cx_webp_status').html("Unsupported");
    }

    if(compressx_alert.support_gd_avif)
    {
        jQuery('#cx_convert_to_avif').prop('disabled', false);
        jQuery('#cx_avif_status').removeClass("compressx-v2-text-red-600");
        jQuery('#cx_avif_status').addClass("compressx-v2-text-green-600");
        jQuery('#cx_avif_status').html("Supported");
    }
    else
    {
        jQuery('#cx_convert_to_avif').prop('disabled', true);
        jQuery('#cx_avif_status').addClass("compressx-v2-text-red-600");
        jQuery('#cx_avif_status').removeClass("compressx-v2-text-green-600");
        jQuery('#cx_avif_status').html("Unsupported");
    }
});

jQuery('#cx_converter_method_imagick').click(function()
{
    if(jQuery('#cx_converter_method_imagick').prop('checked'))
    {
        jQuery('#cx_converter_method_imagick').parent('label').addClass('compressx-v2-bg-white');
    }
    else
    {
        jQuery('#cx_converter_method_imagick').parent('label').removeClass('compressx-v2-bg-white');
    }

    if(compressx_alert.support_imagick_webp)
    {
        jQuery('#cx_convert_to_webp').prop('disabled', false);
        jQuery('#cx_webp_status').removeClass("compressx-v2-text-red-600");
        jQuery('#cx_webp_status').addClass("compressx-v2-text-green-600");
        jQuery('#cx_webp_status').html("Supported");
    }
    else
    {
        jQuery('#cx_convert_to_webp').prop('disabled', true);
        jQuery('#cx_webp_status').addClass("compressx-v2-text-red-600");
        jQuery('#cx_webp_status').removeClass("compressx-v2-text-green-600");
        jQuery('#cx_webp_status').html("Unsupported");
    }

    if(compressx_alert.support_imagick_avif)
    {
        jQuery('#cx_convert_to_avif').prop('disabled', false);
        jQuery('#cx_avif_status').removeClass("compressx-v2-text-red-600");
        jQuery('#cx_avif_status').addClass("compressx-v2-text-green-600");
        jQuery('#cx_avif_status').html("Supported");
    }
    else
    {
        jQuery('#cx_convert_to_avif').prop('disabled', true);
        jQuery('#cx_avif_status').addClass("compressx-v2-text-red-600");
        jQuery('#cx_avif_status').removeClass("compressx-v2-text-green-600");
        jQuery('#cx_avif_status').html("Unsupported");
    }
});

jQuery('#cx_start_bulk_optimization').click(function()
{
    let params = new URLSearchParams(window.location.search);

    params.set('view', 'bulk');

    window.location.href = window.location.pathname + '?' + params.toString();
});

jQuery('#cx_start_bulk_optimization_2').click(function()
{
    let params = new URLSearchParams(window.location.search);

    params.set('view', 'bulk');

    window.location.href = window.location.pathname + '?' + params.toString();
});

jQuery('#cx-v2-save-settings').click(function(e)
{
    e.preventDefault();

    var $saveButton = jQuery(this);
    $saveButton.css({ 'pointer-events': 'none', 'opacity': '0.4' });
    jQuery('#cx-v2-save-settings-progress').show();

    var json = {};
    if(jQuery('#cx_convert_to_webp').prop('checked'))
    {
        json['convert_to_webp'] ='1';
    }
    else
    {
        json['convert_to_webp'] ='0';
    }

    if(jQuery('#cx_convert_to_avif').prop('checked'))
    {
        json['convert_to_avif'] ='1';
    }
    else
    {
        json['convert_to_avif'] ='0';
    }

    if(jQuery('#cx_converter_method_gd').prop('checked'))
    {
        json['converter_method'] = 'gd';
    }
    else if(jQuery('#cx_converter_method_imagick').prop('checked'))
    {
        json['converter_method'] = 'imagick';
    }
    else
    {
        json['converter_method'] = '';
    }

    json['auto_optimize'] = jQuery('#cx_enable_auto_optimize').attr('data-checked');

    json['interface_version']= jQuery('input[name="cx-v2-interface-version"]:checked').val();
    json['image_load']= jQuery('input[name="cx-v2-browser"]:checked').val();
    json['resize']=jQuery('#cx-v2-resize-enable').attr('data-checked') === '1';
    json['resize_width']=jQuery('#cx-v2-resize-width').val();
    json['resize_height']=jQuery('#cx-v2-resize-height').val();
    json['remove_exif']=jQuery('input[name="cx-v2-exif"]:checked').val() === 'strip';
    json['auto_remove_larger_format']=jQuery('#cx-v2-avoid-larger').attr('data-checked') === '1';
    json['exclude_png']=jQuery('#cx-v2-exclude-png').is(':checked');
    json['exclude_png_webp']    =jQuery('#cx-v2-exclude-png-webp').is(':checked');
    json['converter_images_pre_request'] =jQuery('#cx-v2-throughput').val();
    json['quality_webp']=jQuery('#cx-v2-webp-quality-input').val();
    json['quality_avif']=jQuery('#cx-v2-avif-quality-input').val();

    if (json['quality_webp'] < 1 || json['quality_webp'] > 100) {
        alert('WebP Quality must be between 1 and 99.');
        return;
    }
    if ( json['quality_avif'] < 1 ||  json['quality_avif'] > 100) {
        alert('AVIF Quality must be between 1 and 99.');
        return;
    }

    var ajax_data = {
        'action': 'compressx_save_image_optimization_settings',
        'settings': JSON.stringify(json),
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = JSON.parse(data);
            if (jsonarray.result === 'success')
            {
                $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                jQuery('#cx-v2-save-settings-progress').hide();

                jQuery('#cx-v2-save-settings-text').removeClass('compressx-v2-hidden');
                setTimeout(function() {
                    jQuery('#cx-v2-save-settings-text').addClass('compressx-v2-hidden');
                }, 3000);

                if(jsonarray.interface_version_changed===true)
                {
                    location.reload();
                }
            }
            else
            {
                $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                jQuery('#cx-v2-save-settings-progress').hide();
                alert(jsonarray.error || 'Failed to save settings.');
            }
        }
        catch (err)
        {
            $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
            jQuery('#cx-v2-save-settings-progress').hide();
            alert('An error occurred while saving settings.');
        }
    }, function(XMLHttpRequest, textStatus, errorThrown) {
        $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
        jQuery('#cx-v2-save-settings-progress').hide();
        var error_message = compressx_output_ajaxerror('saving settings', textStatus, errorThrown);
        alert(error_message);
    });
});

jQuery('#cx_enable_auto_optimize').on('click', function()
{
    updateAutoOptimizeSwitch();
});

jQuery('#cx-v2-free-toggle-advanced').click(function(e)
{
    e.preventDefault();
    var advancedArrow = jQuery('#cx-v2-free-advanced-arrow');
    var advancedSection = jQuery('#cx-v2-free-advanced-section');

    var isHidden =advancedSection.css('display') === 'none';

    if (isHidden)
    {
        advancedSection.slideDown(300);
        advancedArrow.removeClass('dashicons-arrow-right').addClass('dashicons-arrow-down');
    }
    else
    {
        advancedSection.slideUp(300);
        advancedArrow.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-right');
    }
});

jQuery('.cx-v2-tab-btn').click(function(e)
{
    e.preventDefault();
    var $btn = jQuery(this);
    var tabName = $btn.data('tab');

    jQuery('.cx-v2-tab-btn').removeClass('compressx-v2-border-b-2 compressx-v2-border-blue-600 compressx-v2-font-medium').addClass('compressx-v2-text-slate-600');
    $btn.removeClass('compressx-v2-text-slate-600').addClass('compressx-v2-border-blue-600 compressx-v2-font-medium compressx-v2-border-b-2');

    jQuery('[data-tab-panel]').addClass('compressx-v2-hidden');
    jQuery('[data-tab-panel="' + tabName + '"]').removeClass('compressx-v2-hidden');
});

function updateAutoOptimizeSwitch()
{
    var $switchBtn = jQuery('#cx_enable_auto_optimize');
    var $knob = $switchBtn.find('span');

    var currentState = $switchBtn.attr('data-checked');
    var newState = currentState === '1' ? '0' : '1';
    $switchBtn.attr('data-checked', newState);

    update_switch($switchBtn,$knob);
}

jQuery('#cx-v2-avoid-larger').on('click', function()
{
    var $switchBtn = jQuery('#cx-v2-avoid-larger');
    var $knob = $switchBtn.find('span');

    var currentState = $switchBtn.attr('data-checked');
    var newState = currentState === '1' ? '0' : '1';
    $switchBtn.attr('data-checked', newState);
    update_switch($switchBtn,$knob);
});

jQuery('#cx-v2-resize-enable').on('click', function()
{
    var $switchBtn = jQuery('#cx-v2-resize-enable');
    var $knob = $switchBtn.find('span');

    var currentState = $switchBtn.attr('data-checked');
    var newState = currentState === '1' ? '0' : '1';
    $switchBtn.attr('data-checked', newState);
    update_switch($switchBtn,$knob);
});

function update_switch($switchBtn,$knob)
{
    var isChecked = $switchBtn.attr('data-checked') === '1';
    if (isChecked) {
        $switchBtn.removeClass('compressx-v2-bg-gray-300');
        $switchBtn.addClass('compressx-v2-bg-blue-600');
        $switchBtn.removeClass('compressx-v2-border-gray-300');
        $switchBtn.addClass('compressx-v2-border-blue-600');
        $knob.css('transform', 'translateX(20px)');
        $switchBtn.attr('aria-checked', 'true');
    } else {
        $switchBtn.addClass('compressx-v2-bg-gray-300');
        $switchBtn.removeClass('compressx-v2-bg-blue-600');
        $switchBtn.addClass('compressx-v2-border-gray-300');
        $switchBtn.removeClass('compressx-v2-border-blue-600');
        $knob.css('transform', 'translateX(0px)');
        $switchBtn.attr('aria-checked', 'false');
    }
}

jQuery(document).ready(function ($) {
    var $switchBtn = jQuery('#cx_enable_auto_optimize');
    var $knob = $switchBtn.find('span');
    update_switch($switchBtn,$knob);

    $switchBtn = jQuery('#cx-v2-avoid-larger');
    $knob = $switchBtn.find('span');
    update_switch($switchBtn,$knob);

    $switchBtn = jQuery('#cx-v2-resize-enable');
    $knob = $switchBtn.find('span');
    update_switch($switchBtn,$knob);
});
