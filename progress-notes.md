## Stato progetto Attendize (locale Docker)

- **Installazione e fix Docker**
  - Clonato Attendize nella cartella `attendize` e configurato `.env` di base.
  - Sistemato `Dockerfile` per usare i repository Debian archivio e installare correttamente i pacchetti richiesti.
  - Integrato i comandi di setup Laravel direttamente nel `Dockerfile` (composer install, generazione chiave, permessi).
  - Costruite le immagini Docker: `attendize_base`, `attendize_worker`, `attendize_web`.
  - Avviato lo stack con `docker compose up -d` (servizi: web, db MySQL 5.7, redis, maildev).
  - Risolto errore 500 iniziale dovuto a mancanza della cartella `vendor` montata nel volume (eseguito `composer install` nel container web).
  - Sistemata `APP_KEY` in `.env` (problema di duplicazione valore), rigenerata chiave e ripulita cache config.

- **Localizzazione in italiano**
  - Verificata presenza delle traduzioni italiane in `resources/lang/it`.
  - Aggiornato `config/app.php`:
    - `locale` → `it`
    - `fallback_locale` → `it`
    - `faker_locale` → `it_IT`
  - Eseguito `php artisan config:clear` nel container web per applicare le nuove impostazioni.

- **Home generale eventi**
  - Modificato `IndexController@showIndex` per mostrare una **home pubblica generale** con tutti gli eventi pubblici:
    - Query `Event` per **eventi futuri** (`is_live = 1`, `end_date >= now()`, ordinati per `start_date`).
    - Query per **eventi passati** (ultimi 20, `end_date < now()`, ordinati per `end_date`).
    - Render della view `Public.GeneralEvents.index`.
  - Creata view `resources/views/Public/GeneralEvents/index.blade.php` che:
    - Estende il nuovo layout generico `Public.Layouts.PublicPage`.
    - Mostra una **hero** introduttiva in alto.
    - Mostra eventi futuri e passati in **griglia responsive** (card 1–3 per riga a seconda della larghezza).
    - Per ogni evento mostra: banner (se presente), titolo, data, luogo, pulsante di azione.
  - Creato layout pubblico generico `resources/views/Public/Layouts/PublicPage.blade.php` per pagine pubbliche non legate a un singolo organiser.
  - Pulita cache viste con `php artisan view:clear` dopo le modifiche.

- **Pagina evento singolo – banner**
  - Verificato layout esistente `Public.ViewEvent.Layouts.EventPage` e sezione header `Public.ViewEvent.Partials.EventHeaderSection`.
  - Aggiunto in `EventHeaderSection.blade.php` un **banner immagine** sopra al titolo evento, quando l’evento ha immagini:
    - Usa `asset($event->images->first()['image_path'])` come sorgente banner.
    - Banner responsive con `object-fit: cover`, altezza massima ~420px, bordo arrotondato.
  - Ripulita cache viste per applicare il nuovo header.

- **Homepage eventi – miglioramenti successivi**
  - Resi i box dei **prossimi eventi** in formato **banner rettangolare orizzontale**:
    - layout `flex` con immagine a sinistra e contenuto a destra,
    - immagine centrata su sfondo nero, con `object-fit: contain` per non croppare il banner e `max-height` controllata.
  - Migliorata la leggibilità del contenuto nei box:
    - padding più ampio per titoli/testi e pulsanti,
    - titoli che vanno a capo con `white-space: normal` e `word-wrap: break-word` per gestire nomi evento molto lunghi,
    - pulsante “Biglietti” arrotondato e allineato a destra.
  - Aggiunto un **header pubblico globale** (`Public.Layouts.PublicPage`) con:
    - logo (immagine Attendize personalizzabile) e titolo “Piattaforma Eventi”,
    - menu responsive con link rapidi: Home, Eventi, Area riservata, Contatti.

