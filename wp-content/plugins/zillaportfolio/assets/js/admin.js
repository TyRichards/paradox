(function($) {
	"use strict";

	// Show/Hide Metaboxes as needed
	$(function() {
		var metaboxes = [
			{ 
				'handle' : $('#_tzp_display_gallery'),
				'metabox' : $('#tzp-portfolio-metabox-gallery') 
			},
			{ 
				'handle' : $('#_tzp_display_audio'),
				'metabox' : $('#tzp-portfolio-metabox-audio') 
			},
			{ 
				'handle' : $('#_tzp_display_video'),
				'metabox' : $('#tzp-portfolio-metabox-video') 
			}
		];

		for( var i = 0 ; i < metaboxes.length ; i++ ) {
			if( metaboxes[i].handle.is(':checked') ) {
				metaboxes[i].metabox.css('display', 'block');
			} else {
				metaboxes[i].metabox.css('display', 'none');
			}

			metaboxes[i].handle.on('click', function() {
				var $this = $(this),
						metaboxId = '#' + $this.data('related-metabox-id');
						
				if( $this.is(':checked') ) {
					$(metaboxId).css('display', 'block');
				} else {
					$(metaboxId).css('display', 'none');
				}
			});
		}
	});

	// Media Manager for Galleries
	$(function() {
		var frame,
		    images = $('#_tzp_gallery_images_ids').val(),
		    selection = loadImages(images);

		$('._tzp_gallery_upload_button').on('click', function(e) {
			e.preventDefault();

			// Set options for 1st frame render
			var options = {
				title: zillaportfolio.createGalleryText,
				state: 'gallery-edit',
				frame: 'post',
				selection: selection
			};

			// Check if frame or gallery already exist
			if( frame || selection ) {
				options['title'] = zillaportfolio.editGalleryText;
			}

			frame = wp.media(options).open();
			
			// Tweak views
			frame.menu.get('view').unset('cancel');
			frame.menu.get('view').unset('separateCancel');
			frame.menu.get('view').get('gallery-edit').el.innerHTML = zillaportfolio.editGalleryText;
			frame.content.get('view').sidebar.unset('gallery'); // Hide Gallery Settings in sidebar

			// When we are editing a gallery
			overrideGalleryInsert();
			frame.on( 'toolbar:render:gallery-edit', function() {
				overrideGalleryInsert();
			});
			
			frame.on( 'content:render:browse', function( browser ) {
		    if ( !browser ) return;
		    // Hide Gallery Settings in sidebar
		    browser.sidebar.on('ready', function(){
	        browser.sidebar.unset('gallery');
		    });
		    // Hide filter/search as they don't work
		    browser.toolbar.on('ready', function(){
			    if(browser.toolbar.controller._state == 'gallery-library'){
		        browser.toolbar.$el.hide();
			    }
		    });
			});
			
			// All images removed
			frame.state().get('library').on( 'remove', function() {
		    var models = frame.state().get('library');
				if(models.length == 0){
			    selection = false;
					$.post(ajaxurl, { ids: '', action: 'tzp_save_gallery_images', post_id: zilla_ajax.post_id, nonce: zilla_ajax.nonce });
				}
			});
			
			// Override insert button
			function overrideGalleryInsert() {
				frame.toolbar.get('view').set({
					insert: {
						style: 'primary',
						text: zillaportfolio.saveGalleryText,
						click: function() {
							var models = frame.state().get('library'),
						    ids = '';

							models.each( function( attachment ) {
						    ids += attachment.id + ','
							});

							this.el.innerHTML = zillaportfolio.savingGalleryText;
								
							$.ajax({
								type: 'POST',
								url: ajaxurl,
								data: { 
									ids: ids, 
									action: 'tzp_save_gallery_images', 
									post_id: zillaportfolio.post_id, 
									nonce: zillaportfolio.nonce 
								},
								success: function() {
									selection = loadImages(ids);
									$('#_tzp_gallery_images_ids').val( ids );
									frame.close();
								},
								dataType: 'html'
							}).done( function( data ) {
								$('#_tzp_gallery_images').html( data );
							}); 
						}
					}
				});
			}
		});
		
		// Load images
		function loadImages(images) {
			if( images ){
		    var shortcode = new wp.shortcode({
  					tag:    'gallery',
  					attrs:   { ids: images },
  					type:   'single'
  			});

		    var attachments = wp.media.gallery.attachments( shortcode );

				var selection = new wp.media.model.Selection( attachments.models, {
  					props:    attachments.props.toJSON(),
  					multiple: true
  				}
  			);
      
				selection.gallery = attachments.gallery;
      
				// Fetch the query's attachments, and then break ties from the
				// query to allow for sorting.
				selection.more().done( function() {
					// Break ties with the query.
					selection.props.set({ query: false });
					selection.unmirror();
					selection.props.unset('orderby');
				});
      				
				return selection;
			}	
			return false;
		}
	});

	// Media manager for image insert
	$(function() {
		var frame;

		$('.tzp-upload-file-button').on('click', function(e) {
			e.preventDefault();

			// Set options for 1st frame render
			var $this = $(this),
				$input = $this.siblings('.file'),
				options = {
					state: 'insert',
					frame: 'post'
				};

			frame = wp.media(options).open();
			
			// Tweak views
			frame.menu.get('view').unset('gallery');
			frame.menu.get('view').unset('featured-image');
									
			frame.toolbar.get('view').set({
				insert: {
					style: 'primary',
					text: zillaportfolio.insertText,

					click: function() {
						var models = frame.state().get('selection'),
							url = models.first().attributes.url;

						$input.val( url ); 

						frame.close();
					}
				}
			});
		});
	});

})(jQuery);