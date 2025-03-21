<?php
// Includi WordPress
require_once('../wp-load.php');
get_header();

// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

// Controllo della connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupera lo slug dalla query GET
$slug_borgo = isset($_GET['slug']) ? trim($_GET['slug']) : '';
// Recupera id dalla query GET
$id_borgo = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($slug_borgo)) {
    echo "<p class='text-center'>borgo non trovato.</p>";
    get_footer();
    $conn->close();
    exit;
}

// Query per ottenere i dettagli del borgo usando lo slug
$sql = "SELECT img_copertina, ragione_sociale, slug, latitudine, longitudine, tag, telefono, whatsapp, indirizzo, email, descrizione, descrizione_due, citazione, gallery
        FROM borghi_scheda WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $slug_borgo);
$stmt->execute();
$result = $stmt->get_result();

// Controlla se il borgo esiste
if ($result->num_rows > 0) {
    $borgo = $result->fetch_assoc();
} else {
    echo "<p class='text-center'>borgo non trovato.</p>";
    get_footer();
    $conn->close();
    exit;
}

// Recupero campi
$img_copertina = !empty($borgo['img_copertina']) ? 'https://www.lovenozze.it/fornitori/admin/process/uploads/' . $borgo['img_copertina'] : 'https://via.placeholder.com/400x600';
$ragione_sociale = $borgo['ragione_sociale'];
$slug = $borgo['slug'];
$titolo = $borgo['ragione_sociale'];
$tag = explode(',', $borgo['tag']);
$telefono = $borgo['telefono'];
$whatsapp = $borgo['whatsapp'];
$indirizzo = $borgo['indirizzo'];
$email = $borgo['email'];
$descrizione = $borgo['descrizione'];
$descrizione_due = $borgo['descrizione_due'];
$citazione = $borgo['citazione'];
$gallery = explode(',', $borgo['gallery']);
$latitudine = $borgo['latitudine'];
$longitudine = $borgo['longitudine'];

?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo esc_html(mb_strimwidth($descrizione, 0, 160, '...')); ?>">
  <title><?php echo esc_html($titolo); ?></title>
  <link rel="canonical" href="https://www.lovenozze.it/borghi/<?php echo $slug; ?>/">
    <title><?php echo esc_html($titolo); ?> - Dettaglio borgo</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="style/style-scheda.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Lightbox2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <!-- Lightbox2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <!-- Schema.org Rich Snippets -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "<?php echo esc_html($ragione_sociale); ?>",
      "address": "<?php echo esc_html($indirizzo); ?>",
      "telephone": "<?php echo esc_html($telefono); ?>",
      "email": "<?php echo esc_html($email); ?>",
      "image": "<?php echo esc_url($img_copertina); ?>",
      "url": "https://www.lovenozze.it/borghi/<?php echo $slug; ?>/"
    }
    </script>
</head>

<body>

