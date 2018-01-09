<?php namespace Solarise\WooCommerceReorderProductPage;
/*
 * Plugin Name: Modify Product Page Layout for WooCommerce
 * Plugin URI: http://solarisedesign.co.uk/code-blog/woocommerce-plugin-for-modifying-product-page-layout
 * Author: Robin Metcalfe <robin@solarisedesign.co.uk>
 * Description: Makes it easier to adjust the ordering of the elements on a WooCommerce product page without needing to add extra code.
 * Version 1.0.0
 * Text Domain: woocommerce-modify-product-page-layout
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class Main {

    private $text_domain = 'woocommerce-modify-product-page-layout';
    private $version = '1.0.0';

    private $hooks = array(     
        'woocommerce_before_single_product_summary' => array(
            'woocommerce_show_product_sale_flash' => array(
                'title' => 'Sale flash text',
                'priority' => 10
            ),
            'woocommerce_show_product_images' => array(
                'title' => 'Image(s)',
                'priority' => 20
            )
        ),
        'woocommerce_single_product_summary' => array(
            'woocommerce_template_single_title' => array(
                'title' => 'Title',
                'priority' => 5
            ),
            'woocommerce_template_single_rating' => array(
                'title' => 'Rating',
                'priority' => 10
            ),
            'woocommerce_template_single_price' => array(
                'title' => 'Price',
                'priority' => 10
            ),
            'woocommerce_template_single_excerpt' => array(
                'title' => 'Excerpt',
                'priority' => 20
            ),
            'woocommerce_template_single_add_to_cart' => array(
                'title' => 'Add To Cart',
                'priority' => 30
            ),
            'woocommerce_template_single_meta' => array(
                'title' => 'Metadata/Attributes',
                'priority' => 40
            ),
            'woocommerce_template_single_sharing' => array(
                'title' => 'Sharing options',
                'priority' => 50
            )
        ),
        'woocommerce_after_single_product_summary' => array(
            'woocommerce_output_product_data_tabs' => array(
                'title' => 'Data tabs',
                'priority' => 10
            ),
            'woocommerce_upsell_display' => array(
                'title' => 'Upsell products',
                'priority' => 15
            ),
            'woocommerce_output_related_products' => array(
                'title' => 'Related products',
                'priority' => 20
            )
        ),
        'do_not_display' => array()
    );

    function __construct()
    {
        // run on WP's init
        add_action('init', array($this, 'reorder_page'));

        // add the jquery sortable plugin
        add_action('admin_enqueue_scripts', array($this, 'scripts'));

        // add a new WooCommerce admin field for the drag and drop functionality
        add_action('woocommerce_admin_field_reorder_input', array($this, 'add_field_type'), 10, 1);

        // register the ajax action callbacks for our drag and drop areas
        add_action( 'wp_ajax_reorder_action_dragdrop', array($this, 'ajax_handler'));
        // no non-private method access for this!
        //add_action( 'wp_ajax_nopriv_reorder_action_dragdrop', array($this, 'ajax_handler'));

        // Create a new section within WooCommerce > Settings > Products
        add_filter('woocommerce_get_sections_products', array($this, 'add_section'));
        add_filter('woocommerce_get_settings_products', array($this, 'get_settings'), 10, 2);
    }

    /**
     * Create a custom field type to handle the drag/drop interface
     * @param array $value
     */
    public function add_field_type($value) {
        $hooks = array();
        $options = \get_option('woocommerce_modify_product_page_layout');
        if(!$options) {
            $options = $this->hooks;
        }
        echo "<div id='woocommerce_modify_product_page_layout_heading'>";
        echo "<span class='status'></span>\n";
        echo "</div>";
        echo "<div id='woocommerce_modify_product_page_layout_lists'>";
        foreach($this->hooks as $hook => $actions) {
            echo "<div class='list'>";
            if($hook == 'woocommerce_before_single_product_summary') {
                $hook_title = 'Before summary';
            } else if($hook == 'woocommerce_after_single_product_summary') {
                $hook_title = 'After summary';
            } else if($hook == 'woocommerce_single_product_summary') {
                $hook_title = 'Summary';
            } else if($hook == 'do_not_display') {
                $hook_title = 'Do not display';
            }
            echo "<h3>{$hook_title}</h3>\n";
            echo "<ul class='sortable' data-hook='{$hook}'>\n";
            $hooks[] = '#'.$hook;
            // Need to do slightly alternate process depending on if
            // a) $options aren't yet set, and reading from more detailed $this->hooks array, or
            // b) $options is set, so read through simpler array and grab title from $this->hooks
            if($options) {
                if(isset($options[$hook])) {
                    $actions = $options[$hook];
                } else {
                    $actions = false;
                }
                if(!$actions) {
                    $actions = array();
                }
                foreach($actions as $action => $priority) {
                    $data = $this->get_action_data($action);
                    echo "\t<li class='product_action' data-action='{$action}' data-priority='{$data['priority']}'>{$data['title']}</li>\n";
                }
            } else {
                foreach($actions as $action => $data) {
                    echo "\t<li class='product_action' data-action='{$action}' data-priority='{$data['priority']}'>{$data['title']}</li>\n";
                }
            }
            echo "</ul>";
            echo "</div>";
        }
        echo "</div>";
        ?>
<script>
jQuery(function($){
    function get_data() {
        var data = {};
        $('#woocommerce_modify_product_page_layout_lists ul').each(function(){
            var hook = $(this).data('hook');
            data[hook] = {};
            $('li', $(this)).each(function(){
                var action = $(this).data('action');
                data[hook][action] = $(this).index();
            });
        });
        return data;
    }
    var $el = $(<?php echo "'".implode(',', $hooks)."'"; ?>);
    //$('#woocommerce_modify_product_page_layout_heading .reset').on('click', function(){
        // revert to WooCommerce defaults
    //});
    var $sortable = $('#woocommerce_modify_product_page_layout_lists .sortable');
    $sortable.sortable({
        revert: 50,
        connectWith: ".sortable"
    });
    var $status = $('#woocommerce_modify_product_page_layout_heading .status');
    $sortable.on('sortupdate', function(e, ui){
        if (this === ui.item.parent()[0]) {
            var data = get_data();
            $status.show(0);
            $status.html('Please wait...');
            $.post(
                ajaxurl,
                {
                    action: 'reorder_action_dragdrop',
                    data: data
                },
                function(response) {
                    if(response.update == 1) {
                        var status = 'Updated';
                        $status.css('background-color', '#8F8');
                    } else {
                        var status = 'Could not update';
                        $status.css('background-color', '#F88');
                        $sortable.sortable('cancel');
                    }
                    $status.animate({backgroundColor: '#CCDDEE'}, 1000);
                    $status.html(status);
                },
                'json'
            )
        }
    });
});
</script>
        <?php
    }

    /**
     * Create a new section in the WooCommerce settings (under 'products')
     * @param array $sections
     */
    public function add_section($sections)
    {
        $sections[$this->text_domain] = __('Modify Layout', $this->text_domain);
        return $sections;
    }

    public function ajax_handler()
    {
        if(!current_user_can('manage_options')) {
            echo json_encode(array('update' => $update?1:0));
        }
        $update = false;
        if(isset($_POST['data'])) {
            $update = \update_option('woocommerce_modify_product_page_layout', $_POST['data']);
        }
        echo json_encode(array('update' => $update?1:0));
        exit;
    }

    /**
     * Because the $this->hooks array and the format of the data stored in the db
     * for $options are different (since we don't need e.g. 'title' stored with
     * $options) this is a helper function to extract the data from the $this->hooks
     * array
     * @param string $action    The specific action to extract data for
     * @return array
     */
    private function get_action_data($action)
    {
        // Find the data for this action from original $this->hooks array
        foreach($this->hooks as $_hook => $_actions) {
            // not optimal
            foreach($_actions as $_action => $_data) {
                if($_action == $action) {
                    return $_data;
                }
            }
        }
    }

    /**
     * Get the settings array to add the appropriate UI to the 
     * WooCommerce products setting tab
     * @param  array $settings
     * @param  string $current_section
     * @return array
     */
    public function get_settings($settings, $current_section)
    {
        if($current_section == $this->text_domain) {
            $settings = array();
            $settings[] = array(
                'name'     => __( 'Reorder Product Page', $this->text_domain),
                'desc_tip' => __( 'Reorder various parts of the product listings and archive pages', $this->text_domain),
                'id'       => 'woocommerce_reorder_input',
                'type'     => 'reorder_input',
                'css'      => 'min-width:300px;',
                'std'      => '1',  // WC < 2.0
                'default'  => '1',  // WC >= 2.0
                'desc'     => __( 'Reorder various bits and pieces', $this->text_domain),
              );
            return $settings;
        } else {
            return $settings;
        }
    }

    /**
     * The main process function, hooks into Wordpress' init action
     * @return void
     */
    public function reorder_page()
    {
        $options = \get_option('woocommerce_modify_product_page_layout');
        if($options) {
            // remove all default hooks
            foreach($this->hooks as $hook => $actions) {            
                foreach($actions as $action => $data) {
                    remove_action($hook, $action, $data['priority']);
                }
            }
            // re-add using values from drag-and-drop in admin
            foreach($options as $hook => $actions) {
                if($hook == 'do_not_display') {
                    continue;
                }
                foreach($actions as $action => $priority) {
                    add_action($hook, $action, $priority);
                }
            }
        }
        
    }

    /**
     * Enqueue the stylesheets. No external javascript, it's inlined above
     */
    public function scripts($hook)
    {
        if($hook != 'woocommerce_page_wc-settings') {
            return;
        }
        wp_register_style('reorder_product_page_css', plugin_dir_url( __FILE__ ) . '/style.css', false, $this->version);
        wp_enqueue_style('reorder_product_page_css');
    }

}

// Initialise only if WooCommerce is loaded
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
    new Main;
}