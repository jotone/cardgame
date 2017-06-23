@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="userData">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <table class="data-table">
        <tr>
            <th></th>
            <th></th>
            <th>Логин</th>
            <th>e-mail</th>
            <th>Имя</th>
            <th>Изображение</th>
            <th>Золото</th>
            <th>Серебро</th>
            <th>Энергия</th>
            <th>Создан</th>
            <th>Последняя Активность</th>
            <th>Статус</th>
            <th>Бан</th>
        </tr>
    @foreach($users as $user)
        <tr>
            <td><a class="edit" href="{{ route('admin-user-edit-page', $user['id']) }}"></a></td>
            <td>
                {{ Form::open(['route' => 'admin-user-drop', 'method' => 'POST']) }}
                {{ Form::hidden('_method', 'DELETE') }}
                <input name="user_id" type="hidden" value="{{ $user['id'] }}">
                <input type="submit" class="drop" value="">
                {{ Form::close() }}
            </td>
            <td>{{ $user['login'] }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['name'] }}</td>
            <td>{!! $user['img_url'] !!}</td>
            <td>{{ $user['gold'] }}</td>
            <td>{{ $user['silver'] }}</td>
            <td>{{ $user['energy'] }}</td>
            <td>{{ $user['created'] }}</td>
            <td>{{ $user['updated'] }}</td>
            <td>{{ $user['admin_status'] }}</td>
            <td>{!! $user['ban'] !!}</td>
        </tr>
    @endforeach
    </table>
</div>
@stop