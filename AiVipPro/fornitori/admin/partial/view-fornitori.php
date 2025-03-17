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

// Query per ottenere tutti i fornitori
$query = "SELECT id, ragione_sociale, descrizione, citazione, img_copertina, gallery, tag, indirizzo, email, telefono, votazione_complessiva FROM fornitori_scheda";
$result = $conn->query($query);
?>

<div class="content-card">
  <div style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; justify-content: space-between;">
    <h2 class="mt-2 mb-4">Gestione Fornitori</h2>
    <a href='insert-fornitori.php' class='btn btn-sm btn-success'>Nuovo Fornitore</a>
  </div>
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Ragione Sociale</th>
                <th>Copertina</th>
                <th>Tag</th>
                <th>Indirizzo</th>
                <th>Email</th>
                <th>Telefono</th>
                <th>Votazione</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Mostra ogni fornitore
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['ragione_sociale'] . "</td>";
                    echo "<td><img src='https://www.lovenozze.it/fornitori/admin/process/uploads/" . $row['img_copertina'] . "' alt='Copertina' style='width:100px; height:auto;'></td>";
                    echo "<td>" . $row['tag'] . "</td>";
                    echo "<td>" . $row['indirizzo'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . $row['telefono'] . "</td>";
                    echo "<td>" . $row['votazione_complessiva'] . "</td>";
                    echo "<td>
                        <a href='modifica_fornitore.php?id=" . $row['id'] . "' class='btn btn-sm btn-dark'>Modifica</a>
                        <a href='elimina_fornitore.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Sei sicuro di voler eliminare questo fornitore?\");'>Elimina</a>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12'>Nessun fornitore trovato.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
?>
