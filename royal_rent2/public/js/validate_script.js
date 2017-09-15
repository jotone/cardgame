/*валидация формы*/
function validate(form, options){
	var setings = {
		errorFunction:null,
		submitFunction:null,
		highlightFunction:null,
		unhighlightFunction:null
	}
	$.extend(setings, options);

	var $form = $(form);

	if ($form.length && $form.attr('novalidate') === undefined) {
		$form.on('submit', function(e) {
			e.preventDefault();
		});

		$form.validate({
			errorClass : 'errorText',
			focusCleanup : true,
			focusInvalid : false,
			invalidHandler: function(event, validator) {
				if(typeof(setings.errorFunction) === 'function'){
					setings.errorFunction(form);
				}
			},
			errorPlacement: function(error, element) {
				error.appendTo( element.closest('.form_input'));
			},
			highlight: function(element, errorClass, validClass) {
				$(element).addClass('error');
				$(element).closest('.form_row').addClass('error').removeClass('valid');
				if( typeof(setings.highlightFunction) === 'function' ) {
					setings.highlightFunction(form);
				}
			},
			unhighlight: function(element, errorClass, validClass) {
				$(element).removeClass('error');
				if($(element).closest('.form_row').is('.error')){
					$(element).closest('.form_row').removeClass('error').addClass('valid');
				}
				if( typeof(setings.unhighlightFunction) === 'function' ) {
					setings.unhighlightFunction(form);
				}
			},
			submitHandler: function(form) {
				if( typeof(setings.submitFunction) === 'function' ) {
					setings.submitFunction(form);
				} else {
					$form[0].submit();
				}
			}
		});

		$('[required]',$form).each(function(){
			$(this).rules( "add", {
				required: true,
				messages: {
					required: "Вы пропустили"
				}
			});
		});

		if($('[type="email"]',$form).length) {
			$('[type="email"]',$form).rules( "add",
			{
				messages: {
					email: "Невалидный email"
				 }
			});
		}

		if($('.tel-mask[required]',$form).length){
			$('.tel-mask[required]',$form).rules("add",
			{
				messages:{
					required:"Введите номер мобильного телефона."
				}
			});
		}

		$('[type="password"]',$form).each(function(){
			if($(this).is("#re_password") == true){
				$(this).rules("add", {
					minlength:3,
					equalTo:"#password",
					messages:{
						equalTo:"Неверный пароль.",
						minlength:"Недостаточно символов."
					}
				});
			}
		})
	}
}

/*Отправка формы с вызовом попапа*/
function validationCall(form){
	var thisForm = $(form);
	var formSur = thisForm.serialize();

	$.ajax({
		url : thisForm.attr('action'),
		data: formSur,
		method:'POST',
		success : function(data){
			if(data.trim() == 'true'){
				thisForm.trigger("reset");
				popNext("#call_success", "call-popup");
			}else if(data == 'success'){
				thisForm.trigger('reset');
                popNext("#call_success", "call-popup");
			}else{
                thisForm.trigger('reset');
			}
		}
	});
}

function validationCallReview(form){
	var thisForm = $(form);
	var formSur = thisForm.serializeArray();
	for(var key in formSur){
		if(formSur[key]['name'] == 'g-recaptcha-response') {
			if ('' != formSur[key]['value'].trim()) {
				$.ajax({
					url: thisForm.attr('action'),
					header: {'X-CSRF-TOKEN': thisForm.find('input[name=_token]').val()},
					data: formSur,
					method: 'PUT',
					success: function (data) {
						if(data == 'success'){
							location.reload(true);
						}
					}
				});
			}
		}
	}
}

/* Отправка формы с файлом */
/* не использовать input[type="file"] в форме и не забыть дописать форме enctype="multipart/form-data" */
function validationCallDocument(form){
	var thisForm = $(form);
	var formData = new FormData($(form)[0]);

	formData.append('file', thisForm.find('input[type=file]')[0].files[0]);
	$.ajax({
		url: thisForm.attr('action'),
		type: "POST",
		data: formData,
		contentType:false,
		processData:false,
		cache:false,
		success: function(response) {
			thisForm.trigger("reset");
			popNext("#call_success", "call-popup");
		}
	});

}

/* Отправка формы с файлaми */
/* не использовать input[type="file"] в форме и не забыть дописать форме enctype="multipart/form-data" */
function validationCallDocuments(form){
	var thisForm = $(form);
	var formData = new FormData($(form)[0]);

	$.each(thisForm.find('input[type="file"]')[0].files, function(index, file){
		formData.append('file['+index+']', file);
	});
	$.ajax({
		url: thisForm.attr('action'),
		type: "POST",
		data: formData,
		contentType:false,
		processData:false,
		cache:false,
		success: function(response) {
			thisForm.trigger("reset");
			popNext("#call_success", "call-popup");
		}
	});

}

function popNext(popupId, popupWrap){
	$.fancybox.open(popupId,{
		padding:0,
		fitToView:false,
		wrapCSS:popupWrap,
		autoSize:true,
		afterClose: function(){
			$('form').trigger("reset");
			clearTimeout(timer);
		}
	});

	var timer = null;

	timer = setTimeout(function(){
		$('form').trigger("reset");
		$.fancybox.close(popupId);
	},2000);

}


/*маска на инпуте*/
function Maskedinput(){
	if($('.tel-mask')){
		$('.tel-mask').mask('+9 (999) 999-99-99 ');
	}

	if($(".numeric")){
		$('.numeric').mask('99999999999999');
	}
}

/*fansybox на форме*/
function fancyboxForm(){
	$('.fancybox-form').fancybox({
		openEffect  : 'fade',
		closeEffect : 'fade',
		autoResize:true,
		wrapCSS:'fancybox-form',
		'closeBtn' : true,
		fitToView:true,
		padding:'0',
		closeClick: false
	});
}

//ajax func for programmer

function someAjax(item, someUrl, successFunc, someData){
	$(document).on('click', item, function(e){
		e.preventDefault();
		var itemObject = $(this);
		var ajaxData = null;
		if(typeof someData == 'function'){
			ajaxData = someData(itemObject);
		}else{
			ajaxData = someData;
		}
		console.log(ajaxData);
		$.ajax({
			url:someUrl,
			data:ajaxData,
			method:'POST',
			success : function(data){
				successFunc(data, itemObject);
			}
		});

	});

}

/* example for someAjax func

	write like this
	someAjax('.link', '/programer_item.php', someFuncName, {action:'someAction', item_id:id});

	or

	someAjax('.link','/programer_item.php', someFuncName, someDataFuncName);

	where

	function someDataFuncName(itemObject){

		return {id:itemObject.data('id'), text:itemObject.parents('.parentOfItemObject').data('text')};

		// where itemObject = $('.link') in someAjax func

	}

*/


$(document).ready(function(){
	validate('#call-popup .contact-form', {submitFunction:validationCall});
	validate('.contacts-form', {submitFunction:validationCall});
	validate('.subscribe-form', {submitFunction:validationCall});
	//  validate('.login', {submitFunction:validationCall});

	validate('.back-call', {submitFunction:validationCall});
	validate('.back-call1', {submitFunction:validationCall});

	validate('.corp-contract-form', {submitFunction:validationCall});
	validate('.request-form', {submitFunction:validationCall});

	validate('.add-review-form', {submitFunction:validationCallReview});

	validate('.calculator-form', {submitFunction:validationCall});
	validate('.investment2',{submitFunction:validationCall});

	validate('#hirePurchaseForm', {submitFunction:validationCall});

	Maskedinput();
	fancyboxForm();
});