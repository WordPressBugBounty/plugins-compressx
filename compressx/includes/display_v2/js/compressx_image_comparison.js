//
let isHoveringComparison = false;
let hasUpdateImageData=false;
let latestImageData = null;
let currentComparisonMode = 'webp';

jQuery('#cx-v2-bulk-comparison-container').on('mouseenter', function()
{
    isHoveringComparison = true;
}).on('mouseleave', function() {
    isHoveringComparison = false;
});

jQuery('#cx-v2-bulk-show-webp').on('click', function () {
    switchComparisonMode('webp');
});

jQuery('#cx-v2-bulk-show-avif').on('click', function () {
    switchComparisonMode('avif');
});

jQuery('#cx-v2-bulk-show-formats').on('click', function () {
    switchComparisonMode('formats');
});

function resetComparison()
{
    var $container = jQuery('#cx-v2-bulk-comparison-container');

    // Use the dedicated destroy function
    destroyComparison($container);

    // Reset opacity and add placeholder
    $container.css('opacity', '1');
    $container.append('<div class="cx-comparison-placeholder"><span class="compressx-v2-text-gray-500 compressx-v2-text-sm">Comparison will appear here during optimization</span></div>');

    jQuery('#cx-v2-bulk-format-toggle').hide();

    // Reset comparison buttons to default state
    updateComparisonButtons(currentComparisonMode);

    jQuery('#cx-v2-original-size').text('--');
    jQuery('#cx-v2-webp-size').text('--');
    jQuery('#cx-v2-webp-savings').text('');
    jQuery('#cx-v2-avif-size').text('--');
    jQuery('#cx-v2-avif-savings').text('');

    latestImageData = null;
}

function destroyComparison($container) {
    if ($container.hasClass('twentytwenty-container')) {
        $container.off('.twentytwenty');
        $container.removeClass('twentytwenty-container');
    }
    $container.empty();
}

function switchComparisonMode(mode)
{
    currentComparisonMode = mode;
    updateComparisonButtons(mode);

    if (latestImageData) {
        renderComparison(latestImageData, mode);
    }
}

function updateComparisonButtons(activeMode)
{
    jQuery('#cx-v2-bulk-show-webp, #cx-v2-bulk-show-avif, #cx-v2-bulk-show-formats')
        .removeClass('hover:compressx-v2-bg-gray-100 compressx-v2-bg-green-500 compressx-v2-text-white compressx-v2-border-green-500')
        .addClass('compressx-v2-bg-gray-50 compressx-v2-text-gray-700 compressx-v2-border-gray-300');

    var activeButton = null;
    if (activeMode === 'webp') {
        activeButton = '#cx-v2-bulk-show-webp';
    } else if (activeMode === 'avif') {
        activeButton = '#cx-v2-bulk-show-avif';
    } else if (activeMode === 'formats') {
        activeButton = '#cx-v2-bulk-show-formats';
    }

    if (activeButton)
    {
        jQuery(activeButton)
            .removeClass('compressx-v2-bg-gray-50 compressx-v2-text-gray-700 compressx-v2-border-gray-300')
            .addClass('compressx-v2-bg-green-500 compressx-v2-text-white compressx-v2-border-green-500');
    }
}

function renderComparison(imageData, mode)
{
    var $container = jQuery('#cx-v2-bulk-comparison-container');

    $container.css({
        'opacity': '0',
        'transition': 'opacity 0.3s ease'
    });

    // Completely destroy and rebuild the comparison container
    destroyComparison($container);

    var beforeImg, afterImg, beforeLabel, afterLabel;
    var hasWebP = imageData.webp_url && !imageData.webp_disabled;
    var hasAVIF = imageData.avif_url && !imageData.avif_disabled;

    // Determine which buttons to show and default mode
    var showToggle = hasWebP && hasAVIF;
    var availableModes = [];

    if (hasWebP) availableModes.push('webp');
    if (hasAVIF) availableModes.push('avif');
    if (hasWebP && hasAVIF) availableModes.push('formats');

    // Update button visibility
    jQuery('#cx-v2-bulk-show-webp').toggle(hasWebP && hasAVIF);
    jQuery('#cx-v2-bulk-show-avif').toggle(hasWebP && hasAVIF);
    jQuery('#cx-v2-bulk-show-formats').toggle(hasWebP && hasAVIF);

    // Show or hide the toggle container
    if (showToggle) {
        jQuery('#cx-v2-bulk-format-toggle').show();
    } else {
        jQuery('#cx-v2-bulk-format-toggle').hide();
    }

    // Auto-select appropriate mode if current mode is not available
    if (!availableModes.includes(mode)) {
        mode = availableModes[0] || 'webp';
        currentComparisonMode = mode;
        updateComparisonButtons(mode);
    }

    if (mode === 'webp' && hasWebP) {
        beforeImg = imageData.original_url;
        afterImg = imageData.webp_url;
        beforeLabel = 'Original';
        afterLabel = 'WebP';
    } else if (mode === 'avif' && hasAVIF) {
        beforeImg = imageData.original_url;
        afterImg = imageData.avif_url;
        beforeLabel = 'Original';
        afterLabel = 'AVIF';
    } else if (mode === 'formats' && hasWebP && hasAVIF) {
        beforeImg = imageData.webp_url;
        afterImg = imageData.avif_url;
        beforeLabel = 'WebP';
        afterLabel = 'AVIF';
    } else {
        $container.append('<div class="cx-comparison-placeholder"><span class="compressx-v2-text-gray-500 compressx-v2-text-sm">Comparison not available for this mode</span></div>');
        $container.css('opacity', '1');
        return;
    }

    $container.append('<img src="' + beforeImg + '" />');
    $container.append('<img src="' + afterImg + '" />');

    reinitializeTwentyTwenty($container, function() {
        $container.css('opacity', '1');
    }, beforeLabel, afterLabel);
}

