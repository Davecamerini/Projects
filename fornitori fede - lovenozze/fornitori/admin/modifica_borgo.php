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
    $borgo_id = intval($_GET['id']);

    // Query per recuperare i dati del borgo
    $query = "SELECT * FROM borghi_scheda WHERE id=$borgo_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $borgo = $result->fetch_assoc();
    } else {
        echo "borgo non trovato.";
        exit;
    }
} else {
    echo "ID borgo non specificato.";
    exit;
}

// Recupera tutte le categorie disponibili
$query_categorie = "SELECT id, titolo FROM borghi_categorie";
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
    <title>Modifica borgo</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="../data/tinymce/js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#descrizione, #descrizione_due',
            height: 300,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
                'emoticons template paste textpattern'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | \
                     forecolor backcolor removeformat | \
                     link image media table | \
                     fontselect fontsizeselect | \
                     charmap emoticons | \
                     code fullscreen preview',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
            language: 'it',
            skin: 'oxide',
            branding: false,
            font_family_formats: 'Arial=arial,helvetica,sans-serif; \
                                Times New Roman=times new roman,times; \
                                Verdana=verdana,geneva; \
                                Tahoma=tahoma,arial,helvetica,sans-serif; \
                                Trebuchet MS=trebuchet ms,geneva; \
                                Georgia=georgia,times new roman,times,serif; \
                                Courier New=courier new,courier,monospace; \
                                Comic Sans MS=comic sans ms,sans-serif',
            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt'
        });
    </script>
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
            min-height: 100vh;
            position: relative;
        }

        /* Barra laterale */
        .sidebar {
            width: 250px;
            background-color: #1c1c1c;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
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
            margin-left: 250px; /* Same as sidebar width */
            min-height: 100vh;
        }
        h1 {
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
</head>
<body>

    <!-- Container principale della dashboard -->
    <div class="dashboard-container">
        <!-- Barra laterale -->
<?php include "partial/sidebar.php"; ?>

        <!-- Contenuto principale -->
        <div class="main-content">
            <div class="content-card">
                <h1>Modifica borgo</h1>

                <?php if (isset($borgo)) { ?>
                  <form method="POST" action="process/processa_modifica_borgo.php?id=<?php echo $borgo['id']; ?>" enctype="multipart/form-data">
                        <label class="mt-4">Ragione Sociale</label>
                        <input type="text" name="ragione_sociale" value="<?php echo $borgo['ragione_sociale']; ?>" class="form-control" required>

                        <label class="mt-4">Descrizione</label>
                        <textarea id="descrizione" name="descrizione" class="form-control" required><?php echo htmlspecialchars($borgo['descrizione']); ?></textarea>

                        <label class="mt-4">Citazione</label>
                        <input type="text" name="citazione" value="<?php echo $borgo['citazione']; ?>" class="form-control" required>

                        <label class="mt-4">Descrizione dopo la citazione</label>
                        <textarea id="descrizione_due" name="descrizione_due" class="form-control" required><?php echo htmlspecialchars($borgo['descrizione_due']); ?></textarea>

                        <label class="mt-4">Categoria</label>
                        <select name="categoria" class="form-control" required>
                            <?php foreach ($categorie as $categoria) { ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php if ($borgo['categoria_id'] == $categoria['id']) echo 'selected'; ?>>
                                    <?php echo $categoria['titolo']; ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label class="mt-4">Immagine di Copertina</label>
                        <div>
                            <img src="process/uploads/<?php echo $borgo['img_copertina']; ?>" alt="Copertina" style="width: 110px; height: auto; margin-bottom: 10px;border: 1px solid #d8d7d7;border-radius: 10px;" />
                            <input type="file" name="img_copertina" class="form-control">
                        </div>
                       <label class="mt-4">Gallery</label>
                       <div class="gallery-images" style="display: flex;align-items: flex-end;">
                           <?php
                           $gallery = explode(',', $borgo['gallery']); // Assume le immagini sono separate da virgole
                           foreach ($gallery as $image) { ?>
                               <div class="remove-image">
                                 <div class="remove-icon" onclick="removeImage('<?php echo trim($image); ?>')" style="padding: 0 19px;margin-bottom: -30px;text-align: right;color: red;font-weight: 900;position:relative;z-index:9">X</div>
                                   <img style="width: 90px!important; height: auto; border: 1px solid #cdcdcd;margin: 2px 10px;border-radius: 10px;" src="process/uploads/<?php echo trim($image); ?>" alt="Gallery Image" />
                               </div>
                           <?php } ?>
                       </div>
                       <input type="file" name="gallery[]" multiple class="form-control" style="margin-top: 10px;">

                        <label class="mt-4">Video (carica file)</label>
                        <div class="video-files" style="display: flex;align-items: flex-end;flex-wrap: wrap;gap: 20px;">
                            <?php
                            $videos = explode(',', $borgo['video_files'] ?? ''); // Assume i video sono separati da virgole
                            foreach ($videos as $video) {
                                if (!empty(trim($video))) { ?>
                                    <div class="remove-video" style="position: relative;width: 180px;">
                                        <div class="remove-icon" onclick="removeVideo('<?php echo trim($video); ?>')" style="position: absolute;top: -10px;right: -10px;z-index: 9;background: white;border-radius: 50%;width: 25px;height: 25px;display: flex;align-items: center;justify-content: center;cursor: pointer;box-shadow: 0 0 5px rgba(0,0,0,0.2);">X</div>
                                        <video style="width: 180px; height: auto; border: 1px solid #cdcdcd;border-radius: 10px;" controls>
                                            <source src="process/uploads/videos/<?php echo trim($video); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                            <?php }
                            } ?>
                        </div>
                        <input type="file" name="video_files[]" multiple accept="video/*" class="form-control" style="margin-top: 10px;">
                        <small class="form-text text-muted">Puoi caricare più video contemporaneamente.</small>

                        <label class="mt-4">Link Video (separati da virgola)</label>
                        <textarea name="video_links" class="form-control" rows="3" placeholder="Inserisci i link dei video, separati da virgola"><?php echo htmlspecialchars($borgo['video_links'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">Inserisci i link dei video da incorporare, separati da virgola.</small>

                        <label class="mt-4">Tag</label>
                        <input type="text" name="tag" value="<?php echo $borgo['tag']; ?>" class="form-control" required>

                        <label class="mt-4">Indirizzo</label>
                        <input type="text" name="indirizzo" value="<?php echo $borgo['indirizzo']; ?>" class="form-control" required>

                        <label class="mt-4">Regione</label>
                        <select name="regione" class="form-control" required>
                            <option value="">Seleziona una regione</option>
                            <option value="Abruzzo" <?php if ($borgo['regione'] == 'Abruzzo') echo 'selected'; ?>>Abruzzo</option>
                            <option value="Basilicata" <?php if ($borgo['regione'] == 'Basilicata') echo 'selected'; ?>>Basilicata</option>
                            <option value="Calabria" <?php if ($borgo['regione'] == 'Calabria') echo 'selected'; ?>>Calabria</option>
                            <option value="Campania" <?php if ($borgo['regione'] == 'Campania') echo 'selected'; ?>>Campania</option>
                            <option value="Emilia-Romagna" <?php if ($borgo['regione'] == 'Emilia-Romagna') echo 'selected'; ?>>Emilia-Romagna</option>
                            <option value="Friuli Venezia Giulia" <?php if ($borgo['regione'] == 'Friuli Venezia Giulia') echo 'selected'; ?>>Friuli Venezia Giulia</option>
                            <option value="Lazio" <?php if ($borgo['regione'] == 'Lazio') echo 'selected'; ?>>Lazio</option>
                            <option value="Liguria" <?php if ($borgo['regione'] == 'Liguria') echo 'selected'; ?>>Liguria</option>
                            <option value="Lombardia" <?php if ($borgo['regione'] == 'Lombardia') echo 'selected'; ?>>Lombardia</option>
                            <option value="Marche" <?php if ($borgo['regione'] == 'Marche') echo 'selected'; ?>>Marche</option>
                            <option value="Molise" <?php if ($borgo['regione'] == 'Molise') echo 'selected'; ?>>Molise</option>
                            <option value="Piemonte" <?php if ($borgo['regione'] == 'Piemonte') echo 'selected'; ?>>Piemonte</option>
                            <option value="Puglia" <?php if ($borgo['regione'] == 'Puglia') echo 'selected'; ?>>Puglia</option>
                            <option value="Sardegna" <?php if ($borgo['regione'] == 'Sardegna') echo 'selected'; ?>>Sardegna</option>
                            <option value="Sicilia" <?php if ($borgo['regione'] == 'Sicilia') echo 'selected'; ?>>Sicilia</option>
                            <option value="Toscana" <?php if ($borgo['regione'] == 'Toscana') echo 'selected'; ?>>Toscana</option>
                            <option value="Trentino-Alto Adige" <?php if ($borgo['regione'] == 'Trentino-Alto Adige') echo 'selected'; ?>>Trentino-Alto Adige</option>
                            <option value="Umbria" <?php if ($borgo['regione'] == 'Umbria') echo 'selected'; ?>>Umbria</option>
                            <option value="Valle d'Aosta" <?php if ($borgo['regione'] == "Valle d'Aosta") echo 'selected'; ?>>Valle d'Aosta</option>
                            <option value="Veneto" <?php if ($borgo['regione'] == 'Veneto') echo 'selected'; ?>>Veneto</option>
                        </select>

                        <label class="mt-4">Email</label>
                        <input type="email" name="email" value="<?php echo $borgo['email']; ?>" class="form-control" required>

                        <label class="mt-4">Telefono</label>
                        <input type="text" name="telefono" value="<?php echo $borgo['telefono']; ?>" class="form-control" required>

                        <label class="mt-4">WhatsApp</label>
                        <input type="text" name="whatsapp" value="<?php echo $borgo['whatsapp']; ?>" class="form-control" required>

                        <label class="mt-4">Votazione Complessiva</label>
                        <input type="number" name="votazione_complessiva" value="<?php echo $borgo['votazione_complessiva']; ?>" class="form-control" min="0" max="5">

                        <button type="submit" class="btn btn-primary mt-4">Salva Modifiche</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <script>
         function removeImage(imagePath) {
             // Esegui la logica per rimuovere l'immagine dalla gallery
             if (confirm('Sei sicuro di voler rimuovere questa immagine?')) {
                 // Invia una richiesta AJAX o reindirizza a un endpoint per rimuovere l'immagine
                 const form = new FormData();
                 form.append('action', 'remove_image');
                 form.append('image', imagePath);
                 form.append('id', '<?php echo $borgo['id']; ?>'); // Passa l'ID del borgo

                 fetch('process/remove_image_borgo.php', {
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

         function removeVideo(videoPath) {
             if (confirm('Sei sicuro di voler rimuovere questo video?')) {
                 const form = new FormData();
                 form.append('action', 'remove_video');
                 form.append('video', videoPath);
                 form.append('id', '<?php echo $borgo['id']; ?>');
                 form.append('type', 'borgo');

                 fetch('process/remove_video.php', {
                     method: 'POST',
                     body: form
                 })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         location.reload();
                     } else {
                         alert('Errore nella rimozione del video.');
                     }
                 })
                 .catch(error => console.error('Errore:', error));
             }
         }
     </script>
</body>
</html>
