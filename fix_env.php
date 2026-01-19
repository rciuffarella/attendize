<?php
// Script per correggere il file .env corrotto

$envFile = __DIR__ . '/.env';
$backupFile = __DIR__ . '/.env.backup2';

// Leggi il file originale
$content = file_get_contents($envFile);

// Rimuovi tutte le righe MAIL_ esistenti
$lines = explode("\n", $content);
$newLines = [];
foreach ($lines as $line) {
    if (!preg_match('/^MAIL_/', $line) && !preg_match('/^# Mail Configuration/', $line)) {
        $newLines[] = $line;
    }
}

// Aggiungi le nuove configurazioni MAIL alla fine
$newLines[] = '';
$newLines[] = '# Mail Configuration';
$newLines[] = 'MAIL_DRIVER=smtp';
$newLines[] = 'MAIL_HOST=mail.cercaclick.it';
$newLines[] = 'MAIL_PORT=587';
$newLines[] = 'MAIL_USERNAME=eventi@fondazioneboccadamo.org';
$newLines[] = 'MAIL_PASSWORD=AAAbbb123@2025';
$newLines[] = 'MAIL_ENCRYPTION=tls';
$newLines[] = 'MAIL_FROM_ADDRESS=eventi@fondazioneboccadamo.org';
$newLines[] = 'MAIL_FROM_NAME="Fondazione Boccadamo"';

// Salva il backup
if (file_exists($envFile)) {
    copy($envFile, $backupFile);
}

// Scrivi il nuovo file
file_put_contents($envFile, implode("\n", $newLines));

echo "File .env corretto con successo!\n";
echo "Backup salvato in: .env.backup2\n";
