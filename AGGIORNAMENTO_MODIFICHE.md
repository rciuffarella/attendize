## Aggiornamento modifiche

### Contenuto
- **Banner Cookie Law**: rimosso il taglio in altezza aggiungendo padding con safe-area e permettendo altezza automatica.
- **Banner evento**: rimosso il limite di altezza e impostato `object-fit: contain` per mostrare sempre l'immagine completa.
- **Ordine sezioni pagina evento**: i dettagli evento ora sono subito dopo il banner e prima della mappa/posti.
- **Dump SQL**: escluso `attendize-export.sql` dal tracking Git e aggiunto a `.gitignore`.

### File toccati
- `resources/views/Public/Layouts/PublicPage.blade.php`
- `resources/views/Public/ViewEvent/Layouts/EventPage.blade.php`
- `resources/views/Public/ViewEvent/Partials/EventHeaderSection.blade.php`
- `resources/views/Public/ViewEvent/EventPage.blade.php`
- `.gitignore`

