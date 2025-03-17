<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
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

// Verifica se è stato passato un parametro ID valido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $categoria_id = $_GET['id'];

    // Esegui la query per eliminare la categoria specificata
    $sql = "DELETE FROM categorie_corsi_online WHERE id = $categoria_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: https://www.zoiyoga.it/area-corsi-online/php/gestionale/lista_categorie.php");
    } else {
        echo "Errore durante l'eliminazione della categoria: " . $conn->error;
    }
} else {
    echo "ID categoria non specificato.";
}

// Chiudi la connessione al database
$conn->close();
?>
