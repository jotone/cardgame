@extends('admin.layouts.default')
@section('content')
	<div class="main-central-wrap">
		<input name="fraction_id" type="hidden" value="{{ $fraction->id }}">
		<fieldset>
			<legend>Основные данные</legend>

			<table class="edition" style="width: 100%;">
				<tr>
					<td style="width: 10%;"><label>Название:</label></td>
					<td><input name="fraction_title" type="text" value="{{ $fraction->title }}"></td>
				</tr>
				<tr>
					<td><label>Обозначение:</label></td>
					<td><input name="fraction_slug" type="text" value="{{ $fraction->slug }}"></td>
				</tr>
				<tr>
					<td><label>Тип:</label></td>
					<td><input name="fraction_type" type="text" value="{{ $fraction->type }}"></td>
				</tr>
				<tr>
					<td><label>Описание:</label></td>
					<td><textarea name="fraction_text">{{ $fraction->description }}</textarea></td>
				</tr>
				<tr>
					<td><label>Короткое описание:</label></td>
					<td><textarea name="fraction_short_decr">{{ $fraction->short_description }}</textarea></td>
				</tr>
				<tr>
					<td><label>Изображение фракции:</label></td>
					<td>
						<input name="fractionAddImg" type="file">
						<div class="image-container cfix">
							@if($fraction->img_url !='')
							<img src="{{ URL::asset('/img/fractions_images/'.$fraction->img_url) }}" alt="{{ $fraction->img_url }}" style="max-width: 100px; max-height: 100px;">
							@endif
						</div>
					</td>
				</tr>
				<tr>
					<td><label>Рубашка карт фракции:</label></td>
					<td>
						<input name="fractionCardBG" type="file">
						<div class="image-container cfix">
							@if($fraction->card_img !='')
								<img src="{{ URL::asset('/img/fractions_images/'.$fraction->card_img) }}" alt="{{ $fraction->card_img }}" style="max-width: 100px; max-height: 100px;">
							@endif
						</div>
					</td>
				</tr>
				<tr>
					<td><label>Бэкграунд для фракции:</label></td>
					<td>
						<input name="fractionBGAddImg" type="file">
						<div class="image-container cfix">
							@if($fraction->bg_img !='')
								<img src="{{ URL::asset('/img/fractions_images/'.$fraction->bg_img) }}" alt="{{ $fraction->bg_img }}" style="max-width: 100px; max-height: 100px;">
							@endif
						</div>
					</td>
				</tr>
				<tr>
					<td><label>Описание Фракции в магазине</label></td>
					<td><textarea name="fraction_shop">{{ $fraction->descr_shop }}</textarea></td>
				</tr>
				<tr>
					<td><label>Описание Фракции при покупке волшебства</label></td>
					<td><textarea name="fraction_magic">{{ $fraction->descr_magic }}</textarea></td>
				</tr>
			</table>

			<input type="button" name="editFraction" value="Применить">
		</fieldset>
	</div>

@stop