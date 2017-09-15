<?php
$current_slug = Request::path();
$services = [];
foreach($defaults['menu_services'] as $menu_services){
	foreach ($menu_services['items'] as $service){
		if($service['slug'] != $current_slug){
			$services[] = [
				'title' => $service['title'],
				'slug' => $service['slug']
			];
		}
	}
}
shuffle($services);
$services = array_slice($services,0,5);
?>

<section class="subscribe-block">
	<div class="mbox">
		<div class="subscribe-wrap">
			<div class="subscribe-pic">
				<img src="{{ URL::asset('img/mail.png') }}" alt="Mail">
			</div>
			<div class="subscribe-right">
				<h2>Подпишитесь и экономьте узнавая о всех скидках и акциях первым!</h2>
				<form action="{{ route('subscribe') }}" method="POST" class="subscribe-form">
					<input name="_token" type="hidden" value="{{ csrf_token() }}">
					<div class="form-field">
						<input type="email" name="email" placeholder="Введите свой e-mail" required />
					</div>
					<button class="el-button" type="supmit">Подписаться</button>
				</form>
			</div>
		</div>
	</div>
</section>
<section class="el-services">
	<div class="services-wrap">
		<ul>
			<li><a href="#" class="open_our-services">Показать все наши услуги</a></li>
			@foreach($services as $service)
				<li><a href="/{{ $service['slug'] }}">{{ $service['title'] }}</a></li>
			@endforeach
		</ul>
	</div>
</section>