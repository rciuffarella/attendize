<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatMap;
use App\Models\SeatZone;
use File;
use Illuminate\Http\Request;
use App\Models\Currency;
use Image;
use Validator;

class EventCustomizeController extends MyBaseController
{

    /**
     * Returns data which is required in each view, optionally combined with additional data.
     *
     * @param  int  $event_id
     * @param  array  $additional_data
     *
     * @return array
     */
    public function getEventViewData($event_id, $additional_data = [])
    {
        $event = Event::scope()->findOrFail($event_id);

        $image_path = $event->organiser->full_logo_path;
        if ($event->images->first() != null) {
            $image_path = $event->images()->first()->image_path;
        }

        return array_merge([
            'event'      => $event,
            'questions'  => $event->questions()->get(),
            'image_path' => $image_path,
        ], $additional_data);
    }

    /**
     * Show the event customize page
     *
     * @param string $event_id
     * @param string $tab
     * @return \Illuminate\View\View
     */
    public function showCustomize($event_id = '', $tab = '')
    {
        $data = $this->getEventViewData($event_id, [
            'currencies'               	 => Currency::pluck('title', 'id'),
            'available_bg_images'        => $this->getAvailableBackgroundImages(),
            'available_bg_images_thumbs' => $this->getAvailableBackgroundImagesThumbs(),
            'tab'                        => $tab,
        ]);

        return view('ManageEvent.Customize', $data);
    }

    /**
     * get an array of available event background images
     *
     * @return array
     */
    public function getAvailableBackgroundImages()
    {
        $images = [];

        $files = File::files(public_path() . '/' . config('attendize.event_bg_images'));

        foreach ($files as $image) {
            $images[] = str_replace(public_path(), '', $image);
        }

        return $images;
    }

    /**
     * Get an array of event bg image thumbnails
     *
     * @return array
     */
    public function getAvailableBackgroundImagesThumbs()
    {
        $images = [];

        $files = File::files(public_path() . '/' . config('attendize.event_bg_images') . '/thumbs');

        foreach ($files as $image) {
            $images[] = str_replace(public_path(), '', $image);
        }

        return $images;
    }

