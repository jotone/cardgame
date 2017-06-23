@extends('layouts.default')
@section('content')

<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if(isset($user))
	@include('layouts.top')

	<div class="main table-list-page disable-select">
		<div class="mbox">
			<div class="content-top-wrap disable-select">
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
					<div class="content-card-top">

						<div class="create-table">
							{{ Form::open(['route' => 'user-create-table', 'method' => 'POST']) }}
							<input name="league" type="hidden" value="{{ $league }}">
							<input name="deck_weight" type="hidden" value="{{ $deck_weight }}">
							<input name="players" type="hidden" value="2">

							<button class="button-buy-next" type="submit" onclick="showPreloader()">
								<span class="form-button-hover"></span>
								<span class="form-button-text">Создать Стол</span>
							</button>
							{{ Form::close() }}
						</div>
						<div class="league-info">
							<span>{{ Crypt::decrypt($league) }} лига</span>
						</div>
						<button class="button-buy-next reload"  onclick="location.reload(); showPreloader();">
							<span class="form-button-hover"></span>
							<span class="form-button-text">Обновить</span>
						</button>
					</div>

					<div class="market-cards-wrap">
						<div class="tables-list">
							@foreach($battles['back'] as $value)
								<div class="table-list-item">
									<div class="title">&nbsp;<!--Стол №@{{ $value['data']['id']}}--></div>
									<div class="content">
										<div class="table-place">
											<p><b>Мест:&nbsp;{{ $value['users_count']}}</b>/2</p>
										</div>
										<div class="table-info">
											<div class="row">
												<a class="play-game" href="/play/{{ $value['data']['id'] }}" id="{{ $value['data']['id'] }}">Вернуться за стол</a>
											</div>
										</div>
									</div>
								</div>
							@endforeach

							@foreach($battles['allow'] as $value)
								<div class="table-list-item">
									<div class="title">&nbsp;<!--Стол №@{{$value['data']['id']}}--></div>
									<div class="content">
										<div class="table-place">
											<p><b>Мест:&nbsp;{{ $value['users_count']}}</b>/2</p>
										</div>
										<div class="table-info">
											<div class="row">
												<a class="play-game" href="/play/{{ $value['data']['id'] }}" id="{{ $value['data']['id'] }}">Присоединиться</a>
											</div>
										</div>
									</div>
								</div>
							@endforeach

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif
@stop