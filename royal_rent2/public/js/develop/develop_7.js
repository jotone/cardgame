function arr_diff (a1, a2) {
	var a = [], diff = [];
	for(var i = 0; i < a1.length; i++) a[a1[i]] = true;
	for(var i = 0; i < a2.length; i++){
		if (a[a2[i]]) delete a[a2[i]]; else a[a2[i]] = true;
	}
	for(var k in a) diff.push(k);
	return diff;
};
//Begin Map
var map;
function initMap() {
	if($('section.contacts').length){
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: 48.864137, lng: 2.345414},
			// zoom - определяет масштаб. 0 - видно всю платнеу. 18 - видно дома и улицы города.
			zoom: 15,
			//отключить скорлл
			scrollwheel: false
		});

		var marker = new google.maps.Marker({
			position: {lat: 48.863883, lng: 2.348976},
			// Указываем на какой карте он должен появится. (На странице ведь может быть больше одной карты)
			map: map,
			title: 'ROYAL RENT',
			icon: '/img/marker.png'
		});

		var marker = new google.maps.Marker({
			position: {lat: 48.868294, lng: 2.310184},
			map: map,
			title: 'ROYAL RENT',
			icon: '/img/marker.png'
		});
	}
}
//End Map
// Begin Tabs
function eventTabs(){
	$('.tab-item').not(':first').hide();
		$('.js-tab .tab').click(function(){
		$('.js-tab .tab').removeClass('active').eq($(this).index()).addClass('active');
		$('.js-tab .tab-item').hide().eq($(this).index()).fadeIn();
	}).eq(0).addClass('active');
}
//End Tabs

//Begin Services
function services(){ //develop1
	var services;
	var elServices = $(".el-services");
	var w = $(window).width();
	if(w > 920){
		setTimeout(function(){
			var flag = true;
			if( flag == true && $(".sn-outer-wrapper").length){
				flag = false;
				$("body").prepend(elServices);
				$(".el-services").addClass("appended");
				$('.el-services').removeClass('on-his-place');
			}
		}, 1000);
		if($('.foot-breadcrumbs').length){
			setTimeout(function(){
				services = $('.footer').offset().top-$('.foot-breadcrumbs').outerHeight();
			},2000);

		}else{
			setTimeout(function(){
				services = $('.footer').offset().top+$('.el-services').height();
			},1000);
		}
	}else{
		$(".main").append(elServices);
		$('.el-services').removeClass('active');
		$('.el-services').addClass('on-his-place');
	}
	$(window).scroll(function(){
		w = $(window).width();
		if(w > 920){
		//console.log("scroll-height: " + ($(window).scrollTop()+$(window).height()));
		//console.log("services-position: "+ services);
		// if ($('.order-confirm').length || $('.wrapper-about').length ) {
		//   $(".main").append(elServices);
		//   $('.el-services').addClass('active');
		//   debugger;
		// } else {
			if($(window).scrollTop()+$(window).height() >= services || $(window).scrollTop()+$(window).height() < $(window).height() + 300){
				$(".main").append(elServices);
				$('.el-services').removeClass('active');
				$('.el-services').addClass('on-his-place');
			}else if($(window).scrollTop()+$(window).height() < services){
				$('.el-services').addClass('active');
				$("body").prepend(elServices);
				$('.el-services').removeClass('on-his-place');
			}
		}
	});
}
//End Services

//Begin News Content
function newsContent(){ //develop1
	if($('.news').length){
		var news = $('.news').offset().top;
		$(window).scroll(function(){
			if($(window).scrollTop() >= news){
				$('.news-content').addClass('active');
			}else if($(window).scrollTop() < news){
				$('.news-content').removeClass('active');
			}
		});
	}
}
//End News Content

