@extends('admin.layouts.default')
@section('content')

    <div class="main-central-wrap" id="cardRaces">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/fraction/add') }}">Добавить</a>
        </div>
        <table class="data-table">
            <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Название</th>
                <th>Обозначение</th>
                <th>Тип</th>
                <th>Изображение</th>
                <th>Дата создания</th>
                <th>Дата изменения</th>
            </tr>
            </thead>

            <tbody>
            @foreach($fractions as $fraction)
                <tr>
                    <td><a class="edit" href="{{ route('admin-fraction-edit-it', $fraction['id']) }}"></a></td>
                    <td>
                        {{ Form::open(['route' => 'admin-fraction-drop', 'method' => 'POST']) }}
                        {{ Form::hidden('_method', 'DELETE') }}
                        <input name="fraction_id" type="hidden" value="{{ $fraction['id'] }}">
                        <input type="submit" class="drop" value="">
                        {{ Form::close() }}
                    </td>
                    <td>{{ $fraction['title'] }}</td>
                    <td>{{ $fraction['slug'] }}</td>
                    <td>{{ $fraction['type'] }}</td>
                    <td>
                        @if($fraction['img_url'] != '')
                            <img src="{{ URL::asset('/img/fractions_images/'.$fraction['img_url']) }}" alt="" style="max-width: 100px; max-height: 100px;">
                        @else
                            Изображение отсутсвует
                        @endif
                    </td>
                    <td>{{ $fraction['created'] }}</td>
                    <td>{{ $fraction['updated'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop