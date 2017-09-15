jQuery.browser = {};
jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());

var scroller=jQuery.browser.webkit ? "body": "html";

formData = new FormData();

function goTo(href){
	var target = $(href).offset().top-65;
	$(scroller).animate({scrollTop:target},500);
}

function rus2translit(str){
	str = str.trim();
	var converter = {
		'а':'a',	'б':'b',	'в':'v',	'г':'g',	'д':'d',	'е':'e',
		'ё':'e',	'ж':'zh',	'з':'z',	'и':'i',	'й':'j',	'к':'k',
		'л':'l',	'м':'m',	'н':'n',	'о':'o',	'п':'p',	'р':'r',
		'с':'s',	'т':'t',	'у':'u',	'ф':'f',	'х':'h',	'ц':'ts',
		'ч':'ch',	'ш':'sh',	'щ':'shch',	'ь':'',		'ы':'y',	'ъ':'',
		'э':'e',	'ю':'yu',	'я':'ya',	'і':'i',	'ї':'i',	'є':'ie',
		'А':'A',	'Б':'B',	'В':'V',	'Г':'G',	'Д':'D',	'Е':'E',
		'Ё':'E',	'Ж':'Zh',	'З':'Z',	'И':'I',	'Й':'J',	'К':'K',
		'Л':'L',	'М':'M',	'Н':'N',	'О':'O',	'П':'P',	'Р':'R',
		'С':'S',	'Т':'T',	'У':'U',	'Ф':'F',	'Х':'H',	'Ц':'Ts',
		'Ч':'Ch',	'Ш':'Sh',	'Щ':'Shch',	'Ь':'',		'Ы':'Y',	'Ъ':'',
		'Э':'E',	'Ю':'Yu',	'Я':'Ya',	'І':'I',	'Ї':'I',	'Є':'Ie'
	};

	str = str.split('');
	var result = '';
	for(var char in str){
		if(converter[str[char]] != undefined){
			result += converter[str[char]];
		}else{
			result += str[char];
		}
	}
	return result;
}

