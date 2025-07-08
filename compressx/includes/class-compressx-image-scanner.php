<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Scanner
{
    public static function scan_unoptimized_images($force,$start_row)
    {
        $max_image_count=CompressX_Image_Method::get_max_image_count();

        $convert_to_webp=CompressX_Image_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Method::get_convert_to_avif();

        //$exclude_regex_folder=CompressX_Options::get_excludes();

        $time_start=time();
        $max_timeout_limit=21;
        $finished=true;
        $page=300;
        $max_count=5000;

        $count=0;
        for ($offset=$start_row; $offset <= $max_image_count; $offset += $page)
        {
            CompressX_Image_Scanner::scan_unoptimized_image($page,$offset,$convert_to_webp,$convert_to_avif);

            $count=$count+$page;
            $time_spend=time()-$time_start;
            if($time_spend>$max_timeout_limit)
            {
                $offset+=$page;
                $finished=false;
                break;
            }
            else if($count>$max_count)
            {
                $offset+=$page;
                $finished=false;
                break;
            }
        }

        $need_optimize_images=CompressX_Image_Scanner::get_need_optimize_images_count($force);
        $ret['result']='success';
        $ret['finished']=$finished;
        $ret['offset']=$offset;
        $ret['progress']=sprintf(
        /* translators: %1$d: Scanning images*/
            __('Scanning images: %1$d found' ,'compressx'),
            $need_optimize_images);

        return $ret;
    }

    public static function scan_unoptimized_image($limit,$offset,$convert_to_webp,$convert_to_avif)
    {
        if(!$convert_to_webp&&!$convert_to_avif)
        {
            return;
        }

        global $wpdb;

        $mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif"
        );

        $placeholders = implode(',', array_fill(0, count($mime_types), '%s'));

        if ($limit <= 0) {
            $limit = 300; //
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $subquery = "
        SELECT ID
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
          AND post_mime_type IN ($placeholders)
        ORDER BY ID ASC
        LIMIT %d OFFSET %d
    ";
        $args = array_merge($mime_types, [$limit, $offset]);

        $outer_query = "
        SELECT p.ID
        FROM ($subquery) p
        LEFT JOIN {$wpdb->postmeta} pm 
          ON p.ID = pm.post_id AND pm.meta_key = 'compressx_image_meta_status'
        WHERE (pm.meta_value IS NULL OR pm.meta_value != 'pending')
    ";

        $query = $wpdb->prepare($outer_query, $args);
        $result = $wpdb->get_results($query, OBJECT_K);

        if(!empty($result))
        {
            foreach ( $result as $image )
            {
                wp_cache_delete( $image->ID, 'post_meta' );

                if(CompressX_Image_Meta::is_image_optimized($image->ID))
                {
                    continue;
                }
                else
                {
                    $need_opt=false;
                    $file_path = get_attached_file($image->ID);
                    $type=pathinfo($file_path, PATHINFO_EXTENSION);

                    if($convert_to_webp)
                    {
                        if($type!='webp'&&$type!='avif')
                        {
                            if(!CompressX_Image_Meta::get_image_meta_webp_converted($image->ID))
                            {
                                $need_opt=true;
                            }
                        }
                        else if($type=='webp')
                        {
                            if(!CompressX_Image_Meta::get_image_meta_compressed($image->ID))
                            {
                                $need_opt=true;
                            }
                        }
                    }

                    if($convert_to_avif)
                    {
                        if($type!='avif')
                        {
                            if(!CompressX_Image_Meta::get_image_meta_avif_converted($image->ID))
                            {
                                $need_opt=true;
                            }
                        }
                        else
                        {
                            if(!CompressX_Image_Meta::get_image_meta_compressed($image->ID))
                            {
                                $need_opt=true;
                            }
                        }
                    }

                    if($need_opt)
                    {
                        CompressX_Image_Meta::update_image_meta_status($image->ID,'pending');
                    }
                }
            }
        }
    }

    public static function get_need_optimize_images_count($force)
    {
        global $wpdb;

        $meta_key = 'compressx_image_meta_status';

        if ( $force ) {
            $query = $wpdb->prepare("
            SELECT COUNT(DISTINCT post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
              AND meta_value IN ('pending', 'optimized')
        ", $meta_key);
        } else {
            $query = $wpdb->prepare("
            SELECT COUNT(DISTINCT post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
              AND meta_value = 'pending'
        ", $meta_key);
        }

        return (int) $wpdb->get_var($query);
    }

    public static function get_need_optimize_images($max_image_count = 100, $offset = 0, $force = false)
    {
        global $wpdb;

        $meta_key = 'compressx_image_meta_status';

        if ( $force ) {
            $status_condition = "meta_value IN ('pending', 'optimized')";
        } else {
            $status_condition = "meta_value = 'pending'";
        }

        $query = $wpdb->prepare("
        SELECT DISTINCT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
          AND $status_condition
        LIMIT %d OFFSET %d
    ", $meta_key, $max_image_count, $offset);

        return $wpdb->get_col($query);
    }

    public static function get_need_optimize_images_by_cursor($last_id = 0, $limit = 200, $force = false)
    {
        global $wpdb;

        $meta_key = 'compressx_image_meta_status';

        $status_condition = $force
            ? "pm.meta_value IN ('pending', 'optimized')"
            : "pm.meta_value = 'pending'";

        $query = $wpdb->prepare("
        SELECT DISTINCT pm.post_id
        FROM {$wpdb->postmeta} pm
        WHERE pm.meta_key = %s
          AND $status_condition
          AND pm.post_id > %d
        ORDER BY pm.post_id ASC
        LIMIT %d
    ", $meta_key, $last_id, $limit);

        return $wpdb->get_col($query);
    }
}