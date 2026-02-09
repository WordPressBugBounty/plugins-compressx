jQuery('#cx_replace_media').click(function(e)
{
    e.preventDefault();

    var input = document.getElementById('compressx-new-media-file');
    var file  = input && input.files && input.files[0];

    if (!file)
    {
        alert('Please select an image that you want to replace.');
        return;
    }


    var $saveButton = jQuery(this);
    $saveButton.css({ 'pointer-events': 'none', 'opacity': '0.4' });
    jQuery('#cx_replace_media_progress').show();
    let attachment_id=jQuery('#compressx_attachment_id').val();
    var ajax_data = {
        'action': 'compressx_replace_media',
        'attachment_id': attachment_id,
    };

    compressx_file_upload_request(
        ajax_data,
        'compressx-new-media-file', //file input id
        'image',                    //$_FILES['image']
        function(data)
        {
            try
            {
                var jsonarray = JSON.parse(data);
                if (jsonarray.result === 'success')
                {
                    //$saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                    jQuery('#cx_replace_media_progress').hide();

                    jQuery('#cx_replace_media_text').removeClass('compressx-v2-hidden');
                    setTimeout(function() {
                        jQuery('#cx_replace_media_text').addClass('compressx-v2-hidden');
                        location.href=compressx_media_replace.redirect_url;
                    }, 3000);
                }
                else
                {
                    $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                    jQuery('#cx_replace_media_progress').hide();
                    alert(jsonarray.error || 'Failed to save settings.');
                }
            }
            catch (err)
            {
                $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                jQuery('#cx_replace_media_progress').hide();
                alert(err);
            }
        },
        function(XMLHttpRequest, textStatus, errorThrown)
        {
            $saveButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
            jQuery('#cx_replace_media_progress').hide();
            var error_message = compressx_output_ajaxerror('saving settings', textStatus, errorThrown);
            alert(error_message);
        },
        30000
    );
});

