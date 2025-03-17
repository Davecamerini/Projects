<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database


$conn = new mysqli($host, $username, $password, $database);


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
    $categoria = $_POST["categoria"];
    $slug = generateSlug($categoria);

    // Verifica se è stata caricata una copertina
    if (isset($_FILES["copertina"])) {
        $copertina = uploadFile($_FILES["copertina"]);
    } else {
        $copertina = null;
    }

    // Esegui la query di inserimento
    $sql = "INSERT INTO categorie_corsi_online (categoria, slug, copertina) VALUES ('$categoria', '$slug', '$copertina')";

    if ($conn->query($sql) === TRUE) {
        header("Location: https://www.zoiyoga.it/area-corsi-online/php/gestionale/lista_categorie.php");
    } else {
        echo "Errore durante l'inserimento della categoria: " . $conn->error;
    }
}

// Chiudi la connessione al database
$conn->close();

// Funzione per generare lo slug da un testo
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Funzione per caricare un file e restituire il percorso
function uploadFile($file) {
    $targetDir = "images/"; // Cartella in cui verranno salvati i file
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Verifica se l'upload è andato a buon fine
    if ($uploadOk == 0) {
        echo "Errore durante l'upload della copertina.";
        return null;
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            echo "Errore durante il salvataggio della copertina.";
            return null;
        }
    }
}
?>
