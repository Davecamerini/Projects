<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database


$conn = new mysqli($host, $username, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}



if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}

$cookie_name = "utente_id_admin"; // Sostituisci con il nome del tuo cookie
if (!isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
  header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
  exit();
}
// Verifica se il modulo è stato inviato con il metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera i dati dal modulo
    $id = $_POST["id"];
    $titolo = $_POST["titolo"];
    $link = $_POST["link"];
    $categoria = $_POST["categoria"];
    $data_inserimento = $_POST["data_inserimento"];

    // Esegui la query di aggiornamento
    $sql = "UPDATE corsi_online SET titolo='$titolo', categoria='$categoria', link='$link', data_inserimento='$data_inserimento' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: setting.php");
    } else {
        echo "Errore durante la modifica: " . $conn->error;
    }
} else {
    echo "Metodo non valido per l'accesso a questa pagina.";
}

// Chiudi la connessione al database
$conn->close();
?>
