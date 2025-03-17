<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

// Connessione al database
$conn = new mysqli($host, $username, $password, $database);

// Verifica se la connessione al database ha avuto successo
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}




// Funzione per verificare se l'abbonamento è ancora valido
function abbonamentoValido($duration, $data_acquisto) {
    // Converti la data di acquisto in un oggetto DateTime
    $data_acquisto = new DateTime($data_acquisto);
    // Aggiungi la durata dell'abbonamento in giorni alla data di acquisto
    $data_scadenza = $data_acquisto->modify("+$duration days");
    // Ottieni la data corrente
    $data_corrente = new DateTime();
    // Confronta la data di scadenza con la data corrente
    return $data_scadenza > $data_corrente;
}
?>
