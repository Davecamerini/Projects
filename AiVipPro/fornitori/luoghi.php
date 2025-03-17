<?php
$page_title = 'Luoghi - Fornitori';
require_once('includes/header.php');

// Get database connection
$conn = getDBConnection();

// Try to get suppliers from cache first
$fornitori = getCache('map_suppliers');
if ($fornitori === false) {
    // Query for suppliers
    $sql = "SELECT id, slug, ragione_sociale, descrizione, latitudine, longitudine, indirizzo FROM fornitori_scheda";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Errore nella query: " . $conn->error);
    }

    $fornitori = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fornitori[] = $row;
        }
    }
    setCache('map_suppliers', $fornitori);
}

// Add map-specific CSS
$extra_css = <<<EOT
<style>
    .qodef-page-title {
        display: none;
    }
    #qodef-page-inner {
        padding: 50px 0 150px;
        width: 90%;
    }
    .bodyng {
        display: flex;
        height: 100vh;
        margin: 0;
        font-family: "Cormorant Garamond", serif;
    }
    .map-container {
        flex: 1;
        height: 100%;
        padding: 20px 0;
    }
    .fornitori-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        padding-top: 0;
    }
    #map {
        width: 100%;
        height: 700px;
    }
    .fornitore-card {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .fornitore-card h3 {
        margin: 0;
        font-size: 25px;
        font-weight: bold;
    }
    .fornitore-card p {
        margin: 0!important;
        font-size: 18px;
        color: #616161;
        letter-spacing: .2px;
    }
    @media only screen and (max-width: 768px) {
        .bodyng {
            display: contents !important;
        }
        #map {
            height: 300px !important;
        }
        .map-container {
            padding-bottom: 0 !important;
        }
    }
</style>
EOT;
?>

<div class="bodyng">
    <div class="map-container">
        <div id="map"></div>
    </div>

    <div class="fornitori-container" id="fornitoriList">
        <h1 class="text-center mb-3" style="font-size: 40px;">Lista Fornitori</h1>
        <div id="fornitoriContent">
            <?php foreach ($fornitori as $fornitore): ?>
                <div class="fornitore-card" 
                     data-lat="<?= sanitizeInput($fornitore['latitudine']) ?>" 
                     data-lng="<?= sanitizeInput($fornitore['longitudine']) ?>">
                    <h3><?= sanitizeInput($fornitore['ragione_sociale']) ?></h3>
                    <p><strong>Indirizzo:</strong> <?= sanitizeInput($fornitore['indirizzo']) ?></p>
                    <a style="font-size: 16px;background: #e4e4e4;padding: 3px 12px;" 
                       href="<?= SITE_URL ?>/fornitori/scheda.php?slug=<?= urlencode($fornitore['slug']) ?>" 
                       target="_blank">
                        <strong>Scopri di più</strong>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$extra_js = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([41.9028, 12.4964], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
    }).addTo(map);

    var fornitori = <?= json_encode($fornitori) ?>;
    addFornitoriMarkers(fornitori);

    function addFornitoriMarkers(fornitori) {
        fornitori.forEach(function(fornitore) {
            if (fornitore.latitudine && fornitore.longitudine) {
                var marker = L.marker([fornitore.latitudine, fornitore.longitudine])
                    .addTo(map)
                    .bindPopup('<strong>' + fornitore.ragione_sociale + '</strong><br>' + fornitore.indirizzo);

                marker.fornitore = fornitore;
            }
        });
    }

    function filterFornitori(region) {
        document.querySelectorAll('.fornitore-card').forEach(function(card) {
            var lat = card.getAttribute('data-lat');
            var lng = card.getAttribute('data-lng');
            if (lat && lng) {
                var point = L.latLng(lat, lng);
                if (region.contains(point)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }

    // Load GeoJSON
    fetch('data/limits_IT_regions.geojson')
        .then(response => response.json())
        .then(data => {
            L.geoJSON(data, {
                onEachFeature: function (feature, layer) {
                    layer.on('click', function() {
                        filterFornitori(layer.getBounds());
                    });
                },
                style: function (feature) {
                    return {
                        color: 'grey',
                        weight: 2,
                        fillOpacity: 0.5
                    };
                }
            }).addTo(map);
        })
        .catch(err => console.error(err));
});
</script>
EOT;

require_once('includes/footer.php');
?>