- **Pagamenti Stripe – hardening UI**
  - Aggiornata la view `Public.ViewEvent.Partials.PaymentStripe` per evitare errori quando la config Stripe non è presente:
    - controlla che `$account_payment_gateway->config` sia un array con `publishableKey` prima di inizializzare Stripe,
    - in caso contrario mostra un messaggio di errore chiaro invece di un fatale.

- **Pagamenti: Stripe vs PayPal**
  - Verificato lo stato dei gateway di pagamento nel codice:
    - Gateway supportati nel core: `Dummy`, `Stripe`, `Stripe\PaymentIntents` (Stripe SCA).
    - La vecchia integrazione `PayPal_Express` è stata rimossa dalle migrazioni/seeder (`AddDefaultGateways`, `PaymentGatewaySeeder`).
    - Config `config/laravel-omnipay.php` contiene ancora definizione PayPal, ma manca l’implementazione concreta nelle classi `Services\PaymentGateway` e nelle viste.
  - Decisione: **useremo Stripe** come gateway ufficiale (nessuna modifica strutturale fatta ancora sui pagamenti; configurazione Stripe rimandata a dopo).

- **Mappa posti / posti numerati**
  - Ricerca nel codice e migrazioni: **non esiste un modulo integrato per mappa posti o posti numerati**.
  - Funzionalità attuale: gestione di **tipi di biglietto** con prezzi/quantità/periodi diversi (ma senza mappa grafica).
  - Da valutare in futuro:
    - o sviluppo custom (modello “posti”, mappa, UI per selezione grafica),
    - o integrazione con un servizio esterno specializzato in seating plan.

- **Note per futura pubblicazione su Tophost**
  - Al momento l’istanza gira in Docker locale (nginx + PHP-FPM + MySQL).
  - Per migrare su Tophost (hosting condiviso, no Docker) servirà:

- **Mappa posti – versione sperimentale con piantina**
  - **Data model e migrazioni**
    - Creati i modelli e le tabelle: `seat_maps`, `seat_zones`, `seats`, `reserved_seats`, con `is_seated` su `events` e `seat_id` su `attendees`.
    - Ogni `SeatZone` ha: nome, colore, ticket collegato, righe/colonne generate, coordinate X/Y percentuali per il posizionamento sulla mappa.
  - **Backend – tab “Mappa posti”**
    - Nuova tab in `Customize.blade.php` per:
      - creare una piantina (`SeatMap`) e marcarne l’evento come **a posti numerati**,
      - creare/vedere le **zone esistenti** (nome, colore, ticket, numero posti, X/Y),
      - modificare una zona (nome, colore, ticket, posizione X/Y) e **opzionalmente righe/colonne**:
        - se `rows/cols` cambiano e NON ci sono `Attendee` con `seat_id` in quella zona → cancelliamo i posti esistenti e rigeneriamo la griglia (etichette file A,B,C…).
        - se ci sono posti già assegnati → blocchiamo l’operazione e mostriamo un errore.
    - Il form “Modifica zona” ora posta in modo tradizionale e il controller fa `redirect()->route('showEventCustomizeTab', ['tab' => 'seat_map'])` così, dopo il salvataggio, si resta sempre sulla tab **Mappa posti**.
  - **Anteprima mappa (admin)**
    - Reintrodotta un’anteprima grafica nella tab “Mappa posti” con blocchi colorati (`.seat-zone-admin`) proporzionali al numero di file/colonne della zona.
    - I blocchi usano le coordinate percentuali X/Y della zona per il posizionamento; il **drag & drop** aggiorna `position_x/position_y` nel form di modifica.
    - Per eliminare i ripetuti errori `unexpected 'endforeach'`:
      - la tabella “Zone esistenti” e l’anteprima usano ora **foreach PHP puro** nella view compilata, evitando mix Blade/`endforeach` che mandavano in crash il parser.
      - dopo ogni modifica strutturale alle view, viene eliminato il file compilato incriminato (`storage/framework/views/2931...php`) e rieseguito `php artisan view:clear` nel container web.
  - **Frontend – selezione posti sulla pagina evento**
    - Creata la partial `Public.ViewEvent.Partials.SeatMapSelector`:
      - mostra per ogni zona un blocco con le file e i pallini numerati, posizionato con `top/left` percentuali e larghezza **proporzionale a colonne/file** ma con altezza minima (per evitare tagli).
      - i pallini sono cliccabili: aggiornano un array JS di posti selezionati, che a sua volta aggiorna automaticamente le quantità dei biglietti collegati.
    - Ridotti leggermente `min-width` dei pallini e la larghezza “unitaria” (`cellW`) per evitare sovrapposizioni orizzontali tra blocchi (es. tra “centro sala” e “fondo sala”) pur mantenendo il layout disegnato in admin.
    - Esportare database da MySQL Docker e importarlo nel MySQL di Tophost.
    - Eseguire `composer install --no-dev` in locale (fuori da Docker) per generare `vendor/` pronto per il server.
    - Caricare via FTP il codice (soprattutto `public/` come root del sito) e configurare `.env` con credenziali DB e `APP_URL` del dominio.
    - Verificare permessi di scrittura su `storage` e `bootstrap/cache`.

