$(document).ready(function(){
	var token = $('header').attr('data-token');
	//emails
	$('button[name=moreEmails]').click(function(){
		$('#mailList').append('<div class="row-wrap col_1_2" style="display: flex; align-items: center">'+
			'<input name="mail" type="email" class="text-input col_4_5" placeholder="Введите e-mail&hellip;">'+
			'<span class="drop-add-field">×</span>'+
		'</div>')
	});

	//phones
	$('button[name=morePhones]').click(function(){
		$('#phoneList').append('<div class="row-wrap col_1_2" style="display: flex; align-items: center">'+
			'<input name="phone" type="text" class="text-input col_4_5 needPhoneMask" placeholder="Введите телефонный номер&hellip;">'+
			'<span class="drop-add-field">×</span>'+
		'</div>');
		addTelMask();
	});

	//social
	$('button[name=moreSocial]').click(function(){
		var title = $(this).closest('fieldset').find('ul.pseudo-selector li.active span').text();
		var type = $(this).closest('fieldset').find('ul.pseudo-selector li.active').attr('data-soc');
		$('#socList').append('<div class="row-wrap col_1_2" style="display: flex; align-items: center">' +
			'<span style="width: 110px; padding-right: 10px;" class="tar">'+title+':</span>'+
			'<input name="socLink" type="text" class="text-input col_4_5" placeholder="Ссылка&hellip;" data-soc="'+type+'">'+
			'<span class="drop-add-field">×</span>'+
		'</div>');
	});

	//address
	$('button[name=moreAddresses]').click(function(){
		$('#addresslist').append('<div class="row-wrap col_1_2" style="display: flex; align-items: center">'+
			'<textarea name="address" class="simple-text" placeholder="Введите адресс&hellip;"></textarea>'+
			'<span class="drop-add-field">×</span>'+
		'</div>');
	});

	$(document).find('#mailList, #phoneList, #socList, #addresslist').on('click', '.drop-add-field', function(){
		var result = false;
		if($(this).closest('.row-wrap').find('input').length > 0) {
			result = ($(this).closest('.row-wrap').find('input').val().trim().length > 0) ? confirm('Вы действительно хотите удалить данный элемент?') : true;
		}
		if($(this).closest('.row-wrap').find('textarea').length > 0){
			result = ($(this).closest('.row-wrap').find('textarea').val().trim().length > 0) ? confirm('Вы действительно хотите удалить данный элемент?') : true;
		}
		if(result){
			$(this).closest('.row-wrap').remove();
		}
	});

	$('button[name=saveSettings]').click(function(){
		var emails = [];
		var phones = [];
		var social = [];
		var address= [];

		$('#mailList input[name=mail]').each(function(){
			if($(this).val().trim().length >0){
				emails.push($(this).val().trim());
			}
		});
		$('#phoneList input[name=phone]').each(function(){
			if($(this).val().trim().length >0){
				phones.push($(this).val().trim());
			}
		});
		$('#socList input[name=socLink]').each(function(){
			if($(this).val().trim().length > 0){
				social.push({
					type: $(this).attr('data-soc'),
					link: $(this).val().trim()
				});
			}
		});
		$('#addresslist textarea').each(function(){
			if($(this).val().trim().length > 0){
				address.push($(this).val().trim());
			}
		});

		formData.append('email', JSON.stringify(emails));
		formData.append('phone', JSON.stringify(phones));
		formData.append('social', JSON.stringify(social));
		formData.append('address', JSON.stringify(address));
		formData.append('requisites', CKEDITOR.instances.requisites.getData());
		formData.append('type','settings');

		$.ajax({
			url:		'/admin/settings/save',
			type:		'POST',
			headers:	{'X-CSRF-TOKEN': token},
			processData:false,
			contentType:false,
			datatype:	'JSON',
			data:		formData,
			error:		function(xhr){
				showErrors(xhr.responseText, '/admin/settings/save');
			},
			success:	function(data){
				if(data == 'success'){
					alert('Данные успешно сохранены')
				}else{
					showErrors(data, '/admin/settings/save');
				}
			}
		})
	});

	buildFixedNavMenu();

	$('.info-list li span').click(function(){
		$(this).closest('li').toggleClass('active');
	});
});