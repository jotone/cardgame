@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if($user)
	@include('layouts.top')
	<div class="main">
		<div class="mbox">
			<div class="content-top-wrap disable-select">
				<div class="dragon-image cfix">
					<div class="dragon-middle">
						<img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
						<img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
					</div>
				</div>
				<div class="tabulate-image"></div>
			</div>

			@include('layouts.sidebar')

			<div class="content-wrap training-page">
				<div class="training-title">
					<h3>{!! $page_content->title !!}</h3>
				</div>
				<div class="ctext">
					{!! $page_content->text !!}
				</div>
			</div>
		</div>
	</div>
@endif

@stop