    /**
     * Edit social settings of an event
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEventSocial(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        $rules = [
            'social_share_text'      => ['max:3000'],
            'social_show_facebook'   => ['boolean'],
            'social_show_twitter'    => ['boolean'],
            'social_show_linkedin'   => ['boolean'],
            'social_show_email'      => ['boolean'],
        ];

        $messages = [
            'social_share_text.max' => 'Please keep the text under 3000 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $event->social_share_text = $request->get('social_share_text', false);
        $event->social_show_facebook = $request->get('social_show_facebook', false);
        $event->social_show_linkedin = $request->get('social_show_linkedin', false);
        $event->social_show_twitter = $request->get('social_show_twitter', false);
        $event->social_show_email = $request->get('social_show_email', false);
        $event->social_show_whatsapp = $request->get('social_show_whatsapp', false);
        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => trans("Controllers.social_settings_successfully_updated"),
        ]);

    }

    /**
     * Update ticket details
     *
     * @param Request $request
     * @param $event_id
     * @return mixed
     */
    public function postEditEventTicketDesign(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        $rules = [
            'ticket_border_color'   => ['required'],
            'ticket_bg_color'       => ['required'],
            'ticket_text_color'     => ['required'],
            'ticket_sub_text_color' => ['required'],
            'is_1d_barcode_enabled' => ['required'],
        ];
        $messages = [
            'ticket_bg_color.required' => trans("Controllers.please_enter_a_background_color"),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $event->ticket_border_color = $request->get('ticket_border_color');
        $event->ticket_bg_color = $request->get('ticket_bg_color');
        $event->ticket_text_color = $request->get('ticket_text_color');
        $event->ticket_sub_text_color = $request->get('ticket_sub_text_color');
        $event->is_1d_barcode_enabled = $request->get('is_1d_barcode_enabled');

        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Ticket Settings Updated',
        ]);
    }

    /**
     * Edit fees of an event
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEventFees(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        $rules = [
            'organiser_fee_percentage' => ['numeric', 'between:0,100'],
            'organiser_fee_fixed'      => ['numeric', 'between:0,100'],
        ];
        $messages = [
            'organiser_fee_percentage.numeric' => trans("validation.between.numeric", ["attribute"=>trans("Fees.service_fee_percentage"), "min"=>0, "max"=>100]),
            'organiser_fee_fixed.numeric'      => trans("validation.date_format", ["attribute"=>trans("Fees.service_fee_fixed_price"), "format"=>"0.00"]),
            'organiser_fee_fixed.between'      => trans("validation.between.numeric", ["attribute"=>trans("Fees.service_fee_fixed_price"), "min"=>0, "max"=>100]),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $event->organiser_fee_percentage = $request->get('organiser_fee_percentage');
        $event->organiser_fee_fixed = $request->get('organiser_fee_fixed');
        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => trans("Controllers.order_page_successfully_updated"),
        ]);
    }

    /**
     * Edit the event order page settings
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEventOrderPage(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        // Just plain text so no validation needed (hopefully)
        $rules = [];
        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $event->pre_order_display_message = trim($request->get('pre_order_display_message'));
        $event->post_order_display_message = trim($request->get('post_order_display_message'));
        $event->offline_payment_instructions = prepare_markdown(trim($request->get('offline_payment_instructions')));
        $event->enable_offline_payments = (int)$request->get('enable_offline_payments');
        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => trans("Controllers.order_page_successfully_updated"),
        ]);
    }

    /**
     * Edit event page design/colors etc.
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEventDesign(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        $rules = [
            'bg_image_path' => ['mimes:jpeg,jpg,png', 'max:4000'],
        ];
        $messages = [
            'bg_image_path.mimes' => trans("validation.mimes", ["attribute"=>trans("Event.event_image"), "values"=>"JPEG, JPG, PNG"]),
            'bg_image_path.max'   => trans("validation.max.file", ["attribute"=>trans("Event.event_image"), "max"=>2500]),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        if ($request->get('bg_image_path_custom') && $request->get('bg_type') == 'image') {
            $event->bg_image_path = $request->get('bg_image_path_custom');
            $event->bg_type = 'image';
        }

        if ($request->get('bg_color') && $request->get('bg_type') == 'color') {
            $event->bg_color = $request->get('bg_color');
            $event->bg_type = 'color';
        }

        /*
         * Not in use for now.
         */
        if ($request->hasFile('bg_image_path') && $request->get('bg_type') == 'custom_image') {
            $path = public_path() . '/' . config('attendize.event_images_path');
            $filename = 'event_bg-' . md5($event->id) . '.' . strtolower($request->file('bg_image_path')->getClientOriginalExtension());

            $file_full_path = $path . '/' . $filename;

            $request->file('bg_image_path')->move($path, $filename);

            $img = Image::make($file_full_path);

            $img->resize(1400, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $img->save($file_full_path, 75);

            $event->bg_image_path = config('attendize.event_images_path') . '/' . $filename;
            $event->bg_type = 'custom_image';

            \Storage::put(config('attendize.event_images_path') . '/' . $filename, file_get_contents($file_full_path));
        }

        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => trans("Controllers.event_page_successfully_updated"),
            'runThis' => 'document.getElementById(\'previewIframe\').contentWindow.location.reload(true);',
        ]);
    }

    /**
     * Crea o aggiorna la piantina posti per un evento.
     *
     * Prima versione: gestisce solo il nome della mappa e abilita il flag is_seated sull'evento.
     * L'editor grafico verrà aggiunto in passi successivi.
     *
     * @param  Request  $request
     * @param  int  $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEventSeatMap(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        $rules = [
            'seat_map_name'         => ['required', 'max:191'],
            'background_image_path' => ['nullable', 'max:255'],
            'capacity'              => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        // Per ora gestiamo una sola mappa per evento
        $seatMap = $event->seatMaps()->first();
        if (!$seatMap) {
            $seatMap = new SeatMap();
            $seatMap->event_id = $event->id;
        }

        $seatMap->name = $request->get('seat_map_name');
        $seatMap->background_image_path = $request->get('background_image_path');
        $seatMap->capacity = $request->get('capacity') ?: null;
        $seatMap->save();

        // Segna l'evento come "evento con posti numerati"
        $event->is_seated = true;
        $event->save();

        return response()->json([
            'status'  => 'success',
            'message' => trans('Controllers.event_successfully_updated'),
            'runThis' => 'window.location.reload();',
        ]);
    }

    /**
     * Crea una zona e genera automaticamente i posti (a griglia) per una seat map.
     *
     * @param  Request  $request
     * @param  int  $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postCreateSeatZone(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        if (!$event->seatMaps()->exists()) {
            return response()->json([
                'status'  => 'error',
                'messages' => ['seat_map' => ['Devi prima creare una piantina nella sezione "Mappa posti".']],
            ]);
        }

        $seatMap = $event->seatMaps()->first();

        $rules = [
            'zone_name'        => ['required', 'max:191'],
            'zone_color'       => ['nullable', 'max:20'],
            'ticket_id'        => ['nullable', 'integer'],
            'rows'             => ['required', 'integer', 'min:1', 'max:50'],
            'cols'             => ['required', 'integer', 'min:1', 'max:50'],
            'position_x'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'position_y'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_row_label'  => ['nullable', 'string', 'max:5'],
            'start_col_number' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $rows = (int) $request->get('rows');
        $cols = (int) $request->get('cols');

        // Gestione personalizzazione etichette file / colonne
        $startRowLabel = trim((string) $request->get('start_row_label', ''));
        if ($startRowLabel === '') {
            $startRowAlpha = ord('A');
        } else {
            // prendiamo il primo carattere valido
            $startRowAlpha = ord(mb_substr($startRowLabel, 0, 1, 'UTF-8'));
        }
        $startColNumber = $request->get('start_col_number');
        if ($startColNumber === null || $startColNumber === '') {
            $startColNumber = 1;
        } else {
            $startColNumber = (int) $startColNumber;
        }

        $zone = new SeatZone();
        $zone->seat_map_id = $seatMap->id;
        $zone->ticket_id = $request->get('ticket_id') ?: null;
        // Salviamo i vecchi parametri di etichette per capire se sono cambiati
        $oldStartRowAlpha = $zone->start_row_alpha ?: ord('A');
        $oldStartColNum   = $zone->start_col_num ?: 1;

        $zone->name = $request->get('zone_name');
        $zone->color = $request->get('zone_color') ?: '#999999';
        $zone->price_modifier = 0;
        $zone->position_x = $request->get('position_x');
        $zone->position_y = $request->get('position_y');
        $zone->start_row_alpha = $startRowAlpha;
        $zone->start_col_num = $startColNumber;
        $zone->save();

        // Genera i posti: righe personalizzabili (A,B,C... o da altra lettera) e numeri da start_col_number..N
        $seats = [];
        for ($r = 0; $r < $rows; $r++) {
            $rowLabel = chr($startRowAlpha + $r);
            for ($c = 1; $c <= $cols; $c++) {
                $seats[] = [
                    'seat_zone_id' => $zone->id,
                    'row_label'    => $rowLabel,
                    'seat_number'  => (string) ($startColNumber + $c - 1),
                    'x'            => $c,
                    'y'            => $r + 1,
                    'status'       => 'free',
                    'price_override' => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        if (!empty($seats)) {
            Seat::insert($seats);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Zona e posti generati correttamente.',
            'runThis' => 'window.location.reload();',
        ]);
    }

    /**
     * Aggiorna una zona esistente (solo nome, colore e ticket associato).
     *
     * Non modifica i posti già generati, per non impattare eventuali ordini.
     *
     * @param  Request  $request
     * @param  int  $event_id
     * @param  int  $zone_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateSeatZone(Request $request, $event_id, $zone_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        if (!$event->seatMaps()->exists()) {
            return response()->json([
                'status'  => 'error',
                'messages' => ['seat_map' => ['Piantina non trovata per questo evento.']],
            ]);
        }

        $seatMap = $event->seatMaps()->first();
        /** @var SeatZone|null $zone */
        $zone = $seatMap->zones()->where('id', $zone_id)->first();

        if (!$zone) {
            return response()->json([
                'status'  => 'error',
                'messages' => ['zone' => ['Zona non trovata.']],
            ]);
        }

        // Valori correnti delle etichette (prima di eventuali modifiche)
        $oldStartRowAlpha = $zone->start_row_alpha ?: ord('A');
        $oldStartColNum   = $zone->start_col_num ?: 1;

        $rules = [
            'zone_name'        => ['required', 'max:191'],
            'zone_color'       => ['nullable', 'max:20'],
            'ticket_id'        => ['nullable', 'integer'],
            'position_x'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'position_y'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rows'             => ['nullable', 'integer', 'min:1', 'max:50'],
            'cols'             => ['nullable', 'integer', 'min:1', 'max:50'],
            'start_row_label'  => ['nullable', 'string', 'max:5'],
            'start_col_number' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $zone->name = $request->get('zone_name');
        $zone->color = $request->get('zone_color') ?: '#999999';
        $zone->ticket_id = $request->get('ticket_id') ?: null;
        $zone->position_x = $request->get('position_x');
        $zone->position_y = $request->get('position_y');

        // Aggiorna eventuali preferenze su etichette fila/colonna
        $startRowLabel = trim((string) $request->get('start_row_label', ''));
        if ($startRowLabel !== '') {
            $zone->start_row_alpha = ord(mb_substr($startRowLabel, 0, 1, 'UTF-8'));
        }
        $startColNumber = $request->get('start_col_number');
        if ($startColNumber !== null && $startColNumber !== '') {
            $zone->start_col_num = (int) $startColNumber;
        }

        $zone->save();

        /*
         * Se l'utente ha indicato nuove righe/colonne e non ci sono posti già assegnati
         * a partecipanti, rigeneriamo la griglia per questa zona.
         */
        $newRows = $request->get('rows');
        $newCols = $request->get('cols');

        // Configurazione attuale
        $existingSeats = $zone->seats()->get();
        $existingGroups = $existingSeats->groupBy('row_label')->sortKeys();
        $existingRows = $existingGroups->count();
        $existingCols = 0;
        foreach ($existingGroups as $rowSeats) {
            $existingCols = max($existingCols, $rowSeats->count());
        }

        // Se l'utente non specifica nuove righe/colonne, usiamo quelle esistenti
        if (!$newRows) {
            $newRows = $existingRows;
        }
        if (!$newCols) {
            $newCols = $existingCols;
        }

        if ($newRows && $newCols) {
            $newRows = (int) $newRows;
            $newCols = (int) $newCols;

            // Controlliamo se cambia la geometria o solo le etichette
            $labelsChanged = ($zone->start_row_alpha ?: ord('A')) !== $oldStartRowAlpha
                || ($zone->start_col_num ?: 1) !== $oldStartColNum;

            if ($existingRows !== $newRows || $existingCols !== $newCols || $labelsChanged) {
                $seatIds = $existingSeats->pluck('id');
                $hasAttendees = \App\Models\Attendee::whereIn('seat_id', $seatIds)->exists();
                if ($hasAttendees) {
                    return redirect()->route('showEventCustomizeTab', [
                        'event_id' => $event_id,
                        'tab'      => 'seat_map',
                    ])->withErrors(['seats' => 'Non puoi modificare file/colonne: alcuni posti di questa zona sono già assegnati a ordini.']);
                }

                // Nessun posto assegnato: cancelliamo e rigeneriamo
                $zone->seats()->delete();

                // Usa i valori (eventualmente aggiornati) per le etichette
                $startRowAlpha = $zone->start_row_alpha ?: ord('A');
                $startColNumber = $zone->start_col_num ?: 1;

                $seats = [];
                for ($r = 0; $r < $newRows; $r++) {
                    $rowLabel = chr($startRowAlpha + $r);
                    for ($c = 1; $c <= $newCols; $c++) {
                        $seats[] = [
                            'seat_zone_id' => $zone->id,
                            'row_label'    => $rowLabel,
                            'seat_number'  => (string) ($startColNumber + $c - 1),
                            'x'            => $c,
                            'y'            => $r + 1,
                            'status'       => 'free',
                            'price_override' => null,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                }

                if (!empty($seats)) {
                    Seat::insert($seats);
                }
            }
        }

        return redirect()->route('showEventCustomizeTab', [
            'event_id' => $event_id,
            'tab'      => 'seat_map',
        ])->with('message', 'Zona aggiornata correttamente.');
    }

    /**
     * Cancella una zona se non ci sono posti già assegnati a partecipanti.
     *
     * @param  Request  $request
     * @param  int  $event_id
     * @param  int  $zone_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDeleteSeatZone(Request $request, $event_id, $zone_id)
    {
        $event = Event::scope()->findOrFail($event_id);

        if (!$event->seatMaps()->exists()) {
            return response()->json([
                'status'  => 'error',
                'messages' => ['seat_map' => ['Piantina non trovata per questo evento.']],
            ]);
        }

        $seatMap = $event->seatMaps()->first();
        /** @var SeatZone|null $zone */
        $zone = $seatMap->zones()->with('seats')->where('id', $zone_id)->first();

        if (!$zone) {
            return response()->json([
                'status'  => 'error',
                'messages' => ['zone' => ['Zona non trovata.']],
            ]);
        }

        $seatIds = $zone->seats->pluck('id')->all();

        if (!empty($seatIds)) {
            $hasAssignedAttendees = Attendee::whereIn('seat_id', $seatIds)->exists();
            if ($hasAssignedAttendees) {
                return response()->json([
                    'status'  => 'error',
                    'messages' => ['zone' => ['Non puoi cancellare una zona che contiene posti già assegnati a ordini.']],
                ]);
            }
        }

        // Cancella prima i posti, poi la zona
        $zone->seats()->delete();
        $zone->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Zona cancellata correttamente.',
            'runThis' => 'window.location.reload();',
        ]);
    }
}
