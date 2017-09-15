//call fancybox from headdings links
function fancyboxHeading(){
	$('.fancyboxHeading').fancybox({
		width: 1166,
		openEffect  : 'fade',
		closeEffect : 'fade',
		autoResize:true,
		wrapCSS:'fancyboxHeading-popup',
		'closeBtn' : true,
		fitToView:true,
		padding:'0'
	})
}
function fancyboxHeading1(){
	$('.fancyboxHeading1').fancybox({
		width: 1166,
		openEffect: 'fade',
		closeEffect: 'fade',
		autoResize:true,
		wrapCSS:'fancyboxHeading-popup1',
		'closeBtn' : true,
		fitToView:true,
		padding:'0',
		helpers: {
			overlay: {
				css: {
				}
			}
		},
		onUpdate: function(){
			if($(window).width()>992){
				$(".fancyboxHeading-popup1").parent().css({
					"background": " url('/img/login-menu.png') no-repeat",
					"background-size": "cover",
					"margin-top": "95px"
				});
			}else {
				$(".fancyboxHeading-popup1").parent().css({
					"background": " url('/img/login-menu.png') no-repeat",
					"background-size": "cover",
					"margin-top": "0px"
				});
			}
		}
	});

	$(document).on("click",function(event){
		var clickEl = $(event.target);
		if(!$(clickEl).closest(".fancyboxHeading-popup1").hasClass("fancyboxHeading-popup1") && !$(clickEl).closest(".fancyboxHeading-popup").hasClass("fancyboxHeading-popup") && !$(clickEl).closest(".ui-timepicker-wrapper").hasClass("ui-timepicker-wrapper")){
			$(".fancyboxHeading-popup1 .fancybox-close").trigger("click");
			$(".fancyboxHeading-popup .fancybox-close").trigger("click");
		}
	});

	$(".open_our-transport").on("click", function(event){
		$(".fancybox-close").trigger("click");
	})
}
//

//some action control for cityPicker fancybox
	function cityPickerToggle(){
		$("#city_picker .city-select").click(function(event){
			event.stopPropagation();
			$("#city_picker .city-search").attr("disabled","disabled").css("pointers-event","none");
		});
		$("#city_picker .city-search").click(function(event){
			event.stopPropagation();
			$("#city_picker .city-select").attr("disabled","disabled").css("pointers-event","none");
		});
		$("#city_picker .standartYellow").on("click", function(event){
			event.stopPropagation();
		})
		$("#city_picker").click(function(){
			$("#city_picker .city-select").removeAttr("disabled");
			$("#city_picker .city-search").removeAttr("disabled");
		});
	}
//

function validationCall1(form){
	var thisForm = $(form);
	var formSur = thisForm.serialize();
	$.ajax({
		url:	thisForm.attr('action'),
		type	:'POST',
		headers:{'X-CSRF-TOKEN': thisForm.find('input[name=_token]').val()},
		data:	formSur,
		success: function(data){
			if(data == 'city_changed'){
				location.reload(true);
			}
		}
	});
}

// toggle tabs in calculator fancybox from heading nav menu
function driver_nondriverToggle(){
	$("#headerCalculator .pages-line").eq(1).slideUp();
	$("#headerCalculator .tabs-line .tab").on("click", function(){
		$("#headerCalculator .tabs-line .tab").removeClass("chosen");
		$(this).addClass("chosen");
		var tabIndex = $(this).index();
		$("#headerCalculator .pages-line").slideUp();
		$("#headerCalculator .pages-line").eq(tabIndex).slideDown();
	})
}
//

//functions for date-picker
function datepickerInit(){
	if($(".datepicker-init").length){
		$(".datepicker-init").datepicker({
			minDate: new Date(), // Now can select only dates, which goes after today
			firstDay: 1,
			showOtherMonths: true,
			selectOtherMonths: true,
		})
	}
}
function dropdownDatepicker(){
	$(".input-field").on("click", function(event){
		event.stopPropagation();
		$(this).next(".dropdown-datepicker").toggle();
	})
	$(document).on("click", function(event){
		if(!$(event.target).hasClass("ui-timepicker-am") && !$(event.target).hasClass("ui-timepicker-am")){
			$(".dropdown-datepicker").hide();
		}
	})

	$(".dropdown-datepicker").on("click", function(event){
		event.stopPropagation();
	})
}
function datepickerTab(formName){
	$(""+formName+" .start-rent+.dropdown-datepicker .main-date-content .main-date-tab").eq(1).fadeOut();
	$(""+formName+" .end-rent+.dropdown-datepicker .main-date-content .main-date-tab").eq(1).fadeOut();

	$(""+formName+" .start-rent+.dropdown-datepicker .head-tab").on("click", function(){
		$(formName+" .start-rent+.dropdown-datepicker .head-tab").removeClass("clicked");
		$(this).addClass("clicked");
		var tabIndex = $(this).index();
		$(""+formName+" .start-rent+.dropdown-datepicker .main-date-content .main-date-tab").fadeOut();
		$(""+formName+" .start-rent+.dropdown-datepicker .main-date-content .main-date-tab").eq(tabIndex).fadeIn();
	})

	$(""+formName+" .end-rent+.dropdown-datepicker .head-tab").on("click", function(){
		$(formName+" .end-rent+.dropdown-datepicker .head-tab").removeClass("clicked");
		$(this).addClass("clicked");
		var tabIndex = $(this).index();
		$(""+formName+" .end-rent+.dropdown-datepicker .main-date-content .main-date-tab").fadeOut();
		$(""+formName+" .end-rent+.dropdown-datepicker .main-date-content .main-date-tab").eq(tabIndex).fadeIn();
	})
}
var startRentGlobal=null,
	endRentGlobal=null,
	curdateStart=null,
	curdateEnd=null,
	curStartTime=null,
	curEndTime=null;

function dataRangeStart(startRentInner,endRentIner){
	var date = startRentInner.datepicker("getDate");
	curdateStart = date;
	startRentGlobal = startRentInner.datepicker("getDate").format("dd/mm/yy");
	endRentIner.datepicker("option", "minDate", date);
	$(".start-rent .left-part p").html(""+date.format("dd/mm/yy"));
	$(startRentInner).closest(".page-line-item").find(".input-field").addClass("updated");
	$(startRentInner).closest(".page-line-item").find(".input-field").removeClass("error");
	$(startRentInner).next("input").attr("value",""+date.format("dd/mm/yy"));
	$(startRentInner).closest(".page-line-item").find(".error-message-hour").css("display","none");
	$(startRentInner).closest(".page-line-item").find(".error-message-minutes").css("display","none");

	timePickerRange($('#nonedriver-hourpicker-start'),$("#nonedriver-minutepicker-start"),$('#nonedriver-hourpicker-end'),$("#nonedriver-minutepicker-end"));
	timePickerRange($('#driver-hourpicker-start'),$("#driver-minutespicker-start"),$('#driver-hourpicker-end'),$("#driver-minutespicker-end"));
	timePickerRange($('#nonedriver-hourpicker-start-order'),$("#nonedriver-minutepicker-start-order"),$('#nonedriver-hourpicker-end-order'),$("#nonedriver-minutepicker-end-order"));

	startRentGlobal = startRentInner.datepicker("getDate").format("dd/mm/yy");
	curStartTime = '';
	$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
	curEndTime = '';
	$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
	window.topCalculator.startTime = startRentGlobal+curStartTime;
	window.topCalculator.endTime = endRentGlobal+curEndTime;
	callCalulatorChanges();
	callCalulatorChangesWithDriver();
}

