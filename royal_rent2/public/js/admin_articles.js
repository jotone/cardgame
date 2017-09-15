$(document).ready(function(){
	//add or edit article
	var token = $('header').attr('data-token');
	$('button[name=saveArticle]').click(function(){
		if($('input[name=title]').val().trim() == ''){
			goTo('input[name=title]');
		}else {
			formData.append('module_id', $('.main-block').attr('data-module'));
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
			//Regular text caption
			if($(document).find('input[name=text_caption]').length > 0){
				formData.append('text_caption', $(document).find('input[name=text_caption]').val());
			}else{
				formData.append('text_caption', '');
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

			var path = window.location.pathname.split('/');

			var returnPath = '/'+path[1]+'/'+path[2];
			path = returnPath+'/add';
			$.ajax({
				url:		path,
				type:		'POST',
				headers:	{'X-CSRF-TOKEN': token},
				processData:false,
				contentType:false,
				datatype:	'JSON',
				data:		formData,
				error:		function(xhr){
					showErrors(xhr.responseText, path);
				},
				success:	function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							if($('input[name=id]').val().length == 0){
								var redirectQuestion = confirm('Перейти к списку статей?');
								if(redirectQuestion){
									location = returnPath;
								}else{
									location.reload(true);
								}
							}else{
								location = returnPath;
							}
						}else if( (data['message'] == 'error') && (data['type'] == 'slug_isset') ){
							$('input[name=slug]').addClass('errorInp');
							showErrors('Такая ссылка уже существует...', path);
							goTo('input[name=slug]');
						}else{
							showErrors(data, path);
						}
					}catch(e){
						showErrors(e+data, path);
					}
				}
			});
		}
	});

	//change enabled status
	$('.item-list').on('click', 'input[name=enabled]', function(){
		var id = $(this).closest('tr').attr('data-id');
		var path = window.location.pathname + '/enable';
		var type = $('.main-block').attr('data-module');
		var _this = $(this);
		$.ajax({
			url:	path,
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id, type:type},
			error:	function(xhr){
				showErrors(xhr.responseText, path);
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						_this.closest('td').find('.row-wrap').text(data['published']);
					}else{
						showErrors(data, path);
					}
				}catch(e){
					showErrors(e+data, path);
				}
			}
		});
	});

	//Drop article
	$('.item-list').on('click','.drop',function(e){
		e.preventDefault();
		var result = confirm('Вы действительно хотите удалить '+ $(this).attr('data-title') + '?');
		if(result){
			var id = $(this).attr('data-id');
			var path = window.location.pathname + '/drop';
			var type = $('.main-block').attr('data-module');
			$.ajax({
				url:	path,
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id, type:type},
				error:	function(xhr){
					showErrors(xhr.responseText, path);
				},
				success:function(data){
					try {
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							location.reload(true);
						}else{
							showErrors(data, path);
						}
					}catch(e){
						showErrors(e+data, path);
					}
				}
			});
		}
	});
	buildFixedNavMenu();
});