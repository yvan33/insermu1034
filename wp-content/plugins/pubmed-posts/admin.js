/**
 * Admin Javascript for "PubMed Posts" Wordpress plugin
 */

(function($) {
	$(document).ready(function() {
	
		// Remove PMID placeholder
		$('#pubmed-pmid').focus(function() {
			var value = $(this).val();
			if (pubMedPosts.PMIDText == value) {
				$(this).val('').css('color', 'inherit');
			}
		});
		
		// Restore PMID placeholder
		$('#pubmed-pmid').blur(function() {
			var value = $(this).val();
			if ('' == value) {
				$(this).val(pubMedPosts.PMIDText).css('color', '#bbb');
			}
		});	

		// Categories field
		$('#pubmed-categories').multiselect({
			selectedText: pubMedPosts.selectedText,
			noneSelectedText: "<span class='pubmed-none'>" + pubMedPosts.noneSelectedText + "</span>"
		}).multiselect('uncheckAll');
	
		// Tags field
		$('#pubmed-tags').textext({ 
			plugins: 'tags arrow prompt autocomplete',
			prompt: pubMedPosts.tagsText,
			list: $.parseJSON(pubMedPosts.tags)
		})
		.bind('getSuggestions', function(e, data) {
			var list = $.parseJSON(pubMedPosts.tags),
			textext = $(e.target).textext()[0],
			query = (data ? data.query : '') || '';
			$(this).trigger(
				'setSuggestions',
				{ result : textext.itemManager().filter(list, query) }
			);
    });

		// Publish button
		$('.pubmed-publish').click(function() {
			createPost('publish');
		});
		
		// Draft button
		$('.pubmed-draft').click(function() {
			createPost('draft');
		});
		
		// Reset button
		$('.pubmed-reset').click(function() {
			$('#pubmed-pmid').val('').blur();	
			$('.text-remove').click();
			$('#pubmed-tags').blur();
			$('#pubmed-categories').multiselect('uncheckAll');			
			$('.pubmed-waiting').hide();
			$('.pubmed-message').hide();
		});	
		
		// Create posts
		function createPost(status) {
			var nonce = $('#pubmed-nonce').val();
			var pmid = $('#pubmed-pmid').val();	
			var categories = $('#pubmed-categories').multiselect('getChecked').map(function() {
				return this.value;    
			}).get();
			categories = JSON.stringify(categories);
			var tags = $('input[name="pubmed-tags"]').val();	
			var data = {
				action: 'pubmed-posts',
				nonce: nonce,
				pmid: pmid,
				categories: categories,
				tags: tags,
				status: status
			};
			$('.pubmed-waiting').show();
			$.post(ajaxurl, data, function(response) {
				$('.pubmed-waiting').hide();
				$('.pubmed-message').html(response).show();
			});		
		}
		
	});
})(jQuery)