- **Mappa posti – rifinitura layout standard vs piantina**
  - Aggiunto campo `capacity` su `SeatMap` e relativo salvataggio in `EventCustomizeController@postEditEventSeatMap` (usato come supporto per ragionare sulla scala della sala).
  - Dopo vari test di dimensionamento, il comportamento grafico è stato consolidato con **due modalità**:
    - **Standard (senza `background_image_path`)**:
      - Admin e pagina evento usano lo **stesso wrapper** (altezza ~520px, larghezza 900px centrata, bordo tratteggiato su sfondo chiaro).
      - Box zona in entrambe le view: `cellW ≈ 28`, `cellH ≈ 32`, `baseW ≈ 40`, `baseH ≈ 40`; pallini pubblici grandi (`min-width ~20px`, padding 1×3) come nella prima versione.
      - Le percentuali `position_x/position_y` producono ora in pubblico la stessa distanza fra le zone che si vede in admin (risolte sovrapposizioni tipo “vicino palco” + “zona vip centrale”).
    - **Con piantina di sfondo (`background_image_path` valorizzato)**:
      - Wrapper più alto (admin ~700px, pubblico ~800px), sempre 900px di larghezza centrata, con la piantina come `background-image` `contain`.
      - Box zona più compatti per ospitare molti posti: `cellW ≈ 16`, `cellH ≈ 20`, `baseW ≈ 32`, `baseH ≈ 32`; in pubblico pallini ridotti (~14px) per aumentare la densità.
  - Il toggle tra le due modalità avviene **solo** in base alla presenza/assenza della piantina: la logica di selezione e il salvataggio dei posti non vengono toccati.

