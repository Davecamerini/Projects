<?php
require_once('../../wp-load.php'); // Assicurati che il percorso sia corretto per il tuo progetto

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

// Connessione al database per recuperare le categorie
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupera le categorie disponibili
$category_query = "SELECT id, titolo FROM borghi_categorie";
$categories_result = $conn->query($category_query);


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci borgo</title>
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

        /* Stile per i contenuti della sezione borghi/categorie */
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
    <script>
    // Funzione per creare lo slug
    function createSlug(string) {
        let slug = string.toLowerCase().trim(); // Converti in minuscolo e rimuovi spazi iniziali/finali
        slug = slug.replace(/[^a-z0-9-]+/g, '-'); // Sostituisci caratteri non ammessi con "-"
        slug = slug.replace(/-+/g, '-'); // Rimuovi "-" duplicati
        return slug.replace(/^-+|-+$/g, ''); // Rimuovi "-" iniziali e finali
    }

    // Aggiorna automaticamente lo slug
    function updateSlug() {
        const ragioneSociale = document.getElementById('ragione_sociale').value; // Valore inserito in "Ragione Sociale"
        const slug = createSlug(ragioneSociale); // Genera lo slug
        document.getElementById('slug').value = slug; // Popola il campo slug
    }
</script>
</head>
<body>

    <!-- Container principale della dashboard -->
    <div class="dashboard-container">
      <?php include "partial/sidebar.php"; ?>

        <!-- Contenuto principale -->
        <div class="main-content">
            <div class="content-card">
                <h2>Inserisci Borgo</h2>
                <form action="process/inserisci_borgo.php" method="POST" enctype="multipart/form-data">
                  <div class="form-group">
                      <label for="ragione_sociale">Nome</label>
                      <input type="text" class="form-control" id="ragione_sociale" name="ragione_sociale" oninput="updateSlug()" required>
                  </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="" required>
                    </div>


                    <div class="form-group">
                        <label for="descrizione">Descrizione</label>
                        <?php
                        // Usa l'editor di WordPress per la descrizione
                        $settings = array(
                            'textarea_name' => 'descrizione',
                            'media_buttons' => false,
                            'textarea_rows' => 10,
                            'teeny' => true,
                            'quicktags' => false
                        );
                        wp_editor('', 'descrizione', $settings);
                        ?>
                    </div>

                    <div class="form-group">
                        <label for="citazione">Citazione</label>
                        <input type="text" class="form-control" id="citazione" name="citazione" required>
                    </div>

                    <div class="form-group">
                        <label for="descrizione_due">Descrizione Due</label>
                        <?php
                        // Usa l'editor di WordPress per la descrizione due
                        $settings_descrizione_due = array(
                            'textarea_name' => 'descrizione_due',
                            'media_buttons' => false,
                            'textarea_rows' => 10,
                            'teeny' => true,
                            'quicktags' => false
                        );
                        wp_editor('', 'descrizione_due', $settings_descrizione_due);
                        ?>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select class="form-control" id="categoria" name="categoria" required>
                            <option value="">Seleziona una categoria</option>
                            <?php
                            // Popola il dropdown con le categorie
                            if ($categories_result->num_rows > 0) {
                                while ($category = $categories_result->fetch_assoc()) {
                                    echo '<option value="' . $category['id'] . '">' . $category['titolo'] . '</option>';
                                }
                            } else {
                                echo '<option value="">Nessuna categoria disponibile</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="img_copertina">Immagine di Copertina</label>
                        <input type="file" class="form-control" id="img_copertina" name="img_copertina">
                    </div>

                    <div class="form-group">
                        <label for="gallery">Galleria</label>
                        <input type="file" class="form-control" id="gallery" name="gallery[]" multiple>
                    </div>

                    <div class="form-group">
                        <label for="tag">Tag (separati da virgola)</label>
                        <input type="text" class="form-control" id="tag" name="tag">
                    </div>

                    <div class="form-group">
                        <label for="indirizzo">Indirizzo</label>
                        <input type="text" class="form-control" id="indirizzo" name="indirizzo">
                    </div>

                    <!-- Campi nascosti per latitudine e longitudine -->
                    <input type="hidden" id="latitudine" name="latitudine">
                    <input type="hidden" id="longitudine" name="longitudine">

                    <div class="form-group">
                      <label for="regione">Regione</label>
                      <select class="form-control" id="regione" name="regione" required>
                          <option value="">Seleziona una regione</option>
                          <option value="Abruzzo">Abruzzo</option>
                          <option value="Basilicata">Basilicata</option>
                          <option value="Calabria">Calabria</option>
                          <option value="Campania">Campania</option>
                          <option value="Emilia-Romagna">Emilia-Romagna</option>
                          <option value="Friuli Venezia Giulia">Friuli Venezia Giulia</option>
                          <option value="Lazio">Lazio</option>
                          <option value="Liguria">Liguria</option>
                          <option value="Lombardia">Lombardia</option>
                          <option value="Marche">Marche</option>
                          <option value="Molise">Molise</option>
                          <option value="Piemonte">Piemonte</option>
                          <option value="Puglia">Puglia</option>
                          <option value="Sardegna">Sardegna</option>
                          <option value="Sicilia">Sicilia</option>
                          <option value="Toscana">Toscana</option>
                          <option value="Trentino-Alto Adige">Trentino-Alto Adige</option>
                          <option value="Umbria">Umbria</option>
                          <option value="Valle d'Aosta">Valle d'Aosta</option>
                          <option value="Veneto">Veneto</option>
                      </select>
                  </div>


                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Telefono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Whatsapp</label>
                        <input type="text" class="form-control" id="whatsapp" name="whatsapp">
                    </div>

                    <div class="form-group">
                        <label for="votazione_complessiva">Votazione Complessiva</label>
                        <input type="number" class="form-control" id="votazione_complessiva" min="0" max="5" name="votazione_complessiva">
                    </div>

                    <div class="form-group">
                        <label for="video_links">Link Video (uno per riga)</label>
                        <textarea class="form-control" id="video_links" name="video_links" rows="3" placeholder="Inserisci i link dei video, uno per riga"></textarea>
                        <small class="form-text text-muted">Inserisci i link dei video che vuoi incorporare, uno per riga. Esempio: https://www.youtube.com/watch?v=...</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Inserisci borgo</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>
