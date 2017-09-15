@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					<li class="active"><a href="{{ $meta['slug'] }}">{{ $meta['title'] }}</a></li>
				</ul>
			</div>
		</div>
		<section class="wrap-revievs" style="background:url({{ URL::asset($meta['img_url'][0]['img']) }}) no-repeat center bottom fixed">
			<div class="mbox revievs">
				<div class="title-revievs">{{ $meta['title'] }}</div>

			</div>
			<a class="el-button fancybox-form lower-button" href="#add-review">Оставить свой отзыв</a>
			<a class="mouse" href="#">
				<span class="dot"></span>
			</a>
		</section>
		@foreach($comments_list as $iter => $comment)
			<div class="reviev-item @if($iter % 2 == 0) reviev-item-grey @endif">
				<div class="mbox">
					<div class="stars">
					<?php
					$dec_rating = 5 - $comment['rating'];
					?>
					@for($i =0; $i<$comment['rating']; $i++)
						<span><img src="{{ URL::asset('img/star-icon.png') }}" alt=""></span>
					@endfor
					@for($i =0; $i<$dec_rating; $i++)
						<span><img src="{{ URL::asset('img/star.png') }}" alt=""></span>
					@endfor
					</div>
					<div class="reviev-text">
						<div class="wrap-name">
							<div class="name">{{ $comment['user_name'] }}</div>
							<div class="city">{{ $comment['city'] }}</div>
						</div>
						<div class="wrap-opinion">
							{{ $comment['text'] }}
						</div>
					</div>
					<div class="reviev-text">
						<div class="wrap-name">
							<div class="city">{{ $comment['date'] }}</div>
						</div>
						<div class="wrap-opinion"><span>Арендовали:</span> <a href="nonedriver_card.html">{{ $comment['car'] }} — {{ $comment['has_driver'] }}</a></div>
					</div>
				</div>
			</div>
		@endforeach
		@if($paginate > 1)
		<div class="mbox wrap-numbers">
			<ul class="numbers">
			@for($i=1; $i<=$paginate; $i++)
				 <li><a href="{{ route('reviews') }}?page={{$i}}" @if($current_page == $i) class="active" @endif>{{ $i }}</a></li>
			@endfor
			</ul>
		</div>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop
