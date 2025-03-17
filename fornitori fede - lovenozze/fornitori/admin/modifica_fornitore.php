<?php
// Includi WordPress
require_once('../../wp-load.php'); // Cambia questo percorso secondo la tua installazione di WordPress

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $fornitore_id = intval($_GET['id']);

    // Query per recuperare i dati del fornitore
    $query = "SELECT * FROM fornitori_scheda WHERE id=$fornitore_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
    } else {
        echo "Fornitore non trovato.";
        exit;
    }
} else {
    echo "ID fornitore non specificato.";
    exit;
}

// Recupera tutte le categorie disponibili
$query_categorie = "SELECT id, titolo FROM fornitori_categorie";
$result_categorie = $conn->query($query_categorie);

$categorie = [];
if ($result_categorie->num_rows > 0) {
    while ($row = $result_categorie->fetch_assoc()) {
        $categorie[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Fornitore</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        h1 {
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
</head>
<body>

    <!-- Container principale della dashboard -->
    <div class="dashboard-container">
        <!-- Barra laterale -->
<?php include "partial/sidebar.php"; ?>

        <!-- Contenuto principale -->
        <div class="main-content">
            <div class="content-card">
                <h1>Modifica Fornitore</h1>

                <?php if (isset($fornitore)) { ?>
                  <form method="POST" action="process/processa_modifica_fornitore.php?id=<?php echo $fornitore['id']; ?>" enctype="multipart/form-data">
                        <label class="mt-4">Ragione Sociale</label>
                        <input type="text" name="ragione_sociale" value="<?php echo $fornitore['ragione_sociale']; ?>" class="form-control" required>

                        <label class="mt-4">Descrizione</label>
                        <textarea id="descrizione" name="descrizione" class="form-control" required><?php echo htmlspecialchars($fornitore['descrizione']); ?></textarea>

                        <label class="mt-4">Citazione</label>
                        <input type="text" name="citazione" value="<?php echo $fornitore['citazione']; ?>" class="form-control" required>

                        <label class="mt-4">Descrizione dopo la citazione</label>
                        <textarea id="descrizione_due" name="descrizione_due" class="form-control" required><?php echo htmlspecialchars($fornitore['descrizione_due']); ?></textarea>

                        <label class="mt-4">Categoria</label>
                        <select name="categoria" class="form-control" required>
                            <?php foreach ($categorie as $categoria) { ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php if ($fornitore['categoria_id'] == $categoria['id']) echo 'selected'; ?>>
                                    <?php echo $categoria['titolo']; ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label class="mt-4">Immagine di Copertina</label>
                        <div>
                            <img src="process/uploads/<?php echo $fornitore['img_copertina']; ?>" alt="Copertina" style="width: 110px; height: auto; margin-bottom: 10px;border: 1px solid #d8d7d7;border-radius: 10px;" />
                            <input type="file" name="img_copertina" class="form-control">
                        </div>
                       <label class="mt-4">Gallery</label>
                       <div class="gallery-images" style="display: flex;align-items: flex-end;">
                           <?php
                           $gallery = explode(',', $fornitore['gallery']); // Assume le immagini sono separate da virgole
                           foreach ($gallery as $image) { ?>
                               <div class="remove-image">
                                 <div class="remove-icon" onclick="removeImage('<?php echo trim($image); ?>')" style="padding: 0 19px;margin-bottom: -30px;text-align: right;color: red;font-weight: 900;position:relative;z-index:9">X</div>
                                   <img style="width: 90px!important; height: auto; border: 1px solid #cdcdcd;margin: 2px 10px;border-radius: 10px;" src="process/uploads/<?php echo trim($image); ?>" alt="Gallery Image" />
                               </div>
                           <?php } ?>
                       </div>
                       <input type="file" name="gallery[]" multiple class="form-control" style="margin-top: 10px;">

                        <label class="mt-4">Tag</label>
                        <input type="text" name="tag" value="<?php echo $fornitore['tag']; ?>" class="form-control" required>

                        <label class="mt-4">Indirizzo</label>
                        <input type="text" name="indirizzo" value="<?php echo $fornitore['indirizzo']; ?>" class="form-control" required>
                        <!-- Campi nascosti per latitudine e longitudine -->
                        <input type="hidden" id="latitudine" name="latitudine">
                        <input type="hidden" id="longitudine" name="longitudine">

                        <label class="mt-4">Regione</label>
                        <select name="regione" class="form-control" required>
                            <option value="">Seleziona una regione</option>
                            <option value="Abruzzo" <?php if ($fornitore['regione'] == 'Abruzzo') echo 'selected'; ?>>Abruzzo</option>
                            <option value="Basilicata" <?php if ($fornitore['regione'] == 'Basilicata') echo 'selected'; ?>>Basilicata</option>
                            <option value="Calabria" <?php if ($fornitore['regione'] == 'Calabria') echo 'selected'; ?>>Calabria</option>
                            <option value="Campania" <?php if ($fornitore['regione'] == 'Campania') echo 'selected'; ?>>Campania</option>
                            <option value="Emilia-Romagna" <?php if ($fornitore['regione'] == 'Emilia-Romagna') echo 'selected'; ?>>Emilia-Romagna</option>
                            <option value="Friuli Venezia Giulia" <?php if ($fornitore['regione'] == 'Friuli Venezia Giulia') echo 'selected'; ?>>Friuli Venezia Giulia</option>
                            <option value="Lazio" <?php if ($fornitore['regione'] == 'Lazio') echo 'selected'; ?>>Lazio</option>
                            <option value="Liguria" <?php if ($fornitore['regione'] == 'Liguria') echo 'selected'; ?>>Liguria</option>
                            <option value="Lombardia" <?php if ($fornitore['regione'] == 'Lombardia') echo 'selected'; ?>>Lombardia</option>
                            <option value="Marche" <?php if ($fornitore['regione'] == 'Marche') echo 'selected'; ?>>Marche</option>
                            <option value="Molise" <?php if ($fornitore['regione'] == 'Molise') echo 'selected'; ?>>Molise</option>
                            <option value="Piemonte" <?php if ($fornitore['regione'] == 'Piemonte') echo 'selected'; ?>>Piemonte</option>
                            <option value="Puglia" <?php if ($fornitore['regione'] == 'Puglia') echo 'selected'; ?>>Puglia</option>
                            <option value="Sardegna" <?php if ($fornitore['regione'] == 'Sardegna') echo 'selected'; ?>>Sardegna</option>
                            <option value="Sicilia" <?php if ($fornitore['regione'] == 'Sicilia') echo 'selected'; ?>>Sicilia</option>
                            <option value="Toscana" <?php if ($fornitore['regione'] == 'Toscana') echo 'selected'; ?>>Toscana</option>
                            <option value="Trentino-Alto Adige" <?php if ($fornitore['regione'] == 'Trentino-Alto Adige') echo 'selected'; ?>>Trentino-Alto Adige</option>
                            <option value="Umbria" <?php if ($fornitore['regione'] == 'Umbria') echo 'selected'; ?>>Umbria</option>
                            <option value="Valle d'Aosta" <?php if ($fornitore['regione'] == "Valle d'Aosta") echo 'selected'; ?>>Valle d'Aosta</option>
                            <option value="Veneto" <?php if ($fornitore['regione'] == 'Veneto') echo 'selected'; ?>>Veneto</option>
                        </select>

                        <label class="mt-4">Email</label>
                        <input type="email" name="email" value="<?php echo $fornitore['email']; ?>" class="form-control" required>

                        <label class="mt-4">Telefono</label>
                        <input type="text" name="telefono" value="<?php echo $fornitore['telefono']; ?>" class="form-control" required>

                        <label class="mt-4">WhatsApp</label>
                        <input type="text" name="whatsapp" value="<?php echo $fornitore['whatsapp']; ?>" class="form-control" required>

                        <label class="mt-4">Votazione Complessiva</label>
                        <input type="text" name="votazione_complessiva" value="<?php echo $fornitore['votazione_complessiva']; ?>" class="form-control" required>

                        <button type="submit" class="btn btn-primary">Salva modifiche</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
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
    <script>
         function removeImage(imagePath) {
             // Esegui la logica per rimuovere l'immagine dalla gallery
             if (confirm('Sei sicuro di voler rimuovere questa immagine?')) {
                 // Invia una richiesta AJAX o reindirizza a un endpoint per rimuovere l'immagine
                 const form = new FormData();
                 form.append('action', 'remove_image');
                 form.append('image', imagePath);
                 form.append('id', '<?php echo $fornitore['id']; ?>'); // Passa l'ID del fornitore

                 fetch('process/remove_image.php', {
                     method: 'POST',
                     body: form
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         location.reload(); // Ricarica la pagina per aggiornare le immagini
                     } else {
                         alert('Errore nella rimozione dell\'immagine.');
                     }
                 })
                 .catch(error => console.error('Errore:', error));
             }
         }
     </script>
</body>
</html>
