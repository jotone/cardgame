@extends('admin.layouts.default')
@section('content')

<div class="main-central-wrap" id="supportPage">
    <fieldset>
        <legend>Рубрики</legend>
        <table class="edition" style="margin: 20px 0 20px 20px;" id="rubricsTable">
        @foreach($rubrics as $rubric)
            <tr style="cursor: move;">
                <td><input name="dropRubric" class="drop" value="" type="button" data-id="{{$rubric->id}}"></td>
                <td><input name="changeTitle" type="text" value="{!! $rubric->title !!}"></td>
                <td><input name="applyChange" type="button" value="Применить" data-id="{{$rubric->id}}"></td>
            </tr>
        @endforeach
        </table>

        <div style="margin-left: 20px;">
            <input name="newRubricTitle" type="text" placeholder="Введите название рубрики&hellip;">
            <input name="addRubric" type="button" value="Добавить">
        </div>
    </fieldset>

    <fieldset>
        <legend>Список почты тех. поддержки</legend>
        <table class="edition" id="adminsTable" style="margin: 20px 0 20px 20px;">
            @foreach($emails_list as $i => $email)
                <tr>
                    <td><input name="dropEmail" class="drop" value="" type="button" data-id="{{$i}}"></td>
                    <td><input name="changeEmail" type="text" value="{!! $email !!}"></td>
                    <td><input name="applyEmailChange" type="button" value="Применить" data-id="{{$i}}"></td>
                </tr>
            @endforeach
        </table>

        <div style="margin-left: 20px;">
            <input name="rubricAdminEmail" type="text" placeholder="Введите email&hellip;">
            <input name="addEmail" type="button" value="Добавить">
        </div>

    </fieldset>
</div>

@stop