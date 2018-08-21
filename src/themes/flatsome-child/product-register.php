<?php
$user = get_userdata( get_current_user_id() );

function register_gisc_product( $serialNo, $email ) {
    $gisc_product = GISC_Product()->register( $serialNo, $email );
    if ( $gisc_product->has_error() ) {
        $gisc_product->display_error();
        return;
    }

    if ( $gisc_product->related_post_id() && isset( $_FILES['product-receipt']['tmp_name'] ) && ! $_FILES['product-receipt']['error'] ) {
        $gisc_product->attach_receipt($_FILES);
    }

    if ( $gisc_product->related_post_id() ) :
        ?>
        <p class="success-color">
            <?php echo __( 'Register successfully.', 'garminbygis' ); ?>
        </p>
        <?php
    endif;
}

if ( isset( $_POST['send-serial'] ) ) {
    register_gisc_product( $_POST['serail-product'], $user->user_email );
} else if ( isset( $_POST['attach-receipt'] ) ) {
    $gisc_product = GISC_Product()->load_related_posts( $_POST['serialNo'], $user->user_email );

    if ( $gisc_product->have_related_posts() ) {
        // Update
        $gisc_product->attach_receipt($_FILES);
    } else {
        // Create new post
        $gisc_product->insert_related_post( $_POST['productOwnerId'], $_POST['serialNo'], $user->user_email );

        if ( $gisc_product->related_post_id() && isset( $_FILES['product-receipt']['tmp_name'] ) && ! $_FILES['product-receipt']['error'] ) {
            $gisc_product->attach_receipt($_FILES);
        }
    }
} else if ( isset( $_POST['delete-button'] ) ) {
    GISC_Product()->deregister( $_POST['productOwnerId'], $_POST['delete-button'], $user->user_email );

    wp_redirect( get_permalink() . 'register-product' );
    exit();
}
?>

<form name="frm" method="post" action="#" enctype="multipart/form-data">
    <h3><?php echo __( 'Register your GARMIN products to be eligible for benefits.', 'garminbygis' ); ?></h3>
    <div class="garminbygis-product-registration-form">
        <div>
            <label for="serail-product"><?php echo __( 'Please specify your product\'s serial number', 'garminbygis' ); ?>:</label>
            <input id="serail-product" type="text" name="serail-product" value="">
        </div>

        <p class="form-row form-row-first">
            <label><?php echo __( 'Receipt (optional)', 'garminbygis' ); ?></label>
            <input id="product-receipt" type="file" name="product-receipt" accept=".png,.jpg,.gif,.pdf,image/jpg,image/jpeg,image/png,image/vnd.sealedmedia.softseal-jpg,image/vnd.sealedmedia.softseal-gif,application/vnd.sealedmedia.softseal-pdf,application/pdf">
            <br/><em><small><?php echo __( 'File extensions supported are:', 'garminbygis' ); ?> pdf, jpg, png, gif, bmp</small></em>
        </p>

        <p class="form-row form-row-last garminbygis-form-row-submit-button">
            <input type="submit" value="<?php echo _x( 'Submit', 'product registration - register', 'garminbygis' ); ?>" name="send-serial" id="send-serial">
        </p>

        <div class="clear"></div>
    </div>
</form>

<div class="action-update-software-button">
    <a href="https://www.garmin.com/th-TH/software/express" class="button primary"><?php echo __( 'UPDATE SOFTWARE', 'garminbygis' ); ?></a>
</div>

<?php
$receipt_attachment_modal = '
    <form name="receipt-attachment-form" method="post" action="#" enctype="multipart/form-data">
        <input type="hidden" name="productOwnerId" value="%s">
        <input type="hidden" name="serialNo" value="%s">
        <div class="garminbygis-product-registration-form">
            <p class="form-row form-row-first">
                <label>Receipt</label>
                <input id="product-receipt" type="file" name="product-receipt" accept=".png,.jpg,.gif,.pdf,image/jpg,image/jpeg,image/png,image/vnd.sealedmedia.softseal-jpg,image/vnd.sealedmedia.softseal-gif,application/vnd.sealedmedia.softseal-pdf,application/pdf">
            </p>
            <p class="form-row form-row-last garminbygis-form-row-submit-button">
                <input type="submit" value="Submit" name="attach-receipt" id="attach-receipt">
            </p>
            <div class="clear"></div>
            <div>
                <em><small>' . __( "File extensions supported are:", "garminbygis" ) . ' pdf, jpg, png, gif, bmp</small></em>
            </div>
        </div>
    </form>';
