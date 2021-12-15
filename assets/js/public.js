/**
* Change window url
* 
* @param title
* @param url
*/
function change_url( title, url ) {
    
    var obj = { Title: title, Url: url };
    
    history.pushState( obj, obj.Title, obj.Url );
    
}

/**
* Format a number with grouped thousands
* 
* @param number The number being formatted
* @param decimals Sets the number of decimal points
* @param dec_point Sets the separator for the decimal point
* @param thousands_sep Sets the thousands separator
* @returns string A formatted version of number
*/
function number_format( number, decimals, dec_point, thousands_sep ) {
    // Strip all characters but numerical ones.
    number = ( number + '' ).replace( /[^0-9+\-Ee.]/g, '' );
    var n = !isFinite( +number ) ? 0 : +number,
        prec = !isFinite( +decimals ) ? 0 : Math.abs( decimals ),
        sep = ( typeof thousands_sep === 'undefined' ) ? ',' : thousands_sep,
        dec = ( typeof dec_point === 'undefined' ) ? '.' : dec_point,
        s = '',
        toFixedFix = function ( n, prec ) {
            var k = Math.pow( 10, prec );
            return '' + ( Math.round( n * k ) / k ).toFixed(prec);
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = ( prec ? toFixedFix( n, prec ) : '' + Math.round( n ) ).split( '.' );
    if ( s[0].length > 3 ) {
        s[0] = s[0].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep );
    }
    if ( ( s[1] || '' ).length < prec ) {
        s[1] = s[1] || '';
        s[1] += new Array( prec - s[1].length + 1 ).join( '0' );
    }
    return s.join( dec );
}

function parse_float( number ) {
    
    var control         = PQC_Page.money_format_control; 
        decimals        = control.decimals; 
        decimal_sep     = control.decimal_sep; 
        thousand_sep    = control.thousand_sep;
        reg             = new RegExp( /[,]/g );
        has_comma       = reg.test( number );
        
    if ( thousand_sep == '.' ) {
        
        if ( decimal_sep == ',' && has_comma === true ) {
                 
            number = ( number + '' ).replace( /[,]/g, '!' );
            number = ( number + '' ).replace( /[.]/g, ',' );
            number = ( number + '' ).replace( /[!]/g, '.' );
            
        }
        else if ( decimal_sep == '' ) {
            
            number = ( number + '' ).replace( /[.]/g, ',' );

        }
        
    }
    else {
        
        number = ( number + '' ).replace( thousand_sep, '' );
        number = decimals == 0 ? number : ( number + '' ).replace( decimal_sep, '.' );
        
    }

    number = ( number + '' ).replace( /[^0-9.]/g, '' );
    
    return parseFloat( number );    
    
}

/**
* Formats a number as a currency string
* 
* @param number The number to be formatted
* @return string Returns the formatted string
*/
function money_format( number ) {
    
    var control         = PQC_Page.money_format_control; 
        decimals        = control.decimals; 
        decimal_sep     = control.decimal_sep; 
        thousand_sep    = control.thousand_sep; 
        currency        = control.currency; 
        currency_sym    = control.currency_symbol; 
        currency_pos    = control.currency_pos;
        
    number = number_format( parseFloat( number ), decimals, decimal_sep, thousand_sep );
    
    switch ( currency_pos ) {
        case 'left' :           money = currency_sym + number; break;
        case 'left_space' :     money = currency_sym + " " + number; break;
        case 'right' :          money = number + currency_sym; break;
        case 'right_space' :    money = number + " " + currency_sym; break;
        default :               money = currency_sym + number; break;
    }
    
    return money; 
}