function dataRangeEnd(endRentIner,startRentInner){
	var date = endRentIner.datepicker("getDate");
	curdateEnd = date;
	endRentGlobal = endRentIner.datepicker("getDate").format("dd/mm/yy");
	startRentInner.datepicker("option", "maxDate", date);
	$(".end-rent .left-part p").html(""+date.format("dd/mm/yy"));
	$(endRentIner).closest(".page-line-item").find(".input-field").addClass("updated");
	$(endRentIner).closest(".page-line-item").find(".input-field").removeClass("error");
	$(endRentIner).next("input").attr("value",""+date.format("dd/mm/yy"));
	$(endRentIner).closest(".page-line-item").find(".error-message-hour").css("display","none");
	$(endRentIner).closest(".page-line-item").find(".error-message-minutes").css("display","none");

	timePickerRange($('#nonedriver-hourpicker-start'),$("#nonedriver-minutepicker-start"),$('#nonedriver-hourpicker-end'),$("#nonedriver-minutepicker-end"));
	timePickerRange($('#driver-hourpicker-start'),$("#driver-minutepicker-start"),$('#driver-hourpicker-end'),$("#driver-minutepicker-end"));
	timePickerRange($('#nonedriver-hourpicker-start-order'),$("#nonedriver-minutepicker-start-order"),$('#nonedriver-hourpicker-end-order'),$("#nonedriver-minutepicker-end-order"));

	endRentGlobal = endRentIner.datepicker("getDate").format("dd/mm/yy");
	curStartTime = '';
	$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
	curEndTime = '';
	$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
	window.topCalculator.startTime = startRentGlobal+curStartTime;
	window.topCalculator.endTime = endRentGlobal+curEndTime;
	callCalulatorChanges();
	callCalulatorChangesWithDriver();
}

function dataRange(startRent,endRent){
	startRent.datepicker().on("change", function(){
		dataRangeStart(startRent,endRent);
	});
	endRent.datepicker().on("change", function(){
		dataRangeEnd(endRent,startRent);
	});
}

