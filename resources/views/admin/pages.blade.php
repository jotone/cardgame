@extends('admin.layouts.default')
@section('content')
    <div class="main-central-wrap" id="sitePagesTexts">
        <fieldset>
            <legend>Выберите страницу</legend>
            <select name="pageSelector">
                @foreach($pages as $page)
                    <option value="{{$page->slug}}">{{$page->title}}</option>
                @endforeach
            </select>
        </fieldset>

        <fieldset>
            <legend>Основные данные</legend>
            <table class="edition" style="width: 100%">
                <tr>
                    <td style="width: 10%">
                        <label for="pageTitle">Заголовок:</label>
                    </td>
                    <td>
                        <input id="pageTitle" name="pageTitle" type="text" required value="{{$first_editable['title']}}">
                    </td>
                </tr>
                <tr>
                    <td style="width: 10%">
                        <label for="pageText">Текст:</label>
                    </td>
                    <td>
                        <textarea id="pageText" name="pageText">{!! $first_editable['text'] !!}</textarea>
                    </td>
                </tr>
            </table>
        </fieldset>
        <input name="applyPage" type="button" value="Применить" data-slug="{{$first_editable['slug']}}">
    </div>
@stop