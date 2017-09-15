function viewOptions(options){
	$('#moduleNormalSettings').empty();
	if(options.length > 0){
		$('#moduleNormalSettings').closest('fieldset').show();
	}else{
		$('#moduleNormalSettings').closest('fieldset').hide();
	}
	var disabled = [];
	if($('#moduleNormalSettings').attr('data-option') != undefined){
		disabled = JSON.parse($('#moduleNormalSettings').attr('data-option'));
	}

	for(var i in options){
		var type = '';
		switch(options[i]['type']){
			case 'fulltext': type = 'Полный текст'; break;
			case 'img_slider': type = 'Слайдер изображений'; break;
			case 'string': type = 'Строка'; break;
			case 'date': type = 'Дата'; break;
		}
		var checked = ($.inArray(options[i]['name'], disabled))? 'checked="checked"': '';

		$('#moduleNormalSettings').append('<div class="module-elements-wrap" data-type="'+options[i]['name']+'">' +
			'<div class="col_1_4">Название: '+options[i]['caption']+'</div>' +
			'<div class="col_1_4">Тип: '+type+'</div>' +
			'<div class="col_1_4">Псевдоним: '+options[i]['name']+'</div>' +
			'<div class="col_1_4">' +
			'<label class="">' +
				'<input class="chbox-input" type="checkbox" '+checked+'>' +
				'<span>Включен</span>' +
			'</label>' +
			'</div>' +
		'</div>');
	}
	$('#moduleNormalSettings').sortable();
}

function getModuleDefaultSettings(moduleType){
	var token = $('header').attr('data-token');
	$.ajax({
		url:	'/admin/modules/get_module_default_settings',
		type:	'GET',
		headers:{'X-CSRF-TOKEN': token},
		data:	{type:moduleType},
		error:	function(xhr){
			showErrors(xhr.responseText, '/admin/modules/get_module_default_settings');
		},
		success:function(data){
			try{
				data = JSON.parse(data);
				if(data['message'] == 'success'){
					viewOptions(data['options']);
				}else{
					showErrors(data, '/admin/modules/get_module_default_settings');
				}
			}catch(e){
				showErrors(e+data, '/admin/modules/get_module_default_settings');
			}
		}
	});
}

