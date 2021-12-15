jQuery(document).ready( function( $ ) {

    $( "#expiry_date" ).datepicker({
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        minDate: 0,
    });

    if ( ! $( 'div.wrap' ).hasClass( 'pqc-wrapper' ) ) $( 'div.wrap' ).addClass( 'pqc-wrapper' );
    $( 'th#order_title.column-order_title, .order_title.column-order_title' ).addClass( 'column-primary' );

    $( 'div.pqc-wrapper' ).prepend( $( 'div.pqc-inner-notice' ) );

    $( 'input[name="pqc-license-code[]"]' ).on( 'keyup', function() {

        var val = $(this).val();

        if ( val.length == 4 )
            $(this).next( 'input[name="pqc-license-code[]"]' ).focus();

    } )

    /**
    * For Editing
    *
    */
    $( 'div.row-actions a.editinline.material' ).click( function ( event ) {

        event.preventDefault();

        $( 'h2#edit-material' ).show();
        $( 'h2#add-material' ).hide();

        $( "input[name='pqc_material_add']" ).hide();
        $( "input[name='pqc_material_update']" ).show();
        $( "button#cancel_edit" ).show();

        // Get needed data from table
        var parent = $(this).parents( 'tr' ),
            id = $(this).attr( 'data-id' ),
            materialName = parent.find( 'td.material_name input.material_name_hidden' ).val(),
            materialDesc = parent.find( 'td.material_name input.material_description_hidden' ).val(),
            materialCost = parent.find( 'td.material_name input.material_cost_hidden' ).val();
            materialDens = parent.find( 'td.material_name input.material_density_hidden' ).val();

        // Pass needed data to edit form
        $( 'form#addmaterial' ).find( 'input#pqc_id' ).val( id );
        $( 'form#addmaterial' ).find( "input#pqc_material_name" ).val( materialName );
        $( 'form#addmaterial' ).find( "textarea#pqc_material_description" ).val( materialDesc );
        $( 'form#addmaterial' ).find( "input#pqc_material_cost" ).val( materialCost );
        $( 'form#addmaterial' ).find( "input#pqc_material_density" ).val( materialDens );

        return false;

    } );

    $( 'button#cancel_edit' ).click( function () {

        $( 'h2#edit-quote' ).hide();
        $( 'h2#edit-material' ).hide();
        $( 'h2#add-quote' ).show();
        $( 'h2#add-material' ).show();

        $( "input[name='pqc_quote_add']" ).show();
        $( "input[name='pqc_material_add']" ).show();
        $( "input[name='pqc_quote_update']" ).hide();
        $( "input[name='pqc_material_update']" ).hide();

        $( 'form#addmaterial' ).find( "input#pqc_material_name" ).val( '' );
        $( 'form#addmaterial' ).find( "textarea#pqc_material_description" ).val( '' );
        $( 'form#addmaterial' ).find( "input#pqc_material_cost" ).val( '' );
        $( 'form#addmaterial' ).find( "input#pqc_material_density" ).val( '' );

        $( "button#cancel_edit" ).hide();

        return false;
    } );

	var $items_table = $( 'div.order_options.pqc_options_panel' )
		.find( 'table.cart' ),
		$thead_cb = $items_table.find( 'thead th#cb input' ),
		$tbody_cb = $items_table.find( 'tbody td.cb input.cb-select' );

	$thead_cb.prop('checked', false);

	$thead_cb.change( function(ev) {
		if ( $thead_cb.is(":checked") ) {
			$tbody_cb.prop('checked', true).val(1);
		} else {
			$tbody_cb.prop('checked', false).val(0);
		}
	} ).change();

	$tbody_cb.change( function(ev) {
		var current = $(this);

		if ( current.is(":checked") ) {
			current.prop('checked', true).val(1);
		} else {
			current.prop('checked', false).val(0);
		}
	} );

});
