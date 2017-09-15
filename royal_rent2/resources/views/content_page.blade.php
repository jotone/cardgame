@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main" data-refer="">
		<!-- add partials here -->
		<section class="el-header-bg" style="background: rgba(0,0,0,0) url({{URL::asset($meta['images'][0]['img'])}}) no-repeat fixed center center /cover;">
			<h1 class="header-title header-title-white">{{$meta['title']}}<br>в {!! $defaults['current_city']['data']['string_1']['value'] !!}</h1>
			<a class="mouse mouse-black" href="#">
				<span class="dot"></span>
			</a>
		</section>

		<div class="mbox">
			<ul class="el-breadcrumbs">
				<li><a href="/">Главная</a></li>
				<li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
			</ul>
		</div>
		<section class="photosession">
			<div class="mbox">
				<div class="el-info">
					<div class="info-left">
						<h2>{{ $content['string_0']['value'] }}</h2>
						<div class="el-info-text">
							{!! $content['fulltext_0']['value'] !!}
						</div>
						@if(isset($content['table_0']))
							<div class="el-table">
								<div class="link">
									<p>Таблица стоимости услуг</p>
									<span class="add">+</span>
								</div>
								<div class="wrap-table">
									<div class="table-header">
										@foreach($content['table_0']['value']['head'] as $item)
											<div class="column">{{ $item }}</div>
										@endforeach
									</div>
									<div class="table-body">
										@foreach($content['table_0']['value']['body'] as $row)
											<div class="table-row">
												@foreach($row as $item)
													<div class="table-col">{{$item}}</div>
												@endforeach
											</div>
										@endforeach
									</div>
								</div>
							</div>
						@endif
						@if ((isset($content['fieldset_0']['checkbox_0'])) && ($content['fieldset_0']['checkbox_0']['value'] == 1))
							<a class="el-button calc-button fancybox-form rent-cost" href="#call-popup">Расчитать стоимость аренды</a>
						@endif
						@if ((isset($content['fieldset_0']['checkbox_1'])) && ($content['fieldset_0']['checkbox_1']['value'] == 1))
							<a class="el-button calc-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
						@endif
					</div>
					<div class="info-right">
						@if(count($content['img_slider_0']['value']) > 1)
						<div class="long-term-rent-wrap">
							<div class="info-pic longtime-rent-pic">
								<img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt="">
							</div>
							@for($i=1; $i<count($content['img_slider_0']['value']); $i++)
								<div class="longtime-rent-item">
									<p>{{ $content['img_slider_0']['value'][$i]['alt'] }}</p>
									<div class="longtime-rent-icon">
										<img src="{{ URL::asset($content['img_slider_0']['value'][$i]['img']) }}" alt="">
									</div>
								</div>
							@endfor
						</div>
						@else
							<div class="info-pic photosession-pic">
								<img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt="">
							</div>
						@endif
					</div>
				</div>
				@if(isset($content['content_bottom_caption']))
					<div class="viphall">
						<h2>{{ $content['content_bottom_caption'][0]['content'] }}</h2>
					</div>
				@endif
			</div>
		</section>
		@include('layouts.auto_filter')

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
