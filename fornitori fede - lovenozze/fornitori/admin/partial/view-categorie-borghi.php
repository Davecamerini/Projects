<?php
// Includi WordPress
require_once('../../wp-load.php'); // Modifica il percorso a seconda della tua configurazione

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Query per ottenere tutte le categorie di primo livello (genitore = 0)
$query = "SELECT id, titolo, descrizione, data_inserimento, genitore, immagine FROM borghi_categorie WHERE genitore = 0";
$categories = $conn->query($query);

// Funzione per ottenere le sottocategorie in base all'ID del genitore
function get_subcategories($parent_id, $conn) {
    $sub_query = "SELECT id, titolo, descrizione, data_inserimento, immagine FROM borghi_categorie WHERE genitore = $parent_id";
    return $conn->query($sub_query);
}
?>

<div class="content-card">
  <div style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; justify-content: space-between;">
    <h2 class="mt-2 mb-4">Gestione Categorie</h2>
    <a href='insert-categoria-borghi.php' class='btn btn-sm btn-success'>Nuova Categoria</a>
  </div>

  <table class="table table-striped table-bordered">
    <thead class="thead-dark">
      <tr>
        <th>ID</th>
        <th>Titolo</th>
        <th>Descrizione</th>
        <th>Immagine</th>
        <th>Data Inserimento</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($categories->num_rows > 0) {
        while ($category = $categories->fetch_assoc()) {
          echo "<tr class='category-row'>";
          echo "<td>" . $category['id'] . "</td>";
          echo "<td><a href='#' class='toggle-subcategories' data-id='" . $category['id'] . "'>" . $category['titolo'] . "</a></td>";
          echo "<td>" . $category['descrizione'] . "</td>";
          echo "<td><img src='https://www.lovenozze.it/fornitori/admin/process/uploads/" . $category['immagine'] . "' alt='Immagine Categoria' style='width:100px; height:auto;'></td>";
          echo "<td>" . $category['data_inserimento'] . "</td>";
          echo "<td>
                  <a href='modifica_categoria_borghi.php?id=" . $category['id'] . "' class='btn btn-sm btn-dark'>Modifica</a>
                  <a href='elimina_categoria_borghi.php?id=" . $category['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questa categoria?\");'>Elimina</a>
                </td>";
          echo "</tr>";

          // Sottocategorie
          $subcategories = get_subcategories($category['id'], $conn);
          if ($subcategories->num_rows > 0) {
            while ($subcategory = $subcategories->fetch_assoc()) {
              echo "<tr class='subcategory-row subcat-" . $category['id'] . "' style='display:none;'>";
              echo "<td>" . $subcategory['id'] . "</td>";
              echo "<td style='padding-left: 40px;'>↳ " . $subcategory['titolo'] . "</td>";
              echo "<td>" . $subcategory['descrizione'] . "</td>";
              echo "<td><img src='https://www.lovenozze.it/fornitori/admin/process/uploads/" . $subcategory['immagine'] . "' alt='Immagine Sottocategoria' style='width:100px; height:auto;'></td>";
              echo "<td>" . $subcategory['data_inserimento'] . "</td>";
              echo "<td>
                      <a href='modifica_categoria_borghi.php?id=" . $subcategory['id'] . "' class='btn btn-sm btn-dark'>Modifica</a>
                      <a href='elimina_categoria_borghi.php?id=" . $subcategory['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questa sottocategoria?\");'>Elimina</a>
                    </td>";
              echo "</tr>";
            }
          }
        }
      } else {
        echo "<tr><td colspan='6'>Nessuna categoria trovata.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<?php $conn->close(); ?>

<script>
// Script per il toggle delle sottocategorie
document.querySelectorAll('.toggle-subcategories').forEach(function(element) {
  element.addEventListener('click', function(e) {
    e.preventDefault();
    var categoryId = this.getAttribute('data-id');
    var subcategoryRows = document.querySelectorAll('.subcat-' + categoryId);
    subcategoryRows.forEach(function(row) {
      if (row.style.display === 'none') {
        row.style.display = 'table-row';
      } else {
        row.style.display = 'none';
      }
    });
  });
});
</script>
