
window.compressx = window.compressx || {};

(function($, w, undefined)
{
    w.compressx.media={
        progress_queue:[],
        lock:false,
        init:function()
        {
            $( document ).on( 'click', '.cx-media-item a.cx-media', this.handle_action_button);
            $( document ).on( 'click', '.misc-pub-cx a.cx-media', this.handle_action_button_edit);
            $( document ).on( 'click', '.cx-media-attachment a.cx-media', this.handle_action_button_attachment);
            w.compressx.media.get_progress();
        },
        handle_action_button:function ()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }

            var $row = $(this).closest('.cx-media-item');
            var $select = $row.find('.cx-media-selected');
            var action = $select.val();

            if(action==="")
            {
                return;
            }

            var id=$( this ).data( 'id' );
            $( this ).html("Progressing...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_action=w.compressx.media.get_ajax_action(action);

            var ajax_data = {
                'action': ajax_action,
                'id':id
            };
            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.get_progress();

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.get_progress();
            });
        },
        handle_action_button_edit:function()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }

            var $row = $(this).closest('.misc-pub-cx');
            var $select = $row.find('.cx-media-selected');
            var action = $select.val();

            if(action==="")
            {
                return;
            }

            var id=$( this ).data( 'id' );
            $( this ).html("Converting...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_action=w.compressx.media.get_ajax_action(action);
            var ajax_data = {
                'action': ajax_action,
                'id':id
            };
            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.get_progress('edit');

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.get_progress('edit');
            });
        },
        handle_action_button_attachment:function()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }

            var $row = $(this).closest('.cx-media-attachment');
            var $select = $row.find('.cx-media-selected');
            var action = $select.val();

            if(action==="")
            {
                return;
            }

            var id=$( this ).data( 'id' );
            $( this ).html("Converting...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_action=w.compressx.media.get_ajax_action(action);
            var ajax_data = {
                'action': ajax_action,
                'id':id
            };

            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.get_progress('attachment');

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.get_progress('attachment');
            });
        },
        get_ajax_action: function(action) {
            switch (action) {
                case 'convert': return 'compressx_opt_single_image';
                case 'delete': return 'compressx_delete_single_image';
                default: return '';
            }
        },
        get_progress:function(page='media')
        {
            var ids=[];
            if(page=='media')
            {
                var media=$('.cx-media-item');
                if ( media.length>0 )
                {
                    media.each( function()
                    {
                        ids.push( $( this ).data( 'id' ) );
                    } );
                }
            }
            else if(page=='attachment')
            {
                var id=$('.cx-media-attachment').data( 'id' );
                ids.push(id );
            }
            else
            {
                var id=$('.misc-pub-cx').data( 'id' );
                ids.push(id );
            }

            if(ids.length<1)
            {
                return;
            }
            var ids_json=JSON.stringify(ids);
            var ajax_data = {
                'action': 'compressx_get_opt_single_image_progress',
                ids:ids_json,
                page:page
            };

            compressx_post_request(ajax_data, function(data)
            {
                try
                {
                    if(typeof data !== 'undefined' && data !== '')
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        w.compressx.media.update(jsonarray,page);
                        if (jsonarray.result === 'success')
                        {
                            if(jsonarray.continue)
                            {
                                setTimeout(function ()
                                {
                                    w.compressx.media.get_progress(page);
                                }, 1000);
                            }
                            else if(jsonarray.finished)
                            {
                                w.compressx.media.lockbtn(false);
                            }
                            else
                            {
                                //w.compressx.media.optimize_timeout_image(page);
                                w.compressx.media.lockbtn(false);
                            }

                        }
                        else
                        {
                            if(jsonarray.timeout)
                            {
                                //w.compressx.media.optimize_timeout_image(page);
                                w.compressx.media.lockbtn(false);
                            }
                            else
                            {
                                w.compressx.media.lockbtn(false);
                            }
                        }
                    }
                }
                catch(err)
                {
                    alert(err);
                    w.compressx.media.lockbtn(false);
                }

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                setTimeout(function ()
                {
                    w.compressx.media.get_progress(page);
                }, 1000);
            });
        },
        update:function (jsonarray,page='media')
        {
            if(page=='edit')
            {
                var id=$('.misc-pub-cx').data( 'id' );
                if(jsonarray.hasOwnProperty(id))
                {
                    $( '.misc-pub-cx' ).html(jsonarray[id]['html']);
                }
            }
            else if(page=='attachment')
            {
                var media=$('.cx-media-attachment');
                if ( media.length>0 )
                {
                    media.each( function()
                    {
                        var id=$( this ).data( 'id' );
                        if(jsonarray.hasOwnProperty(id))
                        {
                            $( this ).html(jsonarray[id]['html']);
                        }
                    } );
                }
            }
            else
            {
                var media=$('.cx-media-item');
                if ( media.length>0 )
                {
                    media.each( function()
                    {
                        var id=$( this ).data( 'id' );
                        if(jsonarray.hasOwnProperty(id))
                        {
                            $( this ).html(jsonarray[id]['html']);
                        }
                    } );
                }
            }
        },
        lockbtn:function (status)
        {
            w.compressx.media.lock=status;
        },
        islockbtn:function ()
        {
            return w.compressx.media.lock;
        }
    };
    w.compressx.media.init();
})(jQuery, window);