<?php
$user = get_userdata( get_current_user_id() );

function register_gisc_product( $serialNo, $email ) {
    $gisc_product = GISC_Product()->register( $serialNo, $email );
    if ( $gisc_product->has_error() ) {
        $gisc_product->display_error();
        return;
    }

    if ( $gisc_product->related_post_id() && isset( $_FILES['product-receipt']['tmp_name'] ) && ! $_FILES['product-receipt']['error'] ) {
        $upload = wp_upload_bits(
            $_FILES['product-receipt']['name'],
            null,
            file_get_contents( $_FILES['product-receipt']['tmp_name'] )
        );

        $filename    = $upload['file'];
        $wp_filetype = wp_check_filetype($filename, null );
        $attachment  = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $filename, $gisc_product->related_post_id() );

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $gisc_product->related_post_id(), $attach_id );
    }

    if ( $gisc_product->related_post_id() ) : ?>
        <p class="success-color">
            <?php echo __( 'Register successfully.', 'garminbygis' ); ?>
        </p>
        <?php
    endif;
}

if ( isset( $_POST['send-serial'] ) ) {
    register_gisc_product( $_POST['serail-product'], $user->user_email );
} else if ( isset( $_POST['attach-receipt'] ) ) {
    $post_id = wp_insert_post( array(
        'post_title'  => 'GISC Product Receipt, owner id: " ' . $_POST['productOwnerId'] . ' ", serial: "' . $_POST['serialNo'] . '"',
        'post_status' => 'publish',
        'post_type'   => 'gis_reg_product'
    ) );

    garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_id', $_POST['productOwnerId'] );
    garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_email', $user->user_email );
    garminbygis_update_post_meta( $post_id, 'gisc_reg_product_serial_number', $_POST['serialNo'] );

    if ( $post_id && isset( $_FILES['product-receipt']['tmp_name'] ) && ! $_FILES['product-receipt']['error'] ) {
        $upload = wp_upload_bits(
            $_FILES['product-receipt']['name'],
            null,
            file_get_contents( $_FILES['product-receipt']['tmp_name'] )
        );

        $filename = $upload['file'];
        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $post_id, $attach_id );
    }
} else if ( isset( $_POST['delete-button'] ) ) {
    GISC_Product()->deregister( $_POST['delete-button'], $user->user_email );

    wp_redirect( get_permalink() . 'register-product' );
    exit();
}
?>

<form name="frm" method="post" action="#" enctype="multipart/form-data">
    <h3>Register your GARMIN products to be eligible for benefits.</h3>
    <div class="garminbygis-product-registration-form">
        <div>
            <label for="serail-product">Please specify your product's serial number:</label>
            <input id="serail-product" type="text" name="serail-product" value="">
        </div>

        <p class="form-row form-row-first">
            <label>Receipt (optional)</label>
            <input id="product-receipt" type="file" name="product-receipt" accept=".png,.jpg,.gif,.pdf, image/png,image/vnd.sealedmedia.softseal-jpg,image/vnd.sealedmedia.softseal-gif,application/vnd.sealedmedia.softseal-pdf">
        </p>

        <p class="form-row form-row-last garminbygis-form-row-submit-button">
            <input type="submit" value="Submit" name="send-serial" id="send-serial">
        </p>

        <div class="clear"></div>
    </div>
</form>

<div class="action-update-software-button">
    <a href="https://www.garmin.com/th-TH/software/express" class="button primary">UPDATE SOFTWARE</a>
</div>

<?php
$receipt_attachment_modal = '
    <form name="receipt-attachment-form" method="post" action="#" enctype="multipart/form-data">
        <input type="hidden" name="productOwnerId" value="%s">
        <input type="hidden" name="serialNo" value="%s">
        <div class="garminbygis-product-registration-form">
            <p class="form-row form-row-first">
                <label>Receipt</label>
                <input id="product-receipt" type="file" name="product-receipt" accept=".png,.jpg,.gif,.pdf, image/png,image/vnd.sealedmedia.softseal-jpg,image/vnd.sealedmedia.softseal-gif,application/vnd.sealedmedia.softseal-pdf">
            </p>
            <p class="form-row form-row-last garminbygis-form-row-submit-button">
                <input type="submit" value="Submit" name="attach-receipt" id="attach-receipt">
            </p>
            <div class="clear"></div>
        </div>
    </form>';
?>

