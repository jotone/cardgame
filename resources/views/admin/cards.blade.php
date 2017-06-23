@extends('admin.layouts.default')
@section('content')

<div class="main-central-wrap">
    <div class="button-wrap" id="addCard">
        <a class="add-one" href="{{ URL::asset('admin/card/add') }}">Добавить</a>
    </div>
    
    @if($fractions)

    <select name="chooseRace">
        @foreach($fractions as $key => $value)
            <option value="{{ $value->slug }}" @if($fraction_slug == $value->slug) selected="selected" @endif>{{ $value->title }}</option>
        @endforeach
    </select>
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>
                    <span>Название</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction active" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>Изображение</th>
                <th>
                    <span>Сила карты</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    <span>Вес карты</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    <span>Лидер</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    Ряд действия
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>Группа</th>
                <th>
                    <span>Действия</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    <span>Цена</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    <span>Создан</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
                <th>
                    <span>Изменен</span>
                    <div style="text-align: center;">
                        <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                        <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach($cards as $card)
            <tr>
                <td><a class="edit" href="{{ route('admin-card-edit-page', $card['id']) }}"></a></td>
                <td>
                    {{ Form::open(['route' => 'admin-cards-drop', 'method' => 'POST']) }}
                    {{ Form::hidden('_method', 'DELETE') }}
                    <input name="card_id" type="hidden" value="{{ $card['id'] }}">
                    <input type="submit" class="drop" value="">
                    {{ Form::close() }}
                </td>
                <td>{{ $card['title']}}</td>
                <td>
                    @if($card['img_url'] != '')
                        <img src="{{ URL::asset('/img/card_images/'.$card['img_url']) }}" alt="" style="max-width: 100px; max-height: 100px;">
                    @else
                        Изображение отсутсвует
                    @endif

                </td>
                <td>{{ $card['card_strong'] }}</td>
                <td>{{ $card['card_value'] }}</td>
                <td>
                    @if($card['is_leader'] == 0)
                        Нет
                    @else
                        Да
                    @endif
                </td>
                <td>{{ $card['allowed_rows'] }}</td>
                <td>{!! $card['groups'] !!}</td>
                <td>{{ $card['actions'] }}</td>
                <td>{{ $card['price_gold']}}зол. {{ $card['price_silver'] }}сер.</td>
                <td>{{ $card['created'] }}</td>
                <td>{{ $card['updated'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@stop