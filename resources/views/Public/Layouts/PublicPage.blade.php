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

        <style>
            .public-main-header {
                background-color: #ffffff;
                border-bottom: 1px solid #e5e7eb;
            }
            .public-main-header-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 0;
                flex-wrap: wrap;
                gap: 10px;
            }
            .public-main-header-logo img {
                max-height: 40px;
                width: auto;
            }
            .public-main-header-title {
                font-size: 18px;
                font-weight: 600;
                margin-left: 8px;
            }
            .public-main-header-left {
                display: flex;
                align-items: center;
            }
            .public-main-header-nav a {
                display: inline-block;
                margin-left: 16px;
                font-size: 14px;
                color: #4b5563;
            }
            .public-main-header-nav a:hover {
                color: #111827;
                text-decoration: none;
            }
            @media (max-width: 767px) {
                .public-main-header-inner {
                    flex-direction: column;
                    align-items: flex-start;
                }
                .public-main-header-nav a {
                    margin-left: 0;
                    margin-right: 16px;
                    margin-top: 4px;
                }
            }
        </style>

        @yield('head')
    </head>
    <body class="attendize">
        @include('Shared.Partials.FacebookSdk')

        <header class="public-main-header">
            <div class="container">
                <div class="public-main-header-inner">
                    <div class="public-main-header-left">
                        <a href="{{ route('index') }}" class="public-main-header-logo">
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="{{ config('app.name') }}">
                        </a>
                        <span class="public-main-header-title">Piattaforma Eventi</span>
                    </div>
                    <nav class="public-main-header-nav">
                        <a href="{{ route('index') }}">Home</a>
                        <a href="{{ route('index') }}#events">Eventi</a>
                        <a href="{{ url('/login') }}">Area riservata</a>
                        <a href="#footer-contatti">Contatti</a>
                    </nav>
                </div>
            </div>
        </header>

        <div id="public_page_wrap">
            @yield('content')
        </div>

        {{-- Cookie Law Banner --}}
        <div id="cookie-law-banner" style="display:none; position:fixed; left:0; right:0; bottom:0; z-index:9999; background:#111827; color:#f9fafb; padding:12px 0; font-size:13px;">
            <div class="container" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;">
                <div style="max-width:780px; line-height:1.4;">
                    Questo sito utilizza cookie tecnici e, previo tuo consenso, cookie di profilazione o di terze parti per finalità statistiche e di miglioramento dell’esperienza di navigazione.
                    Proseguendo la navigazione o cliccando su “Accetto” acconsenti all’uso dei cookie.
                    @if(Route::has('privacy'))
                        Consulta la <a href="{{ route('privacy') }}" style="color:#93c5fd; text-decoration:underline;">Privacy & Cookie Policy</a>.
                    @endif
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button id="cookie-law-accept" class="btn btn-success btn-sm">Accetto</button>
                    <button id="cookie-law-decline" class="btn btn-default btn-sm">Rifiuto</button>
                </div>
            </div>
        </div>

        <a href="#top" style="display:none;" class="totop">
            <i class="ico-angle-up"></i>
            <span style="font-size:11px;">@lang('basic.TOP')</span>
        </a>

        @include('Shared.Partials.LangScript')
        {!! Html::script('assets/javascript/frontend.js') !!}

        <script type="text/javascript">
            (function() {
                function setCookie(name, value, days) {
                    var expires = "";
                    if (days) {
                        var date = new Date();
                        date.setTime(date.getTime() + (days*24*60*60*1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
                }
                function getCookie(name) {
                    var nameEQ = name + "=";
                    var ca = document.cookie.split(';');
                    for(var i=0;i < ca.length;i++) {
                        var c = ca[i];
                        while (c.charAt(0)==' ') c = c.substring(1,c.length);
                        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                    }
                    return null;
                }

                var banner = document.getElementById('cookie-law-banner');
                if (!getCookie('cookie_consent')) {
                    if (banner) {
                        banner.style.display = 'block';
                    }
                }

                var acceptBtn = document.getElementById('cookie-law-accept');
                var declineBtn = document.getElementById('cookie-law-decline');

                if (acceptBtn) {
                    acceptBtn.addEventListener('click', function() {
                        setCookie('cookie_consent', 'accepted', 365);
                        if (banner) {
                            banner.style.display = 'none';
                        }
                    });
                }

                if (declineBtn) {
                    declineBtn.addEventListener('click', function() {
                        setCookie('cookie_consent', 'declined', 365);
                        if (banner) {
                            banner.style.display = 'none';
                        }
                    });
                }
            })();
        </script>

        @include('Shared.Partials.GlobalFooterJS')
        @yield('foot')
    </body>
</html>



