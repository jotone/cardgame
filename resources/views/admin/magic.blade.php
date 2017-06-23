@extends('admin.layouts.default')
@section('content')

    <div class="main-central-wrap">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/magic/add') }}">Добавить</a>
        </div>

        @if($magic)

            <table class="data-table">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>
                        <span>Раса</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>
                        <span>Название</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>Изображение</th>
                    <th>Описание</th>
                    <th>
                        <span>Действия</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>
                        <span>Цена в золоте</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>
                        <span>Цена в серебре</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>
                        <span>Затраты энергии</span>
                        <div style="text-align: center;">
                            <a class="table-direction" href="#" data-direct="up">&#9650;</a>
                            <a class="table-direction" href="#" data-direct="down">&#9660;</a>
                        </div>
                    </th>
                    <th>
                        <span>Доступен в лиге</span>
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

                @foreach($magic as $effect)

                    <tr>
                        <td><a class="edit" href="{{ route('admin-magic-edit-page', $effect['id']) }}"></a></td>
                        <td>
                            {{ Form::open(['route' => 'admin-magic-drop', 'method' => 'POST']) }}
                            {{ Form::hidden('_method', 'DELETE') }}
                            <input name="effect_id" type="hidden" value="{{ $effect['id'] }}">
                            <input type="submit" class="drop" value="">
                            {{ Form::close() }}
                        </td>
                        <td>{{$effect['fraction']}}</td>
                        <td>{{ $effect['title'] }}</td>
                        <td>
                            @if(($effect['img_url'] != '') && ($effect['img_url'] != 'undefined'))
                                <img src="{{ URL::asset('/img/card_images/'.$effect['img_url']) }}" alt="" style="max-width: 100px; max-height: 100px;">
                            @else
                                Изображение отсутсвует
                            @endif
                        </td>
                        <td>{!! $effect['description'] !!}</td>
                        <td>
                            {!! $effect['actions'] !!}
                        </td>
                        <td>{{ $effect['price_gold'] }}</td>
                        <td>{{ $effect['price_silver'] }}</td>
                        <td>{{ $effect['energy_cost'] }}</td>
                        <td>{{ $effect['league'] }}</td>
                        <td>{{ $effect['created'] }}</td>
                        <td>{{ $effect['updated'] }}</td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@stop