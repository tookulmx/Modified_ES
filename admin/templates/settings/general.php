<form action="" method="POST" enctype="multipart/form-data">

    <h2><?php _e( 'General Options', 'pqc' ); ?></h2>

    <table class="form-table">
        <tbody>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_max_file_size"><?php _e( 'Max. File Size', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-number">
                    <input id="pqc_max_file_size" name="pqc_max_file_size" style="width:60px;" value="<?php echo esc_attr( $max_file_size ); ?>" min="0" type="number" required> MB
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_max_file_stay"><?php _e( 'Delete files older than', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-number">
                    <input id="pqc_max_file_stay" name="pqc_max_file_stay" style="width: 60px;" value="<?php echo esc_attr( $max_file_stay ); ?>" min="1" type="number" required> day(s)
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_file_max_upload"><?php _e( 'Max. File to upload simultaneously', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-number">
                    <input id="pqc_max_file_upload" name="pqc_max_file_upload" style="width: 60px;" value="<?php echo esc_attr( $max_file_upload ); ?>" min="1" type="number" required> File(s)
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_min_file_volume"><?php _e( 'Min. File volume to accept', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-number">
                    <input id="pqc_min_file_volume" name="pqc_min_file_volume" style="width: 60px;" value="<?php echo esc_attr( $min_file_volume ); ?>" min="0.09" step="any" type="number" required> Cubic cm
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc"><?php _e( 'Density Charge', 'pqc' ); ?></th>
                <td class="forminp">
                    <input id="pqc_density_charge" name="pqc_density_charge" <?php checked( $density_charge, 1 ); ?> type="checkbox">
                    <label for="pqc_density_charge"><?php _e( 'Activate charge by density.', 'pqc' ); ?></label>
                </td>
            </tr>

        </tbody>
    </table>

    <h2><?php _e( 'Currency & Pricing', 'pqc' ); ?></h2>
    <p><?php _e( 'This options affect how prices are displayed on the frontend.', 'pqc' ); ?></p>

    <table class="form-table">
        <tbody>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_initial_price"><?php _e( 'Initial Price', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-number">
                    <input id="pqc_initial_price" name="pqc_initial_price" style="width:100px;" value="<?php echo esc_attr( $initial_price ); ?>" step="any" type="number" required>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_currency"><?php _e( 'Currency', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <?php

                        $options = '';

                        foreach ( $currencies as $key => $data ) {

                            $selected = selected( $currency, $key, false );

                            $name = $data['name'] . ' (' . $data['symbol'] . ')';

                            $options .= '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';

                        }

                        echo '<select id="pqc_currency" name="pqc_currency">' . $options . '</select>';
                    ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pqc_currency_pos"><?php _e( 'Currency Position', 'pqc' ); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <?php
                        $number = pqc_number_format( 1234.56 );
                        $symbol = $currencies[$currency]['symbol'];

                        $currency_poss = array(
                            'left'          => "Left ($symbol$number)",
                            'right'         => "Right ($number$symbol)",
                            'left_space'    => "Left with space ($symbol $number)",
                            'right_space'   => "Right with space ($number $symbol)",
                        );

                        $options = '';

                        foreach ( $currency_poss as $key => $value ) {

                            $selected = selected( $currency_pos, $key, false );

                            $options .= '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';

                        }

                        echo '<select id="pqc_currency_pos" name="pqc_currency_pos">' . $options . '</select>';
                    ?>
                </td>
            </tr>

        </tbody>
    </table>
    <p class="submit">
        <input name="pqc_save_general_settings" class="button-primary pqc-save-button" value="<?php _e( 'Save Changes', 'pqc' ); ?>" type="submit">
        <?php wp_nonce_field( 'pqc_save_general_settings' ); ?>
    </p>

</form>
