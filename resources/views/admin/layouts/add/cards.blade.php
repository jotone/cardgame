@extends('admin.layouts.default')
@section('content')
<script src="{{ URL::asset('js/within_cards.js') }}"></script>
<div class="main-central-wrap">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="card_title" type="text"></td>
            </tr>
            <tr>
                <td><label>Короткое описание:</label></td>
                <td><textarea name="card_short_descr"></textarea></td>
            </tr>
            <tr>
                <td><label>Полное описание:</label></td>
                <td><textarea name="card_full_descr"></textarea></td>
            </tr>
            <tr>
                <td><label>Фон:</label></td>
                <td>
                    <input name="cardAddImg" type="file">
                    <div class="image-container cfix"></div>
                </td>
            </tr>
        </table>

    </fieldset>



    <fieldset>
        <legend>Отнести к группе:</legend>
        <table class="edition" id="cardCurrentGroups">
        </table>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Отнести к группе:</label></td>
                <td>
                    <select name="addCardToGroup">
                        {!! $card_groups !!}
                    </select>
                </td>
            </tr>
        </table>

        <div style="padding: 5px 20px 5px 2%;">
            <input type="button" name="addCardToGroup" value="Добавить группу">
        </div>
    </fieldset>



    <fieldset>
        <legend>Действия</legend>
        
        <table class="edition" id="cardCurrentActions">
        </table>
        
        <table class="actions" id="tableActionList">
        <thead>
            <tr>
                <td><strong>Выбрать действие:</strong></td>
                <td>
                    <select name="card_actions_select">
                        @foreach($card_actions as $action)
                        <option value="{{ $action['id'] }}" data-title="{{ $action['slug'] }}">{{ $action['title'] }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    @foreach($card_actions as $action)
                    <div data-action-type="{{$action['slug']}}">
                        {{$action['description']}}
                    </div>
                    @endforeach
                </td>
            </tr>
        </thead>

        <tbody>
        @foreach($card_actions as $action)
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
            <input type="button" name="addMoreCardActions" value="Добавить действие">
        </div>
    </fieldset>

    <fieldset>
        <legend>Описание</legend>

        <table class="actions" style="width: 100%;">
            <tr>
                <td style="width: 15%;"><label>Тип карты:</label></td>
                <td>
                    <select name="cardType">
                        <option value="neutrall">Нейтральная</option>
                        <option value="race">Расовая</option>
                        <option value="special">Специальная</option>
                    </select>
                </td>
            </tr>

            <tr id="cardCanNotBeSavedByRace" style="display: none">
                <td><label>Карта не может быть собрана в колоде расы:</label></td>
                <td>
                    <div class="container-wrap">
                        <input type="checkbox" value="knight">
                        <label>Рыцари империи</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="forest">
                        <label>Хозяева леса</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="cursed">
                        <label>Проклятые</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="undead">
                        <label>Нечисть</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="highlander">
                        <label>Горцы</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="monsters">
                        <label>Монстры</label>
                    </div>
                </td>
            </tr>

            <tr id="cardInRace" style="display: none;">
                <td><label>Отностися к расе:</label></td>
                <td>
                    <select name="cardRace">
                        @foreach($fractions as $fraction)
                            <option value="{{$fraction->slug}}">{{$fraction->title}}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td><label>Дальность карты:</label></td>
                <td>
                    <div class="container-wrap">
                        <label>
                            <input type="checkbox" value="0" name="C_ActionRow">
                            Ближний
                        </label>
                    </div>
                    <div class="container-wrap">
                        <label>
                            <input type="checkbox" value="1" name="C_ActionRow">
                            Дальний
                        </label>
                    </div>
                    <div class="container-wrap">
                        <label>
                            <input type="checkbox" value="2" name="C_ActionRow">
                            Сверхдальний
                        </label>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label>Сила карты:</label></td>
                <td><input name="cardStrongthValue" type="number" min="0" value="0"></td>
            </tr>

            <tr>
                <td><label>Вес карты:</label></td>
                <td><input name="cardWeightValue" type="number" min="0" value="0"></td>
            </tr>

            <tr>
                <td><label>Карта лидер:</label></td>
                <td><label><input name="cardIsLeader" type="checkbox">Сделать текущую карту картой-лидером</label></td>
            </tr>

            <tr>
                <td><label>Максимальное колличество в колоде:</label></td>
                <td><input name="cardMaxValueInDeck" type="number" min="1" value="1"></td>
            </tr>
        </table>
    </fieldset>


    <fieldset>
        <legend>Цены</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 15%; min-width: 15%;"><label>Цена золото:</label></td>
                <td><input name="cardPriceGold" type="number" min="0" value="0"></td>
            </tr>
            <tr>
                <td><label>Цена серебро:</label></td>
                <td><input name="cardPriceSilver" type="number" min="0" value="0"></td>
            </tr>
            <tr><td style="padding: 15px;"></td><td></td></tr>
        </table>
    </fieldset>

    <input name="cardAdd" type="button" value="Добавить">
</div>
@stop