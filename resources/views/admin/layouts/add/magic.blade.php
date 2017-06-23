@extends('admin.layouts.default')
@section('content')
    <script src="{{ URL::asset('js/within_magic.js') }}"></script>

    <div class="main-central-wrap">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="magic_title" type="text"></td>
                </tr>
                <tr>
                    <td><label>Описание:</label></td>
                    <td><textarea name="magic_descr"></textarea></td>
                </tr>
                <tr>
                    <td><label>Фон:</label></td>
                    <td>
                        <input name="magicAddImg" type="file">
                        <div class="image-container cfix"></div>
                    </td>
                </tr>
                <tr>
                    <td><label>Фракция:</label></td>
                    <td id="racesToUse">
                        @foreach($fractions as $fraction)

                            <div class="container-wrap">
                                <label><input type="radio" name="magic_race_to_use" value="{{ $fraction->slug }}">{{ $fraction->title }}</label>
                            </div>

                        @endforeach
                    </td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Действия</legend>

            <table class="edition" id="magicCurrentActions">
            </table>

            <table class="actions" id="tableMagicActionList">
                <thead>
                <tr>
                    <td><strong>Выбрать действие:</strong></td>
                    <td>
                        <select name="magic_actions_select">
                            @foreach($actions as $action)
                                <option value="{{ $action['id'] }}" data-title="{{ $action['slug'] }}">{{ $action['title'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                    @foreach($actions as $action)
                    <div data-action-type="{{$action['slug']}}">
                        {{$action['description']}}
                    </div>
                    @endforeach
                    </td>
                </tr>
                </thead>

                <tbody>
                @foreach($actions as $action)
                    <?php $html_options = unserialize($action['html_options']);?>
                    @foreach($html_options as $i => $options)
                    <tr data-action-type="{{$action['slug']}}">
                        <td>{!!$options[0]!!}:</td>
                        <td>{!!$options[1]!!}</td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>

            </table>

            <div style="padding: 5px 20px 5px 2%;">
                <input type="button" name="addMoreMagicActions" value="Добавить действие">
            </div>
        </fieldset>

        <fieldset>
            <legend>Доступен для лиги</legend>
            <table class="edition" style="width: 100%;">
                <tr>
                    <td><label>Название лиги:</label></td>
                    <td>
                        <select name="min_league">
                            <option value="0">Все</option>
                            @foreach($leagues as $key => $league)
                                <option value="{{$league->id}}">{{$league->title}} (от {{$league->min_lvl}} - до {{$league->max_lvl}})</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Цены</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td><label>Начальное количество использований:</label></td>
                    <td><input name="usage_count" type="number" min="0"></td>
                </tr>
                <tr>
                    <td style="width: 10%;"><label>Затраты энергии:</label></td>
                    <td><input name="energy_cost" type="number" min="0"></td>
                </tr>
                <tr>
                    <td><label>Цена золото:</label></td>
                    <td><input name="price_gold" type="number" min="0"></td>
                </tr>
                <tr>
                    <td><label>Цена серебро:</label></td>
                    <td><input name="price_silver" type="number" min="0"></td>
                </tr>

            </table>

        </fieldset>
        <input name="magicAdd" type="button" value="Добавить">
    </div>
@stop