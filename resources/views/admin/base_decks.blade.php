@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="baseCards">
@foreach($fractions as $fraction)
    <fieldset data-race="{{$fraction['slug']}}">
        <legend>Базовые карты колоды "{{ $fraction['title'] }}"</legend>

        <table class="edition">
            <thead>
            <tr>
                <th></th>
                <th>Карта</th>
                <th>Количество</th>
                <th>Вес</th>
            </tr>
            </thead>
            <tbody>
                {!! $fraction['deck'] !!}
            </tbody>
            <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
        </table>

        <div class="container-wrap">
            <input name="baseCardsAddRow" type="button" value="Добавить Строку">
        </div>
        <div class="container-wrap">
            <input name="baseCardsApply" type="button" value="Применить" id="{{ $fraction['slug'] }}">
        </div>
    </fieldset>
@endforeach
</div>
@stop