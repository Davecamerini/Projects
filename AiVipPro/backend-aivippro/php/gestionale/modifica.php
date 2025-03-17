<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database


$conn = new mysqli($host, $username, $password, $database);

// Verifica se è stato passato un parametro "id" nella query string
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = $_GET["id"];

    // Esegui la query per recuperare i dati del video
    $sql = "SELECT * FROM corsi_online WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Ora puoi utilizzare i dati per popolare il modulo di modifica
    } else {
        echo "Nessun video trovato con questo ID.";
    }
} else {
    echo "ID non valido.";
}

// Verifica se è stato passato un parametro "cat" nella query string
if (isset($_GET["cat"]) && is_numeric($_GET["cat"])) {
    $cat = $_GET["cat"];
} else {
  /**/
}
// Esegui la query per ottenere tutte le categorie
$sqlCategorie = "SELECT id, categoria FROM categorie_corsi_online";
$resultCategorie = $conn->query($sqlCategorie);

// Chiudi la connessione al database
$conn->close();


if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}
$cookie_name = "utente_id_admin"; // Sostituisci con il nome del tuo cookie
if (!isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
  header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Video</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #fff;
        }
        header {
            background-color: #fcf6f5;
            padding: 10px 0;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;

        }
        h2 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        form {
          padding: 40px!important;
          background: #510905!important;
          border: unset!important;
          width: 90%!important;
          margin: 30px auto!important;
          padding: 50px 5%!important;
          border-radius: 14px!important;
        }
        label {
            margin-top: 10px;
            color: #fff;
        }
        input,select {
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
            width: 100%!important;
        }
        button {
            background-color: #990011;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff3d3d;
        }
    </style>
<link rel="stylesheet" href="/area-corsi-online/css/style.css">
</head>
<body>
  <?php include "../../partial/header.php"; ?>
    <header>
        <h1 style="font-size:30px;text-align:center;color:white!important;width:100%;margin:auto;">Modifica Elemento</h1>
    </header>
    <div class="container">
        <form method="post" action="processa_modifica.php">
            <!-- Aggiungi campi del modulo qui, usando i dati recuperati dal database -->
            <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
            <label for="titolo">Nuovo Titolo:</label>
            <input type="text" name="titolo" value="<?php echo $row["titolo"]; ?>" required>

            <label for="link">Nuovo Link:</label>
            <input type="text" name="link" value="<?php echo $row["file_video"]; ?>" required>

            <label for="categoria">Categoria:</label>
              <select name="categoria" required>
                  <?php
                  if ($resultCategorie->num_rows > 0) {
                      while ($rowCategoria = $resultCategorie->fetch_assoc()) {
                          $selected = ($rowCategoria['id'] == $cat) ? 'selected' : '';
                          echo "<option value='{$rowCategoria['id']}' $selected>{$rowCategoria['categoria']}</option>";
                      }
                  } else {
                      echo "<option value='' disabled>Nessuna categoria trovata</option>";
                  }
                  ?>
              </select>

            <label for="data_inserimento">Nuova Data di Inserimento:</label>
            <input type="date" name="data_inserimento" value="<?php echo $row["data_inserimento"]; ?>" required>

            <!-- Aggiungi altri campi del modulo qui -->

            <button type="submit">Salva Modifiche</button>
        </form>
    </div>
      <?php include "../../partial/footer.php"; ?>
</body>
</html>