function calcModelChangesByMark(slug){
	$.ajax({
		url:	'/get_models_by_mark',
		type:	'GET',
		data:	{mark: slug},
		success:function(data) {
			try{
				data = JSON.parse(data);
				$('.transport-services-left select[name=modelTransport]').empty();
				for(var i in data){
					$('.transport-services-left select[name=modelTransport]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>')
				}
				$('.transport-services-left select[name=modelTransport]').trigger('refresh');
				carByModel($('#markTransport').val(), data[0]['slug'])
			}catch(e){}
		}
	});
}
function carByModel(mark, model){
	$.ajax({
		url:	'/get_car_by_model',
		type:	'GET',
		data:	{mark:mark, model:model},
		success:function(data){
			data = JSON.parse(data);
			var tag = '';
			if(typeof data[0].color != 'undefined'){
				tag += '<div class="transport-services-pic"><img src="'+data[0]['img_url'][0]['img']+'" alt="'+data[0]['img_url'][0]['alt']+'"></div>';
				tag += '<p>Минимальный заказ — '+data[0]['time']+' часа</p>' +
					'<p>Стоимость — от '+data[0]['price']+' руб. в час</p>';
				if(data[0]['color'].length >0){
					tag += '<div class="transport-services-color">';
					for(var color_iter in data[0]['color']){
						tag += '<a href="#" style="background-color: '+data[0]['color'][color_iter]['color']+'" title="'+data[0]['color'][color_iter]['title']+'"></a>'
					}
					tag += '</div>';
				}
				tag += '<a class="transport-services-btn el-button corp-contract-link" href="#corp-contract">Стать корпоративным клиентом</a>';
			}
			$('.transport-services-center').empty().append(tag);
		}
	})
}
function filterMarkByCategory(slug){
	var category = $('.left-bar .sort-item select[name=driver]').val();
	$.ajax({
		url:	'/get_mark_by_category',
		type:	'GET',
		data:	{slug:slug, category:category},
		success:function(data){
			try{
				data = JSON.parse(data);
				$('.left-bar select[name=carMark]').empty().append('<option></option>');
				if(data.length>0){
					for(var i in data){
						$('.left-bar select[name=carMark]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
					}
				}
				$('.left-bar .driver').trigger('refresh');
			}catch(e){}
		}
	});
}
function buildCarView(data){
	var tag = '';
	var promo = {val:0,type:''};
	if((data['data']['number_0']['value'].length > 0) && (data['data']['number_0']['value'] > 0)){
		promo.val = data['data']['number_0']['value'];
		promo.type = 'percent';
	}else{
		if(data['promo'].length > 0){
			promo.val = data['promo']['value'];
			promo.type = data['promo']['type'];
		}
	}
	tag += '<div class="car-item" data-pos="'+data['id']+'">';
	if(promo.val > 0){
		tag += '<div class="sale"><span>скидка</span><br>';
		if(promo.type == 'percent'){
			tag += '<span><b>'+promo.val+'</b>%</span>';
		}else{
			tag += '<span>&minus;<b>'+promo.val+'</b> <i>₽</i></span>';
		}
		tag += '</div>';
	}

	tag += '<div class="photo">';
	if(typeof data['img_url']['img'] != 'undefined'){
		tag += '<a href="/car/'+data['upper_cat']+'/'+data['slug']+'"><img src="'+data['img_url']['img']+'" alt="'+data['img_url']['alt']+'"></a>';
	}
	tag +=	'</div>'+
		'<a href="#photo-slider" class="view-photo"><span><img src="/img/pic-icon.png" alt=""></span><span>Посмотреть реальные фото</span> </a>'+
		'<p>'+data['title']+'</p>';
	if(data['price'] > 0){
		var type = ('car_nonedriver' == data['upper_cat'])? ' руб. в сутки': ' руб. в час';
		tag += '<p>от '+ new Intl.NumberFormat('ru-RU').format(data['price'])+type+'</p>';
	}
	tag += '<div class="colors"><div class="color" style="background-color:'+data['color']['color']+'" title="'+data['color']['title']+'"></div></div>';
	tag += '<a href="/car/'+data['upper_cat']+'/'+data['slug']+'" class="el-button">Взять в аренду</a></div>';
	return tag;
}

function sendFilterData(items){
	var filterData = JSON.stringify({
		parent:		$('.left-bar select[name=driver]').val(),
		category:	$('.left-bar #transportCategories .dropdown-transport>li>a').attr('data-type'),
		mark:		$('.left-bar select[name=carMark]').val(),
		car_event:	$('.left-bar select[name=carEvent]').val(),
		min:		$('.left-bar input[name=minAmount]').val().substr(0, $('.left-bar input[name=minAmount]').val().length -1),
		max:		$('.left-bar input[name=maxAmonut]').val().substr(0, $('.left-bar input[name=maxAmonut]').val().length -1),
		color:		$('.left-bar select[name=carColor]').val(),
		sort_by:	$(document).find('.sort-bar .dropdown-sorting').attr('data-order'),
		items:		items
	});

	$.ajax({
		url:	'/get_cars_by_filter',
		type:	'GET',
		data:	{filter:filterData},
		beforeSend:function(){
			$('.content-wrap .all-cars').html("<img class='preload-image' src='http://royalrent.sheepfish.pro/img/25.gif' alt='25.gif' style='width: 100px; min-height: 100px; display: block; margin: 100px auto;'>")
		},
		error:function(e){
			$('.all-cars').append(e.responseText);
		},
		success:function(data){
			$('.content-wrap .all-cars .preload-image').remove();
			$('#carFilterPagination #preloader').hide();
			try{
				data = JSON.parse(data);
				if(data.length < 6){
					$('#carFilterPagination #showMore').hide();
				}else{
					$('#carFilterPagination #showMore').show();
				}
				var tag = '';

				for(var i = 0; i<data.length; i++){
					tag += buildCarView(data[i]);
				}
				$('.all-cars').append(tag);
			}catch(e){
				$('.all-cars').append(e+data);
			}
		}
	});
}

$(document).ready(function(){
	window.orderSteps = 0;
	if ($(window).width() <= 854) {
		$('.footer .footer-list .footer-list-col .footer-list-item h3').click(function() {
			$(this).closest('.footer-list').find('.footer-list-col .footer-list-item').removeClass('current');
			$(this).closest('.footer-list-item').addClass('current');
			$(this).closest('.footer-list').find('.footer-list-col').each(function() {
				$(this).find('.footer-list-item').each(function() {
					if (!$(this).hasClass('current')) {
						$(this).find('ul').slideUp();
					}
				});
			});
			$(this).next('ul').slideToggle();
		});
	}

	// backgroundScrollAnimation($(".el-header-bg"));

	$("#phone").mask("+9 (999) 999-99-99");

	$('#tariff-select').styler();
	$('#brand-select').styler();
	$('#model-select').styler();
	$('#get-select').styler();
	$('#return-select').styler();

//Begin Fancybox
	$('.corp-contract-link').fancybox({
		openEffect:	'fade',
		closeEffect:'fade',
		autoSize:	true,
		width:		800,
		height:		884,
		maxWidth:	'100%',
		wrapCSS:	'corp-сontract-wrap',
		'closeBtn':	true,
		fitToView:	true,
		padding:	'0'
	});

	$('.request-link').fancybox({
		openEffect:	'fade',
		closeEffect:'fade',
		autoSize:	true,
		width:		800,
		height:		615,
		maxWidth:	'100%',
		wrapCSS:	'request-wrap',
		'closeBtn':	true,
		fitToView:	true,
		padding:	'0'
	});
//End Fancybox

	// services();
	eventTabs();
	initMap();

//Begin Select Styler
	$('#typeTransport').styler();
	$('#markTransport').styler();
	$('#modelTransport').styler();
//End Select Styler

	//Reviews
	$(document).find('select[name=auto_has_driver]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url:	'/get_subcategory',
			type:	'GET',
			data:	{slug:slug},
			success:function(data){
				try{
					data = JSON.parse(data);
					$(document).find('select[name=choose] option:not(:first-child)').remove();
					for(var i in data){
						$(document).find('select[name=choose]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>')
					}
					$('select[name=choose]').trigger('refresh');
				}catch(e){}
			}
		});
	});
	$(document).find('select[name=choose]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url:	'/get_mark_by_category',
			type:	'GET',
			data:	{slug:slug},
			success:function(data){
				try{
					data = JSON.parse(data);
					$(document).find('select[name=auto_mark] option:not(:first-child)').remove();
					for(var i in data){
						$(document).find('select[name=auto_mark]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>')
					}
					$('select[name=auto_mark]').trigger('refresh');
				}catch(e){}
			}
		});
	});
	$(document).find('select[name=auto_mark]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url:	'/get_models_by_mark',
			type:	'GET',
			data:	{mark:slug},
			success:function(data){
				try{
					data = JSON.parse(data);
					$(document).find('select[name=auto_model] option:not(:first-child)').remove();
					for(var i in data){
						$(document).find('select[name=auto_model]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>')
					}
					$('select[name=auto_model]').trigger('refresh');
				}catch(e){}
			}
		});
	});
	// /Reviews

	//Auto Filters
	function showCars(path, itemsCount, sortType, take, emptyContent, recomended){
		$.ajax({
			url:	'/show_more_cars',
			type:	'GET',
			data:	{path:path, items:itemsCount, sort:sortType, take:take, recomended:recomended},
			beforeSend: function(){
				$('#carFilterPagination #showMore img').addClass("rotate-animation");
				// $('#carFilterPagination #preloader').show();
				$('.content-wrap .all-cars').html("<img class='preload-image' src='http://royalrent.sheepfish.pro/img/25.gif' alt='25.gif' style='width: 100px; min-height: 100px; display: block; margin: 100px auto;'>")

			},
			success:function(data){
				$('.content-wrap .all-cars .preload-image').remove();
				// $('#carFilterPagination #preloader').hide();
				$('#carFilterPagination #showMore img').removeClass("rotate-animation");

				try{
					data = JSON.parse(data);
					if(data.length < 6){
						$('#carFilterPagination #showMore').hide();
					}else{
						$('#carFilterPagination #showMore').show();
					}
					if(emptyContent === true){
						$('.all-cars').empty();
					}
					var tag = '';

					for(var i = 0; i<data.length; i++){
						tag += buildCarView(data[i]);
					}
					$('.all-cars').append(tag);
				}catch(e){}
			}
		});
	}

	$(document).find('#carFilterPagination').on('click', 'a', function(e){
		e.preventDefault();
		var path = window.location.pathname;
		var itemsCount = $(document).find('.all-cars .car-item').length;
		var sortType = $(document).find('.sort-bar .dropdown-sorting').attr('data-order');
		if( (typeof window.filterWasUsed != 'undefined') && (window.filterWasUsed > 0) ){
			sendFilterData($(document).find('.all-cars .car-item').length);
		}else{
			showCars(path, itemsCount, sortType, 6, false, 0);
		}
	});

	$('.sort .dropdown-sorting .main-link').click(function(e){
		e.preventDefault();
		$(this).closest('.sort').find('.dropdown-sorting ul').slideToggle(0);
		return false;
	});

	$('.dropdown-sorting ul li').click(function(e){
		e.preventDefault();
		$(this).closest('ul').find('li').css('display', 'block');
		$(this).css('display', 'none');
		var order = $(this).find('a').attr('data-type');
		var text = $(this).find('a p').text();
		$(this).closest('.dropdown-sorting').find('.main-link p span').text(text);
		$(this).closest('.dropdown-sorting').attr('data-order',order);
		$(this).closest('ul').slideUp('fast');
		var path = window.location.pathname;
		var sortType = $(document).find('.sort-bar .dropdown-sorting').attr('data-order');
		if( (typeof window.filterWasUsed != 'undefined') && (window.filterWasUsed > 0) ){
			$('.all-cars').empty();
			sendFilterData(0);
		}else{
			showCars(path, 0, sortType, 6, true, 0);
		}
		return false;
	});

	$('.sort-bar #showRecomended').on('click',function(e){
		e.preventDefault();
		var path = window.location.pathname;
		var sortType = $(document).find('.sort-bar .dropdown-sorting').attr('data-order');
		showCars(path, 0, sortType, 6, true, 1);
	});

	//transport filter
	$('.left-bar select[name=driver]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url: '/get_subcategory',
			type:'GET',
			data:{slug:slug},

			success:function(data){
				try{
					data = JSON.parse(data);
					$('.left-bar #transportCategories').empty();
					if(data.length>0){
						var tag = '' +
						'<p>Вид транспорта: <span>'+data[0]['title']+'</span></p>'+
						'<ul class="dropdown-transport">'+
							'<li>'+
								'<a href="#" class="main-link" data-refer="'+data[0]['refer']+'" data-type="'+data[0]['slug']+'">'+
								'<img src="'+data[0]['img_url']+'" alt="">'+
							'</a>'+
						'<ul>';
						for(var i in data){
							tag += '' +
							'<li data-refer="'+data[i]['refer']+'">'+
								'<a class="select-transport-category" href="#" data-type="'+data[i]['slug']+'">'+
									'<img src="'+data[i]['img_url']+'" alt="">'+
									'<p>'+data[i]['title']+'</p>'+
								'</a>'+
							'</li>';
						}
						tag += '</ul></li></ul><div class="arrows"><img src="/img/arrows.png" alt=""></div>';
						filterMarkByCategory(data[0]['slug']);
						$('.left-bar #transportCategories').append(tag);
					}
					$('.left-bar .driver').trigger('refresh');
				}catch(e){}
			}
		});
	});

	$(document).find('.wrap-sort').on('click', 'a.select-transport-category', function(e){
		e.preventDefault();
		var slug = $(this).attr('data-type');
		filterMarkByCategory(slug);
	});

	$(document).find('.left-bar').on('click','a.filter-button', function(e){
		e.preventDefault();
		window.filterWasUsed = 1;
		$('.all-cars').empty();
		sendFilterData(0);
	});

	$('.sort-bar a.reset').click(function(e){
		e.preventDefault();
		window.filterWasUsed = 0;
		$('.all-cars').empty();
		var path = window.location.pathname;
		var sortType = $(document).find('.sort-bar .dropdown-sorting').attr('data-order');
		var itemsCount = $(document).find('.all-cars .car-item').length;
		showCars(path, itemsCount, sortType, 6, true, 0);
	});
	// /transport filter
	// /Auto Filters

	$(document).on('click', '.all-cars .view-photo', function(e){
		var id = $(this).closest('.car-item').attr('data-pos');
		$.ajax({
			url:	'/get_car_by_id',
			type:	'GET',
			data:	{id:id},
			success:function(data){
				data = JSON.parse(data);
				var tagPhoto = '', tagSmPhoto = '';
				for(var i = 1; i< data['img_url'].length; i++){
					tagPhoto += '<div class="photo"><img src="'+data['img_url'][i]['img']+'" alt="'+data['img_url'][i]['alt']+'"></div>';
					tagSmPhoto += '<div class="sm-photo"><img src="'+data['img_url'][i]['img']+'" alt="'+data['img_url'][i]['alt']+'"></div>';
				}

				$('#photo-slider .big-popup').empty().append(tagPhoto);
				$('#photo-slider .small-popup').empty().append(tagSmPhoto);

				$.fancybox.open($('#photo-slider'), {
					openEffect  : 'fade',
					closeEffect : 'fade',
					autoSize:true,
					width : 1165,
					height : 782,
					maxWidth : '100%',
					wrapCSS:'slider-wrap',
					'closeBtn' : true,
					fitToView:true,
					padding:'0',
					afterShow: function(){
						 setTimeout(function(){
							 popup_nav('.small-popup');
							 popup_for('.big-popup');
							 // $('.popup-slider .big-popup').slick('setPosition');
						 }),1000;
					},
					afterClose: function(){
						$('.small-popup').slick('unslick');
						$('.big-popup').slick('unslick');
					}
				});
			}
		})
	});

	//business travel
	$('.transport-services-left select[name=typeTransport]').change(function(){
		var slug = $(this).val();
		$.ajax({
			url:    '/get_mark_by_category',
			type:   'GET',
			data:   {slug:slug},
			success:function(data){
				try{
					data = JSON.parse(data);
					$('.transport-services-left select[name=markTransport]').empty();
					for(var i in data){
						$('.transport-services-left select[name=markTransport]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>')
					}
					$('.transport-services-left select[name=markTransport]').trigger('refresh');

					if(data.length > 0) {
						calcModelChangesByMark(data[0]['slug'])
					}
				}catch(e){}
			}
		});
	});
	$(document).on('change', '.transport-services-left select[name=markTransport]', function(){
		var slug = $(this).val();
		calcModelChangesByMark(slug)
	});

	$(document).on('change', '.transport-services-left select[name=modelTransport]', function(){
		var mark = $('.transport-services-left select[name=markTransport]').val();
		var model = $(this).val();
		carByModel(mark, model);
	});
	// /business travel

	//car order with driver
	$(document).on('click','button[name=driverNextStep]', function(e){
		var tarif = $('.tariffs .chosen').closest('.tarif-item').attr('data-pos');
		if(typeof tarif != 'undefined'){
			validate('#driverOrder', {submitFunction: nextStepDriver});
		}else{
			alert('Выбрите тариф.');
		}
	});

	$(document).find('.headline[data-type=withDriver] .steps').on('click','li',function(){
		if($(this).index() > 0){
			$('#driverOrder button[name=driverNextStep]').click();
		}else{
			$(document).find('.tab-switch:last').removeClass('active');
			$(document).find('.tab-switch:first').addClass('active');
			$('.headline ul li').removeClass('active');
			$('.headline ul li:eq(0)').addClass('active');
		}
	});

	//car order none driver
	$(document).on('click','button[name=nonedriverStepOne]',function(){
		validate('#noneDriverOrderStepOne', {submitFunction: noneDriverStepTwo});
	});

	$(document).on('click','button[name=nonedriverStepTwo]', function(){
		window.orderSteps = 2;
		$(document).find('.tab-switch').removeClass('active');
		$(document).find('.tab-switch:last').addClass('active');
		$('.headline ul li').removeClass('active');
		$('.headline ul li:last').addClass('active');
		noneDriverRefillFields();
	});

	$(document).find('.headline[data-type=noneDriver] .steps').on('click', 'li', function(){
		if($(this).index() <= window.orderSteps){
			$(document).find('.headline ul li').removeClass('active');
			$(this).addClass('active');
			$(document).find('.tab-switch').removeClass('active');
			$(document).find('.tab-switch:eq('+$(this).index()+')').addClass('active');
			noneDriverRefillFields();
		}
	});
});

