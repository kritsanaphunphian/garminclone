/**
 * Scripts run on attribute group add/edit page
 */
jQuery(function($){

	// disable attribute dropdown on click
	$('.attribute_taxonomy').on('focus', function(){
		$(this).find('option').each(function(){
			var _option = $(this);
			if(_option.val() !== ''){
				if($('.woocommerce_attribute.'+_option.val()).is(':visible')){
					_option.attr("disabled", "disabled");
				}else{
					_option.removeAttr("disabled");
				}
			}
		});
	});

	// on publish add input value to mark attribute as visible
	$('#publish').click(function(){

		$('input.jcaa_attribute_visible').remove();

		$('.product_attributes .woocommerce_attribute').each(function(index, item){

			if($(item).is(':visible')){

				var name = $(item).find('input[name^="attribute_names"]').attr('name');
				var res = name.match(/[0-9]+/);

				if( res != null){
					_pos = res[0];
					$('form#post').prepend('<input type="hidden" name="jcaa_attribute_visible['+_pos+']" class="jcaa_attribute_visible" value="1" />');	
				}
			}				
		});
	});
});