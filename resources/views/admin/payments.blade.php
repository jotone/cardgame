@extends('admin.layouts.default')
@section('content')

	<div class="main-central-wrap">
		<table class="data-table">
			<thead>
			<tr>
				<th>
					<span>Пользователь</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Получено денег (без учета комиссии)</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Кол-во золота до оплаты</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Получено золота</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Оплата по курсу</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Создан</span>
					<div class="tac">
						<a class="table-direction" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
				<th>
					<span>Дата оплаты</span>
					<div class="tac">
						<a class="table-direction active" href="#" data-direct="up">&#9650;</a>
						<a class="table-direction" href="#" data-direct="down">&#9660;</a>
					</div>
				</th>
			</tr>
			</thead>
			<tbody>
			@foreach($payments as $payment)
				<tr>
					<td><a href="{{ route('admin-user-edit-page', $payment->user_id) }}">{{ $payment->user_name }}</a></td>
					<td>{{ $payment->money_amount }} руб.</td>
					<td>{{ $payment->last_gold_status }}</td>
					<td>{{ $payment->gold_amount }}</td>
					<td>{{ $payment->last_exchange_status }} руб. за доллар</td>
					<td>{{ substr($payment->created_at,0,16) }}</td>
					<td>{{ substr($payment->updated_at,0,16) }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	@stop