@extends('admin.layouts.default')
@section('content')
<div class="main-central-wrap" id="timing">
    {{ Form::open(['route' => 'admin-timing-save', 'method' => 'POST']) }}
    {{ Form::hidden('_method', "PUT") }}
    <table class="edition" style="width: 100%">
        <tbody>
        @foreach($timing_options as $field)
            <tr>
                <td style="max-width: 20%; width:20%;">{{ Form::label($field->meta_key, $field->meta_key_title.':') }}</td>
                <td>{{ Form::input('text', $field->meta_key, $field->meta_value) }}</td>
            </tr>
        @endforeach

        </tbody>
    </table>
    {{ Form::submit('Применить') }}
    {{ Form::close() }}
</div>
@stop