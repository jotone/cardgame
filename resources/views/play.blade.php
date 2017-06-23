@extends('layouts.game')
@section('content')
<div class="wrap-play disable-select">
	<div class="field-battle">
		<!-- Поле битвы -->
		<div class="convert-battle-front">
			<!-- Колода и отбой противника -->
			<div class="convert-left-info">
				<div class="cards-bet cards-oponent">
					<ul id="card-give-more-oponent">
						<!-- Колода противника -->
						<li data-field="deck">
							@if( (!empty($enemy)) && ($enemy['deck_counts']['deck'] > 0))
								<div class="card-init" @if(!empty($enemy) && (!empty($enemy['fraction_data']['card_img']))) style="background-image: url({{ URL::asset('/img/fractions_images/'.$enemy['fraction_data']['card_img']) }}) !important;" @endif>
									<div class="card-otboy-counter deck">
										<div class="counter">{{ $enemy['deck_counts']['deck'] }}</div>
									</div>
								</div>
							@else
								<div class="nothinh-for-swap"></div>
							@endif
						</li>
						<!-- Отбой противника -->
						<li data-field="discard">
							@if( (!empty($enemy)) && ($enemy['deck_counts']['discard'] > 0))
								<div class="card-init" @if(!empty($enemy) && (!empty($enemy['fraction_data']['card_img']))) style="background-image: url({{ URL::asset('/img/fractions_images/'.$enemy['fraction_data']['card_img']) }}) !important;" @endif>
									<div class="card-otboy-counter deck">
										<div class="counter">{{ $enemy['deck_counts']['discard'] }}</div>
									</div>
								</div>
							@else
								<div class="nothinh-for-swap"></div>
							@endif
						</li>
					</ul>
				</div>
			</div>
			<!--END OF Колода и отбой противника -->
			<!-- Счетчик раундов -->
			<div class="rounds-counter-wrapper">
				<div class="rounds-counter-container">
					<div class="rounds-counts user">
						<div class="rounds-counts-count">
							{{ $ally['wins_count'] }}
						</div>
						<div class="rounds-counts-title">
							{{ $ally['login'] }}
						</div>
					</div>
					<div class="vs">vs</div>
					<div class="rounds-counts oponent">
						<div class="rounds-counts-count">
							@if(!empty($enemy)){{ $enemy['wins_count'] }}@endif
						</div>
						<div class="rounds-counts-title">
							@if(!empty($enemy)){{ $enemy['login'] }}@endif
						</div>
					</div>
				</div>
			</div>
			<!-- END OF Счетчик раундов -->
			<!-- Поле противника -->
			<div class="convert-cards oponent">
				<div class="convert-card-box">
					<!-- Сверхдальние Юниты противника -->
					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="superRange">
								<div class="image-inside-line">
									<!-- Спецкарты -->
								</div>
								<!-- Поле размещения сверхдальних карт -->
								<div class="inputer-field-super-renge fields-for-cards-wrap">
									<div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого сверхдальнего ряда --></div>
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список сверхдальних карт-->
								</div>
							<!-- END OF Поле размещения сверхдальних карт -->
							</div>
						</div>
						<div class="field-for-sum"><!-- Сумарная сила воинов в сверхдальнем ряду --></div>
					</div>
					<!-- END OF Сверхдальние Юниты противника -->

					<!-- Дальние Юниты противника -->
					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="range">
								<div class="image-inside-line">
									<!-- Спецкарты -->
								</div>
								<!-- Поле размещения дальних карт -->
								<div class="inputer-field-range fields-for-cards-wrap">
									<div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого дальнего ряда --></div>
									<!-- Список дальних карт-->
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список дальних карт-->
								</div>
							<!-- END OF Поле размещения дальних карт -->
							</div>
						</div>
						<div class="field-for-sum"><!-- Сумарная сила воинов в дальнем ряду --></div>
					</div>
					<!-- END OF Дальние Юниты противника -->

					<!-- Ближние Юниты противника -->
					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="meele">
								<div class="image-inside-line">
									<!-- Спецкарты -->
								</div>
								<div class="inputer-field-meele fields-for-cards-wrap">
									<div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
									<!-- Список ближних карт-->
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список ближних карт-->
								</div>
							</div>
						</div>
						<div class="field-for-sum"><!-- Сумарная сила воинов в ближнем ряду --></div>
					</div>
					<!-- END OF Ближние Юниты противника -->
				</div>
			</div>
			<!--END OF Поле противника -->

			<div class="mezdyline"></div>

			<!-- Поле пользователя -->
			<div class="convert-cards user">
				<div class="convert-card-box">
					<!-- Ближние Юниты пользователя -->
					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="meele">
								<div class="image-inside-line">
									<!-- Спецкарты -->
								</div><!-- Место для спецкарты -->
								<div class="inputer-field-meele fields-for-cards-wrap">
									<div class="bg-img-meele fields-for-cards-img"></div>
									<!-- Список ближних карт-->
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список ближних карт-->
								</div>
							</div>
						</div>
						<div class="field-for-sum"><!-- Сила воинов в ближнем ряду--></div>
					</div>
					<!-- END OF Ближние Юниты пользователя -->

					<!-- Дальние Юниты пользователя -->

					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="range">
								<div class="image-inside-line">
									<!-- Спецкарты -->
								</div><!-- Место для спецкарты -->
								<div class="inputer-field-range fields-for-cards-wrap">
									<div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
									<!-- Список дальних карт-->
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список дальних карт-->
								</div>
							</div>
						</div>
						<div class="field-for-sum"></div>
					</div>
					<!-- END OF Дальние Юниты пользователя -->

					<!-- Сверхдальние юниты пользователя -->
					<div class="convert-stuff">
						<div class="convert-one-field">
							<div class="field-for-cards" id="superRange">
								<div class="image-inside-line">
									<!-- Место для спецкарты -->
								</div>
								<div class="inputer-field-super-renge fields-for-cards-wrap">
									<div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
									<!-- Список сверхдальних карт-->
									<ul class="cards-row-wrap">

									</ul>
									<!-- END OF Список сверхдальнихдальних карт-->
								</div>
							</div>
						</div>
						<div class="field-for-sum"></div>
					</div>
					<!-- END OF Сверхдальние юниты пользователя -->
				</div>
			</div>
			<!-- END OF Поле пользователя -->
			<div class="convert-left-info">
				<div class="cards-bet cards-main">
					<!-- Колода и отбой игрока-->
					<ul id="card-give-more-user" data-user="">
						<li data-field="deck">
							@if($ally['deck_counts']['deck'] > 0)
								<div class="card-my-init cards-take-more" @if(!empty($ally['fraction_data']['card_img'])) style="background-image: url({{ URL::asset('/img/fractions_images/'.$ally['fraction_data']['card_img']) }}) !important;" @endif>
									<!-- Количество карт в колоде -->
									<div class="card-take-more-counter deck">
										<div class="counter">{{ $ally['deck_counts']['deck'] }}</div>
									</div>
									<!--END OF Количество карт в колоде -->
								</div>
							@else
								<div class="nothinh-for-swap"></div>
							@endif
						</li>
						<li data-field="discard">
							@if($ally['deck_counts']['discard'] > 0)
								<div class="card-my-init cards-take-more" @if(!empty($ally['fraction_data']['card_img'])) style="background-image: url({{ URL::asset('/img/fractions_images/'.$ally['fraction_data']['card_img']) }}) !important;" @endif>
									<!-- Количество карт в отбое -->
									<div class="card-take-more-counter deck">
										<div class="counter">{{ $ally['deck_counts']['discard'] }}</div>
									</div>
									<!--END OF Количество карт в отбое -->
								</div>
							@else
								<div class="nothinh-for-swap"></div>
							@endif
						</li>
					</ul>
					<!--END OF Колода и отбой игрока-->
				</div>
			</div>
			<div class="user-card-stash">
				<!-- Карты руки пользователя -->
				<ul id="sortableUserCards" class="user-hand-cards-wrap cfix">

				</ul>
				<!-- END OF Карты руки пользователя -->
			</div>
			<div class="buttons-block-play cfix pass">
				<button class="button-push" name="userPassed">
					<div class="button-pass"> <p> ПАС </p></div>
				</button>
			</div>
		</div>
		<!-- END OF Поле битвы -->
	</div>

	<!-- Правый сайдбар -->
	<div class="convert-right-info">
		<div class="block-with-exit">
			<div class="buttons-block-play">
				<button class="button-push" name="userGiveUpRound">
					<div class="button-giveup"> <p> СДАТЬСЯ </p></div>
				</button>
			</div>
		</div>
		<div class="oponent-describer">

			<div class="useless-card">
				<div class="inside-for-some-block" style="">
					<ul class="magic-effects-wrap" data-player="">
						<!-- Активная магия -->
					</ul>
				</div>
			</div>

			<!-- Данные попротивника -->
			<div class="stash-about" >
				<div class="power-element">
					<div class="power-text power-text-oponent"><!-- Сумарная сила воинов во всех рядах противника --></div>
				</div>
				<div class="oponent-discribe">

					<div class="image-oponent-ork">

					</div><!-- Аватар игрока -->

					<!-- Количество выиграных раундов (скорее всего) n из 3х -->
					<div class="circle-status" data-pct="25">
						<svg id="svg" width='140px'  viewPort="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
							<filter id="MyFilter" filterUnits="userSpaceOnUse" x="0" y="0" width="200" height="200">
								<feGaussianBlur in="SourceAlpha" stdDeviation="4" result="blur"/>
								<feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
								<feSpecularLighting in="blur" surfaceScale="5" specularConstant=".75" specularExponent="20" lighting-color="#bbbbbb" result="specOut">
									<fePointLight x="-5000" y="-10000" z="20000"/>
								</feSpecularLighting>
								<feComposite in="specOut" in2="SourceAlpha" operator="in" result="specOut"/>
								<feComposite in="SourceGraphic" in2="specOut" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litPaint"/>
								<feMerge>
									<feMergeNode in="offsetBlur"/>
									<feMergeNode in="litPaint"/>
								</feMerge>
							</filter>
							<circle filter="url(#MyFilter)" id="bar-oponent" r="65" cx="71" cy="71" fill="transparent" stroke-dasharray="409" stroke-dashoffset="100px" stroke-linecap="round"></circle>
						</svg>
					</div>

					<div class="naming-oponent">
						<div class="name"><!-- Имя противника --></div>
						<div class="rasa">

						<!-- Колода противника-->
						</div>
					</div>
				</div>

				<div class="oponent-stats">
					<div class="stats-power">
						<div class="pover-greencard">
							<img src="{{ URL::asset('images/greencard.png') }}" alt="">
							<div class="greencard-num"></div>
						</div>
					</div>
					<div class="stats-shit"></div>
					<div class="stats-energy">
					<!-- Количество Энергии противника -->
					</div>
				</div>
			</div>
		</div>

		<div class="mezhdyblock">
			<div class="bor-beutifull-box">
				<ul id="sortable-cards-field-more" class="can-i-use-useless sort">
				</ul>
			</div>
		</div>

		<!-- Данные пользователя -->
		<div class="user-describer" id="">
			<div class="stash-about">
				<div class="power-element">
					<div class="power-text  power-text-user"><!-- Сумарная сила воинов во всех рядах противника --></div>
				</div>
				<div class="oponent-discribe">
					<div class="image-oponent-ork"></div><!-- Аватар игрока -->
					<div class="circle-status">
						<svg id="svg" width='140px'  viewPort="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
							<filter id="MyFilter" filterUnits="userSpaceOnUse" x="0" y="0" width="200" height="200">
								<feGaussianBlur in="SourceAlpha" stdDeviation="4" result="blur"/>
								<feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
								<feSpecularLighting in="blur" surfaceScale="5" specularConstant=".75" specularExponent="20" lighting-color="#bbbbbb" result="specOut">
									<fePointLight x="-5000" y="-10000" z="20000"/>
								</feSpecularLighting>
								<feComposite in="specOut" in2="SourceAlpha" operator="in" result="specOut"/>
								<feComposite in="SourceGraphic" in2="specOut" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litPaint"/>
								<feMerge>
									<feMergeNode in="offsetBlur"/>
									<feMergeNode in="litPaint"/>
								</feMerge>
							</filter>
							<circle filter="url(#MyFilter)" id="bar-user" r="65" cx="71" cy="71" fill="transparent" stroke-dasharray="409" stroke-dashoffset="100px" stroke-linecap="round"></circle>
						</svg>
					</div>

					<div class="naming-user">
						<div class="name"><!-- Имя игрока --></div>
						<div class="rasa"><!-- Колода игрока --></div>
					</div>

				</div>
				<div class="user-stats">
					<div class="stats-power">
						<div class="pover-greencard">
							<img src="{{ URL::asset('images/greencard.png') }}" alt="">
							<div class="greencard-num"></div>
						</div>
					</div>
					<div class="stats-shit"></div>
					<div class="stats-energy"><!-- Количество Энергии игрока --></div>
				</div>
			</div>
			<div class="useless-card">
				<div class="inside-for-some-block">
					<ul class="magic-effects-wrap" data-player="">
					</ul>
				</div>
			</div>
		</div>
		<div class="info-block-with-timer">
			<div class="timer-for-play cfix">
				<div class="title-timer"><span>ход противника:</span></div>
				<div class="timer-tic-tac-convert">
					<div class="tic-tac">
						<div class="tic-tac-wrap">
							<span class="tic" data-time="minute">00</span>
							<span>:</span>
							<span class="tac" data-time="seconds">00</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
