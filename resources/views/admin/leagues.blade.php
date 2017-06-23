@extends('admin.layouts.default')
@section('content')
    <div class="main-central-wrap" id="leagueOptions">
        <table class="edition">
            <thead>
            <tr>
                <th></th>
                <th>Лига</th>
                <th>От</th>
                <th>До</th>
                <th>Золото за победу</th>
                <th>Золото за проигрыш</th>
                <th>Серебро за победу</th>
                <th>Серебро за проигрыш</th>
                <th>Рейтинг за победу</th>
                <th>Рейтинг за проигрыш</th>
                <th>Минимальное число серебра</th>
            </tr>
            </thead>
            <tbody>
            @foreach($leagues as $league)
                <tr>
                    <td>
                        {{ Form::open(['route' => 'admin-league-drop', 'method' => 'POST']) }}
                        {{ Form::hidden('_method', 'DELETE') }}
                        <input data-type="toAdd" name="leagueId" type="hidden" value="{{ $league->id }}">
                        <input type="submit" class="drop" value="" title="{{ $league->id }}">
                        {{ Form::close() }}
                    </td>
                    <td><input data-type="toAdd" name="title" type="text" value="{{ $league->title }}" style="min-width: 65px; width: 65px;"></td>
                    <td><input data-type="toAdd" name="min_lvl" type="number" value="{{ $league->min_lvl }}" style="width: 65px;"></td>
                    <td><input data-type="toAdd" name="max_lvl" type="number" value="{{ $league->max_lvl }}" style="width: 65px;"></td>
                    <td>
                        <table>
                            <tr>
                                <td>Обычный:</td>
                                <td><input data-type="toAdd" name="gold_per_win" type="number" value="{{ $league->gold_per_win }}" style="width: 65px;"></td>
                            </tr>
                            <tr>
                                <td>Премиум:</td>
                                <td><input data-type="toAdd" name="prem_gold_per_win" type="number" value="{{ $league->prem_gold_per_win }}" style="width: 65px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>Обычный:</td>
                                <td><input data-type="toAdd" name="gold_per_loose" type="number" value="{{ $league->gold_per_loose }}" style="width: 65px;"></td>
                            </tr>
                            <tr>
                                <td>Премиум:</td>
                                <td><input data-type="toAdd" name="prem_gold_per_loose" type="number" value="{{ $league->prem_gold_per_loose }}" style="width: 65px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>Обычный:</td>
                                <td><input data-type="toAdd" name="silver_per_win" type="number" value="{{ $league->silver_per_win }}" style="width: 65px;"></td>
                            </tr>
                            <tr>
                                <td>Премиум:</td>
                                <td><input data-type="toAdd" name="prem_silver_per_win" type="number" value="{{ $league->prem_silver_per_win }}" style="width: 65px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>Обычный:</td>
                                <td><input data-type="toAdd" name="silver_per_loose" type="number" value="{{ $league->silver_per_loose }}" style="width: 65px;"></td>
                            </tr>
                            <tr>
                                <td>Премиум:</td>
                                <td><input data-type="toAdd" name="prem_silver_per_loose" type="number" value="{{ $league->prem_silver_per_loose }}" style="width: 65px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td><input data-type="toAdd" name="rating_per_win" type="number" value="{{ $league->rating_per_win }}" style="width: 65px;"></td>
                    <td><input data-type="toAdd" name="rating_per_loose" type="number" value="{{ $league->rating_per_loose }}" style="width: 65px;"></td>
                    <td><input data-type="toAdd" name="min_amount" type="number" value="{{ $league->min_amount }}" style="width: 65px;"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="container-wrap">
            <input name="leagueAddRow" type="button" value="Добавить строку">
        </div>

        <div class="container-wrap">
            <input name="leagueApply" type="button" value="Применить">
        </div>
    </div>
@stop