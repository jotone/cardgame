@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/spectrum.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/admin_products.js') }}"></script>
	<link rel="stylesheet" href="{{ URL::asset('css/spectrum.css') }}" />
@stop
@section('content')
	<div class="main-block" data-module="{{ $module_id }}" data-sort="{{ $active_direction['sort'] }}" data-direction="{{$active_direction['dir']}}">
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">{{ $page_title }}</div>
			<div class="button-wrap">
				<a class="control-button" href="{{ URL::asset('/admin/'.$global_slug.'/add') }}">Добавить</a>
			</div>
			@if(1 < $pagination['last_page'])
				<div class="row-wrap">
					<ul class="pagination">
						@if($pagination['current_page'] > 1)
							<li><a href="{{ URL::asset('/admin/'.$global_slug.'/?page=1&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&laquo;</a></li>
							<li><a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.($pagination['current_page'] -1).'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&lsaquo;</a></li>
						@endif
						@for($i=1; $i<=$pagination['last_page']; $i++)
							<li><a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$i.'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}" @if($pagination['current_page'] == $i) class="active" @endif>{{$i}}</a></li>
						@endfor
						@if($pagination['current_page'] < $pagination['last_page'])
							<li><a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.($pagination['current_page'] +1).'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&rsaquo;</a></li>
							<li><a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['last_page'].'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&raquo;</a></li>
						@endif
					</ul>
				</div>
			@endif
			<div class="categories-list-wrap">
				@if(!empty($content))
				<table class="item-list col_1">
					<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Название
							<div class="direction" id="title">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=title&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=title&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Ссылка
							<div class="direction" id="slug">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Превью</th>
						<th>Цена
							<div class="direction" id="price">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=price&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=price&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Автор
							<div class="direction" id="author">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=author&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=author&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Категория
							<div class="direction" id="category">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=category&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=category&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Марка
							<div  class="direction" id="mark">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=mark&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=mark&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Модель
							<div  class="direction" id="model">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=model&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=model&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Опубликован
							<div class="direction" id="published">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=published&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=published&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Создан
							<div class="direction" id="created">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=created&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=created&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
						<th>Изменен
							<div class="direction" id="updated">
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=updated&dir=asc') }}" class="up">&#9650;</a>
								<a href="{{ URL::asset('/admin/'.$global_slug.'/?page='.$pagination['current_page'].'&sort_by=updated&dir=desc') }}" class="down">&#9660;</a>
							</div>
						</th>
					</tr>
					</thead>
					@foreach($content as $item)
						<tr data-id="{{ $item['id'] }}">
							<td>
								<a class="block-button edit" href="{{ URL::asset('/admin/'.$global_slug.'/edit/'.$item['id']) }}" title="Редактировать">
									<img src="{{ URL::asset('img/edit.png') }}" alt="Редактировать">
								</a>
							</td>
							<td>
								<a class="block-button drop" data-id="{{ $item['id'] }}" href="#" data-title="{{ $item['title'] }}" title="Удалить">
									<img src="{{ URL::asset('img/drop.png') }}" alt="Удалить">
								</a>
							</td>
							<td>{{ $item['title'] }}</td>
							<td>{{ $item['slug'] }}</td>
							<td>
								@if(!empty($item['img_url']))
									<img class="item-list-image" src="{{ URL::asset($item['img_url']) }}" alt="">
								@else
									Изображение отсутствует
								@endif
							</td>
							<td>{{ $item['price'] }}</td>
							<td>{{ $item['author'] }}</td>
							<td></td>
							<td></td>
							<td></td>
							<td>
								<div class="row-wrap">{{ $item['published_at'] }}</div>
								<label class="fieldset-label-wrap">
									<input name="enabled" class="chbox-input" type="checkbox" {{ $item['enabled'] }}>
									<span>Опубликован</span>
								</label>
							</td>
							<td>{{ $item['created_at'] }}</td>
							<td>{{ $item['updated_at'] }}</td>
						</tr>
						@endforeach
						</tbody>
				</table>
				@endif
			</div>
		</div>
	</div>
@stop