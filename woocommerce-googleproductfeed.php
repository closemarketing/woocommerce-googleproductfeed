<?php
/*
Plugin Name: WooCommerce Google Product Feed
Plugin URI: http://www.closemarketing.es/portafolio/plugin-woocommerce-googleproductfeed/
Description: Creates a Feed for Google Merchant

Version: 0.2
Requires at least: 3.0

Author: Closemarketing
Author URI: http://www.closemarketing.es/

Text Domain: wc_gfeed
Domain Path: /languages/

License: GPL
*/

class WCGoogleProductFeed {
    
    public function __construct()
    {  
        //Localization
        add_action('init', array($this, 'load_plugin_textdomain'));
        //First XML File
        add_action('init', array($this, 'generate_xml_product_file'));
        
        //Cron
        add_action( 'wp',  array($this,'prefix_setup_schedule') );
        add_action( 'prefix_hourly_event', array($this,'prefix_do_this_hourly') );
        
        //Taxonomy
        add_action( 'product_cat_add_form_fields', array($this, 'wc_gfeed_taxonomy_add_new_meta_field'), 10, 2 );
        add_action( 'product_cat_edit_form_fields', array($this, 'wc_gfeed_taxonomy_edit_meta_field'), 10, 2 );
        
        add_action( 'edited_product_cat', array($this, 'save_taxonomy_custom_meta'), 10, 2 );  
        add_action( 'create_product_cat', array($this, 'save_taxonomy_custom_meta'), 10, 2 );
    }
    
    /**
     * On an early action hook, check if the hook is scheduled - if not, schedule it.
     */
    public function prefix_setup_schedule() 
    {
        if ( ! wp_next_scheduled( 'prefix_hourly_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'prefix_hourly_event');
        }
    }
    /**
     * On the scheduled action hook, run a function.
     */
    public function prefix_do_this_hourly() 
    {
        $this->generate_xml_product_file();
    }
    
    /*
     * localization
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain('wc_gfeed', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    }

    // Add term page
    public function wc_gfeed_taxonomy_add_new_meta_field() {
        // this will add the custom meta field to the add new term page
        ?>
        <div class="form-field">
            <label for="term_meta[gcategory]"><?php _e( 'Google Product Category', 'wc_gfeed' ); ?></label>
            <input type="text" name="term_meta[gcategory]" id="term_meta[gcategory]" value="">
            <p class="description"><?php _e( 'Enter a value for this field','wc_gfeed' ); ?></p>
        </div>
    <?php
    }

    // Edit term page
    public function wc_gfeed_taxonomy_edit_meta_field($term) {

        // put the term ID into a variable
        $t_id = $term->term_id;

        // retrieve the existing value(s) for this meta field. This returns an array
        $term_meta = get_option( "taxonomy_$t_id" ); ?>
        <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[gcategory]"><?php _e( 'Google Product Category', 'wc_gfeed' ); ?></label></th>
            <td>
                <input type="text" name="term_meta[gcategory]" id="term_meta[gcategory]" value="<?php echo esc_attr( $term_meta['gcategory'] ) ? esc_attr( $term_meta['gcategory'] ) : ''; ?>">
                <p class="description"><?php _e( 'Enter a value for this field from Table Google Product','wc_gfeed' ); ?> <a href="http://www.google.com/basepages/producttype/taxonomy.<?php echo str_replace("_", "-", WPLANG);?>.txt" target="_blank"><?php _e( 'List File of Google ','wc_gfeed' ); ?></a></p>
            </td>
        </tr>
    <?php
    }

    // Save extra taxonomy fields callback function.
    public function save_taxonomy_custom_meta( $term_id ) {
        if ( isset( $_POST['term_meta'] ) ) {
            $t_id = $term_id;
            $term_meta = get_option( "taxonomy_$t_id" );
            $cat_keys = array_keys( $_POST['term_meta'] );
            foreach ( $cat_keys as $key ) {
                if ( isset ( $_POST['term_meta'][$key] ) ) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }
            // Save the option array.
            update_option( "taxonomy_$t_id", $term_meta );
        }
    }
    
    /*
     * Generate XML File
     */
    public function generate_xml_product_file() 
    {
    
       $xml = new SimpleXMLElement('<xml/>');
       $products = get_posts( array( 'post_type'=>'product', 'numberposts'=>-1 ) );

       $xml->addChild('item');

       foreach($products as $i=>$product){
            $name = get_the_title($product->ID);
            $price = $product->price;
           
           /*$terms = get_the_terms( $product->ID, 'product_cat' );
			foreach ($terms as $term) {
				$product_cat_id = $term->term_id;
				break;
			}
			$term_meta = get_option( "taxonomy_".$product_cat_id );
			$google_cat = $term_meta['gcategory'];*/
            $xml->producers->addChild('product');
            $xml->products->product[$i]->addChild('g:price', $name);
            $xml->products->product[$i]->addChild('g:image_link', $name);
            $xml->products->product[$i]->addChild('g:condition', 'new');
        } 
        
        
        $file = ABSPATH . '/products-feed.xml';
         
        $open = fopen($file, 'w') or die ("File cannot be opened.");
        fwrite($open, $xml->asXML());
        fclose($open); 
    }
    
} // from Class

$WCGoogleProductFeed = new WCGoogleProductFeed();