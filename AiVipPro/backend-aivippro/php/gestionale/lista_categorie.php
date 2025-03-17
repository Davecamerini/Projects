<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database


$conn = new mysqli($host, $username, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Esegui la query per ottenere tutte le categorie
$sql = "SELECT id, categoria, slug, copertina FROM categorie_corsi_online";
$result = $conn->query($sql);

// Chiudi la connessione al database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elenco Categorie</title>
    <style>

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th {
            background-color: #990011;
            color: #fff;
        }
        td img {
            max-width: 100px;
            max-height: 100px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            min-height: 68px!important;
            vertical-align: middle!important;
            align-items: center!important;

        }
        .action-buttons a {
            text-decoration: none;
            color: #fff;
            padding: 6px 12px;
            border: 1px solid #e50914;
            border-radius: 4px;
            background-color: #990011;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .action-buttons a:hover {
            background-color: #ff3d3d;
        }
        .info-icon{
          padding: 15px;
        }
        .action-menu{
          background-color: #991B0F;
          padding: 0px 0;
          text-align: center;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-direction: column;
        }
    </style>
</head>
<body>
  <?php include "../../partial/header.php"; ?>

    <div class="action-menu">
      <h1 class="h1-gestionale">Elenco Categorie</h1>
      <div class="menu-action-categories">
      <div class="info-icon"><a href="form_inserimento_categoria.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg> Aggiungi categoria</a></div>
                  <div class="info-icon"><a href="/area-corsi-online/index.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna in home</a></div>
      </div>
    </div>
    <div  class="container-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Categoria</th>
                <th>Slug</th>
                <th>Copertina</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['categoria']}</td>";
                    echo "<td>{$row['slug']}</td>";
                    echo "<td><img src='{$row['copertina']}' alt='Copertina'></td>";
                    echo "<td class='action-buttons'>";
                    echo "<a href='modifica_categoria.php?id={$row['id']}'>Modifica</a>";
                    echo "<a href='elimina_categoria.php?id={$row['id']}'>Elimina</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Nessuna categoria trovata.</td></tr>";
            }
            ?>
        </tbody>
    </table>
  </div>
    <?php include "../../partial/footer.php"; ?>
</body>
</html>
