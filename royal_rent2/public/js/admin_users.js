$(document).ready(function(){
	$('button[name=editUset]').click(function(){
		var token = $('header').attr('data-token');
		formData.append('id', $('input[name=id]').val());
		formData.append('email', $('input[name=email]').val());
		formData.append('name', $('input[name=name]').val());
		formData.append('phone', $('input[name=phone]').val());
		formData.append('role', $('select[name=userRole]').val());

		$.ajax({
			url:	'/admin/users/edit',
			type:	'POST',
			headers:{'X-CSRF-TOKEN': token},
			processData: false,
			contentType: false,
			data:	formData,
			error:	function(xhr){
				showErrors(xhr.responseText, '/admin/users/edit');
			},
			success:function(data) {
				if(data != 'success'){
					showErrors(data, '/admin/users/edit');
				}else{
					location = '/admin/users';
				}
			}
		});
	});

	$('.item-list a.drop').click(function(e){
		e.preventDefault();
		var result = prompt('Для удаления пользователя введите его логин как подтверждение.','');
		if(result == $(this).attr('data-title')){
			var token = $('header').attr('data-token');
			var id = $(this).attr('data-id');
			var that = $(this);
			$.ajax({
				url:	'/admin/users/delete',
				type:	'DELETE',
				headers:{'X-CSRF-TOKEN': token},
				data:	{id:id},
				error:	function(xhr){
					showErrors(xhr.responseText, '/admin/users/delete');
				},
				success:function(data){
					if(data != 'success'){
						showErrors(data, '/admin/users/delete');
					}else{
						that.closest('tr').remove();
					}
				}
			});
		}
	});
});