$(document).ready(function(){
	var token = $('header').attr('data-token');

	$('button[name=addItem]').click(function(){
		$('.item-list tbody').append('<tr>' +
			'<td>'+
				'<a class="block-button drop" data-id="0" href="#" title="Удалить">'+
					'<img src="/img/drop.png" alt="Удалить">'+
				'</a>'+
			'</td>' +
			'<td><input name="link_from" type="text" class="text-input col_1" placeholder="Источник&hellip;"></td>'+
			'<td><input name="link_to" type="text" class="text-input col_1" placeholder="Назначение&hellip;"></td>' +
		'</tr>');
	});

	$('button[name=save]').click(function(){
		var links = [];
		$('.item-list tbody tr').each(function(){
			if($(this).find('input[name=link_from]').val().trim() != ''){
				var id = (typeof $(this).attr('data-id') != 'undefined')?  $(this).attr('data-id'): 0;

				links.push({
					id: id,
					from: $(this).find('input[name=link_from]').val().trim(),
					to: $(this).find('input[name=link_to]').val().trim()
				});
			}
		});

		$.ajax({
			url:		'/admin/save_redirects',
			type:		'POST',
			headers:	{'X-CSRF-TOKEN': token},
			data:		{links:JSON.stringify(links)},
			error:		function(xhr){
				showErrors(xhr.responseText, '/admin/save_redirects');
			},
			success:	function(data){
				try{
					data = JSON.parse(data);
					if(data['message'] == 'success'){
						location.reload(true);
					}else{
						showErrors(data, '/admin/save_redirects');
					}
				}catch(e){
					showErrors(e+data, '/admin/save_redirects');
				}
			}
		});
	});

	$('.item-list').on('click','a.drop',function(e){
		e.preventDefault();
		if(typeof $(this).closest('tr').attr('data-id') == 'undefined'){
			$(this).closest('tr').remove();
		}else{
			var _this = $(this);
			var result = confirm('Вы действительно хотите удалить данное посылание?');
			if(result){
				var id = $(this).closest('tr').attr('data-id');
				$.ajax({
					url:	'/admin/drop_redirect',
					type:	'DELETE',
					headers:{'X-CSRF-TOKEN': token},
					data:	{id:id},
					error:	function(xhr){
						showErrors(xhr.responseText, '/admin/drop_redirect');
					},
					success:function(data){
						try {
							data = JSON.parse(data);
							if(data['message'] == 'success'){
								_this.closest('tr').remove();
							}else{
								showErrors(data, '/admin/drop_redirect');
							}
						}catch(e){
							showErrors(e+data, '/admin/drop_redirect');
						}
					}
				});
			}
		}
	});
});