function shipping_sol( val ) {

    var subtotal = parse_float( jQuery( '#pqc-wrapper table.cart-total tr.sub-total td#subtotal' ).text() ),
        total   = subtotal,
        desc    = '',
        cost    = '';

    if ( val >= 1 ) {
        
        desc = PQC_Shipping[val]['desc'];
        cost = 'Cost: ' + PQC_Shipping[val]['cost'];
        total = parseFloat( PQC_Shipping[val]['amount'] ) + subtotal;
                
    }

    var object = { 'desc' : desc, 'cost' : cost, 'total' : total };
    
    return object;
    
}

function bindRemoveCoupon() {
    
    jQuery( '#pqc-wrapper a#remove-coupon' ).click( function () {
        
        jQuery( '#pqc-wrapper form#cart-form' ).append( '<input type="hidden" name="remove_coupon" value="1">' ).submit();
        
        jQuery( '#pqc-wrapper form#cart-form' ).find( 'input[name="remove_coupon"]' ).remove();
        
        return false;

    });
    
}

function removeEditModal() {
    
    var modal = jQuery( "#pqc-wrapper #edit-item-modal" );
    
    modal.animate({
        opacity : 0,
        top : "-=50"
    },
    300,
    "easeInOutSine",
    function () {
        modal.css({ opacity: 1, top : "0" }).removeClass( "loading" );
    });
    
}

function loadSTL( url ) {
    
    if ( editStlUrl == url ) return; // Return if we are still loading thesame url
    
    //3D Object loading
    editStlViewer.enableDefaultInputHandler( true );
    editStlViewer.replaceSceneFromUrl( url );
    editStlViewer.setParameter( 'InitRotationX', 90 );
    editStlViewer.setParameter( 'InitRotationY', 150 );
    editStlViewer.setParameter( 'InitRotationZ', 180 );
    editStlViewer.setParameter( 'ModelColor', '#7fca18' );
    editStlViewer.setParameter( 'BackgroundColor1', '#FFFFFF' );
    editStlViewer.setParameter( 'BackgroundColor2', '#FFFFFF' );
    editStlViewer.setParameter( 'RenderMode', 'flat' );
    editStlViewer.setParameter( 'Definition', 'high' );
    editStlViewer.init();
    editStlViewer.update();
    editStlCtx.font = '12px Courier New';
    editStlCtx.fillStyle = '#FF0000';
    editStlUrl = url;
}

/**
* Render the STL file in a specific mode
* 
* @param mode
*/
function renderSTL( mode ) {

    editStlViewer.setRenderMode( mode );
    editStlViewer.update();
    
}

