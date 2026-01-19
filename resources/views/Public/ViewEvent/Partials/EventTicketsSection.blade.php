<section id="tickets" class="container">
    <div class="row">
        <h1 class='section_head'>
            @lang("Public_ViewEvent.tickets")
        </h1>
    </div>

    @if($event->end_date->isPast())
        <div class="alert alert-boring">
            @lang("Public_ViewEvent.event_already", ['started' => trans('Public_ViewEvent.event_already_ended')])
        </div>
    @else

        @if($tickets->count() > 0)

            {!! Form::open(['url' => route('postValidateTickets', ['event_id' => $event->id]), 'class' => 'ajax']) !!}
            <div class="row">
                <div class="col-md-12">
                    <div class="content">

                        {{-- Selettore data/orario se l'evento ha più date --}}
                        @php
                            try {
                                $activeDates = method_exists($event, 'activeEventDates') ? $event->activeEventDates : collect();
                                $hasMultipleDates = $activeDates && $activeDates->count() > 0;
                            } catch (\Exception $e) {
                                $activeDates = collect();
                                $hasMultipleDates = false;
                            }
                        @endphp

                        @if($hasMultipleDates)
                            <div class="event-date-selector" style="margin-bottom: 40px;">
                                <h3 style="margin-bottom: 25px; font-weight: 600; color: #333;">
                                    <i class="ico-calendar" style="margin-right: 8px;"></i> Scegli data e ora
                                </h3>
                                
                                <div class="date-selection-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                                    @foreach($activeDates as $date)
                                        @php
                                            $dayNames = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];
                                            $dayName = $dayNames[$date->start_date->dayOfWeek];
                                            $monthNames = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
                                            $monthName = $monthNames[$date->start_date->month - 1];
                                        @endphp
                                        <div class="date-card" 
                                             data-date-id="{{ $date->id }}"
                                             style="
                                                 border: 2px solid #e0e0e0;
                                                 border-radius: 12px;
                                                 padding: 18px;
                                                 cursor: pointer;
                                                 transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                                 background: #fff;
                                                 text-align: center;
                                                 position: relative;
                                                 overflow: hidden;
                                             ">
                                            
                                            <div class="date-weekday" style="font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 6px; font-weight: 600; letter-spacing: 0.5px;">
                                                {{ $dayName }}
                                            </div>
                                            
                                            <div class="date-day" style="font-size: 32px; font-weight: 700; color: #333; margin-bottom: 4px; line-height: 1;">
                                                {{ $date->start_date->format('d') }}
                                            </div>
                                            
                                            <div class="date-month" style="font-size: 13px; color: #666; text-transform: uppercase; margin-bottom: 12px; font-weight: 500;">
                                                {{ $monthName }}
                                            </div>
                                            
                                            <div class="date-time" style="font-size: 13px; color: #007bff; font-weight: 600; margin-bottom: 10px; padding-top: 12px; border-top: 1px solid #f0f0f0;">
                                                <i class="ico-clock" style="margin-right: 5px; font-size: 14px;"></i>
                                                {{ $date->start_date->format('H:i') }} - {{ $date->end_date->format('H:i') }}
                                            </div>
                                            
                                            @if($date->quantity_available)
                                                <div class="date-availability" style="font-size: 11px; color: #28a745; margin-top: 8px; font-weight: 500;">
                                                    <i class="ico-ticket" style="margin-right: 3px;"></i> {{ $date->quantity_available }} disponibili
                                                </div>
                                            @else
                                                <div class="date-availability" style="font-size: 11px; color: #28a745; margin-top: 8px; font-weight: 500;">
                                                    <i class="ico-checkmark" style="margin-right: 3px;"></i> Disponibile
                                                </div>
                                            @endif
                                            
                                            <div class="selected-indicator" style="display: none; position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 50%; color: white; line-height: 28px; font-size: 16px; box-shadow: 0 2px 8px rgba(0,123,255,0.3);">
                                                <i class="ico-checkmark"></i>
                                            </div>
                                            
                                            <div class="card-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,123,255,0.05) 0%, rgba(0,86,179,0.05) 100%); opacity: 0; transition: opacity 0.3s ease; pointer-events: none;"></div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <input type="hidden" name="event_date_id" id="event_date_id" required>
                                
                                <div class="date-selection-error" style="display: none; color: #dc3545; font-size: 14px; margin-top: 10px;">
                                    <i class="ico-warning"></i> Seleziona una data per continuare
                                </div>
                            </div>
                            
                            <style>
                                .date-card {
                                    position: relative;
                                }
                                
                                .date-card:hover {
                                    border-color: #007bff !important;
                                    box-shadow: 0 6px 20px rgba(0,123,255,0.15) !important;
                                    transform: translateY(-2px);
                                }
                                
                                .date-card:hover .card-overlay {
                                    opacity: 1 !important;
                                }
                                
                                .date-card.selected {
                                    border-color: #007bff !important;
                                    background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%) !important;
                                    box-shadow: 0 6px 20px rgba(0,123,255,0.25) !important;
                                    transform: translateY(-2px);
                                }
                                
                                .date-card.selected .selected-indicator {
                                    display: block !important;
                                }
                                
                                .date-card.selected .card-overlay {
                                    opacity: 1 !important;
                                }
                                
                                .date-card.selected .date-day {
                                    color: #007bff !important;
                                }
                                
                                .date-card.selected .date-time {
                                    color: #0056b3 !important;
                                }
                                
                                @media (max-width: 768px) {
                                    .date-selection-grid {
                                        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
                                        gap: 12px !important;
                                    }
                                    .date-card {
                                        padding: 15px 12px !important;
                                    }
                                    .date-card .date-day {
                                        font-size: 28px !important;
                                    }
                                }
                                
                                @media (max-width: 480px) {
                                    .date-selection-grid {
                                        grid-template-columns: repeat(2, 1fr) !important;
                                    }
                                }
                            </style>
                            
                            <style>
                                /* Preloader per l'aggiornamento della mappa */
                                .seat-map-preloader {
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    right: 0;
                                    bottom: 0;
                                    background: rgba(255, 255, 255, 0.9);
                                    display: none;
                                    align-items: center;
                                    justify-content: center;
                                    z-index: 1000;
                                    border-radius: 8px;
                                }
                                
                                .seat-map-preloader.active {
                                    display: flex;
                                }
                                
                                .seat-map-preloader .spinner {
                                    width: 50px;
                                    height: 50px;
                                    border: 4px solid #f3f3f3;
                                    border-top: 4px solid #007bff;
                                    border-radius: 50%;
                                    animation: spin 1s linear infinite;
                                }
                                
                                .seat-map-preloader .spinner-text {
                                    margin-top: 15px;
                                    color: #007bff;
                                    font-weight: 600;
                                    font-size: 14px;
                                }
                                
                                @keyframes spin {
                                    0% { transform: rotate(0deg); }
                                    100% { transform: rotate(360deg); }
                                }
                                
                                .seat-map-container {
                                    position: relative;
                                }
                            </style>
                            
                            <script>
                                // Funzione per mostrare/nascondere il preloader
                                function showSeatMapPreloader(show) {
                                    let preloader = document.getElementById('seat-map-preloader');
                                    if (!preloader) {
                                        // Crea il preloader se non esiste
                                        const seatMapContainer = document.querySelector('.seat-map-container') || 
                                                               document.getElementById('seat-map-wrapper') ||
                                                               document.querySelector('.panel-body');
                                        if (seatMapContainer) {
                                            preloader = document.createElement('div');
                                            preloader.id = 'seat-map-preloader';
                                            preloader.className = 'seat-map-preloader';
                                            preloader.innerHTML = '<div style="text-align: center;"><div class="spinner"></div><div class="spinner-text">Aggiornamento mappa...</div></div>';
                                            
                                            // Posiziona il preloader
                                            if (seatMapContainer.style.position !== 'relative' && 
                                                seatMapContainer.style.position !== 'absolute') {
                                                seatMapContainer.style.position = 'relative';
                                            }
                                            seatMapContainer.appendChild(preloader);
                                        }
                                    }
                                    
                                    if (preloader) {
                                        if (show) {
                                            preloader.classList.add('active');
                                        } else {
                                            preloader.classList.remove('active');
                                        }
                                    }
                                }
                                
                                // Funzione per aggiornare la mappa dei posti in base alla data selezionata
                                window.updateSeatMapForDate = function(eventDateId) {
                                        console.log('updateSeatMapForDate chiamata con eventDateId:', eventDateId);
                                        if (!eventDateId) {
                                            console.log('eventDateId non valido, esco');
                                            return;
                                        }
                                        
                                        // Mostra il preloader
                                        showSeatMapPreloader(true);
                                        
                                        // Aspetta che la mappa sia caricata
                                        const seatMapWrapper = document.getElementById('seat-map-wrapper');
                                        if (!seatMapWrapper) {
                                            console.log('seat-map-wrapper non trovato, aspetto...');
                                            setTimeout(function() {
                                                window.updateSeatMapForDate(eventDateId);
                                            }, 500);
                                            return;
                                        }
                                        
                                        const seatButtons = document.querySelectorAll('.seat-btn');
                                        if (seatButtons.length === 0) {
                                            console.log('Nessun posto trovato nella mappa, aspetto...');
                                            setTimeout(function() {
                                                window.updateSeatMapForDate(eventDateId);
                                            }, 500);
                                            return;
                                        }
                                        
                                        const url = '{{ url("api/events/" . $event->id . "/occupied-seats") }}?event_date_id=' + eventDateId;
                                        console.log('Chiamata AJAX a:', url);
                                        
                                        // Fai una chiamata AJAX per recuperare i posti occupati per questa data
                                        fetch(url, {
                                            method: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => {
                                            console.log('Risposta ricevuta:', response.status);
                                            const contentType = response.headers.get('content-type');
                                            console.log('Content-Type:', contentType);
                                            
                                            if (!contentType || !contentType.includes('application/json')) {
                                                return response.text().then(text => {
                                                    console.error('Risposta non JSON:', text.substring(0, 200));
                                                    throw new Error('Risposta non JSON ricevuta');
                                                });
                                            }
                                            
                                            return response.json();
                                        })
                                        .then(data => {
                                            console.log('Dati ricevuti:', data);
                                            if (data.status === 'success' && data.occupied_seats) {
                                                const occupiedSeats = data.occupied_seats;
                                                console.log('Posti occupati per questa data:', occupiedSeats);
                                                
                                                // Aggiorna tutti i posti nella mappa
                                                const seatButtons = document.querySelectorAll('.seat-btn');
                                                console.log('Trovati', seatButtons.length, 'posti nella mappa');
                                                seatButtons.forEach(btn => {
                                                    const seatId = parseInt(btn.getAttribute('data-seat-id'));
                                                    // Converti gli occupiedSeats in array di numeri per il confronto
                                                    const occupiedSeatsNumbers = occupiedSeats.map(id => parseInt(id));
                                                    const isOccupied = occupiedSeatsNumbers.includes(seatId);
                                                    
                                                    if (isOccupied) {
                                                        btn.classList.add('seat-taken');
                                                        btn.classList.remove('seat-free', 'seat-selected');
                                                        btn.disabled = true;
                                                        btn.innerHTML = '✕';
                                                        btn.setAttribute('data-seat-taken', '1');
                                                        
                                                        // Rimuovi dalla selezione se era selezionato
                                                        const ticketId = btn.getAttribute('data-ticket-id');
                                                        const compoundId = ticketId + ':' + seatId;
                                                        const selectedField = document.getElementById('selected_seat_ids');
                                                        if (selectedField) {
                                                            let selected = selectedField.value ? selectedField.value.split(',') : [];
                                                            const idx = selected.indexOf(compoundId);
                                                            if (idx !== -1) {
                                                                selected.splice(idx, 1);
                                                                selectedField.value = selected.join(',');
                                                            }
                                                        }
                                                    } else {
                                                        btn.classList.remove('seat-taken');
                                                        btn.classList.add('seat-free');
                                                        btn.disabled = false;
                                                        const seatNumber = btn.getAttribute('data-seat-number');
                                                        if (seatNumber && seatNumber !== '✕') {
                                                            btn.innerHTML = seatNumber;
                                                        }
                                                        btn.setAttribute('data-seat-taken', '0');
                                                        
                                                        // Ripristina lo stile se non era selezionato
                                                        if (!btn.classList.contains('seat-selected')) {
                                                            btn.style.backgroundColor = '';
                                                            btn.style.color = '';
                                                            btn.style.borderColor = '';
                                                        }
                                                    }
                                                });
                                            }
                                            
                                            // Nascondi il preloader
                                            showSeatMapPreloader(false);
                                        })
                                        .catch(error => {
                                            console.error('Errore nel recupero dei posti occupati:', error);
                                            // Nascondi il preloader anche in caso di errore
                                            showSeatMapPreloader(false);
                                        });
                                }
                                
                                document.addEventListener('DOMContentLoaded', function() {
                                    const dateCards = document.querySelectorAll('.date-card');
                                    const hiddenInput = document.getElementById('event_date_id');
                                    const errorMsg = document.querySelector('.date-selection-error');
                                    
                                    // Seleziona automaticamente la prima data se nessuna è selezionata
                                    if (dateCards.length > 0 && (!hiddenInput || !hiddenInput.value)) {
                                        const firstCard = dateCards[0];
                                        const firstDateId = firstCard.getAttribute('data-date-id');
                                        firstCard.classList.add('selected');
                                        if (hiddenInput) {
                                            hiddenInput.value = firstDateId;
                                        }
                                        // Aggiorna la mappa per la prima data
                                        if (typeof window.updateSeatMapForDate === 'function') {
                                            window.updateSeatMapForDate(firstDateId);
                                        }
                                    } else if (hiddenInput && hiddenInput.value) {
                                        // Se c'è già una data selezionata, aggiorna la mappa
                                        if (typeof window.updateSeatMapForDate === 'function') {
                                            window.updateSeatMapForDate(hiddenInput.value);
                                        }
                                    }
                                    
                                    dateCards.forEach(card => {
                                        card.addEventListener('click', function() {
                                            // Rimuovi selezione da tutte le card
                                            dateCards.forEach(c => {
                                                c.classList.remove('selected');
                                            });
                                            
                                            // Aggiungi selezione alla card cliccata
                                            this.classList.add('selected');
                                            
                                            // Imposta il valore nel campo hidden
                                            const selectedDateId = this.getAttribute('data-date-id');
                                            hiddenInput.value = selectedDateId;
                                            
                                            // Nascondi messaggio di errore
                                            if(errorMsg) errorMsg.style.display = 'none';
                                            
                                            // Aggiorna la mappa dei posti per la data selezionata
                                            console.log('Data selezionata, aggiorno mappa con ID:', selectedDateId);
                                            if (typeof window.updateSeatMapForDate === 'function') {
                                                window.updateSeatMapForDate(selectedDateId);
                                            } else {
                                                console.error('updateSeatMapForDate non è una funzione!');
                                                // Riprova dopo un breve delay
                                                setTimeout(function() {
                                                    if (typeof window.updateSeatMapForDate === 'function') {
                                                        window.updateSeatMapForDate(selectedDateId);
                                                    }
                                                }, 100);
                                            }
                                            
                                            // Scroll leggero per evidenziare la selezione
                                            this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                        });
                                    });
                                    
                                    // Validazione al submit
                                    const form = document.querySelector('form.ajax');
                                    if(form) {
                                        form.addEventListener('submit', function(e) {
                                            if(!hiddenInput.value) {
                                                e.preventDefault();
                                                if(errorMsg) {
                                                    errorMsg.style.display = 'block';
                                                    errorMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                }
                                                // Scroll alla sezione date
                                                const dateSelector = document.querySelector('.event-date-selector');
                                                if(dateSelector) {
                                                    dateSelector.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                                }
                                                return false;
                                            }
                                        });
                                    }
                                });
                            </script>
                        @else
                            {{-- Se non ci sono date multiple, usa la data principale dell'evento --}}
                            {!! Form::hidden('event_date_id', '') !!}
                        @endif

                        {{-- Per eventi con posti numerati mostriamo la piantina qui, prima della tabella biglietti --}}
                        @if($event->is_seated)
                            @include('Public.ViewEvent.Partials.SeatMapSelector')
                        @endif

                        <div class="tickets_table_wrap">
                            <table class="table">
                                <?php
                                $is_free_event = true;
                                ?>
                                @foreach($tickets->where('is_hidden', false) as $ticket)
                                    <tr class="ticket" property="offers" typeof="Offer">
                                        <td>
                                <span class="ticket-title semibold" property="name">
                                    {{$ticket->title}}
                                </span>
                                            <p class="ticket-descripton mb0 text-muted" property="description">
                                                {{$ticket->description}}
                                            </p>
                                        </td>
                                        <td style="width:200px; text-align: right;">
                                            <div class="ticket-pricing" style="margin-right: 20px;">
                                                @if($ticket->is_free)
                                                    @lang("Public_ViewEvent.free")
                                                    <meta property="price" content="0">
                                                @else
                                                    <?php
                                                    $is_free_event = false;
                                                    ?>
                                                    <span title='{{money($ticket->price, $event->currency)}} @lang("Public_ViewEvent.ticket_price") + {{money($ticket->total_booking_fee, $event->currency)}} @lang("Public_ViewEvent.booking_fees")'>{{money($ticket->total_price, $event->currency)}} </span>
                                                    <span class="tax-amount text-muted text-smaller">{{ ($event->organiser->tax_name && $event->organiser->tax_value) ? '(+'.money(($ticket->total_price*($event->organiser->tax_value)/100), $event->currency).' '.$event->organiser->tax_name.')' : '' }}</span>
                                                    <meta property="priceCurrency"
                                                          content="{{ $event->currency->code }}">
                                                    <meta property="price"
                                                          content="{{ number_format($ticket->price, 2, '.', '') }}">
                                                @endif
                                            </div>
                                        </td>
                                        <td style="width:85px;">
                                            @if($ticket->is_paused)

                                                <span class="text-danger">
                                    @lang("Public_ViewEvent.currently_not_on_sale")
                                </span>

                                            @else

                                                @if($ticket->sale_status === config('attendize.ticket_status_sold_out'))
                                                    <span class="text-danger" property="availability"
                                                          content="http://schema.org/SoldOut">
                                    @lang("Public_ViewEvent.sold_out")
                                </span>
                                                @elseif($ticket->sale_status === config('attendize.ticket_status_before_sale_date'))
                                                    <span class="text-danger">
                                    @lang("Public_ViewEvent.sales_have_not_started")
                                </span>
                                                @elseif($ticket->sale_status === config('attendize.ticket_status_after_sale_date'))
                                                    <span class="text-danger">
                                    @lang("Public_ViewEvent.sales_have_ended")
                                </span>
                                                @else
                                                    {!! Form::hidden('tickets[]', $ticket->id) !!}
                                                    <meta property="availability" content="http://schema.org/InStock">
                                                    <select name="ticket_{{$ticket->id}}" class="form-control"
                                                            style="text-align: center">
                                                        @if ($tickets->count() > 1)
                                                            <option value="0">0</option>
                                                        @endif
                                                        @for($i=$ticket->min_per_person; $i<=$ticket->max_per_person; $i++)
                                                            <option value="{{$i}}">{{$i}}</option>
                                                        @endfor
                                                    </select>
                                                @endif

                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($tickets->where('is_hidden', true)->count() > 0)
                                <tr class="has-access-codes" data-url="{{route('postShowHiddenTickets', ['event_id' => $event->id])}}">
                                    <td colspan="3"  style="text-align: left">
                                        @lang("Public_ViewEvent.has_unlock_codes")
                                        <div class="form-group" style="display:inline-block;margin-bottom:0;margin-left:15px;">
                                            {!!  Form::text('unlock_code', null, [
                                            'class' => 'form-control',
                                            'id' => 'unlock_code',
                                            'style' => 'display:inline-block;width:65%;text-transform:uppercase;',
                                            'placeholder' => 'ex: UNLOCKCODE01',
                                        ]) !!}
                                            {!! Form::button(trans("basic.apply"), [
                                                'class' => "btn btn-success",
                                                'id' => 'apply_access_code',
                                                'style' => 'display:inline-block;margin-top:-2px;',
                                                'data-dismiss' => 'modal',
                                            ]) !!}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="3" style="text-align: center">
                                        @lang("Public_ViewEvent.below_tickets")
                                    </td>
                                </tr>
                                <tr class="checkout">
                                    <td colspan="3">
                                        @if(!$is_free_event)
                                            <div class="hidden-xs pull-left">
                                                <img class=""
                                                     src="{{asset('assets/images/public/EventPage/credit-card-logos.png')}}"/>
                                                @if($event->enable_offline_payments)

                                                    <div class="help-block" style="font-size: 11px;">
                                                        @lang("Public_ViewEvent.offline_payment_methods_available")
                                                    </div>
                                                @endif
                                            </div>

                                        @endif
                                        {!!Form::submit(trans("Public_ViewEvent.register"), ['class' => 'btn btn-lg btn-primary pull-right'])!!}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::hidden('is_embedded', $is_embedded) !!}
            {!! Form::close() !!}

        @else

            <div class="alert alert-boring">
                @lang("Public_ViewEvent.tickets_are_currently_unavailable")
            </div>

        @endif

    @endif

</section>
