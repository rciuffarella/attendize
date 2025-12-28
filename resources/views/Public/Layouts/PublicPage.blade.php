<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <!--
                  _   _                 _ _
             /\  | | | |               | (_)
            /  \ | |_| |_ ___ _ __   __| |_ _______   ___ ___  _ __ ___
           / /\ \| __| __/ _ \ '_ \ / _` | |_  / _ \ / __/ _ \| '_ ` _ \
          / ____ \ |_| ||  __/ | | | (_| | |/ /  __/| (_| (_) | | | | | |
         /_/    \_\__|\__\___|_| |_|\__,_|_/___\___(_)___\___/|_| |_| |_|

        -->
        <title>
            @yield('title', config('app.name').' - '.config('app.url'))
        </title>

        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0" />

        {!! Html::style('assets/stylesheet/frontend.css') !!}

        @yield('head')
    </head>
    <body class="attendize">
        @include('Shared.Partials.FacebookSdk')

        <div id="public_page_wrap">
            @yield('content')
        </div>

        <a href="#top" style="display:none;" class="totop">
            <i class="ico-angle-up"></i>
            <span style="font-size:11px;">@lang('basic.TOP')</span>
        </a>

        @include('Shared.Partials.LangScript')
        {!! Html::script('assets/javascript/frontend.js') !!}

        @include('Shared.Partials.GlobalFooterJS')
        @yield('foot')
    </body>
</html>