var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:		d,
				dd:		pad(d),
				ddd:	dF.i18n.dayNames[D],
				dddd:	dF.i18n.dayNames[D + 7],
				m:		m + 1,
				mm:		pad(m + 1),
				mmm:	dF.i18n.monthNames[m],
				mmmm:	dF.i18n.monthNames[m + 12],
				yy:		String(y).slice(2),
				yyyy:	y,
				h:		H % 12 || 12,
				hh:		pad(H % 12 || 12),
				H:		H,
				HH:		pad(H),
				M:		M,
				MM:		pad(M),
				s:		s,
				ss:		pad(s),
				l:		pad(L, 3),
				L:		pad(L > 99 ? Math.round(L / 10) : L),
				t:		H < 12 ? "a"  : "p",
				tt:		H < 12 ? "am" : "pm",
				T:		H < 12 ? "A"  : "P",
				TT:		H < 12 ? "AM" : "PM",
				Z:		utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:		(o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:		["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":		"ddd mmm dd yyyy HH:MM:ss",
	shortDate:		"m/d/yy",
	mediumDate:		"mmm d, yyyy",
	longDate:		"mmmm d, yyyy",
	fullDate:		"dddd, mmmm d, yyyy",
	shortTime:		"h:MM TT",
	mediumTime:		"h:MM:ss TT",
	longTime:		"h:MM:ss TT Z",
	isoDate:		"yyyy-mm-dd",
	isoTime:		"HH:MM:ss",
	isoDateTime:	"yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime:	"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};
// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};

// timePicker init and range
function timePicker(hours, minutes){
	hours.timepicker({
		"step": 60,
		'timeFormat': "H:i"
	});
	minutes.timepicker({
		"step": 5,
		'timeFormat': "H:i",
		'minTime': '0:00',
		'maxTime': '0:55',
	});
}

var startMinutsGlobal =null,
	endMinutsGlobal =null,
	startHourGlobal=null,
	endHourGlobal=null;

function timePickerRange(startHour,startMinuts,endHour,endMinuts){
	startHourGlobal = startHour.timepicker({"step": 60,
		'timeFormat': "H:i",
		'minTime': '0:00',
		'maxTime': '23:00'}).on("change", function(){

		var hours=startHour.timepicker("getTime");
		if(startRentGlobal==endRentGlobal){
			endHour.timepicker('option',{'minTime': hours,'timeFormat': "H:i"});
		}else{
			endHour.timepicker('option',{'minTime': '0:00','timeFormat': "H:i"});
			startHour.timepicker('option','minTime',  "0:00");
			startHour.timepicker('option','maxTime', '23:00');
		}
		$(".start-rent .right-part p span:nth-child(1)").html(""+hours.format("HH"));
		startMinuts.timepicker('option','minTime',  "0:00");
		startMinuts.timepicker('option','maxTime', '0:55');
		endMinuts.timepicker('option','minTime',  "0:00");
		endMinuts.timepicker('option','maxTime', '0:55');

		curStartTime = '';
		$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
		curEndTime = '';
		$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
		window.topCalculator.startTime = startRentGlobal+curStartTime;
		window.topCalculator.endTime = endRentGlobal+curEndTime;
		callCalulatorChanges();
		callCalulatorChangesWithDriver();
	});

	endHourGlobal = endHour.timepicker({
		"step": 60,
		'timeFormat': "H:i",
		'minTime': '0:00',
		'maxTime': '23:00'}).on("change", function(){

		var hours = endHour.timepicker("getTime");
		if(startRentGlobal==endRentGlobal){
			startHour.timepicker('option',{'maxTime': hours,'timeFormat': "H:i"});
		}else{
			startHour.timepicker('option',{'maxTime': '23:00','timeFormat': "H:i"});
			endHour.timepicker('option','minTime',  "0:00");
			endHour.timepicker('option','maxTime', '23:00');
		}
		$(".end-rent .right-part p span:nth-child(1)").html(""+hours.format("HH"));
		startMinuts.timepicker('option','minTime',  "0:00");
		startMinuts.timepicker('option','maxTime', '0:55');
		endMinuts.timepicker('option','minTime',  "0:00");
		endMinuts.timepicker('option','maxTime', '0:55');

		curStartTime = '';
		$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
		curEndTime = '';
		$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
		window.topCalculator.startTime = startRentGlobal+curStartTime;
		window.topCalculator.endTime = endRentGlobal+curEndTime;
		callCalulatorChanges();
		callCalulatorChangesWithDriver();
	});

	if(startRentGlobal==endRentGlobal){
		var _hoursEnd = endHourGlobal.timepicker("getTime");
		var _hoursStart = startHourGlobal.timepicker("getTime");
		endHour.timepicker('option',{'minTime': _hoursStart,'timeFormat': "H:i"});
		startHour.timepicker('option',{'maxTime': _hoursEnd,'timeFormat': "H:i"});
	}else{
		startHourGlobal.timepicker('option','minTime',  "0:00");
		startHourGlobal.timepicker('option','maxTime', '23:00');
		endHourGlobal.timepicker('option','minTime',  "0:00");
		endHourGlobal.timepicker('option','maxTime', '23:00');
	}

	startMinutsGlobal = startMinuts.timepicker({"step": 5,
		'timeFormat': "H:i",
		'minTime': '0:00',
		'maxTime': '0:55',}).on("change", function(){
		var dataToCheck1 = startRentGlobal;
		var dataToCheck2 = endRentGlobal;
		var minuts = startMinuts.timepicker("getTime");
		if(dataToCheck1==dataToCheck2){
			var hoursToCheck1 = startHour.timepicker("getTime");
			var hoursToCheck2 = endHour.timepicker("getTime");

			if(hoursToCheck1==hoursToCheck1){
				endMinuts.timepicker('option',{'minTime': minuts,'timeFormat': "H:i"});
			}else{
				startMinuts.timepicker('option','minTime',  "0:00");
				startMinuts.timepicker('option','maxTime', '0:55');
			}
		}else{
			startMinuts.timepicker('option','minTime',  "0:00");
			startMinuts.timepicker('option','maxTime', '0:55');
		}

		$(".start-rent .right-part p span:nth-child(2)").html(""+minuts.format("MM"));

		curStartTime = '';
		$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
		curEndTime = '';
		$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
		window.topCalculator.startTime = startRentGlobal+curStartTime;
		window.topCalculator.endTime = endRentGlobal+curEndTime;
		callCalulatorChanges();
		callCalulatorChangesWithDriver();
	});

	endMinutsGlobal = endMinuts.timepicker({
		"step": 5,
		'timeFormat': "H:i",
		'minTime': '0:00',
		'maxTime': '0:55',}).on("change", function(){
		var dataToCheck1 = startRentGlobal;
		var dataToCheck2 = endRentGlobal;
		var minuts = endMinuts.timepicker("getTime");
		if(dataToCheck1==dataToCheck2){
			var hoursToCheck1 = startHour.timepicker("getTime");
			var hoursToCheck2 = endHour.timepicker("getTime");
			if(hoursToCheck1==hoursToCheck1){
				// minuts = endMinuts.timepicker("getTime");
				startMinuts.timepicker('option',{'maxTime': minuts,'timeFormat': "H:i"});
			}else{
				endMinuts.timepicker('option','minTime',  "0:00");
				endMinuts.timepicker('option','maxTime', '0:55');
			}
		}else{
			endMinuts.timepicker('option','minTime',  "0:00");
			endMinuts.timepicker('option','maxTime', '0:55');
		}
		$(".end-rent .right-part p span:nth-child(2)").html(""+minuts.format("MM"));
		curStartTime = '';
		$(".start-rent .right-part p span").each(function(){curStartTime += ':'+$(this).text();});
		curEndTime = '';
		$(".end-rent .right-part p span").each(function(){curEndTime += ':'+$(this).text();});
		window.topCalculator.startTime = startRentGlobal+curStartTime;
		window.topCalculator.endTime = endRentGlobal+curEndTime;
		callCalulatorChanges();
		callCalulatorChangesWithDriver();
	})
}

function datepickerButtonSubmit(datepicker1, datepicker2){
	$(".datepicker-button").on("click", function(event){
		event.preventDefault();
		$(this).closest(".dropdown-datepicker").hide();
		if($(this).closest(".page-line-item").find(".datepicker-init").prop("id")==$(datepicker1).prop("id")){
			curdateStart = $(datepicker1).datepicker("getDate");
			$(".start-rent .left-part p").html(""+curdateStart.format("dd/mm/yy"));
			$(datepicker1).closest(".page-line-item").find(".input-field").addClass("updated");
			// $(datepicker1).closest(".dropdown-datepicker").toggle();
		}else if($(this).closest(".page-line-item").find(".datepicker-init").prop("id")==$(datepicker2).prop("id")){
			curdateEnd = $(datepicker2).datepicker("getDate");
			$(".end-rent .left-part p").html(""+curdateEnd.format("dd/mm/yy"));
			$(datepicker2).closest(".page-line-item").find(".input-field").addClass("updated");
			// $(datepicker2).closest(".dropdown-datepicker").toggle();
		}
	})
}

function calculatorDatePickerValidate(form){
	var curForm = $(form);
	curForm.on('submit', function(e) {
		if(!curForm.find(".start-rent").hasClass("updated")){
			e.preventDefault();
			curForm.find(".start-rent").addClass("error");
			curForm.find(".error-message-hour").css("display","block");
			curForm.find(".error-message-minutes").css("display","block");
		}else if(curForm.find(".start-rent").hasClass("updated")){
			curForm.find(".start-rent").removeClass("error");
			curForm.find(".error-message-hour").css("display","none");
			curForm.find(".error-message-minutes").css("display","none");
		}

		if(!curForm.find(".end-rent").hasClass("updated")){
			e.preventDefault();
			curForm.find(".end-rent").addClass("error");
			curForm.find(".error-message-hour").css("display","block");
			curForm.find(".error-message-minutes").css("display","block");
		}else if(curForm.find(".end-rent").hasClass("updated")){
			curForm.find(".end-rent").removeClass("error");
			curForm.find(".error-message-hour").css("display","none");
			curForm.find(".error-message-minutes").css("display","none");
		}
	});
}

function HeaderCalculatorValidationCall(form){
	var thisForm = $(form);
	var formSur = thisForm.serialize();
	formSur += '&total='+thisForm.find('.total p').text()+'&type='+thisForm.attr('data-type');
	$.ajax({
		url:	thisForm.attr('action'),
		type:	'POST',
		data:	formSur,
		success: function(data){
			if(data['message'] == 'success'){
				thisForm.trigger('reset');
				setTimeout(function(){
					thisForm.find('select').trigger('refresh');
				}, 1);
				$.fancybox.close();
			}
			if(data['message'] == 'not_logged_in'){

			}
		}
	});
}

//togle for sliding nav menu from above
var stucture =null;
var structureAbout = null;
function headerLeftMenuToggle(){
	$(".global-wrapper").wrap("<div class='global-wrapper-wrapper global-wrapper-closed'></div>");
	stuctureService = $(".wrapper-our-servicesjs").html();
	structureAbout = $(".wrapper-about-companyjs").html();
	structurePartners = $(".wrapper-partners_investorsjs").html();
	$(".global-wrapper").on("click",function(event){
		// if($(".sn-outer-wrapper").hasClass("animate")){
		// 	$(".our-transport-nav-tabs").fadeOut();
		// }
		if($(".global-wrapper-wrapper").hasClass("global-wrapper-opend") || $('.sn-outer-wrapper').hasClass('animate')){
			event.stopPropagation();
			$(".header .bottom-part .main-wrap ul li a").removeClass("active");
			$(".nav-mobile-menu .bottom-part ul li a").removeClass("active");

			$(".global-wrapper-wrapper").removeClass("global-wrapper-opend");
			$(".sn-outer-wrapper .our-services-nav.navigated").fadeOut(function(){
				$(".sn-outer-wrapper .our-services-nav.navigated").detach();
				$(".sn-outer-wrapper").css("overflow","visible");
			});
			$(".sn-outer-wrapper .about-company-nav.navigated").fadeOut(function(){
				$(".sn-outer-wrapper .about-company-nav.navigated").detach();
				$(".sn-outer-wrapper").css("overflow","visible");
			});
			$(".sn-outer-wrapper .partners_investors-nav.navigated").fadeOut(function(){
				$(".sn-outer-wrapper .partners_investors-nav.navigated").detach();
				$(".sn-outer-wrapper").css("overflow","visible");
			});
		}
	})

	$(document).on("click", function(event){
		var eventNew = event;
		var navItem = event.target;

		if($(navItem).hasClass("open_our-services") || $(navItem).closest(".open_our-services").length){
			$(".global-wrapper").addClass("opened-nav-menu");
			$(".global-wrapper-wrapper").addClass("global-wrapper-opend");
			$(".sn-outer-wrapper").append(stuctureService);
			$(".our-services-nav").addClass("navigated");
			// $(".our-transport-nav-tabs").fadeOut();
			$(".sn-outer-wrapper").css("overflow-x","hidden");
			if($(window).width()<1601){
				$(".our-services-nav.navigated div .heading+ul").slideUp();
			}else{
				$(".our-services-nav.navigated div .heading+ul").slideDown();
			}
		}else if($(navItem).hasClass("open_about-company")){
			$(".global-wrapper").addClass("opened-nav-menu");
			$(".global-wrapper-wrapper").addClass("global-wrapper-opend");
			$(".sn-outer-wrapper").append(structureAbout);
			$(".about-company-nav").addClass("navigated");
			// $(".our-transport-nav-tabs").fadeOut();
			$(".sn-outer-wrapper").css("overflow-x","hidden");
		}else if($(navItem).hasClass("open_partners_investors")){
			$(".global-wrapper").addClass("opened-nav-menu");
			$(".global-wrapper-wrapper").addClass("global-wrapper-opend");
			$(".sn-outer-wrapper").append(structurePartners);
			$(".partners_investors-nav").addClass("navigated");
			// $(".our-transport-nav-tabs").fadeOut();
			$(".sn-outer-wrapper").css("overflow-x","hidden");
		}
	});

	$(".sn-outer-wrapper").on("click", function(event){
		if( $(".sn-outer-wrapper").hasClass("modalview")){
			$(".header .bottom-part .main-wrap ul li a").removeClass("active");
			$(".nav-mobile-menu .bottom-part ul li a").removeClass("active");
		}
		$(this).removeClass("opened-nav-menu");
	});
}

function mobileMenuAccordion(){
	$(document).on("click", function(event){
		if($(window).width()<1601){
			var targetItem = $(event.target);
			if(targetItem.parent().hasClass("our-services-heading")){
				$(".our-services-nav.navigated div .heading+ul").slideUp();
				$(".our-services-nav .menu-list-item").removeClass("opened");
				if(!$(targetItem).parent().next("ul").hasClass("slided")){
					$(targetItem).closest(".menu-list-item").addClass("opened");
					$(targetItem).parent().next("ul").addClass("slided");
					$(targetItem).parent().next("ul").slideDown();
				}else{
					$(".our-services-nav.navigated div .heading+ul").removeClass("slided");
					$(".our-services-nav .menu-list-item").removeClass("opened");
				}
			}
		}
	})
}

function ourTransportTabs(){
	$(".open_our-transport").on("click",function(event){
		// $(".our-transport-nav-tabs").fadeIn(function(){
		// 	$(this).css('display','flex');
		// });
	});
	$(".our-transport-nav-page").fadeOut();
	$(".our-transport-nav-page").eq(0).fadeIn(function(){
		$(this).css('display','flex')})
	$(".our-transport-nav-tab").on("click", function(){
		$(".our-transport-nav-tab").removeClass("tabbed")
		$(this).addClass("tabbed");
		var currentTab = $(this).index();
		$(".our-transport-nav-page").fadeOut();
		$(".our-transport-nav-page").eq(currentTab).fadeIn("slow", function(){
			$(this).css('display','flex')});
	})
}

// range slider for investor page
var rangeCalc;
function rangeSlider(){
	var leftPosition= null;
	var handle = $( "#custom-handle");
	var step = parseInt($('.number-visual li:last').attr('data-price')) / $('.number-visual li').length;
	rangeCalc = $('#range-calc').slider({
		animate: "slow",
		value:0,
		range: "min",
		min: parseInt($('.number-visual li:first').attr('data-price')),
		max: parseInt($('.number-visual li:last').attr('data-price')),
		step: step,
		create: function(event, ui) {
			$(".upper-handle").text("0");
			$("#custom-handle").trigger("click");
			$( ".range-calc" ).val( "$" + ui.value );
			$("dimension-value").text("0");
			leftPosition = parseInt(handle.css("left"))-25;
			$(".upper-handle").css("left","0%");
		},
		slide: function( event, ui ) {
			$(".upper-handle").text( ui.value);
			$( ".range-calc" ).val( "$" + ui.value );
			leftPosition = parseInt(handle.css("left"))-25;
			setTimeout(function(){
				$(handle).trigger("click");
			},700);
			$(".upper-handle").css("left",leftPosition);
			$(".dimension-value[data-type=cost] span").text(ui.value);
			$('.dimension-value[data-type=total] span').text(parseInt(ui.value) + parseInt($('.dimension-value[data-type=casco] span').text().replace(/\D+/g,'')) + parseInt($('.dimension-value[data-type=osago] span').text().replace(/\D+/g,'')));
			for(i=0; i<$('.number-visual li').length; i++){
				var dataPrice = $(".investment .number-visual li").eq(i).data("price");
				if(ui.value >= dataPrice){
					$(".investment .number-visual li[data-price='"+dataPrice+"']").addClass("priced");
					var index = $(".investment .number-visual li[data-price='"+dataPrice+"']").index();
					$(".investment .income-calc-result .midle-image-wrap img").eq(index).fadeIn();
				}else{
					$(".investment .number-visual li[data-price='"+dataPrice+"']").removeClass("priced");
					var index = $(".investment .number-visual li[data-price='"+dataPrice+"']").index();
					$(".investment .income-calc-result .midle-image-wrap img").eq(index).fadeOut();
				}
			}
			var total = parseInt($('.dimension-value[data-type=total] span').text());
			var incomeTotal = total + (total * 0.01 * parseInt($('.number-visual .priced:last').attr('data-percent')));
			$('.income-calc-result .dimension-value[data-type=income_total] span').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(incomeTotal)));
			$('.income-calc-result .dimension-value[data-type=income_year] span').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(incomeTotal)));
			$('.income-calc-result .dimension-value[data-type=income_month] span').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(incomeTotal/12)));
			$('.income-calc-result .dimension-value[data-type=income_day] span').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(incomeTotal/365)));

			rangeCalcSpinner.spinner("value", ui.value);
		},
		change: function( event, ui ){
			$(".upper-handle").text( ui.value);
			$( ".range-calc" ).val( "$" + ui.value );
			leftPosition = parseInt(handle.css("left"))-25;
			$(".upper-handle").css("left",leftPosition);
		}
	}).draggable();

	$(".range-calc").val( "$" + $("#range-calc").slider("value"));
	$("#range-calc").on("click", function(e){
		leftPosition = parseInt(handle.css("left"))-25;
		$(".upper-handle").css("left",leftPosition);
	});
	$(".midle-part .midle-image-wrap img").fadeOut("fast");
	$(".midle-part .midle-image-wrap img").eq(0).fadeIn("fast");

}

