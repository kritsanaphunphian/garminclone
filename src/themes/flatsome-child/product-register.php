<?php
$user = get_userdata( get_current_user_id() );

/**
 * @param  string $productOwnerId
 *
 * @return WP_Query
 */
function get_related_registered_product_post( $productOwnerId ) {
    $args = array(
        'post_type'   => 'gis_reg_product',
        'post_status' => array( 'publish' ),
        'meta_query'  => array(
            array(
                'key'     => 'gisc_reg_product_product_owner_id',
                'value'   => $productOwnerId,
                'compare' => 'LIKE'
            )
        )
    );

    return new WP_Query( $args );
}

function register_product( $email, $serial ) {
    $result = GISC()->request( 'register_product', array( 'serialNo' => $serial, 'Email' => $email ) );

    if ( $result['Flag'] == 102 ) {
        ?>
        <p class="alert-color">
            <?php echo __( 'The serial number has been registered.', 'garminbygis' ); ?>
        </p>
        <?php
        return false;
    } else if ( $result['Flag'] == 3 || $result['Flag'] == 0 ) {
        $post_id = wp_insert_post( array(
            'post_title'  => 'GISC Product Receipt, owner id: " ' . $result['ProductOwnerId'] . ' ", serial: "' . $serial . '"',
            'post_status' => 'publish',
            'post_type'   => 'gis_reg_product'
        ) );

        garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_id', $result['ProductOwnerId'] );
        garminbygis_update_post_meta( $post_id, 'gisc_reg_product_product_owner_email', $email );
        garminbygis_update_post_meta( $post_id, 'gisc_reg_product_serial_number', $serial );

        return $post_id;
    } else {
        ?>
        <p class="alert-color">
            <?php echo __( 'No serial found.', 'garminbygis' ); ?>
        </p>
        <?php
        return false;
    }
}

if ( isset( $_POST['send-serial'] ) ) {
    if ( $user ) {
        $post_id = register_product( $user->user_email, $_POST['serail-product'] );
        // $post_id = register_product( 's.tuasakul@gmail.com', $_POST['serail-product'] );

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

        if ( $post_id ) : ?>
            <p class="success-color">
                <?php echo __( 'Register successfully.', 'garminbygis' ); ?>
            </p>
            <?php
        endif;
    }


    // add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );

    // $user_id = get_current_user_id();
    // $user = get_userdata( $user_id );

    // $firstname = get_user_meta( $user_id, 'first_name', true );
    // $lastname = get_user_meta( $user_id, 'last_name' , true );
    // $tel = get_user_meta( $user_id, 'billing_phone' , true );
    // $useremail = $user->user_email;

    // $serailProduct = $_POST['serail-product']; 

    // $headers[] = 'CC: kritsana.phunpian@gmail.com';
        
    // $to = array(
    //     'kolokolo.jack@gmail.com'
    //     );
    // $subject = 'Serial Number';
    // $body = '<p>Serial Number product ' . $serailProduct . '</p> 
    // <p>Name : ' . $firstname . ' ' . $lastname . '</p>
    // <p>Email : ' . $useremail . '</p>
    // <p>Tel : ' . $tel . '</p>';

    // wp_mail( $to, $subject, $body, $headers );

    ?>
    <!-- <div class="woocommerce-message message-wrapper">
        <div class="message-container container success-color medium-text-center sb-custom-alert">
            <i class="icon-checkmark"><strong>Success:</strong> Your serial number product has sended.</i>
        </div>
    </div> -->
    <?php
    
    // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
    // remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
    // function wpdocs_set_html_mail_content_type() {
    //     return 'text/html';
    // }
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
    GISC()->request( 'remove_registed_product', array( 'productOwnerId' => $_POST['delete-button'] ) );

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
$items = GISC()->request( 'list_registered_product', array( 'Email' => $user->user_email ) );
// $items = GISC()->request( 'list_registered_product', array( 'Email' => 's.tuasakul@gmail.com' ) ); // TODO: Remove mock email.
?>
<?php if ( $items && ! empty( $items ) ) : ?>
    <form class="garminbygis-form-registered-product-list" name="frm" method="post" action="#" enctype="multipart/form-data">
        <input type="hidden" name="form-delete" />

        <h3>Registered Products.</h3>
        <table class="woocommerce-gisc-registered-product-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th class="woocommerce-gisc-registered-product-table__header woocommerce-gisc-registered-product-table__header-product-name"><span class="nobr">Product's Information</span></th>
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

                        <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-order-date" data-title="Date">
                            <?php $datetime =  new DateTime( $value['BuyDate'] ); ?>
                            <time datetime="<?php echo $value['BuyDate']; ?>"><?php echo $datetime->format(' Y.m.d' ); ?></time>
                        </td>

                        <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-receipt" data-title="Receipt">
                            <?php $query = get_related_registered_product_post( $value['ProductOwnerId'] ); ?>

                            <?php if ( $query->have_posts() ): $posts = $query->posts; ?>
                                <?php $url = wp_get_attachment_url( get_post_thumbnail_id($posts[0]->ID), 'thumbnail' ); ?>
                                <a href="<?php echo $url ?>">view receipt</a>
                            <?php else: ?>
                                <?php echo do_shortcode('[button text="attach file" link="#attach-to-owner-id-' . $value['ProductOwnerId'] . '"][lightbox id="attach-to-owner-id-' . $value['ProductOwnerId'] . '" width="600px" padding="20px"]' . sprintf( $receipt_attachment_modal, $value['ProductOwnerId'], $value['SerialNo'] ) . '[/lightbox]'); ?>
                            <?php endif; ?>
                        </td>

                        <td class="woocommerce-gisc-registered-product-table__cell woocommerce-gisc-registered-product-table__cell-delete" data-title="">
                            <button class="button" type="submit" name="delete-button" value="<?php echo $value['ProductOwnerId']; ?>" onClick="return confirm( 'Are you sure you want to remove this product?' )">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
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
.woocommerce .woocommerce-gisc-registered-product-table__header-receipt,
.woocommerce .woocommerce-gisc-registered-product-table__cell-receipt {
    text-align: center;
}

.woocommerce .woocommerce-gisc-registered-product-table__cell-receipt .button,
.woocommerce .woocommerce-gisc-registered-product-table__cell-delete button {
    margin: 3px;
    font-size: .75em;
}
</style>

<?php
