<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_default_folder
{
    public function __construct()
    {

    }

    public function create_uploads_dir()
    {
        $root_path= WP_CONTENT_DIR."/compressx-nextgen";

        if(!is_dir($root_path))
        {
            @mkdir($root_path);
        }

        $uploads_folder=WP_CONTENT_DIR."/compressx-nextgen/uploads";

        if(!is_dir($uploads_folder))
        {
            @mkdir($uploads_folder);
        }

        $this->create_htaccess();
    }

    public function create_htaccess()
    {
        $htaccess_file= WP_CONTENT_DIR."/compressx-nextgen/.htaccess";
        $line[]='<IfModule mod_mime.c>';
        $line[]='AddType image/avif .avif';
        $line[]='AddType image/webp .webp';
        $line[]='</IfModule>';

        $line[]='<IfModule mod_expires.c>';
        $line[]='ExpiresActive On';
        $line[]='ExpiresByType image/avif "access plus 1 year"';
        $line[]='ExpiresByType image/webp "access plus 1 year"';
        $line[]='</IfModule>';
        $line[]='Options -Indexes';
        insert_with_markers($htaccess_file,'CompressX Webp',$line);
    }
}