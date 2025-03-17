<?php
$page_title = 'Tutti i Fornitori';
require_once('includes/header.php');

// Get database connection
$conn = getDBConnection();

// Try to get categories from cache first
$categories = getCache('categories');
if ($categories === false) {
    // Query for categories
    $sql_categories = "SELECT id, titolo FROM fornitori_categorie";
    $result_categories = $conn->query($sql_categories);
    if (!$result_categories) {
        die("Errore nella query per le categorie: " . $conn->error);
    }
    $categories = $result_categories->fetch_all(MYSQLI_ASSOC);
    setCache('categories', $categories);
}

// Try to get suppliers from cache first
$suppliers = getCache('suppliers');
if ($suppliers === false) {
    // Query for suppliers
    $sql_fornitori = "SELECT ragione_sociale, slug, descrizione, img_copertina, tag, id, latitudine, longitudine, categoria_id, regione FROM fornitori_scheda";
    $result_fornitori = $conn->query($sql_fornitori);
    if (!$result_fornitori) {
        die("Errore nella query per i fornitori: " . $conn->error);
    }
    $suppliers = $result_fornitori->fetch_all(MYSQLI_ASSOC);
    setCache('suppliers', $suppliers);
}
?>

<div class="content-page-fornitori">
    <h1 class="text-center mb-3 pb-3">Tutti i Fornitori</h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5">La tua dose di ispirazione, bellezza e romanticismo. Il mondo del matrimonio e del destination wedding in Italia, declinato sotto la lente della bellezza fine art e senza tempo.</p>

    <!-- Filtri -->
    <div class="row mb-5">
        <div class="col-md-4">
            <select id="category-filter" class="form-control">
                <option value="">Seleziona Categoria</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= sanitizeInput($category['id']) ?>"><?= sanitizeInput($category['titolo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" id="search" class="form-control" placeholder="Cerca...">
        </div>
        <div class="col-md-4">
            <select id="region-filter" class="form-control">
                <option value="">Seleziona Regione</option>
                <?php
                $regioni = [
                    'Abruzzo', 'Basilicata', 'Calabria', 'Campania', 'Emilia-Romagna',
                    'Friuli Venezia Giulia', 'Lazio', 'Liguria', 'Lombardia', 'Marche',
                    'Molise', 'Piemonte', 'Puglia', 'Sardegna', 'Sicilia', 'Toscana',
                    'Trentino-Alto Adige', 'Umbria', 'Valle d\'Aosta', 'Veneto'
                ];
                foreach ($regioni as $regione):
                ?>
                    <option value="<?= sanitizeInput($regione) ?>"><?= sanitizeInput($regione) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Griglia fornitori -->
    <div class="row mt-5">
        <?php if (!empty($suppliers)): ?>
            <?php foreach ($suppliers as $fornitore): ?>
              <div class="col-md-3 fornitore-card" 
                   data-category="<?= sanitizeInput($fornitore['categoria_id']) ?>" 
                   data-region="<?= sanitizeInput($fornitore['regione']) ?>">
                  <div class="card category-card">
                      <a href="scheda.php?slug=<?= urlencode($fornitore['slug']) ?>">
                          <img data-src="<?= UPLOAD_URL . sanitizeInput($fornitore['img_copertina']) ?>" 
                               class="card-img-top lazy" 
                               alt="<?= sanitizeInput($fornitore['ragione_sociale']) ?>">
                          <div class="card-body">
                              <h2 class="card-title"><?= sanitizeInput($fornitore['ragione_sociale']) ?></h2>
                              <p class="card-text"><?= sanitizeInput($fornitore['descrizione']) ?></p>
                          </div>
                      </a>
                  </div>
              </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Nessun fornitore disponibile.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$extra_js = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('category-filter');
    const regionFilter = document.getElementById('region-filter');
    const searchInput = document.getElementById('search');

    function filterFornitori() {
        const selectedCategory = categoryFilter.value;
        const selectedRegion = regionFilter.value;
        const searchTerm = searchInput.value.toLowerCase();

        document.querySelectorAll('.fornitore-card').forEach(card => {
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

    // Add debounce to search input
    let searchTimeout;
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterFornitori, 300);
    });

    // Add listeners for filters
    categoryFilter.addEventListener('change', filterFornitori);
    regionFilter.addEventListener('change', filterFornitori);
});
</script>
EOT;

require_once('includes/footer.php');
?>