function viewAdditionalContent(type){
	//Отображение содержимого блока в зависимости от выбраного элемента в селекторе
	$('#contentTune').empty();
	switch(type){
		case 'group':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название группы&hellip;">' +
					'<span>Название группы</span>' +
				'</label>' +
			'</div>');
		break;
		case 'email':
		case 'string':
		case 'textarea':
		case 'fulltext':
		case 'img_slider':
		case 'file_upload':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;">' +
					'<span>Название</span>' +
				'</label>' +
			'</div>');
		break;
		case 'number':
		case 'range':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;">' +
					'<span>Название</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="min" type="text" class="text-input col_1_2" placeholder="Минимальное значение&hellip;">' +
					'<span>Минимальное значение</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="max" type="text" class="text-input col_1_2" placeholder="Максимальное значение&hellip;">' +
					'<span>Максимальное значение</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="step" type="text" class="text-input col_1_2" placeholder="Шаг&hellip;">' +
					'<span>Шаг</span>' +
				'</label>' +
			'</div>');
		break;
		case 'checkbox':
		case 'radio':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;">' +
					'<span>Название</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="group" type="text" class="text-input col_1_2" placeholder="Группа&hellip;">' +
					'<span>Группа</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="value" type="text" class="text-input col_1_2" placeholder="Значение по умолчанию&hellip;">' +
					'<span>Значение по умолчанию</span>' +
				'</label>' +
			'</div>');
		break;
		case 'table':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;">' +
					'<span>Название</span>' +
				'</label>' +
				'<label class="fieldset-label-wrap">' +
					'<input name="value" type="number" class="text-input col_1_2" min="1" placeholder="Количество столбцов&hellip;">' +
					'<span>Количество столбцов</span>' +
				'</label>' +
			'</div>');
		break;
		case 'custom_slider':
			$('#contentTune').append('<div class="row-wrap">' +
				'<input name="type" type="hidden" value="'+type+'">'+
				'<label class="fieldset-label-wrap">' +
					'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название слайдера&hellip;">' +
					'<span>Название слайдера</span>' +
				'</label>' +
				'<div class="row-wrap">' +
					'<p>Поля слайдера:</p>' +
					'<select name="moreFields" class="select-input col_1_2">' +
						'<option value="image">Изображение</option>' +
						'<option value="string">Строка</option>' +
						'<option value="text">Текстовое поле</option>' +
					'</select><input name="addCustomSliderField" type="button" class="control-button" value="Добавить Поле">' +
				'</div>' +
				'<div id="customSliderFieldList"></div>' +
			'</div>');
		break;

		case 'articles':
		case 'category':
		case 'products':
		case 'promo':
			var destination = 'articles';
			if(type == 'category') destination = 'categories';
			if(type == 'products') destination = 'products';
			if(type == 'promo') destination = 'promo';
			$.ajax({
				url:	'/admin/get_modules_list',
				type:	'GET',
				data:	{destination:destination},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/get_modules_list');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							var categories = '';
							for(var i in data['result']){
								categories += '<option value="'+data['result'][i]['id']+'">'+data['result'][i]['title']+'</option>';
							}
							$('#contentTune').append('<div class="row-wrap">' +
								'<label class="fieldset-label-wrap">' +
								'<input name="caption" type="text" class="text-input col_1_2" placeholder="Название&hellip;">' +
								'<span>Название</span>' +
								'</label>' +
							'</div>' +
							'<div class="row-wrap">' +
								'<input name="type" type="hidden" value="'+type+'">' +
								'<label class="fieldset-label-wrap">' +
									'<input name="multipleSelect" type="checkbox">' +
									'<span>Разрешить мультивыбор</span>' +
								'</label>' +
							'</div>' +
							'<div class="row-wrap">' +
								'<input name="type" type="hidden" value="'+type+'">' +
								'<label class="fieldset-label-wrap">' +
									'<input name="positionate" type="checkbox">' +
									'<span>Разрешить позиционирование</span>' +
								'</label>' +
							'</div>' +
							'<div class="row-wrap">' +
								'<label class="fieldset-label-wrap">' +
									'<select name="selectedCategory" class="select-input col_1_2">'+categories+'</select>' +
									'<span>Доступные модули</span>' +
								'</label>' +
							'</div>');
						}else{
							showErrors(data, '/admin/get_modules_list');
						}
					}catch(e){
						showErrors(e+data, '/admin/get_modules_list');
					}
				}
			});
		break;
	}
}

