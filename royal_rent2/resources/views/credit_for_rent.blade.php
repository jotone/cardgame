@extends('layouts.default')
@section('content')
    <!-- MAIN -->
    <div class="main">
        <!-- add partials here -->
        <div class="mbox">
            <ul class="el-breadcrumbs">
                <li><a href="/">Главная</a></li>
                <li class="active"><a href="{{ URL::asset($meta['slug']) }}">{{$meta['title']}}</a></li>
            </ul>
        </div>
        <section class="credit">
            <div class="mbox">
                <div class="credit-spb">
                    <h1>{{$meta['title']}}</h1>
                    {!! $meta['text'] !!}
                    <a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
                </div>
                <div class="credit-cart-pic" style="background: url({{ URL::asset($meta['img_url'][0]['img']) }}) no-repeat center center; ">
                    <!-- <img src="{{ URL::asset($meta['img_url'][0]['img']) }}" alt="{{ $meta['img_url'][0]['alt'] }}"> -->
                    <a class="mouse" href="#">
                        <span class="dot"></span>
                    </a>
                </div>
            </div>
        </section>

        <section class="credit-content">
            <div class="mbox">
                <div class="few-steps">
                    <h2>{{ $content['string_0']['value'] }}</h2>
                    <div class="few-steps-wrap">
                        @foreach($content['img_slider_0']['value'] as $slide)
                            <div class="few-steps-item">
                                <div class="few-steps-pic">
                                    <img src="{{ URL::asset($slide['img']) }}" alt="">
                                </div>
                                <p>{{ $slide['alt'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
                </div>
                <div class="credit-banks">
                    <h2>{!! $content['string_1']['value'] !!}</h2>
                    <div class="credit-banks-row">
                        @foreach($content['category_0'] as $item)
                            <div class="credit-banks-item">
                                <a href="#"><img src="{{ URL::asset($item['img_url'][0]['img']) }}" alt="{{ $item['img_url'][0]['alt'] }}"></a>
                            </div>
                        @endforeach
                    </div>
                    {!! $content['fulltext_0']['value'] !!}
                    <a class="el-button fancybox-form form-order" href="#call-popup">Оформить заявку</a>
                </div>
            </div>
        </section>

        @if( (!empty($content['string_2']['value'])) && (!empty($content['fulltext_1']['value'])) )
            <section class="seo-block">
                <div class="mbox">
                    <div class="seo-wrap">
                        <div class="seo-pic">
                            @foreach($content['img_slider_1']['value'] as $slide)
                                <img src="{{ URL::asset($slide['img']) }}" alt="{{ $slide['alt'] }}">
                            @endforeach
                        </div>
                        <div class="seo-info">
                            <h2>{{ $content['string_2']['value'] }}</h2>
                            {!! str_replace('<pre>','<p>', str_replace('</pre>','</p>',$content['fulltext_1']['value'])) !!}
                        </div>
                    </div>
                </div>
            </section>
        @endif
        @include('layouts.attachment')
    </div>
    <!-- /MAIN -->
@stop