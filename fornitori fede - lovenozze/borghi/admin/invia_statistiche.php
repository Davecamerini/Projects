<?php
// Includi WordPress per accedere alle funzioni WP
require_once('../../wp-load.php');

// Parametri di connessione al database (puoi usare i dettagli della tua connessione DB di WordPress)
define('DB_SERVER', 'db16.webme.it');
define('DB_USERNAME', 'sitidi_759');
define('DB_PASSWORD', 'c2F1K5cd08442336');
define('DB_NAME', 'sitidi_759');

// Crea connessione
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Controlla la connessione
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connessione al database fallita: " . $conn->connect_error]));
}

// Recupera i dati inviati tramite POST
$id_borgo = isset($_POST['id_borgo']) ? intval($_POST['id_borgo']) : 0;
$azione = isset($_POST['azione']) ? trim($_POST['azione']) : '';

// Verifica se i dati sono validi
if ($id_borgo > 0 && !empty($azione)) {

    // Verifica se esiste già una riga per l'ID borgo e l'azione specifica
    $sql_check = "SELECT id FROM borghi_statistiche WHERE id_borgo = ? AND azione = ?";
    $stmt_check = $conn->prepare($sql_check);
    if ($stmt_check) {
        $stmt_check->bind_param("is", $id_borgo, $azione);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Se esiste già, incrementa il contatore
            $sql_update = "UPDATE borghi_statistiche SET contatore = contatore + 1 WHERE id_borgo = ? AND azione = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("is", $id_borgo, $azione);
                if ($stmt_update->execute()) {
                    echo json_encode(["status" => "success", "message" => "Contatore aggiornato con successo"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Errore durante l'aggiornamento del contatore"]);
                }
                $stmt_update->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Errore nella preparazione della query di aggiornamento"]);
            }
        } else {
            // Se non esiste, crea una nuova riga con contatore = 1
            $sql_insert = "INSERT INTO borghi_statistiche (id_borgo, azione, contatore) VALUES (?, ?, 1)";
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert) {
                $stmt_insert->bind_param("is", $id_borgo, $azione);
                if ($stmt_insert->execute()) {
                    echo json_encode(["status" => "success", "message" => "Nuova statistica creata con contatore = 1"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Errore durante la creazione della statistica"]);
                }
                $stmt_insert->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Errore nella preparazione della query di inserimento"]);
            }
        }

        $stmt_check->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nella preparazione della query di verifica"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Dati mancanti o non validi"]);
}

// Chiudi la connessione al database
$conn->close();
?>
