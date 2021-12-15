<form action="" method="POST" enctype="multipart/form-data">

    <h2>Checkout</h2>

    <table class="form-table">
        <tbody>
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_shop_location"><?php _e( 'Shop Location', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <?php
                        $s_options = array(
                            '1' => __( 'US', 'pqc' ),
                            '2' => __( 'International', 'pqc' ),
                        );
                        
                        $options = '';
                        
                        foreach ( $s_options as $key => $value ) {
                            
                            $selected = selected( $shop_location, $key, false );
                            
                            $options .= '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
                            
                        }
                        
                        echo '<select style="width: 12em;" id="pqc_shop_location" name="pqc_shop_location">' . $options . '</select>';
                    ?>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_checkout_option"><?php _e( 'Checkout Option', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <?php
                        $c_options = array(
                            '1' => __( 'Price Calculation and Instant Checkout', 'pqc' ),
                            '2' => __( 'Estimated Price and Request Price', 'pqc' ),
                        );
                        
                        $options = '';
                        
                        foreach ( $c_options as $key => $value ) {
                            
                            $selected = selected( $checkout_option, $key, false );
                            
                            $options .= '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
                            
                        }
                        
                        echo '<select id="pqc_checkout_option" name="pqc_checkout_option">' . $options . '</select>';
                    ?>
                </td>
            </tr>
            
        </tbody>        
    </table>

    <p class="submit">
        <input name="pqc_save_checkout_settings" class="button-primary pqc-save-button" value="<?php _e( 'Save Changes', 'pqc' ); ?>" type="submit">
        <?php wp_nonce_field( 'pqc_save_checkout_settings' ); ?>
    </p>
    
</form>