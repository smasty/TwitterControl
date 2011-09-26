var TwitterControl = {

	init: function(){
		// Intents
		jQuery('.TwitterControl .intents a').click(function(){
			return !window.open(this.href, null, 'width=550,height=420');
		});

		// Media
		jQuery('.TwitterControl a.media').click(function(){
			var type = jQuery(this).attr('data-media-type');

			if(type == 'photo')
				return !TwitterControl.previewMedia(jQuery(this).attr('data-media-url'));

			return true;

		});
		jQuery(document).keyup(function(e) {
			if(e.keyCode == 27) // ESC
				TwitterControl.closePreview();
		});
	},

	previewMedia: function(url){
		if(!url) return false;

		jQuery('body').append(
			jQuery('<div id="TwitterControl-preview" style="display:none"/>')
				.append('<div class="preview"/>')
				.append('<div class="overlay"/>')
				.click(function(){
					TwitterControl.closePreview();
				})
		);

		var preview = $('#TwitterControl-preview .preview');
		preview.append('<img src="' + url + '">');
		preview.css({left: (window.innerWidth/2) - (preview.find('img')[0].width/2)});

		jQuery('#TwitterControl-preview').fadeIn(100);
		return true;
	},

	closePreview: function(){
		if(jQuery('#TwitterControl-preview').length > 0){
			jQuery('#TwitterControl-preview').fadeOut(100, function(){
				jQuery(this).remove();
			});
		}
	}

};

jQuery(function(){

	TwitterControl.init();

});