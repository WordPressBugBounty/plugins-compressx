
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

    jQuery('#cx-v2-show-more-size').click(function() {
        jQuery('.cx-v2-thumbnail-size').show();
        jQuery(this).hide();
    });

    jQuery('#cx-v2-save-settings').click(function(e)
    {
        e.preventDefault();

        var $saveButton = jQuery(this);
        var originalText = $saveButton.text();

        $saveButton.css({ 'pointer-events': 'none', 'opacity': '0.4' });
        jQuery('#cx-v2-save-settings-progress').show();

        var settings = {
            size_settings: {},
            list_view_setting: {},
            cache_control_setting: {}
        };

        jQuery('input[option="size_setting"]').each(function() {
            var name = jQuery(this).attr('name');
            var checked = jQuery(this).is(':checked');
            settings.size_settings[name] = checked;
        });

        jQuery('input[option="list_view_setting"]').each(function() {
            var name = jQuery(this).attr('name');
            var checked = jQuery(this).is(':checked');
            settings.list_view_setting[name] = checked ? '1' : '0';
        });

        jQuery('input[option="cache_control_setting"]').each(function() {
            var name = jQuery(this).attr('name');
            var checked = jQuery(this).is(':checked');
            settings.cache_control_setting[name] = checked ? '1' : '0';
        });

        var select_folders = jQuery('#compressx_exclude_dir_node').find('.cx-remove-custom-exclude-tree');
        var json = {};
        jQuery.each(select_folders, function ()
        {
            var value=jQuery(this).data("id");
            //var value = select_item.data( 'id' );
            json[value]=value;
        });

        var exclude_node=JSON.stringify(json);

        //
        var ajax_data = {
            'action': 'compressx_v2_save_settings',
            'settings': JSON.stringify(settings),
            'excludes':exclude_node,
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

                    jQuery('#cx-v2-save-settings-text').removeClass('hidden');
                    setTimeout(function() {
                        jQuery('#cx-v2-save-settings-text').addClass('hidden');
                    }, 3000);
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

jQuery('#cx-v2-delete-file').click(function() {
    var confirm_text = jQuery('#cx-v2-confirm-delete-file').val();
    if (confirm_text !== 'DELETE') {
        alert('Please type "DELETE" to confirm.');
        return;
    }

    if (!confirm('Are you sure you want to delete all WebP and AVIF images? This action cannot be undone.')) {
        return;
    }

    var $deleteButton = jQuery(this);
    $deleteButton.css({ 'pointer-events': 'none', 'opacity': '0.4' });
    jQuery('#cx-v2-delete-file-progress').show();

    var ajax_data = {
        'action': 'compressx_delete_files'
    };

    compressx_post_request(ajax_data, function(data)
    {
        try
        {
            var jsonarray = JSON.parse(data);
            if (jsonarray.result === 'success')
            {
                $deleteButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                jQuery('#cx-v2-delete-file-progress').hide();

                jQuery('#cx-v2-delete-file-success').removeClass('hidden');
                jQuery('#cx-v2-confirm-delete-file').val('');
                setTimeout(function() {
                    jQuery('#cx-v2-delete-file-success').addClass('hidden');
                }, 3000);
            } else {
                $deleteButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
                jQuery('#cx-v2-delete-file-progress').hide();
                alert(jsonarray.error || 'Failed to delete files.');
            }
        } catch (err) {
            $deleteButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
            jQuery('#cx-v2-delete-file-progress').hide();
            alert('An error occurred while deleting files.');
        }
    }, function(XMLHttpRequest, textStatus, errorThrown) {
        $deleteButton.css({ 'pointer-events': 'auto', 'opacity': '1' });
        jQuery('#cx-v2-delete-file-progress').hide();
        var error_message = compressx_output_ajaxerror('deleting files', textStatus, errorThrown);
        alert(error_message);
    });
});