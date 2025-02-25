<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Log
{
    public $log_file;
    public $log_file_handle;

    public function __construct()
    {
        $this->log_file_handle=false;
    }

    public function CreateLogFile($file_name='')
    {
        if(empty($file_name))
        {
            $offset=get_option('gmt_offset');
            $localtime = time() + $offset * 60 * 60;
            $file_name='compressx_'.gmdate('Ymd',$localtime).'_log.txt';
        }

        $this->log_file=$this->GetSaveLogFolder().$file_name;

        $this->log_file_handle = fopen($this->log_file, 'a');
        if($this->log_file_handle===false)
        {
            return false;
        }
        $text="====================================================\n";
        $time =gmdate("Y-m-d H:i:s",time());
        $text.='open log file: '.$time."\n";
        fwrite($this->log_file_handle,$text);
        return $this->log_file;
    }

    public function OpenLogFile($file_name='')
    {
        if(empty($file_name))
        {
            $offset=get_option('gmt_offset');
            $localtime = time() + $offset * 60 * 60;
            $file_name='compressx_'.gmdate('Ymd',$localtime).'_log.txt';
        }

        $this->log_file=$this->GetSaveLogFolder().$file_name;

        $this->log_file_handle = fopen($this->log_file, 'a');

        return $this->log_file;
    }

    public function WriteLog($log,$type)
    {
        if ($this->log_file_handle)
        {
            $time =gmdate("Y-m-d H:i:s",time());
            $text='['.$time.']'.'['.$type.']'.$log."\n";
            fwrite($this->log_file_handle,$text );
        }
    }

    public function GetlastLog()
    {
        if(empty($file_name))
        {
            $offset=get_option('gmt_offset');
            $localtime = time() + $offset * 60 * 60;
            $file_name='compressx_'.gmdate('Ymd',$localtime).'_log.txt';
        }

        $this->log_file=$this->GetSaveLogFolder().$file_name;
        $file = file($this->log_file);
        $text='';
        for ($i = max(0, count($file)-1); $i < count($file); $i++)
        {
            $text.= $file[$i] . "\n";
        }
        return $text;
    }

    public function CloseFile()
    {
        if ($this->log_file_handle)
        {
            fclose($this->log_file_handle);
            $this->log_file_handle=false;
        }
    }

    public function GetSaveLogFolder()
    {
        $path=WP_CONTENT_DIR.'/compressx/'.'log';

        if(!is_dir($path))
        {
            @mkdir($path,0777,true);
            @fopen($path.DIRECTORY_SEPARATOR.'index.html', 'x');
            $tempfile=@fopen($path.DIRECTORY_SEPARATOR.'.htaccess', 'x');
            if($tempfile)
            {
                $text="deny from all";
                fwrite($tempfile,$text );
            }
        }

        return $path.'/';
    }

    public function get_logs()
    {
        $dir=$this->GetSaveLogFolder();

        $files = scandir($dir, SCANDIR_SORT_DESCENDING);

        $log_files=array();

        $regex='#^compressx.*_log.txt#';
        foreach ($files as $filename)
        {
            if(preg_match($regex,$filename))
            {
                $log_files[] = $dir.DIRECTORY_SEPARATOR.$filename;
            }

            if(sizeof($log_files)>=5)
            {
                break;
            }
        }

        return $log_files;
    }
}

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CompressX_Log_List extends WP_List_Table
{
    public $page_num;
    public $log_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'log',
                'screen' => 'log'
            )
        );
    }



    public function get_columns()
    {
        $columns = array();
        $columns['cx_date'] = __('Data and Time','compressx');
        $columns['cx_log_file_name'] =__( 'Log File Name', 'compressx'  );
        $columns['cx_size'] = __( 'Log Size', 'compressx'  );
        $columns['cx_log_actions'] = __( 'Actions', 'compressx'  );


        return $columns;
    }

    public function set_log_list($log_list, $page_num=1)
    {
        $this->log_list=$log_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->log_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 30,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->log_list);
    }

    public function _column_cx_date( $log )
    {
        echo '<td class="tablelistcolumn">'.esc_html($log['date']).'</td>';
    }

    public function _column_cx_log_file_name( $log )
    {
        echo '<td class="tablelistcolumn"><span>'.esc_html($log['file_name']).'</span></td>';
    }

    public function _column_cx_size( $log )
    {
        echo '<td class="tablelistcolumn"><span>'.esc_html(size_format($log['size'],2)).'</span></td>';
    }

    public function _column_cx_log_actions( $log )
    {
        ?>
        <td class="manage-column" data-id="<?php echo esc_attr($log['file_name'])?>">
            <span><a style="cursor: pointer" class="cs-log-detail"><?php esc_html_e('Details','compressx')?></a></span><span> | </span>
            <span><a style="cursor: pointer" class="cs-log-download"><?php esc_html_e('Download','compressx')?></a></span><span> | </span>
            <span><a style="cursor: pointer" class="cs-log-delete"><?php esc_html_e('Delete','compressx')?></a></span>
        </td>
        <?php
    }

    public function display_rows()
    {
        $this->_display_rows( $this->log_list );
    }

    private function _display_rows($log_list)
    {
        $page=$this->get_pagenum();

        $page_log_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_log_list = array_splice( $log_list, 0, 30);
            $count++;
        }
        foreach ( $page_log_list as $log)
        {
            $this->single_row($log);
        }
    }

    public function single_row($log)
    {
        ?>
        <tr style="display: table-row;">
            <?php $this->single_row_columns( $log ); ?>
        </tr>
        <?php
    }

    protected function display_tablenav( $which )
    {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'padding:0 0 1em 0;';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'display: none;';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php echo esc_attr($css_type); ?>">
                <div class="alignleft actions bulkactions">
                    <span><input type="date" id="cx_log_start_date"></span>
                    <span><input type="time" id="cx_log_start_time"></span>
                    <span>to</span>
                    <span><input type="date" id="cx_log_end_date"></span>
                    <span><input type="time" id="cx_log_end_time"></span>
                    <span><input type="submit" id="cx_log_search_by_date" class="button action" value="Apply"></span>
                </div>
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages <= 1)
        {
            ?>
            <div class="tablenav top" style="padding:0 0 1em 0;">
                <div class="alignleft actions bulkactions">
                    <span><input type="date" id="cx_log_start_date"></span>
                    <span><input type="time" id="cx_log_start_time"></span>
                    <span>to</span>
                    <span><input type="date" id="cx_log_end_date"></span>
                    <span><input type="time" id="cx_log_end_time"></span>
                    <span><input type="submit" id="cx_log_search_by_date" class="button action" value="Apply"></span>
                </div>
            </div>
            <?php
        }

        ?>
        <table class="wp-list-table <?php echo esc_attr(implode( ' ', $this->get_table_classes() )); ?>">
            <thead>
            <tr class="cx-table-header">
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list">
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr class="cx-table-footer">
                <?php $this->print_column_headers(); ?>
            </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $current_url = remove_query_arg( 'paged', $current_url );

        // When users click on a column header to sort by other columns.
        if ( isset( $_GET['orderby'] ) ) {
            $current_orderby = $_GET['orderby'];
            // In the initial view there's no orderby parameter.
        } else {
            $current_orderby = '';
        }

        // Not in the initial view and descending order.
        if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
            $current_order = 'desc';
        } else {
            // The initial view is not always 'asc', we'll take care of this below.
            $current_order = 'asc';
        }

        if ( ! empty( $columns['cb'] ) ) {
            static $cb_counter = 1;
            $columns['cb']     = '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />
			<label for="cb-select-all-' . $cb_counter . '">' .
                '<span class="screen-reader-text">' .
                /* translators: Hidden accessibility text. */
                __( 'Select All' ) .
                '</span>' .
                '</label>';
            ++$cb_counter;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class          = array( 'manage-column', "column-$column_key" );
            $aria_sort_attr = '';
            $abbr_attr      = '';
            $order_text     = '';

            if ( in_array( $column_key, $hidden, true ) ) {
                $class[] = 'hidden';
            }

            if ( 'cb' === $column_key ) {
                $class[] = 'check-column';
            } elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
                $class[] = 'num';
            }

            if ( isset( $sortable[ $column_key ] ) ) {
                $orderby       = isset( $sortable[ $column_key ][0] ) ? $sortable[ $column_key ][0] : '';
                $desc_first    = isset( $sortable[ $column_key ][1] ) ? $sortable[ $column_key ][1] : false;
                $abbr          = isset( $sortable[ $column_key ][2] ) ? $sortable[ $column_key ][2] : '';
                $orderby_text  = isset( $sortable[ $column_key ][3] ) ? $sortable[ $column_key ][3] : '';
                $initial_order = isset( $sortable[ $column_key ][4] ) ? $sortable[ $column_key ][4] : '';

                /*
                 * We're in the initial view and there's no $_GET['orderby'] then check if the
                 * initial sorting information is set in the sortable columns and use that.
                 */
                if ( '' === $current_orderby && $initial_order ) {
                    // Use the initially sorted column $orderby as current orderby.
                    $current_orderby = $orderby;
                    // Use the initially sorted column asc/desc order as initial order.
                    $current_order = $initial_order;
                }

                /*
                 * True in the initial view when an initial orderby is set via get_sortable_columns()
                 * and true in the sorted views when the actual $_GET['orderby'] is equal to $orderby.
                 */
                if ( $current_orderby === $orderby ) {
                    // The sorted column. The `aria-sort` attribute must be set only on the sorted column.
                    if ( 'asc' === $current_order ) {
                        $order          = 'desc';
                        $aria_sort_attr = ' aria-sort="ascending"';
                    } else {
                        $order          = 'asc';
                        $aria_sort_attr = ' aria-sort="descending"';
                    }

                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    // The other sortable columns.
                    $order = strtolower( $desc_first );

                    if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
                        $order = $desc_first ? 'desc' : 'asc';
                    }

                    $class[] = 'sortable';
                    $class[] = 'desc' === $order ? 'asc' : 'desc';

                    /* translators: Hidden accessibility text. */
                    $asc_text = __( 'Sort ascending.' );
                    /* translators: Hidden accessibility text. */
                    $desc_text  = __( 'Sort descending.' );
                    $order_text = 'asc' === $order ? $asc_text : $desc_text;
                }

                if ( '' !== $order_text ) {
                    $order_text = ' <span class="screen-reader-text">' . $order_text . '</span>';
                }

                // Print an 'abbr' attribute if a value is provided via get_sortable_columns().
                $abbr_attr = $abbr ? ' abbr="' . esc_attr( $abbr ) . '"' : '';

                $column_display_name = sprintf(
                    '<a href="%1$s">' .
                    '<span>%2$s</span>' .
                    '<span class="sorting-indicators">' .
                    '<span class="sorting-indicator asc" aria-hidden="true"></span>' .
                    '<span class="sorting-indicator desc" aria-hidden="true"></span>' .
                    '</span>' .
                    '%3$s' .
                    '</a>',
                    esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
                    $column_display_name,
                    $order_text
                );
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) ) {
                $class = "class='" . implode( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class $aria_sort_attr $abbr_attr>$column_display_name</$tag>";
        }
    }

    protected function get_table_classes() {
        $mode = get_user_setting( 'posts_list_mode', 'list' );

        $mode_class = esc_attr( 'table-view-' . $mode );

        return array( 'cx-table','widefat', 'striped', $this->_args['plural'] );
    }
}