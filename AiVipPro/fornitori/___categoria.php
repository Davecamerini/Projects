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

// Recupera lo slug dalla query GET
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    echo "<p class='text-center'>Categoria non trovata.</p>";
    get_footer();
    $conn->close();  // Chiusura della connessione
    exit;
}

// Query per ottenere i dettagli della categoria
$sql = "SELECT id, titolo, descrizione, immagine FROM fornitori_categorie WHERE slug = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo "<p class='text-center'>Errore nella preparazione della query per la categoria.</p>";
    get_footer();
    $conn->close();  // Chiusura della connessione
    exit;
}

$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

// Controlla se la categoria esiste
if ($result->num_rows > 0) {
    $category = $result->fetch_assoc();
    $category_title = $category['titolo'];
    $category_description = $category['descrizione'];
    $category_image = !empty($category['immagine']) ? 'https://www.lovenozze.it/fornitori/admin/process/uploads/' . $category['immagine'] : 'https://via.placeholder.com/400x600';
} else {
    get_header();
    echo "<style>
    div.qodef-page-title {
      display: none!important;
    } </style>";
    echo "<p class='text-center'>Categoria non trovata.</p>";
    get_footer();
    $conn->close();  // Chiusura della connessione
    exit;
}

// Query per i fornitori collegati alla categoria
$sql_fornitori = "SELECT ragione_sociale, descrizione, img_copertina, tag, id FROM fornitori_scheda WHERE categoria_id = ?";
$stmt_fornitori = $conn->prepare($sql_fornitori);

if ($stmt_fornitori === false) {
  get_header();
  echo "<style>
  div.qodef-page-title {
    display: none!important;
  }</style>";
  echo "<p class='center text-center'>Nono sono presenti fornitori per la categoria scelta. Prova a navigare in un'altra categoria.</p>";
  echo "<a class='center btn-primary btn' style='text-decoration:underline;'>Torna Indietro</a>";
    get_footer();
    $conn->close();  // Chiusura della connessione
    exit;
}

$stmt_fornitori->bind_param('i', $category['id']);
$stmt_fornitori->execute();
$result_fornitori = $stmt_fornitori->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_title; ?> - Fornitori</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
    #qodef-page-wrapper .qodef-page-title {
      display: none!important;
    }
    #qodef-page-inner {
      padding: 50px 0 150px;
      width: 90%;
    }
    .content-page-fornitori p, .content-page-fornitori h1, .content-page-fornitori h2, .content-page-fornitori h3, .content-page-fornitori h4, .content-page-fornitori div, .content-page-fornitori span{
          font-family: "Cormorant Garamond";
    }
    .content-page-fornitori h1{
      font-size: 60px;
    }
    .content-page-fornitori p{
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
    /* Stile per il contenitore dell'immagine, mantenendo il rapporto 9:16 */
    .category-card .card-img-top {
        width: 100%;
        aspect-ratio: 9 / 14; /* Rapporto di 9:16 */
        overflow: hidden;
    }

    /* Stile per l'immagine */
    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Ritaglia l'immagine e riempie il contenitore */
        object-position: center; /* Centra l'immagine all'interno del contenitore */
    }


    </style>
</head>

<body>
<?php get_header(); ?>
<link href="style/style.css" rel="stylesheet">
<!-- Sezione contenuti -->
<div class="content-page-fornitori">
    <h1 class="text-center mb-3"><?php echo $category_title; ?></h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5"><?php echo esc_html($category_description); ?></p>

    <!-- Griglia fornitori -->
    <div class="row mt-5 pt-5">
        <?php if ($result_fornitori->num_rows > 0): ?>
            <?php while ($fornitore = $result_fornitori->fetch_assoc()): ?>
            <div class="col-md-3">
                <div class="card category-card">
                  <a href="<?php echo "scheda.php?id=" . esc_html($fornitore['id']); ?>">
                    <img src="<?php echo "/fornitori/admin/process/uploads/". $fornitore['img_copertina']; ?>" class="card-img-top" alt="<?php echo esc_attr($fornitore['ragione_sociale']); ?>">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo esc_html($fornitore['ragione_sociale']); ?></h2>
                        <p class="card-text"><?php echo esc_html($fornitore['descrizione']); ?></p>
                    </div>
                  </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">Nessun fornitore disponibile per questa categoria.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<?php get_footer(); ?>

<!-- Script Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