- **Mappa posti – personalizzazione file/colonne e UI pubblica**
  - **Backend**
    - Aggiunta migration `2025_12_29_000006_add_position_to_seat_zones_table` (posizioni X/Y percentuali) e `2025_12_29_000008_add_start_labels_to_seat_zones_table` che introduce `start_row_alpha` e `start_col_num` su `seat_zones`.
    - In `EventCustomizeController@postCreateSeatZone` e `@postUpdateSeatZone`:
      - supporto per **Prima fila** (`start_row_label`, default `A`) e **Primo posto n°** (`start_col_number`, default `1`);
      - alla creazione (e alla rigenerazione, se cambiano righe/colonne e non ci sono posti assegnati) i `Seat` vengono generati con:
        - `row_label` calcolato da `start_row_alpha` (`A,B,C…` o a partire da altra lettera),
        - `seat_number` calcolato da `start_col_num` (es. 1–8, 101–108),
        - `x` / `y` che indicano la griglia per fila/colonna.
    - Tentativo di editor avanzato per etichette file/colonne (CSV e tabella “tipo Excel”) **rimosso** perché introduceva complessità e problemi di salvataggio: la versione attuale usa solo il pattern automatico + eventuali modifiche manuali dirette nel DB (consigliate solo su zone senza ordini).
  - **Admin – UI**
    - Nel tab `Mappa posti` la sezione “Crea zona” e “Modifica zona” espone ora esplicitamente:
      - `Numero file`, `Posti per fila`,
      - `Posizione X/Y %`,
      - `Prima fila` (campo testo breve) e `Primo posto n°`.
    - Il pulsante “Modifica” per le zone popola questi campi usando i dati effettivi della zona e delle sue `Seat`, mantenendo in sync numeri di righe/colonne e partenza etichette.
  - **Pagina evento – stile pallini**
    - Aggiornata la partial `Public.ViewEvent.Partials.SeatMapSelector`:
      - i pallini hanno ora uno sfondo di base grigio chiaro `#c3c3c3`, bordo `#a3a3a3` e testo scuro `#111827` per migliorare la leggibilità;
      - dimensioni leggermente aumentate:
        - con piantina: diametro ~18px, font 10px,
        - senza piantina: diametro ~24px, font 11px.
      - i posti **selezionati** usano il colore della zona (`SeatZone::color`) come background e bordo (via `data-zone-color`), con testo nero per rimanere leggibile.
      - i posti **occupati** hanno sfondo grigio medio `#9ca3af`, testo bianco e bordo grigio scuro.
    - I calcoli di larghezza/altezza dei blocchi zona in admin e in pubblico sono stati mantenuti coerenti (stessi `cellW/cellH/baseW/baseH` a seconda della modalità), così il posizionamento percentuale dei box resta allineato tra backend e frontend.

---

## Duplica evento & piccoli aggiustamenti UI (fine dicembre 2025)

### 1. Funzione “Duplica evento”

- **Controller**: `app/Http/Controllers/EventController.php`
  - Aggiunte use in testa:
    - `use Illuminate\Support\Facades\DB;`
    - `use App\Models\SeatMap;`
    - `use App\Models\SeatZone;`
    - `use App\Models\Seat;`
  - Nuovo metodo `postDuplicateEvent(Request $request, $event_id)`:
    - Carica l’evento originale con relazioni:
      - `Event::scope()->with(['tickets', 'images', 'seatMaps.zones.seats'])->findOrFail($event_id);`
    - Avvolge tutto in una **transazione** DB:
      - `DB::beginTransaction();` / `DB::commit();` / `DB::rollBack();`.
    - **Duplica l’evento**:
      - `$newEvent = $originalEvent->replicate();`
      - imposta:
        - `title` = titolo originale + `" (copia)"`,
        - `is_live = 0` (nuovo evento non è pubblico),
        - se presenti: `sales_volume = 0`, `organiser_fees_volume = 0`.
      - salva il nuovo evento.
    - **Duplica le immagini** dell’evento:
      - per ogni `EventImage` in `$originalEvent->images`:
        - `replicate()`,
        - `event_id = $newEvent->id`,
        - `save()`.
    - **Duplica i biglietti**:
      - per ogni `Ticket` in `$originalEvent->tickets`:
        - `replicate()`,
        - `event_id = $newEvent->id`,
        - azzera (se esistono) `quantity_sold`, `sales_volume`, `organiser_fees_volume`,
        - `save()`.
      - costruisce una mappa `$ticketIdMap[old_id] = new_id` per ricollegare eventuali `SeatZone`.
    - **Duplica le mappe posti** (se presenti):
      - per ogni `SeatMap` in `$originalEvent->seatMaps`:
        - `replicate()`, `event_id = $newEvent->id`, `save()`.
        - per ogni `SeatZone` della mappa:
          - `replicate()`, `seat_map_id = $newSeatMap->id`,
          - se `ticket_id` esiste in `$ticketIdMap`, lo rimpiazza col nuovo ID, altrimenti imposta `ticket_id = null`,
          - `save()`.
          - per ogni `Seat` della zona:
            - `replicate()`, `seat_zone_id = $newZone->id`,
            - forza `status = 'free'` per azzerare eventuali vendite,
            - `save()`.
    - Al termine:
      - **successo** → redirect a `route('showEventDashboard', ['event_id' => $newEvent->id])` con flash `Evento duplicato correttamente.`.
      - **errore** → `DB::rollBack();`, `Log::error($e);` e redirect back con errore generico “errore durante la duplicazione dell'evento”.