//validation popup forms for investor page
function validationCallInvestment(form){
	var thisForm = $(form);
	var formSur = thisForm.serialize();
	$.ajax({
		url:	thisForm.attr('action'),
		data:	formSur,
		type:	'POST',
		success: function(data){
			if( data.trim() == 'true'){
				thisForm.trigger("reset");
				popNextInvestment("#back-call1", "fancyboxHeading-popup");
			}else{
				thisForm.trigger('reset');
			}
		}
	});
}

function popNextInvestment(popupId, popupWrap){
	$.fancybox.open(popupId,{
		width:		1166,
		openEffect:	'fade',
		closeEffect:'fade',
		padding:	0,
		fitToView:	true,
		wrapCSS:	popupWrap,
		autoResize:	true,
		'closeBtn':	false
	});
 }

function oneImageSize(){
	var curWidth = $(".midle-part").width();
	var curHeight = $(".midle-part").height();
	$(".midle-part img").css({
		"width": curWidth,
		"height": curHeight
	})
}

function investCalculator(){
	$(".investment2").on('change keyup', 'input[name=invest-total]', function(){
		var enteredValue = $(this).val();
		var yearlyIncome = Math.ceil(enteredValue * parseInt($('.investment2 .total-income span').text()) /100);
		var monthlyIncome = Math.ceil(yearlyIncome / 12);
		var dailyIncome = Math.ceil(yearlyIncome /365);
		$(".daily-income .number").html(dailyIncome + ' руб.');
		$(".weekly-income .number").html(monthlyIncome + ' руб.');
		$(".yearly-income .number").html(yearlyIncome + ' руб.');
	})
}