$(document).ready(function(){
	$('#moduleAdditionalSettings').sortable({
		connectWith: '.dropable-field'
	});
	$('#moduleAdditionalSettings .tune-fieldset .dropable-field').sortable({
		connectWith: '#moduleAdditionalSettings'
	});
	//Enabled modules page
	$('#modulesSelectable .enabled-module-on').click(function(){
		var id = $(this).closest('.enabled-module-wrap').attr('data-id');
		var token = $('header').attr('data-token');
		var _this = $(this);
		$.ajax({
			url:	'/admin/modules/enable',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': token},
			data:	{id:id},
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/modules/enable');
			},
			success:function(data) {
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						_this.toggleClass('active');
						_this.closest('.enabled-module-wrap').toggleClass('active');
						if(_this.hasClass('active')){
							_this.text('Включен');
						}else{
							_this.text('Выключен');
						}
					}
				}catch(e){
					showErrors(e+data, '/admin/modules/enable');
				}
			}
		});
	});

	$('#modulesSelectable .show-button-wrap').click(function(){
		$(this).parent('div').find('.show-wrap').toggleClass('active');
		if($(this).parent('div').find('.show-wrap').hasClass('active')){
			$(this).children('span').text('Свернуть');
		}else{
			$(this).children('span').text('Развернуть');
		}
	});

	$('#modulesSelectable').sortable({
		update: function(){
			var modules = [];
			var token = $('header').attr('data-token');
			$('#modulesSelectable .enabled-module-wrap').each(function(){
				var temp = {
					id: $(this).attr('data-id'),
					pos: $(this).index()
				}
				modules.push(temp);
			});
			$.ajax({
				url:	'/admin/modules/change_postion',
				type:	'PUT',
				headers:{'X-CSRF-TOKEN': token},
				data:	{modules:modules},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/modules/change_postion');
				},
				success:function(data) {
					if(data != 'success'){
						showErrors(data, '/admin/modules/change_postion');
					}
				}
			});
		}
	});

	$('#modulesSelectable .drop').click(function(e){
		e.preventDefault();
		var result = confirm('Вы действительно хотите удалить модуль '+$(this).attr('data-title')+'?');
		if(result){
			var id = $(this).closest('.enabled-module-wrap').attr('data-id');
			var token = $('header').attr('data-token');
			var _this = $(this);
			$.ajax({
				url:	'/admin/modules/drop',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/modules/drop');
				},
				success:function(data) {
					if(data == 'success'){
						location.reload(true);
					}else{
						showErrors(data, '/admin/modules/drop');
					}
				}
			});
		}
	});
	// /Enabled modules page

	//Module add or edit
	$('.module-list-wrap .module-wrap').click(function(){
		var result = confirm('Добавить модуль '+$(this).find('span').text()+'?');
		if(result){
			var id = $(this).attr('data-id');
			location = '/admin/modules/add/'+id;
		}
	});

	$(document).on('click','input[name=addCustomSliderField]', function(){
		$('#customSliderFieldList').append('<div>' +
			'<span class="slider-elem-drop">&times;</span>' +
			'<input class="slider-elem-caption" type="text" name="field_caption" data-fieldtype="'+$('select[name=moreFields]').val()+'" placeholder="Название&hellip;">' +
			'<span class="slider-elem-type">'+$('select[name=moreFields] option:selected').text()+'</span>' +
		'</div>');
	});
	$(document).on('click','.slider-elem-drop',function(){$(this).parent('div').remove();});

	$('#contentTune').on('keyup', 'input[name=caption]', function(){
		if($(this).closest('.row-wrap').find('input[name=name]').length > 0){
			$(this).closest('.row-wrap').find('input[name=name]').val(str2url($(this).val()));
		}
	});

	viewAdditionalContent($('select[name=contentType]').val());

	var moduleType = $('select[name=moduleType]').val();
	if(moduleType != undefined){
		if($('#moduleNormalSettings').attr('data-deny') == undefined){
			getModuleDefaultSettings(moduleType);
		}else{
			$('#moduleNormalSettings').sortable();
		}
	}

	$('select[name=moduleType]').change(function(){
		var moduleType = $(this).val();
		getModuleDefaultSettings(moduleType);
	});

	$('select[name=contentType]').change(function(){
		viewAdditionalContent($(this).val())
	});

	$('button[name=addContent]').click(function(){
		//Добавление контента
		if($('#contentTune').find('input[name=caption]').val().trim().length < 1){
			$('#contentTune').find('input[name=caption]').addClass('errorInp');
		}else{
			var type = $('#contentTune input[name=type]').val();
			var tag = '';

			switch (type) {
				case 'group':
					tag += '<fieldset class="tune-fieldset">' +
						'<legend><span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span><span class="drop-add-field">&times;</span></legend>' +
						'<div class="dropable-field">Перетащите сюда контент для вставки</div>' +
						'</fieldset>';
				break;
				case 'string':
				case 'email':
				case 'textarea':
				case 'fulltext':
				case 'img_slider':
				case 'file_upload':
					var customType = 'Строка';
					if (type == 'email') customType = 'E-mail';
					if (type == 'textarea') customType = 'Текстовое поле';
					if (type == 'fulltext') customType = 'Полный текст';
					if (type == 'img_slider') customType = 'Слайдер изображений';
					if (type == 'file_upload') customType = 'Файл';
					tag +='' +
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: ' + customType +
							'<input name="type" type="hidden" value="' + type + '">' +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
				case 'number':
				case 'range':
					var customType = 'Ввод чисел';
					if (type == 'range') customType = 'Ползунок';
					tag +=
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: ' + customType +
							'<input name="type" type="hidden" value="' + type + '">' +
							'<div>min:<span data-type="min"> ' + $('#contentTune input[name=min]').val() + '</span>;</div>' +
							'<div>max:<span data-type="max"> ' + $('#contentTune input[name=max]').val() + '</span>;</div>' +
							'<div>шаг:<span data-type="step"> ' + $('#contentTune input[name=step]').val() + '</span>;</div>' +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
				case 'checkbox':
				case 'radio':
					var customType = 'Флажок';
					if (type == 'radio') customType = 'Переключатель';
					tag += '' +
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: ' + customType +
							'<input name="type" type="hidden" value="' + type + '">; ' +
							'<div>Группа: <span data-type="group">' + str2url($('#contentTune input[name=group]').val()) + '</span>;</div>' +
							'<div>Значение: <span data-type="value">' + $('#contentTune input[name=value]').val() + '</span>;</div>' +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
				case 'table':
					tag += '' +
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: Таблица' +
							'<input name="type" type="hidden" value="' + type + '">; ' +
							'Столбцов: <span data-type="value">' + $('#contentTune input[name=value]').val() + '</span>;' +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
				case 'custom_slider':
					var sliderContent = '';
					$('#contentTune #customSliderFieldList>div').each(function () {
						sliderContent += $(this).find('.slider-elem-type').text() + ': ';
						sliderContent += '<span data-type="value" data-fieldtype="' + $(this).find('input[name=field_caption]').attr('data-fieldtype') + '">' + $(this).find('input[name=field_caption]').val() + '</span>;<br>';
					});
					tag +=
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: Настраиваемый слайдер;<br>' +
							'<input name="type" type="hidden" value="' + type + '">' + sliderContent +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
				case 'articles':
				case 'category':
				case 'products':
				case 'promo':
					var contentType = 'Статьи';
					if(type == 'category') contentType = 'Категория';
					if(type == 'products') contentType = 'Товары';
					if(type == 'promo') contentType = 'Акции';
					var multSelect = ($('#contentTune input[name=multipleSelect]').prop('checked') == true)? 1: 0;
					var multVal = ($('#contentTune input[name=multipleSelect]').prop('checked') == true)? 'Да': 'Нет';

					var posSel = ( $('#contentTune input[name=positionate]').prop('checked') == true)? 1: 0;
					var posVal = ( $('#contentTune input[name=positionate]').prop('checked') == true)? 'Да': 'Нет';

					tag += '' +
					'<div class="module-custom-elements-wrap">' +
						'<div class="col_1_4">Название: <span data-type="caption">' + $('#contentTune input[name=caption]').val() + '</span></div>' +
						'<div class="col_1_2">Тип: ' +contentType+
							'<input name="type" type="hidden" value="' + type + '">;<br>' +
							contentType+': <span data-type="name">' + $('#contentTune select[name=selectedCategory] option:selected').text() + '</span>;<br>' +
							'Разрешить мультивыбор: ' + multVal + '<input name="multSel" type="hidden" value="' + multSelect + '"><br>' +
							'Разрешить позиционирование: ' + posVal + '<input name="posSel" type="hidden" value="' + posSel + '">' +
							'<input name="value" type="hidden" value="' + $('#contentTune select[name=selectedCategory]').val() + '">' +
						'</div>' +
						'<div class="col_1_4">' +
							'<span class="drop-add-field">&times;</span>' +
						'</div>' +
					'</div>';
				break;
			}
			$('#moduleAdditionalSettings').append(tag);
			$('#moduleAdditionalSettings').sortable({
				connectWith: '.dropable-field'
			});
			$('#moduleAdditionalSettings .tune-fieldset .dropable-field').sortable({
				connectWith: '#moduleAdditionalSettings'
			});
		}
	});
	$(document).on('click','.drop-add-field',function(){
		$(this).closest('.module-custom-elements-wrap').remove();
		$(this).parent().parent('.tune-fieldset').remove();
	});

	$('button[name=saveModule]').click(function(){
		if( $('input[name=title]').val().trim() == '' ){
			$('input[name=title]').addClass('errorInp');
			goTo('input[name=title]');
		}else{
			var token = $('header').attr('data-token');
			var disabledFields = [];
			$('#moduleNormalSettings .module-elements-wrap').each(function(){
				var disabledTemp = {
					enabled: ($(this).find('input[type=checkbox]').prop('checked') == false)? 0: 1,
					type: $(this).attr('data-type')
				}
				disabledFields.push(disabledTemp);
			});

			var additionalData = [];
			$('#moduleAdditionalSettings>.tune-fieldset, #moduleAdditionalSettings>.module-custom-elements-wrap').each(function(){
				if($(this).hasClass('module-custom-elements-wrap')){
					var temp = {
						pos: $(this).index(),
						type: $(this).find('input[name=type]').val(),
						capt: $(this).find('span[data-type=caption]').text()
					};
					if($(this).find('span[data-type=min]').length > 0){
						temp.min = $(this).find('span[data-type=min]').text();
					}
					if($(this).find('span[data-type=max]').length > 0){
						temp.max = $(this).find('span[data-type=max]').text();
					}
					if($(this).find('span[data-type=step]').length > 0){
						temp.step = $(this).find('span[data-type=step]').text()
					}
					if($(this).find('span[data-type=group]').length > 0){
						temp.group = str2url($(this).find('span[data-type=group]').text());
					}
					if($(this).find('span[data-type=value]').length > 0){
						if($(this).find('span[data-type=value]').length > 1){
							var values = [];
							$(this).find('span[data-type=value]').each(function(){
								values.push({
									field: $(this).attr('data-fieldtype'),
									val: $(this).text()
								});
							});
							temp.val = values;
						}else{
							temp.val = $(this).find('span[data-type=value]').text();
						}
					}
					if($(this).find('input[name=value]').length > 0){
						temp.val = $(this).find('input[name=value]').val();
					}
					if($(this).find('input[name=multSel]').length > 0){
						temp.mult = $(this).find('input[name=multSel]').val();
					}
					if($(this).find('input[name=posSel]').length > 0){
						temp.pos_sel = $(this).find('input[name=posSel]').val();
					}
					additionalData.push(temp);
				}
				if($(this).hasClass('tune-fieldset')){
					var temp = {
						pos: $(this).index(),
						type: 'fieldset',
						capt: $(this).find('legend').find('span[data-type=caption]').text(),
						val: []
					}
					$(this).find('.dropable-field').find('.module-custom-elements-wrap').each(function(){
						var fieldsetTemp = {
							pos: $(this).index(),
							type: $(this).find('input[name=type]').val(),
							capt: $(this).find('span[data-type=caption]').text()
						}
						if($(this).find('span[data-type=min]').length > 0){
							fieldsetTemp.min = $(this).find('span[data-type=min]').text();
						}
						if($(this).find('span[data-type=max]').length > 0){
							fieldsetTemp.max = $(this).find('span[data-type=max]').text();
						}
						if($(this).find('span[data-type=step]').length > 0){
							fieldsetTemp.step = $(this).find('span[data-type=step]').text()
						}
						if($(this).find('span[data-type=group]').length > 0){
							fieldsetTemp.group = str2url($(this).find('span[data-type=group]').text());
						}
						if($(this).find('span[data-type=value]').length > 0){
							if($(this).find('span[data-type=value]').length > 1){
								var values = [];
								$(this).find('span[data-type=value]').each(function(){
									values.push({
										field: $(this).attr('data-fieldtype'),
										val: $(this).text()
									});
								});
								fieldsetTemp.val = values;
							}else{
								fieldsetTemp.val = $(this).find('span[data-type=value]').text();
							}
						}
						if($(this).find('input[name=value]').length > 0){
							fieldsetTemp.val = $(this).find('input[name=value]').val();
						}
						if($(this).find('input[name=multSel]').length > 0){
							fieldsetTemp.mult = $(this).find('input[name=multSel]').val();
						}
						if($(this).find('input[name=posSel]').length > 0){
							temp.pos_sel = $(this).find('input[name=posSel]').val();
						}
						temp.val.push(fieldsetTemp);
					});
					additionalData.push(temp);
				}
			});

			formData.append('id', $('input[name=id]').val());
			formData.append('type', $('select[name=moduleType]').val());
			formData.append('title', $('input[name=title]').val());
			formData.append('slug', $('input[name=slug]').val());
			formData.append('unique_slug', ($('input[name=unique_slug]').prop('checked') == true)? 1: 0);
			formData.append('description', $('textarea[name=description]').val());
			formData.append('disabled_fields', JSON.stringify(disabledFields));
			formData.append('additional_data', JSON.stringify(additionalData));

			$.ajax({
				url:	'/admin/modules/add',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				processData: false,
				contentType: false,
				data:	formData,
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/modules/add');
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							location = '/admin/modules'
						}else if( (data['message'] == 'error') && (data['type'] == 'slug_isset') ){
							showErrors('Такая ссылка уже существует...', '/admin/modules/add');
							$('input[name=slug]').addClass('errorInp');
							goTo('input[name=slug]');
						}else{
							showErrors(data, '/admin/modules/add');
						}
					}catch(e){
						showErrors(e+data, '/admin/modules/add');
					}
				}
			});
		}
	});
});