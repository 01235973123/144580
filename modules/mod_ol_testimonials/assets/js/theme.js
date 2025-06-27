

(function ($) {
	'use strict';



	// ===============================================
	// OWL Carousel
	// Source: http://www.owlcarousel.owlgraphic.com
	// ===============================================

	$(window).on('load', function() { // fixes Owl Carousel "autoWidth: true" issue (https://github.com/OwlCarousel2/OwlCarousel2/issues/1139).

		$('.owl-carousel').each(function(){
			var $carousel = $(this);
			$carousel.owlCarousel({

				items: $carousel.data("items"),
				loop: $carousel.data("loop"),
				margin: $carousel.data("margin"),
				center: $carousel.data("center"),
				startPosition: $carousel.data("start-position"),
				animateIn: $carousel.data("animate-in"),
				animateOut: $carousel.data("animate-out"),
				autoWidth: $carousel.data("autowidth"),
				autoHeight: $carousel.data("autoheight"),
				autoplay: $carousel.data("autoplay"),
				autoplayTimeout: $carousel.data("autoplay-timeout"),
				autoplayHoverPause: $carousel.data("autoplay-hover-pause"),
				autoplaySpeed: $carousel.data("autoplay-speed"),
				nav: $carousel.data("nav"),
				navText: ['', ''],
				navSpeed: $carousel.data("nav-speed"),
				dots: $carousel.data("dots"),
				dotsSpeed: $carousel.data("dots-speed"),
				mouseDrag: $carousel.data("mouse-drag"),
				touchDrag: $carousel.data("touch-drag"),
				dragEndSpeed: $carousel.data("drag-end-speed"),
				lazyLoad: true,
				video: false,
				responsive: {
					0: {
						items: $carousel.data("mobile-portrait"),
						center: false,
					},
					480: {
						items: $carousel.data("mobile-landscape"),
						center: false,
					},
					768: {
						items: $carousel.data("tablet-portrait"),
						center: false,
					},
					992: {
						items: $carousel.data("tablet-landscape"),
					},
					1200: {
						items: $carousel.data("items"),
					}
				}

			});

			// Mousewheel plugin
			var owl = $('.owl-mousewheel');
			owl.on('mousewheel', '.owl-stage', function (e) {
				if (e.deltaY > 0) {
					owl.trigger('prev.owl', [800]);
				} else {
					owl.trigger('next.owl', [800]);
				}
				e.preventDefault();
			});

		});

	});


	// CC item hover
	$('.cc-item').on('mouseenter',function() {
		$('.owl-carousel').addClass('cc-item-hovered');
	});
	$('.cc-item').on('mouseleave',function() {
		$('.owl-carousel').removeClass('cc-item-hovered');
	});

	// If ".cc-caption" exist add class "cc-caption-on" to ".cc-item".
	$('.cc-item').each(function() {
		if ($(this).find('.cc-caption').length) {
			$(this).addClass('cc-caption-on');
		}
	});

})(jQuery); 
