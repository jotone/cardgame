$(document).ready(function(){
	//add or edit menu item
	var token = $('header').attr('data-token');
	$('button[name=saveMenu]').click(function(){
		if($('input[name=title]').val().trim() == ''){
			goTo('input[name=title]');
		}else{
			formData.append('module_id', $('.main-block').attr('data-module'));
			formData.append('id', $('input[name=id]').val());
			formData.append('title', $('input[name=title]').val());
			formData.append('slug', $('input[name=slug]').val());
			formData.append('refer_to', $('select[name=refer_to]').val());

			//is enabled
			formData.append('enabled', ($('input[name=enabled]').prop('checked') == true)? 1: 0);
			//is active
			formData.append('active', $('.row-wrap input[name=active]:checked').val());

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
								var redirectQuestion = confirm('Перейти к списку меню?');
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
	$('.categories-list-wrap').on('click', '.trigger_on, .trigger_off', function(e){
		e.preventDefault();
		var id = $(this).closest('li').attr('data-id');
		var path = window.location.pathname + '/enable';
		var type = $('.main-block').attr('data-module');
		var _this =  $(this);
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
						if(_this.hasClass('trigger_on')){
							_this.removeClass('trigger_on').addClass('trigger_off').text('off');
						}else{
							_this.removeClass('trigger_off').addClass('trigger_on').text('on');
						}
						_this.closest('.category-wrap').toggleClass('disabled');
					}else{
						showErrors(data, path);
					}
				}catch(e){
					showErrors(e+data, path);
				}
			}
		});
	});

	//Drop menu item
	$('.categories-list-wrap').on('click','.drop',function(e){
		e.preventDefault();
		var result = confirm('Вы действительно хотите удалить '+ $(this).closest('.category-wrap').find('.category-title').text() + '?');
		if(result){
			var id = $(this).closest('li').attr('data-id');
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

	var path = window.location.pathname+'/change_position';
	categoriesList(path);

	buildFixedNavMenu();
});