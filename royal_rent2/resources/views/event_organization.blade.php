@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		<section class="event-header"style="background: rgba(0,0,0,0) url({{URL::asset($meta['img_url'][0]['img'])}}) no-repeat fixed center center /cover;">
			<h1 class="header-title header-title-white">{{$meta['title']}}<br>в {!! $defaults['current_city']['data']['string_1']['value'] !!}</h1>
			<a class="mouse" href="#">
				<span class="dot"></span>
			</a>
		</section>
		<div class="el-breadcrumbs">
			<div class="mbox">
				<ul>
					<li><a href="/">Главная</a></li>
					<li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
				</ul>
			</div>
		</div>
		<div class="event js-tab">
			<div class="mbox">
				<div class="el-info">
					<div class="info-left">
						<h2>{{ $content['string_0']['value'] }}</h2>
						<div class="el-info-text">
							{!! $content['fulltext_0']['value'] !!}
						</div>
					</div>
					<div class="info-right">
						<div class="info-pic">
							<img src="{{ URL::asset($content['img_slider_0']['value'][0]['img']) }}" alt="Invite">
						</div>
					</div>
				</div>
			</div>

			<div class="tabs-wrap">
				<div class="mbox">
					<ul class="el-tabs">
						@foreach($events as $item)
							<li class="tab">{{ $item['title'] }}</li>
						@endforeach
					</ul>
					<div class="tab-content">
						@foreach($events as $item)
							<div class="tab-item">
								<div class="event-list-wrap">
								{!! $item['text'] !!}
								</div>
								@if(!empty($item['data']['table_0']['value']['body']))
									<div class="el-table">
										<div class="link">
											<p>Таблица стоимости услуг</p>
											<span class="add">+</span>
										</div>
										<div class="wrap-table">
											<div class="table-header">
												<?php $n = count($item['data']['table_0']['value']['head']);?>
												@foreach($item['data']['table_0']['value']['head'] as $header_item)
													<div class="column">{{$header_item}}</div>
												@endforeach
											</div>
											<div class="table-body">
												@foreach($item['data']['table_0']['value']['body'] as $row)
													<div class="table-row">
													@foreach($row as $col)
														<div class="table-col">{{ $col }}</div>
													@endforeach
													</div>
												@endforeach
											</div>
										</div>
									</div>
								@endif
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop