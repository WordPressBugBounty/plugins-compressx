
window.compressx = window.compressx || {};

(function($, w, undefined)
{
    w.compressx.media={
        progress_queue:[],
        lock:false,
        init:function()
        {
            $( document ).on( 'click', '.cx-media-item a.cx-media', this.optimize_image );
            $( document ).on( 'click', '.cx-media-item a.cx-media-delete', this.restore_image);
            $( document ).on( 'click', '.misc-pub-cx a.cx-media-delete', this.restore_image_edit);
            $( document ).on( 'click', '.misc-pub-cx a.cx-media', this.optimize_image_edit);
            $( document ).on( 'click', '.cx-media-attachment a.cx-media', this.optimize_image_attachment);
            $( document ).on( 'click', '.cx-media-attachment a.cx-media-delete', this.restore_image_attachment);
            $( document ).on( 'click', '.thumbnail', this.get_attachment_progress);
            w.compressx.media.get_progress();
        },
        optimize_image:function ()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            var id=$( this ).data( 'id' );
            $( this ).html("Converting...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_data = {
                'action': 'compressx_opt_single_image',
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
        optimize_image_edit:function()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            var id=$( this ).data( 'id' );
            $( this ).html("Converting...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_data = {
                'action': 'compressx_opt_single_image',
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
        optimize_image_attachment:function()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            var id=$( this ).data( 'id' );
            $( this ).html("Converting...");
            $( this ).removeClass('cx-media');
            w.compressx.media.lockbtn(true);

            var ajax_data = {
                'action': 'compressx_opt_single_image',
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
        optimize_timeout_image:function (page='media')
        {
            var ajax_data = {
                'action': 'compressx_opt_image',
            };
            compressx_post_request(ajax_data, function(data)
            {
                setTimeout(function ()
                {
                    w.compressx.media.get_progress(page);
                }, 1000);

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                setTimeout(function ()
                {
                    w.compressx.media.get_progress(page);
                }, 1000);
            });
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
                w.compressx.media.get_progress(page);
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
        },
        restore_image:function()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            w.compressx.media.lockbtn(true);
            var id=$( this ).data( 'id' );

            $( this ).addClass("button-disabled");

            var ajax_data = {
                'action': 'compressx_delete_single_image',
                'id':id
            };
            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.lockbtn(false);
                var jsonarray = jQuery.parseJSON(data);
                w.compressx.media.update(jsonarray);

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.lockbtn(false);
                var error_message = compressx_output_ajaxerror('restore image', textStatus, errorThrown);
                alert(error_message);
            });
        },
        restore_image_edit:function ()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            w.compressx.media.lockbtn(true);
            var id=$( this ).data( 'id' );

            $( this ).addClass("button-disabled");

            var ajax_data = {
                'action': 'compressx_delete_single_image',
                'id':id,
                'page':'edit'
            };
            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.lockbtn(false);
                var jsonarray = jQuery.parseJSON(data);
                w.compressx.media.update(jsonarray,'edit');

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.lockbtn(false);
                var error_message = compressx_output_ajaxerror('restore image', textStatus, errorThrown);
                alert(error_message);
            });
        },
        restore_image_attachment:function ()
        {
            if(w.compressx.media.islockbtn())
            {
                return ;
            }
            w.compressx.media.lockbtn(true);
            var id=$( this ).data( 'id' );

            $( this ).addClass("button-disabled");

            var ajax_data = {
                'action': 'compressx_delete_single_image',
                'id':id,
                'page':'attachment'
            };

            compressx_post_request(ajax_data, function(data)
            {
                w.compressx.media.lockbtn(false);
                var jsonarray = jQuery.parseJSON(data);
                w.compressx.media.update(jsonarray,'attachment');

            }, function(XMLHttpRequest, textStatus, errorThrown)
            {
                w.compressx.media.lockbtn(false);
                var error_message = compressx_output_ajaxerror('restore image attachment', textStatus, errorThrown);
                alert(error_message);
            });
        },
        get_attachment_progress:function ()
        {
            $(this).find('.cx-media-attachment').each(function()
            {
                var id=$(this).data( 'id' );
                alert(id);
            });

        }
    };
    w.compressx.media.init();
})(jQuery, window);