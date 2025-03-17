<?php
require_once('includes/header.php');

// Get database connection
$conn = getDBConnection();

// Get slug from GET parameter and sanitize it
$slug_fornitore = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($slug_fornitore)) {
    showNotification('Fornitore non trovato.', 'error');
    require_once('includes/footer.php');
    exit;
}

// Try to get supplier from cache first
$cache_key = 'supplier_' . $slug_fornitore;
$fornitore = getCache($cache_key);

if ($fornitore === false) {
    // Query for supplier details
    $sql = "SELECT img_copertina, ragione_sociale, slug, latitudine, longitudine, tag, telefono, whatsapp, indirizzo, email, descrizione, descrizione_due, citazione, gallery
            FROM fornitori_scheda WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $slug_fornitore);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
        setCache($cache_key, $fornitore);
    } else {
        showNotification('Fornitore non trovato.', 'error');
        require_once('includes/footer.php');
        exit;
    }
}

// Set page title
$page_title = $fornitore['ragione_sociale'];

// Process data
$img_copertina = !empty($fornitore['img_copertina']) ? UPLOAD_URL . $fornitore['img_copertina'] : 'https://via.placeholder.com/400x600';
$tag = explode(',', $fornitore['tag']);
$gallery = explode(',', $fornitore['gallery']);
?>

