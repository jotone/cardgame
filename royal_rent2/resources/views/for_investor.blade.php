@extends('layouts.default')
@section('content')
<!-- MAIN -->
<div class="main">
	<section class="investors-block">
		<div class="investor-wrap">
			<section>
				<h2>{{ $content['string_0']['value'] }}</h2>
				<div class="undrheading">
					{!! $content['fulltext_0']['value'] !!}
				</div>
				<div class="investors-image-wrap" style="background: url({{ URL::asset($content['img_slider_0']['value'][0]['img']) }}) no-repeat center center /contain;">
					<a class="mouse" href="#">
						<span class="dot invisible"></span>
					</a>
				</div>
			</section>
			<div class="investment-form">
				<div class="investment-heading">
					<h4>{{ $content['string_1']['value'] }}</h4>
				</div>
				<form action="" class="investment2">
					<label>
						<p>Введите сумму инвестиций, руб.</p>
						<input type="number" name="invest-total" min="1" class="investor-input">
					</label>
					<div class="total-income">
						<p>Доходность составляет: <span>{{ $content['number_0']['value'] }}</span>%</p>
					</div>
					<div class="divaded-income">
						<div class="divaded-income-item ">
							<img src="{{ URL::asset('img/time_inv.png') }}">
							<div class="daily-income">
								<div class="number">1 000 000 руб.</div>
								<p>В сутки</p>
							</div>
						</div>
						<div class="divaded-income-item ">
							<img src="{{ URL::asset('img/time_inv.png') }}">
							<div class="weekly-income">
								<div class="number">1 000 000 руб.</div>
								<p>В месяц</p>
							</div>
						</div>
						<div class="divaded-income-item ">
							<img src="{{ URL::asset('img/time_inv.png') }}">
							<div class="yearly-income">
								<div class="number">1 000 000 руб.</div>
								<p>В год</p>
							</div>
						</div>
					</div>
					<div class="button-wrapper">
						<a class="el-button fancybox-form investor-order" href="#call-popup">Стать инвестором</a>
					</div>
				</form>
			</div>
			<div class="income-calculator">
				<div class="income-calculator-heading">
					<h4>{{ $content['string_2']['value'] }}</h4>
				</div>
				<div class="input-range-wrap">
					<form action="ajax.php" class="investment">
						<input type="text" name="range-calc" class="range-calc">
						<input type="" name="range-calc-spinner" id="range-calc-spinner" value="0">
						<div id="range-calc">
							<div id="custom-handle" class="ui-slider-handle"></div>
							<div class="upper-handle"></div>
						</div>
						<div class="number-visual">
							<ul>
							@foreach($content['table_0']['value']['body'] as $item)
								<li data-price="{{$item[0]}}" data-percent="{{$item[1]}}">{{$item[0]}}p</li>
							@endforeach
							</ul>
						</div>

						<div class="income-calc-result">
							<div class="left-part">
								<div class="current-heding">
									<h5>Инвестиции</h5>
								</div>
								<div class="line">
									<div class="dimension">Стоимость:</div>
									<div class="dimension-value" data-type="cost"><span>{{ number_format($content['table_0']['value']['body'][0][0],0,'',' ') }}</span> р.</div>
								</div>
								<div class="line">
									<div class="dimension">Каско на год:</div>
									<div class="dimension-value" data-type="casco"><span>{{ number_format($content['number_1']['value'],0,'',' ') }}</span> р.</div>
								</div>
								<div class="line">
									<div class="dimension">Осаго на год:</div>
									<div class="dimension-value" data-type="osago"><span>{{ number_format($content['number_2']['value'],0,'',' ') }}</span> р.</div>
								</div>
								<div class="line">
									<div class="dimension">Итого:</div>
									<?php
									$total = $content['table_0']['value']['body'][0][0]+ $content['number_2']['value'] + $content['number_1']['value'];
									$income_total = $total*0.01*$content['table_0']['value']['body'][0][1] + $total;
									?>
									<div class="dimension-value" data-type="total"><span>{{ number_format($total,0,'',' ') }}</span> р.</div>
								</div>
							</div>
							<div class="midle-part">
								<div class="midle-image-wrap">
									@foreach($content['img_slider_1']['value'] as $image)
										<img src="{{ URL::asset($image['img']) }}" data-img-cost="{{ $image['alt'] }}">
									@endforeach
								</div>
							</div>
							<div class="right-part">
								<div class="current-heding">
									<h5>Доходы</h5>
								</div>
								<div class="line">
									<div class="dimension">Доход в сутки:</div>
									<div class="dimension-value" data-type="income_day"><span>{{ number_format(ceil($income_total / 365),0,'',' ') }}</span> р.</div>
								</div>
								<div class="line">
									<div class="dimension">Доход в месяц:</div>
									<div class="dimension-value" data-type="income_month"><span>{{ number_format(ceil($income_total / 12),0,'',' ') }}</span> р.</div>
								</div>
								<div class="line">
									<div class="dimension">Доход за год:</div>
									<div class="dimension-value" data-type="income_year"><span>{{ number_format(ceil($income_total),0,'',' ') }}</span> р.</div>
								</div>
								<div class="line"><div class="dimension">Итого:</div>
									<div class="dimension-value" data-type="income_total"><span>{{ number_format(ceil($income_total),0,'',' ') }}</span> р.</div>
								</div>
							</div>
						</div>

						<div class="button-wrapper">
							<a class="el-button fancybox-form investor-calc-order" href="#call-popup">Стать инвестором</a>
						</div>
					</form>
				</div>
			</div>

			@if( (!empty($content['string_3']['value'])) && (!empty($content['fulltext_1']['value'])) )
				<section class="seo-block">
					<div class="mbox">
						<div class="seo-wrap">
							<div class="seo-pic">
								@foreach($content['img_slider_2']['value'] as $slide)
									<img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
								@endforeach
							</div>
							<div class="seo-info">
								<h2>{{ $content['string_3']['value'] }}</h2>
								{!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_1']['value'])) !!}
							</div>
						</div>
					</div>
				</section>
			@endif
			@include('layouts.attachment')
		</div>
	</section>
</div>
<!-- /MAIN -->
@stop
