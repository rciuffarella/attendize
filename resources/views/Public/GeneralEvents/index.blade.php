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
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            margin-bottom: 25px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .event-card-image img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .event-card-body {
            padding: 15px 18px 18px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .event-card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 6px;
        }

        .event-card-date {
            font-size: 14px;
            color: #777;
            margin-bottom: 4px;
        }

        .event-card-venue {
            font-size: 14px;
            color: #555;
            margin-bottom: 12px;
        }

        .event-card-actions {
            margin-top: auto;
        }

        @media (max-width: 767px) {
            .home-hero {
                padding-top: 25px;
            }

            .home-hero h1 {
                font-size: 24px;
            }

            .event-card-image img {
                height: 160px;
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


