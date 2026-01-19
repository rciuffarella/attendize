@if(!isset($event))
    <div class="alert alert-danger">
        <p>Errore: evento non trovato.</p>
    </div>
@else
<div class="panel panel-default">
    <div class="panel-heading">
        <h4>Gestione Date e Orari</h4>
        <p class="text-muted">Aggiungi più date e orari per il tuo evento. I biglietti saranno disponibili per tutte le date.</p>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <h5>Date esistenti</h5>
                <div id="event-dates-list">
                    @php
                        $eventDates = collect();
                        if (isset($event) && is_object($event)) {
                            try {
                                if (method_exists($event, 'eventDates')) {
                                    $eventDates = $event->eventDates ?? collect();
                                } elseif (isset($event->eventDates)) {
                                    $eventDates = $event->eventDates;
                                }
                            } catch (\Exception $e) {
                                $eventDates = collect();
                            }
                        }
                    @endphp
                    @if($eventDates && $eventDates->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data inizio</th>
                                    <th>Data fine</th>
                                    <th>Disponibilità</th>
                                    <th>Stato</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($eventDates)
                                    @foreach($eventDates as $eventDate)
                                        <tr data-date-id="{{ $eventDate->id }}">
                                            <td>
                                                @if(method_exists($eventDate, 'getFormattedStartDate'))
                                                    {{ $eventDate->getFormattedStartDate('d/m/Y H:i') }}
                                                @else
                                                    {{ $eventDate->start_date ? $eventDate->start_date->format('d/m/Y H:i') : '' }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(method_exists($eventDate, 'getFormattedEndDate'))
                                                    {{ $eventDate->getFormattedEndDate('d/m/Y H:i') }}
                                                @else
                                                    {{ $eventDate->end_date ? $eventDate->end_date->format('d/m/Y H:i') : '' }}
                                                @endif
                                            </td>
                                            <td>{{ isset($eventDate->quantity_available) ? $eventDate->quantity_available : 'Illimitata' }}</td>
                                            <td>
                                                @if(isset($eventDate->is_active) && $eventDate->is_active)
                                                    <span class="label label-success">Attiva</span>
                                                @else
                                                    <span class="label label-default">Disattivata</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-xs btn-primary edit-date" data-date-id="{{ $eventDate->id }}">
                                                    <i class="ico-edit"></i> Modifica
                                                </button>
                                                <button class="btn btn-xs btn-danger delete-date" data-date-id="{{ $eventDate->id }}">
                                                    <i class="ico-trash"></i> Elimina
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">
                            <p>Nessuna data configurata. L'evento userà la data principale 
                                @if(isset($event) && method_exists($event, 'getFormattedDate'))
                                    ({{ $event->getFormattedDate('start_date') }}).
                                @else
                                    dell'evento.
                                @endif
                            </p>
                            <p>Per abilitare più date, aggiungi almeno una data qui sotto.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12">
                <h5 id="form-title">Aggiungi nuova data</h5>
                {!! Form::open(['url' => route('postCreateEventDate', ['event_id' => $event->id]), 'class' => 'ajax', 'id' => 'event-date-form']) !!}
                
                {!! Form::hidden('date_id', null, ['id' => 'date_id']) !!}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('start_date', 'Data e ora inizio', ['class' => 'control-label required']) !!}
                            {!! Form::text('start_date', null, [
                                'class' => 'form-control hasDatepicker',
                                'data-field' => 'datetime',
                                'readonly' => '',
                                'id' => 'start_date_input'
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('end_date', 'Data e ora fine', ['class' => 'control-label required']) !!}
                            {!! Form::text('end_date', null, [
                                'class' => 'form-control hasDatepicker',
                                'data-field' => 'datetime',
                                'data-startend' => 'end',
                                'data-startendelem' => '#start_date_input',
                                'readonly' => '',
                                'id' => 'end_date_input'
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('quantity_available', 'Disponibilità (opzionale)', ['class' => 'control-label']) !!}
                            {!! Form::number('quantity_available', null, [
                                'class' => 'form-control',
                                'min' => 1,
                                'placeholder' => 'Lascia vuoto per illimitata'
                            ]) !!}
                            <small class="help-block">Se lasciato vuoto, usa la disponibilità totale dell'evento</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('is_active', 'Stato', ['class' => 'control-label']) !!}
                            {!! Form::select('is_active', [
                                '1' => 'Attiva',
                                '0' => 'Disattivata'
                            ], '1', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::submit('Salva data', ['class' => 'btn btn-success']) !!}
                    <button type="button" class="btn btn-default" id="cancel-edit" style="display:none;">Annulla</button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@if(isset($event) && $event)
<script>
$(function() {
    var eventId = {{ $event->id }};
    
    // Edit date
    $('.edit-date').on('click', function() {
        var dateId = $(this).data('date-id');
        var row = $('tr[data-date-id="' + dateId + '"]');
        
        // Load date data via AJAX
        var baseUrl = '{{ url("event/" . $event->id . "/customize/dates") }}';
        $.ajax({
            url: baseUrl + '/' + dateId,
            method: 'GET',
            success: function(data) {
                $('#date_id').val(data.id);
                $('#start_date_input').val(data.start_date_formatted);
                $('#end_date_input').val(data.end_date_formatted);
                $('input[name="quantity_available"]').val(data.quantity_available);
                $('select[name="is_active"]').val(data.is_active ? '1' : '0');
                $('#form-title').text('Modifica data');
                $('#cancel-edit').show();
                var updateUrl = '{{ url("event/" . $event->id . "/customize/dates") }}';
                $('#event-date-form').attr('action', updateUrl + '/' + dateId);
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#event-date-form').offset().top - 100
                }, 500);
            }
        });
    });

    // Delete date
    $('.delete-date').on('click', function() {
        if (!confirm('Sei sicuro di voler eliminare questa data?')) {
            return;
        }
        
        var dateId = $(this).data('date-id');
        var deleteUrl = '{{ url("event/" . $event->id . "/customize/dates") }}';
        $.ajax({
            url: deleteUrl + '/' + dateId + '/delete',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function() {
                location.reload();
            }
        });
    });

    // Cancel edit
    $('#cancel-edit').on('click', function() {
        $('#date_id').val('');
        $('#start_date_input').val('');
        $('#end_date_input').val('');
        $('input[name="quantity_available"]').val('');
        $('select[name="is_active"]').val('1');
        $('#form-title').text('Aggiungi nuova data');
        $(this).hide();
        var createUrl = '{{ url("event/" . $event->id . "/customize/dates") }}';
        $('#event-date-form').attr('action', createUrl);
    });

    // Form submit success
    $('#event-date-form').on('ajax:success', function() {
        location.reload();
    });
});
</script>
@endif
@endif
