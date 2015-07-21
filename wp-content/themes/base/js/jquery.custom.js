/*-----------------------------------------------------------------------------------

 	Custom JS - All front-end jQuery

-----------------------------------------------------------------------------------*/

;(function($) {
	"use strict";

	$(document).ready(function($) {

		var $window = $(window);

		/* --------------------------------------- */
		/* ZillaMobileMenu & Superfish
		/* --------------------------------------- */
		$('#primary-menu')
			.zillaMobileMenu({
				breakPoint: 768,
				hideNavParent: true,
				onInit: function( menu ) {
					$(menu).removeClass('zilla-sf-menu primary-menu');
				}
			})
			.superfish({
		    		delay: 200,
		    		animation: {opacity:'show', height:'show'},
		    		speed: 'fast',
		    		cssArrows: false,
		    		disableHI: true
			});

		/* --------------------------------------- */
		/* Cycle - Slideshow media
		/* --------------------------------------- */
		if( $().cycle ) {
			var $sliders = $('.slideshow');
			$sliders.each(function() {
				var $this = $(this),
					prev = $this.next().find('.zilla-slide-prev'),
					next = $this.next().find('.zilla-slide-next');

				$this.cycle({
					autoHeight: 0,
					slides: '> li',
					swipe: true,
					timeout: 0,
					prev: prev,
					next: next
				});

				if( $('body').hasClass('single-post') ) {
					$this.on('cycle-update-view', function(e,o,sh,cs) {
						var $cs = $(cs);

						$(this).animate({
							height: $cs.height()
						}, 300);
					});
				}
			});
		}

		// /* --------------------------------------- */
		// /* Masonry - Portfolio media
		// /* --------------------------------------- */
		// if( $().masonry ) {
		// 	var $wall = $('.single-portfolio .zilla-gallery'),
		// 		getColumnWidth;

		// 	getColumnWidth = function() {
		// 		var width = 0,
		// 			windowWidth = $window.width();

		// 		if( $wall.length ) {
		// 			if( windowWidth <= 400 ) {
		// 				width = Math.floor( $wall.width() );
		// 			} else {
		// 				width = Math.floor( $wall.width() / 2 );
		// 			}
		// 		}

		// 		return width;
		// 	}

		// 	$wall.imagesLoaded( function() {
		// 		$wall.masonry({
		// 			columnWidth: getColumnWidth(),
		// 			itemSelector: 'li'
		// 		});
		// 	});

		// 	$window.on("resize", function() {
		// 		$wall.masonry( 'option', { columnWidth: getColumnWidth() } );
		// 	});
		// }

		/* --------------------------------------- */
		/* Responsive media - FitVids
		/* --------------------------------------- */
		if( $().fitVids ) {
			$('#content').fitVids();
		} /* FitVids --- */

	});

})(window.jQuery);