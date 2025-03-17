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

// Verifica se è stato passato un parametro "id" nella query string
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = $_GET["id"];

    // Esegui la query per recuperare i dati dell'utente
    $sql = "SELECT * FROM abbonamenti_online WHERE order_id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Ora puoi utilizzare i dati per popolare il modulo di modifica
    } else {
        echo "Nessun utente trovato con questo ID.";
        exit();
    }
} else {
    echo "ID non valido.";
    exit();
}

// Chiudi la connessione al database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Utente</title>
    <!-- Aggiungi eventuali stili CSS aggiuntivi qui -->
    <style>

       .container-mask {
           max-width: 800px;
           margin: 20px auto;
           padding: 20px;
           background-color: #fcf6f5;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
       }
       h2 {
           text-align: center;
       }
       form {
           display: flex;
           flex-direction: column;
           border-width: 0!important;
       }
       label {
           margin-top: 10px;
           color: #333;
       }
       input {
           padding: 10px;
           margin-top: 5px;
           margin-bottom: 15px;
       }
       .primario{
         background: #991b0f!important;
         border-radius: 10px;
         padding: 12px 18px;
         color: white;
       }

   </style>
         <?php include "../../partial/header.php"; ?>
</head>
<body>

    <h2>Modifica Utente</h2>
    <form method="post" action="processa_modifica_utente.php" class="container-mask">
        <!-- Aggiungi campi del modulo qui, usando i dati recuperati dal database -->
        <input type="hidden" name="id" value="<?php echo $row["order_id"]; ?>">
        <label for="first_name">Nuovo Nome:</label>
        <input type="text" name="first_name" value="<?php echo $row["first_name"]; ?>" required><br>

        <label for="last_name">Nuovo Cognome:</label>
        <input type="text" name="last_name" value="<?php echo $row["last_name"]; ?>" required><br>

        <label for="email">Nuova Email:</label>
        <input type="email" name="email" value="<?php echo $row["email"]; ?>" required><br>

        <!-- Aggiungi altri campi del modulo qui -->

        <button class="primario" type="submit">Salva Modifiche</button>
    </form>
        <?php include "../../partial/footer.php"; ?>
</body>
</html>