function navMobileToggle(){
	$(".mobile-nav-menu-button").on("click", function(){
		$(".nav-mobile-menu").addClass("visible-nav-mobile-menu");
		$(".global-wrapper").addClass("visible-nav-mobile-menu");
		$("body").addClass("mobile-menu-wieve");
		$('.global-wrapper').addClass("mobile-menu-wieve");
	});
	$(".nav-mobile-close-button").on("click", function(){
		$(".nav-mobile-menu").removeClass("visible-nav-mobile-menu");
		$("body").removeClass("mobile-menu-wieve");
		$(".global-wrapper").removeClass("visible-nav-mobile-menu");
		$('.global-wrapper').removeClass("mobile-menu-wieve");
	});
	// $(window).on("scroll", function(){
	// 	$(".nav-mobile-menu").removeClass("visible-nav-mobile-menu");
	// })
}

//toggle class active for navigation menu items
function toggleClassActive(){


	$(".header .bottom-part .main-wrap ul li a").on("click", function(event){
		$(".header .bottom-part .main-wrap ul li a").removeClass("active");
		$(".nav-mobile-menu .bottom-part ul li a").removeClass("active");
		$(this).addClass("active");
	});
	$(".nav-mobile-menu .bottom-part ul li a").on("click", function(event){
		$(".header .bottom-part .main-wrap ul li a").removeClass("active");
		$(".nav-mobile-menu .bottom-part ul li a").removeClass("active");
		$(this).addClass("active");
		// if($(this).hasClass("open_our-transport")){
		// 	event.stopPropagation();
		// }
	})
}

var rangeCalcSpinner;
function rangeCalcSpinner(){
	rangeCalcSpinner = $( "#range-calc-spinner" ).spinner({
		min:	500000,
		max:	5000000,
		step:	10000,
		start:	500000,
		spin:	function(event, ui){
			$(".dimension-value").text(ui.value);
			rangeCalc.slider("option","value", ui.value);
			for(i=0; i<10; i++){
			var dataPrice = $(".investment .number-visual li").eq(i).data("price");
				if(ui.value >= dataPrice){
					$(".investment .number-visual li[data-price='"+dataPrice+"']").addClass("priced");
					var index = $(".investment .number-visual li[data-price='"+dataPrice+"']").index();
					$(".investment .income-calc-result .midle-image-wrap img").eq(index).fadeIn();
				}else{
					$(".investment .number-visual li[data-price='"+dataPrice+"']").removeClass("priced");
					var index = $(".investment .number-visual li[data-price='"+dataPrice+"']").index();
					$(".investment .income-calc-result .midle-image-wrap img").eq(index).fadeOut();
				}
			}
		}
	});
}

function leftBarPriceSlider(){
	if($('.sort-item #left-bar-min-amount').length > 0){
		var minValue = ($('.sort-item #left-bar-min-amount').val().substr(0,1) == '$')
			? parseInt($('.sort-item #left-bar-min-amount').val().substr(1))
			: parseInt($('.sort-item #left-bar-min-amount').val());
		var maxValue = ($('.sort-item #left-bar-min-amount').val().substr(0,1) == '$')
			? parseInt($('.sort-item #left-bar-max-amount').val().substr(1))
			: parseInt($('.sort-item #left-bar-max-amount').val());

		$("#left-bar-slider-range").slider({
			range:	true,
			min:	0,
			max:	maxValue,
			values:	[ minValue, maxValue ],
			slide:	function( event, ui ){
				$("#left-bar-min-amount").val("" + parseInt(ui.values[ 0 ])+"р");
				$("#left-bar-max-amount").val("" + parseInt(ui.values[ 1 ])+"р");
			}
		});
		$( "#left-bar-min-amount" ).val("" + parseInt($( "#left-bar-slider-range" ).slider("values", 0))+"р");
		$( "#left-bar-max-amount" ).val("" + parseInt($( "#left-bar-slider-range" ).slider("values", 1))+"р");
	}
}

function changePictureAttachment(elem){
	$(window).on("scroll", function(){
		if($(window).scrollTop() > elem.offset().top){
			elem.addClass('attached');
		}else{
			elem.removeClass('attached');
		}
	})
}

