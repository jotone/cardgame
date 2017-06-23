@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="userDataTable">
	<input name="_token" type="hidden" value="{{ csrf_token() }}">
	<input name="user_id" type="hidden" value="{{ $user->id }}">
	<fieldset>
		<legend>Основные данные</legend>

		<table class="edition" style="width: 100%;">
			<tr>
				<td style="width: 10%;"><label>Логин:</label></td>
				<td>{{ $user->login }}</td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>e-mail:</label></td>
				<td><input name="user_email" type="text" value="{{ $user->email }}"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Имя:</label></td>
				<td><input name="user_name" type="text" value="{{ $user->name }}"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Дата рождения:</label></td>
				<td><input name="user_birth" type="text" value="{{ $user->birth_date }}" placeholder="yyyy-mm-dd"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Пол:</label></td>
				<td><input name="user_gender" type="text" value="{{ $user->user_gender }}"></td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>Ресурсы</legend>
		<table class="edition" style="width: 100%;">
			<tr>
				<td style="width: 10%;"><label>Золото:</label></td>
				<td><input name="user_gold" type="text" value="{{ $user->user_gold }}"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Серебро:</label></td>
				<td><input name="user_silver" type="text" value="{{ $user->user_silver }}"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Энергия:</label></td>
				<td><input name="user_energy" type="text" value="{{ $user->user_energy }}"></td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Премиум:</label></td>
				<td>
					<label>
						<input name="user_premium_active" type="checkbox" @if($user->premium_activated) checked @endif>
						<span>@if($user->premium_activated) Дизактивировать @else Активировать @endif</span>
					</label>
					<input name="premium_expire_data" type="date" value="{{ $user->premium_expire_date }}" placeholder="yyyy-mm-dd hh:ii">
				</td>
			</tr>
			<tr>
				<td style="width: 10%;"><label>Администратор:</label></td>
				<td>
					<label>
						<input name="user_admin" type="checkbox" @if($user->user_role) checked @endif>
						<span>@if($user->user_role) Снять права @else Дать права @endif</span>
					</label>
				</td>
			</tr>
		</table>
	</fieldset>
	<input name="userApplyChanges" type="button" value="Применить изменения">

	<fieldset>
		<legend>Карты в колодах</legend>
		<?php $user_decks = unserialize($user->user_cards_in_deck); ?>

		@foreach($user_decks as $deck_slug=> $cards)
			<?php $fraction = \DB::table('tbl_fraction')->select('title','slug')->where('slug', '=', $deck_slug)->get(); ?>
			@if(!empty($cards))
			<div style="float: left; margin: 10px 20px; width: 30%;">
				<table class="edition" id="{{$deck_slug}}">
					<thead>
					<tr>
						<th>{{ $fraction[0]->title }}</th>
						<th>Количество карт</th>
					</tr>
					</thead>
					<tbody>
					@foreach($cards as $card_id => $q)
						<?php $card = \DB::table('tbl_cards')->select('id','title','card_strong','card_value')->where('id','=',$card_id)->get();?>
						@if($card)
							<tr>
								<td>
									<a href="{{ route('admin-card-edit-page', $card_id) }}">{{ $card[0]->title }} (сила {{ $card[0]->card_strong }}; вес{{ $card[0]->card_value }};)</a>
								</td>
								<td>{{ $q }}</td>
							</tr>
						@endif
					@endforeach
					</tbody>
				</table>
			</div>
			@endif
		@endforeach
	</fieldset>

	<fieldset>
		<legend>Карты в "доступных"</legend>
		<table class="edition" id="available">
			<thead>
			<tr>
				<th>Карты</th>
				<th>Количество карт</th>
			</tr>
			</thead>
			<tbody>
			<?php $user_available = unserialize($user -> user_available_deck); ?>
			@foreach($user_available as $card_id => $q)
				<?php $card = \DB::table('tbl_cards')->select('id','title','card_strong','card_value')->where('id','=',$card_id)->get(); ?>
				@if($card)
				<tr>
					<td>
						<a href="{{ route('admin-card-edit-page', $card_id) }}">{{ $card[0]->title }} (сила {{ $card[0]->card_strong }}; вес{{ $card[0]->card_value }};)</a>
					</td>
					<td>{{ $q }}</td>
				</tr>
				@endif
			@endforeach
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>Волшебство</legend>
		<?php $magic = unserialize($user->user_magic); ?>
		<table class="edition" id="magic">
			<thead>
			<tr>
				<th>Название</th>
				<th>Использований</th>
				<th>Активность</th>
			</tr>
			</thead>
			<tbody>
			@foreach($magic as $magic_id => $usage_data)
			<?php $magic_data = \DB::table('tbl_magic_effect')->select('id','title')->where('id','=',$magic_id)->get(); ?>
				@if($magic_data)
				<tr>
					<td>{{ $magic_data[0]->title }}</td>
					<td>{{ $usage_data['used_times'] }}</td>
					<td>@if($usage_data['active'] == 1) Активна @else Не активирована @endif</td>
				</tr>
				@endif
			@endforeach
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>Рейтинг</legend>
		<table class="edition" id="magic">
			<thead>
			<tr>
				<th>Название Лиги</th>
				<th>Вес колоды</th>
				<th>Количество игр в лиге</th>
				<th>Количество побед</th>
				<th>Рейтинг в лиге</th>
			</tr>
			</thead>
			<tbody>
			<?php $user_rating = unserialize($user->user_rating); ?>
			@foreach($user_rating as $league_slug => $rating)
				<?php $league = \DB::table('tbl_league')->select('slug','title','min_lvl','max_lvl')->where('slug','=',$league_slug)->get(); ?>
				<tr>
					<td>{{ $league[0]->title }}</td>
					<td>{{ $league[0]->min_lvl }} - {{ $league[0]->max_lvl }}</td>
					<td>{{ $rating['games_count'] }}</td>
					<td>{{ $rating['win_count'] }}</td>
					<td>{{ $rating['user_rating'] }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>Пополнения золота</legend>
		<table class="data-table" id="payment">
			<thead>
			<tr>
				<th>Получено денег (без учета комиссии)</th>
				<th>Кол-во золота до оплаты</th>
				<th>Получено золота</th>
				<th>Оплата по курсу</th>
				<th>Создан</th>
				<th>Дата оплаты</th>
			</tr>
			</thead>
			<tbody>
			@foreach($payments as $payment)
				<tr>
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
	</fieldset>
</div>
@stop