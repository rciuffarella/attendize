## Modifiche GDPR & Cookie Law

### 1. Consenso GDPR su pagamento Stripe

- **File**: `resources/views/Public/ViewEvent/Partials/PaymentStripe.blade.php`  
  - Aggiunta checkbox obbligatoria per il consenso GDPR subito dopo `Form::token()` nel form di pagamento Stripe classico.
  - Testo:  
    > Autorizzo il trattamento dei miei dati personali ai sensi del Regolamento (UE) 2016/679 (GDPR) e dell'informativa privacy fornita dall'organizzatore.

- **File**: `resources/views/Public/ViewEvent/Partials/PaymentStripeSCA.blade.php`  
  - Stessa checkbox obbligatoria e stesso testo di consenso GDPR, sempre dopo `Form::token()`, per il flusso Stripe SCA.

Effetto: l'utente non può procedere al pagamento senza aver spuntato il consenso al trattamento dati.

---

### 2. Banner Cookie Law – homepage e pagine generali

- **File**: `resources/views/Public/Layouts/PublicPage.blade.php`  
  - Inserito un banner fisso in basso (`#cookie-law-banner`) che informa sull'uso dei cookie tecnici e di profilazione.  
  - Bottoni:
    - `Accetto` → imposta cookie `cookie_consent=accepted` valido 365 giorni e nasconde il banner.
    - `Rifiuto` → imposta cookie `cookie_consent=declined` valido 365 giorni e nasconde il banner.
  - Link opzionale alla Privacy & Cookie Policy (se esiste la route `privacy`):  
    ```php
    @if(Route::has('privacy'))
        Consulta la <a href="{{ route('privacy') }}" style="color:#93c5fd; text-decoration:underline;">Privacy & Cookie Policy</a>.
    @endif
    ```
  - Script JS inline che:
    - legge il cookie `cookie_consent`;
    - mostra il banner solo se il cookie **non** è impostato;
    - gestisce i click su `#cookie-law-accept` e `#cookie-law-decline`.

Effetto: il banner cookie appare alla prima visita e non viene riproposto per 365 giorni dopo una scelta esplicita.

---

### 3. Banner Cookie Law – pagine evento

- **File**: `resources/views/Public/ViewEvent/Layouts/EventPage.blade.php`  
  - Aggiunto lo stesso banner Cookie Law (markup e testo come sopra) all’interno del layout delle pagine evento.  
  - Riutilizzato lo stesso script JS inline per la gestione del cookie `cookie_consent`.

Effetto: il banner Cookie Law è coerente e visibile anche sulla pagina pubblica dell’evento (home evento, checkout, pagamento, ecc.), finché l’utente non esprime la propria scelta.

---

### 4. Note operative

- Ambiente di esecuzione: **Docker**, servizio `web` definito in `docker-compose.yml`.
- Per rendere effettive le modifiche alle view sono stati eseguiti nel container `web`:

```bash
docker compose exec web php artisan view:clear
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear
docker compose exec web php artisan route:clear
```

---

### 5. Configurazione email e fix “Rinvia i biglietti”

- **Configurazione SMTP (file `.env`, lato server)**  
  - Impostate variabili `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` per usare il server `mail.cercaclick.it` con l’account `eventi@fondazioneboccadamo.org`.
  - Per rendere effettive le modifiche del `.env` è stato necessario ricreare i container Docker:

    ```bash
    docker compose up -d --force-recreate web worker
    ```

  - Dopo la ricreazione, verificate nel container `web` (comando `docker compose exec web env`) le variabili effettive:
    - `MAIL_HOST=mail.cercaclick.it`
    - `MAIL_PORT=587`
    - `MAIL_USERNAME=eventi@fondazioneboccadamo.org`
    - `MAIL_PASSWORD=AAAbbb123@2025`
    - `MAIL_ENCRYPTION=tls`

- **Fix messaggio “undefined” su “Rinvia i Biglietti”**
  - **File**: `app/Http/Middleware/SetViewVariables.php`  
    - Aggiunta anche la chiave JS `GenericErrorMessages` oltre a `GenericErrorMessage`, entrambe mappate su `trans('Controllers.whoops')`, per compatibilità con il JavaScript (`Attendize.GenericErrorMessages`).
  - **File**: `app/Http/Controllers/EventOrdersController.php`  
    - Metodo `resendOrder()` aggiornato per restituire anche il campo `message` nella risposta JSON:

    ```php
    return response()->json([
        'status'      => 'success',
        'message'     => trans('Controllers.ticket_successfully_resent'),
        'redirectUrl' => '',
    ]);
    ```

Effetto: il click su **“Rinvia i Biglietti”** mostra ora un messaggio di conferma coerente e non più la stringa `undefined`; l’invio email usa la nuova configurazione SMTP definita nel `.env` e caricata nei container Docker.

---

### 6. Inizio struttura posti numerati con piantina

