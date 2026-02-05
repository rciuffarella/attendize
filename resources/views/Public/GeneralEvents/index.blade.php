@extends('Public.Layouts.PublicPage')

@section('title')
    EventiOne - @lang('Public_ViewOrganiser.upcoming_events')
@overwrite

@section('head')
    <style>
        body { background-color: #f9fafb !important; }
        
        /* Hero Banner */
        .hero-banner {
            position: relative;
            background: url('{{ asset("banner.jpeg") }}') center center no-repeat;
            background-size: cover;
            background-attachment: scroll;
            color: #ffffff;
            padding: 80px 15px 60px;
            text-align: center;
            margin-bottom: 50px;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            pointer-events: none;
        }
        .hero-banner .container {
            position: relative;
            z-index: 1;
        }

        .hero-banner h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hero-banner p {
            font-size: 20px;
            opacity: 0.95;
            margin-bottom: 40px;
        }

        /* Search Box */
        .search-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box {
            display: flex;
            background: #ffffff;
            border-radius: 50px;
            padding: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .search-box input {
            flex: 1;
            border: none;
            padding: 16px 24px;
            font-size: 16px;
            border-radius: 50px;
            outline: none;
        }

        .search-box button {
            background: #667eea;
            color: #ffffff;
            border: none;
            padding: 16px 32px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #5568d3;
        }

        /* Categories Section */
        .categories-section {
            background: #ffffff;
            padding: 50px 0;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #111827;
            text-align: center;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .category-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .category-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: currentColor;
        }

        .category-box-icon {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
        }

        .category-box-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .category-box-desc {
            font-size: 13px;
            opacity: 0.7;
            color: #6b7280;
        }

        /* Featured Events */
        .featured-section {
            background: #ffffff;
            padding: 50px 0;
            margin-bottom: 50px;
        }

        .featured-events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .featured-event-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .featured-event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .featured-event-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            object-position: center;
            background: #000;
        }

        .featured-event-body {
            padding: 20px;
        }

        .featured-event-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #111827;
        }

        .featured-event-date {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .featured-event-venue {
            font-size: 14px;
            color: #4b5563;
        }

        /* Events Section */
        .events-section {
            background: #ffffff;
            padding: 50px 0;
        }

        .events-grid {
            margin-bottom: 40px;
        }

        .events-grid .row {
            display: flex;
            flex-wrap: wrap;
        }

        .events-grid .row > [class*="col-"] {
            display: flex;
        }

        .event-card {
            width: 100%;
            height: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(15,23,42,.12);
            margin-bottom: 25px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15,23,42,.16);
        }

        .event-card-image {
            flex: 0 0 auto;
            width: 100%;
            height: 200px;
            background-color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-card-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .event-card-body {
            flex: 1 1 auto;
            min-height: 0;
            padding: 24px;
            display: flex;
            flex-direction: column;
        }

        .event-card-title {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 10px;
            color: #111827;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .event-card-title a {
            color: #111827;
            text-decoration: none;
        }

        .event-card-title a:hover {
            color: #667eea;
        }

        .event-card-date {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .event-card-venue {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 20px;
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
            padding: 10px 24px;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .no-results-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 767px) {
            .hero-banner {
                padding: 50px 15px 40px;
            }

            .hero-banner h1 {
                font-size: 32px;
            }

            .hero-banner p {
                font-size: 16px;
            }

            .search-box {
                flex-direction: column;
                border-radius: 12px;
            }

            .search-box input,
            .search-box button {
                border-radius: 12px;
                margin: 4px 0;
            }

            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .category-box {
                padding: 20px 12px;
            }

            .category-box-icon {
                font-size: 36px;
            }

            .category-box-name {
                font-size: 16px;
            }

            .featured-events-grid {
                grid-template-columns: 1fr;
            }

            .event-card-image {
                height: 180px;
            }
        }
    </style>
@stop

@section('content')
    {{-- Hero Banner con ricerca --}}
    <section class="hero-banner">
        <div class="container">
            <h1>Cose da fare: eventi, esperienze e molto altro</h1>
            <p>Scopri i migliori eventi nella tua città</p>
            
            <div class="search-container">
                <form method="GET" action="{{ route('index') }}" class="search-box">
                    <input type="text" 
                           name="search" 
                           placeholder="Cerca eventi, luoghi, categorie..." 
                           value="{{ $search_term }}"
                           autocomplete="off">
                    <button type="submit">Cerca</button>
                </form>
            </div>
        </div>
    </section>

    {{-- Box Categorie --}}
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Categorie</h2>
            <div class="categories-grid">
                @foreach($categories as $category)
                    <div class="category-box" 
                         style="color: {{ $category['color'] }};"
                         onclick="window.location.href='{{ route('index') }}?category={{ urlencode($category['name']) }}'">
                        <span class="category-box-icon">
                            <i class="{{ $category['icon'] }}"></i>
                        </span>
                        <div class="category-box-name">{{ $category['name'] }}</div>
                        <div class="category-box-desc">{{ $category['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Top 5 Eventi in evidenza (solo se non c'è ricerca o filtro categoria) --}}
    @if(empty($search_term) && empty($category_filter) && $featured_events->count() > 0)
        <section class="featured-section">
            <div class="container">
                <h2 class="section-title">La top {{ $featured_events->count() }}</h2>
                <div class="featured-events-grid">
                    @foreach($featured_events as $event)
                        <a href="{{ $event->event_url }}" class="featured-event-card">
                            @if(count($event->images))
                                <img src="{{ asset($event->images->first()['image_path']) }}" 
                                     alt="{{ $event->title }}"
                                     class="featured-event-image">
                            @else
                                <div class="featured-event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px;">
                                    <i class="ico-calendar"></i>
                                </div>
                            @endif
                            <div class="featured-event-body">
                                <h3 class="featured-event-title">{{ $event->title }}</h3>
                                <div class="featured-event-date">
                                    {{ $event->start_date->format('d/m/Y H:i') }}
                                </div>
                                @if(!empty($event->venue_name))
                                    <div class="featured-event-venue">{{ $event->venue_name }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Eventi in programma --}}
    <section class="events-section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="section-title">
                        @if(!empty($category_filter))
                            Categoria: {{ $category_filter }}
                        @elseif(!empty($search_term))
                            Risultati ricerca
                        @else
                            @lang('Public_ViewOrganiser.upcoming_events')
                        @endif
                    </h2>
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
                            @else
                                <div class="event-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 48px;">
                                    <i class="ico-calendar"></i>
                                </div>
                            @endif
                            <div class="event-card-body">
                                <h3 class="event-card-title">
                                    <a href="{{ $event->event_url }}">{{ $event->title }}</a>
                                </h3>
                                <div class="event-card-date">
                                    <i class="ico-calendar"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                                </div>
                                @if(!empty($event->venue_name))
                                    <div class="event-card-venue">
                                        <i class="ico-location"></i> {{ $event->venue_name }}
                                    </div>
                                @endif
                                <div class="event-card-actions">
                                    <a href="{{ $event->event_url }}" class="btn btn-primary">
                                        @lang('Public_ViewOrganiser.tickets')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-xs-12">
                        <div class="no-results">
                            <div class="no-results-icon">
                                <i class="ico-search"></i>
                            </div>
                            <h3>Nessun evento trovato</h3>
                            <p>
                                @if(!empty($search_term))
                                    Prova a cercare con altri termini o <a href="{{ route('index') }}">visualizza tutti gli eventi</a>.
                                @else
                                    @lang('Public_ViewOrganiser.no_events', ['panel_title' => trans('Public_ViewOrganiser.upcoming_events')])
                                @endif
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Eventi passati (solo se non c'è ricerca o filtro categoria) --}}
            @if(empty($search_term) && empty($category_filter) && $past_events->count() > 0)
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="section-title">@lang('Public_ViewOrganiser.past_events')</h2>
                    </div>
                </div>
                <div class="row events-grid">
                    @foreach($past_events as $event)
                        <div class="col-xs-12 col-sm-6 col-md-4">
                            <div class="event-card">
                                @if(count($event->images))
                                    <div class="event-card-image">
                                        <a href="{{ $event->event_url }}">
                                            <img src="{{ asset($event->images->first()['image_path']) }}"
                                                 alt="{{ $event->title }}">
                                        </a>
                                    </div>
                                @else
                                    <div class="event-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 36px;">
                                        <i class="ico-calendar"></i>
                                    </div>
                                @endif
                                <div class="event-card-body">
                                    <h3 class="event-card-title">
                                        <a href="{{ $event->event_url }}">{{ $event->title }}</a>
                                    </h3>
                                    <div class="event-card-date">
                                        <i class="ico-calendar"></i> {{ $event->start_date->format('d/m/Y H:i') }}
                                    </div>
                                    @if(!empty($event->venue_name))
                                        <div class="event-card-venue">
                                            <i class="ico-location"></i> {{ $event->venue_name }}
                                        </div>
                                    @endif
                                    <div class="event-card-actions">
                                        <a href="{{ $event->event_url }}" class="btn btn-default">
                                            @lang('Public_ViewOrganiser.information')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@stop