<?php
$items = GISC()->get( 'list_registered_product', array( 'Email' => $user->user_email ) );
?>
<?php if ( $items && ! empty( $items ) ) : ?>
    <h3>Registered Products.</h3>
    <table class="woocommerce-gisc-registered-product-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-product-name"><span class="nobr">Product's Information</span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-product-update"><span class="nobr">Update</span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-order-date"><span class="nobr">Purchase Date</span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-receipt"><span class="nobr">Receipt / Warranty</span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-delete"><span class="nobr">Delete</span></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ( $items as $key => $value ) : ?>
                <tr class="woocommerce-gisc-registered-product-table__row woocommerce-gisc-registered-product-table__row--status-on-hold order">
                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-product-name" data-title="Product">
                        <span class="name"><?php echo $value['ProductName']; ?></span>
                        <br/><em>Serial No : <?php echo $value['SerialNo']; ?></em>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-product-update" data-title="Update">
                        <?php if ( (int) $value['Flag'] === 3 ): ?>
                            <a href="http://www.garmin.co.th/mapupdate/" class="button primary">Download Map</a>
                        <?php elseif ( (int) $value['Flag'] >= 1 && (int) $value['Flag'] <= 6 ): ?>
                            <a href="#" class="button primary">Download Map</a>
                        <?php elseif ( (int) $value['Flag'] === 0 ): ?>
                            <a href="#" class="button primary">Buy Map</a>
                        <?php endif; ?>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-order-date" data-title="Date">
                        <?php $datetime =  new DateTime( $value['BuyDate'] ); ?>
                        <time datetime="<?php echo $value['BuyDate']; ?>"><?php echo $datetime->format(' Y.m.d' ); ?></time>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-receipt" data-title="Receipt">
                        <?php
                        $query = GISC_Product()->get_related_posts( $value['ProductOwnerId'], $user->user_email );
                        $post  = $query->have_posts() ? $query->posts[0] : null;

                        if ( $post && $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ) ) {
                            ?>
                            <a href="#receipt-id-<?php echo $value['ProductOwnerId']; ?>">view receipt</a>
                            <?php
                            echo do_shortcode('[lightbox id="receipt-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"]<img src="' . $url . '" class="img-responsive" />[/lightbox]');
                        } else {
                            echo do_shortcode('[button text="attach file" link="#attach-to-owner-id-' . $value['ProductOwnerId'] . '"][lightbox id="attach-to-owner-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"]' . sprintf( $receipt_attachment_modal, $value['ProductOwnerId'], $value['SerialNo'] ) . '[/lightbox]');
                        }
                        ?>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-delete" data-title="">
                        <form class="garminbygis-form-registered-product-list" name="frm" method="post" action="#" enctype="multipart/form-data">
                            <input type="hidden" name="form-delete" />
                            <button class="button" type="submit" name="delete-button" value="<?php echo $value['ProductOwnerId']; ?>" onClick="return confirm( 'Are you sure you want to remove this product?' )">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>
        ..<br/>
        <em><small>You have no registed products.</small></em>
    </p>
<?php endif; ?>

<script type="text/javascript">
    document.getElementById( 'serail-product' ).onkeydown = function ( e ) {
        var value =  e.target.value;

        //only allow a-z, A-Z, digits 0-9 and comma, with only 1 consecutive comma ...
        if (!e.key.match(/[a-zA-Z0-9,]/) || (e.key == ',' && value[value.length-1] == ',')) {
            e.preventDefault();
        }
    };
</script>

<style>
.garminbygis-form-row-submit-button {
    padding-top: 10px;
    text-align: right;
}

.garminbygis-form-registered-product-list h3 {
    margin-top: 2em;
    margin-bottom: 1em;
}

.garminbygis-product-registration-form {
    background: #f5f5f5;
    padding: 1rem;
}

.woocommerce-gisc-registered-product-table td {
    padding: .75em .5em;
}

.woocommerce .woocommerce-gisc-registered-product-table__cell-product-name span.name {
    color: #005395;
    font-weight: 700;
}

.woocommerce .woocommerce-gisc-registered-product-table__cell-product-name em {
    font-size: .9em;
}

.shop_table thead tr th:last-of-type.woocommerce-gisc-registered-product-table__header-delete,
.shop_table tr td:last-of-type.woocommerce-gisc-registered-product-table__cell-delete,
.woocommerce .woocommerce-gisc-registered-product-table__header-product-update,
.woocommerce .woocommerce-gisc-registered-product-table__header-receipt,
.woocommerce .woocommerce-gisc-registered-product-table__cell-product-update,
.woocommerce .woocommerce-gisc-registered-product-table__cell-receipt {
    text-align: center;
}

.woocommerce .woocommerce-gisc-registered-product-table__cell-product-update .button,
.woocommerce .woocommerce-gisc-registered-product-table__cell-receipt .button,
.woocommerce .woocommerce-gisc-registered-product-table__cell-delete button {
    margin: 3px;
    font-size: .75em;
}

.action-update-software-button {
    margin-bottom: 1em;
    text-align: right;
}
</style>

<?php