### 2. Route per duplicazione evento

- **File**: `routes/web.php`
  - Nel gruppo con `prefix => 'event'`, subito dopo:
    - `Route::post('{event_id}/go_live', [EventController::class, 'postMakeEventLive'])->name('MakeEventLive');`
  - Aggiunta route POST:
    ```php
    Route::post('{event_id}/duplicate',
        [EventController::class, 'postDuplicateEvent']
    )->name('postDuplicateEvent');
    ```
  - Questa route viene usata dal tasto “Duplica” nella dashboard organizzatore.

### 3. Pulsante “Duplica” nella lista eventi (dashboard organizzatore)

- **File**: `resources/views/ManageOrganiser/Partials/EventPanel.blade.php`
  - Nel `<div class="panel-footer">` (dove ci sono già “Edit” e “Manage”) è stato aggiunto un terzo `<li>`:
    ```blade
    <li>
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('duplicate-event-{{$event->id}}').submit();">
            <i class="ico-copy"></i> Duplica
        </a>
        <form id="duplicate-event-{{$event->id}}"
              action="{{ route('postDuplicateEvent', ['event_id' => $event->id]) }}"
              method="POST"
              style="display:none;">
            {{ csrf_field() }}
        </form>
    </li>
    ```
  - Il pulsante effettua una POST alla route `postDuplicateEvent` per l’evento corrispondente e, in caso di successo, porta direttamente al **dashboard del nuovo evento duplicato**.

### 4. Traduzione pulsante “Registrati” → “ACQUISTA”

- **File**: `resources/lang/it/Public_ViewEvent.php`
  - Testo guida sotto la sezione biglietti:
    ```php
    'below_tickets' => 'Scegli il numero di biglietti e clicca su "ACQUISTA". Nella schermata successiva potrai procedere con il pagamento.',
    ```
  - Etichetta del bottone che porta al carrello / checkout:
    ```php
    'register' => 'ACQUISTA',
    ```
  - Effetto: nella pagina pubblica evento l'utente vede ora il pulsante **ACQUISTA** (al posto di "Registrati") e il testo descrittivo coerente.

---

## Home migliorata con banner, ricerca e categorie (gennaio 2026)

### 1. Redesign home pubblica ispirato a Fever

- **Banner hero principale**
  - Aggiunto banner hero in alto con gradiente viola/blu (`linear-gradient(135deg, #667eea 0%, #764ba2 100%)`)
  - Titolo: "Cose da fare: eventi, esperienze e molto altro"
  - Sottotitolo: "Scopri i migliori eventi nella tua città"
  - Box di ricerca integrato nel banner con design moderno (bordi arrotondati, ombre)

- **Sistema di ricerca**
  - Box di ricerca nel banner hero che filtra eventi per:
    - Titolo
    - Descrizione
    - Nome venue
    - Luogo
    - Categoria (se presente)
  - La ricerca viene eseguita tramite parametro GET `search` nella route `index`

- **Box categorie colorati**
  - Sezione "Categorie" con 6 box colorati cliccabili:
    - **Turismo** (blu `#3B82F6`) - Attrazioni e tour
    - **Cultura** (viola `#8B5CF6`) - Mostre e musei
    - **Spettacoli** (rosa `#EC4899`) - Teatro e concerti
    - **Eventi musicali** (arancione `#F59E0B`) - Concerti e live
    - **Cibo & Drink** (verde `#10B981`) - Degustazioni e ristoranti
    - **Sport** (rosso `#EF4444`) - Eventi sportivi
  - Ogni box ha icona, nome e descrizione
  - Cliccando su un box, filtra gli eventi per quella categoria

