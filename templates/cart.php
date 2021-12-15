<div id="pqc-wrapper" class="container">

    <?php do_action( 'pqc_wrapper_cart_start' ); ?>

    <?php if ( ( isset( $error ) && $error ) || ( isset( $notice ) && $notice ) ) : ?>
    
    <header class="codrops-header">
        <?php if ( isset( $error ) && $error ) : ?>
        <div class="error"><p><?php echo $error_msg ?></p></div>
        <?php elseif ( $notice ) : ?>
        <div class="notice"><p><?php echo $notice_msg ?></p></div>
        <?php endif; ?>
    </header>
    
    <?php elseif( isset( $the_items ) && ! empty( $the_items ) ) : ?>
    
    <header class="codrops-header">
        <div id="coupon-msg" style="margin-bottom: 2%;">
            <?php if ( isset( $coupon_msg ) ) : ?>
            <div class="<?php echo $coupon_type; ?>"><p><?php echo $coupon_msg; ?></p></div>
            <?php endif; ?>
        </div>
        <div id="msg"></div>
    </header>
    
    <?php if ( ( isset( $update_notice ) && $update_notice ) ) : ?>
    
    <header class="codrops-header">
        <div class="notice update-notice"><p><?php echo $update_notice_msg; ?></p></div>
    </header>
    
    <?php endif; ?>

    <div class="content">
    
        <div id="content-full">
            <div class="item-total-heading">
                <h1 style="padding: 0; float: left; width: 50%;"><?php printf( _n( '%1$s artículo en el carrito', '%1$s artículos en el carrito', $total_item, 'pqc' ), $total_item ); ?></h1>
                <p style="float: right; width: 50%; margin-bottom: 0.1px;">
                    <a id="empty-cart" href="#">
                        <i style="font-size: 14px; vertical-align: middle; margin-right: 2px;" class="fa fa-remove"></i>
                        <?php _e( 'Vaciar carrito', 'pqc' ); ?>
                    </a>
                    <a id="add-more-item" href="<?php echo get_permalink( pqc_page_exists( 'pqc-upload' ) ); ?>">
                        <i style="font-size: 14px; vertical-align: middle; margin-right: 2px;" class="fa fa-cart-arrow-down"></i>&nbsp;&nbsp;<?php _e( 'Agregar artículo', 'pqc' ); ?>
                    </a>
                </p>
            </div>
            
            <form id="cart-form" method="POST" enctype="multipart/form-data">
            
                <table class="cart">
                    <thead style="background: #41377D; color: #fff;">
                        <tr>
                            <th id="canvas"><?php _e( 'Artículo', 'pqc' ); ?></th>
                            <th id="item"><?php _e( 'Detalles', 'pqc' ); ?></th>
                            <th id="quantity"><?php _e( 'Cantidad x Precio unitario', 'pqc' ); ?></th>
                            <th id="total"><?php _e( 'Total', 'pqc' ); ?></th>
                            <th id="action"><?php _e( 'Parámetros de impresión', 'pqc' ); ?></th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php $i = 0; foreach( $the_items as $the_item ) : ?>
                        <tr id="<?php echo $the_item['ID']; ?>" <?php if ( $i % 2 ) { echo 'class="even"'; } ?> data-url="<?php echo $the_item['url']; ?>">
                            <td><canvas id="cv<?php echo $i; ?>" class="cv" width="400" height="420"></canvas></td> 
                            <td class="item item-attr" id="<?php echo $the_item['ID']; ?>-attr">
                                <p class="item-name"><strong><?php echo $the_item['name']; ?></strong></p>
                                <p class="item-volume"><?php echo 'Volumen: ' . $the_item['volume']; ?> cm<sup>3</sup></p>
                                <p class="item-material">
                                <?php
                                printf( 'Material: %s - %sg/cm<sup>3</sup>',
                                $selected_material[$the_item['ID']]['name'],
                                $selected_material[$the_item['ID']]['density']
                                );
                                ?>
                                </p>
                                <!-- <p class="item-infill"><?php echo 'Infill Rate: ' . $the_item['infill']; ?>%</p>
                                <p class="item-scale"><?php echo 'Scale: ' . $the_item['scale']; ?>%</p> -->
                            </td>
                            <td class="item" id="<?php echo $the_item['ID']; ?>-cost">
                                <p>
                                    <input name="quantities[<?php echo $the_item['ID']; ?>]" type="number" value="<?php echo $the_item['quantity']; ?>" min="1" style="width: 80px; padding: 0px 8%;">
                                    <span class="cost"> x <?php echo $the_item['cost']; ?></span>
                                </p>
                            </td>
                            <td class="item" id="<?php echo $the_item['ID']; ?>-total"><p><?php echo $the_item['total']; ?></p></td>
                            <td>
                                <div style="float: left; width: 100%;">
                                    <a data-title="<?php echo $the_item['name']; ?>" title="<?php printf( __( 'Setup %s' ), $the_item['name'] ); ?>" class="edit-item">
                                        <i class="fa fa-gear"></i>
                                    </a>
                                    <a id="<?php echo $the_item['unique_id']; ?>" title="<?php printf( __( 'Remove %s from cart' ), $the_item['name'] ); ?>" class="remove-item">
                                        <i class="fa fa-trash"></i>
                                    </a>                                
                                </div>
                            </td>
                            
                            <!-- Core Data -->
                            <td class="item item-materials" style="display: none;">
                                <?php if ( $materials ) : ?>
                                <label style="float: left; width:30%;">Material</label>
                                <select name="materials[<?php echo $the_item['ID']; ?>]" style="float: right; width: 70%; font-size: 12px;">
                                    <?php
                                    foreach ( $materials as $material ) :
                                    $ID = $material->ID;
                                    $name = $material->material_name;
                                    $dens = $material->material_density . 'g/cm³';
                                    $selected = ( $material->ID == $selected_material[$the_item['ID']]['id'] ) ? 'selected="selected"' : '';
                                    ?>
                                    <option label="<?php echo "$name - $dens"; ?>" value="<?php echo $ID ?>" <?php echo $selected; ?>><?php echo "$name - $dens"; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php endif; ?>
                            </td>
                            <!-- <td class="item item-infill" style="display: none;">
                                <label style="float: left; width:30%;">Infill Rate</label>
                                <output style="float: right; text-align: center; width:14%;background: #eee;color: #000;padding: 1%; margin-left: 1%"><?php echo $the_item['infill']; ?>%</output>
                                <input style="float: right; width: 55%;" type="range" name="infills[<?php echo $the_item['ID']; ?>]" min="0" max="100" step="1" value="<?php echo $the_item['infill']; ?>">
                            </td>
                            <td class="item item-scale" style="display: none;">
                                <label style="float: left; width:30%;">Scale</label>
                                <output style="float: right; text-align: center; width:14%;background: #eee;color: #000;padding: 1%; margin-left: 1%"><?php echo $the_item['scale']; ?>%</output>
                                <input style="float: right; width: 55%;" type="range" name="scales[<?php echo $the_item['ID']; ?>]" min="100" max="1000" step="1" value="<?php echo $the_item['scale']; ?>">
                            </td> -->
                            
                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
                
                <div class="coupon">
                    <input type="text" id="coupon" name="coupon" placeholder="Coupon Code" autocomplete="off" style="width: 48%; float: left;">
                    <input type="submit" name="apply-coupon" id="apply-coupon" value="Aplicar cupón" style="width: 50%; float: right;">
                </div>
                
                <div class="update-cart">
                    <input type="submit" name="update-cart" value="Actualizar carrito" style="width: 100%; float: right;">
                </div>
            
            </form>
        </div>
        
        <div id="content-right">
        
            <div id="item-attributes">
            
                <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                    
                    <legend><?php _e( 'Cart Total', 'pqc' ); ?></legend>
                    
                    <table class="cart-total">
                        <thead>
                            <tr style="visibility: hidden;">
                                <th style="width: 40%; visibility: hidden;"></th>
                                <th style="width: 60%; visibility: hidden;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="sub-total">
                                <td>Subtotal</td>
                                <td id="subtotal"><?php echo $subtotal; ?></td>
                            </tr>
                            <?php if ( $shipping_options ) : ?>
                            <tr class="shipping">
                                <td>Envío</td>
                                <td>
                                    <div id="shipping_options">

                                        <select name="shipping_option" style="font-size: 12px; margin-top: 4%;">
                                            <option value="-1"><?php _e( '- Selecciona opciones de envío -', 'pqc' ); ?></option>
                                            <?php foreach ( $shipping_options as $shipping_option ) : ?>
                                            <option <?php if ( $current_shipping_option_id == $shipping_option['ID'] ) echo 'selected="selected"'; ?> value="<?php echo $shipping_option['ID']; ?>"><?php echo $shipping_option['title']; ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <p id="shipping-description"><?php if ( isset( $current_shipping_option ) ) echo $current_shipping_option['desc']; ?></p>
                                        
                                        <p id="shipping-cost"><?php if ( isset( $current_shipping_option ) ) echo 'Cost: ' . $current_shipping_option['cost']; ?></p>
                                                 
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                        </tbody>
                        
                        <tfoot>
                            <tr class="total">
                                <td>Total</td>
                                <td id="total"><?php echo $total; ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <form action="<?php echo get_permalink( pqc_page_exists( 'pqc-checkout' ) ); ?>" method="post" id="pqc_proceed_checkout_form">
                    
                        <input type="hidden" name="shipping_option_id">
                        
                        <input type="submit" name="pqc_proceed_checkout" value="Realizar pedido" style="margin-top: 3%;">

                    </form>
                    
                </fieldset>
                
            </div>
            
        </div>

    </div>
    
    <?php endif; ?>
    
    <div id="modal"></div>
    
    <!-- The Modal -->
    <div id="edit-item-modal">
        <!-- Modal content -->
        <div class="modal-content">
            <div class="modal-header" style="border-radius: 10px 10px 0 0;">
                <h2 style="float: left; margin: 1%; color: #fff;"></h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                
                <div class="content" style="margin: 0;" data-id="">
                
                    <header class="codrops-header" style="margin: 0 0 2% 0;">
                        <div id="msg"></div>
                    </header>
                
                    <div id="content-left">
                    
                        <div id="view3d">
                        
                            <canvas id="cv-edit-item" width="500" height="600"></canvas>
                            
                            <div id="render-modes">
                                <a href="#wireframe">Wireframe</a> |
                                <a href="#point">Puntos</a> |
                                <a href="#flat">Relleno/a>
                            </div>

                        </div>
                        
                    </div>
                    
                    <div id="content-right">
                    
                        <div id="item-attributes">
                        
                            <fieldset id="item-attributes_fieldset" class="pqc-item-fieldset">
                                
                                <legend><?php _e( 'Item Properties', 'pqc' ); ?></legend>
                                
                                <section id="attributes"></section>
                                
                                <div id="material" style="float: left; width:100%; margin: 2% 0;"></div>
                                
                                <!-- <div id="infill" style="float: left; width:100%; margin: 2% 0;"></div>
                                
                                <div id="scale" style="float: left; width:100%; margin: 2% 0;"></div> -->
                                
                                <div style="float: left; width:100%; margin: 1em 0 0 0;">
                                    <input type="button" style="float: left;width: 50%;" id="update_current_item" value="<?php _e( 'Actualizar', 'pqc' ); ?>">
                                    
                                    <span id="item-update-load">
                                        <i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
                                        <span class="sr-only"><?php _e( 'Actualizando...', 'pqc' ); ?></span>
                                    </span>
                                </div>
                                
                            </fieldset>
                            
                        </div>
                        
                    </div>

                </div>
                
            </div>
        </div>
    </div>
    
    <?php do_action( 'pqc_wrapper_cart_end' ); ?>
    
</div>