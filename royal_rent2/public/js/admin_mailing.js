$(document).ready(function(){
	var token = $('header').attr('data-token');
	$('select[name=dataType]').change(function(){
		$('button[name=addDynamicField]').show();
		$('select[name=dataType] option:eq(0)').attr('disabled','disabled');
		$('select[name=dataField]').show();
		if($(this).val() >= 0){
			var type = $(this).val();
			$.ajax({
				url:	'/admin/mailing/get_mailing_data_fields',
				type:	'GET',
				data:	{type:type},
				error:	function(xhr) {
					showErrors(xhr.responseText, '/admin/mailing/get_mailing_data_fields');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						$('select[name=dataField]').empty();
						for(var i in data.response){
							$('select[name=dataField]').append('<option value="'+data.response[i][0]+'">'+data.response[i][1]+'</option>');
						}
					}catch(e){
						showErrors(e+data, '/admin/mailing/get_mailing_data_fields');
					}
				}
			});
		}
	});

	$(document).on('click','button[name=addDynamicField]',function(){
		var type = $('select[name=dataType] option:selected').attr('data-source');
		var parentSource = (type == 'users')? type+'~': type+'~'+$('select[name=dataType] option:selected').attr('data-module')+'~';
		var sourcePattern = parentSource+$('select[name=dataField]').val();
		var sourceTitle = '[%'+sourcePattern.replace(/~/g,'_')+'%]';
		var title = $('select[name=dataType] option:selected').text()+' &rarr; '+$('select[name=dataField] option:selected').text();

		$.ajax({
			url:	'/admin/mailing/add_pattern',
			type:	'POST',
			headers:{'X-CSRF-TOKEN': token},
			data:	{title:title, template:sourceTitle, pattern:sourcePattern},
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/mailing/add_pattern');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data.message == 'success'){
						$('#dynamicAssets .item-list tbody').append('<tr>' +
							'<td><a class="block-button drop"href="#" data-id="'+data.id+'" data-title="'+title+'" title="Удалить"><img src="/img/drop.png" alt="Удалить"></a></td>'+
							'<td>'+title+'</td>'+
							'<td class="force-select-all">'+sourceTitle+'</td>'+
							'<td style="color: #73799c">'+sourcePattern+'</td>'+
						'</tr>');
					}else{
						showErrors(data, '/admin/mailing/drop_pattern');
					}
				}catch(e){
					showErrors(e+data, '/admin/mailing/add_pattern');
				}
			}
		});
	});

	$(document).find('#dynamicAssets').on('click', 'a.drop', function(e){
		e.preventDefault();
		var result = confirm('Вы действительно хотите удалить паттерн '+$(this).attr('data-title')+'?');
		if(result){
			var id = $(this).attr('data-id');
			var _this = $(this);
			$.ajax({
				url:	'/admin/mailing/drop_pattern',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/mailing/drop_pattern');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							_this.closest('tr').remove();
						}else{
							showErrors(data, '/admin/mailing/drop_pattern');
						}
					}catch(e){
						showErrors(e+data, '/admin/mailing/drop_pattern');
					}
				}
			});
		}
	});

	$('button[name=saveLetter]').click(function(){
		formData.append('id', $('input[name=id]').val());
		formData.append('caption', $('input[name=caption]').val());
		formData.append('sender', $('input[name=mailSender]').val());
		formData.append('receiver', $('input[name=mailReceiver]').val());
		formData.append('replyer', $('input[name=mailReplyer]').val());
		formData.append('text', CKEDITOR.instances.mailText.getData());

		$.ajax({
			url:		'/admin/mailing/add',
			type:		'POST',
			headers:	{'X-CSRF-TOKEN': token},
			processData:false,
			contentType:false,
			datatype:	'JSON',
			data:		formData,
			error:		function (xhr) {
				showErrors(xhr.responseText, '/admin/mailing/add');
			},
			success:	function(data){
				try{
					data = JSON.parse(data);
					if($('input[name=id]').val().length == 0){
						var redirectQuestion = confirm('Перейти к списку шаблонов писем?');
						if(redirectQuestion){
							location = '/admin/mailing';
						}else{
							location.reload(true);
						}
					}else{
						location = '/admin/mailing';
					}
				}catch(e){
					showErrors(e+data, '/admin/mailing/add');
				}
			}
		});
	});

	$('#mailingTable').on('click', 'a.drop', function(){
		var result = confirm('Вы действительно хотите удалить шаблон '+$(this).attr('data-title')+'?');
		if(result) {
			var id = $(this).attr('data-id');
			var _this = $(this);
			$.ajax({
				url:	'/admin/mailing/drop',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id},
				error:	function (xhr) {
					showErrors(xhr.responseText, '/admin/mailing/drop');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data.message == 'success'){
							_this.closest('tr').remove();
						}else{
							showErrors(data, '/admin/mailing/drop');
						}
					}catch(e){
						showErrors(e+data, '/admin/mailing/drop');
					}
				}
			});
		}
	});
	buildFixedNavMenu();

	$('button[name=saveTemplate]').click(function(){
		var text = CKEDITOR.instances.dispatchText.getData();
		var title = $('input[name=title]').val().trim();
		if(text.trim().length > 0){
			$.ajax({
				url:	'/admin/dispatch/add',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				data:	{text: text, title:title},
				error:	function (xhr) {
					showErrors(xhr.responseText, '/admin/dispatch/add');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							location.reload(true)
						}else{
							showErrors(data, '/admin/dispatch/add');
						}
					}catch(e){
						showErrors(e+data, '/admin/dispatch/add');
					}
				}
			});
		}
	});

	$('select[name=dispatchTemplate]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url:	'/admin/dispatch/get_template',
			type:	'GET',
			data:	{slug:slug},
			error:	function (xhr) {
				showErrors(xhr.responseText, '/admin/dispatch/get_template');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						$('input[name=title]').val(data['title']);
						CKEDITOR.instances.dispatchText.setData(data['text']);
					}else{
						showErrors(data, '/admin/dispatch/get_template');
					}
				}catch(e){
					showErrors(e+data, '/admin/dispatch/get_template');
				}
			}
		});
	});

	$('button[name=dispatch]').click(function(){
        var text = CKEDITOR.instances.dispatchText.getData();
        var title = $('input[name=title]').val().trim();
		$.ajax({
			url:	'/admin/dispatch/make',
			type:	'POST',
			headers:{'X-CSRF-TOKEN': token},
			data:	{text: text, title:title},
			error:	function (xhr) {
				showErrors(xhr.responseText, '/admin/dispatch/make');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						alert('Рассылка произведена');
					}else{
						showErrors(data, '/admin/dispatch/make');
					}
				}catch(e){
					showErrors(e+data, '/admin/dispatch/make');
				}
			}
		});
	});
});