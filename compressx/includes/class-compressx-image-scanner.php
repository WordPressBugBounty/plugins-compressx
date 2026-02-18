<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Scanner
{
    private static $has_sellvia = null;

    public static function check_sellvia_environment()
    {
        if ( self::$has_sellvia === null )
        {
            if ( !function_exists('get_plugins') )
            {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugin_path = 'sellvia-platform/sellvia-platform.php';

            $installed_plugins = get_plugins();
            $is_installed = isset( $installed_plugins[ $plugin_path ] );
            if($is_installed)
            {
                self::$has_sellvia=true;
            }
            else
            {
                self::$has_sellvia=false;
            }
        }
        return self::$has_sellvia;
    }

    public static function scan_all_unoptimized_images()
    {
        global $wpdb;

        $convert_to_webp = CompressX_Image_Method::get_convert_to_webp();
        $convert_to_avif = CompressX_Image_Method::get_convert_to_avif();

        $page = 500;
        $last_id = 0;
        $max_image_id = (int) $wpdb->get_var("
        SELECT MAX(ID)
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'");

        while (true)
        {
            $result = self::scan_unoptimized_image_by_cursor(
                $page,
                $last_id,
                $convert_to_webp,
                $convert_to_avif,
                false
            );

            if (empty($result['last_id']))
            {
                break;
            }

            $last_id = (int) $result['last_id'];
            if ($last_id >= $max_image_id)
            {
                break;
            }
        }

        $need_optimize_images = self::get_need_optimize_images_count(false);

        global $wpdb;
        $total_attachments   = self::get_total_attachments_cached();

        $scanned_attachments = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type='attachment' AND ID <= %d",
                $last_id
            )
        );

        $progress_percent = $total_attachments > 0
            ? min(100, round(($scanned_attachments / $total_attachments) * 100, 2))
            : 100;

        $ret = array(
            'result'   => 'success',
            'progress' => sprintf('Full scan completed: %1$d images found, scanned up to ID %2$d of %3$d (%4$s%%)',
                $need_optimize_images,
                $scanned_attachments,
                $total_attachments,
                $progress_percent
            ),
        );

        return $ret;
    }

    public static function scan_unoptimized_images($force,$start_row)
    {
        $convert_to_webp=CompressX_Image_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Method::get_convert_to_avif();

        //$exclude_regex_folder=CompressX_Pro_Options::get_excludes();

        $time_start=time();
        $max_timeout_limit=21;
        $finished=true;
        $page=500;
        $max_count=10000;

        $count = 0;
        $last_id = (int) $start_row;
        $processed = 0;

        while (true)
        {
            $result = self::scan_unoptimized_image_by_cursor($page, $last_id, $convert_to_webp, $convert_to_avif, $force);

            if (empty($result['last_id'])) {
                $finished = true;
                break;
            }

            $last_id = $result['last_id'];
            $processed += $result['count'];

            $time_spend = time() - $time_start;
            if ($time_spend > $max_timeout_limit || $processed > $max_count) {
                $finished = false;
                break;
            }
        }

        $need_optimize_images = self::get_need_optimize_images_count($force);

        $ret['result']   = 'success';
        $ret['finished'] = $finished;
        $ret['offset']   = $last_id;
        //$max_image_count=CompressX_Image_Method::get_max_image_count();
        global $wpdb;
        $max_image_id = (int) $wpdb->get_var("
    SELECT MAX(ID) FROM {$wpdb->posts} WHERE post_type='attachment'
");
        $progress = min(100, round(($last_id / $max_image_id) * 100, 2));
        $ret['progress'] = sprintf('Scanning up to ID %1$d of %2$d, Found %3$d (%4$s%%)',
            $last_id, $max_image_id, $need_optimize_images, $progress
        );

        return $ret;
    }

    public static function scan_unoptimized_images_v2($force, $start_row)
    {
        $convert_to_webp=CompressX_Image_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Method::get_convert_to_avif();

        $time_start=time();
        $max_timeout_limit=21;
        $finished=true;
        $page=500;
        $max_count=10000;

        $count = 0;
        $last_id = (int) $start_row;
        $processed = 0;

        while (true)
        {
            $result = self::scan_unoptimized_image_by_cursor($page, $last_id, $convert_to_webp, $convert_to_avif, $force);

            if (empty($result['last_id'])) {
                $finished = true;
                break;
            }

            $last_id = $result['last_id'];
            $processed += $result['count'];

            $time_spend = time() - $time_start;
            if ($time_spend > $max_timeout_limit || $processed > $max_count) {
                $finished = false;
                break;
            }
        }

        // percent
        global $wpdb;
        $total_attachments   = self::get_total_attachments_cached();

        $scanned_attachments = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type='attachment' AND ID <= %d",
                $last_id
            )
        );

        $percent = $total_attachments > 0
            ? min(100, round(($scanned_attachments / $total_attachments) * 100, 2))
            : 100;

        // need optimize images
        $need_optimize_images = self::get_need_optimize_images_count($force);

        $progress_text = sprintf(
            '%1$d / %2$d scanned',
            $scanned_attachments,
            $total_attachments
        );

        $ret = [
            'result'           => 'success',
            'finished'         => $finished,
            'offset'           => $last_id,
            'found'            => $need_optimize_images,
            'progress_text'    => $progress_text,
            'progress_percent' => $percent
        ];

        return $ret;
    }

    private static function get_total_attachments_cached()
    {
        global $wpdb;
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type='attachment'"
        );

        return $total;
    }

    public static function scan_unoptimized_image_by_cursor($limit, $last_id, $convert_to_webp, $convert_to_avif, $force)
    {
        if (!$convert_to_webp && !$convert_to_avif)
        {
            return ['count' => 0, 'last_id' => 0];
        }

        global $wpdb;

        $mime_types = ["image/jpg", "image/jpeg", "image/png", "image/webp", "image/avif"];
        $placeholders = implode(',', array_fill(0, count($mime_types), '%s'));

        $subquery = "
        SELECT ID,post_mime_type
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
          AND post_mime_type IN ($placeholders)
          AND ID > %d
        ORDER BY ID ASC
        LIMIT %d
    ";
        $args = array_merge($mime_types, [$last_id, $limit]);

        if ($force) {
            $status_filter = "1=1";
        } else {
            $status_filter = "(pm.status IS NULL OR pm.status NOT IN ('pending', 'skip'))";
        }

        $meta_table=CompressX_Image_Meta_V2::table_name();
        $outer_query = "
        SELECT p.ID,p.post_mime_type,pm.attachment_id
        FROM ($subquery) p
        LEFT JOIN {$meta_table} pm
          ON p.ID = pm.attachment_id
        WHERE $status_filter
    ";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $query = $wpdb->prepare($outer_query, $args);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->get_results($query, OBJECT_K);

        if (empty($result)) {
            return ['count' => 0, 'last_id' => 0];
        }

        $processed = 0;
        $max_id = 0;

        foreach ($result as $image)
        {
            $image_id = (int) $image->ID;
            $max_id = max($max_id, $image_id);
            $processed++;

            $meta=CompressX_Image_Meta_V2::get_image_meta($image_id);

            if (self::should_skip($image_id))
            {
                CompressX_Image_Meta_V2::update_image_meta_status($image_id, 'skip');
                continue;
            }

            if(isset($meta['status'])&&$meta['status']=='optimized')
            {
                continue;
            }

            $type=$image->post_mime_type;
            $file_path = get_post_meta($image_id, '_wp_attached_file', true);
            if (empty($file_path))
            {
                continue;
            }

            $need_opt = false;

            if ($convert_to_webp)
            {
                if ($type != 'image/avif')
                {
                    if( !isset($meta['webp_converted']) ||$meta['webp_converted']==0)
                    {
                        $need_opt = true;
                    }
                }
            }

            if ($convert_to_avif)
            {
                if( !isset($meta['avif_converted']) ||$meta['avif_converted']==0)
                {
                    $need_opt = true;
                }
            }

            if ($need_opt)
            {
                CompressX_Image_Meta_V2::update_image_meta_status($image_id, 'pending');
            }
        }

        return [
            'count' => $processed,
            'last_id' => $max_id,
        ];
    }

    public static function should_skip($image_id)
    {
        if ( ! self::check_sellvia_environment() )
        {
            return false;
        }

        $is_sellvia = get_post_meta($image_id, '_wp_sellvia_attached_file', true);
        if ($is_sellvia === '1')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function get_need_optimize_images_count($force)
    {
        global $wpdb;

        $meta_table = CompressX_Image_Meta_V2::table_name();

        if ($force) {
            $statuses = ['pending', 'optimized', 'failed'];
        } else {
            $statuses = ['pending', 'failed'];
        }

        $placeholders = implode(',', array_fill(0, count($statuses), '%s'));

        return (int) $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(attachment_id)
        FROM {$meta_table}
        WHERE status IN ($placeholders)
    ", $statuses));
    }

    public static function get_need_optimize_images_by_cursor($last_id = 0, $limit = 200, $force = false)
    {
        global $wpdb;

        $meta_table=CompressX_Image_Meta_V2::table_name();

        if ($force)
        {
            return $wpdb->get_col($wpdb->prepare("
        SELECT pm.attachment_id 
        FROM {$meta_table} pm
        WHERE pm.status IN ('pending', 'optimized', 'failed')
          AND pm.attachment_id  > %d
        ORDER BY pm.attachment_id  ASC
        LIMIT %d
    ",  $last_id, $limit));
        }
        else
        {
            return $wpdb->get_col($wpdb->prepare("
        SELECT pm.attachment_id 
        FROM {$meta_table} pm
        WHERE pm.status IN ('pending', 'failed')
          AND pm.attachment_id  > %d
        ORDER BY pm.attachment_id  ASC
        LIMIT %d
    ",  $last_id, $limit));
        }
    }
}
