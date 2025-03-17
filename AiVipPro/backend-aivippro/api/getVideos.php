<?php
// Include il file di connessione al database o inserisci direttamente qui il codice di connessione
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Verifica se l'ID della categoria è stato fornito
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Restituisci un errore o un messaggio appropriato, ad esempio un JSON con un messaggio di errore
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID categoria non valido']);
    $conn->close(); // Chiudi la connessione al database
    exit();
}

// Ottieni l'ID della categoria dalla query string
$id_categoria = $_GET['id'];
$token = $_GET['token'];

// Controllo se il token è valido
if (!isValidToken($token, $conn)) {
    // Token non valido, restituisci un errore o esegui una redirezione
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Token non valido']);
    $conn->close(); // Chiudi la connessione al database
    exit();
}

// Ottieni i video associati alla categoria
$sql_video = "SELECT * FROM corsi_online WHERE categoria = $id_categoria ORDER BY titolo";
$result_video = $conn->query($sql_video);

// Inizializza un array per contenere i markup dei video
$videos = [];
// Inizializza un array per contenere i markup dagli audio
$audios = [];

// Loop attraverso i risultati del database e genera i markup dei video
while ($row_video = $result_video->fetch_assoc()) {
    $videoId = $row_video["id_video"];
    $fileParts = explode('.', $row_video["file_video"]);
    $fileExtension = end($fileParts);

    if (in_array($fileExtension, ["mp4", "webm", "ogg"])) {
    $videoMarkup = '<div class="video"><video width="100%" height="auto" controls preload="metadata" controlsList="nodownload" oncontextmenu="return false;" data-video-id="' . $videoId . '" style="display: block;"><source src="https://www.zoiyoga.it' . $row_video["file_video"] . '?token=' . $token . '#t=0.1" type="video/mp4" /></video><h3 class="title-video mt-2">' . $row_video["titolo"] . '</h3></div>';
    $videos[] = $videoMarkup;
  } elseif (in_array($fileExtension, ["mp3", "ogg", "wav"])) {
    $audioMarkup = '<div class="audio"><audio width="100%" height="auto" controls preload="metadata" controlsList="nodownload" oncontextmenu="return false;" data-media-id="' . $mediaId . '"  style="display: block; width:90%;"><source src="https://www.zoiyoga.it' . $row_video["file_video"] . '?token=' . $token . '#t=0.1" type="audio/mp3" /></audio><h3 class="title-video mt-2">' . $row_video["titolo"] . '</h3></div>';
    $audios[] = $audioMarkup;
  }
}


$responseArray = [
    'videos' => $videos,
    'audios' => $audios
];
// Restituisci l'array dei markup dei video come risposta JSON
header('Content-Type: application/json');
echo json_encode(['videos' => $responseArray]);

$conn->close();


function isValidToken($token, $conn) {
    /*$token = $conn->real_escape_string($token);

    $sql = "SELECT * FROM video_tokens WHERE token = '$token' AND expiration_date > NOW()";

    $result = $conn->query($sql);

    return $result->num_rows > 0;*/
    return true;
}
?>