- **Top eventi in evidenza**
  - Sezione "La top 5" che mostra i 5 eventi futuri più prossimi
  - Card con immagine evento (o gradiente di fallback se non presente)
  - Visibile solo quando non ci sono filtri attivi (ricerca o categoria)

- **Layout migliorato**
  - Design responsive per mobile e desktop
  - Card eventi con effetti hover
  - Gestione eventi senza immagini (gradiente di fallback)
  - Sezioni eventi futuri e passati separate

### 2. Sistema categorie eventi

- **Migration creata**
  - File: `database/migrations/2025_12_29_000009_add_category_to_events_table.php`
  - Aggiunge campo `category` VARCHAR(50) NULL alla tabella `events` dopo `description`
  - Migration eseguita con successo: `docker-compose exec web php artisan migrate --path=database/migrations/2025_12_29_000009_add_category_to_events_table.php --force`

- **Campo categoria nel form personalizza evento**
  - Aggiunto campo select nella tab "Generale" del form `EditEventForm.blade.php`
  - Opzioni: Turismo, Cultura, Spettacoli, Eventi musicali, Cibo & Drink, Sport
  - Campo opzionale con testo di aiuto: "Seleziona la categoria dell'evento per facilitare la ricerca"
  - Il campo viene salvato nel controller `EventController@postEditEvent`

- **Filtro per categoria nella home**
  - Il controller `IndexController@showIndex` supporta il parametro GET `category`
  - Quando viene passato, filtra gli eventi per quella categoria specifica
  - I box categorie usano il parametro `category` invece di `search` per filtrare correttamente

- **Ricerca include categoria**
  - La ricerca testuale include anche il campo `category` nei risultati
  - Permette di trovare eventi cercando il nome della categoria

### 3. Modifiche ai file

- **Controller**: `app/Http/Controllers/IndexController.php`
  - Aggiunto supporto per ricerca testuale (parametro `search`)
  - Aggiunto supporto per filtro categoria (parametro `category`)
  - Aggiunta sezione "Top 5 eventi" (featured events)
  - Definizione array categorie con nome, icona, colore e descrizione

- **View**: `resources/views/Public/GeneralEvents/index.blade.php`
  - Completamente riscritta con nuovo design
  - Banner hero con ricerca integrata
  - Sezione box categorie colorati
  - Sezione top eventi in evidenza
  - Layout responsive migliorato
  - Gestione eventi senza immagini

- **Form**: `resources/views/ManageEvent/Partials/EditEventForm.blade.php`
  - Aggiunto campo select per categoria dopo il campo descrizione

- **Controller**: `app/Http/Controllers/EventController.php`
  - Aggiunto salvataggio campo `category` nel metodo `postEditEvent`

### 4. Note tecniche

- Le categorie sono hardcoded nell'array `$categories` del controller (non c'è ancora una tabella categorie)
- Il campo categoria è opzionale (NULL) - gli eventi possono esistere senza categoria
- La ricerca e il filtro funzionano anche se alcuni eventi non hanno categoria assegnata
- Il design è responsive e si adatta a schermi mobile e desktop
- Gli eventi senza immagini mostrano un gradiente colorato con icona calendario come fallback

---

## Sistema multi-data eventi e miglioramenti pagamento (gennaio 2026)

### 1. Sistema eventi multi-data

- **Database e migrazioni**
  - Creata tabella `event_dates` per gestire più date/orari per ogni evento:
    - `id`, `event_id`, `start_date`, `end_date`, `quantity_available` (opzionale), `is_active`, `sort_order`
    - Foreign key su `events` con cascade delete
    - Soft deletes supportati
  - Aggiunto campo `event_date_id` alla tabella `orders` per collegare ogni ordine a una data specifica
  - Migration: `2025_12_29_000010_create_event_dates_table.php`
  - Migration: `2025_12_29_000011_add_event_date_id_to_orders_table.php`