<!-- Sezione contenuti -->
<div class="content-page-borgo">
    <!-- Tabella per layout 1/3 e 2/3 -->
    <div class="content-page-borgo">
      <!-- Contenuto principale del borgo diviso in due colonne con Flexbox -->
    <a href="https://www.lovenozze.it/borghi/" style="color:rgba(139, 119, 128, 1); text-align: right; width: 100%; FONT-WEIGHT: 700; margin: auto; display: block; text-decoration:none;">Tutti i borghi <svg class="qodef-svg--button-arrow" style="width:10px; fill: rgba(139, 119, 128, 1);position: absolute;" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 11.9 11.9" xml:space="preserve"><path class="qodef-path" d="M.4 11.5 11.4.6M11.4 10V.5H1.9"></path><path class="qodef-path" d="M.4 11.5 11.4.6M11.4 10V.5H1.9"></path></svg></a>
      <div class="borgo-container d-flex flex-wrap">
          <!-- Colonna sinistra: Immagine copertina -->
          <div class="left-column" style="width: 40%;">
              <div class="container-img">
                  <img src="<?php echo esc_url($img_copertina); ?>" alt="<?php echo esc_attr($titolo); ?>" class="fixed-image">
              </div>
          </div>

          <!-- Colonna destra: Informazioni borgo -->
          <div class="right-column" style="width: 60%;">
              <h1><?php echo esc_html($ragione_sociale); ?></h1>

              <!-- Tag -->
              <div class="tags mt-2 mb-3">
                  <?php foreach ($tag as $single_tag): ?>
                      <span class="tag"><?php echo esc_html(trim($single_tag)); ?></span>
                  <?php endforeach; ?>
              </div>

              <!-- Descrizione -->
              <div class="description mb-4">
                  <p><?php echo esc_html($descrizione); ?></p>
              </div>

              <!-- Citazione -->
              <?php if (!empty($citazione)): ?>
                  <blockquote class="blockquote">
                      <p class="mb-4">"<?php echo esc_html($citazione); ?>"</p>
                  </blockquote>
              <?php endif; ?>

              <!-- Descrizione ripetuta -->
              <div class="description mb-4">
                  <p><?php echo esc_html($descrizione_due); ?></p>
              </div>

              <!-- Tabella per le icone di contatto con ID univoco (mantenuta) -->
              <table id="contact-table" class="content-table">
                  <tr>
                      <!-- Telefono -->
                      <?php if (!empty($telefono)): ?>
                      <td>
                          <div class="contact-item d-flex" style="flex-direction: column;">
                              <div class="contact-item d-flex align-items-center">
                                  <a href="tel:<?php echo esc_attr($telefono); ?>" class="contact-link caller" title="Chiama" style="text-decoration: none;">
                                      <i class="fa-solid fa-phone fa-lg" style="color: #333;"></i>
                                      <span class="contact-text ml-3">Chiama</span>
                                  </a>
                              </div>
                              <div class="contact-item d-flex align-items-center">
                                  <a href="tel:<?php echo esc_attr($telefono); ?>" class="caller-1 contact-link contact-text text-small-contact offuscato" style="text-decoration: none; ">(+39) <?php echo esc_attr($telefono); ?></a>
                              </div>
                          </div>
                      </td>
                      <?php endif; ?>

                      <!-- Indirizzo -->
                      <?php if (!empty($indirizzo)): ?>
                      <td>
                          <div class="contact-item d-flex" style="flex-direction: column;">
                              <div class="contact-item d-flex align-items-center">
                                  <a href="#map" class="contact-link" title="Visualizza su Maps" style="text-decoration: none;">
                                      <i class="fa fa-map-pin fa-lg" style="color: #333;"></i>
                                      <span class="contact-text ml-3">Posizione</span>
                                  </a>
                              </div>
                              <div class="contact-item d-flex align-items-center">
                                  <a href="#map" class="contact-link contact-text text-small-contact text-small-contact offuscato" style="text-decoration: none;"><?php echo $indirizzo; ?></a>
                              </div>
                          </div>
                      </td>
                      <?php endif; ?>
                  </tr>

                  <tr>
                      <!-- Email -->
                      <?php if (!empty($email)): ?>
                      <td>
                          <div class="contact-item d-flex" style="flex-direction: column;">
                              <div class="contact-item d-flex align-items-center">
                                  <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-link mailer" title="Invia Email" target="_blank" style="text-decoration: none;">
                                      <i class="far fa-envelope fa-lg" style="color: #333;"></i>
                                      <span class="contact-text ml-3">Email</span>
                                  </a>
                              </div>
                              <div class="contact-item d-flex align-items-center">
                                  <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-link contact-text text-small-contact mailer-1 offuscato" target="_blank" style="text-decoration: none;"><?php echo esc_attr($email); ?></a>
                              </div>
                          </div>
                      </td>
                      <?php endif; ?>

                      <!-- WhatsApp -->
                      <?php if (!empty($whatsapp)): ?>
                      <td>
                          <div class="contact-item d-flex" style="flex-direction: column;">
                              <div class="contact-item d-flex align-items-center">
                                <a href="https://wa.me/39<?php
                                $whatsapp = preg_replace('/\s+/', '', $whatsapp);
                                echo esc_attr($whatsapp);
                                ?>" target="_blank" title="WhatsApp" class="contact-link contact-text whatsapp text-small-contact" style="text-decoration: none;">
                                      <i class="fa-brands fa-whatsapp fa-lg" style="color: #333;"></i>
                                      <span class="contact-text ml-3">WhatsApp</span>
                                  </a>
                              </div>
                              <div class="contact-item d-flex align-items-center">
                                  <a href="https://wa.me/39<?php
                                  $whatsapp = preg_replace('/\s+/', '', $whatsapp);
                                  echo esc_attr($whatsapp);
                                  ?>" target="_blank" title="WhatsApp" class="contact-link contact-text whatsapp-1 text-small-contact" style="text-decoration: none;">Scrivi al borgo</a>
                              </div>
                          </div>
                      </td>
                      <?php endif; ?>
                  </tr>
              </table>
          </div>
      </div>


    <!-- Galleria con stile masonry -->
    <div class="gallery">
        <?php foreach ($gallery as $image): ?>
            <a href="<?php echo esc_url('https://www.lovenozze.it/fornitori/admin/process/uploads/' . trim($image)); ?>" data-lightbox="borgo-gallery" data-title="<?php echo esc_attr($ragione_sociale); ?>">
                <img src="<?php echo esc_url('https://www.lovenozze.it/fornitori/admin/process/uploads/' . trim($image)); ?>" alt="Gallery Image">
            </a>
        <?php endforeach; ?>
    </div>



    <!-- per mappa -->
    <?php if (!empty($borgo['latitudine']) && !empty($borgo['longitudine'])): ?>
        <div class="map-container mt-5">
            <div id="map" class="map-overlay">
                <!-- Bottone per visualizzare la mappa -->
                <button id="map-toggle" class="btn btn-secondary">Clicca per visualizzare la mappa</button>
                <div id="map-frame" style="display:none;">
                    <iframe
                        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDyi2qjyYB4_WUBAW-2KXVgPL8zhRvAFOI&q=<?php echo $borgo['latitudine']; ?>,<?php echo $borgo['longitudine']; ?>"
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    <?php endif; ?>

  </div>


