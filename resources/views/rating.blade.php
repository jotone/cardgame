@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if($user)
	@include('layouts.top')

	<div class="main not-main ranking-page">
		<div class="mbox">
			<div class="content-top-wrap">
				<div class="dragon-image cfix">
					<div class="dragon-middle">
						<img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
						<img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
					</div>
				</div>
				<div class="tabulate-image"></div>
			</div>

			@include('layouts.sidebar')

			<div class="content-wrap">
				<div class="content-card-wrap-main">
					<div class="ranking-page-title">Рейтинг игроков</div>
					<div class="ranking-page-buttons-league">
						<ul>
							<li class="active loaded" data-league="all">Общий рейтинг</li>
							@foreach($leagues as $league_iter => $league_data)
							<li data-league="{{ $league_data->slug }}">{{ $league_data->title }} лига</li>
							@endforeach
						</ul>
						<div class="ranking-page-search">
							<form action="">
								<input type="text" required="required" placeholder="Введите имя игрока" data-activeUser="">
								<button type="submit"></button>
							</form>
						</div>
					</div>
					<div class="ranking-page-table-head">
						<div class="ranking-page-table-row bg-toper cfix">
							<div class="cell place">№</div>
							<div class="cell name">Игроки</div>
							<div class="cell battles">Количевство боев</div>
							<div class="cell percent">Процент побед</div>
							<div class="cell ranking">Общий рейтинг</div>
						</div>
						<div class="ranking-page-table-head-container active" data-league="all">
							<div class="ranking-page-table-row cfix @if($user['login'] == $users_data[0]['login']){{ 'active' }}@endif">
								<div class="cell place"><img src="{{ URL::asset('images/1st-place.png') }}" alt=""></div>
								<div class="cell name">{{ $users_data[0]['login'] }}</div>
								<div class="cell battles">{{ $users_data[0]['games'] }}</div>
								<div class="cell percent">{{ $users_data[0]['wins_percent'] }} %</div>
								<div class="cell ranking">{{ $users_data[0]['rating'] }}</div>
							</div>
							@if(isset($users_data[1]))
							<div class="ranking-page-table-row cfix @if($user['login'] == $users_data[1]['login']){{ 'active' }}@endif">
								<div class="cell place"><img src="{{ URL::asset('images/2nd-place.png') }}" alt=""></div>
								<div class="cell name">{{ $users_data[1]['login'] }}</div>
								<div class="cell battles">{{ $users_data[1]['games'] }}</div>
								<div class="cell percent">{{ $users_data[1]['wins_percent'] }} %</div>
								<div class="cell ranking">{{ $users_data[1]['rating'] }}</div>
							</div>
							@endif
							@if(isset($users_data[2]))
							<div class="ranking-page-table-row  cfix @if($user['login'] == $users_data[2]['login']){{ 'active' }}@endif">
								<div class="cell place"><img src="{{ URL::asset('images/3rd-place.png') }}" alt=""></div>
								<div class="cell name">{{ $users_data[2]['login'] }}</div>
								<div class="cell battles">{{ $users_data[2]['games'] }}</div>
								<div class="cell percent">{{ $users_data[2]['wins_percent'] }} %</div>
								<div class="cell ranking">{{ $users_data[2]['rating'] }}</div>
							</div>
							@endif
						</div>
						@foreach($leagues as $league_iter => $league_data)
							<div class="ranking-page-table-head-container" data-league="{{ $league_data->slug }}"></div>
						@endforeach
					</div>

					<div class="ranking-page-table-content">
						<ul>
							<li class="pseudo"></li>
							<li class="active" data-league="all">

							@if(isset($users_data[3]))
								@for($i=3; $i < count($users_data); $i++)
								<div class="ranking-page-table-row cfix @if($user['login'] == $users_data[$i]['login']){{ 'active' }}@endif">
									<div class="cell place">{{ $users_data[$i]['position'] }}</div>
									<div class="cell name">{{ $users_data[$i]['login'] }}</div>
									<div class="cell battles">{{ $users_data[$i]['games'] }}</div>
									<div class="cell percent">{{ $users_data[$i]['wins_percent'] }} %</div>
									<div class="cell ranking">{{ $users_data[$i]['rating'] }}</div>
								</div>
								@endfor
							@endif
							</li>
							@foreach($leagues as $league_iter => $league_data)
								<li data-league="{{ $league_data->slug }}"></li>
							@endforeach
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif

@stop