function formatBytes( bytes, decimals, showUnit ) {
    
    if ( bytes == 0 ) return showUnit == true ? 0.00 + ' ' + 'Bytes' : 0.00;
    
    var k = 1000,
        dm = decimals || 3,
        sizes = [ 'Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ],
        i = Math.floor( Math.log( bytes ) / Math.log( k ) ),
        size = parseFloat( ( bytes / Math.pow( k, i ) ).toFixed( dm ) );
        
    return showUnit == true ? size + ' ' + sizes[i] : size;
}

( function( $, window, document, undefined ) {
    
    $( '#pqc-wrapper' ).tooltip();
    
    if ( PQC_Page.page == 1 ) {
    
        var input       = $( '#pqc-wrapper #pqc_file' ),
            label       = input.next( 'label' ),
            labelVal    = label.html();
            
        input.on( 'change', function( e ) {
            
            var files = $(this)[0].files,
                fileLength = files.length,
                blob,
                format;
                
            if ( fileLength > PQC.max_file_upload ) {
                
                alert( 'Max upload is ' + PQC.max_file_upload );
                
                return false;
            }
            
            for ( var i = 0; i < fileLength; i++ ) {
                
                blob = files[i];
                
                if ( blob.name.split( '.' ).pop().toLowerCase() !== 'stl' ) {
                    
                    alert( 'File type not supported for ' + blob.name );
                    
                    return false;                    
                    
                }
                
                if ( parseFloat( blob.size / ( 1024 * 1024 ) ) > parseFloat( PQC.max_file_size ) ) {
                    
                    alert( 'Maximum file size exceeded for ' + blob.name );
                    
                    return false;                    
                    
                }
                
                if ( i == 0 ) {
                    
                    if ( blob.name ) {
                        
                        label.find( 'div.pqc_fileuploader-input-caption span' ).html( blob.name );
                    
                    } else {
                        
                        label.html( labelVal );
                        
                    }
                    
                }
                else {
                    
                    label.find( 'div.pqc_fileuploader-input-caption span' ).html( fileLength + " files selected" );
                    
                }
                
                /*
                format = $( 'div.pqc_fileuploader-items ul li.pqc_fileuploader-item-format' ).clone();
                format.removeClass( 'pqc_fileuploader-item-format' ).addClass( 'pqc_fileuploader-item' );
                format.find( 'div.column-title div' ).attr( 'title', blob.name );
                format.find( 'div.column-title div' ).text( blob.name );
                format.find( 'div.column-title span' ).text( formatBytes( blob.size, 2 ) );
                         
                $( 'div.pqc_fileuploader-items ul li.pqc_fileuploader-item-format' ).after( format );
                */
                
            }
            /*
            // To be decided
            
            if ( $( 'div.pqc_fileuploader-items ul li' ).length > 6 ) {
                
                $( 'div.pqc_fileuploader-items' ).css({
                    'overflow-x' : 'hidden',
                    'overflow-y' : 'scroll',
                    'height' : '380px',
                });
            }
            else {
                
                $( 'div.pqc_fileuploader-items' ).css({
                    'overflow-x' : 'hidden',
                    'overflow-y' : 'hidden',
                    'height' : 'auto',
                });
            }
            */   
            
        });

        // Firefox bug fix
        input
        .on( 'focus', function() { input.addClass( 'has-focus' ); })
        .on( 'blur', function() { input.removeClass( 'has-focus' ); });

    }
    else if ( PQC_Page.page == 2 ) {
        
        window.editStlCanvas   = document.getElementById( 'cv-edit-item' );
        window.editStlViewer   = new JSC3D.Viewer( editStlCanvas );
        window.editStlCtx      = editStlCanvas.getContext( '2d' );
        window.editStlUrl      = '';
        
        bindRemoveCoupon();
        
        if ( PQC_Shipping.shipping_set == 0 )
            $( '#pqc-wrapper select[name="shipping_option"] option:selected' ).prop( "selected", false );
        
        $( window ).click( function (event) {
        
            var modal = $( "#pqc-wrapper #edit-item-modal" )[0];

            if ( event.target == modal ) removeEditModal();
            
        } );
        
        $( '#pqc-wrapper div#edit-item-modal span.close' ).click( function () {
            
            removeEditModal();
            
            return false;
            
        } );
        
        $( '#pqc-wrapper td a.edit-item' ).click( function ( event ) {
            
            event.preventDefault();
            
            var modal   = $( "#pqc-wrapper #edit-item-modal" ),
                tr      = $(this).parents( 'tr' );
                id      = tr.attr( 'id' );
                title   = $(this).attr( 'data-title' );
                url     = tr.attr( 'data-url' );
                volume  = tr.find( 'td.item-attr p.item-volume' )[0].outerHTML;
                // infillR = tr.find( 'td.item-attr p.item-infill' )[0].outerHTML;
                // scaleV  = tr.find( 'td.item-attr p.item-scale' )[0].outerHTML;
                material= tr.find( 'td.item-attr p.item-material' )[0].outerHTML;
                cost    = '<p class="item-cost">Unit Price: ' + money_format( parse_float( tr.find( 'td.item p span.cost' ).text() ) ) + '</p>';
                mats    = tr.find( 'td.item-materials' ).html();
                // infill  = tr.find( 'td.item-infill *' ).clone( true );
                // scale  = tr.find( 'td.item-scale *' ).clone( true );
            
            loadSTL( url );
            /** Rotate The Canvas
            setInterval( function() {
                editStlViewer.rotate( 0, 1, 0 );
                editStlViewer.update();
            }, 1 ); */
                
            modal.find( '.modal-header h2' ).text( title );            
            modal.find( '.modal-body > div.content' ).attr( 'data-id', id );
            modal.find( '.modal-body fieldset#item-attributes_fieldset section#attributes' ).html( volume + material /*+ infillR*/ + cost );
            modal.find( '.modal-body fieldset#item-attributes_fieldset div#material' ).html( mats );
            // modal.find( '.modal-body fieldset#item-attributes_fieldset div#infill' ).html( infill );
            // modal.find( '.modal-body fieldset#item-attributes_fieldset div#scale' ).html( scale );
            modal.find( 'div#msg div.success, div#msg div.error' ).remove(); // Remove message element just incase.
            modal.addClass( 'loading' );
            
            $( '#pqc-wrapper div#material select' ).on( 'change', function() {
                
                var label = $( this ).find( ':selected' ).attr( 'label' );
                
                $( this ).parents( 'fieldset#item-attributes_fieldset' ) 
                    .find( 'section#attributes p.item-material' )
                    .html( 'Material: ' + label );

                });
            
            return false;
            
        } );
        
        $( "div#render-modes a" ).click( function( evt ) {
            
            mode = $( this ).attr( "href" ).substr(1);
            
            renderSTL( mode );
            
            return false;
            
        } );
        
        $( '#pqc-wrapper select[name="shipping_option"]' ).change( function() {
            
            var object = shipping_sol( $( this ).val() );
            
            $( this ).siblings( 'p#shipping-description' ).text( object.desc );
            $( this ).siblings( 'p#shipping-cost' ).text( object.cost );
            $( '#pqc-wrapper tr.total td#total' ).text( money_format( object.total ) );
            $( '#pqc-wrapper form#pqc_proceed_checkout_form input[name="shipping_option_id"]' ).val( $( this ).val() );

        });
        
        /*
        $( '#pqc-wrapper td.item-infill input[type="range"], #pqc-wrapper td.item-scale input[type="range"]' ).on( 'input', function() {
            
            var val = $( this ).val();
            
            if ( $( this ).parent( 'div' ).attr( 'id' ) == 'infill' ) {
                
                $( this ).siblings( 'output' ).text( val + '%' );
                $( this ).parents( 'fieldset#item-attributes_fieldset' ) 
                    .find( 'section#attributes p.item-infill' )
                    .text( 'Infill Rate: ' + val + '%' );
                
            }
            else if ( $( this ).parent( 'div' ).attr( 'id' ) == 'scale' ) {
                
                $( this ).siblings( 'output' ).text( val + '%' );
                
            }

        });
        */
        
        $( '#pqc-wrapper fieldset#item-attributes_fieldset input#update_current_item' ).on( 'click', function() {
            
            $( this ).siblings( 'span#item-update-load' ).addClass( 'loading' );
            
            var parent      = $( this ).parents( 'div.content' );
                id          = parent.attr( 'data-id' );
                volume      = parent.find( 'section#attributes p.item-volume' ).html();
                // infillR     = parent.find( 'section#attributes p.item-infill' ).html();
                // scaleV      = parent.find( 'section#attributes p.item-scale' ).html();
                material    = parent.find( 'section#attributes p.item-material' ).html();
                mats        = parent.find( 'div#material select' ).val();
                // infill      = parent.find( 'div#infill input' ).val();
                // scale       = parent.find( 'div#scale input' ).val();
                
            $( '#pqc-wrapper tr#' + id + ' td.item-attr p.item-volume' ).html( volume );
            // $( '#pqc-wrapper tr#' + id + ' td.item-attr p.item-infill' ).html( infillR );
            $( '#pqc-wrapper tr#' + id + ' td.item-attr p.item-material' ).html( material );
            $( '#pqc-wrapper tr#' + id + ' td.item-materials select' ).val( mats );
            // $( '#pqc-wrapper tr#' + id + ' td.item-infill input[type="range"]' ).val( infill );
            // $( '#pqc-wrapper tr#' + id + ' td.item-scale input[type="range"]' ).val( scale );
            // $( '#pqc-wrapper tr#' + id + ' td.item-infill output' ).text( infill + '%' );
            // $( '#pqc-wrapper tr#' + id + ' td.item-scale output' ).text( scale + '%' );
            
            $( '#pqc-wrapper form#cart-form' ).submit();
                
            return false;

        });
        
        $( 'form#pqc_proceed_checkout_form' ).submit( function () {

            var shippingOption = $( '#pqc-wrapper select[name="shipping_option"]' ).val(),
                shippingOptionId = $( '#pqc-wrapper form#pqc_proceed_checkout_form input[name="shipping_option_id"]' ).val( shippingOption );
            
            if ( shippingOption >= 1 && shippingOptionId.val() >= 1 ) {
                
                return true;
                
            } else {
                
                alert( 'Please choose a shipping option.' );
                
                return false;
                
            }
            
        } );
        
        $( '#pqc-wrapper input#apply-coupon' ).click( function () {
            
            $( '#pqc-wrapper form#cart-form' ).append( '<input type="hidden" name="apply_coupon" value="1">' ).submit();
            
            $( '#pqc-wrapper form#cart-form' ).find( 'input[name="apply_coupon"]' ).remove();
            
            return false;

        });
        
        $( '#pqc-wrapper form#cart-form' ).submit( function( e ) {
           
            var object = $(this).serialize(),
                tbody = $(this).find( 'table.cart tbody' ),
                modal = $( "#pqc-wrapper #modal" );

            $.ajax({
                type : "post",
                dataType : "json",
                url : PQC_Ajax.url,
                beforeSend: function() { modal.addClass( "loading" ); },
                complete: function() { modal.removeClass( "loading" ); },
                data : { action: PQC_Ajax.action + '-update', nonce: PQC_Ajax.nonce, data: object },
                success: function( response ) {
                    
                    // console.log( response );
                    
                    if ( response.type == "success" ) {
                        
                        $.each( response.the_items, function ( index, value ) {
                            
                            // tbody.find( 'tr#' + index + ' td#' + index + '-attr p.item-volume' ).html( 'Volume: ' + value.volume + ' cm<sup>3</sup>' );
                            // tbody.find( 'tr#' + index + ' td#' + index + '-attr p.item-scale' ).html( 'Scale: ' + value.scale + '%' );
                            tbody.find( 'tr#' + index + ' td#' + index + '-cost span.cost' ).text( ' x ' + value.cost );
                            tbody.find( 'tr#' + index + ' td#' + index + '-total p' ).text( value.total );

                        } );
                        
                        // Modify Subtotal
                        $( '#pqc-wrapper table.cart-total tr.sub-total td#subtotal' ).text( response.sub_total );
                        
                        $( "#pqc-wrapper div#coupon-msg" ).find( 'div.success, div.error' ).remove(); // Remove Coupon message element just incase.
                        $( "#pqc-wrapper div#msg" ).find( 'div.success, div.error' ).remove(); // Remove message element just incase.
                        $( "#pqc-wrapper div.update-notice" ).remove(); // Remove notice message element just incase.

                        if ( response.coupon_msg != '' )
                            $( "#pqc-wrapper div#coupon-msg" ).html( '<div class="' + response.coupon_type + '"><p>' + response.coupon_msg + '</p></div>' );

                        
                        if ( response.msg != '' )
                            $( "#pqc-wrapper div#msg" ).html( '<div class="' + response.type + '"><p>' + response.msg + '</p></div>' );
                        
                        if ( $( '#pqc-wrapper span#item-update-load' ).hasClass( 'loading' ) ) {
                            
                            var parent = $( '#pqc-wrapper span#item-update-load' ).parents( 'div.content' );
                                id = parent.attr( 'data-id' );
                                
                            // parent.find( 'section#attributes p.item-volume' ).html( 'Volume: ' + response.the_items[id].volume + ' cm<sup>3</sup>' ).effect( 'pulsate' );
                            parent.find( 'section#attributes p.item-cost' ).html( 'Unit Price: ' + response.the_items[id].cost ).effect( 'pulsate' );
                            
                            $( '#pqc-wrapper span#item-update-load' ).removeClass( 'loading' );

                        }
                        else {
                            
                            $( 'html, body' ).animate( { scrollTop: $( '#pqc-wrapper' )
                                .parent()
                                .parent()
                                .parent()
                                .offset().top
                            }, 'slow' );
                            
                        }
                        
                        var shippingSol = shipping_sol( $( '#pqc-wrapper select[name="shipping_option"]' ).val() );
                        
                        $( '#pqc-wrapper tr.total td#total' ).text( money_format( shippingSol.total ) );
                        

                    }
                    else {
                        if ( response.msg == undefined ) {
                            alert( PQC_Ajax.err_msg );
                        }
                        else {
                            
                            $( "#pqc-wrapper div#coupon-msg" ).find( 'div.success, div.error' ).remove(); // Remove Coupon message element just incase.
                            $( "#pqc-wrapper div#msg" ).find( 'div.success, div.error' ).remove(); // Remove message element just incase.
                            
                            if ( response.msg != '' )
                                $( "#pqc-wrapper div#msg" ).html( '<div class="' + response.type + '"><p>' + response.msg + '</p></div>' );
                            
                            if ( $( '#pqc-wrapper span#item-update-load' ).hasClass( 'loading' ) ) {
                                
                                $( '#pqc-wrapper span#item-update-load' ).removeClass( 'loading' );

                            }
                            else {
                                
                                $( 'html, body' ).animate( { scrollTop: $( '#pqc-wrapper' )
                                    .parent()
                                    .parent()
                                    .parent()
                                    .offset().top
                                }, 'slow' );
                                
                            }
                    
                        }
                    }
                    
                    bindRemoveCoupon();
                }
            });
            
            return false;
            
        } );
        
        /**
        * For Removing Items in Cart
        * 
        */
        $( '#pqc-wrapper form#cart-form a.remove-item, #pqc-wrapper a#empty-cart' ).click( function() {
            
            var active = $(this).attr( 'id' ),
                modal = $( "#pqc-wrapper #modal" );
            
            if ( active == 'empty-cart' ) {
                
                var ids = [];
                
                $( 'a.remove-item' ).each( function() {
                    ids.push( $(this).attr( 'id' ) );    
                } );
        
            }
            else {
                
                var ids = [$(this).attr( 'id' )],
                    parent = $(this).parents( 'tr' );
        
            }

            $.ajax({
                type : "post",
                dataType : "json",
                url : PQC_Ajax.url,
                beforeSend: function() { modal.addClass( "loading" ); },
                complete: function() { modal.removeClass( "loading" ); },
                data : { action: PQC_Ajax.action + '-delete', nonce: PQC_Ajax.nonce, unique_ids: ids },
                success: function( response ) {
                    
                    if ( response.type == "success" ) {
                        
                        if ( active == 'empty-cart' ) {
                            
                            $( '#pqc-wrapper div.content' ).remove();

                            $( "#pqc-wrapper div#coupon-msg" ).find( 'div.success, div.error' ).remove(); // Remove Coupon message element just incase.
                            $( "#pqc-wrapper div#msg" ).find( 'div.success, div.error' ).remove(); // Remove message element just incase.
                            
                        }
                        else {
                        
                            var total = parent.siblings( 'tr' ).length;
                            
                            if ( total > 1 ) {
                                
                                $( 'div.item-total-heading h1' ).text( total + ' items in cart' );
                                
                            }
                            else {
                                
                                $( 'div.item-total-heading h1' ).text( total + ' item in cart' );
                                
                            }
                            
                            if ( total == 0 ) {
                                
                                parent.parents( '#pqc-wrapper div.content' ).remove();
                                
                                $( 'header.codrops-header' ).show( 'fast' );
                                
                            }
                            else {

                                parent.remove();
                                
                            }
                            
                            $( '#pqc-wrapper table.cart-total tr.sub-total td#subtotal' ).text( response.sub_total );
                            
                            var shippingSol = shipping_sol( $( '#pqc-wrapper select[name="shipping_option"]' ).val() );
                            
                            $( '#pqc-wrapper tr.total td#total' ).text( money_format( shippingSol.total ) );
                            
                            $( "#pqc-wrapper div#coupon-msg" ).find( 'div.success, div.error' ).remove(); // Remove Coupon message element just incase.
                            $( "#pqc-wrapper div#msg" ).find( 'div.success, div.error' ).remove(); // Remove message element just incase.

                            if ( response.coupon_msg != '' )
                                $( "#pqc-wrapper div#coupon-msg" ).html( '<div class="' + response.coupon_type + '"><p>' + response.coupon_msg + '</p></div>' );
                        
                        }
                        
                        if ( response.msg != '' )
                            $( "#pqc-wrapper div#msg" ).html( '<div class="' + response.type + '"><p>' + response.msg + '</p></div>' );
                        
                        $( 'html, body' ).animate( { scrollTop: $( '#pqc-wrapper' )
                            .parent()
                            .parent()
                            .parent()
                            .offset().top
                        }, 'slow' );
                        
                    }
                    else {
                    
                        alert( PQC_Ajax.err_msg );
                        
                    }
                    
                    bindRemoveCoupon();
                }
            });
            
            return false;
            
        } );

    }
    else if ( PQC_Page.page == 3 ) {
        
        $( 'a#show-cart-details' ).click( function () {
            
            $( 'div#content-full' ).slideToggle( 'slow' );
            
            return false;
        } );
        
        $( 'ul.pqc-payment-options-list li input:checked' ).attr( 'data-prev', 'selected' )
        .siblings( '.payment-options-description' ).fadeIn( 'slow' )
        .parents( 'ul' ).siblings( 'input#place-order-button, input#complete-order-button' ).fadeIn( 'fast' );

        $( 'ul.pqc-payment-options-list li input' ).change( function() {
            
            if ( $(this).is( ":checked" ) ) {
                
                $( 'ul.pqc-payment-options-list li input[data-prev="selected"]' ).removeAttr( 'data-prev' )
                .siblings( '.payment-options-description' ).hide( 'fast' )
                .parent( 'label' ).siblings( '.pqc-place-order' ).hide( 'fast' );
                
                $(this).attr( 'data-prev', 'selected' );
                    
                $(this).siblings( '.payment-options-description' ).fadeIn( 'slow' );
                
                $(this).parents( 'form#pqc_place_order' ).find( 'input#place-order-button' ).fadeIn( 'fast' );
                $(this).parents( 'form#pqc_place_order' ).find( 'input#complete-order-button' ).fadeIn( 'fast' );
                
            }

        });
        
        $( 'form#pqc_place_order' ).submit( function () {

            var paymentMethod   = $( this ).find( 'ul.pqc-payment-options-list li input:checked' ).val();
                firstname       = $( this ).find( 'fieldset#item-attributes_fieldset input[name="firstname"]' );
                lastname        = $( this ).find( 'fieldset#item-attributes_fieldset input[name="lastname"]' );
                email           = $( this ).find( 'fieldset#item-attributes_fieldset input[name="email"]' );
                address         = $( this ).find( 'fieldset#item-attributes_fieldset input[name="address"]' );
                city            = $( this ).find( 'fieldset#item-attributes_fieldset input[name="city"]' );
                zipcode         = $( this ).find( 'fieldset#item-attributes_fieldset input[name="zipcode"]' );
                state           = $( this ).find( 'fieldset#item-attributes_fieldset input[name="state"]' );
            
            if ( paymentMethod == '' || paymentMethod == null || paymentMethod == 'undefined' ) {
                
                if ( PQC_Page.checkout_option == 2 ) return true;
                
                alert( 'Please choose a payment option.' );
                
                return false;
                
            }
            else {
                
                var error = 0;
                
                if ( firstname.val() == '' || firstname.val().length < 3 ) {
                    error = 1;
                    firstname.removeClass( 'buyer-details-success' );
                    firstname.addClass( 'buyer-details-error' );
                }
                else {
                    firstname.removeClass( 'buyer-details-error' );
                    firstname.addClass( 'buyer-details-success' );
                }
                
                if ( lastname.val() == '' || lastname.val().length < 3 ) {
                    error = 1;
                    lastname.removeClass( 'buyer-details-success' );
                    lastname.addClass( 'buyer-details-error' );
                }
                else {
                    lastname.removeClass( 'buyer-details-error' );
                    lastname.addClass( 'buyer-details-success' );
                }
                
                if ( email.val() == '' || email.val().length < 3 ) {
                    error = 1;
                    email.removeClass( 'buyer-details-success' );
                    email.addClass( 'buyer-details-error' );
                }
                else {
                    email.removeClass( 'buyer-details-error' );
                    email.addClass( 'buyer-details-success' );
                }
                
                if ( address.val() == '' || address.val().length < 3 ) {
                    error = 1;
                    address.removeClass( 'buyer-details-success' );
                    address.addClass( 'buyer-details-error' );
                }
                else {
                    address.removeClass( 'buyer-details-error' );
                    address.addClass( 'buyer-details-success' );
                }
                
                if ( city.val() == '' ) {
                    error = 1;
                    city.removeClass( 'buyer-details-success' );
                    city.addClass( 'buyer-details-error' );
                }
                else {
                    city.removeClass( 'buyer-details-error' );
                    city.addClass( 'buyer-details-success' );
                }
                
                if ( zipcode.val() == '' || zipcode.val().length < 4 ) {
                    error = 1;
                    zipcode.removeClass( 'buyer-details-success' );
                    zipcode.addClass( 'buyer-details-error' );
                }
                else {
                    zipcode.removeClass( 'buyer-details-error' );
                    zipcode.addClass( 'buyer-details-success' );
                }
                
                if ( state.val() == '' ) {
                    error = 1;
                    state.removeClass( 'buyer-details-success' );
                    state.addClass( 'buyer-details-error' );
                }
                else {
                    state.removeClass( 'buyer-details-error' );
                    state.addClass( 'buyer-details-success' );
                }
                
                if ( error == 0 ) {
                    return true;
                }
                else {
                    $( 'html, body' ).animate( { scrollTop: $( '#pqc-wrapper div#item-attributes' )
                        .parent()
                        .parent()
                        .parent()
                        .offset().top
                    }, 'slow', function () { alert( 'Please input shipping details.' ); return false; } );
                    
                    return false;
                }
                
            }
            
        } );
        
        
    }
    else if ( PQC_Page.page == 4 ) {
        
        var url = new URI(),
            obj = {
                order_id: undefined,
                order_action: undefined,
                order_request_sent: undefined,
                order_status: undefined,
                payment_method: undefined,
                paymentId: undefined,
                PayerID: undefined,
                token: undefined,
                stripeEmail: undefined,
                stripeToken: undefined,
                _wpnonce: undefined,
                'pqc-setup': undefined
            };

        url.removeQuery( obj );

        change_url( '', url );
        
    }
    
})
( jQuery, window, document );