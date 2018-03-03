/**
 * Scripts run on product add/edit page
 */
jQuery(function($){

	var jcaa_attribute_to_load = [];

	// remove duplicate input indexes
	$('body').on('jcaa_reindex_attributes', function(){
		$('.woocommerce_attribute').each(function (idx, item) {
	        var $inputs = $(item).find('input, select, textarea');
	        $inputs.each(function () {
	            this.name = this.name.replace(/(\[[0-9]+\])/, '[' + idx + ']');  
	        });
	        $(this).find('.attribute_position').val(idx);
	    });
	});

	$('body').on('jcaa_load_next_attr', function(){

		if(jcaa_attribute_to_load.length > 0){
			jcaa_load_attribute(jcaa_attribute_to_load.shift());
		}
	});

	var jcaa_load_attribute = function(attribute){

		$('.product_attributes').block();

		if(attribute.name.indexOf('pa_') === 0){

			// taxonomy attribute
			if($('.woocommerce_attribute.'+attribute.name).length === 0 || !$('.woocommerce_attribute.'+attribute.name).is(':visible') ){
				$('select.attribute_taxonomy').val(attribute.name);
				$('button.add_attribute').trigger('click');
			}
			
			/**
			 * Wait on select opening, keep checking every second
			 */
			function jcaa_check_select2_attr(attribute) {

				if( $('.woocommerce_attribute.'+attribute.name).is(':visible') ){

					// add values to select
					var _select = $('.woocommerce_attribute.'+attribute.name+' select');

					for( var i =0; i < attribute.values.length; i++){
						_select.find('[value="'+attribute.values[i]+'"]').attr("selected","selected");
					}

					// set visible on product page
					if(attribute.visible !== undefined && attribute.visible === 'true'){
						$('.woocommerce_attribute.'+attribute.name+' input[name^="attribute_visibility"]').prop('checked', true);
					}else{
						$('.woocommerce_attribute.'+attribute.name+' input[name^="attribute_visibility"]').prop('checked', false);
					}

					// set used for variations [attribute_variation]
					if(attribute.variation !== undefined && attribute.variation === 'true'){
						$('.woocommerce_attribute.'+attribute.name+' input[name^="attribute_variation"]').prop('checked', true);
					}else{
						$('.woocommerce_attribute.'+attribute.name+' input[name^="attribute_variation"]').prop('checked', false);
					}
					
					_select.change();
					_select.trigger("chosen:updated");

					$('body').trigger('jcaa_reindex_attributes');
					if(jcaa_attribute_to_load.length == 0) {
						$('.product_attributes').unblock();
					}
					$('body').trigger('jcaa_load_next_attr');

				}else{
					setTimeout(function(){jcaa_check_select2_attr(attribute); }, 500);	
				}  		
			}

			jcaa_check_select2_attr(attribute);
			
		}else{

			// custom text attribute
			var _elem = false;
				
			// check for exisint
			$('.woocommerce_attribute').each(function(index){
				
				var _this = $(this);
				if(_this.find('input[name^="attribute_names"]').val() === attribute.name){
					_elem = _this;
					return false;
				}
			});

			// add new box if not found
			if(_elem === false){
				
				$('select.attribute_taxonomy').val('');
				$('button.add_attribute').trigger('click');	

				jcaa_check_text_attribute(attribute, _elem);

				function jcaa_check_text_attribute(attribute, _elem){

					var escape = false;
					
					$('.woocommerce_attribute').each(function(){

						if($(this).find('input[name^="attribute_names"]').val() === '' && $(this).find('textarea[name^="attribute_values"]').val() === ''){
							_elem = $(this);
							escape = true;
							return false;
						}
					});

					if(escape){
						return jcaa_populate_text_attribute(attribute, _elem);								
					}else{
						setTimeout(function() {
							jcaa_check_text_attribute(attribute, _elem);
						}, 500);
					}
				}

			}else{
				
				jcaa_populate_text_attribute(attribute, _elem)
			}
			
			function jcaa_populate_text_attribute(attribute, _elem){

				var _input_name = _elem.find('input[name^="attribute_names"]');
				_input_name.val(attribute.name);
				_input_name.trigger('blur');

				var _textarea_choice = _elem.find('textarea[name^="attribute_values"]');

				var output = '';
				for( var i =0; i < attribute.values.length; i++){

					if(i > 0){
						output += '|' + attribute.values[i];
					}else{
						output += attribute.values[i];
					}						
				}

				// set visible on product page
				if(attribute.visible !== undefined && attribute.visible === 'true'){
					_elem.find('input[name^="attribute_visibility"]').prop('checked', true);
				}

				// set used for variations [attribute_variation]
				if(attribute.variation !== undefined && attribute.variation === 'true'){
					_elem.find('input[name^="attribute_variation"]').prop('checked', true);
				}

				_textarea_choice.val(output);

				$('body').trigger('jcaa_reindex_attributes');
				if(jcaa_attribute_to_load.length == 0) {
					$('.product_attributes').unblock();
				}
				$('body').trigger('jcaa_load_next_attr');
			}
		}
	};

	var jcaa_load_group = function(group){

		for(var i = 0; i < jcaa_attribute_groups.length; i++){
			if(jcaa_attribute_groups[i]['name'] == group){

				jcaa_attribute_to_load = jcaa_attribute_groups[i]['attributes'].slice(0);
				$('body').trigger('jcaa_load_next_attr');
			}
		}
	};
	
	// load attribute group to stage
	$('body').on('click', '.jcaa_load_attribute_group', function(event){
		jcaa_load_group( $('select[name="jcaa_attribute_groups"]').val() );
		event.preventDefault();
	});

	$('body').on('attribute_group_update', function(){

		var selectHtml = '<option value="">Attribute Groups</option>';

		var groupSaveHtml = '<option value="">Please Select One</option><option value="create">Create Attribute Group</option>'; 

		for(var i = 0; i < jcaa_attribute_groups.length; i++){

			var row = '<option value="'+jcaa_attribute_groups[i].name+'">'+jcaa_attribute_groups[i].label+'</option>';
			selectHtml += row
			groupSaveHtml += row;
		}

		$('#jcaa_attribute_group_select').html(groupSaveHtml);
		$('#jcaa_attribute_groups').html(selectHtml);
	});

	$('body').trigger('attribute_group_update');	
});