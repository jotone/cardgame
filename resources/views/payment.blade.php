@extends('layouts.default')
@section('content')

<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if($user)

	@include('layouts.top')

	<div class="main not-main settings-page-wrap">
		<div class="mbox">
			<div class="content-top-wrap">
				<div class="dragon-image cfix">
					<div class="dragon-middle">
						<img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
						<img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
					</div>
				</div>
				<div class="tabulate-image"></div>
			</div>

			@include('layouts.sidebar')

			<div class="content-wrap settings-page">
				<div class="content-card-wrap-main">
					<div class="form-wrap preloading-class">

						<div class="preloader" style="display: none;">
							<img src="{{ URL::asset('images/359.gif') }}" alt="">
						</div>

						<div class="form-wrap-main">
							<div class="form-title">ПОПОЛНЕНИЕ БАЛАНСА ЗОЛОТА</div>

							<div class="form-wrap-item">
								<div class="form-wrap-for-rows">
									<div class="form-wrap-row form_row">
										<div class="form-wrap-key">Количество покупаемого золота</div>
										<div class="form-wrap-value payment-title">{{ $pay_data['gold_amount'] }}</div>
									</div>
									<div class="form-wrap-row form_row">
										<div class="form-wrap-key">К оплате</div>
										<div class="form-wrap-value payment-title">{{ number_format($pay_data['money_amount'],2 , ',',' ') }} руб.</div>
									</div>
									<div class="form-wrap-row form_row">
										<div class="form-wrap-key">Способ оплаты</div>
										<div class="form-wrap-value payment-title">
										<?php
										switch($pay_data['type']){
											case 'PC': echo 'Яндекс Деньги'; break;
											case 'AC': echo 'Банковская карта'; break;
										}
										?>
										</div>
									</div>
									<form id="pay" name="pay" method="POST" action="https://money.yandex.ru/quickpay/confirm.xml" target="_blank" enctype="application/x-www-form-urlencoded">
										<input type="hidden" name="receiver" value="410013926813756">
										<input type="hidden" name="quickpay-form" value="shop">
										<input type="hidden" name="targets" value="Пополнение счета Dragonheart">
										<input type="hidden" name="sum" value="{{ number_format($pay_data['money_amount'],2 , '.','') }}">
										<input type="hidden" name="successURL" value="{{ route('ya-mo-succ') }}">
										<input type="hidden" name="label" value="{{ base64_encode($user['id'].'_'.$pay_data['id']) }}">
										<input type="hidden" name="paymentType" value="{{ $pay_data['type'] }}">
										<div class="form-wrap-row submit">
											<div class="form-wrap-value">
												<div class="form-wrap-input">
													<button class="form-button" type="submit">
														<span class="form-button-hover"></span>
														<span class="form-button-text">ОПЛАТИТЬ</span>
													</button>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif
@stop