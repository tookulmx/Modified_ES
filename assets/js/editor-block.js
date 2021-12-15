/**
 * For Guternberg Editor
 */

'use strict';

(function (element, blocks, i18n) {

    let el = element.createElement;

    blocks.registerBlockType('phanes3dp/block', {
        title: 'Phanes 3DP',
        description: i18n.__('Phanes3DP Multiverse Shortcode Creator', 'pqc'),
        icon: 'editor-code',
        category: 'embed',

        save() { return el( 'div', null, '[phanes3dp]'); }
    });

})(
    window.wp.element,
    window.wp.blocks,
    window.wp.i18n
);