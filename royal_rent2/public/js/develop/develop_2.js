function slider_nav(selector){
	$(selector).slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		asNavFor: '.slider-for',
		dots: false,
		centerMode: true,
		variableWidth: true,
		focusOnSelect: true,
		responsive: [{
				breakpoint: 700,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
				}
		}]
	});
}

function slider_for(selector){
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		asNavFor: '.slider-nav'
	});
}

function card_nav(selector){
	$(selector).slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		asNavFor: '.big-photos',
		dots: false,
		variableWidth: true,
		centerMode: false,
		focusOnSelect: true,
	});
}

function card_for(selector){
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		asNavFor: '.small-photos'
	});
}

function popup_nav(selector){
	$(selector).slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		asNavFor: '.big-popup',
		dots: false,
		variableWidth: true,
		centerMode: true,
		focusOnSelect: true
	});
}

function popup_for(selector){
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		prevArrow: '.prev',
		nextArrow: '.next',
		fade: true,
		asNavFor: '.small-popup'
	});
}

function reviews(selector){
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		dots: true,
		fade: true,
	});
}

function wide_slider(selector){
	$(selector).slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		infinite: true,
		arrows: true,
		useCSS: true,
		nextArrow: '.prev-wide',
		prevArrow: '.next-wide',
		dots: false,
		speed: 800,
		asNavFor: '.text-slider, .title-slider',
	});
}
function thin_slider(selector) {
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		infinite: true,
		arrows: false,
		dots: false,
		speed: 800,
		draggable: false,
		asNavFor: '.wide-slider, .title-slider'
	});
}
function title_slider(selector) {
	$(selector).slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		dots: false,
		speed: 800,
		infinite: true,
		vertical: true,
		cssEase: 'linear',
		arrows: false,
		draggable: false,
		asNavFor: '.wide-slider, .text-slider'
	});
}

function main_slider(selector) {
	$(selector).slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		dots: false,
		arrows: false,
		asNavFor: '.shadow-hidden-blocks',
		responsive: [{
			breakpoint: 769,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1,
			}
		},{
			breakpoint: 480,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1
			}
		},{
			breakpoint: 415,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	});
}
function part_slider(selector) {
	$(selector).slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		dots: false,
		arrows: false,
		asNavFor: '.main-slider',
		responsive: [{
			breakpoint: 769,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1,
			}
		},{
			breakpoint: 480,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1
			}
		},{
			breakpoint: 415,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	});
}
function cars_slider(selector) {
	$(selector).slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		nextArrow: '.prev',
		prevArrow: '.next',
		responsive: [{
			breakpoint: 1100,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
			}
		},{
			breakpoint: 768,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	});
}
function cars_watched(selector) {
	$(selector).slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		nextArrow: '.prev-watched',
		prevArrow: '.next-watched',
		responsive: [{
			breakpoint: 1100,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
			}
		},{
			breakpoint: 768,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		},{
			breakpoint: 568,
			settings:{
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: false,
				arrows: false
			}
		}]
	});
}

function calcRentCost(fullPrice){
	var firstPay = ($(document).find('#sum-range').find('#amount-two').val().length > 0)
		? parseInt($(document).find('#sum-range').find('#amount-two').val().replace(/\s+/g, ''))
		: 0;
	var rentTerm = ($(document).find('#date-ranges').find('#amount').val().length > 0)
		? parseInt($(document).find('#date-ranges').find('#amount').val())
		: 3;

	switch(true){
		case rentTerm <= 12:				var z = 1.5;break;
		case rentTerm > 12 && rentTerm <=24:var z = 2;	break;
		case rentTerm > 24 && rentTerm <=30:var z = 2.5;break;
		case rentTerm > 30:					var z = 3;	break;
	}

	return Math.round((fullPrice - firstPay) * (z/rentTerm));
}

