<?php
// Includi WordPress
require_once('../../wp-load.php'); // Modifica il percorso a seconda della tua configurazione

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Funzione per generare lo slug dal titolo
function genera_slug($titolo) {
    // Rimuove caratteri non alfanumerici
    $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($titolo)));
    // Rimuove eventuali trattini all'inizio o alla fine
    $slug = trim($slug, '-');
    return $slug;
}

// Inserimento categoria
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titolo = $conn->real_escape_string($_POST['titolo']);
    $descrizione = $conn->real_escape_string($_POST['descrizione']);
    $genitore = (int) $_POST['genitore']; // Può essere 0 o un ID valido
    $data_inserimento = date('Y-m-d H:i:s');
    $slug = genera_slug($titolo);

    // Gestione immagine
    $immagine = '';
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
        $target_dir = "process/uploads/";
        $target_file = $target_dir . basename($_FILES["immagine"]["name"]);
        move_uploaded_file($_FILES["immagine"]["tmp_name"], $target_file);
        $immagine = basename($_FILES["immagine"]["name"]);
    }

    // Query per inserire la nuova categoria
    $insert_query = "INSERT INTO fornitori_categorie (titolo, slug, descrizione, genitore, data_inserimento, immagine)
                     VALUES ('$titolo', '$slug', '$descrizione', '$genitore', '$data_inserimento', '$immagine')";

    if ($conn->query($insert_query) === TRUE) {
        echo "<div class='alert alert-success'>Nuova categoria inserita con successo.</div>";
    } else {
        echo "<div class='alert alert-danger'>Errore: " . $conn->error . "</div>";
    }
}

// Query per ottenere tutte le categorie (per selezionare il genitore)
$categories = $conn->query("SELECT id, titolo FROM fornitori_categorie WHERE genitore = 0");

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Fornitore</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDyi2qjyYB4_WUBAW-2KXVgPL8zhRvAFOI&libraries=places"></script>

    <style>
        /* Stile generale */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Stile del container principale */
        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        /* Barra laterale */
        .sidebar {
            width: 250px;
            background-color: #1c1c1c;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
        }
        .sidebar a {
            color: white;
            padding: 10px 0;
            text-decoration: none;
            font-size: 18px;
            border-bottom: 1px solid #444;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            color: #c0c0c0;
            text-decoration: none;
            transition: color 0.3s ease-in-out;
        }

        /* Stile del contenuto principale */
        .main-content {
            flex-grow: 1;
            padding: 40px;
            background-color: #f3f3f3;
        }
        h1, h2 {
            color: #444;
        }

        /* Stile per i contenuti della sezione fornitori/categorie */
        .content-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        // Google Places Autocomplete Initialization
        function initialize() {
            var input = document.getElementById('indirizzo');
            var autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();

                // Riempire latitudine e longitudine
                document.getElementById('latitudine').value = place.geometry.location.lat();
                document.getElementById('longitudine').value = place.geometry.location.lng();
            });
        }

        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
</head>
<body>

  <div class="dashboard-container">
    <?php include "partial/sidebar.php"; ?>

      <!-- Contenuto principale -->
      <div class="main-content">
<div class="content-card">
  <div style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; justify-content: space-between;">
    <h2 class="mt-2 mb-4">Inserisci Nuova Categoria</h2>
    <a href='dashboard.php?page=categorie' class='btn btn-sm btn-dark'>Torna alla gestione categorie</a>
  </div>

  <form method="POST" enctype="multipart/form-data" class="form">
    <div class="form-group">
      <label for="titolo">Titolo Categoria</label>
      <input type="text" name="titolo" id="titolo" class="form-control" placeholder="Inserisci il titolo della categoria" required>
    </div>
    <!-- promemoria: usare quill per creare un campo con editor di testo html -->
    <div class="form-group">
      <label for="descrizione">Descrizione</label>
      <textarea name="descrizione" id="descrizione" class="form-control" rows="3" placeholder="Inserisci la descrizione della categoria"></textarea>
    </div>

    <div class="form-group">
      <label for="genitore">Categoria Genitore</label>
      <select name="genitore" id="genitore" class="form-control">
        <option value="0">Nessuna (Categoria di Primo Livello)</option>
        <?php
        if ($categories->num_rows > 0) {
            while ($category = $categories->fetch_assoc()) {
                echo "<option value='" . $category['id'] . "'>" . $category['titolo'] . "</option>";
            }
        }
        ?>
      </select>
    </div>

    <div class="form-group">
      <label for="immagine">Immagine Categoria</label>
      <input type="file" name="immagine" id="immagine" class="form-control-file">
    </div>

    <button type="submit" class="btn btn-success">Inserisci Categoria</button>
  </form>
</div>
</div>
</div>
</body>
</html>

<?php
$conn->close();
?>
