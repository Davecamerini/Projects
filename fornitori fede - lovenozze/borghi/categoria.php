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
$sql = "SELECT id, titolo, descrizione, immagine FROM borghi_categorie WHERE slug = ?";
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

// Query per i borghi collegati alla categoria
$sql_borghi = "SELECT ragione_sociale, descrizione, img_copertina, tag, id FROM borghi_scheda WHERE categoria_id = ?";
$stmt_borghi = $conn->prepare($sql_borghi);

if ($stmt_borghi === false) {
  get_header();
  echo "<style>
  div.qodef-page-title {
    display: none!important;
  }</style>";
  echo "<p class='center text-center'>Nono sono presenti borghi per la categoria scelta. Prova a navigare in un'altra categoria.</p>";
  echo "<a class='center btn-primary btn' style='text-decoration:underline;'>Torna Indietro</a>";
    get_footer();
    $conn->close();  // Chiusura della connessione
    exit;
}

$stmt_borghi->bind_param('i', $category['id']);
$stmt_borghi->execute();
$result_borghi = $stmt_borghi->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_title; ?> - borghi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
    #qodef-page-wrapper .qodef-page-title {
      display: none!important;
    }
    #qodef-page-inner {
      padding: 50px 0 150px;
      width: 90%;
    }
    .content-page-borghi p, .content-page-borghi h1, .content-page-borghi h2, .content-page-borghi h3, .content-page-borghi h4, .content-page-borghi div, .content-page-borghi span{
          font-family: "Cormorant Garamond";
    }
    .content-page-borghi h1{
      font-size: 60px;
    }
    .content-page-borghi p{
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
<div class="content-page-borghi">
    <h1 class="text-center mb-3"><?php echo $category_title; ?></h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5"><?php echo esc_html($category_description); ?></p>

    <!-- Griglia borghi -->
    <div class="row mt-5 pt-5">
        <?php if ($result_borghi->num_rows > 0): ?>
            <?php while ($borgo = $result_borghi->fetch_assoc()): ?>
            <div class="col-md-3">
                <div class="card category-card">
                  <a href="<?php echo "scheda.php?id=" . esc_html($borgo['id']) ."&id=" . esc_html($borgo['slug']); ?>">
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
            <p class="text-center">Nessun borgo disponibile per questa categoria.</p>
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
