@extends('Public.Layouts.PublicPage')

@section('title')
    {{ config('app.name') }} - @lang('Public_ViewOrganiser.upcoming_events')
@overwrite

@section('head')
    <style>
        body { background-color: #ffffff !important; }
        section#intro {
            background-color: #222222 !important;
            color: #ffffff !important;
        }

        .home-hero {
            padding: 40px 15px 30px;
            text-align: center;
        }

        .home-hero h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .home-hero p {
            font-size: 16px;
            opacity: .85;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin: 30px 0 20px;
        }

        .events-grid {
            margin-bottom: 40px;
        }

        .event-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(15,23,42,.12);
            margin-bottom: 25px;
            overflow: hidden;
            display: flex;
            flex-direction: row; /* rettangolare orizzontale */
        }

        .event-card-image {
            flex: 0 0 38%;
            background-color: #000; /* per incorniciare banner larghi */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-card-image img {
            width: 100%;
            height: auto;
            max-height: 260px;
            object-fit: contain; /* non croppa l'immagine */
            display: block;
        }

        .event-card-body {
            flex: 1 1 auto;
            padding: 20px 24px 22px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .event-card-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 8px;
            color: #1f2933;
            white-space: normal;
            word-wrap: break-word;
        }

        .event-card-date {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .event-card-venue {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 16px;
        }

        .event-card-actions {
            margin-top: auto;
            text-align: right;
        }

        .event-card-actions .btn {
            display: inline-block;
            width: auto;
            min-width: 140px;
            border-radius: 9999px;
        }

        @media (max-width: 767px) {
            .home-hero {
                padding-top: 25px;
            }

            .home-hero h1 {
                font-size: 24px;
            }

            .event-card {
                flex-direction: column;
            }

            .event-card-image img {
                max-height: 220px;
            }
        }
    </style>
@stop

@section('content')
    <section id="intro" class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="home-hero">
                    <h1>Scopri tutti gli eventi</h1>
                    <p>Una panoramica completa degli eventi disponibili: scegli il tuo e acquista i biglietti in pochi click.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="events" class="container">
        {{-- Eventi in programma --}}
        <div class="row">
            <div class="col-xs-12">
                <h2 class="section-title">@lang('Public_ViewOrganiser.upcoming_events')</h2>
            </div>
        </div>
        <div class="row events-grid">
            @forelse($upcoming_events as $event)
                <div class="col-xs-12">
                    <div class="event-card">
                        @if(count($event->images))
                            <div class="event-card-image">
                                <a href="{{ $event->event_url }}">
                                    <img src="{{ asset($event->images->first()['image_path']) }}"
                                         alt="{{ $event->title }}">
                                </a>
                            </div>
                        @endif
                        <div class="event-card-body">
                            <h3 class="event-card-title ellipsis">
                                <a href="{{ $event->event_url }}">{{ $event->title }}</a>
                            </h3>
                            <div class="event-card-date">
                                {{ $event->start_date->format('d/m/Y H:i') }}
                            </div>
                            @if(!empty($event->venue_name))
                                <div class="event-card-venue">
                                    {{ $event->venue_name }}
                                </div>
                            @endif
                            <div class="event-card-actions">
                                <a href="{{ $event->event_url }}" class="btn btn-primary btn-block">
                                    @lang('Public_ViewOrganiser.tickets')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        @lang('Public_ViewOrganiser.no_events', ['panel_title' => trans('Public_ViewOrganiser.upcoming_events')])
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Eventi passati --}}
        <div class="row">
            <div class="col-xs-12">
                <h2 class="section-title">@lang('Public_ViewOrganiser.past_events')</h2>
            </div>
        </div>
        <div class="row events-grid">
            @forelse($past_events as $event)
                <div class="col-xs-12 col-sm-6 col-md-4">
                    <div class="event-card">
                        @if(count($event->images))
                            <div class="event-card-image">
                                <a href="{{ $event->event_url }}">
                                    <img src="{{ asset($event->images->first()['image_path']) }}"
                                         alt="{{ $event->title }}">
                                </a>
                            </div>
                        @endif
                        <div class="event-card-body">
                            <h3 class="event-card-title ellipsis">
                                <a href="{{ $event->event_url }}">{{ $event->title }}</a>
                            </h3>
                            <div class="event-card-date">
                                {{ $event->start_date->format('d/m/Y H:i') }}
                            </div>
                            @if(!empty($event->venue_name))
                                <div class="event-card-venue">
                                    {{ $event->venue_name }}
                                </div>
                            @endif
                            <div class="event-card-actions">
                                <a href="{{ $event->event_url }}" class="btn btn-default btn-block">
                                    @lang('Public_ViewOrganiser.information')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-xs-12">
                    <div class="alert alert-info">
                        @lang('Public_ViewOrganiser.no_events', ['panel_title' => trans('Public_ViewOrganiser.past_events')])
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@stop


