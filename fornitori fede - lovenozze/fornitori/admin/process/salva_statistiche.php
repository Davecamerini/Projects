<?php
// Includi WordPress per accedere alle funzioni WP
require_once('../../../wp-load.php');

// Parametri di connessione al database (puoi usare i dettagli della tua connessione DB di WordPress)
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

// Crea connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupera i dati inviati tramite POST
$id_fornitore = isset($_POST['id_fornitore']) ? intval($_POST['id_fornitore']) : 0;
$azione = isset($_POST['azione']) ? $_POST['azione'] : '';

// Controllo se i dati sono validi
if ($id_fornitore > 0 && !empty($azione)) {

    // Verifica se esiste già una riga per l'ID fornitore e l'azione specifica
    $sql_check = "SELECT id FROM fornitori_statistiche WHERE id_fornitore = ? AND azione = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $id_fornitore, $azione);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Se esiste già, incrementa il contatore
        $sql_update = "UPDATE fornitori_statistiche SET contatore = contatore + 1 WHERE id_fornitore = ? AND azione = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("is", $id_fornitore, $azione);

        if ($stmt_update->execute()) {
            echo json_encode(["status" => "success", "message" => "Contatore aggiornato con successo"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Errore durante l'aggiornamento del contatore"]);
        }

        $stmt_update->close();
    } else {
        // Se non esiste, crea una nuova riga con contatore = 1
        $sql_insert = "INSERT INTO fornitori_statistiche (id_fornitore, azione, contatore) VALUES (?, ?, 1)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("is", $id_fornitore, $azione);

        if ($stmt_insert->execute()) {
            echo json_encode(["status" => "success", "message" => "Nuova statistica creata con contatore = 1"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Errore durante la creazione della statistica"]);
        }

        $stmt_insert->close();
    }

    $stmt_check->close();
} else {
    echo json_encode(["status" => "error", "message" => "Dati mancanti o non validi"]);
}

$conn->close();
?>