$(document).ready(function(){

	// if($(window).width() < 581 ) {
	// 	$('.all-cars').slick({
	// 		slidesToShow: 1,
	// 		slidesToScroll: 1,
	// 		dots: false,
	// 		arrows: false,
	// 	});
	// }
	// $('.small-photos').on("init", function(event, slick){
	// 	setTimeout(function(){$('.small-photos').find(".slick-current").css('width', "0px");},2000);
	// })
	// $('.small-photos').on('beforeChange', function(event, slick, currentSlide, nextSlide){
	// 	// $(".small-photos .slick-slide[data-slick-index='"+currentSlide+"']").css("display", "block");
	// 	// $(".small-photos .slick-slide[data-slick-index='"+nextSlide+"']").css('display', 'none');

	// 	console.log($(".small-photos .slick-slide[data-slick-index='"+currentSlide+"']"));
	// 	console.log($(".small-photos .slick-slide[data-slick-index='"+nextSlide+"']"));
	// })

	$('.all-cars .car-item').each(function() {
		var href = $(this).find('.el-button').attr('href');
		$(this).find('.photo img').wrap('<a href="'+href+'"></a>');
	});

	// $('.small-photos').on('afterChange', function(event, slick, currentSlide){
	// 	var curSlideWidth = $(".small-photos .slick-slide").width();
	// 	$(".small-photos .slick-slide").css("width", curSlideWidth);
	// 	$(".small-photos .slick-slide[data-slick-index='"+currentSlide+"']").css("width", "0");
	// });
	slider_for('.slider-for');
	slider_nav('.slider-nav');
	card_nav('.small-photos');
	card_for('.big-photos');
	/*popup_nav('.small-popup');
	popup_for('.big-popup');*/
	wide_slider('.wide-slider');
	thin_slider('.text-slider');
	title_slider('.title-slider');
	main_slider('.main-slider');
	part_slider('.shadow-hidden-blocks');
	cars_slider('.all-cars-slider');
	cars_watched('.all-cars-watched');

	$('.wrap-work #auto').change(function(){
		var mark = $(this).val();
		$.ajax({
			url:  '/get_models_by_mark',
			type: 'GET',
			data: {mark:mark},
			success: function(data){
				data = JSON.parse(data);
				$('.wrap-work #auto-type option:not(:first-child)').remove();
				for(var i in data){
					$('.wrap-work #auto-type').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
				}
				$('#auto-type').trigger('refresh')
			}
		})
	});

	$('.wrap-work #auto-type').change(function(){
		var mark = $('.wrap-work #auto').val();
		var model = $(this).val();
		$.ajax({
			url:	'/get_car_by_model',
			type:	'GET',
			data:	{mark:mark, model:model, driver:1},
			success: function(data){
				data = JSON.parse(data);
				var maxPrice = 0;
				for(var i in data){
					if(data[i]['price'] > maxPrice){
						maxPrice = data[i]['price'];
					}
				}
				var formatter = new Intl.NumberFormat('ru-RU', {
					minimumFractionDigits: 0,
				});
				$('.about-car .wrap-prices .day .price').text(formatter.format(maxPrice));
				$('.about-car .wrap-prices .month .price').text(formatter.format(maxPrice*30));
				$('.about-car .wrap-prices .year .price').text(formatter.format(maxPrice*365));
				$('.wrap-work .wrap-car .preview-photo').attr('src',data[0]['img_url'][0]['img']);
				$('.wrap-work .wrap-car').show();
			}
		})
	});

	$('#auto').styler();
	$('#auto-type').styler();
	$('.choose').styler();
	$('.age').styler();
	$('.staz').styler();
	$('.oplata').styler();
	$('.driver').styler();
	$('.marka').styler();

	// sorting tabs
	$('.tabs-wrap .el-tabs .tab').click(function(e){
		e.preventDefault();
		var index = $(this).index();
		var text = $(this).text();
		var app = $(this).closest('.tabs-wrap').find('.tab-content .filter-tab-item').eq(index);
		$(this).closest('.tabs-wrap').find('.tab-content .filter-tab-item:not(.all)').empty();
		$(this).closest('.tabs-wrap').find('.tab-content .filter-tab-item .excursion-item').each(function(){
			var category = $(this).find('.exc-text .category').text();
			if (category.indexOf(text) >= 0) {
				$(this).clone().appendTo(app);
			}
		});
	});
	// /sorting tabs

	$('.index-video').css('height', $('video').height());

	// main-slider
	var sliderHeight = $(window).height()-$('.header').height();
	if ($(window).height() > $(window).width()) {
		$('.wrapper-acordeon .main-slider').css('height', sliderHeight);
	} else if ($('.wrapper-acordeon').height() > sliderHeight) {
		$('.wrapper-acordeon').css('height', sliderHeight);
	}

	$('.shadow-item').on('mouseover', function() {
		var slideIndex = $(this).attr('data-attr');
		$(this).closest('.wrapper-acordeon').find('.huge-hidden-blocks .hidden-item[data-attr="'+slideIndex+'"]').addClass('active');
	});
	$('.shadow-item').on('mouseout', function() {
		var slideIndex = $(this).attr('data-attr');
		$(this).closest('.wrapper-acordeon').find('.huge-hidden-blocks .hidden-item[data-attr="'+slideIndex+'"]').removeClass('active');
	});
	// /main slider


	//filter
	if ($('.content-wrap').length) {
		if ($(window).width() > 1280) {
	// 		var filter_top = $('.content-wrap').offset().top;
	// 		$(window).scroll(function(){
	// 			var scrollheight = $(window).scrollTop()+$(window).height();
	// 			var fottop = $('footer').offset().top + $('.el-services').height() + $('.foot-breadcrumbs').height();
	// 			var fil_btm = $(window).scrollTop()+$('.left-bar').height();
	// 			if ($(window).scrollTop() >= filter_top) {
	// 				if (fil_btm >= fottop-100) {
	// 					$('.content-wrap .left-bar').removeClass('fixed').css('top', 'auto');
	// 				} else {
	// 					$('.content-wrap .left-bar').addClass('fixed').css('top', $(window).scrollTop());
	// 					$('.all-cars').css('margin-right', '0');
	// 				}
	// 			} else if ($(window).scrollTop() < filter_top) {
	// 				$('.content-wrap .left-bar').removeClass('fixed').css('top', 'auto');
	// 			}
	// 		});
		} else {
			$('.left-bar').addClass('nonfilter');
		}
		// find('.sort-bar .options .filter')
		$(document).on('click', function(e){
			var eventTarget = $(e.target);
			if(eventTarget.closest('.filter').length){
				e.preventDefault();
				$('.content-wrap .left-bar').toggleClass('nonfilter');
				$('.wrap-slider').slick('unslick');
				reviews('.wrap-slider');
				var sliderTarget = $(this).attr('data-slider');
				if ($('.content-wrap .left-bar').hasClass('nonfilter')) {
					$('.wrap-slider').slick('setPosition');
				}
			}
		});
	}
	// /filter

	// type of transport dropdown

	$(document).find('.wrap-sort').on('click' ,'.arrows', function(){
		$(this).closest('.sort-item').find('.dropdown-transport ul').slideToggle(0);
		return false;
	});
	$(document).find('.wrap-sort').on('click', '.dropdown-transport .main-link', function(e){
		e.preventDefault();
		$(this).closest('.sort-item').find('.dropdown-transport ul').slideToggle(0);
		return false;
	});

	$(document).find('.wrap-sort').on('click', 'a.select-transport-category', function(e){
		e.preventDefault();
		$(this).closest('ul').find('li').css('opacity', '1');
		$(".select-transport-category").css("opacity", '1');
		$(this).css('opacity', '0.3');
		var pic = $(this).find('img').clone();
		$(this).closest('.dropdown-transport').find('li .main-link').html(pic);
		/*Add data-attr to parent node*/
		$(this).closest('.dropdown-transport').find('li .main-link').attr('data-refer', $(this).attr('data-refer'));
		$(this).closest('.dropdown-transport').find('li .main-link').attr('data-type', $(this).attr('data-type'));

		var text = $(this).find('p').text();
		$(this).closest('.sort-item').find('p span').text(text);
		$(this).closest('ul').slideUp('fast');
		return false;
	});

	// /type of transport dropdown

	// scroll on click
	$('.mouse').click(function(e) {
		e.preventDefault();
		var top = $(this).closest('section').offset().top;
		var height = $(this).closest('section').outerHeight();
		var scroll = top + height;
		$('body, html').animate({scrollTop: scroll}, 1500);
	});
	// /scroll on click

	// calc tabs
	$('.content').not(':first').hide();
	$('.el-calc-tabs .tabs li').on('click', function(e){
		e.preventDefault();
		if($('.custom-info select[name=marka]').val() != ''){
			$(this).closest('.tabs').find('li').removeClass('active');
			$(this).addClass('active');

			var thisIndex = $(this).index();
			$(this).closest('.el-calc-tabs').find('.tab-content .content').hide();
			$(this).closest('.el-calc-tabs').find('.tab-content .content').eq(thisIndex).fadeIn();
		}
	});

	$(document).find('.el-calc-tabs').on('click', '.car-item', function(){
		$(this).closest('.car-model').find('.car-item').removeClass('current-model');
		$(this).addClass('current-model');
	});

	$('.el-calc-tabs .mark').on('change', function(){
		$(this).closest('.tab-content').prev('.tabs').find('.active').removeClass('active');
		var content_index = $(this).closest('.content').index()+1;
		$(this).closest('.wrap-tabs').find('.tabs li').eq(content_index).addClass('active');
		$(this).closest('.content').hide().next('.content').fadeIn();
	});
	$(document).on('click', '.car-item', function(){
		$(this).closest('.tab-content').prev('.tabs').find('.active').removeClass('active');
		var content_index = $(this).closest('.content').index()+1;
		$(this).closest('.wrap-tabs').find('.tabs li').eq(content_index).addClass('active');
		$(this).closest('.content').hide().next('.content').fadeIn();

		var mark = $('.el-calc-tabs .marka select[name=marka]').val();
		var model = $(this).find('.photo').attr('data-model');

		if($(this).closest('.content').hasClass('select-model')){
			//Клик по "Выбор модели"
			$.ajax({
				url:	'/get_car_by_model',
				type:	'GET',
				data:	{mark:mark, model:model},
				success:function(data){
					try{
						data = JSON.parse(data);
						$('.el-calc-tabs .tab-content .content:eq(2)').empty();
						for(var i in data){
							var image = (data[i]['img_url'][0]['img'].length > 0)? '<img src="'+data[i]['img_url'][0]['img']+'" alt="">': '';
							$('.el-calc-tabs .tab-content .content:eq(2)').append('<div class="car-item" data-pos="'+data[i]['id']+'">'+
								'<div class="photo">'+image+'</div>'+
								'<p>'+data[i]['title']+' '+data[i]['transmission']+'</p>' +
							'</div>');
						}
					}catch(e){
					}
				}
			});
		}
		if($(this).closest('.content').hasClass('select-modification')){
			var id = $(this).attr('data-pos');
			$.ajax({
				url:	'/get_car_by_id',
				type:	'GET',
				data:	{id:id},
				success:function(data){
					try{
						data = JSON.parse(data);
						switch(true){
							case data['cmp_full_price'] <= 700000: var depositScent = 0.03; break;
							case data['cmp_full_price'] >700000 && data['cmp_full_price'] <= 1000000: var depositScent = 0.07; break;
							case data['cmp_full_price'] > 1000000: var depositScent = 0.1; break;
						}
						var preprice = new Intl.NumberFormat('ru-RU').format(Math.round(data['cmp_full_price'] * depositScent));
						console.log(preprice);

						var step = Math.round(data['cmp_full_price']/100);
						$(document).find('.wrap-tabs').find('.about-car').empty().append('<img src="'+data['img_url'][0]['img']+'" alt="'+data['img_url'][0]['alt']+'">' +
						'<div class="model">'+
							'<p>'+data['title']+' '+data['transmission']+'</p>'+
						'</div>');
						$(document).find('.wrap-tabs').find('.about-car').attr('data-car', data['id']);
						$(document).find('.wrap-tabs').find('.functional').find('.func-title span').text(data.full_price);
						$(document).find('.wrap-tabs').find('.functional').find('#slider-vznos').next('.min-max').find('.max').text(data.full_price);
						$(document).find('.wrap-tabs').find('.functional').find('.preprice span').text(preprice);
						$(document).find('.wrap-tabs').find('.information .main-price .price span').text(calcRentCost(data['cmp_full_price']));

						// range calculator
						$(document).find( "#slider" ).slider({
							value:3,
							min: 3,
							max: 36,
							step: 1,
							slide: function( event, ui ) {
								$( "#amount" ).val(ui.value );
							}
						}).draggable();
						$("#amount").val($( "#slider" ).slider("value"));

						$(document).find('#slider').append('<div class="line"></div>');
						$(document).find('#slider').find('span').css('left', '0px');
						$(document).find('#slider').prev('p').css('left', '0px');
						$(document).find('#slider').find('.line').css('width','0px');

						$(document).find('#slider').on('slide', function(){
							var left = $(this).find('#custom-handle').position().left;
							var newleft = left-$(this).prev('p').outerWidth()/2;

							if (left<=45) {
								$(this).prev('p').css('left', '0px');
							} else if (left>45 && left<345) {
								$(this).prev('p').css('left', newleft);
							} else if (left>=345) {
								$(this).prev('p').css('left', '320px');
							}

							$(this).find('.line').css('width', left);

						});

						$(document).find('#slider').on('slidechange', function(){
							var value = $(this).prev('p').find('input').val();

							if (value == '1') {
								$(this).prev('p').find('label').text('месяц');
							} else if (value == '2' || value == '3' || value == '4') {
								$(this).prev('p').find('label').text('месяцa');
							} else {
								$(this).prev('p').find('label').text('месяцев');
							}
							$(document).find('.wrap-tabs').find('.information .price span').text(calcRentCost(data['cmp_full_price']));
						});

						$(document).find("#slider-vznos").slider({
							value: 0,
							min: 0,
							max: data['cmp_full_price'],
							step: step,
							slide: function( event, ui ) {
								$("#amount-two").val(new Intl.NumberFormat('ru-RU').format(ui.value));
							}
						}).draggable();
						$(document).find( "#amount-two" ).val($( "#slider-vznos" ).slider( "value" ));

						$(document).find('#slider-vznos').append('<div class="line"></div>');
						$(document).find('#slider-vznos').find('span').css('left', '0px');
						$(document).find('#slider-vznos').prev('p').css('left', '0px');
						$(document).find('#slider-vznos').find('.line').css('width','0px');

						$(document).find('#slider-vznos').on('slide', function(){
							var left = $(this).find('#custom-handle1').position().left;

							if (left<=15) {
								$(this).prev('p').css('left', '0px');
							} else if (left>15 && left<400) {
								$(this).prev('p').css('left', left);
							} else if (left>=350) {
								$(this).prev('p').css('left', '350px');
							}

							$(this).find('.line').css('width', left);
						});

						$(document).find('#slider-vznos').on('slidechange', function(){
							$(document).find('.wrap-tabs').find('.information .price span').text(calcRentCost(data['cmp_full_price']));
						});
						// /range calculator
					}catch(e){}
				}
			});
		}
	});
	$('.leave-btn').click(function(e){
		e.preventDefault();
		$(this).closest('.tab-content').prev('.tabs').find('.active').removeClass('active');
		var content_index = $(this).closest('.content').index()+1;
		$(this).closest('.wrap-tabs').find('.tabs li').eq(content_index).addClass('active');
		$(this).closest('.content').hide().next('.content').fadeIn();
		$('#hirePurchaseForm input[name=car]').val($('.view-car .model p').text());
		$('#hirePurchaseForm input[name=price]').val($('.view-car .func-title span').text()+' руб.');
		$('#hirePurchaseForm input[name=rent_time]').val($('.view-car #date-ranges #amount').val());
		$('#hirePurchaseForm input[name=first_pay]').val($('.view-car #sum-range #amount-two').val());
		$('#hirePurchaseForm input[name=deposite_pay]').val($('.view-car .preprice span').text()+' руб.');
		$('#hirePurchaseForm input[name=pay_per_month]').val($('.view-car .price span').text()+' руб.');
	});
	// /calc tabs

	// cost table
	$('.el-table .link').on('click', function(){
		$(this).closest('.el-table').find('.wrap-table').slideToggle();
		$(this).toggleClass('active');
	});
	// /cost table

	$('.js-tariff').click(function(){
		$(this).closest('.el-tariffs').find('.item').removeClass('chosen');
		$(this).find('.item').addClass('chosen');
		if ($(this).hasClass('transfer')) {
			$(this).empty();
			$(this).append('<a class="el-button fancybox-form" href="#call-popup">Заказать звонок</a>')
		}
	});

	$('.js-add-env').on('click', function(){
		$(this).find('.item').toggleClass('chosen');
	});

	$('.wrap-conditions .wrap-tabs .btn').on('click', function(){
		$('.wrap-tabs .btn').removeClass('active');
		$('.wrap-conditions .wrap-content .content').css('display', 'none');
		$(this).addClass('active');
		var elId = $(this).index();
		var elData = $('.wrap-conditions .wrap-content .content').eq(elId).css('display', 'block');
	});

	$('.wrap-tariffs .tariff-item .link:first').addClass('arrow');
	$('.wrap-tariffs .tariff-item .link').on('click', function(){
		// $(this).next('.unvisible').toggleClass('visible');
		$(this).toggleClass('arrow');
		$(this).next('.unvisible').slideToggle();
	});

	//Car page -> Case color
	$('.colors .choose-color span').click(function(){
		$(this).closest('.colors').find('.choose-color').removeClass('active');
		$(this).parent('.choose-color').addClass('active');

		if( ($(this).closest('.choose-color').attr('data-refer') != undefined) && ($(this).closest('.choose-color').hasClass('active')) ){
			var car = $(this).closest('.choose-color').attr('data-refer');
			var path = window.location.pathname.split('/');

			$.ajax({
				url:	'/get_car_by_id',
				type:	'GET',
				beforeSend:function(){
					$('.wrap-slider-card .slider-card .big-photos').slick('unslick');
					$('.wrap-slider-card .slider-card .small-photos').slick('unslick');
					$('.wrap-slider-card .slider-card .big-photos, .wrap-slider-card .slider-card .small-photos').empty();
					$('.wrap-slider-card .slider-card .big-photos').append('<img src="/img/fancybox_loading.gif" alt="loading">');
				},
				data:	{id:car, slug:path[2]},
				success:function(data){
					try{
						data = JSON.parse(data);
						$('.wrap-slider-card .slider-card .big-photos').empty();
						for(var i=1; i<data['img_url'].length; i++){
							$('.wrap-slider-card .slider-card .big-photos').append('<div class="photo"><img src="'+data['img_url'][i]['img']+'" alt="'+data['img_url'][i]['alt']+'"></div>');
							$('.wrap-slider-card .slider-card .small-photos').append('<div class="sm-photo"><img src="'+data['img_url'][i]['img']+'" alt="'+data['img_url'][i]['alt']+'"></div>');
						}

						card_nav('.small-photos');
						card_for('.big-photos');

						if(data['price'] > 0){
							$('.right-card span[data-name=price]').parent().show();
						}else{
							$('.right-card span[data-name=price]').parent().hide();
						}
						$('.right-card span[data-name=price]').text(data['price']);

						$('.right-card .color-content .content').html("Цвет &mdash; "+data['current_color'][0]['title']);
						$('.right-card .advantages p[data-name=seat]').text('до '+data['seat_quant']);
						$('.right-card .advantages p[data-name=fuel-system]').text(data['fuel_system']);
						$('.right-card .advantages p[data-name=fuel-consume]').text(data['fuel_consume']+'л/100км');
						$('.right-card .advantages p[data-name=engine-power]').text(data['engine_power']+' л.с.');
						$('.right-card .advantages p[data-name=transmission]').text(data['transmission']);

						$('#video-popup').empty();
						if(data.video.length > 0){
							$('#video-popup').append(data.video);
							$('.right-card a.video').show();
						}else{
							$('.right-card a.video').hide();
						}

						$('.right-card .functions .options-hidden').empty();
						if(data.options.length > 0){
							for(var i in data['options']){
								var tag =
								'<div class="item">'+
									'<div class="name">'+data['options'][i]['title']+'</div>'+
									'<div class="price">';
								if(data['options'][i]['data']['string_0']['value'] != ''){
									tag += data['options'][i]['data']['string_0']['value']+' р в сутки';
								}else{
									tag += data['options'][i]['data']['string_1']['value'];
								}
								tag += '</div>' +
									'<div class="pic"><img src="'+data['options'][i]['img_url'][0]['img']+'" alt="'+data['options'][i]['img_url'][0]['alt']+'"></div>'+
								'</div>';
								$('.right-card .functions .options-hidden').append(tag);
							}
							$('.right-card .functions').show();
						}else{
							$('.right-card .functions').hide();
						}

						$('.wrapper-details .equipment-hidden').html(data['text']);

						$('.wrapper-details .available-tariffs').empty();
						if(data.rents.length > 0){
							for(var i in data['rents']){
								var tag = '' +
								'<div class="tar-item">' +
									'<div class="title">'+data['rents'][i]['title']+'</div>' +
									'<div class="price">';
									if(data['rents'][i]['data']['string_0']['value'] != ''){
										if(data['rents'][i]['data']['string_0']['value'] == '[%price%]'){
                                            tag += '<p>от <b>'+data['price']+'</b> <i>₽</i></p><span><strong>/</strong>в час</span>';
										}else{
                                            tag += '<p>от <b>'+data['rents'][i]['data']['string_0']['value']+'</b> <i>₽</i></p><span><strong>/</strong>в час</span>';
										}
									}else{
										tag += '<p><i></i><b>'+data['rents'][i]['data']['string_1']['value']+'</b></p>';
									}
									tag += '</div>';
									data['rents'][i]['text'] = data['rents'][i]['text'].split("\n\r");
									for(var j in data['rents'][i]['text']){
										var text = data['rents'][i]['text'][j].trim();
										if(text.length > 0){
											tag += '<div class="desc">'+text+'</div>';
										}
									}
									tag += '<div class="prompt-hidden">'+data['rents'][i]['data']['fulltext_0']['value']+'</div>' +
								'</div>';
								$('.wrapper-details .available-tariffs').append(tag);
							}
						}
					}catch(e){}
				}
			})
		}
	});

	$('.wrapper-details .details').click(function(e) {
		e.preventDefault();
		$(this).toggleClass('open');
		$(this).next('.equipment-hidden').slideToggle();
	});

	$('#all-filds').on('change', function(){
		$(this).closest('.contacts').find('.full-info').slideToggle().css('display', 'flex');
		if($(this).prop('checked') == true){
			$(this).closest('.contacts').find('.need-req').attr('required','required');
		}else{
			$(this).closest('.contacts').find('.need-req').removeAttr('required');
		}
	});

	if($('.about-rent').length ){
		var top_rent = $('.about-rent').offset().top-300;
		$(window).scroll(function(){
			// idex image
			if ($(window).scrollTop() >= top_rent) {
				$('.about-rent .photo .photo-item').addClass('active');
			} else if ($(window).scrollTop() < top_rent) {
				$('.about-rent .photo .photo-item').removeClass('active');
			}
			// /idex image
		});
	};

	$('.el-calc-tabs .marka select[name=marka]').change(function(){
		var mark = $(this).val();
		$.ajax({
			url:	'/get_models_by_mark',
			type:	'GET',
			data:	{mark:mark},
			success:function(data){
				try{
					data = JSON.parse(data);
					$('.el-calc-tabs .tab-content .content:eq(1)').empty();
					for(var i in data){
						var image = (data[i]['img_url'].length>0)? '<img src="'+data[i]['img_url']+'" alt="">': '';
						$('.el-calc-tabs .tab-content .content:eq(1)').append('<div class="car-item">'+
							'<div class="photo" data-model="'+data[i]['slug']+'">'+image+'</div>'+
							'<p>'+data[i]['title']+'</p>'+
						'</div>');
					}
				}catch(e){}
			}
		})
	});

	//Reviews show_more
	$('.left-bar .wrap-slider .slide-item .text').each(function(){
		if(150 > $(this).outerHeight()){
			$(this).css({'padding': '15px'});
			$(this).closest('.slide-item').find('.show-more').hide();
		}
	});
	$('.left-bar .wrap-slider .slide-item .show-more').on('click',function(e){
		e.preventDefault();
		$(this).closest('.slide-item').find('.text').toggleClass('active');
		if($(this).closest('.slide-item').find('.text').hasClass('active')){
			$(this).find('a').text('Скрыть');
		}else{
			$(this).find('a').text('Показать');
		}
	});

	$('.order-info input[name=order_excursion]').click(function(){
		var tarif = $('.el-tariffs .chosen').closest('.tarif-item').attr('data-type');
		if(typeof tarif != 'undefined'){
			var name = $('.order-info input[name=name]').val();
			var tel = $('.order-info input[name=tel]').val();
			var date = $('.order-info input[name=date]').val();
			var path = window.location.pathname.split('/');
			var type = path[path.length -2];
			path = path[path.length -1];
			var token = $('.order-info input[name=_token]').val();
			var letter = (type == 'order_excursion')? 'zakaz_ekskursii': 'zakaz_romanticheskoj_vstrechi';
			$.ajax({
				url:	'/send_letter',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				data:	{name:name, tel:tel, date:date, type:letter, tarif:tarif, path:path},
				success:function(data){
					if(data == 'success'){
						popNext("#call_success", "call-popup")
					}
				}
			})
		}
	});

	$('#corp-contract input.el-button').click(function(){
		var services = '';
		$('#corp-contract input[name=sevices]').each(function(){
			if($(this).prop('checked') == true){
				services += $(this).val()+'; ';
			}
		});
		$.ajax({
			url:	'/send_letter',
			type:	'POST',
			headers:{'X-CSRF-TOKEN': $('input[name=_token]').val()},
			data:	{
				company:$('#corp-contract input[name=company]').val().trim(),
				contact:$('#corp-contract input[name=contact]').val().trim(),
				phone: $('#corp-contract input[name=phone]').val().trim(),
				contact_mail:$('#corp-contract input[name=contact_mail]').val().trim(),
				services: services,
				orders_volume: $('#corp-contract input[name=orders_volume]').val().trim(),
				etc_data: $('#corp-contract textarea[name=etc_data]').val().trim(),
				type: 'zapros_korporativnogo_dogovora'
			},
			success:function(data){
				if(data == 'success'){
					popNext("#call_success", "call-popup")
				}
			}
		});
	});

	$('.wrap-slider-card .orenda').click(function(e){
		e.preventDefault();
		var path = window.location.pathname.split('/');
		path = path[path.length -2];
		var car = $('.wrap-color .active').attr('data-refer');
		location = '/car/order/'+path+'/'+car;
	});
});

jQuery(document).click( function(event){
	if( $(event.target).closest(".dropdown-sorting ul").length )
	return;
	jQuery(".dropdown-sorting ul").slideUp('fast');
	event.stopPropagation();
});

jQuery(document).click( function(event){
	if( $(event.target).closest(".dropdown-transport ul").length )
	return;
	jQuery(".dropdown-transport ul").slideUp('fast');
	event.stopPropagation();
});

$(window).load(function(){
	reviews('.wrap-slider');
	$('.wrap-slider').slick('setPosition');
	setInterval(function(){
		$('.mouse .dot').toggleClass('invisible');
	}, 1500);
});


$(window).resize(function(){

});
