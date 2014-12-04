<?php
/*
Plugin Name: WooCommerce Google Product Feed
Plugin URI: http://www.closemarketing.es/portafolio/plugin-woocommerce-googleproductfeed/
Description: Creates a Feed for Google Merchant

Version: 0.1
Requires at least: 3.0

Author: Closemarketing
Author URI: http://www.closemarketing.es/

Text Domain: wc_gfeed
Domain Path: /languages/

License: GPL
*/

//Google Product Feed
//Define the product feed php page
function products_feed_rss2() {
 $rss_template = dirname( __FILE__ ) . '/product-feed.php';
 load_template ( $rss_template );
}
 
//Add the product feed RSS
add_action('do_feed_products', 'products_feed_rss2', 10, 1);

//Update the Rerewrite rules
add_action('init', 'my_add_product_feed');
 
//function to add the rewrite rules
function my_rewrite_product_rules( $wp_rewrite ) {
 $new_rules = array(
 'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)
 );
 $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
 
//add the rewrite rule
function my_add_product_feed( ) {
 global $wp_rewrite;
 add_action('generate_rewrite_rules', 'my_rewrite_product_rules');
 $wp_rewrite->flush_rules();
}



// Add term page
function wc_gfeed_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[gcategory]"><?php _e( 'Google Product Category', 'wc_gfeed' ); ?></label>
		<input type="text" name="term_meta[gcategory]" id="term_meta[gcategory]" value="">
		<p class="description"><?php _e( 'Enter a value for this field','wc_gfeed' ); ?></p>
	</div>
<?php
}
add_action( 'product_cat_add_form_fields', 'wc_gfeed_taxonomy_add_new_meta_field', 10, 2 );

// Edit term page
function wc_gfeed_taxonomy_edit_meta_field($term) {
 
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
add_action( 'product_cat_edit_form_fields', 'wc_gfeed_taxonomy_edit_meta_field', 10, 2 );

// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
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
add_action( 'edited_product_cat', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_product_cat', 'save_taxonomy_custom_meta', 10, 2 );