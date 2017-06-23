@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>

@if($user)

	@include('layouts.top')

	<div class="main disable-select">
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
					<div class="content-card-top cfix nice-select-items-wrap">
						<button class="button-buy-next clear"  onclick="clearDeck()">
							<span class="form-button-hover"></span>
							<span class="form-button-text">Очистить колоду</span>
						</button>
						<div class="content-card-left">
							<div class="content-card-description">
								<div class="content-card-description-wrap">
									Карты в колоде
								</div>
							</div>
						</div>
						<div class="content-card-center market-page">
							<div class="selection-rase">
								<div class="selection-rase-wrap">
									<div class="selection-rase-img">
										<div class="selection-rase-img-wrap">
											<div class="select-rase-img active">
												<img src="{{ URL::asset('img/fractions_images/'.$user_fraction->img_url) }}" alt="">
											</div>
										</div>
									</div>
									<select>
									@foreach($fractions as $fraction){
										<?php $selected = ($user['last_user_deck'] == $fraction['slug'])? 'selected="selected"': ''; ?>
										<option value="{{ $fraction['slug'] }}" {{ $selected }}>{{ $fraction['title'] }}</option>
									@endforeach
									</select>
								</div>
							</div>
						</div>
						<div class="content-card-right">
							<div class="content-card-description">
								<div class="content-card-description-wrap">
									Доступные карты
								</div>
							</div>
						</div>
					</div>
					<div class="content-card-field-wrap cfix">

						<div class="content-card-field cfix">
							<div class="content-card-left">
								<div class="content-card-cards scroll-pane">
									<div class="content-card-cards-wrap cfix">
										<ul id="sortableOne" class="connected-sortable">

										</ul>
									</div>
								</div>
							</div>
							<div class="content-card-field-center">
								{{ Form::open(['class' => 'content-card-form', 'method' => 'POST']) }}
									<div class="content-card-field-center-wrap">

										<div class="content-card-center-block">
											<div class="content-card-center-img-wrap">

												<img data-src="{{ URL::asset('img/fractions_images/') }}" alt="" />

											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Всего карт в колоде</div>
												<div class="content-card-center-description-value deck-card-sum"></div>
											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Карты воинов</div>
												<div class="content-card-center-description-value deck-warriors">
													<span class="current-value">0</span> / <span class="min-value">min (<?= $deck['minWarriorQuantity'] ?>)</span>
												</div>
											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Специальные</div>
												<div class="content-card-center-description-value deck-special">
													<span class="current-value">0</span> / <span class="min-value"><?= $deck['specialQuantity'] ?></span>
												</div>
											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Сила колоды</div>
												<div class="content-card-center-description-value deck-cards-power">0</div>
											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Лига</div>
												<div class="content-card-center-description-value deck-league">0</div>
											</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-description-key">Карты лидеров</div>
												<div class="content-card-center-description-value deck-liders">
													<span class="current-value">0</span> / <span class="min-value"><?= $deck['leaderQuantity'] ?></span>
												</div>
											</div>
											<div class="content-card-center-description-key">Фильтр</div>
											<div class="content-card-center-description-block">
												<div class="content-card-center-checkbox filter-decks">
													<label>
														<input type="checkbox" name="content-card-center-checkbox" checked="checked" data-card-type="special">
														<span class="card-center-checkbox"></span>
														<span>специальные</span>
													</label>
													<label>
														<input type="checkbox" name="content-card-center-checkbox" checked="checked" data-card-type="neutral">
														<span class="card-center-checkbox"></span>
														<span>нейтральные</span>
													</label>
													<label>
														<input type="checkbox" name="content-card-center-checkbox" checked="checked" data-card-type="fraction">
														<span class="card-center-checkbox"></span>
														<span>фракционные</span>
													</label>
												</div>
											</div>
										</div>
									</div>
								 {{ Form::close() }}
							</div>
							<div class="content-card-right">
								<div class="content-card-cards scroll-pane">
									<div class="content-card-cards-wrap cfix">
										<ul id="sortableTwo" class="connected-sortable">

										</ul>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>

		</div>
	</div>

@endif

@stop