?>

<?php
$items = GISC()->get( 'list_registered_product', array( 'Email' => $user->user_email ) );
?>
<?php if ( $items && ! empty( $items ) ) : ?>
    <h3><?php echo __( 'Registered Products.', 'garminbygis' ); ?></h3>
    <table class="woocommerce-gisc-registered-product-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-product-name"><span class="nobr"><?php echo __( 'Product\'s Information', 'garminbygis' ); ?></span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-product-update"><span class="nobr"><?php echo __( 'Update', 'garminbygis' ); ?></span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-order-date"><span class="nobr"><?php echo __( 'Purchase Date', 'garminbygis' ); ?></span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-receipt"><span class="nobr"><?php echo __( 'Receipt / Warranty', 'garminbygis' ); ?></span></th>
                <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-delete"><span class="nobr"><?php echo __( 'Delete', 'garminbygis' ); ?></span></th>
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
                            <a href="http://www.garmin.co.th/mapupdate/" class="button primary"><?php echo __( 'Download Map', 'garminbygis' ); ?></a>
                        <?php elseif ( (int) $value['Flag'] >= 1 && (int) $value['Flag'] <= 6 ): ?>
                            <a href="#" class="button primary"><?php echo __( 'Download Map', 'garminbygis' ); ?></a>
                        <?php elseif ( (int) $value['Flag'] === 0 ): ?>
                            <a href="#" class="button primary"><?php echo __( 'Buy Map', 'garminbygis' ); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-order-date" data-title="Date">
                        <?php $datetime =  new DateTime( $value['BuyDate'] ); ?>
                        <time datetime="<?php echo $value['BuyDate']; ?>"><?php echo $datetime->format(' Y.m.d' ); ?></time>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-receipt" data-title="Receipt">
                        <?php
                        $query = GISC_Product()->get_related_posts( $value['SerialNo'], $user->user_email );
                        $post  = $query->have_posts() ? $query->posts[0] : null;

                        if ( $post && $url = get_post_meta( $post->ID, 'gisc_reg_product_receipt_document_url' ) ) {
                            if ( 'pdf' === pathinfo($url[0])['extension'] ) {
                                echo do_shortcode('[lightbox id="receipt-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"] ' . __( 'The PDF file cannot be rendered. Please click the link to refer to your original file', 'garminbygis' ) . ': "<a href="' . $url[0] . '" target="_blank">' . pathinfo($url[0])['filename'] . '</a>"[/lightbox]');
                            } else {
                                echo do_shortcode('[lightbox id="receipt-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"]<img src="' . $url[0] . '" class="img-responsive" />[/lightbox]');
                            }
                            ?>
                            <a href="#receipt-id-<?php echo $value['ProductOwnerId']; ?>"><?php echo __( 'view receipt', 'garminbygis' ); ?></a>
                            <br/>
                            <?php
                        }

                        echo do_shortcode('[button text="' . __( 'attach file', 'garminbygis' ) . '" link="#attach-to-owner-id-' . $value['ProductOwnerId'] . '"][lightbox id="attach-to-owner-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"]' . sprintf( $receipt_attachment_modal, $value['ProductOwnerId'], $value['SerialNo'] ) . '[/lightbox]');
                        ?>
                    </td>

                    <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-delete" data-title="">
                        <form class="garminbygis-form-registered-product-list" name="frm" method="post" action="#" enctype="multipart/form-data">
                            <input type="hidden" name="productOwnerId" value="<?php echo $value['ProductOwnerId']; ?>" />
                            <button class="button" type="submit" name="delete-button" value="<?php echo $value['SerialNo']; ?>" onClick="return confirm( 'Are you sure you want to remove this product?' )"><?php echo __( 'Remove', 'garminbygis' ); ?></button>
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
