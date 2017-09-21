@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="leagueOptions">
	<table class="edition" style="width: 100%">
		<thead>
		<tr>
			<th></th>
			@foreach($list as $value)
				<th>Лига №{{ $value['league'] }}</th>
			@endforeach
		</tr>
		</thead>
		<tbody>
			<tr>
				<td>Рыцари Империи</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['knight']))
						<p>@if($value['knight']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['knight']['win']) }}</p>
						<p>"Честных": {{ $value['knight']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['knight']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
			<tr>
				<td>Хозяева Леса</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['forest']))
						<p>@if($value['forest']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['forest']['win']) }}</p>
						<p>"Честных": {{ $value['forest']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['forest']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
			<tr>
				<td>Горцы</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['highlander']))
						<p>@if($value['highlander']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['highlander']['win']) }}</p>
						<p>"Честных": {{ $value['forest']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['highlander']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
			<tr>
				<td>Проклятые</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['cursed']))
						<p>@if($value['cursed']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['cursed']['win']) }}</p>
						<p>"Честных": {{ $value['cursed']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['cursed']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
			<tr>
				<td>Нечисть</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['undead']))
						<p>@if($value['undead']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['undead']['win']) }}</p>
						<p>"Честных": {{ $value['undead']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['undead']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
			<tr>
				<td>Монстры</td>
				@foreach($list as $value)
					<td>
					@if(!empty($value['monsters']))
						<p>@if($value['monsters']['win'] >= 0) Выграно игр: @else Проиграно игр: @endif{{ abs($value['monsters']['win']) }}</p>
						<p>"Честных": {{ $value['monsters']['fair'] }}</p>
						<p>Игрок сдался: {{ $value['monsters']['leave'] }}</p>
					@endif
					</td>
				@endforeach
			</tr>
		</tbody>
	</table>
	<div class="container-wrap">
		<form action="{{ route('admin-statistics-reset') }}" method="post" target="_self">
			{{ csrf_field() }}
			<button name="reset" type="submit">Обнулить данные</button>
		</form>
	</div>
</div>
@stop