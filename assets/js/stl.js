( function( $, window, document, undefined ) {

    //Function Definitions
    //====================================================================
    function loadSTL() {
        
        //3D Object loading
        $.each( PQC.url, function( index, value ) {
            var canvas = document.getElementById( 'cv' + index );
            var viewer = new JSC3D.Viewer( canvas );
            var ctx = canvas.getContext( '2d' );
            
            viewer.enableDefaultInputHandler( true );
            viewer.replaceSceneFromUrl( value );
            viewer.setParameter( 'InitRotationX', 105 );
            viewer.setParameter( 'InitRotationY', 150 );
            viewer.setParameter( 'InitRotationZ', 180 );
            viewer.setParameter( 'ModelColor', '#7fca18' );
            viewer.setParameter( 'BackgroundColor1', '#FFFFFF' );
            viewer.setParameter( 'BackgroundColor2', '#FFFFFF' );
            viewer.setParameter( 'RenderMode', 'flat' );
            viewer.setParameter( 'Definition', 'high' );
            viewer.init();
            viewer.update();
            ctx.font = '12px Courier New';
            ctx.fillStyle = '#FF0000';
            // setInterval( rotateThatShit( viewer ), 10 );    
        } );
    }
    
    //END Function Definitions
    //====================================================================

    loadSTL();
    
}) ( jQuery, window, document );