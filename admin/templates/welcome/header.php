<div class="wrap pqc-wrapper about-wrap pqc-about-wrap">

    <?php if ( isset( $_GET['pqc-upgrade'] ) && $_GET['pqc-upgrade'] == 1 ) : ?>

	<h1><?php printf( __( 'Upgraded to %s Version %s', 'pqc' ), PQC_NAME, PQC_VERSION ); ?></h1>
    
    <?php else : ?>
    
    <h1><?php echo __( 'Welcome to ', 'pqc' ) . PQC_NAME; ?></h1>
    
    <?php endif; ?>

	<div class="about-text"><?php printf( __( '%s is a powerful STL object Calculator plugin that allows your Customers to upload their STL files, get instant quotes, and order online seamlessly by paying with PayPal on your sites with WordPress.', 'pqc' ), PQC_NAME ); ?></div>

	<div class="wp-badge pqc-badge">Version <?php echo PQC_VERSION; ?></div>
	
	<p class="pqc-admin-notice pqc-actions">
        <a href="<?php echo admin_url( 'admin.php?page=pqc-settings-page' ); ?>" class="button button-primary"><?php _e( 'Settings', 'pqc' ); ?></a>
		<a href="<?php echo admin_url( 'admin.php?page=pqc-license-page' ); ?>" class="button button-success"><?php _e( 'Insert / Update License code', 'pqc' ); ?></a>
	</p>
    
    <h2 class="nav-tab-wrapper pqc-nav-tab-wrapper">
    
    <?php
        
        foreach ( $pqc_getting_started_tabs as $key => $label ) {
            
            $class = ( $key == $string ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            
            $link = admin_url( 'admin.php?page=pqc-' . $key );
            
            echo '<a href="' . $link . '" class="' . $class .'">' . $label . '</a>';
            
        }
    
    ?>
    
    </h2>