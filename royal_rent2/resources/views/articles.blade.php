@extends('layouts.default')
@section('content')
	<!-- MAIN -->
	<div class="main">
		<!-- add partials here -->
		@if(!empty($first))
		<section class="news">
			<div class="news-header" @if(!empty($first['img_url']))style="background: url({{ URL::asset($first['img_url'][0]['img']) }}) no-repeat center bottom; background-size: cover; background-attachment: fixed;"@endif>
				<div class="mbox">
					<div class="news-header-left">
						<span>{!! $first['date'] !!} </span>
						<h2>{{ $first['title'] }}</h2>
						{!! $first['description'] !!}
						<a class="el-button" href="{{ URL::asset($link_to.'/'.$first['slug'] ) }}">Узнать подробнее</a>
					</div>
				</div>
			</div>
			<div class="mbox">
				<div class="news-content">
					<div class="news-row">
						@foreach($content as $new)
							<a href="{{ URL::asset($link_to.'/'.$new['slug'] ) }}" class="news-item">
								@if(!empty($new['img_url']))
								<div class="news-pic">
									<img src="{{ $new['img_url'][0]['img'] }}" alt="{{ $new['img_url'][0]['alt'] }}">
								</div>
								@endif
								<span>{!! $new['date'] !!}</span>
								<h3>{{ $new['title'] }}</h3>
								{!! $new['description'] !!}
							</a>
						@endforeach
					</div>
				</div>
				@if($paginate > 1)
				<div class="news-pagination">
					<ul>
						@for($i=0; $i<$paginate; $i++)
							<li><a href="{{ route('news') }}?page={{$i+1}}" @if($current_page == $i+1) class="active" @endif>{{ $i+1 }}</a></li>
						@endfor
					</ul>
				</div>
				@endif
			</div>
		</section>
		@endif
		@include('layouts.attachment')
	</div>
	<!-- /MAIN -->
@stop