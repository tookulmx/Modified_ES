<form action="" method="POST" enctype="multipart/form-data">

    <h2>Stripe</h2>
    
    <p><?php _e( 'Stripe allows Customers Pay with their Card securely on your website.', 'pqc' ); ?></p>

    <table class="form-table">
        <tbody>
        
            <tr valign="top">
                <th scope="row" class="titledesc">Activate Stripe Payment</th>
                <td class="forminp">
                    <input id="pqc_stripe_active" name="pqc_stripe_active" <?php checked( $stripe_active, 1 ); ?> type="checkbox">
                    <label for="pqc_stripe_active"><?php _e( 'Enable/Disable Stripe payment.', 'pqc' ); ?></label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_stripe_publishable_key">API Publishable Key</label>
                </th>
                <td class="forminp">
                    <input id="pqc_stripe_publishable_key" name="pqc_stripe_publishable_key" value="<?php echo esc_attr( $stripe_publishable_key ); ?>" class="regular-text ltr" type="text" autocomplete="off" required>
                    <p class="description"><?php _e( 'Your Stripe API Publishable key. Find it in your <a target="_blank" href="https://dashboard.stripe.com/account/apikeys">dashboard</a>', 'pqc' ); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_stripe_secret_key">API Secret Key</label>
                </th>
                <td class="forminp">
                    <input id="pqc_stripe_secret_key" name="pqc_stripe_secret_key" value="<?php echo esc_attr( $stripe_secret_key ); ?>" class="regular-text ltr" type="password" autocomplete="off" required>
                    <p class="description"><?php _e( 'Your Stripe API Secret key. It can be found in your stripe <a target="_blank" href="https://dashboard.stripe.com/account/apikeys">dashboard</a>', 'pqc' ); ?></p>
                </td>
            </tr>
            
        </tbody>        
    </table>

    <p class="submit">
        <input name="pqc_save_stripe_settings" class="button-primary pqc-save-button" value="<?php _e( 'Save Changes', 'pqc' ); ?>" type="submit">
        <?php wp_nonce_field( 'pqc_save_stripe_settings' ); ?>
    </p>
    
</form>