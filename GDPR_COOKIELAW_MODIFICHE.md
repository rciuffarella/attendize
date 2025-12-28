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