function languageChange(){
	var curUrl = window.location.href;
	var curLangMin = curUrl.substring(curUrl.indexOf('('),curUrl.indexOf(')')+1);
	var _imgUrl="";
	var langName="";
	switch(curLangMin){
		case "(ru)":
			_imgUrl="/img/russia1.png";
			langName="Русский";
		break;
		case "(zh-CN)":
			_imgUrl="/img/china.png";
			langName="Китайский";
		break;
		case "(en)":
			_imgUrl="/img/england.png";
			langName="Английский";
		break;
		default:
			_imgUrl="/img/russia1.png";
			langName="Русский";
		break;
	}

	$(".language-picker img").attr("src",_imgUrl);
	$(".language-picker a p").text(langName);
	$("#country_picker a").on("click", function(e){
		e.preventDefault();
		var curLanguage=$(this).data("language");
		var langMin =  curLanguage.substring(curLanguage.indexOf('('),curLanguage.indexOf(')')+1);
		if((/#googtrans/).test(curUrl)){
			curUrl = curUrl.replace(curLangMin,langMin);
		}else{
			curUrl = curUrl + curLanguage;
		}
		window.location = curUrl;
		window.location.reload();
	});
}

function exurtionDatepickeer(){
	$( function() {
		$( "#exurtion-datepicker" ).datepicker({
		  showOtherMonths: true,
		  selectOtherMonths: true,
		  minDate: new Date()
		});
	 });
}

function tableCellWidth(){
	$('.tariff-item').each(function(i,elem){
		var curLenght = $(elem).find(".table-header .cell-description .column").length;
		var curWidth = 100 / curLenght;
		$(elem).find(".table-header .cell-description .column").width(""+curWidth+"%");
		$(elem).find(".cell-description .table-col").width(""+curWidth+"%");
	});
}

function callCalulatorChangesWithDriver(){
	if(
		(typeof window.topCalculator.startPrice == 'undefined')&&
		(typeof window.topCalculator.finishPrice == 'undefined')&&
		(typeof window.topCalculator.maxPrice != 'undefined')&&
		(typeof window.topCalculator.car != 'undefined')&&
		(typeof window.topCalculator.startTime != 'undefined')&&
		(typeof window.topCalculator.endTime != 'undefined')
	){
		var dateStart = {
			year:	'20'+window.topCalculator.startTime.substr(6,2),
			month:	window.topCalculator.startTime.substr(3,2)-1,
			day:	window.topCalculator.startTime.substr(0,2),
			hours:	window.topCalculator.startTime.substr(9,2),
			minute:	window.topCalculator.startTime.substr(12,2)
		};
		var dateFinish = {
			year:	'20'+window.topCalculator.endTime.substr(6,2),
			month:	window.topCalculator.endTime.substr(3,2)-1,
			day:	window.topCalculator.endTime.substr(0,2),
			hours:	window.topCalculator.endTime.substr(9,2),
			minute:	window.topCalculator.endTime.substr(12,2)
		};
		dateStart = new Date(dateStart.year, dateStart.month, dateStart.day, dateStart.hours, dateStart.minute, 0, 0);
		dateFinish = new Date(dateFinish.year, dateFinish.month, dateFinish.day, dateFinish.hours, dateFinish.minute, 0, 0);

		if( (!isNaN(dateStart.getTime())) && (!isNaN(dateFinish.getTime())) ){
			var hours = (dateFinish.getTime() - dateStart.getTime()) / (1000*60*60);
			var price = window.topCalculator.maxPrice*hours;
			$('#headerCalculator .none-driver-cont .bottom-total .score .total p').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(price))+' руб.');
			$('#headerCalculator .none-driver-cont .bottom-total .score').show();
		}
	}
}

function callCalulatorChanges(){
	if(
		(typeof window.topCalculator.startPrice != 'undefined')&&
		(typeof window.topCalculator.finishPrice != 'undefined')&&
		(typeof window.topCalculator.maxPrice != 'undefined')&&
		(typeof window.topCalculator.car != 'undefined')&&
		(typeof window.topCalculator.startTime != 'undefined')&&
		(typeof window.topCalculator.endTime != 'undefined')
	){
		var dateStart = {
			year:	'20'+window.topCalculator.startTime.substr(6,2),
			month:	window.topCalculator.startTime.substr(3,2)-1,
			day:	window.topCalculator.startTime.substr(0,2),
			hours:	window.topCalculator.startTime.substr(9,2),
			minute:	window.topCalculator.startTime.substr(12,2)
		};
		var dateFinish = {
			year:	'20'+window.topCalculator.endTime.substr(6,2),
			month:	window.topCalculator.endTime.substr(3,2)-1,
			day:	window.topCalculator.endTime.substr(0,2),
			hours:	window.topCalculator.endTime.substr(9,2),
			minute:	window.topCalculator.endTime.substr(12,2)
		};
		dateStart = new Date(dateStart.year, dateStart.month, dateStart.day, dateStart.hours, dateStart.minute, 0, 0);
		dateFinish = new Date(dateFinish.year, dateFinish.month, dateFinish.day, dateFinish.hours, dateFinish.minute, 0, 0);

		if( (!isNaN(dateStart.getTime())) && (!isNaN(dateFinish.getTime())) ){
			var hours = (dateFinish.getTime() - dateStart.getTime()) / (1000*60*60);
			var days = Math.ceil(hours / 24);

			var categoryType = $('.calculator-tab-wrapper .pages-line:visible').attr('data-type');
			var category = (categoryType == 'none-driver')? 'car_nonedriver': 'car_with_driver';
			$.ajax({
				url:	'/calculate_rent',
				type:	'GET',
				data:	{
					hours:	hours,
					days:	days,
					id:		window.topCalculator.car,
					type:	category,
					start_price:	window.topCalculator.startPrice,
					finish_price:	window.topCalculator.finishPrice,
					start_day:		dateStart.getDay(),
					finish_day:		dateFinish.getDay()
				},
				success:function(data){
					try{
						data = JSON.parse(data);
						if(data['message'] == 'success'){
							$('#headerCalculator .driver-cont .bottom-total .score .total p').text(new Intl.NumberFormat('ru-RU').format(Math.ceil(data['price']))+' руб.');
							$('#headerCalculator .driver-cont .bottom-total .score').show();
						}
					}catch(e){}
				}
			});
		}
	}
}
var exurtionImagesBindToLink = function(){
	$('.excursion-item .exc-photo').on('click', function(){
		$(this).closest('.excursion-item').find('.info-details a')[0].click();
	});
};

