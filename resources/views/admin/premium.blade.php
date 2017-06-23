@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="premiumOptions">
    {{ Form::open(['route' => 'admin-premium-options', 'method' => 'POST']) }}
    {{ Form::hidden('_method', "PUT") }}

    <table class="edition" style="width: 100%">
        <tbody>
        @foreach($premium_options as $field)
            <tr>
                <td style="max-width: 15%; width:15%;">{!! $field->meta_key_title.':' !!}</td>
                <td>{{ Form::input('text', $field->meta_key, $field->meta_value) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ Form::submit('Применить') }}
    {{ Form::close() }}
</div>
@stop