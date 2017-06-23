<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/reset.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/within_style.css') }}">
    <script src="{{ URL::asset('js/jquery-2.min.js') }}"></script>
    <script src="{{ URL::asset('js/jquery.tinymce.min.js') }}"></script>
    <script src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>

    <script src="{{ URL::asset('js/within.js') }}"></script>
    <title>Gwent Admin Main Page</title>
</head>

<body>
    
<header>
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <ul class="top-menu">
        <li><a href="{{ route('admin-main') }}">Фракции</a></li>
        <li><a href="#">Настройки</a>
            <ul>
                <li><a href="{{ route('admin-leagues') }}">Настройки лиг</a></li>
                <li><a href="{{ route('admin-base-deck') }}">Базовые карты</a></li>
                <li><a href="{{ route('admin-exchanges') }}">Соотношение обменов</a></li>
                <li><a href="{{ route('admin-deck-options') }}">Настройка колоды</a></li>
                <li><a href="{{ route('admin-premium') }}">Покупка Премиума</a></li>
                <li><a href="{{ route('admin-user-fields') }}">Базовые поля пользователей</a></li>
                <li><a href="{{ route('admin-timing') }}">Тайминг боя</a></li>
            </ul>
        </li>

        <li><a href="{{ route('admin-cards') }}">Карты</a></li>
        <li><a href="{{ route('admin-card-groups') }}">Группы Карт</a></li>
        <li><a href="{{ route('admin-magic') }}">Волшебство</a></li>
        <li><a href="{{ route('admin-actions') }}">Действия</a></li>
        <li><a href="{{ route('admin-users') }}">Пользователи</a>
            <ul>
                <li><a href="{{ route('admin-payment') }}">Оплаченые пополнения</a></li>
            </ul>
        </li>
        <li><a href="{{ route('admin-pages') }}">Страницы</a>
            <ul>
                <li><a href="{{ route('admin-support') }}">Тех.Поддержка</a></li>
            </ul>
        </li>
    </ul>
    
    <div class="admin-status-bar">
    <?php
    $user = Auth::user();
    if($user){
    ?>
        Вы зашли как, <strong>{{ $user -> login }}</strong>
        &nbsp;&nbsp;&nbsp;
        <a href="{{ URL::asset('admin/logout') }}" style="color: #fff">Выйти</a>
    <?php
    }
    ?>
    </div>
</header>