<!-- Footer -->
<?php get_footer(); ?>

<!-- Script Bootstrap e FontAwesome -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Funzione generica per inviare una richiesta AJAX
    function inviaStatistiche(id_borgo, azione) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "admin/invia_statistiche.php", true); // Cambia con il percorso corretto del tuo file PHP
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("id_borgo=" + id_borgo + "&azione=" + azione);
    }

    // Visualizzazione di pagina
    document.addEventListener('DOMContentLoaded', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'visualizzazioni_pagina');


    // Clic sul telefono
    document.querySelector('.caller').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_telefono');
    });
    document.querySelector('.caller-1').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_telefono');
    });

    // Clic su email
    document.querySelector('.mailer').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_email');
    });
    document.querySelector('.mailer-1').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_email');
    });

    // Clic su WhatsApp
    document.querySelector('.whatsapp').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_whatsapp');
    });
    document.querySelector('.whatsapp-1').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'click_whatsapp');
    });

    // Visualizzazione mappa con bottone "Visualizza mappa"
    document.querySelector('#map-toggle').addEventListener('click', function() {
        var id_borgo = <?php echo $id_borgo; ?>;
        inviaStatistiche(id_borgo, 'visualizzazioni_mappa');

        // Mostra la mappa
        document.querySelector('#map-frame').style.display = 'block';
        document.querySelector('#map-toggle').style.display = 'none';

    });

        });
</script>

</body>
</html>

<?php
$conn->close();
?>
