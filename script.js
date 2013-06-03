jQuery(function() {

    var forms = jQuery('.plugin_log');

    if (forms.length === 0) {
        return;
    }

    function handleEvent( form ) {
         var loading;
         var ajax = new jQuery.ajax({
			'type': 'post',
			'url': form.action,
			'data': jQuery( form ).serialize(),
			'success': function( data ){
                var rootTag = jQuery('<root>');
                jQuery(data).appendTo(rootTag);
                var listitem = rootTag.find('.dokuwiki form.plugin_log').parents('ul').find('li:eq(1)');

                jQuery( form )
					.parents( 'li' )
					.after( listitem )
					.find( '.edit' ).val( '' );
				
				loading && loading.remove() 
			},
			'error': function(){
				alert(this.response);
				loading.remove();
			}
		 });
         loading = jQuery('<img/>', {
			'src': DOKU_BASE+'lib/images/throbber.gif',
			'alt': '...',
			'class': 'load',
			'css': {
				'margin-bottom': '-5px'
			}
		 }).appendTo( form )
     }

	jQuery( forms ).each(function(idx, el){
		var el = el;
		jQuery( el ).find( '.button' ).click(function(e){
			e.preventDefault();
			handleEvent( el );
		})
	})
});
