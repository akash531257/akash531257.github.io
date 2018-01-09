var GF_PFUI = GF_PFUI || {};
(function ($) {
	"use strict";
	var post_format = ['audio','gallery','link','quote','video'];
	GF_PFUI = {
		init : function() {
			GF_PFUI.switchTab();
			GF_PFUI.gallery();
		},
		switchTab : function() {
			$('.gf-post-formats-ui-tabs .tab-nav a').on('click',function(event){
				event.preventDefault();
				if ($(this).hasClass('active')) return;
				var tabId = $(this).attr('href').replace('#',''),
					$wrap = $(this).closest('.gf-post-formats-ui-tabs'),
					format = $(this).data('format');

				$('.tab-nav a',$wrap).removeClass('active');
				$('.tab-pane',$wrap).removeClass('active');

				$(this).addClass('active');
				$('[id="tab-'+ tabId +'"]',$wrap).addClass('active');

				if ($('#' + tabId).length) {
					$('#' + tabId).trigger('click');
				} else {
					$('[name="post_format"]').val(format);
				}
			});

			$('[name="post_format"]').on('change',function(){
				var format = $(this).val();
				$('a[href="#post-format-'+ format +'"]').trigger('click');

			});

			/**
			 * Hide format box
			 */
			var $formatToggle = $('[name="formatdiv-hide"]');
			if ($formatToggle.attr('checked') == 'checked') {
				$formatToggle.click();
			}
		},
		gallery: function() {
			$('.sf-field-gallery-inner','.gf-post-formats-ui-tabs').each(function () {
				var field = new SF_GalleryClass($(this));
				field.init();
			});
		}
	};
	$(document).ready(GF_PFUI.init);
})(jQuery);
