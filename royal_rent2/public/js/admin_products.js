$(document).ready(function(){
	//initialize colorPicker
	$('input[name=colorPicker]').spectrum({
		color: '#f00',
		showAlpha: true,
		showPalette: true,
		palette: [
			['black','darkgrey','gray','whitesmoke','azure','white'],
			['brown','red','orangered','orange','gold','yellow'],
			['greenyellow','springgreen','lightgreen','limegreen','green','teal'],
			['aqua','deepskyblue','steelblue','blue','darkslateblue','navy'],
			['pink','rosybrown','violet','magenta','darkviolet','purple']
		]
	});

	$('button[name=chooseColor]').click(function(){
		var color = $(this).closest('fieldset').find('.sp-preview-inner').css('background-color');
		$(this).closest('fieldset').find('#colors').append('<div class="color-item-wrap">' +
			'<span class="drop-add-field">&times;</span>' +
			'<div class="color-box" style="background-color: '+color+'"></div>' +
			'<input name="colorTitle" type="text" class="text-input" placeholder="Название цвета&hellip;">' +
		'</div>');
	});
	$(document).find('#colors').on('click','.drop-add-field',function(){
		$(this).closest('.color-item-wrap').remove();
	});
	//add or edit products
	var token = $('header').attr('data-token');
	$('button[name=saveProduct]').click(function(){
		if($('input[name=title]').val().trim() == ''){
			goTo('input[name=title]');
		}else{
			formData.append('module_id', $('.main-block').attr('data-module'));
			formData.append('id', $('input[name=id]').val());
			formData.append('title', $('input[name=title]').val());
			formData.append('slug', $('input[name=slug]').val());
			formData.append('enabled', ($('input[name=enabled]').prop('checked') == true)? 1: 0);

			//colors
			var colors = [];
			$('#colors .color-item-wrap').each(function(){
				var temp ={
					color: $(this).find('.color-box').css('background-color'),
					title: $(this).find('input[name=colorTitle]').val()
				}
				colors.push(temp);
			});
			formData.append('colors', JSON.stringify(colors));

			//meta data
			formData.append('meta_title', $('input[name=metaTitle]').val());
			formData.append('meta_description', $('textarea[name=metaDescription]').val());
			formData.append('meta_keywords', $('textarea[name=metaKeywords]').val());

			//Regular price
			if($(document).find('input[name=price]').length > 0){
				formData.append('price', $('input[name=price]').val());
			}else{
				formData.append('price', '0');
			}
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
								var redirectQuestion = confirm('Перейти к списку товаров?');
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

	//Drop product
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