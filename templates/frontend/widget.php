<?php $wrapper = 'PhanesWidgetWrapper'; ?>

<center>
    <div id="<?php echo $wrapper ?>">
        <div id="loader_icon" style="text-align: center;">
            <img src="<?php echo PQC()->host ?>/assets/img/loader.gif" alt="<?php esc_attr( 'Loading...', 'pqc' ) ?>">
        </div>
    </div>
</center>

<script type="text/javascript">
    const PhanesClient = {
        host: '<?php echo PQC()->host ?>',
        wrapper: '#<?php echo $wrapper ?>',
        merchant_id: '<?php echo PQC()->get_option( 'merchant_id' ) ?>',
        access_token: '<?php echo PQC()->get_option( 'access_token' ) ?>',
    };
</script>

<script type="text/javascript" src="<?php echo PQC()->host ?>/libs/widget/main.min.js"></script>