function compressx_file_upload_request(ajax_data, file_input_id, file_field_name, callback, error_callback, time_out)
{
    if (typeof time_out === 'undefined') time_out = 30000;
    if (typeof file_field_name === 'undefined' || !file_field_name) file_field_name = 'image';

    var form_data = (ajax_data instanceof FormData) ? ajax_data : new FormData();

    if (!(ajax_data instanceof FormData)) {
        for (var key in ajax_data) {
            if (!ajax_data.hasOwnProperty(key)) continue;
            form_data.append(key, ajax_data[key]);
        }
    }

    form_data.append('nonce', compressx_ajax_object.ajax_nonce);

    var input = document.getElementById(file_input_id);
    var file  = input && input.files && input.files[0];

    if (!file) {
        if (typeof error_callback === 'function') {
            error_callback(null, 'no_file', 'No file selected.');
        }
        return;
    }

    form_data.append(file_field_name, file);

    jQuery.ajax({
        type: "post",
        url: compressx_ajax_object.ajax_url,
        data: form_data,
        processData: false,
        contentType: false,
        success: function (data) {
            callback(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error_callback(XMLHttpRequest, textStatus, errorThrown);
        },
        timeout: time_out
    });
}

jQuery(function ($)
{
    // ===== Helpers =====
    function cx_getSettingsPayload()
    {
        const auto_re_optimize = $('#cx_auto_re_optimize').is(':checked') ? 1 : 0;
        const thumbnail_generation = $('input[name="thumbnail_strategy"]:checked').val() || 'default_fill';

        return {
            auto_re_optimize,
            thumbnail_generation,
        };
    }

    let ajaxTimer = null;
    function compressx_save_media_replace_setting(triggerEl,triggerNotice)
    {
        window.clearTimeout(ajaxTimer);

        ajaxTimer = window.setTimeout(function ()
        {
            const payload = cx_getSettingsPayload();

            var ajax_data = {
                'action': 'compressx_save_media_replace_setting',
                'settings': payload,
            };

            jQuery(triggerEl).css({'pointer-events': 'none', 'opacity': '0.4'});

            compressx_post_request(ajax_data, function (data)
            {
                try
                {
                    var jsonarray = jQuery.parseJSON(data);

                    jQuery(triggerEl).css({'pointer-events': 'auto', 'opacity': '1'});
                    if (jsonarray.result === 'success')
                    {
                        jQuery(triggerNotice).removeClass("hidden");
                        setTimeout(function ()
                        {
                            jQuery(triggerNotice).addClass( 'hidden' );
                        }, 3000);
                    }

                }
                catch (err)
                {
                    alert(err);
                    jQuery(triggerEl).css({'pointer-events': 'auto', 'opacity': '1'});
                }
            }, function (XMLHttpRequest, textStatus, errorThrown)
            {
                jQuery('triggerEl').css({'pointer-events': 'auto', 'opacity': '1'});
                var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
                alert(error_message);
            });
        }, 200);
    }

    // ===== Bindings =====
    // checkbox change
    $(document).on('change', '#cx_auto_re_optimize', function ()
    {
        var notice= jQuery("#auto_re_optimize-saved-indicator");
        compressx_save_media_replace_setting(this,notice);
    });

    $(document).on('change', '#cx_backup_original', function ()
    {
        var notice= jQuery("#cx_backup_original-saved-indicator");
        compressx_save_media_replace_setting(this,notice);
    });

    // radio change
    $(document).on('change', 'input[name="thumbnail_strategy"]', function ()
    {
        var thumbnail_generation = $('input[name="thumbnail_strategy"]:checked').val() || 'default_fill';
        var notice=null;
        if(thumbnail_generation==='default_fill')
        {
            notice= jQuery("#default_fill-saved-indicator");
        }
        else
        {
            notice= jQuery("#match_original-saved-indicator");
        }
        compressx_save_media_replace_setting(this,notice);
    });
    //cx_backup_keep_versions
    $(document).on('change', '#cx_backup_keep_versions', function ()
    {
        var notice= jQuery("#cx_backup_keep_versions-saved-indicator");
        compressx_save_media_replace_setting(this,notice);
    });

    $(document).on('change', '#cx_backup_keep_date', function ()
    {
        var notice= jQuery("#cx_backup_keep_date-saved-indicator");
        compressx_save_media_replace_setting(this,notice);
    });
});

jQuery(function ($)
{
    var og_type= compressx_media_replace.type;
    var $dz   = $("#compressx-new-media-dropzone");
    var $inp  = $("#compressx-new-media-file");

    var $lay  = $("#compressx-new-media-preview-layout");
    var $pclk = $("#compressx-new-media-preview-click");
    var $pre  = $("#compressx-new-media-preview");

    var $remove  = $("#compressx-remove-new-media-preview");

    var $dim  = $("#compressx-new-media-dim");
    var $size = $("#compressx-new-media-size");
    var $fmt  = $("#compressx-new-media-format");

    var lastUrl = null;

    function humanSize(bytes) {
        if (bytes === undefined || bytes === null) return "—";
        var units = ["B", "KB", "MB", "GB"], i = 0, n = bytes;
        while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
        var fixed = i === 0 ? 0 : (n >= 10 ? 0 : 1);
        return n.toFixed(fixed) + " " + units[i];
    }

    function getFormat(file) {
        if (file.type) {
            var p = file.type.split("/");
            return (p[1] || "—").toUpperCase();
        }
        var m = (file.name || "").match(/\.([a-z0-9]+)$/i);
        return m ? m[1].toUpperCase() : "—";
    }

    function showEmpty() {
        $lay.hide();
        $dz.show();
    }

    function showFilled() {
        $dz.hide();
        $lay.show();
    }

    function setFile(file) {
        if (!file) return;

        if (!file.type || file.type.indexOf("image/") !== 0) {
            alert("Please choose an image file.");
            return;
        }

        try
        {
            var dt = new DataTransfer();
            dt.items.add(file);
            $inp[0].files = dt.files;
        } catch (e) {}

        $size.text(humanSize(file.size));
        $fmt.text(getFormat(file));

        var url = URL.createObjectURL(file);
        var img = new Image();

        img.onload = function ()
        {
            $dim.text(img.naturalWidth + " × " + img.naturalHeight);

            if (lastUrl) URL.revokeObjectURL(lastUrl);
            lastUrl = url;

            $pre.attr("src", url);
            cx_showNotice(img.naturalWidth,img.naturalHeight,file.type);

        };

        img.onerror = function ()
        {
            URL.revokeObjectURL(url);
            alert("Failed to read the image.");
            $inp.val("");
            $dim.text("—"); $size.text("—"); $fmt.text("—");
            $pre.attr("src", "");
            showEmpty();
        };

        img.src = url;
    }

    function openDialog() {
        $inp[0].click();
    }

    function removeImage()
    {
        $inp.val("");

        $pre.attr("src", "");
        $dim.text("—");
        $size.text("—");
        $fmt.text("—");

        if (lastUrl) {
            URL.revokeObjectURL(lastUrl);
            lastUrl = null;
        }

        $lay.hide();
        $dz.show();
    }

    $dz.on("click", openDialog);

    $pclk.on("click", openDialog);

    $remove.on("click", removeImage);

    $inp.on("change", function () {
        var file = this.files && this.files[0];
        if (file) setFile(file);
    });

    $dz.on("dragenter dragover", function (e) {
        e.preventDefault(); e.stopPropagation();
        $dz.addClass("compressx-drop-active");
    });
    $dz.on("dragleave drop", function (e) {
        e.preventDefault(); e.stopPropagation();
        $dz.removeClass("compressx-drop-active");
    });

    $dz.on("drop", function (e) {
        var files = e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
        var file = files && files[0];
        if (file) setFile(file);
    });

    showEmpty();

    function cx_showNotice(width,height,mimetype)
    {
        let og_w=$("#og_image_dimensions").data("width");
        let og_h=$("#og_image_dimensions").data("height");

        let og_mime=$("#og_image_dimensions").data("mime");
        if(mimetype!==og_mime)
        {
            $("#cx-upload-notice").show();
            $("#cx_notice_title").html("File format must match");
            $("#cx_notice_content").html("The replacement file must use the same format as the current media ("+og_mime+" → "+mimetype+" detected). Please upload a "+og_type+" file to avoid unexpected issues.");
            removeImage();
            return;
        }

        showFilled();
        /*
        if(og_w>width)
        {
            $("#cx-upload-notice").show();
            $("#cx_notice_title").html("Smaller Image Detected");
            $("#cx_notice_content").html("The replacement image is smaller than the original. Some larger thumbnail sizes may not be generated, which can affect pages that reference those sizes.");
        }
        else if(og_w<width)
        {
            $("#cx-upload-notice").show();
            $("#cx_notice_title").html("Larger Image Detected");
            $("#cx_notice_content").html("The replacement image is significantly larger than the original. This may generate larger thumbnails and increase disk usage.");
        }*/
    }

    $("#cx-hide-upload-notice").on("click", function ()
    {
        $("#cx-upload-notice").hide();
    });
});

jQuery(document).ready(function ($)
{
    $(document).on('click', '.cx-restore-btn', function ()
    {
        var $btn = $(this);
        var backup_id = $btn.data('backup-id');
        var attachment_id = $btn.data('attachment-id');

        var ajax_data = {
            'action': 'compressx_restore_media_replace',
            'backup_id': backup_id,
            'attachment_id':attachment_id
        };

        jQuery(".cx-restore-btn").css({'pointer-events': 'none', 'opacity': '0.4'});
        var $wrap = $btn.closest('.cx-restore-item');
        let $progress = $wrap.find('.cx-restore-progress');
        let $success = $wrap.find('.cx-restore-text');
        $progress.show();

        compressx_post_request(ajax_data, function (data)
        {
            jQuery(".cx-restore-btn").css({'pointer-events': 'auto', 'opacity': '1'});
            $progress.hide();
            try
            {
                var jsonarray = jQuery.parseJSON(data);

                if (jsonarray.result === 'success')
                {
                    $success.removeClass("hidden");
                    setTimeout(function ()
                    {
                        location.href=compressx_media_replace.redirect_url;
                        $success.addClass('hidden');
                    }, 3000);
                }
            }
            catch (err)
            {
                alert(err);
            }
        }, function (XMLHttpRequest, textStatus, errorThrown)
        {
            jQuery(".cx-restore-btn").css({'pointer-events': 'auto', 'opacity': '1'});
            var error_message = compressx_output_ajaxerror('replace media', textStatus, errorThrown);
            alert(error_message);
        });
    });
});