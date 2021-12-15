<div class="wrap pqc-wrapper" style="margin: 20px 20px 0 2px;">

    <h2 style="margin: 0; padding: 0;"></h2>

    <div class="welcome-panel" style="float: left;width: 77%;padding: 0.89% 0.5%;margin: 0;">

        <div class="welcome-panel-content">

            <h1><?php printf( __( '%s License code page', 'pqc' ), PQC_NAME ); ?></h1>

            <p class="about-description"><?php _e( 'Please insert your License code.', 'pqc' ); ?></p>

            <div class="welcome-panel-column-container" style="float: left;width: 100%;margin: 2.5em 0 1em;">

                <div class="welcome-panel-column" style="width: 100%;">

                    <h3 style="display: inline; margin-right: 2%"><?php _e( 'License code:', 'pqc' ); ?></h3>

                    <form method="POST" action="" enctype="multipart/form-data" style="display: inline; font-family: camingoCode;">

                        <input size="4" type="text" value="WOOS" required="required" readonly="readonly"> –

                        <input size="4" type="text" name="pqc-license-code[]" minlength="4" maxlength="4" autocomplete="off" required="required"> –
                        <input size="4" type="text" name="pqc-license-code[]" minlength="4" maxlength="4" autocomplete="off" required="required"> –
                        <input size="4" type="text" name="pqc-license-code[]" minlength="4" maxlength="4" autocomplete="off" required="required"> –
                        <input size="4" type="text" name="pqc-license-code[]" minlength="4" maxlength="4" autocomplete="off" required="required"> –
                        <input size="4" type="text" name="pqc-license-code[]" minlength="4" maxlength="4" autocomplete="off" required="required"> –

                        <input size="3" type="text" value="KEY" required="required" readonly="readonly">

                        <button style="margin-left: 2%" name="pqc-apply-license-code" class="button button-primary"><?php _e( 'Apply Code', 'pqc' ); ?></button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <div class="wp-badge pqc-badge" style="float: right;width: 20%;margin: 0;">Version <?php echo PQC_VERSION; ?></div>

</div>
