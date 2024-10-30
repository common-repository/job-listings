
function toggle_disable_custom_field(el) {
	$this = jQuery(el);
	var disabledEl = $this.siblings('input[type="hidden"]');
	var parentEl = $this.closest('tr');
	if( disabledEl.val() == 'yes' ) {
		disabledEl.val('no');
		$this.prop('value', jltCustomFieldL10n.disable_text);
		parentEl.find('input[type="text"], input[type="checkbox"], input[type="radio"], select, textarea').removeAttr( "readonly" );
		parentEl.removeClass('jlt-disable-field');
	} else {
		disabledEl.val('yes');
		$this.prop('value', jltCustomFieldL10n.enable_text);
		parentEl.find('input[type="text"], input[type="checkbox"], input[type="radio"], select, textarea').attr( "readonly", "readonly" );
		parentEl.addClass('jlt-disable-field');
	}
	
}

function delete_custom_field(el){
	jQuery(el).closest('tr').remove();
	return false;
}

jQuery( document ).ready( function ( $ ) {
	// Clone Education, Experience and Skill
	$(".jlt-clone-fields").on("click", function() {
		var $this = $(this);
		var $template = $( $this.data('template') );
		$template.find(".jlt-remove-fields").on("click", remove );
		$this.parents('.jlt-addable-fields').find('tbody').append( $template );
	});

	function remove() {
		$(this).parents('tr').remove();
	}

	$(".jlt-remove-fields").on("click", remove);

	// Custom field for resume
	$(".jlt_custom_field_table").sortable({
		'items': 'tbody tr',
		'axis': 'y',
		placeholder: "jlt-state-highlight"
	});
	
	$('#add_custom_field').click(function(){
		var table = $('.jlt_custom_field_table'),
			n = 0,
			num = table.data('num'),
			field_name = table.data('field_name');
		
		n = num + 1;
		var tmpl = jltCustomFieldL10n.custom_field_tmpl.replace( /__i__|%i%/g, n );
		tmpl = tmpl.replace( /__name__/g, field_name );
		table.append(tmpl);
		table.data('num',n);
		
		$(".jlt_custom_field_table").sortable({
			'items': 'tbody tr',
			'axis': 'y',
			 placeholder: "jlt-state-highlight"
		});
	});

	$('.help_tip').tooltip({
	    content: function() {
	        return $(this).attr('title');
	    }
	});
} );

