<?php
// Includi WordPress
require_once('../wp-load.php');
get_header();

// Connessione manuale al database (in base alla tua configurazione)
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Query per ottenere le categorie dalla tabella fornitori_categorie
$sql = "SELECT id, titolo, descrizione, immagine, slug FROM fornitori_categorie";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorie di fornitori - Lovenozze</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="style/style.css" rel="stylesheet">
    <style>
    .qodef-page-title {
      display: none;
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
    /* Regola per tutte le immagini */
    .content-page-fornitori img {
      width: 100%;
      height: auto;
      aspect-ratio: 9 / 16;
      object-fit: cover;
      display: block;
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

    a{
      color: black;
    }
    a:hover{
      color:rgb(139,119,128);
      text-decoration: none;
    }
    </style>
</head>

<body>

<!-- Sezione contenuti -->
<div class="content-page-fornitori">
    <h1 class="text-center mb-3">Categorie Fornitori per Matrimoni</h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5">La tua dose di ispirazione, bellezza e romanticismo. Il mondo del matrimonio e del destination wedding in Italia, declinato sotto la lente della bellezza fine art e senza tempo. Lasciati ispirare dalla nostra collezione di matrimoni veri e da idee e consigli da parte di real brides, wedding planners e professionisti esperti delle nozze.</p>

    <!-- Griglia Responsive con Card -->
    <div class="row">

        <?php
        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                // Recupera i dati dalla query
                $category_title = $row['titolo'];
                $category_description = $row['descrizione'];
                $category_image = !empty($row['immagine']) ? 'https://www.lovenozze.it/fornitori/admin/process/uploads/' . $row['immagine'] : 'https://via.placeholder.com/400x600';
                $category_link = $row['slug']; // Link basato sullo slug
        ?>

        <!-- Card Categoria -->
        <div class="col-md-3">
            <div class="card category-card">
              <a href="categoria.php?slug=<?php echo $category_link; ?>" style="">
                <img src="<?php echo esc_url($category_image); ?>" class="card-img-top" alt="<?php echo esc_attr($category_title); ?>">
              </a>
                <div class="card-body">
                  <a href="categoria.php?slug=<?php echo $category_link; ?>" style="">
                    <h2 class="card-title"><?php echo esc_html($category_title); ?></h2>
                    <p class="card-text" style="font-size: 20px;line-height: normal;"><?php echo esc_html($category_description); ?></p>
                  </a>
                </div>
            </div>
        </div>

        <?php
            endwhile;
        else:
        ?>
        <p class="text-center">Nessuna categoria disponibile.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<?php
get_footer();
?>

<!-- Script Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
