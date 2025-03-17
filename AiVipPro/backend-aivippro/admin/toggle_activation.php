<?php
// toggle_activation.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Includi il file di configurazione del database
    require('../database.php');

    // Recupera i dati dalla richiesta AJAX
    $userId = $_POST['userId'];
    $currentStatus = $_POST['currentStatus'];

    // Calcola il nuovo stato di attivazione
    $newStatus = ($currentStatus == 1) ? 0 : 1;

    // Esegui l'aggiornamento dello stato di attivazione nel database
    $updateQuery = "UPDATE users SET attivazione = '$newStatus' WHERE id = '$userId'";
    $conn->query($updateQuery);

    // Restituisci il nuovo stato di attivazione
    echo $newStatus;

    // Chiudi la connessione al database
    $conn->close();
} else {
    // Se la richiesta non è di tipo POST, restituisci un errore
    http_response_code(400);
    echo "Errore: Richiesta non valida.";
}
?>
