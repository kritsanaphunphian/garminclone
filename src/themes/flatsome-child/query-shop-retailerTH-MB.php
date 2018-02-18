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
<?php echo do_shortcode('[wp_jdt id="retailerTH-MB"]');?>
<table id="retailerTH-MB" width="100%">
    <thead>
        <tr>
            <th>ชื่อร้าน</th>
            <th>ที่อยู่</th>
            <th>เบอร์โทร</th>
            <th>วันทำการ</th>
            <th>เวลาทำการ</th>
            <th>เว็บไซต์</th>
            <th style="display:none;"></th>
            <th style="display:none;"></th>
    </tr>
    </thead>
    <tfoot>
        <tr>
            <th>ชื่อร้าน</th>
            <th>ที่อยู่</th>
            <th>เบอร์โทร</th>
            <th>วันทำการ</th>
            <th>เวลาทำการ</th>
            <th>เว็บไซต์</th>
            <th style="display:none;"></th>
            <th style="display:none;"></th>
        </tr>
    </tfoot>  
    <tbody>

    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
    <tr>
        <td><?php the_field('company_th'); ?></td>
        <td><?php the_field('shop_address_th');?></td>
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





