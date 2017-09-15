$(document).ready(function(){

	$('.work-place-wrap').on('keyup change', 'input[name=title], input[name=pseudonim]',function(){
		var str = str2urlUpperCase($(this).val());
		$('.work-place-wrap input[name=pseudonim]').val(str);
	});

	$('button[name=saveRole]').click(function(){
		if( $('input[name=title]').val().trim() == '' ){
			$('input[name=title]').addClass('errorInp');
			goTo('input[name=title]');
		}else{
			var token = $('header').attr('data-token');
			var pages = [];
			$('.chbox-selector-wrap .checkbox-item-wrap').each(function(){
				if($(this).find('input[name=page]').prop('checked') == true){
					pages.push($(this).find('input[name=page]').val());
				}
			});
			formData.append('id',$('input[name=id]').val());
			formData.append('title',$('input[name=title]').val().trim());
			formData.append('pseudonim',$('input[name=pseudonim]').val().trim());
			formData.append('pages', JSON.stringify(pages));
			$.ajax({
				url:	'/admin/user_roles/add',
				type:	'POST',
				headers:{'X-CSRF-TOKEN': token},
				processData: false,
				contentType: false,
				data:	formData,
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/user_roles/add');
				},
				success:function(data) {
					if(data != 'success'){
						showErrors(data, '/admin/user_roles/add');
					}else{
						location = '/admin/user_roles';
					}
				}
			});
		}
	});

	$('.item-list a.drop').click(function(e){
		e.preventDefault();
		var title = $(this).attr('data-title');
		var conf = confirm('Вы действительно хотите удалить '+title+'?');
		var that = $(this);
		if(conf){
			var token = $('header').attr('data-token');
			var id = $(this).attr('data-id');
			$.ajax({
				url:	'/admin/user_roles/delete',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/user_roles/delete');
				},
				success:function(data){
					if(data != 'success'){
						showErrors(data, '/admin/user_roles/delete');
					}else{
						that.closest('tr').remove();
					}
				}
			});
		}
	})
});