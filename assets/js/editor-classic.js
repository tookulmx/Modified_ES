/**
 * For Classic Editor
 */

'use strict';

(function (i18n) {

    tinymce.PluginManager.add('pqc', function (editor, url) {
    	// Add a button that will insert the shortcode
    	editor.addButton('pqc', {
    		icon: 'icon dashicons-editor-code',
    		text: ' Phanes 3DP',
    		tooltip: i18n.__('Phanes3DP Multiverse Shortcode Creator', 'pqc'),
    		onclick: function () {
    			var sc = wp.shortcode.string({
    				tag: 'phanes3dp',
    				attrs: {},
    				type: 'single'
    			});

    			editor.insertContent(sc);
    		}
    	});
    });
    
}) ( window.wp.i18n );