//different values for different submit buttons and one popup= #call-popup
var currentText="";
	function pushInfoOrderValue(){
		$(".rent-cost").on("click", function(){
			currentText=0;
			$("#call-popup input[name='order_case']").val('0');
		});
		$(".form-order").on("click", function(){
			currentText=1;
			$("#call-popup input[name='order_case']").val('1');
		});
		$(".fast-order a").on("click", function(){
			currentText = $(this).closest(".car-info").find("em").text();
			$("#call-popup input[name='order_case']").val(currentText);
		});
		$('.about-order').on('click', function(){
			currentText = 'about_'+$(this).closest(".slick-track").find(".slick-active .transport-title").text();
			$("#call-popup input[name='order_case']").val(currentText);
		});
		$('.investor-order').on('click',function(){
			$("#call-popup input[name='order_case']").val('2');

			$('#call-popup input[name=etc]').val($('.investment2 input[name=invest-total]').val());
		});
		$('.investor-calc-order').on('click',function(){
			$("#call-popup input[name='order_case']").val('3');
			var temp = [];
			$('.income-calc-result .left-part .line').each(function(){
				temp.push({
					title: $(this).find('.dimension').text(),
					val: $(this).find('.dimension-value span').text()
				});
			});
			$('.income-calc-result .right-part .line').each(function(){
				temp.push({
					title: $(this).find('.dimension').text(),
					val: $(this).find('.dimension-value span').text()
				});
			});
			$('#call-popup input[name=etc]').val(JSON.stringify(temp));
		});
	}
//
var fixedFilterCallLine = function(){
	if($('.sort-bar').length){
		$('.sort-bar').clone().prependTo('body').addClass('new-sort-bar');
		var offsetTop = $('.main>.sort-bar').offset().top;
		$(window).on("scroll", function(){
			if($(window).scrollTop() >= offsetTop){
				$("body > .sort-bar").addClass("visible");
				$(".main > .sort-bar").addClass("hidden");
			}else{
				$("body > .sort-bar").removeClass("visible");
				$(".main > .sort-bar").removeClass("hidden");
			}
		});
	}
};
var resize_falag;

//home page adding links in  slider
	var linksAdding = function(){
		if($('.shadow-hidden-blocks').length){
			var linksArray = [
				'transport/car_with_driver',
				'transport/car_nonedriver',
				'transport/van_bus/car_with_driver',
				'transport/bus/car_with_driver'
			];
			for(var q = 0; q<$('.shadow-hidden-blocks .shadow-item_link').length; q++ ){
				$('.shadow-hidden-blocks .shadow-item_link').eq(q).attr('href',linksArray[q]);
			}
		}
	};
//home page adding links in  slider

