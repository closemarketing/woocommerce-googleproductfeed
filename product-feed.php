<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;
 
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'" ?>'; ?>
 
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:g="http://base.google.com/ns/1.0" <?php do_action('rss2_ns'); ?>
>
<channel>
    <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+XML">
    <link><?php bloginfo_rss('url') ?></link>
    <description><?php bloginfo('description');?></description>
    <lastbuilddate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastbuilddate>
    <language><?php bloginfo_rss( 'language' ); ?></language>
    <sy:updateperiod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updateperiod>
    <sy:updatefrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updatefrequency>
    <?php do_action('rss2_head'); ?>
    <?php
    $args = array( 'post_type' => 'product', 'posts_per_page' => -1 );
    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post(); global $product;
    ?>
    <item>
        <link><?php the_permalink_rss() ?></link>
        <g:price><?php echo $product->price ?></g:price>
        <g:image_link><?php echo wp_get_attachment_url( get_post_thumbnail_id() ) ?></g:image_link>
        <g:condition>new</g:condition>
        <g:id><?php echo $id; ?></g:id>
        <g:availability><?php echo $product->is_in_stock() ? 'in stock' : 'out of stock'; ?></g:availability>
		<g:product_type><?php echo strip_tags($product->get_categories( '>', '', '' ) ); ?></g:product_type>
        <g:google_product_category><?php 
			$terms = get_the_terms( $id, 'product_cat' );

			foreach ($terms as $term) {
				$product_cat_id = $term->term_id;
				break;
			}
			$term_meta = get_option( "taxonomy_".$product_cat_id );
			echo $term_meta['gcategory'];?></g:google_product_category>
		<?php if( $product->get_weight() ) { ?><g:shipping_weight><?php echo $product->get_weight();?></g:shipping_weight><?php } ?>
		<?php if($product->get_sku()) { ?><g:mpn><?php echo $product->get_sku(); ?></g:mpn><?php } ?>
		<?php if (get_option('rss_use_excerpt')) : ?>
		<description><?php the_excerpt_rss(); ?></description>
		<?php else : ?>
		<description><?php the_excerpt_rss(); ?></description>
		<?php endif; ?>
 
<?php rss_enclosure(); ?>
    <?php do_action('rss2_item'); ?>
    </item>
    <?php endwhile; ?>
</atom:link></channel>
</rss>