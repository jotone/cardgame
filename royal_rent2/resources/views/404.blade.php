@extends('layouts.default')
@section('content')
	<div class="main">
		<!-- add partials here -->
		<section class="not-found">
			<div class="mbox">
				<div class="not-found-wrap">
					<h1>404</h1>
					<div class="not-found-right">
						<h2>Оооой!</h2>
						<p>Извините, такой страницы не существует.</p>
					</div>
				</div>
				<a class="el-button" href="{{ route('home') }}">Вернуться на главную страницу</a>
			</div>
		</section>
	</div>
@stop