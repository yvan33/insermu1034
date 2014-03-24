/**
 * Front Javascript for "PubMed Posts" Wordpress plugin
 */
 
(function($) {
	$(document).ready(function() {
	
		// Tags field
		$('.post-tags').textext({ 
			plugins: 'tags arrow prompt autocomplete',
			prompt: pubMedPosts.tagsText,
			list: $.parseJSON(pubMedPosts.tags),
		})
		.bind('getSuggestions', function(e, data) {
			var list = $.parseJSON(pubMedPosts.tags);
			var textext = $(e.target).textext()[0];
			var query = (data ? data.query : '') || '';
			$(this).trigger(
				'setSuggestions',
				{ result : textext.itemManager().filter(list, query) }
			);
    })

		// Show options button
		$('.pubmed-show-advanced, .pubmed-hide-advanced').click(function() {
			var $form = $(this).closest('form');
			if ( $form.find('.pubmed-advanced').is(":visible") ) {
				$form.find('.pubmed-hide-advanced').addClass('pubmed-hide');
				$form.find('.pubmed-show-advanced').removeClass('pubmed-hide');
				$form.find('.pubmed-search').val('simple');
				$form.find('.pubmed-advanced').addClass('pubmed-hide');
				$form.find('.pubmed-keyword').removeClass('pubmed-hide');
			} else {
				$form.find('.pubmed-hide-advanced').removeClass('pubmed-hide');
				$form.find('.pubmed-show-advanced').addClass('pubmed-hide');
				$form.find('.pubmed-search').val('advanced');
				$form.find('.pubmed-keyword').addClass('pubmed-hide');
				$form.find('.pubmed-advanced').removeClass('pubmed-hide');
				// Refresh bounds of tags fields to fix overlap
				var $tags = $('.post-tags').textext();
				for (var i = 0; i < $tags.length; i++) {
					$tags[i].tags().core().invalidateBounds();
				}
			}
		});
		
		// Reset button
		$('.pubmed-reset').click(function() {
			var $form = $(this).closest('form');
			$form.find('.pubmed-fields input, .pubmed-fields textarea').val('');
		});
		
		// Convert values to tags
		$('.post-tags').each(function() {
			var value = $(this).val();
			if (value != '') {
				var tags = JSON.parse($(this).val());
				$(this).val('');
				$(this).textext()[0].tags().addTags(tags);
			}
		});
		
	});
})(jQuery); 