$(document).ready(function(){
	if($(window).width()>=1601){
		resize_falag= 0;
	}else if($(window).width()<1601){
		resize_falag= 1;
	}
	window.topCalculator = {};
	investCalculator();
	rangeSlider();
	driver_nondriverToggle();
	timePicker($('#nonedriver-hourpicker-start'),$("#nonedriver-minutepicker-start"));
	timePicker($('#nonedriver-hourpicker-end'),$("#nonedriver-minutepicker-end"));

	if($('#nonedriver-hourpicker-start-order').length && $("#nonedriver-minutepicker-start-order").length && $('#nonedriver-hourpicker-end-order') && $("#nonedriver-minutepicker-end-order")){
		timePicker($('#nonedriver-hourpicker-start-order'),$("#nonedriver-minutepicker-start-order"));
		timePicker($('#nonedriver-hourpicker-end-order'),$("#nonedriver-minutepicker-end-order"));
	}

	timePicker($('#driver-hourpicker-start'),$("#driver-minutepicker-start"));
	timePicker($('#driver-hourpicker-end'),$("#driver-minutepicker-end"));

	timePickerRange($('#nonedriver-hourpicker-start'),$("#nonedriver-minutepicker-start"),$('#nonedriver-hourpicker-end'),$("#nonedriver-minutepicker-end"));

	timePickerRange($('#driver-hourpicker-start'),$("#driver-minutepicker-start"),$('#driver-hourpicker-end'),$("#driver-minutepicker-end"));
	if($('#nonedriver-hourpicker-start-order').length){
		timePickerRange($('#nonedriver-hourpicker-start-order'),$("#nonedriver-minutepicker-start-order"),$('#nonedriver-hourpicker-end-order'),$("#nonedriver-minutepicker-end-order"));
	}
	dropdownDatepicker();
	datepickerTab(".driver-cont");
	datepickerTab(".none-driver-cont");
	if($(".order-info").length){
		datepickerTab(".order-info");
	}
	datepickerInit();
	dataRange($('#nonedriver-datepicker-start'),$('#nonedriver-datepicker-end'));
	dataRange($('#driver-datepicker-start'),$('#driver-datepicker-end'));
	datepickerButtonSubmit($('#nonedriver-datepicker-start'),$('#nonedriver-datepicker-end'));
	datepickerButtonSubmit($('#driver-datepicker-start'),$('#driver-datepicker-end'));
	dataRange($('#nonedriver-datepicker-start-order'),$('#nonedriver-datepicker-end-order'));
	datepickerButtonSubmit($('#nonedriver-datepicker-start-order'),$('#nonedriver-datepicker-end-order'));

	// dataRange($("#headerCalculator nonedriver-cont .start-rent+.dropdown-datepicker .datepicker-init"),$("#headerCalculator nonedriver-cont .end-rent+.dropdown-datepicker .datepicker-init"));
	cityPickerToggle();
	fancyboxHeading();
	fancyboxHeading1();
	validate('.city-picker-form', {submitFunction:validationCall1});
	validate('.driver-cont', {submitFunction:HeaderCalculatorValidationCall});
	validate('.none-driver-cont', {submitFunction:HeaderCalculatorValidationCall});
	calculatorDatePickerValidate('.driver-cont');
	calculatorDatePickerValidate('.none-driver-cont');
	ourTransportTabs();
	// backgroundScrollAnimation($(".investors-image-wrap"));
	$('.global-wrapper').SecretNav({
		navSelector: '.our-transport-nav',         // selector of the nav tag
		openSelector: '.open_our-transport', // selector of the menu's opener
		position: 'top'            // left | top
	});
	validate('.investment', {submitFunction:validationCallInvestment});
	headerLeftMenuToggle();
	SmoothScroll({ stepSize: 100,
	animationTime    : 1000 });
	oneImageSize();
	navMobileToggle();
	toggleClassActive();
	rangeCalcSpinner();
	mobileMenuAccordion();
	leftBarPriceSlider();
	if($(".investors-image-wrap").length){changePictureAttachment($(".investors-image-wrap"));}
	if($(".business-travel-pic").length){changePictureAttachment($(".business-travel-pic"));}
	if($(".credit-cart-pic").length){changePictureAttachment($(".credit-cart-pic"));}
	if($(".rent-buy-car-pic").length){changePictureAttachment($(".rent-buy-car-pic"));}
	if($(".operation-lising-pic").length){changePictureAttachment($(".operation-lising-pic"));}
	if($(".car-taxi-pic").length){changePictureAttachment($(".car-taxi-pic"));}
	if($(".cooperation-people-pic").length){changePictureAttachment($(".cooperation-people-pic"));}
	if($(".franchising-pic").length){changePictureAttachment($(".franchising-pic"));}
	languageChange();
	exurtionDatepickeer();
	pushInfoOrderValue();
	tableCellWidth();
	linksAdding();

	//header calculator
	$('#headerCalculator .driver-cont select[name=calculator-city]').change(function(){
		$.ajax({
			url:	'/get_location',
			type:	'GET',
			data:	{city: $(this).val()},
			success:function(data){
				try{
					data = JSON.parse(data);
					$('#headerCalculator select[name=calculator-place] option:gt(0), #headerCalculator select[name=calculator-place-back] option:gt(0)').remove();
					for(var i in data){
						$('#headerCalculator .driver-cont select[name=calculator-place]').append('<option value="'+data[i]['slug']+'" data-take="'+data[i]['take']+'" data-return="'+data[i]['return']+'">'+data[i]['title']+'</option>');
						$('#headerCalculator .driver-cont select[name=calculator-place-back]').append('<option value="'+data[i]['slug']+'"data-take="'+data[i]['take']+'" data-return="'+data[i]['return']+'">'+data[i]['title']+'</option>');
					}
					$('#headerCalculator select[name=calculator-place], #headerCalculator select[name=calculator-place-back]').trigger('refresh');
					window.topCalculator.places = [];
				}catch(e){}
			}
		})
	});

	$('#headerCalculator select[name=calculator-place], #headerCalculator select[name=calculator-place-back]').change(function(){
		window.topCalculator.places[$(this).val()] = $(this).find('option:selected').text();
		/*$('#headerCalculator select[name=calculator-place-pay]').empty();
		for(var i in window.topCalculator.places){
			$('#headerCalculator select[name=calculator-place-pay]').append('<option value="'+i+'">'+window.topCalculator.places[i]+'</option>');
		}
		$('#headerCalculator select[name=calculator-place-pay]').trigger('refresh');*/

		if($(this).attr('name') == 'calculator-place'){
			window.topCalculator.startPrice = $(this).find('option:selected').attr('data-take');
		}else{
			window.topCalculator.finishPrice = $(this).find('option:selected').attr('data-return');
		}
	});

	$('#headerCalculator select[name=calculator-car-type]').change(function(){
		var categoryType = $('.calculator-tab-wrapper .pages-line:visible').attr('data-type');
		var category = (categoryType == 'none-driver')? 'car_nonedriver': 'car_with_driver';
		$.ajax({
			url:	'/get_mark_by_category',
			type:	'GET',
			data:	{slug: $(this).val(), category:category},
			success:function(data) {
				try{
					data = JSON.parse(data);
					if(category == 'car_nonedriver'){
						$('#headerCalculator .driver-cont select[name=calculator-car-brand]').empty().append('<option></option>');
						for(var i in data){
							$('#headerCalculator .driver-cont select[name=calculator-car-brand]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
						}
						$('#headerCalculator .driver-cont select[name=calculator-car-brand]').trigger('refresh');
					}else{
						$('#headerCalculator .none-driver-cont select[name=calculator-car-brand]').empty().append('<option></option>');
						for(var i in data){
							$('#headerCalculator .none-driver-cont select[name=calculator-car-brand]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
						}
						$('#headerCalculator .none-driver-cont select[name=calculator-car-brand]').trigger('refresh');
					}
				}catch(e){}
			}
		});
	});
	$('#headerCalculator').on('change','select[name=calculator-car-brand]', function(){
		var upperType = $('.calculator-tab-wrapper .pages-line:visible').attr('data-type');
		var upper = (upperType == 'none-driver')? 'car_nonedriver': 'car_with_driver';
		var category = (upper == 'car_nonedriver')? $('#headerCalculator .driver-cont select[name=calculator-car-type]').val(): $('#headerCalculator .none-driver-cont select[name=calculator-car-type]').val();

		$.ajax({
			url:	'/get_models_by_mark',
			type:	'GET',
			data:	{mark: $(this).val(), category:category, upper:upper},
			success:function(data){
				try{
					data = JSON.parse(data);
					if(upper == 'car_nonedriver'){
						$('#headerCalculator .driver-cont select[name=calculator-car-model]').empty().append('<option></option>');
						for(var i in data){
							$('#headerCalculator .driver-cont select[name=calculator-car-model]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
						}
						$('#headerCalculator .driver-cont select[name=calculator-car-model]').trigger('refresh');
					}else{
						$('#headerCalculator .none-driver-cont select[name=calculator-car-model]').empty().append('<option></option>');
						for(var i in data){
							$('#headerCalculator .none-driver-cont select[name=calculator-car-model]').append('<option value="'+data[i]['slug']+'">'+data[i]['title']+'</option>');
						}
						$('#headerCalculator .none-driver-cont select[name=calculator-car-model]').trigger('refresh');
					}
				}catch(e){}
			}
		});
	});
	$('#headerCalculator').on('change','select[name=calculator-car-model]', function(){
		var categoryType = $('.calculator-tab-wrapper .pages-line:visible').attr('data-type');
		var category = (categoryType == 'none-driver')? '2': '1';
		var mark = (category == '2')? $('#headerCalculator .driver-cont select[name=calculator-car-brand]').val(): $('#headerCalculator .none-driver-cont select[name=calculator-car-brand]').val();
		var model = $(this).val();

		$.ajax({
			url:	'/get_car_by_model',
			type:	'GET',
			data:	{mark:mark, model: model, driver:category},
			success:function(data){
				try{
					data = JSON.parse(data);
					window.topCalculator.maxPrice = -1;
					for(var i in data){
						if(data[i]['price'] > window.topCalculator.maxPrice){
							window.topCalculator.maxPrice = data[i]['price'];
							window.topCalculator.car = data[i]['id'];
						}
					}
					$('#headerCalculator .bottom-total .image-wrap').empty();
					if(category == '2'){
						if(data[0]['img_url'].length >0){
							$('#headerCalculator .driver-cont .bottom-total .image-wrap').append('<img src="'+data[0]['img_url'][0]['img']+'" alt="">');
						}
						callCalulatorChanges()
					}else{
						if(data[0]['img_url'].length >0){
							$('#headerCalculator .none-driver-cont .bottom-total .image-wrap').append('<img src="'+data[0]['img_url'][0]['img']+'" alt="">');
						}
						callCalulatorChangesWithDriver();
					}
				}catch(e){}
			}
		})
	});

	$('#headerCalculator .driver-cont select[name=calculator-place], #headerCalculator .driver-cont select[name=calculator-place-back]').change(function(){
		callCalulatorChanges();
	});
	// /header calculator
	if($('video').length >0) $('video').get(0).play();
	fixedFilterCallLine();
	exurtionImagesBindToLink();
	/*$(document).on('click', function(event){
		console.log(event.target);
	})*/
});

$(window).load(function(){

});

$(window).resize(function(){
	oneImageSize();
	if($(window).width()<1601 && resize_falag == 1){
		$(".our-services-nav.navigated div .heading+ul").slideUp();
		resize_falag=0;
	}else if($(window).width()>=1601 && resize_falag == 0){
		$(".our-services-nav.navigated div .heading+ul").slideDown();
		resize_falag = 1;
	}
});
