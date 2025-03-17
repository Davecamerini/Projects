<?php
session_start();


$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}


// Verifica se il token è presente nella richiesta GET
if (!isset($_GET['token']) || empty($_GET['token'])) {
    // Token mancante nella richiesta, reindirizza o gestisci l'errore
    header("Location: index.php");
    exit();
}

// Ottieni il token dalla richiesta GET
$tokenFromRequest = $_GET['token'];


function isValidToken($tokenFromRequest, $conn) {
    $token = $conn->real_escape_string($tokenFromRequest);

    $sql = "SELECT * FROM video_tokens WHERE token = '$token' AND expiration_date > NOW()";

    $result = $conn->query($sql);

    return $result->num_rows > 0;
}
// Verifica la validità del token
if (!isValidToken($conn, $tokenFromRequest)) {
    // Token non valido, reindirizza o gestisci l'errore
    header("Location: errore.php");
    exit();
}

// Se il token è valido, puoi procedere a trasmettere il video
$videoPath = "/area-corsi-online/php/gestionale/video/" . $_GET['video_filename'];

if (file_exists($videoPath)) {
    header("Content-Type: video/mp4");
    header("Content-Length: " . filesize($videoPath));
    readfile($videoPath);
} else {
    // Il video non esiste, reindirizza o gestisci l'errore
    header("Location: Location: index.php");
    exit();
}
?>
