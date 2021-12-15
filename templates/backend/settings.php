<div class="wrap pqc-wrapper">

    <h1><?php printf( __( ' %s Settings.', 'pqc' ), PQC()->name); ?></h1>

    <form action="" method="POST">

        <?php wp_nonce_field( 'pqc_nonce', 'pqc_settings' ); ?>

        <p><?php printf( __( 'This options affect how %s works.', 'pqc' ), PQC()->name ); ?></p>
        <p><?php printf( __( 'You can get your API credentials <a href="%s" target="_blank">here</a>', 'pqc' ), PQC()->host . '/api' ); ?></p>

        <?php
        $merchant_id = PQC()->get_option('merchant_id');
        $access_token = PQC()->get_option('access_token');

        if ( ! empty($merchant_id) && ! empty($access_token) ) :
        ?>
        <p><?php printf( __( 'Use the %s block widget or this shortcode <code>[phanes3dp]</code> in your post/page to display the widget.', 'pqc' ), PQC()->name ); ?></p>
        <?php endif; ?>

        <table class="form-table">
            <tbody>

                <tr valign="top">
                    <th scope="row">
                        <label for="pqc_merchant_id"><?php esc_html_e( 'Merchant ID', 'pqc' ); ?></label>
                    </th>
                    <td><input type="text" id="pqc_merchant_id" name="pqc_merchant_id" class="regular-text" value="<?php echo esc_attr( $merchant_id ); ?>" required></td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="pqc_access_token"><?php esc_html_e( 'Access Token', 'pqc' ); ?></label>
                    </th>
                    <td><input type="text" id="pqc_access_token" name="pqc_access_token" class="regular-text" value="<?php echo esc_attr( $access_token ); ?>" required></td>
                </tr>

            </tbody>
        </table>

        <p class="submit">
            <input type="submit" value="<?php esc_attr_e( 'Save Changes', 'pqc' ); ?>" class="button button-primary" name="pqc_submit">
        </p>

    </form>

</div>