<?php
// Includi WordPress
require_once('../wp-load.php');
get_header();

// Connessione manuale al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Query per ottenere i borghi
$sql = "SELECT id, slug, ragione_sociale, descrizione, latitudine, longitudine, indirizzo FROM borghi_scheda";
$result = $conn->query($sql);

if ($result === false) {
    die("Errore nella query: " . $conn->error);
}

// Crea un array per i borghi
$borghi = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borghi[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luoghi - borghi</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="js/regioni.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([41.9028, 12.4964], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
            }).addTo(map);

            var borghi = <?php echo json_encode($borghi); ?>;
            addborghiMarkers(borghi);

            function addborghiMarkers(borghi) {
                borghi.forEach(function(borgo) {
                    if (borgo.latitudine && borgo.longitudine) {
                        // Aggiungi il marker con un attributo per il borgo
                        var marker = L.marker([borgo.latitudine, borgo.longitudine])
                            .addTo(map)
                            .bindPopup('<strong>' + borgo.ragione_sociale + '</strong><br>' + borgo.indirizzo);

                        // Associa il borgo al marker
                        marker.borgo = borgo;
                    }
                });
            }

            function filterborghi(region) {
                document.querySelectorAll('.borgo-card').forEach(function(card) {
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

            // Funzione per caricare il GeoJSON
            fetch('data/limits_IT_regions.geojson')
                .then(response => response.json())
                .then(data => {
                    // Aggiungi il GeoJSON alla mappa
                    L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            // Aggiungi un evento click sulla regione
                            layer.on('click', function() {
                                // Filtra i borghi in base alla regione
                                filterborghi(layer.getBounds());
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
        .borghi-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            padding-top: 0;
        }
        #map {
            width: 100%;
            height: 700px;
        }
        .borgo-card {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .borgo-card h3 {
            margin: 0;
            font-size: 25px;
            font-weight: bold;
        }
        .borgo-card p {
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
</head>
<body>
  <div class="bodyng">

<div class="map-container">
    <div id="map"></div>
</div>

<div class="borghi-container" id="borghiList">
    <h1 class="text-center mb-3" style="font-size: 40px;">Lista borghi</h1>
    <div id="borghiContent">
        <?php foreach ($borghi as $borgo): ?>
            <div class="borgo-card" data-lat="<?php echo esc_html($borgo['latitudine']); ?>" data-lng="<?php echo esc_html($borgo['longitudine']); ?>">
                <h3><?php echo esc_html($borgo['ragione_sociale']); ?></h3>
                <p><strong>Indirizzo:</strong> <?php echo esc_html($borgo['indirizzo']); ?></p>
                <a style="font-size: 16px;background: #e4e4e4;padding: 3px 12px;" href="https://www.lovenozze.it/borghi/scheda.php?id=<?php echo esc_html($borgo['id']); ?>&slug=<?php echo esc_html($borgo['slug']); ?>" target="_blank"><strong>Scopri di più</strong></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>
<!-- Footer -->
<?php
get_footer();
?>
</body>
</html>

<?php
$conn->close();
?>
