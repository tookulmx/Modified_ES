<form action="" method="POST" enctype="multipart/form-data">

    <h2>PayPal</h2>
    
    <p><?php _e( 'PayPal standard sends customers to PayPal to enter their payment information.', 'pqc' ); ?></p>

    <table class="form-table">
        <tbody>
        
            <tr valign="top">
                <th scope="row" class="titledesc">Activate PayPal Payment</th>
                <td class="forminp">
                    <input id="pqc_paypal_active" name="pqc_paypal_active" <?php checked( $paypal_active, 1 ); ?> type="checkbox">
                    <label for="pqc_paypal_active"><?php _e( 'Enable/Disable PayPal payment.', 'pqc' ); ?></label>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_paypal_email">PayPal Receiving Email</label>
                </th>
                <td class="forminp">
                    <input id="pqc_paypal_email" name="pqc_paypal_email" value="<?php echo esc_attr( $paypal_email ); ?>" class="regular-text ltr" type="email" autocomplete="off" required>
                    <p class="description"><?php _e( 'Your PayPal Email Address.', 'pqc' ); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row" class="titledesc"><?php _e( 'PayPal Sandbox', 'pqc' ); ?></th>
                <td class="forminp">
                    <input id="pqc_paypal_sandbox" name="pqc_paypal_sandbox" <?php checked( $paypal_sandbox, 1 ); ?> type="checkbox">
                    <label for="pqc_paypal_sandbox"><?php _e( 'Activate Sandbox to test payments.', 'pqc' ); ?></label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_paypal_client_id">PayPal Client ID</label>
                </th>
                <td class="forminp">
                    <input id="pqc_paypal_client_id" name="pqc_paypal_client_id" value="<?php echo esc_attr( $paypal_client_id ); ?>" class="regular-text ltr" type="text" autocomplete="off" required>
                    <p class="description"><?php _e( 'Get a PayPal Developer account <a href="https://developer.paypal.com">here</a>.', 'pqc' ); ?></p>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_paypal_client_secret_key">PayPal Secret Key</label>
                </th>
                <td class="forminp">
                    <input id="pqc_paypal_client_secret_key" name="pqc_paypal_client_secret_key" value="<?php echo esc_attr( $paypal_client_secret_key ); ?>" class="regular-text ltr" type="password" autocomplete="off" required>
                    <p class="description"><?php _e( 'Get a PayPal Developer account <a href="https://developer.paypal.com">here</a>.', 'pqc' ); ?></p>
                </td>
            </tr>
            
        </tbody>        
    </table>

    <p class="submit">
        <input name="pqc_save_paypal_settings" class="button-primary pqc-save-button" value="<?php _e( 'Save Changes', 'pqc' ); ?>" type="submit">
        <?php wp_nonce_field( 'pqc_save_paypal_settings' ); ?>
    </p>
    
</form>