@extends('layouts.default')
@section('content')
	<div class="main">
		<!-- add partials here -->
		<section class="romantic-header" style="background: rgba(0,0,0,0) url({{URL::asset($meta['images'][0]['img'])}}) no-repeat fixed center center /cover;">
			<h1 class="header-title header-title-white">
				{{$meta['title']}}<br>
				@if( (isset($build_exursion)) && ($build_exursion == true) )
					по {!! $defaults['current_city']['data']['string_0']['value'] !!}
				@else
					в {!! $defaults['current_city']['data']['string_1']['value'] !!}
				@endif
			</h1>
			<a class="mouse" href="#">
				<span class="dot"></span>
			</a>
		</section>
		<div class="mbox">
			<ul class="el-breadcrumbs">
				<li><a href="/">Главная</a></li>
				<li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
			</ul>
		</div>
		<div class="excursions js-tab">

			<div class="mbox">
				<div class="event-wrap">
					<div class="event-left">
						<h2>{{ $content['string_0']['value'] }}</h2>
						{!! $content['fulltext_0']['value'] !!}
					</div>
					<div class="event-right">
						<div class="event-pic">
							<img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt="">
						</div>
					</div>
				</div>
			</div>

			<div class="tabs-wrap">
				<div class="mbox">
					<ul class="el-tabs">
						@if( (isset($build_exursion)) && ($build_exursion == true) )
							<li class="tab">Все экскурсии</li>
							<li class="tab">Популярные</li>
						@else
							<li class="tab">Все свидания</li>
						@endif

						@foreach($excursion_types as $category)
							<li class="tab">{{ $category['title'] }}</li>
						@endforeach
					</ul>
					<div class="tab-content">
						<div class="tab-item filter-tab-item all">

						@foreach($excursions as $article)
							<?php
							$excursion_tab = '';
							foreach($article['data']['category_0'] as $cat_data){
								$excursion_tab .= ' '.$cat_data['slug'];
							}
							?>
							<div class="excursion-item{{$excursion_tab}}">
								<div class="exc-text">
									<div class="category">
										@foreach($article['data']['category_0'] as $cat_data)
											{{ $cat_data['title'] }}<br>
										@endforeach
									</div>
									<div class="info">
										<span class="time"><img src="{{ URL::asset('img/time-one.png') }}" alt=""> {{ $article['data']['string_1']['value'] }} часа</span>
										<span class="time"><img src="{{ URL::asset('img/cost.png') }}" alt=""> от {{ number_format($article['data']['string_0']['value'],0,',',' ') }} руб.</span>
									</div>
									<div class="info">
										<div class="info-title">{{ $article['title'] }}</div>
									</div>
									<div class="info">
										<div class="info-desc">{!! $article['description'] !!}</div>
									</div>
									<div class="info-details">
										<a href="{{ URL::asset($parent_slug.'/'.$article['slug']) }}">Узнать подробнее</a>
									</div>
								</div>
								<div class="exc-photo">
									@if(!empty($article['img_url']))
										<img src="{{ $article['img_url'] }}" alt="">
									@endif
								</div>
							</div>
						@endforeach
						</div>

						<div class="tab-item filter-tab-item" data-attr="popular"></div>
						@foreach($excursion_types as $category)
							<div class="tab-item filter-tab-item" data-attr="{{ $category['slug'] }}"></div>
						@endforeach
					</div>
				</div>
			</div>
		</div>

		@if( (!empty($content['string_1']['value'])) && (!empty($content['fulltext_1']['value'])) )
        <section class="seo-block">
            <div class="mbox">
                <div class="seo-wrap">
                    <div class="seo-pic">
                        @foreach($content['img_slider_1']['value'] as $slide)
                            <img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
                        @endforeach
                    </div>
                    <div class="seo-info">
                        <h2>{{ $content['string_1']['value'] }}</h2>
                        {!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_1']['value'])) !!}
                    </div>
                </div>
            </div>
        </section>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop