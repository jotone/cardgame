@extends('admin.layouts.default')
@section('content')

    <div class="main-central-wrap">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/card/groups/add') }}">Добавить</a>
        </div>

        @if($card_groups)
            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Название</th>
                        <th>Ссылка</th>
                        <th>Карты в группе</th>
                        <th>Создан</th>
                        <th>Изменен</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($card_groups as $group)
                    <tr>
                        <td><a class="edit" href="{{ route('admin-card-groups-edit-page', $group['id']) }}"></a></td>
                        <td>
                            {{ Form::open(['route' => 'admin-card-groups-drop', 'method' => 'POST']) }}
                            {{ Form::hidden('_method', 'DELETE') }}
                            <input name="group_id" type="hidden" value="{{ $group['id'] }}">
                            <input type="submit" class="drop" value="">
                            {{ Form::close() }}
                        </td>
                        <td>{{ $group['title'] }}</td>
                        <td>{{ $group['slug'] }}</td>
                        <td>{!! $group['cards'] !!}</td>
                        <td>{{ $group['created'] }}</td>
                        <td>{{ $group['updated'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

@stop