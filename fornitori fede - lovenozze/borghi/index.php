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
    <title>Borghi - Lovenozze</title>
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
    .content-page-borghi p, .content-page-borghi h1, .content-page-borghi h2, .content-page-borghi h3, .content-page-borghi h4, .content-page-borghi div, .content-page-borghi span{
          font-family: "Cormorant Garamond";
    }
    .content-page-borghi h1{
      font-size: 60px;
    }
    .content-page-borghi p{
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
      max-width: 1100px;
      margin: auto;
    }

    .col-custom {
      flex-basis: 49%; /* Tre colonne affiancate con margine tra di loro */
    }

    </style>
</head>

<body>
<!-- Sezione contenuti -->
<div class="content-page-borghi">
    <h1 class="text-center mb-3 borghi-title"><?php echo stripslashes("I Borghi Più Belli d’Italia:"); ?></br> Destination Wedding Nei Borghi </h1>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5"><?php echo stripslashes("L’associazione dei Borghi più belli d’Italia in collaborazione con LoveNozze seleziona e racconta i migliori Borghi d'Italia per matrimoni da sogno ed esperienze di soggiorno uniche.<br> Per vivere emozioni straordinarie e collezionare ricordi indelebili."); ?></p>

    <!-- Sezione con le tre immagini affiancate -->
    <div id="row-selection-index" class="row-custom">
        <!-- Categorie -->
    <!--    <div class="col-custom">
            <div class="category-section">
                <a href="categorie.php">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/matrimonio.jpg" alt="Categorie">
                    <div class="overlay">
                        <h2>Categorie</h2>
                        <p>Stai cercando qualcosa in particolare? Filtra direttamente la ricerca per una delle categorie di borghi. </p>
                    </div>
                </a>
            </div>
        </div>-->

        <!-- Luoghi -->
        <div class="col-custom">
            <div class="category-section">
                <a href="luoghi">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/medieval.jpg" alt="Luoghi" style="filter:grayscale(0);">
                    <div class="overlay">
                        <h2>Destinazione Borghi</h2>
                        <p>Scopri l’Italia autentica, quella dei Borghi più belli e del romanticismo assoluto.  Clicca sulla mappa e scopri i migliori borghi d’Italia.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tutti i borghi -->
        <div class="col-custom">
            <div class="category-section">
                <a href="tutti-borghi">
                    <img src="https://www.lovenozze.it/fornitori/admin/process/uploads/borgo.jpg" alt="Tutti i borghi">
                    <div class="overlay">
                        <h2>Destination Wedding<br>nei Borghi più belli</h2>
                        <p>Organizza il tuo matrimonio da sogno in uno dei borghi più belli d'Italia. Ecco le migliori proposte scoperte e selezionate per te.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <p style="max-width:850px; margin:auto;" class="text-center mb-5">Vuoi organizzare il tuo matrimonio in uno dei Borghi più belli con il supporto delle migliori agenzie e Wedding Planner d’Italia?Hai una location o sei un fornitore di matrimonio e vuoi collaborare con LoveNozze? <a target="_blank" href="https://www.lovenozze.it/contatti/">Contattaci.</p>
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
