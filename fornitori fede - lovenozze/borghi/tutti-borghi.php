<?php
// Includi WordPress
require_once('../wp-load.php');

// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controllo della connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupero categorie per la tendina
$sql_categories = "SELECT id, titolo FROM borghi_categorie";
$result_categories = $conn->query($sql_categories);
if (!$result_categories) {
    die("Errore nella query per le categorie: " . $conn->error);
}
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

// Query per ottenere tutti i borghi
$sql_borghi = "SELECT ragione_sociale, slug, descrizione, img_copertina, tag, id, latitudine, longitudine, categoria_id, regione FROM borghi_scheda";
$result_borghi = $conn->query($sql_borghi);
if (!$result_borghi) {
    die("Errore nella query per i borghi: " . $conn->error);
}

$places = "";
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutti i borghi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome per l'icona dei filtri -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

    <style>
    #qodef-page-inner{
      padding: 20px 10px 80px 10px!important;
      width: 90%;
    }
        #qodef-page-wrapper .qodef-page-title {
            display: none!important;
        }
        .content-page-borghi p, .content-page-borghi h1, .content-page-borghi h2, .content-page-borghi h3, .content-page-borghi h4, .content-page-borghi div, .content-page-borghi span {
            font-family: "Cormorant Garamond";
        }
        .content-page-borghi h1 {
            font-size: 60px;
        }
        .content-page-borghi p {
            font-size: 22px;
        }
        .category-card {
            margin-bottom: 30px;
        }
        .category-card h2 {
            font-size: 24px;
            font-weight: bold;
        }
        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .category-card .card-img-top {
            width: 100%;
            aspect-ratio: 9 / 14;
            overflow: hidden;
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

    </style>
    <link href="style/style.css" rel="stylesheet">
</head>

<body>
<?php get_header(); ?>

<div class="content-page-borghi">
    <h1 class="text-center mb-3 pb-3 borghi-title">Tutti i borghi</h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5">La tua dose di ispirazione, bellezza e romanticismo. Il mondo del matrimonio e del destination wedding in Italia, declinato sotto la lente della bellezza fine art e senza tempo.</p>

    <!-- Bottone per aprire i filtri -->
  <div class="text-right mb-5">
      <button id="toggle-filters" class="btn btn-outline-secondary">
          <i class="fas fa-filter"></i> Filtri
      </button>
  </div>

  <!-- Filtri (inizialmente nascosti su mobile) -->
  <div id="filter-container" class="row mb-5 d-none d-md-flex">
      <div class="col-12 col-md-4 mb-2">
          <input type="text" id="search" class="form-control" placeholder="Cerca...">
      </div>
      <div class="col-6 col-md-4 mb-2">
          <select id="category-filter" class="form-control">
              <option value="">Seleziona Categoria</option>
              <?php foreach ($categories as $category): ?>
                  <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['titolo']) ?></option>
              <?php endforeach; ?>
          </select>
      </div>
      <div class="col-6 col-md-4 mb-2">
          <select id="region-filter" class="form-control">
              <option value="">Seleziona Regione</option>
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
  </div>

  <!-- Script per mostrare/nascondere i filtri -->
  <script>
      document.getElementById('toggle-filters').addEventListener('click', function() {
          document.getElementById('filter-container').classList.toggle('d-none');
      });
  </script>

    <!-- Griglia borghi -->
    <div class="row mt-15">
        <?php if ($result_borghi->num_rows > 0): ?>
            <?php while ($borgo = $result_borghi->fetch_assoc()): ?>
              <div class="col-md-3 borgo-card" data-category="<?= htmlspecialchars($borgo['categoria_id']) ?>" data-region="<?= htmlspecialchars($borgo['regione']) ?>">
                  <div class="card category-card">
                      <a href="<?php echo "scheda.php?id=" . urlencode($borgo['id']) . "&slug=" . urlencode($borgo['slug']); ?>">
                          <img src="<?php echo "/fornitori/admin/process/uploads/". $borgo['img_copertina']; ?>" class="card-img-top" alt="<?php echo esc_attr($borgo['ragione_sociale']); ?>">
                          <div class="card-body">
                              <h2 class="card-title"><?php echo esc_html($borgo['ragione_sociale']); ?></h2>
                              <p class="card-text"><?php echo esc_html($borgo['descrizione']); ?></p>
                          </div>
                      </a>
                  </div>
              </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">Nessun borgo disponibile.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<?php get_footer(); ?>

<!-- Script Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const regionFilter = document.getElementById('region-filter');
    const searchInput = document.getElementById('search');

    function filterborghi() {
        const selectedCategory = categoryFilter.value;
        const selectedRegion = regionFilter.value;
        const searchTerm = searchInput.value.toLowerCase();

        document.querySelectorAll('.borgo-card').forEach(card => {
            const categoryMatches = selectedCategory ? card.getAttribute('data-category') === selectedCategory : true;
            const regionMatches = selectedRegion ? card.getAttribute('data-region') === selectedRegion : true;
            const searchMatches = card.innerText.toLowerCase().includes(searchTerm);

            if (categoryMatches && regionMatches && searchMatches) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Aggiungi listener per i filtri
    categoryFilter.addEventListener('change', filterborghi);
    regionFilter.addEventListener('change', filterborghi);
    searchInput.addEventListener('keyup', filterborghi);
});

</script>

</body>
</html>

<?php
$conn->close();
?>