function updateComparisonData(imageData) {
    if (isHoveringComparison) {
        return;
    }

    latestImageData = imageData;

    if (imageData.original_size) {
        jQuery('#cx-v2-original-size').text(imageData.original_size);
    }

    if (imageData.webp_url && !imageData.webp_disabled) {
        jQuery('#cx-v2-webp-size').text(imageData.webp_size || '--');
        jQuery('#cx-v2-webp-savings').text(imageData.webp_savings || '');
    }

    if (imageData.avif_url && !imageData.avif_disabled) {
        jQuery('#cx-v2-avif-size').text(imageData.avif_size || '--');
        jQuery('#cx-v2-avif-savings').text(imageData.avif_savings || '');
    }

    // Determine appropriate comparison mode based on available formats
    var hasWebP = imageData.webp_url && !imageData.webp_disabled;
    var hasAVIF = imageData.avif_url && !imageData.avif_disabled;

    var targetMode = currentComparisonMode;

    // Auto-switch to available format if current mode is not available
    if (currentComparisonMode === 'webp' && !hasWebP && hasAVIF) {
        targetMode = 'avif';
    } else if (currentComparisonMode === 'avif' && !hasAVIF && hasWebP) {
        targetMode = 'webp';
    } else if (currentComparisonMode === 'formats' && !(hasWebP && hasAVIF)) {
        targetMode = hasWebP ? 'webp' : 'avif';
    } else if (!hasWebP && !hasAVIF) {
        // No formats available
        targetMode = 'webp'; // Fallback
    } else if (hasWebP && currentComparisonMode !== 'avif' && currentComparisonMode !== 'formats') {
        targetMode = 'webp';
    } else if (hasAVIF && !hasWebP) {
        targetMode = 'avif';
    }

    if (targetMode !== currentComparisonMode) {
        currentComparisonMode = targetMode;
    }

    renderComparison(imageData, currentComparisonMode);
}

function reinitializeTwentyTwenty($container, callback, beforeLabel, afterLabel) {
    var images = $container.find('img');
    var loadedCount = 0;
    var totalImages = images.length;

    if (totalImages === 0) {
        if (callback) callback();
        return;
    }

    images.off('load.cxcmp').on('load.cxcmp', function () {
        loadedCount++;
        if (loadedCount !== totalImages) return;

        setTimeout(function () {
            if (typeof jQuery.fn.twentytwenty !== 'function') {
                if (callback) callback();
                return;
            }

            var options = {
                default_offset_pct: 0.5,
                move_slider_on_hover: false,
                move_with_handle_only: true,
                click_to_move: false,
                no_overlay: false,
                before_label: beforeLabel || 'Before',
                after_label: afterLabel || 'After'
            };

            var $img = $container.find('img').first();
            var naturalW = $img[0]?.naturalWidth || 0;
            if (naturalW) {
                $container.css({
                    maxWidth: naturalW + 'px',
                    marginLeft: 'auto',
                    marginRight: 'auto'
                });
            }

            $container.twentytwenty(options);
            $container.find('img').css({
                'max-width': '100%',
                'height': 'auto',
                'width': '100%',
                'display': 'block'
            });

            var raf = window.requestAnimationFrame || function (fn) { return setTimeout(fn, 0); };
            raf(async function () {
                jQuery(window).trigger('resize.twentytwenty');
                callback && callback();
            });

        }, 0);
    });

    images.each(function () {
        if (this.complete && this.naturalHeight !== 0) {
            jQuery(this).trigger('load.cxcmp');
        }
    });
}

function init_latestImageData()
{
    var ajax_data = {
        action: 'compressx_get_latest_image_data',
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = JSON.parse(data);
            if (jsonarray.result === 'success')
            {
                updateComparisonData(jsonarray.current_image);
            }
            else
            {
                resetComparison();
            }
        }
        catch (err)
        {
            resetComparison();
        }
    }, function(XMLHttpRequest, textStatus, errorThrown) {
        resetComparison();
    });
}

function autoRefreshLatestImageData()
{
    if(!isHoveringComparison&&hasUpdateImageData)
    {
        init_latestImageData();
        hasUpdateImageData = false;
    }

    setTimeout(autoRefreshLatestImageData, 10000);
}

jQuery(document).on('compressx:hasUpdateImageData', function () {
    hasUpdateImageData = true;
});

jQuery(document).ready(function ($)
{
    init_latestImageData();
    autoRefreshLatestImageData();
});


