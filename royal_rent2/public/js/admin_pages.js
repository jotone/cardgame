$(document).ready(function(){
	var token = $('header').attr('data-token');
	function getTemplateData(templateId){
		var page = $(document).find('input[name=id]').val();
		$.ajax({
			url:	'/admin/get_template_fields',
			type:	'GET',
			data:	{id:templateId, page_id:page},
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/get_template_fields');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						$('#default_fields').html(data['disabled']);
						$('#customFieldsWrap').html(data['custom']);
						CKEDITOR.replaceAll('needCKE');
						sliderSortable();
						buildFixedNavMenu();
					}else{
						showErrors(data, '/admin/get_template_fields');
					}
				}catch(e){
					showErrors(e+data, '/admin/get_template_fields');
				}
			}
		});
	}

	if($('select[name=templateType]').length > 0){
		getTemplateData($('select[name=templateType]').val());
		$('select[name=templateType]').change(function(){
			getTemplateData($(this).val());
		});
	}

	//add or edit page
	var token = $('header').attr('data-token');
	$('button[name=savePage]').click(function(){
		if($('input[name=title]').val().trim() == ''){
			goTo('input[name=title]');
		}else {
			formData.append('module_id', $('select[name=templateType]').val());
			formData.append('id', $('input[name=id]').val());
			formData.append('title', $('input[name=title]').val());
			formData.append('slug', $('input[name=slug]').val());
			formData.append('enabled', ($('input[name=enabled]').prop('checked') == true)? 1: 0);

			//meta data
			formData.append('meta_title', $('input[name=metaTitle]').val());
			formData.append('meta_description', $('textarea[name=metaDescription]').val());
			formData.append('meta_keywords', $('textarea[name=metaKeywords]').val());

			//Regular slider
			if($(document).find('fieldset[data-name=regular_slider]').length > 0){
				var regularSlider = sliderDataFill($(document).find('fieldset[data-name=regular_slider]'), 'regular_slider');
				formData.append('regular_slider', JSON.stringify(regularSlider));
			}
			//Regular description
			if($(document).find('textarea[name=description]').length > 0){
				formData.append('description', CKEDITOR.instances.description.getData());
			}else{
				formData.append('description', '');
			}
			//Regular text
			if($(document).find('textarea[name=text]').length > 0){
				formData.append('text', CKEDITOR.instances.text.getData());
			}else{
				formData.append('text', '');
			}

			//Custom Data Fill
			var customElements = [];
			$('#customFieldsWrap>fieldset, #customFieldsWrap>.row-wrap').each(function(){
				var element = buildCustomFiledData($(this));
				customElements.push(element);
			});
			formData.append('custom_data', JSON.stringify(customElements));

			$.ajax({
				url:		'/admin/pages/add',
				type:		'POST',
				headers:	{'X-CSRF-TOKEN': token},
				processData:false,
				contentType:false,
				datatype:	'JSON',
				data:		formData,
				error:		function(xhr){
					showErrors(xhr.responseText, '/admin/pages/add');
				},
				success:	function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							if($('input[name=id]').val().length == 0){
								var redirectQuestion = confirm('Перейти к списку страниц?');
								if(redirectQuestion){
									location = '/admin/pages';
								}else{
									location.reload(true);
								}
							}else{
								location = '/admin/pages';
							}
						}else if( (data['message'] == 'error') && (data['type'] == 'slug_isset') ){
							$('input[name=slug]').addClass('errorInp');
							showErrors('Такая ссылка уже существует...', path);
							goTo('input[name=slug]');
						}else{
							showErrors(data, '/admin/pages/add');
						}
					}catch(e){
						showErrors(e+data, '/admin/pages/add');
					}
				}
			});
		}
	});

	//change enabled status
	$('.item-list').on('click', 'input[name=enabled]', function(){
		var id = $(this).closest('tr').attr('data-id');
		var _this = $(this);
		$.ajax({
			url:	'/admin/pages/enable',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id, type:'pages'},
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/pages/enable');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						_this.closest('td').find('.row-wrap').text(data['published']);
					}else{
						showErrors(data, '/admin/pages/enable');
					}
				}catch(e){
					showErrors(e+data, '/admin/pages/enable');
				}
			}
		});
	});

	//Drop page
	$('.item-list').on('click','.drop',function(e){
		e.preventDefault();
		var result = confirm('Вы действительно хотите удалить '+ $(this).attr('data-title') + '?');
		if(result){
			var id = $(this).attr('data-id');
			$.ajax({
				url:	'/admin/pages/drop',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id, type:'pages'},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/pages/drop');
				},
				success:function(data){
					try {
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							location.reload(true);
						}else{
							showErrors(data, '/admin/pages/drop');
						}
					}catch(e){
						showErrors(e+data, '/admin/pages/drop');
					}
				}
			});
		}
	});
});