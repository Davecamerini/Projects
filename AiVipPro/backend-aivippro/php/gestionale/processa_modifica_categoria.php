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

// Verifica se il modulo di modifica è stato inviato con il metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera i dati dal modulo
    $categoria_id = $_POST["categoria_id"];
    $nuova_categoria = $_POST["categoria"];

    // Verifica se è stata caricata una nuova copertina
    if (isset($_FILES["copertina"]) && $_FILES["copertina"]["size"] > 0) {
        $nuova_copertina = uploadFile($_FILES["copertina"]);
    } else {
        // Se non è stata caricata una nuova copertina, mantieni la copertina esistente
        $sqlSelect = "SELECT copertina FROM categorie_corsi_online WHERE id = $categoria_id";
        $resultSelect = $conn->query($sqlSelect);

        if ($resultSelect->num_rows > 0) {
            $row = $resultSelect->fetch_assoc();
            $nuova_copertina = $row['copertina'];
        } else {
            echo "Categoria non trovata.";
            exit();
        }
    }

    // Esegui la query di aggiornamento
    $sqlUpdate = "UPDATE categorie_corsi_online SET categoria = '$nuova_categoria', copertina = '$nuova_copertina' WHERE id = $categoria_id";

    if ($conn->query($sqlUpdate) === TRUE) {
        header('Location: https://www.zoiyoga.it/area-corsi-online/index.php');
    } else {
        echo "Errore durante l'aggiornamento della categoria: " . $conn->error;
    }
}

// Chiudi la connessione al database
$conn->close();

// Funzione per caricare un file e restituire il percorso
function uploadFile($file) {
    $targetDir = "images/"; // Cartella in cui verranno salvati i file
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));


    // Verifica se l'upload è andato a buon fine
    if ($uploadOk == 0) {
        echo "Errore durante l'upload della copertina.";
        exit();
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            echo "Errore durante il salvataggio della copertina.";
            exit();
        }
    }
}
?>