function str2url(str){
	str = rus2translit(str);
	str = str.toLowerCase();
	str = str.replace(/[^-a-z0-9_\.\#]/g, '_');
	return str;
}

function str2urlUpperCase(str){
	str = rus2translit(str);
	str = str.toUpperCase();
	str = str.replace(/[^-A-Z0-9_\.\#]/g, '_');
	return str;
}

function categoriesList(path, check_menu){
	$('.categories-list-wrap ul').sortable({
		connectWith: ['.categories-list-wrap ul'],
		over: function(e, ui){
			$(this).find('ul.empty').show();
		},
		stop: function(e, ui){
			$('.categories-list-wrap ul.empty').hide();
		},
		update: function(e, ui){
			$(this).closest('ul.empty').removeClass('empty');
			sendCategoryPosition(path);
			if( (typeof check_menu != 'undefined') && (check_menu != false) ){
				$.ajax({
					url:	'/admin/get_menu',
					type:	'GET',
					error:	function(xhr){
						showErrors(xhr.responseText, '/admin/get_menu');
					},
					success:function(data){
						$('header .top-menu').html(data);
					}
				});
			}
		}
	});

	$('.categories-list-wrap .sort-controls').on('click','p',function(){
		var direction = $(this).attr('data-direction');
		if(direction == 'up'){
			if($(this).closest('li').prev().length > 0){
				var el = $(this).closest('li').prev();
				$(this).closest('li').after(el);
			}
		}else{
			if($(this).closest('li').next().length > 0){
				var el = $(this).closest('li').next();
				$(this).closest('li').before(el);
			}
		}
		sendCategoryPosition(path);
	});
}
function sendCategoryPosition(path) {
	var items = [];
	$('.categories-list-wrap ul li').each(function () {
		var category = {
			'id': $(this).attr('data-id'),
			'position': $(this).index(),
			'refer_to': $(this).parent('ul').attr('data-refer')
		}
		items.push(category);
	});
	items = JSON.stringify(items);

	var token = $('header').attr('data-token');
	var type = (typeof $('.main-block').attr('data-type') != "undefined") ? $('.main-block').attr('data-type') : '';
	var module = (typeof $('.main-block').attr('data-module') != "undefined") ? $('.main-block').attr('data-module') : '';
	$.ajax({
		url: path,
		type: 'PUT',
		headers: {'X-CSRF-TOKEN': token},
		data: {items: items, type: type, module: module},
		error: function (xhr) {
			showErrors(xhr.responseText, path);
		},
		success: function (data) {
			if ((data != 'success') && (data != '')) {
				showErrors(data, path);
			}
		}
	});
}
function showErrors(data, url){
	$('.error-popup .popup-caption span').html('&quot;'+url+'&quot;');
	$('.error-popup .error-wrap').html(data);
	$('footer .error-log').show();
}

function resortSliderWrap(_this){
	_this.closest('.slider-wrap').find('.slider-content-element').each(function(){
		$(this).attr('data-position', $(this).index());
	});
	_this.closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap').each(function(){
		$(this).attr('data-position', $(this).index());
	});
}

function caseImageInOverviewPopup(_this){
	$('.overview-popup .image-container').click(function(){
		$(this).toggleClass('active');
	});
	$('.overview-popup').off('click','button[name=addImageFromSaved]');
	$('.overview-popup').on('click','button[name=addImageFromSaved]', function(){
		var point = 0;
		$('.overview-popup .popup-images .active').each(function(){
			var imageSrc	= $(this).find('img').attr('alt');
			var imageCaption= imageSrc.split('/');

			imageCaption = imageCaption[imageCaption.length -1];
			imageSrc = (imageSrc.substr(0,1) != '/')? '/'+imageSrc: imageSrc;

			_this.closest('.slider-wrap').find('.slider-images-wrap').append('' +
				'<div class="image-wrap" data-position="'+point+'">' +
					'<img src="'+imageSrc+'" alt="">' +
					'<div class="attributes-wrap">' +
						'<input name="altText" type="text" class="text-input" placeholder="Альтернативный текст&hellip;" style="width: 90%;">' +
						'<a href="#" class="drop-image button" title="Удалить">' +
							'<img src="/img/drop.png" alt="">' +
						'</a>' +
					'</div>' +
				'</div>');
			_this.closest('.slider-wrap').find('.slider-list-wrap').append('' +
				'<div class="slider-content-element" data-position="'+point+'">' +
					'<div class="element-title">'+imageCaption+'</div>' +
					'<div class="element-size"></div>' +
					'<div class="element-image">' +
						'<img src="'+imageSrc+'" alt="">' +
					'</div>' +
					'<div class="element-alt"></div>' +
					'<div class="element-drop">' +
						'<img src="/img/drop.png" alt="Удалить" title="Удалить">' +
				'	</div>' +
				'</div>');
			point++
		});
		_this.closest('.slider-wrap').find('.slider-images-wrap .image-wrap').removeClass('active');
		_this.closest('.slider-wrap').find('.slider-images-wrap .image-wrap:first').addClass('active');
		resortSliderWrap(_this);
		$('.overview-popup').hide();
	});
}

function sliderDataFill(obj, name){
	var sliderData = {
		name: name,
		type: 'slider',
		items: []
	};
	obj.find('.slider-list-wrap').find('.slider-content-element').each(function(){
		var temp = {
			pos: $(this).index(),
			alt: $(this).find('.element-alt').text().trim()
		};
		temp.uploaded = ($(this).find('.element-size').text().trim().length > 0)
			? $(this).find('.element-title').text().trim()
			: '';
		temp.image = ($(this).find('.element-size').text().trim().length > 0)
			? ''
			: $(this).find('.element-image').find('img').attr('src');
		sliderData.items.push(temp);
	});
	return sliderData;
}

function buildCustomFiledData(_this){
	var element = {
		name: _this.attr('data-name'),
		type: _this.attr('data-type'),
		capt: _this.children('legend').text(),
		items: []
	};
	switch(_this.attr('data-type')){
		case 'checkbox':
		case 'radio':
			element.name = _this.attr('data-name');
			element.type = _this.attr('data-type');
			element.capt = _this.find('label span').text();
			element.items =(_this.find('input').prop('checked') == true)? 1: 0;
		break;

		case 'fieldset':
			var items = [];
			_this.children('.row-wrap, fieldset').each(function(){
				items.push(buildCustomFiledData($(this)));
			});
			element.items = items;
		break;

		case 'string':
		case 'email':
		case 'number':
		case 'range':
			element.items = _this.find('input').val();
		break;

		case 'file':
			var upload = true;
			if(_this.find('.upload-image-preview').text().length > 0){
				if(_this.find('.upload-image-preview').find('img').length > 0){
					if(_this.find('.upload-image-preview').find('img').attr('alt') != ''){
						upload = false;
						element.items = formData.append(_this.attr('data-name'), _this.find('.upload-image-preview').find('img').attr('alt'));
					}
				}else{//if text file
					upload = false;
				}
			}
			if(upload){
				if(_this.find('input[type=file]').prop('files')[0] != undefined){
					element.items = formData.append(_this.attr('data-name'), _this.find('input[type=file]').prop('files')[0]);
				}else{
					element.items = formData.append(_this.attr('data-name'), '');
				}
			}else{
				element.items = formData.append(_this.attr('data-name'), _this.find('.upload-image-preview').text());
			}
		break;

		case 'textarea':
			element.items = _this.find('textarea').val();
		break;

		case 'fulltext':
			element.items = CKEDITOR.instances[_this.attr('data-name')].getData();
		break;

		case 'table':
			var tableData = {head: [], body:[]};
			_this.find('.item-list thead th:gt(0)').each(function(){
				tableData.head.push($(this).find('input[name=tableHead]').val());
			});
			_this.find('.item-list tbody tr').each(function(){
				var tempRow = [];
				$(this).find('td:gt(0)').each(function(){
					tempRow.push($(this).find('input[name=tableBody]').val())
				});
				tableData.body.push(tempRow);
			});
			element.items = tableData;
		break;

		case 'slider':
			element = sliderDataFill(_this, _this.attr('data-name'));
			element.capt = _this.find('legend').text();
		break;

		case 'custom_slider':
			var items = [];
			var slide_pos = 0;
			_this.find('.custom-slider-content-wrap').find('.custom-slide-container').each(function(){
				var slide = {
					preview: ($(this).find('input[name=previevBar]').prop('checked') == true)? 1: 0,
					items: []
				};
				$(this).find('input[type=text], input[type=file], textarea').each(function(){
					var item = {
						name: $(this).attr('name'),
						capt: $(this).attr('placeholder'),
						type: '',
						value: ''
					};
					switch($(this).attr('type')){
						case 'file':
							item.type = 'file';
							if( ($(this).closest('.row-wrap').find('img').attr('alt') != undefined) && ($(this).closest('.row-wrap').find('img').attr('alt').length > 0) ){
								formData.append(_this.attr('data-name')+'-'+$(this).attr('name')+'-slide'+slide_pos, $(this).closest('.row-wrap').find('img').attr('alt'));
							}else{
								if($(this).prop('files')[0] != undefined) {
									formData.append(_this.attr('data-name')+'-'+$(this).attr('name')+'-slide'+slide_pos, $(this).prop('files')[0]);
								}
							}
						break;
						case 'text':
							item.type	= 'string';
							item.value	= $(this).val();
						break;
						default:
							item.type	= 'text';
							item.value	= $(this).val();
					}
					slide.items.push(item);

				});
				slide_pos++;
				items.push(slide);
			});
			element.items = items;
		break;
		case 'articles':
		case 'category':
		case 'products':
		case 'promo':
			var items = [];
			if(_this.find('select').length > 0){
				items.push(_this.find('select').val());
			}else if(_this.find('.chbox-selector-wrap').length > 0){
				_this.find('.chbox-selector-wrap').each(function(){
					$(this).find('.checkbox-item-wrap').each(function(){
						if ($(this).find('input[name=category]').prop('checked') == true) {
							items.push($(this).find('input[name=category]').val());
						}
					});
				});
			}else if(_this.find('.optional-list-wrap').length > 0){
				_this.find('.optional-list-wrap li').each(function(){
					var temp = {
						id:			$(this).attr('data-id'),
						position:	$(this).index(),
						refer_to:	$(this).closest('ul').attr('data-refer')
					}
					items.push(temp);
				});
			}
			element.items = items;
		break;
	}
	return element;
}
function sliderSortable(){
	$(document).find('.slider-wrap').find('.slider-list-wrap').sortable({
		update: function(event, ui){
			var oldPos = $(ui.item).attr('data-position');
			var newPos = $(ui.item).index();
			var element = $(ui.item).closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap[data-position='+oldPos+']');
			$(ui.item).closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap[data-position='+oldPos+']').remove();
			if(newPos > oldPos){
				$(ui.item).closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap[data-position='+newPos+']').after(element);
			}else{
				$(ui.item).closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap[data-position='+newPos+']').before(element);
			}
			resortSliderWrap($(this));
		}
	});
}
//Fixed nav menu building
function buildFixedNavMenu(){
	$('.fixed-navigation-menu ul').empty();
	$(document).find('.work-place-wrap').children('div').children('fieldset').each(function(){
		var slug = str2url($(this).children('legend').text());
		$(this).attr('data-link',slug);
		$('.fixed-navigation-menu ul').append('<li data-link="'+slug+'">'+$(this).children('legend').text()+'</li>');
	});
	$(document).find('.fixed-navigation-menu').on('click','li',function(){
		var link = $(this).attr('data-link');
		goTo('fieldset[data-link='+link+']');
	});
}

function addTelMask(){
	$(document).find('.needPhoneMask').mask('0 (000) 000-00-00', {
		placeholder: '_ (___) __-__-__',
		pattern: /[0-9*]/
	});
}

$(document).ready(function(){
	$('footer .error-log').click(function(){
		$('.error-popup').show();
		$('footer .error-log').hide();
	});

	$(document).on('click','.close-popup',function(){
		$(this).parent().hide();
	});

	$(document).on('focus','.errorInp',function(){
		$(this).removeClass('errorInp');
	});

	CKEDITOR.replaceAll('needCKE');

	addTelMask();

	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		currentText: 'Сегодня',
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false
	};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
	$('.needDatePicker').datepicker();

	// Slider
	$(document).on('click','input[name=loadFileToSlider]', function(){
		$(this).closest('.slider-manage-buttons').find('input[name=imageFileToUpload]').trigger('click');
	});

	var point = 0;
	$(document).on('change', 'input[name=imageFileToUpload]', function(){
		point = 0;
		var formdata_iter = point;
		var sliderName = $(this).closest('fieldset').attr('data-name');
		var _this = $(this);
		var count = $(this).prop('files').length;
		for(var i=0; i<count; i++){
			var reader = new FileReader();
			reader.onload = function(e){
				_this.closest('.slider-wrap').find('.slider-images-wrap').append('' +
					'<div class="image-wrap" data-position="'+point+'">' +
						'<img src="'+e.target.result+'" alt="">' +
						'<div class="attributes-wrap">' +
							'<input name="altText" type="text" class="text-input" placeholder="Альтернативный текст&hellip;" style="width: 90%;">' +
							'<a href="#" class="drop-image button" title="Удалить">' +
								'<img src="/img/drop.png" alt="">' +
							'</a>' +
						'</div>' +
					'</div>');

				_this.closest('.slider-wrap').find('.slider-list-wrap').append('' +
					'<div class="slider-content-element" data-position="'+point+'">' +
						'<div class="element-title">'+(_this.prop('files')[point]['name'])+'</div>' +
						'<div class="element-size">'+(_this.prop('files')[point]['size'] /1024).toFixed(2)+' Kb</div>' +
						'<div class="element-image">' +
							'<img src="'+e.target.result+'" alt="">' +
						'</div>' +
						'<div class="element-alt"></div>' +
						'<div class="element-drop">' +
							'<img src="/img/drop.png" alt="Удалить" title="Удалить">' +
						'</div>' +
					'</div>');
				for(var key of formData.keys()){
					if(key == sliderName+'_file_'+formdata_iter){
						formdata_iter++;
					}
				}
				formData.append(sliderName+'_file_'+formdata_iter , _this.prop('files')[point]);
				if(point == count-1){
					_this.closest('.slider-wrap').find('.slider-images-wrap .image-wrap').removeClass('active');
					_this.closest('.slider-wrap').find('.slider-images-wrap .image-wrap:first').addClass('active');
					resortSliderWrap(_this);
				}
				point++;
				formdata_iter++;
			};
			reader.readAsDataURL($(this).prop('files')[i]);
		}
	});
	// slider controls
	$(document).on('click', '.slider-controls', function(){
		var sliderLength = $(this).closest('.slider-preview').find('.slider-images-wrap .image-wrap').length -1;
		var activeEl = $(this).closest('.slider-preview').find('.slider-images-wrap').find('.active').index();
		if($(this).hasClass('left')){
			var showEl = (activeEl == 0)? sliderLength: activeEl -1;
		}else{
			var showEl = (activeEl == sliderLength)? 0: activeEl+1;
		}
		$(this).closest('.slider-preview').find('.slider-images-wrap .image-wrap').removeClass('active');
		$(this).closest('.slider-preview').find('.slider-images-wrap .image-wrap:eq('+showEl+')').addClass('active');
	});

	// slider sortable
	sliderSortable();

	$(document).on('keyup','input[name=altText]', function(){
		var position = $(this).closest('.image-wrap').index();
		$(this).closest('.slider-wrap').find('.slider-list-wrap').find('.slider-content-element[data-position='+position+']').find('.element-alt').text($(this).val());
	});
	//drop image
	$(document).on('click', '.drop-image', function(e){
		e.preventDefault();
		var sliderName = $(this).closest('fieldset').attr('data-name');
		var position = $(this).closest('.image-wrap').index();
		var _that = $(this).closest('.slider-images-wrap');
		$(this).closest('.slider-wrap').find('.slider-list-wrap').find('.slider-content-element[data-position='+position+']').remove();
		$(this).closest('.image-wrap').remove();
		resortSliderWrap(_that);
		formData.delete(sliderName+'_file_'+position);
	});
	$(document).on('click', '.element-drop', function(){
		var sliderName = $(this).closest('fieldset').attr('data-name');
		var position = $(this).closest('.slider-content-element').index();
		var _that = $(this).closest('.slider-list-wrap');
		$(this).closest('.slider-wrap').find('.slider-images-wrap').find('.image-wrap[data-position='+position+']').remove();
		$(this).closest('.slider-content-element').remove();
		resortSliderWrap(_that);
		formData.delete(sliderName+'_file_'+position);
	});
	//add images from uploaded
	$(document).on('click','input[name=getImgToSlider]', function(){
		var _this = $(this);
		var token = $('.central-block').attr('data-token');
		$.ajax({
			url:	'/admin/get_server_images',
			type:	'GET',
			headers:{'X-CSRF-TOKEN': token},
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/get_server_images');
			},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success') {
						$('.overview-popup .popup-images').empty();
						for(var img in data['folders']){
							$('.overview-popup .popup-images').append('<div class="image-container"><img src="/'+data['folders'][img]+'" alt="'+data['folders'][img]+'"></div>');
						}
						$('.overview-popup').show();
						caseImageInOverviewPopup(_this);
					}else{
						showErrors(data, '/admin/get_server_images');
					}
				}catch(e){
					showErrors(e+data, '/admin/get_server_images');
				}
			}
		});
	});
	// /Slider

	//Custom fields table
	$(document).on('click','input[name=addRowToTable]', function(){
		var colCount = $(this).closest('fieldset').find('.item-list').find('tr th').length;
		var tag = '<tr>';
		for(var i=0; i<colCount; i++){
			if(i==0){
				tag += '<td><a href="#" class="drop-row block-button" title="Удалить"><img src="/img/drop.png" alt=""></a></td>';
			}else{
				tag += '<td><input name="tableBody" type="text" class="text-input" placeholder="Содержимое ячейки&hellip;"></td>';
			}
		}
		tag += '</tr>';
		$(this).closest('fieldset').find('.item-list').find('tbody').append(tag);
	});

	$(document).on('click', 'a.drop-row', function(e){
		e.preventDefault();
		$(this).closest('tr').remove();
	});
	// /Custom fields table

	//Custom fields custom slider
	//add slide
	$(document).on('click', 'input[name=customSliderAddSlide]', function(){
		var clone = $(this).closest('fieldset').find('.custom-slider-content-wrap').find('.custom-slide-container:first').clone(false);
		clone.find('.row-wrap').each(function(){
			$(this).find('input').val('');
			$(this).find('input').prop('checked',false);
			$(this).find('textarea').text('');
			$(this).find('textarea').val('');
			$(this).find('.upload-image-preview').empty();
		});
		$(this).closest('fieldset').find('.custom-slider-content-wrap').append(clone);
		$(this).closest('fieldset').find('.custom-slider-content-wrap').find('.custom-slide-container').removeClass('active');
		$(this).closest('fieldset').find('.custom-slider-content-wrap').find('.custom-slide-container:last').addClass('active');
	});

	//drop current slide
	$(document).on('click', 'input[name=customSliderDropCurrentSlide]', function(){
		if($(this).closest('fieldset').find('.custom-slider-content-wrap .custom-slide-container').length > 1){
			$(this).closest('fieldset').find('.custom-slider-content-wrap').find('.active').remove();
			$(this).closest('fieldset').find('.custom-slider-content-wrap .custom-slide-container').removeClass('active');
			$(this).closest('fieldset').find('.custom-slider-content-wrap .custom-slide-container:first').addClass('active');
		}else{
			$(this).closest('fieldset').find('.custom-slider-content-wrap').find('.custom-slide-container').find('.row-wrap').each(function(){                $(this).find('input').val('');
				$(this).find('textarea').text('');
				$(this).find('textarea').val('');
			});
		}
	});

	//slider controls
	$(document).on('click', '.slider-controls-bg', function(){
		var sliderLength = $(this).closest('.custom-slider-wrap').find('.custom-slider-content-wrap .custom-slide-container').length -1;
		var activeEl = $(this).closest('.custom-slider-wrap').find('.custom-slider-content-wrap').find('.active').index();
		if($(this).hasClass('left')){
			var showEl = (activeEl == 0)? sliderLength: activeEl -1;
		}else{
			var showEl = (activeEl == sliderLength)? 0: activeEl+1;
		}
		$(this).closest('.custom-slider-wrap').find('.custom-slider-content-wrap .custom-slide-container').removeClass('active');
		$(this).closest('.custom-slider-wrap').find('.custom-slider-content-wrap .custom-slide-container:eq('+showEl+')').addClass('active');
	});
	// /Custom fields custom slider

	//Autoslug
	$(document).on('keyup', 'input[name=title], input[name=slug]', function(){
		var str = str2url($(this).val());
		$(document).find('input[name=slug]').val(str);
	});
	// /Autoslug

	//Single File Upload
	$(document).on('change','.file-upload-button', function(){
		var _this = $(this);
		var reader = new FileReader();
		reader.onload = function(e){
			var mimeType = e.target.result.split(",")[0].split(":")[1].split(";")[0].split('/')[0];
			if(mimeType == 'image'){
				_this.closest('.row-wrap').find('.upload-image-preview').empty().append('<img src="'+e.target.result+'" alt="">');
			}else{
				_this.closest('.row-wrap').find('.upload-image-preview').empty();
			}
		}
		reader.readAsDataURL($(this).prop('files')[0]);
	});
	// /Single File Upload

	//sorting
	if( (typeof $('.main-block').attr('data-sort') != 'undefined') && (typeof $('.main-block').attr('data-direction') != 'undefined') ){
		var sort = $('.main-block').attr('data-sort');
		var direction = $('.main-block').attr('data-direction');
		$('.item-list thead #'+sort+' a.'+direction).addClass('active');
	}

	//pseudo selector
	$(document).find('.pseudo-selector').on('click','li',function(e){
		e.stopPropagation();
		if($(this).hasClass('active')){
			$(this).closest('ul').find('li').css({'display':'flex'})
		}else{
			$(this).closest('ul').find('li').removeClass('active').css({'display':'none'});
			$(this).addClass('active').css({'display':'flex'});
		}
		$(document).click(function(){
			$(document).find('.pseudo-selector li:not(.active)').css({'display':'none'});
		});
	});

	//faker
	$(document).on('change','.faker-changeable',function(){
		$(this).find('option:first').attr('disabled','disabled');
		$(this).closest('div').find('.faker-input').val($(this).val());
	});
	//optional categories
	$('.optional-list-wrap .sort-controls').on('click','p',function(){
		var direction = $(this).attr('data-direction');
		if(direction == 'up'){
			if($(this).closest('li').prev().length > 0){
				var el = $(this).closest('li').prev();
				$(this).closest('li').after(el);
			}
		}else{
			if($(this).closest('li').next().length > 0){
				var el = $(this).closest('li').next();
				$(this).closest('li').before(el);
			}
		}
	});
});