- **Modelli**
  - Creato modello `EventDate` con relazioni:
    - `belongsTo(Event::class)`
    - `hasMany(Order::class)`
  - Aggiunta relazione `eventDates()` e `activeEventDates()` al modello `Event`
  - Aggiunta relazione `eventDate()` al modello `Order`
  - Metodi helper: `getFormattedStartDate()`, `getFormattedEndDate()`, `isPast()`, `isAvailable()`

- **Backend - gestione date**
  - Nuova tab "Date e orari" in `Customize.blade.php`
  - Partial `ManageEvent.Partials.EventDates` per:
    - Visualizzare lista date esistenti (tabella con data/ora, disponibilità, stato)
    - Creare nuove date (form con datepicker, timepicker, quantità disponibile)
    - Modificare date esistenti (AJAX)
    - Eliminare date (AJAX con conferma)
  - Controller `EventCustomizeController`:
    - `postCreateEventDate()` - crea nuova data
    - `getEventDate()` - recupera dati data per modifica
    - `postUpdateEventDate()` - aggiorna data esistente
    - `postDeleteEventDate()` - elimina data (soft delete)
  - Parsing date con Carbon per garantire formato corretto

- **Frontend - selezione data**
  - Selettore date migliorato nella pagina acquisto biglietti (`EventTicketsSection.blade.php`):
    - Layout a card per ogni data disponibile
    - Ogni card mostra: giorno settimana, data, mese, orario inizio/fine, disponibilità
    - Design moderno con effetti hover e selezione visiva
    - Selezione automatica della prima data disponibile
    - Validazione: obbligatorio selezionare una data prima di procedere
  - JavaScript per gestione selezione e aggiornamento dinamico

- **Checkout e ordini**
  - `EventCheckoutController`:
    - Salva `event_date_id` in sessione durante validazione biglietti
    - Salva `event_date_id` nell'ordine durante completamento
    - Gestione valori null per retrocompatibilità
  - Visualizzazione data evento:
    - Pagina conferma ordine (`EventViewOrderSection.blade.php`)
    - Biglietti PDF (`PDFTicket.blade.php`)
    - Modal gestione ordine admin (`ManageOrder.blade.php`)
    - Piattaforma check-in (`CheckIn.blade.php` e `check_in.js`)

- **Mappa posti dinamica**
  - API endpoint `GET /api/events/{event_id}/occupied-seats` per recuperare posti occupati per data specifica
  - JavaScript per aggiornare mappa quando si cambia data:
    - Chiamata AJAX per recuperare posti occupati
    - Aggiornamento dinamico stato posti (disponibili/occupati)
    - Rimozione automatica posti occupati dalla selezione
  - Preloader durante aggiornamento mappa (spinner animato con testo "Aggiornamento mappa...")

### 2. Risoluzione problemi pagamento

- **Timeout 504 risolto**
  - Problema: job email eseguiti in modo sincrono (`QUEUE_CONNECTION=sync`) causavano timeout
  - Soluzione: uso di `dispatchAfterResponse()` invece di `dispatch()`:
    - I job vengono eseguiti DOPO che la risposta HTTP è stata inviata al client
    - La risposta JSON viene restituita immediatamente
    - Il redirect avviene subito senza attendere l'invio email
  - Modificato `EventCheckoutController@completeOrder`:
    - `SendOrderNotificationJob::dispatchAfterResponse()`
    - `SendOrderConfirmationJob::dispatchAfterResponse()`
    - `SendOrderAttendeeTicketJob::dispatchAfterResponse()`

