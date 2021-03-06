<?php 

// args
$args = array(
    'posts_per_page'	=> -1,
    'post_type'		=> 'post_type_shop',
    'meta_query'	=> array(
		array(
            'key' => 'shop_type', // name of custom field
            'value' => '"retailer"', // matches exactly "red"
            'compare' => 'LIKE'
		)
	)
);

// query
$the_query = new WP_Query( $args );
?>

<?php if( $the_query->have_posts() ): ?>
<?php echo do_shortcode('[wp_jdt id="retailerMB"]');?>
<table id="retailerMB" width="100%">
    <thead>
        <tr>
            <th>Company</th>
            <th>Address</th>
            <th>Telephone</th>
            <th>Business Day</th>
            <th>Business hour</th>
            <th>Website</th>
            <th style="display:none;"></th>
            <th style="display:none;"></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>Company</th>
            <th>Address</th>
            <th>Telephone</th>
            <th>Business Day</th>
            <th>Business hour</th>
            <th>Website</th>
            <th style="display:none;"></th>
            <th style="display:none;"></th>
        </tr>
    </tfoot>  
    <tbody>

    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
    <tr>
        <td><?php the_field('company_eng'); ?></td>
        <td><?php the_field('shop_address_eng');?></td>
        <td><?php the_field('telephone_1') ?></td>
        <td><?php the_field('business_day') ?></td>
        <td><?php the_field('business_hour') ?></td>
        <td><a href="http://<?php the_field('shop_url') ?>"><?php the_field('shop_url') ?></a></td>
        <td style="display:none;"><?php the_field('shop_address') ?></td>
        <td style="display:none;"><?php the_field('product_type') ?></td>
    </tr>
    <?php endwhile; ?>

    </tbody>
</table>

<?php endif; ?>
<?php wp_reset_query();	 // Restore global post data stomped by the_post().