<div class="content-page-fornitore">
    <a href="<?= SITE_URL ?>/fornitori/" class="back-link">
        Tutti i fornitori 
        <svg class="qodef-svg--button-arrow" style="width:10px; fill: rgba(139, 119, 128, 1);position: absolute;" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 11.9 11.9" xml:space="preserve">
            <path class="qodef-path" d="M.4 11.5 11.4.6M11.4 10V.5H1.9"></path>
            <path class="qodef-path" d="M.4 11.5 11.4.6M11.4 10V.5H1.9"></path>
        </svg>
    </a>

    <div class="fornitore-container d-flex flex-wrap">
        <!-- Colonna sinistra: Immagine copertina -->
        <div class="left-column" style="width: 40%;">
            <div class="container-img">
                <img data-src="<?= $img_copertina ?>" alt="<?= sanitizeInput($fornitore['ragione_sociale']) ?>" class="fixed-image lazy">
            </div>
        </div>

        <!-- Colonna destra: Informazioni fornitore -->
        <div class="right-column" style="width: 60%;">
            <h1><?= sanitizeInput($fornitore['ragione_sociale']) ?></h1>

            <!-- Tag -->
            <div class="tags mt-2 mb-3">
                <?php foreach ($tag as $single_tag): ?>
                    <span class="tag"><?= sanitizeInput(trim($single_tag)) ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Descrizione -->
            <div class="description mb-4">
                <p><?= sanitizeInput($fornitore['descrizione']) ?></p>
            </div>

            <!-- Citazione -->
            <?php if (!empty($fornitore['citazione'])): ?>
                <blockquote class="blockquote">
                    <p class="mb-4">"<?= sanitizeInput($fornitore['citazione']) ?>"</p>
                </blockquote>
            <?php endif; ?>

            <!-- Descrizione ripetuta -->
            <div class="description mb-4">
                <p><?= sanitizeInput($fornitore['descrizione_due']) ?></p>
            </div>

            <!-- Tabella per le icone di contatto -->
            <table id="contact-table" class="content-table">
                <tr>
                    <!-- Telefono -->
                    <?php if (!empty($fornitore['telefono'])): ?>
                    <td>
                        <div class="contact-item d-flex" style="flex-direction: column;">
                            <div class="contact-item d-flex align-items-center">
                                <a href="tel:<?= sanitizeInput($fornitore['telefono']) ?>" class="contact-link caller" title="Chiama" style="text-decoration: none;">
                                    <i class="fa-solid fa-phone fa-lg" style="color: #333;"></i>
                                    <span class="contact-text ml-3">Chiama</span>
                                </a>
                            </div>
                            <div class="contact-item d-flex align-items-center">
                                <a href="tel:<?= sanitizeInput($fornitore['telefono']) ?>" class="caller-1 contact-link contact-text text-small-contact offuscato" style="text-decoration: none;">
                                    (+39) <?= sanitizeInput($fornitore['telefono']) ?>
                                </a>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>

                    <!-- Indirizzo -->
                    <?php if (!empty($fornitore['indirizzo'])): ?>
                    <td>
                        <div class="contact-item d-flex" style="flex-direction: column;">
                            <div class="contact-item d-flex align-items-center">
                                <a href="#map" class="contact-link" title="Visualizza su Maps" style="text-decoration: none;">
                                    <i class="fa fa-map-pin fa-lg" style="color: #333;"></i>
                                    <span class="contact-text ml-3">Posizione</span>
                                </a>
                            </div>
                            <div class="contact-item d-flex align-items-center">
                                <a href="#map" class="contact-link contact-text text-small-contact text-small-contact offuscato" style="text-decoration: none;">
                                    <?= sanitizeInput($fornitore['indirizzo']) ?>
                                </a>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>

                <tr>
                    <!-- Email -->
                    <?php if (!empty($fornitore['email'])): ?>
                    <td>
                        <div class="contact-item d-flex" style="flex-direction: column;">
                            <div class="contact-item d-flex align-items-center">
                                <a href="mailto:<?= sanitizeInput($fornitore['email']) ?>" class="contact-link mailer" title="Invia Email" target="_blank" style="text-decoration: none;">
                                    <i class="far fa-envelope fa-lg" style="color: #333;"></i>
                                    <span class="contact-text ml-3">Email</span>
                                </a>
                            </div>
                            <div class="contact-item d-flex align-items-center">
                                <a href="mailto:<?= sanitizeInput($fornitore['email']) ?>" class="contact-link contact-text text-small-contact mailer-1 offuscato" target="_blank" style="text-decoration: none;">
                                    <?= sanitizeInput($fornitore['email']) ?>
                                </a>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>

                    <!-- WhatsApp -->
                    <?php if (!empty($fornitore['whatsapp'])): ?>
                    <td>
                        <div class="contact-item d-flex" style="flex-direction: column;">
                            <div class="contact-item d-flex align-items-center">
                                <a href="https://wa.me/39<?= preg_replace('/\s+/', '', $fornitore['whatsapp']) ?>" target="_blank" title="WhatsApp" class="contact-link contact-text whatsapp text-small-contact" style="text-decoration: none;">
                                    <i class="fa-brands fa-whatsapp fa-lg" style="color: #333;"></i>
                                    <span class="contact-text ml-3">WhatsApp</span>
                                </a>
                            </div>
                            <div class="contact-item d-flex align-items-center">
                                <a href="https://wa.me/39<?= preg_replace('/\s+/', '', $fornitore['whatsapp']) ?>" target="_blank" title="WhatsApp" class="contact-link contact-text whatsapp-1 text-small-contact" style="text-decoration: none;">
                                    Scrivi al fornitore
                                </a>
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
            <a href="<?= UPLOAD_URL . trim($image) ?>" data-lightbox="fornitore-gallery" data-title="<?= sanitizeInput($fornitore['ragione_sociale']) ?>">
                <img data-src="<?= UPLOAD_URL . trim($image) ?>" alt="Gallery Image" class="lazy">
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Mappa -->
    <?php if (!empty($fornitore['latitudine']) && !empty($fornitore['longitudine'])): ?>
        <div class="map-container mt-5">
            <div id="map" class="map-overlay">
                <button id="map-toggle" class="btn btn-secondary">Clicca per visualizzare la mappa</button>
                <div id="map-frame" style="display:none;">
                    <iframe
                        src="https://www.google.com/maps/embed/v1/place?key=<?= GOOGLE_MAPS_API_KEY ?>&q=<?= $fornitore['latitudine']; ?>,<?= $fornitore['longitudine']; ?>"
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$extra_js = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Map toggle functionality
    const mapToggle = document.getElementById('map-toggle');
    const mapFrame = document.getElementById('map-frame');
    
    if (mapToggle && mapFrame) {
        mapToggle.addEventListener('click', function() {
            mapFrame.style.display = mapFrame.style.display === 'none' ? 'block' : 'none';
            mapToggle.textContent = mapFrame.style.display === 'none' ? 'Clicca per visualizzare la mappa' : 'Nascondi mappa';
        });
    }

    // Contact information reveal
    document.querySelectorAll('.offuscato').forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.remove('offuscato');
        });
    });
});
</script>
EOT;

require_once('includes/footer.php');
?>
