@extends('admin.layouts.default')
@section('content')

<div class="main-central-wrap">
    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="fraction_title" type="text"></td>
            </tr>
            <tr>
                <td><label>Обозначение:</label></td>
                <td><input name="fraction_slug" type="text"></td>
            </tr>
            <tr>
                <td><label>Тип:</label></td>
                <td><input name="fraction_type" type="text"></td>
            </tr>
            <tr>
                <td><label>Описание:</label></td>
                <td><textarea name="fraction_text"></textarea></td>
            </tr>
            <tr>
                <td><label>Короткое описание:</label></td>
                <td><textarea name="fraction_short_decr"></textarea></td>
            </tr>
            <tr>
                <td><label>Изображение фракции:</label></td>
                <td>
                    <input name="fractionAddImg" type="file">
                    <div class="image-container cfix">

                    </div>
                </td>
            </tr>
            <tr>
                <td><label>Рубашка карт фракции:</label></td>
                <td><input name="fractionCardBG" type="file"><div class="image-container cfix"></div></td>
            </tr>
            <tr>
                <td><label>Бэкграунд для фракции:</label></td>
                <td><input name="fractionBGAddImg" type="file"><div class="image-container cfix"></div></td>
            </tr>
            <tr>
                <td><label>Описание Фракции в магазине</label></td>
                <td><textarea name="fraction_shop"></textarea></td>
            </tr>
            <tr>
                <td><label>Описание Фракции при покупке волшебства</label></td>
                <td><textarea name="fraction_magic"></textarea></td>
            </tr>
        </table>

        <input type="button" name="addFraction" value="Добавить">
    </fieldset>
</div>

@stop