<div class="left-menu-wrap disable-select">
	<div class="left-menu-wrapping">
		<div class="left-menu">
			<div class="left-menu-naviagation">
				<div class="left-menu-naviagation-wrap">

					<div class="nav-item">
						<a href="#" id="start-game" class="start-search-game">
							<span class="nav-item-wrap">
								<span>Играть</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('rating-page') }}">
							<span class="nav-item-wrap">
								<span>Рейтинг игроков</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('deck-page') }}">
							<span class="nav-item-wrap">
								<span>Мои карты</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('market-page') }}">
							<span class="nav-item-wrap">
								<span>Магазин</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('magic-page') }}">
							<span class="nav-item-wrap">
								<span>Волшебство</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('user-settings-page') }}">
							<span class="nav-item-wrap">
								<span>Настройки</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a href="{{ route('training-page') }}">
							<span class="nav-item-wrap">
								<span>Обучение</span>
							</span>
						</a>
					</div>
					<div class="nav-item">
						<a data-href="{{ route('user-logout') }}" class="log_out_menu">
							<span class="nav-item-wrap">
								<span>Выход</span>
							</span>
						</a>
					</div>
				</div>
			</div>
			<div class="left-menu-bottom">
				<div class="left-menu-bottom-wrap">
					<div class="left-menu-img">
						<img src="{{ URL::asset('images/left-menu-img.png') }}" alt="">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>