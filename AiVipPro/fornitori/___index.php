<?php
// Includi WordPress
require_once('../wp-load.php');
get_header();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornitori - Lovenozze</title>
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

    /* Stili per le immagini con rapporto 8:10 */
    .category-section {
      position: relative;
      margin-bottom: 30px;
      overflow: hidden;
    }

    .category-section img {
      width: 100%;
      height: auto;
      aspect-ratio: 8 / 10;
      object-fit: cover;
      transition: all 0.3s ease-in-out;
    }

    /* Overlay sfumatura nera */
    .category-section .overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 100%;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0));
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 20px;
      transition: background 0.3s ease-in-out;
      flex-direction: column;
      flex-wrap: nowrap;
    }

    /* Testo bianco */
    .category-section h2 {
      font-size: 40px;
      font-weight: bold;
      color: white;
      text-align: center;
      z-index: 10;
      display: block;
    }
    .category-section p {
      text-align: center;
      color: white;
      display: block;
    }

    /* Effetto hover per scurire l'immagine */
    .category-section:hover img {
      filter: brightness(0.8);
    }

    .category-section:hover .overlay {
      background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0));
    }

    /* Layout a tre colonne */
    .row-custom {
      display: flex;
      justify-content: space-between;
      max-width: 1500px;
      margin: auto;
    }

    .col-custom {
      flex-basis: 32%; /* Tre colonne affiancate con margine tra di loro */
    }

    </style>
</head>

<body>

<!-- Sezione contenuti -->
<div class="content-page-fornitori">
    <h1 class="text-center mb-3">I migliori Fornitori per Matrimoni</h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5">La tua dose di ispirazione, bellezza e romanticismo. Il mondo del matrimonio e del destination wedding in Italia, declinato sotto la lente della bellezza fine art e senza tempo.</p>

    <!-- Sezione con le tre immagini affiancate -->
    <div class="row-custom">
        <!-- Categorie -->
        <div class="col-custom">
            <div class="category-section">
                <a href="categorie.php">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/matrimonio.jpg" alt="Categorie">
                    <div class="overlay">
                        <h2>Categorie</h2>
                        <p>Stai cercando qualcosa in particolare? Filtra direttamente la ricerca per una delle categorie di fornitori. </p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Luoghi -->
        <div class="col-custom">
            <div class="category-section">
                <a href="luoghi.php">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/viaggi-di-nozze.jpg" alt="Luoghi">
                    <div class="overlay">
                        <h2>Luoghi</h2>
                        <p>Hai già in mente la zona? Possiamo proporti solamente i migliori fornitori della tua regione o provincia. </p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tutti i fornitori -->
        <div class="col-custom">
            <div class="category-section">
                <a href="tutti-fornitori.php">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/fornitori-love.jpg" alt="Tutti i Fornitori">
                    <div class="overlay">
                        <h2>Tutti i Fornitori</h2>
                        <p>Vuoi lasciarti ispirare da ogni professionista che collabora con noi? Esplora tutte le proposte dei fornitori.  </p>
                    </div>
                </a>
            </div>
        </div>
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
