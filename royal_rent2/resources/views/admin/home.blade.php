@extends('admin.layouts.default', [
	'start' => $start
])
@section('scripts')
	<script type="text/javascript" src="{{ URL::asset('js/admin_settings.js') }}"></script>
@stop
@section('content')
	<div class="main-block">
		<div class="aside-wrap">

		</div>
		<div class="center-wrap col_1">
			<div class="page-caption row-wrap">Инструкция</div>
			<div class="work-place-wrap">
				<ul class="info-list">
					<li><span>Настройки</span>
						<ul>
							<li><span>Пользователи и права</span>
								<ul>
									<li><span>Роли и права</span>
										<ul>
											<div class="info-container">
												<div class="info-text">
													<p>Страница предназначена для создания типов администраторов и ограничения доступа к внутренним страницам админки.</p>
													<p>Стандартными ролями являются: <ins>Главный администратор</ins>, с полным доступом ко всем сраницам, и <ins>Гость</ins>, как обычный пользователь сайта, без права к доступу в админку.</p>
													<p>При создании роли необходимо указать <ins>Название роли</ins>, её <ins>Псевдоним</ins> и указать какие страницы будут запрещены данной роли пользователя (не обязательно заполнять)</p>
													<p><strong>ВНИМАНИЕ!!!</strong> <ins>Псевдоним роли</ins> должен быть заполненым.</p>
												</div>
											</div>
										</ul>
									</li>
									<li><span>Пользователи</span>
										<ul>
											<div class="info-container">
												<div class="info-text">
													<p>Страница предназначена для редактирования данных пользователя и выдачи/снятия прав администратора или других ролей (см. раздел <strong>Роли и права</strong>)</p>
												</div>
											</div>
										</ul>
									</li>
								</ul>
							</li>
							<li><span>Основные настройки</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Страница предназначена для заполнения основных конактных данных сайта.</p>
											<p>Список e-mail'ов &ndash; непосредственно является списком адресатов e-mail рассылки и переписки</p>
											<p>	Социальные сети &ndash; список ссылок на страницы/группы в соц.сетях.
												Для добавления ссылки выбирите тип соц.сети в выпадающем меню и нажмите кнопку <strong>Добавить</strong>.
												Для удаления нажмите на крестик <strong>&times;</strong> рядом с полем ввода.
											</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Рассылки</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p title="Спасибо Капитан Очевиднось">Страница предназначена для создания e-mail рассылок.</p>
											<p>Сформированое письмо будет отправлено всем пользователям сайта.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Почта и переписка</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Страница предназначена для создания шаблонов писем.</p>
											<p>В текстах шаблонов писем встречаются подшаблоны автозамены выделеные последовательностью символов <strong>[%%]</strong>.</p>
											<p>Данные последовательности подставляют на свое место конкретные значения полей при отправке форм заявок и тп.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Админ. меню</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню админки.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Меню в шапке</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню в шапке сайта.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Навигация "Наши услуги"</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню <ins>Услуги</ins> и <ins>Юридическим лицам</ins>.</p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/our_services_img.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Навигация "Партнерам и инвесторам"</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню <ins>Партнерам и инвесторам</ins>.</p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/partners_img.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Навигация "О компании"</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню <ins>О компании</ins>.</p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/about_company_img.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Меню в футере</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для сортировки элементов меню подвала сайта.</p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/footer_img.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
						</ul>
					</li>
					<li><span>Страницы</span>
						<ul>
							<div class="info-container">
								<div class="info-text">
									<p>Данная страница предназначена для создания и редактирования страниц сайта.</p>
									<p>Создание шаблонов страниц происходит на странице <ins>Модули</ins></p>
								</div>
							</div>
						</ul>
						<ul>
							<li><span>Акции</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для создания и редактирования акций.</p>
											<p>Скидка на услуги указвается в процентном значении и привязывается к значению "Цена" транспорта.</p>
											<p>Также процентное значение скидки можно указать на прямую из карточки товара.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Новости</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для создания и редактирования новостей.</p>
											<p>Новости выводятся в порядке возрастания даты публикации. Самая свежая новость выводится в шапке страницы.</p>
											<p>Для превью новостей выбирайте изображения высокого разрешения.</p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/news_image.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Статьи</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данная страница предназначена для создания и редактирования статей. Схема создания и вывода статей аналогична новостям.</p>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Условия аренды</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Содержит контент горизонтального меню страницы <ins>Условия аренды</ins></p>
										</div>
										<div class="info-image">
											<img src="{{ URL::asset('/img/info/rent_terms_img.jpg') }}" alt="">
										</div>
									</div>
								</ul>
							</li>
							<li><span>Наши Клиенты</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Содержит список клиентов на странице <ins>О компании</ins></p>
											<div class="info-image">
												<img src="{{ URL::asset('/img/info/our_clients.jpg') }}" alt="">
											</div>
										</div>
									</div>
								</ul>
							</li>
							<li><span>Организация мероприятий</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Содержит контент горизонтального меню страницы <ins>Организация мероприятий</ins></p>
										</div>
										<div class="info-image">
											<img src="{{ URL::asset('/img/info/event_org_img.jpg') }}" alt="">
										</div>
									</div>
								</ul>
							</li>
							<li><span>Сотрудничество с банками</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Содержит список банков-партнеров на странице <ins>Аренда в кредит</ins></p>
										</div>
										<div class="info-image">
											<img src="{{ URL::asset('/img/info/bamks-Img.jpg') }}" alt="">
										</div>
									</div>
								</ul>
							</li>
							<li><span>Тарифы-Сортировка категорий</span>
								<ul>
									<div class="info-container">
										<div class="info-text">
											<p>Данный раздел предназначен для сортировки категорий на странице <ins>Тарифы</ins></p>
										</div>
									</div>
								</ul>
							</li>
						</ul>
					</li>
					<li><span>Модули</span>
						<ul>
							<div class="info-container">
								<div class="info-text">
									<p>Данный раздел предназначен для создания функциональных частей админки.</p>
									<p>Функционал позволяет создавать такие типы модулей</p>
									<dl>
										<dt>Акции:</dt>
										<dd>Модуль предназначен для создания функционала акций и привязки к ним необходимых данных.</dd>
										<dt>Категории:</dt>
										<dd>Создание категорий, рубрик, сортируемого контента и тп.</dd>
										<dt>Меню Сайта:</dt>
										<dd>Создание групп елементов меню.</dd>
										<dt>Статьи</dt>
										<dd>Создание записей и полноценных статей.</dd>
										<dt>Товары</dt>
										<dd>Создание товаров и зписей им подобных.</dd>
										<dt>Шаблоны страниц</dt>
										<dd>Создание представлений и шаблонов страниц для их наполнения в будущем.</dd>
									</dl>
								</div>
							</div>
						</ul>
					</li>
					<li><span>Транспорт</span>
						<ul>
							<li><span>Типы транспорта</span></li>
							<li><span>Марки и модели</span></li>
							<li><span>Тип трансмиссии</span></li>
							<li><span>Тип топливной системы</span></li>
							<li><span>Сроки оренды</span></li>
							<li><span>Дополнительные опции</span></li>
							<li><span>События</span></li>
							<li><span>Список Ограничьте вашу ответственность</span></li>
							<li><span>Список Покрытие повреждений</span></li>
							<li><span>Список Выезды за область</span></li>
						</ul>
					</li>
					<li><span>Экскурсии</span>
						<ul>
							<li><span>Виды экскурсий</span></li>
							<li><span>Тарифы экскурсий</span></li>
						</ul>
					</li>
					<li><span>Романтические встречи</span>
						<ul>
							<li><span>Виды романтических встреч</span></li>
							<li><span>Тарифы романтических встреч</span></li>
						</ul>
					</li>
					<li><span>Список городов</span></li>
				</ul>
			</div>
		</div>
	</div>
@stop