- **Nuove tabelle (migration create\_...)**
  - `seat_maps` (`2025_12_28_000001_create_seat_maps_table.php`)  
    - Colonne principali: `event_id`, `name`, `background_image_path`, dimensioni (`width`, `height`).
  - `seat_zones` (`2025_12_28_000002_create_seat_zones_table.php`)  
    - Colonne: `seat_map_id`, `ticket_id` (tipo di biglietto collegato), `name`, `color`, `price_modifier`.
  - `seats` (`2025_12_28_000003_create_seats_table.php`)  
    - Colonne: `seat_zone_id`, `row_label`, `seat_number`, coordinate `x`/`y`, `status` (`free`, `reserved`, `sold`, `blocked`), `price_override`.
  - `reserved_seats` (`2025_12_28_000004_create_reserved_seats_table.php`)  
    - Colonne: `seat_id`, `event_id`, `session_id`, `expires_at` – per gestire la prenotazione temporanea dei posti durante il checkout.
  - `attendees` – aggiunta colonna `seat_id` (`2025_12_28_000005_add_seat_id_to_attendees_table.php`) collegata alla tabella `seats` (FK `set null`).

- **Nuovi model**
  - `App\Models\SeatMap`  
    - `belongsTo(Event::class)`  
    - `hasMany(SeatZone::class)`
  - `App\Models\SeatZone`  
    - `belongsTo(SeatMap::class, 'seat_map_id')`  
    - `belongsTo(Ticket::class)`  
    - `hasMany(Seat::class)`
  - `App\Models\Seat`  
    - `belongsTo(SeatZone::class, 'seat_zone_id')`
  - `App\Models\ReservedSeat`  
    - `belongsTo(Seat::class)`  
    - `belongsTo(Event::class)`

- **Modifica model esistente**
  - `App\Models\Attendee`  
    - `seat_id` aggiunto tra i campi `fillable` per poter associare ogni partecipante a un posto specifico.

Questa è solo la **prima fase strutturale**: definisce il data model per piantina, zone, posti e prenotazioni; i passi successivi saranno l’editor grafico della piantina e l’integrazione nel flusso di acquisto.

---

### 7. Selezione posti da frontend + integrazione con biglietti

- **Frontend: selezione posti nella pagina evento**
  - **File**: `resources/views/Public/ViewEvent/Partials/SeatMapSelector.blade.php`  
    - Mostra, per gli eventi con `is_seated = true` e almeno una `SeatMap`, un pannello “Seleziona i posti” nella pagina pubblica dell’evento.
    - Per ogni `SeatZone` viene visualizzato:
      - il nome zona con il colore configurato,
      - una griglia di bottoni/pallini per riga (`row_label`) e numero di posto (`seat_number`), costruita raggruppando i `Seat` per riga e ordinandoli per numero.
      - stato posti:
        - `seat-free` (grigio chiaro) → cliccabile,
        - `seat-taken` (grigio scuro) → disabilitato (posti non liberi).
    - Ogni bottone ha `data-seat-id` e `data-ticket-id` (dal `ticket_id` della zona).
    - JavaScript inline:
      - mantiene un array di selezioni `selected` con elementi nel formato `ticketId:seatId`;
      - aggiorna il campo nascosto `selected_seat_ids` (stringa di coppie separate da virgola);
      - calcola, per ciascun `ticket_id`, il numero di posti selezionati e aggiorna automaticamente il relativo `select name="ticket_{ticket_id}"` nella tabella biglietti.

- **Integrazione nella pagina evento**
  - **File**: `resources/views/Public/ViewEvent/Partials/EventTicketsSection.blade.php`  
    - All’interno del form che punta a `postValidateTickets`, prima della tabella biglietti:
      - se `$event->is_seated` è vero, viene incluso `SeatMapSelector`.
    - In questo modo l’utente seleziona i posti **già nella sezione Biglietti**; le quantità dei biglietti vengono sincronizzate con le selezioni.

- **Pulizia precedente integrazione sperimentale**
  - **File**: `resources/views/Public/ViewEvent/Partials/EventPaymentSection.blade.php`  
    - Rimossa l’inclusione della piantina dalla pagina di pagamento: ora la logica di scelta posti vive solo nello step biglietti, come da flusso desiderato.

- **Fix e compatibilità Blade**
  - Corretto l’uso di `@php` in `SeatMapSelector` per evitare errori di sintassi (`unexpected endforeach`):

    ```blade
    @if($event->is_seated && $event->seatMaps->count())
        @php $seatMap = $event->seatMaps->first(); @endphp
        ...
    @endif
    ```

  - Dopo ogni modifica strutturale alle viste sono stati eseguiti i comandi nel container Docker:

    ```bash
    docker compose exec -T web php artisan view:clear
    ```

Effetto: nella pagina pubblica dell’evento, per gli eventi con posti numerati e seat map configurata, l’utente può selezionare visivamente i posti; il sistema tiene traccia delle selezioni e allinea automaticamente le quantità dei relativi tipi di biglietto, pronto per essere collegato in modo vincolante al backend (riserve e `attendees.seat_id`).