function noneDriverRefillFields(){
	if($('.step-null .el-tariffs .chosen').length > 0){
		$('.third-step .wrap-confirm[data-name=current-tarif] .tar-name').text($('.step-null .el-tariffs .chosen').prev('p').text());
		$('.third-step .wrap-confirm[data-name=current-tarif] .price').text($('.step-null .el-tariffs .chosen .price').text());
		$('.third-step .wrap-confirm[data-name=current-tarif] .tar-desc>.item-desc>p').text($('.step-null .el-tariffs .chosen .item-desc').text());
		$('.third-step .wrap-confirm[data-name=current-tarif]').show();
	}else{
		$('.third-step .wrap-confirm[data-name=current-tarif]').hide();
	}

	if($('.step-one .el-tariffs .chosen').length > 0){
		$('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif').remove();

		$('.step-one .el-tariffs .chosen').each(function(){
			$('.third-step .wrap-confirm[data-name=car-equip]').append('<div class="cur-tarif">'+
				'<div class="tar-name">'+$(this).prev('p').text()+'</div>'+
				'<div class="tar-desc">'+
					'<div class="price">'+$(this).find('.price').text()+'</div>'+
					'<div class="pic">'+$(this).find('.item-pic').html()+'</div>'+
				'</div>'+
			'</div>');
		});
		$('.third-step .wrap-confirm[data-name=car-equip]').show();
	}else{
		$('.third-step .wrap-confirm[data-name=car-equip]').hide();
	}

	$('.third-step .wrap-confirm[data-name=responsibility] .desc').text($('.step-one select[name=responsibility] option:selected').text());
	$('.third-step .wrap-confirm[data-name=damage_coverage] .desc').text($('.step-one select[name=damage_coverage] option:selected').text());
	if($('.step-one .wrap-zones .zones input[type=checkbox]:checked').length > 0){
		$('.third-step .wrap-confirm[data-name=ride_out] .desc:gt(0)').remove();
		$('.step-one .wrap-zones .zones input[type=checkbox]:checked').each(function(){
			$('.third-step .wrap-confirm[data-name=ride_out]').append('<div class="desc">'+$(this).next('label').text()+'</div>');
		});
	}else{
		$('.third-step .wrap-confirm[data-name=ride_out]').hide();
	}
	var dateStart = $('#noneDriverOrderStepOne .page-line-item[data-name=start] .left-part p').text()+':'+$('#noneDriverOrderStepOne .page-line-item[data-name=start] .right-part p').text();
	var dateFinish = $('#noneDriverOrderStepOne .page-line-item[data-name=finish] .left-part p').text()+':'+$('#noneDriverOrderStepOne .page-line-item[data-name=finish] .right-part p').text();
	var checkStart = dateStart.match(/^\d{2}\/\d{2}\/\d{2}/);
	var checkFinish = dateFinish.match(/^\d{2}\/\d{2}\/\d{2}/);
	if( (checkStart == null) || (checkFinish == null) ){
		alert('Укажите дату аренды автомобиля.');
	}else{
		var dateStart = {
			year:	'20'+dateStart.substr(6,2),
			month:	dateStart.substr(3,2)-1,
			day:	dateStart.substr(0,2),
			hours:	dateStart.substr(9,2),
			minute:	dateStart.substr(12,2)
		}
		var dateFinish = {
			year:	'20'+dateFinish.substr(6,2),
			month:	dateFinish.substr(3,2)-1,
			day:	dateFinish.substr(0,2),
			hours:	dateFinish.substr(9,2),
			minute:	dateFinish.substr(12,2)
		}
		dateStart = new Date(dateStart.year, dateStart.month, dateStart.day, dateStart.hours, dateStart.minute, 0, 0);
		dateFinish = new Date(dateFinish.year, dateFinish.month, dateFinish.day, dateFinish.hours, dateFinish.minute, 0, 0);

		var hours = (dateFinish.getTime() - dateStart.getTime()) / (1000*60*60);
		var days = Math.ceil(hours / 24);
		var currentTarif = 0;
		switch(true){
			case ((dateStart.getDay()==6) && (dateFinish.getDay()==0) && (days == 2)):
				currentTarif = parseInt($('.step-null .el-tariffs .tarif-item:last').attr('data-pos'));
			break;
			case ((dateStart.getDay()==1) && (dateFinish.getDay()==5) && (days == 5)):
				currentTarif = parseInt($('.step-null .el-tariffs .tarif-item:eq(3)').attr('data-pos'));
			break;
			default:
				$('.step-null .el-tariffs .tarif-item').each(function(){
					var num = $(this).find('.item').prev('p').text().replace(/\D+/g, ' ').split(' ');
					num = arr_diff(num ,['']);
					if(num.length > 0){
						if(num.length < 2){
							if(days >= num[0]){
								currentTarif = parseInt($(this).attr('data-pos'));
								return false;
							}
						}else{
							if( (days >= num[0]) && (days <= num[1]) ){
								currentTarif = parseInt($(this).attr('data-pos'));
								return false;
							}
						}
					}
				});
		}
		if(currentTarif > 0){
			var recomendedTarif = {
				title: $('.step-null .el-tariffs .tarif-item[data-pos='+currentTarif+']>p').text(),
				price: $('.step-null .el-tariffs .tarif-item[data-pos='+currentTarif+'] .price').text()
			};
			var tarifPrice = parseInt($('.step-null .el-tariffs .tarif-item[data-pos='+currentTarif+'] .price').text().replace(/\D+/g, ''));
			var tarifType = $('.step-null .el-tariffs .tarif-item[data-pos='+currentTarif+'] .price').attr('data-type');
			var fullPrice = (tarifType == 'per_day')? days*tarifPrice: hours*tarifPrice;

			var equipPrice = 0;
			if($('.third-step .wrap-confirm[data-name=car-equip]').is(':visible')){
				$('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif').each(function(){
					equipPrice += parseInt($(this).find('.price').text().replace(/\D+/g, ''));
				});
			}

			var responsibility = $('.step-one select[name=responsibility]').val();
			if(responsibility == 'all'){
				responsibility = 0;
				$('.step-one select[name=responsibility] option').each(function(){
					var temp = $(this).text().replace(/\D+/g, '');
					if(temp.trim() != ''){
						responsibility += parseInt(temp);
					}
				});
			}else{
				responsibility = parseInt($('.step-one select[name=responsibility] option:selected').text().replace(/\D+/g, ''));
			}

			var damage_coverage = $('.step-one select[name=damage_coverage]').val();
			if(damage_coverage == 'all'){
				damage_coverage = 0;
				$('.step-one select[name=damage_coverage] option').each(function(){
					var temp = $(this).text().replace(/\D+/g, '');
					if(temp.trim() != ''){
						damage_coverage += parseInt(temp);
					}
				});
			}else{
				damage_coverage = parseInt($('.step-one select[name=damage_coverage] option:selected').text().replace(/\D+/g, ''));
			}

			var ride_out = 0;
			$('.wrap-zones input[type=checkbox]:checked').each(function(){
				ride_out += parseInt($(this).val());
			});
			var accesories = (equipPrice+responsibility+damage_coverage+ride_out)*days;

			var pledge = parseInt($('.order-info .info-title span[data-type=pledge]').text());
			fullPrice = fullPrice + accesories + pledge;
			$('.end-price b').text(new Intl.NumberFormat('ru-RU').format(fullPrice));

			//SEND LETTER
			$('.third-step[data-type=nonedriver] button[name=sendRequest]').off('click');
			$('.third-step[data-type=nonedriver] button[name=sendRequest]').on('click', function(e){
				e.preventDefault();
				var type = 'zakaz_bez_voditelya';
				var car = $('.third-step .car-info-details .name').text();
				var color = $('.third-step .car-info-details .color').text();

				var chosenTarif = {title: '', price: ''};
				if($('.third-step .wrap-confirm[data-name=current-tarif] .cur-tarif:visible').length > 0){
					chosenTarif.title = $('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif .tar-name').text();
					chosenTarif.price = $('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif .price').text();
				}
				var carEquip = [];
				if($('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif:visible').length > 0){
					$('.third-step .wrap-confirm[data-name=car-equip] .cur-tarif:visible').each(function(){
						carEquip.push({
							title: $(this).find('.tar-name').text(),
							price: $(this).find('.price').text()
						});
					});
				}
				var responsibility = $('.third-step .wrap-confirm[data-name=responsibility] .desc').text().trim();
				var damageCoverage = $('.third-step .wrap-confirm[data-name=damage_coverage] .desc').text().trim();
				var rideOut = [];
				$('.third-step .wrap-confirm[data-name=ride_out] .desc:gt(0)').each(function(){
					rideOut.push($(this).text().trim());
				});
				var endPrice = $('.third-step .end-price b').text();
				var token = $('input[name=_token]').val();

				var city = $('#noneDriverOrderStepOne select[name=city] option:selected').text();
				var startPlace = $('#noneDriverOrderStepOne input[name=startPlace]').val();
				var finishPlace = $('#noneDriverOrderStepOne input[name=finishPlace]').val();
				var age = $('#noneDriverOrderStepOne input[name=age]').val();
				var staz = $('#noneDriverOrderStepOne input[name=staz]').val();
				var payment = $('#noneDriverOrderStepOne select[name=oplata] option:selected').text();
				var userName = $('#noneDriverOrderStepOne input[name=name]').val();
				var userTel = $('#noneDriverOrderStepOne input[name=phone]').val();
				var userMail = $('#noneDriverOrderStepOne input[name=email]').val();

				var info_surname = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_surname]').val().trim(): '';
				var info_name = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_name]').val().trim(): '';
				var info_fathername = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_fathername]').val().trim(): '';
				var info_driver_id = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_driver_id]').val().trim(): '';
				var info_drive_date = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_drive_date]').val().trim(): '';
				var info_citizenship = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_citizenship]').val().trim(): '';
				var info_pasport_id = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_pasport_id]').val().trim(): '';
				var info_passport_date = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_passport_date]').val().trim(): '';
				var info_passport_issue = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_passport_issue]').val().trim(): '';
				var info_registration = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_registration]').val().trim(): '';
				var info_address_phone = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_address_phone]').val().trim(): '';
				var info_address = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_address]').val().trim(): '';
				var info_fact_phone = ($('#noneDriverOrderStepOne input[name=all_fields]').prop('checked') == true)? $('.full-info input[name=info_fact_phone]').val().trim(): '';
				$.ajax({
					url:	'/send_letter',
					type:	'POST',
					headers:{'X-CSRF-TOKEN': token},
					data:	{
						type:type,
						city:city, start_place:startPlace, finish_place:finishPlace, age:age, staz:staz,
						date_start:dateStart.toLocaleString(), date_finish:dateFinish.toLocaleString(), payment:payment,
						username:userName, usertel:userTel , usermail:userMail,
						info_surname:info_surname, info_name:info_name, info_fathername:info_fathername, info_driver_id:info_driver_id,
						info_drive_date:info_drive_date, info_citizenship:info_citizenship, info_pasport_id:info_pasport_id,
						info_passport_issue:info_passport_issue, info_passport_date:info_passport_date, info_registration:info_registration,
						info_address_phone:info_address_phone, info_address:info_address, info_fact_phone:info_fact_phone,
						car:car, color:color, chosen_tarif:chosenTarif, recomended_tarif:recomendedTarif,
						car_equip:carEquip, resp:responsibility, damage_coverage:damageCoverage, ride_out:rideOut,
						end_price:endPrice
					},
					success:function(data){
						if(data == 'success'){
							popNext("#call_success", "call-popup");
						}
					}
				});
			});
			// /SEND LETTER
		}
	}
}

