<!DOCTYPE html>
<html lang="en">
    <head>
        <!--
                  _   _                 _ _
             /\  | | | |               | (_)
            /  \ | |_| |_ ___ _ __   __| |_ _______   ___ ___  _ __ ___
           / /\ \| __| __/ _ \ '_ \ / _` | |_  / _ \ / __/ _ \| '_ ` _ \
          / ____ \ |_| ||  __/ | | | (_| | |/ /  __/| (_| (_) | | | | | |
         /_/    \_\__|\__\___|_| |_|\__,_|_/___\___(_)___\___/|_| |_| |_|

        -->
        <title>{{{$event->title}}} - Attendize.com</title>


        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0" />
        <link rel="canonical" href="{{$event->event_url}}" />


        <!-- Open Graph data -->
        <meta property="og:title" content="{{{$event->title}}}" />
        <meta property="og:type" content="article" />
        <meta property="og:url" content="{{$event->event_url}}?utm_source=fb" />
        @if($event->images->count())
        <meta property="og:image" content="{{config('attendize.cdn_url_user_assets').'/'.$event->images->first()['image_path']}}" />
        @endif
        <meta property="og:description" content="{{{Str::words(md_to_str($event->description), 20)}}}" />
        <meta property="og:site_name" content="Attendize.com" />
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        @yield('head')

       {!!Html::style(config('attendize.cdn_url_static_assets').'/assets/stylesheet/frontend.css')!!}

        <!--Bootstrap placeholder fix-->
        <style>
            ::-webkit-input-placeholder { /* WebKit browsers */
                color:    #ccc !important;
            }
            :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
                color:    #ccc !important;
                opacity:  1;
            }
            ::-moz-placeholder { /* Mozilla Firefox 19+ */
                color:    #ccc !important;
                opacity:  1;
            }
            :-ms-input-placeholder { /* Internet Explorer 10+ */
                color:    #ccc !important;
            }

            input, select {
                color: #999 !important;
            }

            .btn {
                color: #fff !important;
            }

        </style>
        @if ($event->bg_type == 'color' || Request::input('bg_color_preview'))
            <style>body {background-color: {{(Request::input('bg_color_preview') ? '#'.Request::input('bg_color_preview') : $event->bg_color)}} !important; }</style>
        @endif

        @if (($event->bg_type == 'image' || $event->bg_type == 'custom_image' || Request::input('bg_img_preview')) && !Request::input('bg_color_preview'))
            <style>
                body {
                    background: url({{(Request::input('bg_img_preview') ? URL::to(Request::input('bg_img_preview')) :  asset(config('attendize.cdn_url_static_assets').'/'.$event->bg_image_path))}}) no-repeat center center fixed;
                    background-size: cover;
                }
            </style>
        @endif

    </head>
    <body class="attendize">
        <div id="event_page_wrap" vocab="http://schema.org/" typeof="Event">
            @yield('content')

            {{-- Push for sticky footer--}}
            @stack('footer')
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

        {{-- Sticky Footer--}}
        @yield('footer')

        <a href="#intro" style="display:none;" class="totop"><i class="ico-angle-up"></i>
            <span style="font-size:11px;">@lang("basic.TOP")</span></a>

        @include("Shared.Partials.LangScript")
        {!!Html::script(config('attendize.cdn_url_static_assets').'/assets/javascript/frontend.js')!!}

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

        @if(isset($secondsToExpire))
        <script>if($('#countdown')) {setCountdown($('#countdown'), {{$secondsToExpire}});}</script>
        @endif

        @include('Shared.Partials.GlobalFooterJS')
    </body>
</html>
