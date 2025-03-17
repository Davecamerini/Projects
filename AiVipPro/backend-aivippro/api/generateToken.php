<?php


$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

//salva il token su db
function saveToken($conn, $token, $expirationDate) {
    // Escapa i valori per prevenire SQL injection
    $token = $conn->real_escape_string($token);
    $expirationDate = $conn->real_escape_string($expirationDate);

    // Query preparata per inserire il nuovo token nella tabella
    $stmt = $conn->prepare("INSERT INTO video_tokens (token, expiration_date) VALUES (?, ?)");
    $stmt->bind_param("ss", $token, $expirationDate);

    // Esegui la query
    if ($stmt->execute()) {

    } else {
        // Errore durante l'inserimento
        echo "Errore durante il salvataggio del token: " . $stmt->error;
    }

    // Chiudi lo statement preparato
    $stmt->close();
}
//prendi dal db la scadenza di un token
function getExpirationDateForToken($conn, $token) {
    // Escapa il valore del token per prevenire SQL injection
    $token = $conn->real_escape_string($token);

    // Query per ottenere la data di scadenza del token
    $sql = "SELECT expiration_date FROM video_tokens WHERE token = '$token' LIMIT 1";
    $result = $conn->query($sql);

    // Gestisci eventuali errori di query
    if ($result === FALSE) {
        die("Errore nella query di ottenimento data di scadenza: " . $conn->error);
    }

    // Verifica se esiste un record
    if ($result->num_rows > 0) {
        // Restituisci la data di scadenza
        $row = $result->fetch_assoc();
        return $row['expiration_date'];
    } else {
        // Nessun record trovato
        return null;
    }
}
//elimina tutti i token scaduti
function removeExpiredTokens($conn) {
    // Ottieni la data corrente
    $currentDate = date('Y-m-d H:i:s');

    // Query per rimuovere i token scaduti
    $sql = "DELETE FROM video_tokens WHERE expiration_date <= '$currentDate'";
    $result = $conn->query($sql);

    // Gestisci eventuali errori di query
    if ($result === FALSE) {
        die("Errore nella query di rimozione: " . $conn->error);
    }
}
//controlla se esiste un token valido nel db
function getExistingToken($conn) {
    // Rimuovi i token scaduti prima di cercare un nuovo token
    removeExpiredTokens($conn);

    // Ottieni la data corrente
    $currentDate = date('Y-m-d H:i:s');

    // Query per ottenere un token valido non scaduto
    $sql = "SELECT token FROM video_tokens WHERE expiration_date > '$currentDate' LIMIT 1";
    $result = $conn->query($sql);

    // Gestisci eventuali errori di query
    if ($result === FALSE) {
        die("Errore nella query: " . $conn->error);
    }

    // Verifica se esiste un token valido
    if ($result->num_rows > 0) {
        // Restituisci il token esistente
        $row = $result->fetch_assoc();
        return $row['token'];
    } else {
        // Nessun token valido trovato
        return null;
    }
}

// Verifica se esiste già un token valido nel database o in altro luogo
$existingToken = getExistingToken($conn);

if ($existingToken) {
    // Restituisci il token esistente
    $response = [
        'token' => $existingToken,
        'expirationDate' => getExpirationDateForToken($conn, $existingToken)
    ];
} else {
    // Genera un nuovo token
    $token = bin2hex(random_bytes(32));
    $expirationDate = date('Y-m-d H:i:s', strtotime('+1 hours'));

    // Salva il nuovo token nel database o in altro luogo
    saveToken($conn, $token, $expirationDate); // Funzione per salvare un nuovo token

    // Restituisci il nuovo token come risposta
    $response = [
        'token' => $token,
        'expirationDate' => $expirationDate
    ];
}

// Chiudi la connessione al database
$conn->close();

// Restituisci la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);


?>