- **Configurazione email forzata**
  - Problema: server email usava porta 25 invece di 587
  - Soluzione: configurazione diretta del transport SwiftMailer in ogni job:
    - Metodo `configureMailTransport()` in ogni job email
    - Impostazione diretta: `$transport->setPort(587)`, `$transport->setEncryption('tls')`
    - Valori hardcoded per evitare problemi di lettura `.env`
  - Disabilitata verifica certificato SSL:
    - Problema: certificato CN `*.seeweb.it` non corrisponde a `mail.cercaclick.it`
    - Soluzione: `$transport->setStreamOptions(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])`
  - Job modificati:
    - `SendOrderNotificationJob`
    - `SendOrderConfirmationJob`
    - `SendOrderAttendeeTicketJob`

- **Gestione errori migliorata**
  - Try-catch più robusti usando `\Throwable` invece di `\Exception`
  - Doppio try-catch attorno ai dispatch job per evitare propagazione errori
  - Logging dettagliato per debug
  - Errori email non bloccano più il processo di pagamento

### 3. Preloader mappa posti

- **UI migliorata**
  - Preloader animato quando si cambia data nella pagina acquisto biglietti
  - Spinner CSS con animazione rotazione
  - Testo "Aggiornamento mappa..." durante caricamento
  - Posizionamento assoluto sopra la mappa con sfondo semi-trasparente
  - Scompare automaticamente al termine aggiornamento (successo o errore)

- **Implementazione**
  - Funzione JavaScript `showSeatMapPreloader(show)` per mostrare/nascondere
  - Integrata in `updateSeatMapForDate()`:
    - Mostra preloader all'inizio
    - Nasconde al termine (successo o errore)
  - Classe CSS `seat-map-container` aggiunta al container mappa per posizionamento

### 4. File modificati

- **Controller**
  - `EventCheckoutController.php` - gestione `event_date_id`, `dispatchAfterResponse()`, logging
  - `EventCustomizeController.php` - gestione date eventi (CRUD)
  - `EventViewController.php` - endpoint API posti occupati per data
  - `EventCheckInController.php` - visualizzazione data evento nel check-in
  - `EventOrdersController.php` - eager loading `eventDate` per ordini

- **Job**
  - `SendOrderNotificationJob.php` - configurazione transport email
  - `SendOrderConfirmationJob.php` - configurazione transport email
  - `SendOrderAttendeeTicketJob.php` - configurazione transport email

- **Modelli**
  - `EventDate.php` - nuovo modello per date eventi
  - `Event.php` - relazioni `eventDates()` e `activeEventDates()`
  - `Order.php` - relazione `eventDate()` e campo `event_date_id` in fillable

- **View**
  - `ManageEvent.Partials.EventDates.blade.php` - nuova partial gestione date
  - `ManageEvent.Customize.blade.php` - aggiunta tab "Date e orari"
  - `Public.ViewEvent.Partials.EventTicketsSection.blade.php` - selettore date migliorato, preloader
  - `Public.ViewEvent.Partials.SeatMapSelector.blade.php` - classe container per preloader
  - `Public.ViewEvent.Partials.EventViewOrderSection.blade.php` - visualizzazione data ordine
  - `Public.ViewEvent.Partials.PDFTicket.blade.php` - data evento su biglietto
  - `ManageEvent.Modals.ManageOrder.blade.php` - data evento in modal ordine
  - `ManageEvent.CheckIn.blade.php` - data evento in check-in
  - `public/assets/javascript/check_in.js` - formattazione data evento

- **Config**
  - `config/mail.php` - valori default porta 587 e encryption TLS

- **Route**
  - `routes/api.php` - endpoint `GET /api/events/{event_id}/occupied-seats`

- **Migration**
  - `2025_12_29_000010_create_event_dates_table.php`
  - `2025_12_29_000011_add_event_date_id_to_orders_table.php`

### 5. Note tecniche

- Gli eventi possono avere più date/orari, ma biglietti e sale restano gli stessi
- Ogni ordine è collegato a una data specifica tramite `event_date_id`
- La mappa posti si aggiorna dinamicamente in base alla data selezionata
- Le email vengono inviate in background dopo il completamento pagamento
- La configurazione email è forzata direttamente nel transport per evitare problemi di cache
- Il preloader migliora l'UX durante l'aggiornamento della mappa
