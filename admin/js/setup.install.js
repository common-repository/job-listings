jQuery(document).ready(function($) {
	$('.correct-setting').on('click', function(event) {
		event.preventDefault();
		var title = $(this).data('title');
		var content = $(this).data('content');
		var page_template = $(this).data('page-template');
		var setting_group = $(this).data('setting-group');
		var setting_key = $(this).data('setting-key');
		$this = $(this);
		var data = {
			action : 'jlt_setup_page',
			title : title,
			content : content,
			page_template : page_template,
			setting_group : setting_group,
			setting_key : setting_key
		}
		$.post( jltSetup.ajax_url, data, function( result ) {
			result = $.parseJSON(result);
			$this.closest('tr').find('span.error').html( result.id + ' - /' + result.slug + '/' ).removeClass('error').addClass('yes');
			$this.closest('.button').hide();
		});
	});
	$('.help_tip').tooltip();
});