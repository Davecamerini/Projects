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
if (!isset($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
  header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
  exit();
}

$sqlCategorie = "SELECT id, categoria FROM categorie_corsi_online";
$resultCategorie = $conn->query($sqlCategorie);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titolo = $_POST["titolo"];
    $link = $_POST["link"];
    $data_inserimento = $_POST["data_inserimento"];
    $stato = isset($_POST["stato"]) ? 1 : 0;
    $categoria = $_POST["categoria"];

    // Gestione del caricamento del file video
    $target_directory = "/area-corsi-online/php/gestionale/video/";
    $target_file = $target_directory . basename($_FILES["file_video"]["name"]);
    $uploadOk = 1;
    $videoFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Verifica se il file esiste già
    if (file_exists($target_file)) {
        echo "Spiacenti, il file audio esiste già.";
        $uploadOk = 0;
    }

    // Verifica la dimensione massima del file (400 MB)
    if ($_FILES["file_video"]["size"] > 400000000) {
        echo "Spiacenti, il file video è troppo grande.";
        $uploadOk = 0;
    }

    // Accetta solo determinati formati di file audio (MP3)
    if ($videoFileType != "mp3") {
        echo "Spiacenti, sono consentiti solo file audio MP3. Il file che hai provato a caricare è di tipo: $videoFileType";
        $uploadOk = 0;
    }
    
    // Se $uploadOk è impostato su 0 a causa di un errore, mostra un messaggio di errore
    if ($uploadOk == 0) {
        echo "Spiacenti, il tuo file non è stato caricato.";
    } else {
        // Se tutto è ok, prova a caricare il file
        if (move_uploaded_file($_FILES["file_video"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $target_file)) {
              // Utilizza una dichiarazione preparata per prevenire SQL injection
              $sql = "INSERT INTO corsi_online (titolo, link, data_inserimento, stato, categoria, file_video) VALUES (?, ?, ?, ?, ?, ?)";
              $stmt = $conn->prepare($sql);

              if ($stmt) {
                  // Associa i parametri e esegui la query
                  $stmt->bind_param("sssiis", $titolo, $link, $data_inserimento, $stato, $categoria, $target_file);
                  $stmt->execute();

                  if ($stmt->affected_rows > 0) {
                    header("Location: https://www.zoiyoga.it/area-corsi-online/php/gestionale/setting.php");
                      exit;
                  } else {
                      echo "Errore nell'inserimento del record: " . $stmt->error;
                  }

                // Chiudi la dichiarazione preparata
                $stmt->close();
            } else {
                echo "Errore nella preparazione della query: " . $conn->error;
            }
        } else {
            echo "Spiacenti, si è verificato un errore durante il caricamento del file.";
        }
    }
}
// Chiudi la connessione al database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Form di Inserimento Video</title>
    <style>

     h2 {
         text-align: center;
         margin-bottom: 30px;
     }

     input, select {
         width: 100%;
         padding: 8px;
         margin-bottom: 16px;
         box-sizing: border-box;
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
         background-color: #990011;
     }
     .info-icon{
      display: inline-flex;
      flex-direction: row;
      justify-content: center;
     }
     .icon-button-bottom{
       display: flex;
        justify-content: center;
        align-items: center;
        align-content: center;
        flex-wrap: nowrap;
        flex-direction: row;
        max-width: 420px;
        margin: auto;
     }
 </style>
 <script>
    function showLoader() {
        document.getElementById('loader').style.display = 'block';
    }

    function hideLoader() {
        document.getElementById('loader').style.display = 'none';
    }
</script>
</head>
<body>
  <?php include "../../partial/header.php"; ?>
  <div class="container mt-5">
         <h2>Inserimento Audio</h2>
         <div id="loader" style="display: none; text-align: center; position: absolute;width: 100%;margin: auto;background: #000000ba;;left: 0;top: 0%;height: 100%;padding: 10%;font-size: 30px;text-transform: uppercase;">
              <img src="images/loader.gif" alt="Caricamento in corso..." style="width: 84px; border-radius: 300px;">
              <p>Caricamento in corso...</p>
          </div>
         <form class="form-insert" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
             <div class="mb-3">
                 <label for="titolo" class="form-label">Titolo</label>
                 <input type="text" class="form-control" id="titolo" name="titolo" required>
             </div>
             <div class="mb-3 hidden" style="display: none;">
                 <label for="link" class="form-label">Link (solo se esterno es. vimeo)</label>
                 <input type="text" class="form-control" id="link" name="link" >
             </div>
             <div class="mb-3">
                 <label for="data_inserimento" class="form-label">Data di Inserimento</label>
                 <input type="date" class="form-control" id="data_inserimento" name="data_inserimento" required>
             </div>
             <div class="mb-3">
                 <label for="categoria" class="form-label">Categoria</label>
                 <select class="form-select" id="categoria" name="categoria" required>
                     <?php
                     if ($resultCategorie->num_rows > 0) {
                         while ($rowCategoria = $resultCategorie->fetch_assoc()) {
                             echo "<option value='{$rowCategoria['id']}'>{$rowCategoria['categoria']}</option>";
                         }
                     } else {
                         echo "<option value='' disabled>Nessuna categoria trovata</option>";
                     }
                     ?>
                 </select>
             </div>
             <div class="mb-3">
                 <label for="file_video" class="form-label">File Audio (MP3)</label>
                 <input type="file" class="form-control" id="file_video" name="file_video" accept="audio/mp3" required>
             </div>
             <div class="mb-3">
                 <label for="stato">Stato</label>
                 <input style="width: 25px;display:inline;" type="checkbox" name="stato" checked> Attivo/Non attivo
             </div>
             <button type="submit" class="btn btn-primary" style="background:#990011;border-color:#990011;" onclick="showLoader()">Inserisci</button>
         </form>
         <br><br>
         <div class="icon-button-bottom"><div class="info-icon" style="margin:auto;text-align:center; width:100%;"><a href="/area-corsi-online/index.php" style="background: #990011; padding: 8px 12px; border-radius: 10px; color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna in home</a></div>  <div class="info-icon" style="margin:auto;text-align:center; width:100%;"><a href="setting.php" style="background: #990011; padding: 8px 12px; border-radius: 10px; color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna alla lista</a></div></div>
    <br><br> </div>
     <?php include "../../partial/footer.php"; ?>
</body>
</html>
