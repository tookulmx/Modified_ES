jQuery(document).ready( function( $ ) {

    if ( typeof PQC_Admin !== 'undefined' ) {
        
        if ( PQC_Admin.do_url_mod == true ) {
    
            var url = new URI(),
                obj = PQC_Admin.url_params;
                
                /**
                * Change window url
                * 
                * @param title
                * @param url
                */
                change_url = function( title, url ) {
                    
                    var obj = { Title: title, Url: url };
                    
                    history.pushState( obj, obj.Title, obj.Url );
                    
                }
                
            url.removeQuery( obj );

            change_url( '', url );
            
        }    
    }
    
});