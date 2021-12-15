jQuery(document).ready(function ($) {
    
    "use strict";
    
    StripeCheckout.open({
        key:            PQC_STRIPE.pub_key,
        name:           PQC_STRIPE.name,
        panelLabel:     PQC_STRIPE.button,
        currency:       PQC_STRIPE.currency,
        description:    PQC_STRIPE.desc,
        amount:         parse_float( PQC_STRIPE.price ),
        locale:         'auto',
        token: function( token ) {
            
            var stripeEmail     = '<input name="stripeEmail" value="' + token.email + '" type="hidden">',
                stripeToken     = '<input name="stripeToken" value="' + token.id + '" type="hidden">',
                orderID         = '<input name="order_id" value="' + PQC_STRIPE.order_id + '" type="hidden">',
                orderStatus     = '<input name="order_status" value="complete" type="hidden">',
                paymentMethod   = '<input name="payment_method" value="stripe" type="hidden">',
                form            = jQuery( '<form id="#pqc_stripe_process_form" method="GET"></form>' );
            
            form.attr( 'action', PQC_STRIPE.form_action )
                .append( stripeEmail )
                .append( stripeToken )
                .append( orderID )
                .append( orderStatus )
                .append( paymentMethod )
                
            jQuery( 'body' ).append( form );
            
            form.submit();
        }  
    });
    
});