jQuery.noConflict();

jQuery(document).ready(function($) {
    console.log( "ready for nx_variation!" );
    $( ".variations" ).addClass( "nx_variation" );
    $(".single_variation_wrap").addClass("nx_variation_wrap");

    $('.nx_variation__container').on( 'click', '.nx_variation__button', function ( e ) {
        // clicked swatch
        const el = $( this );
        // original select dropdown with variations
        const select = el.closest( '.value' ).find( 'select' );
        // this specific term slug, i.e color slugs, like "coral", "grey" etc
        const value = el.data( 'value' );

        // do three things
        el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
        select.val( value );
        select.change();

    });
});
