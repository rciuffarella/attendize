@if($event->is_seated && $event->seatMaps->count())
    @php
        $seatMap = $event->seatMaps->first();
        // Recupera i posti già assegnati a partecipanti per questo evento
        $assignedSeatIds = \App\Models\Attendee::where('event_id', $event->id)
            ->whereNotNull('seat_id')
            ->pluck('seat_id')
            ->toArray();
    @endphp
    <div class="panel panel-default" style="margin-bottom: 20px;">
        <div class="panel-heading">
            <h3 class="panel-title">
                Seleziona i posti (piantina sperimentale)
            </h3>
        </div>
        <div class="panel-body">
            @if($seatMap->zones->count())
                <p class="help-block">
                    Clicca sui posti disponibili per selezionarli. I posti selezionati verranno aggiunti automaticamente al riepilogo dei biglietti.
                </p>
                @php
                    $hasBg = !empty($seatMap->background_image_path);

                    if ($hasBg) {
                        // Modalità con piantina di sfondo: area un po' più alta
                        $mapHeight = 800;
                        $publicSeatMapBgStyle = "position:relative;height:{$mapHeight}px;width:900px;max-width:100%;border:1px dashed #e5e7eb;margin:0 auto 10px auto;box-sizing:border-box;background:#f9fafb;";
                        $publicSeatMapBgStyle .= " background-image:url('".$seatMap->background_image_path."'); background-size:contain; background-position:center; background-repeat:no-repeat;";
                    } else {
                        // Modalità standard originale (senza immagine di sfondo) – replica lo stile admin
                        $mapHeight = 520;
                        $publicSeatMapBgStyle = "position:relative;height:{$mapHeight}px;width:900px;max-width:100%;border:1px dashed #e5e7eb;margin:0 auto 10px auto;box-sizing:border-box;background:#f9fafb;";
                    }
                @endphp
                <div id="seat-map-wrapper" style="{{ $publicSeatMapBgStyle }}">
                    @foreach($seatMap->zones as $zone)
                        @php
                            // Calcolo dimensioni proporzionali al numero di posti
                            $groupedSeats = $zone->seats->groupBy('row_label')->sortKeys();
                            $rowCount = max(1, $groupedSeats->count());
                            $maxCols = 1;
                            foreach ($groupedSeats as $rowSeats) {
                                $maxCols = max($maxCols, $rowSeats->count());
                            }

                            if ($hasBg) {
                                // Con piantina di sfondo: slot più compatti (molti posti)
                                $cellW = 16;
                                $cellH = 20;
                                $baseW = 32;
                                $baseH = 32;
                            } else {
                                // Modalità standard: stessi parametri dell'anteprima admin
                                $cellW = 28;
                                $cellH = 32;
                                $baseW = 40;
                                $baseH = 40;
                            }
                            $blockWidth = $baseW + $maxCols * $cellW;
                            $blockMinHeight = $baseH + $rowCount * $cellH;

                            $style = 'margin-bottom: 15px;';
                            // larghezza fissa in proporzione al numero di posti, altezza minima (il contenuto può crescere oltre)
                            $style .= 'width:'.$blockWidth.'px;min-height:'.$blockMinHeight.'px;';
                            if(!is_null($zone->position_x) && !is_null($zone->position_y)) {
                                $style .= 'position:absolute; top:'.$zone->position_y.'%; left:'.$zone->position_x.'%;';
                            }
                        @endphp
                        <div class="seat-zone-block" style="{{ $style }}">
                            <strong style="display:block;margin-bottom:5px;">
                                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:{{ $zone->color ?: '#999' }};margin-right:5px;"></span>
                                {{ $zone->name }}
                            </strong>
                            <div class="seat-grid">
                                @foreach($groupedSeats as $rowLabel => $rowSeats)
                                    <div class="seat-row">
                                        <span class="seat-row-label">{{ $rowLabel }}</span>
                                        @foreach($rowSeats->sortBy('seat_number') as $seat)
                                            @php
                                                $isTaken = $seat->status !== 'free' || in_array($seat->id, $assignedSeatIds, true);
                                            @endphp
                                            <button type="button"
                                                    class="btn btn-xs seat-btn {{ $isTaken ? 'seat-taken' : 'seat-free' }}"
                                                    data-seat-id="{{ $seat->id }}"
                                                    data-ticket-id="{{ $zone->ticket_id }}"
                                                    data-zone-color="{{ $zone->color ?: '#2563eb' }}"
                                                    {{ $isTaken ? 'disabled' : '' }}>
                                                @if($isTaken)
                                                    ✕
                                                @else
                                                    {{ $seat->seat_number }}
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="selected_seat_ids" id="selected_seat_ids" value="">

                <style>
                    #seat-map-wrapper {
                        position: relative;
                        /* altezza iniziale calcolata via PHP in base alla capienza;
                           qui verrà solo eventualmente aumentata via JS se i box superano l'area */
                        box-sizing: border-box;
                    }
                    #seat-map-wrapper .seat-row { margin-bottom: 4px; white-space: nowrap; }
                    #seat-map-wrapper .seat-row-label {
                        display: inline-block;
                        width: 18px;
                        font-size: 11px;
                        text-align: right;
                        margin-right: 4px;
                    }
                    #seat-map-wrapper .seat-btn {
                        margin: 1px;
                        border-radius: 50%;
                        border: 1px solid #a3a3a3;
                        background-color: #c3c3c3;
                        color: #111827;
                        box-shadow: 0 0 0 1px rgba(255,255,255,0.7);
                        transition: background-color .12s ease, transform .05s ease;
                    }
                    @if($hasBg)
                        #seat-map-wrapper .seat-btn {
                            min-width: 18px;
                            height: 18px;
                            line-height: 16px;
                            padding: 0;
                            font-size: 10px;
                        }
                    @else
                        #seat-map-wrapper .seat-btn {
                            min-width: 24px;
                            height: 24px;
                            line-height: 20px;
                            padding: 0;
                            font-size: 11px;
                        }
                    @endif
                    #seat-map-wrapper .seat-free:hover {
                        background-color: #d4d4d4;
                    }
                    #seat-map-wrapper .seat-selected {
                        /* colore di fallback, se per qualche motivo manca data-zone-color */
                        background-color: #22c55e;
                        color: #111827;
                        border-color: #16a34a;
                        font-weight: 600;
                        text-shadow: 0 0 1px rgba(255,255,255,0.8);
                    }
                    #seat-map-wrapper .seat-taken {
                        background-color: #e5e7eb;
                        color: #b91c1c;
                        border-color: #b91c1c;
                        cursor: not-allowed;
                        opacity: 0.9;
                        font-weight: 700;
                    }
                </style>

                <script>
                    (function () {
                        // Adatta dinamicamente l'altezza del wrapper in base ai box effettivi
                        var wrapper = document.getElementById('seat-map-wrapper');
                        if (wrapper) {
                            var blocks = wrapper.getElementsByClassName('seat-zone-block');
                            var maxBottom = 0;
                            for (var i = 0; i < blocks.length; i++) {
                                var rect = blocks[i].getBoundingClientRect();
                                var parentRect = wrapper.getBoundingClientRect();
                                var bottom = rect.bottom - parentRect.top;
                                if (bottom > maxBottom) {
                                    maxBottom = bottom;
                                }
                            }
                            if (maxBottom > 0) {
                                // margine extra per sicurezza
                                wrapper.style.height = (maxBottom + 30) + 'px';
                            }
                        }

                        var selected = [];

                        function updateSelectedField() {
                            document.getElementById('selected_seat_ids').value = selected.join(',');

                            // Calcola quanti posti sono stati selezionati per ciascun ticket_id
                            var counts = {};
                            selected.forEach(function (entry) {
                                var parts = entry.split(':');
                                if (parts.length !== 2) return;
                                var ticketId = parts[0];
                                counts[ticketId] = (counts[ticketId] || 0) + 1;
                            });

                            // Aggiorna tutti i select dei biglietti:
                            // - se esistono posti selezionati per quel ticket_id → imposta quella quantità
                            // - altrimenti → imposta 0 (se l'opzione 0 esiste)
                            var selects = document.querySelectorAll('select[name^=\"ticket_\"]');
                            Array.prototype.forEach.call(selects, function (select) {
                                var name = select.getAttribute('name') || '';
                                var ticketId = name.replace('ticket_', '');
                                var desired = counts[ticketId] || 0;
                                var desiredStr = String(desired);

                                // Cambia il valore solo se l'opzione esiste
                                var option = select.querySelector('option[value=\"' + desiredStr + '\"]');
                                if (option) {
                                    select.value = desiredStr;
                                }
                            });
                        }

                        document.addEventListener('click', function (e) {
                            if (!e.target.classList.contains('seat-btn') || e.target.classList.contains('seat-taken')) {
                                return;
                            }
                            var seatId = e.target.getAttribute('data-seat-id');
                            var ticketId = e.target.getAttribute('data-ticket-id');
                            if (!ticketId) {
                                return;
                            }
                            var compoundId = ticketId + ':' + seatId;
                            var idx = selected.indexOf(compoundId);
                            if (idx === -1) {
                                selected.push(compoundId);
                                e.target.classList.add('seat-selected');

                                // Colore selezione basato sul colore della zona
                                var zoneColor = e.target.getAttribute('data-zone-color') || '#22c55e';
                                e.target.style.backgroundColor = zoneColor;
                                e.target.style.color = '#111827';
                                e.target.style.borderColor = zoneColor;
                            } else {
                                selected.splice(idx, 1);
                                e.target.classList.remove('seat-selected');

                                // Ripristina stile di base per posti liberi
                                e.target.style.backgroundColor = '';
                                e.target.style.color = '';
                                e.target.style.borderColor = '';
                            }
                            updateSelectedField();
                        });
                    })();
                </script>
            @else
                <p class="text-muted">Nessuna zona definita per questa piantina.</p>
            @endif
        </div>
    </div>
@endif