function noneDriverStepTwo(){
	var dateStart = $('#noneDriverOrderStepOne .page-line-item[data-name=start] .left-part p').text()+':'+$('#noneDriverOrderStepOne .page-line-item[data-name=start] .right-part p').text();
	var dateFinish = $('#noneDriverOrderStepOne .page-line-item[data-name=finish] .left-part p').text()+':'+$('#noneDriverOrderStepOne .page-line-item[data-name=finish] .right-part p').text();
	var checkStart = dateStart.match(/^\d{2}\/\d{2}\/\d{2}/);
	var checkFinish = dateFinish.match(/^\d{2}\/\d{2}\/\d{2}/);
	if( (checkStart == null) || (checkFinish == null) ){
		alert('Укажите дату аренды автомобиля.');
	}else{
		window.orderSteps = 1;
		$(document).find('.tab-switch').removeClass('active');
		$(document).find('.tab-switch:eq(1)').addClass('active');
		$('.headline ul li').removeClass('active');
		$('.headline ul li:eq(1)').addClass('active');
	}
}

function nextStepDriver(){
	var dateStart = $('#driverOrder .page-line-item[data-name=start] .left-part p').text()+':'+$('#driverOrder .page-line-item[data-name=start] .right-part p').text();
	var dateFinish = $('#driverOrder .page-line-item[data-name=finish] .left-part p').text()+':'+$('#driverOrder .page-line-item[data-name=finish] .right-part p').text();
	var checkStart = dateStart.match(/^\d{2}\/\d{2}\/\d{2}/);
	var checkFinish = dateFinish.match(/^\d{2}\/\d{2}\/\d{2}/);
	if( (checkStart == null) || (checkFinish == null) ){
		alert('Укажите дату аренды автомобиля.');
	}else{
		$(document).find('.tab-switch:first').removeClass('active');
		$(document).find('.tab-switch:last').addClass('active');
		var item = $('.tab-switch.active').index() -1;
		$('.headline ul li').removeClass('active');
		$('.headline ul li:eq('+item+')').addClass('active');

		$('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif .tar-name').text($('.tariffs[data-name=tarif-type] .chosen').prev('p').text());
		$('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif .price').text($('.tariffs[data-name=tarif-type] .chosen .price').text());
		$('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif .tar-desc>.item-desc>p').text($('.tariffs[data-name=tarif-type] .chosen .item-desc').text());

		if( ($('#driverOrder .tariffs[data-name=equipment]').length >0) && ($('#driverOrder .tariffs[data-name=equipment] .chosen').length >0)){
			$('.third-step .wrap-confirm[data-name=equipment] .cur-tarif').remove();
			$('#driverOrder .tariffs[data-name=equipment] .chosen').each(function(){
				$('.third-step .wrap-confirm[data-name=equipment]').append('<div class="cur-tarif">'+
					'<div class="tar-name">'+$(this).prev().text()+'</div>'+
					'<div class="tar-desc">'+
						'<div class="price">'+$(this).find('.price').text()+'</div>'+
						'<div class="pic">'+$(this).find('.item-pic').html()+'</div>'+
					'</div>'+
				'</div>');
			});
			$('.third-step .wrap-confirm[data-name=equipment]').show();
		}else{
			$('.third-step .wrap-confirm[data-name=equipment]').hide();
		}

		var dateStart = {
			year:	'20'+dateStart.substr(6,2),
			month:	dateStart.substr(3,2)-1,
			day:	dateStart.substr(0,2),
			hours:	dateStart.substr(9,2),
			minute:	dateStart.substr(12,2)
		}
		var dateFinish = {
			year:	'20'+dateFinish.substr(6,2),
			month:	dateFinish.substr(3,2)-1,
			day:	dateFinish.substr(0,2),
			hours:	dateFinish.substr(9,2),
			minute:	dateFinish.substr(12,2)
		}
		dateStart = new Date(dateStart.year, dateStart.month, dateStart.day, dateStart.hours, dateStart.minute, 0, 0);
		dateFinish = new Date(dateFinish.year, dateFinish.month, dateFinish.day, dateFinish.hours, dateFinish.minute, 0, 0);

		var hours = (dateFinish.getTime() - dateStart.getTime()) / (1000*60*60);
		var days = Math.ceil(hours / 24);
		var price = parseInt($('.tariffs[data-name=tarif-type] .el-tariffs').attr('data-price'));

		var fullPrice = hours*price;
		if($('.wrap-confirm[data-name=equipment]').length > 0){
			$('.wrap-confirm[data-name=equipment] .price').each(function(){
				var equipPrice = parseInt($(this).text());
				fullPrice += days*equipPrice;
			});
		}
		$('.end-price b').text(new Intl.NumberFormat('ru-RU').format(fullPrice));

		$('.third-step[data-type=driver] button[name=sendRequest]').off('click');
		$('.third-step[data-type=driver] button[name=sendRequest]').on('click', function(e) {
			e.preventDefault();
			var type = 'zakaz_s_voditelem';
			var car = $('.third-step .car-info-details .name').text();
			var color = $('.third-step .car-info-details .color').text();
			var chosenTarif = {title: '', price: ''};
			if($('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif:visible').length > 0){
				chosenTarif.title = $('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif .tar-name').text();
				chosenTarif.price = $('.third-step .wrap-confirm[data-name=tarif-type] .cur-tarif .price').text();
			}
			var carEquip = [];
			if($('.third-step .tariffs[data-name=equipment] .tarif-item:visible').length > 0){
				$('.third-step .tariffs[data-name=equipment] .tarif-item:visible').each(function(){
					carEquip.push({
						title: $(this).children('p').text(),
						price: $('.tariffs[data-name=tarif-type] .el-tariffs').attr('data-price')
					});
				});
			}
			var endPrice = $('.third-step .end-price b').text();
			var token = $('input[name=_token]').val();

			var city = $('#driverOrder select[name=city] option:selected').text();
			var startPlace = $('#driverOrder input[name=start_place]').val();
			var finishPlace = $('#driverOrder input[name=finish_place]').val();
			var payment = $('#driverOrder select[name=oplata] option:selected').text();
			var userName = $('#driverOrder input[name=name]').val();
			var userTel = $('#driverOrder input[name=phone]').val();
			var userMail = $('#driverOrder input[name=email]').val();

			$.ajax({
				url:	'/send_letter',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				data:	{
					type:type,
					city:city, start_place:startPlace, finish_place:finishPlace,
					date_start:dateStart.toLocaleString(), date_finish:dateFinish.toLocaleString(), payment:payment,
					username:userName, usertel:userTel , usermail:userMail, car:car, color:color, chosen_tarif:chosenTarif,
					car_equip:carEquip, end_price:endPrice
				},
				success:function(data){
					if(data == 'success'){
						popNext("#call_success", "call-popup");
					}
				}
			});
		});
	}
}


$(window).load(function(){

});

$(window).resize(function(){
	services();
});
