<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}


if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}

$cookie_name = "utente_id_admin"; // Sostituisci con il nome del tuo cookie
if (!isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
  header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
  exit();
}

// Verifica se è stato passato un parametro ID valido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $categoria_id = $_GET['id'];

    // Esegui la query per ottenere i dati della categoria specificata
    $sql = "SELECT id, categoria, slug, copertina FROM categorie_corsi_online WHERE id = $categoria_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $categoria = $row['categoria'];
        $slug = $row['slug'];
        $copertina = $row['copertina'];
    } else {
        echo "Categoria non trovata.";
        exit();
    }
} else {
    echo "ID categoria non specificato.";
    exit();
}

// Chiudi la connessione al database
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Categoria</title>

</head>
<body>
  <div style="width:100%">
    <?php include "../../partial/header.php"; ?>
    <style>

form {
  max-width: 400px;
  margin: 40px auto;
  padding: 20px;
  background-color: #fcf6f5;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
  border:unset;
}
label {
  display: block;
  margin-top: 10px;
  color: #333;
}
input {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  margin-bottom: 15px;
  box-sizing: border-box;
}
button {
  background-color: #990011;
  color: #fff;
  padding: 10px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  width: 100%;
}
button:hover {
  background-color: #ff3d3d;
}
img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 10px auto;
}
    </style>

    <div style="background: #990011; width:100%;">
    <h1 class="h1-gestionale">Modifica Categoria</h1>
  </div>
    <form action="processa_modifica_categoria.php" method="post">
        <input type="hidden" name="categoria_id" value="<?php echo $categoria_id; ?>">

        <label for="categoria">Categoria:</label>
        <input type="text" name="categoria" value="<?php echo $categoria; ?>" required><br>

        <label for="copertina">Copertina (JPG):</label>
        <input type="file" name="copertina" accept="image/jpeg">
        <img src="<?php echo $copertina; ?>" alt="Copertina attuale" style="max-width: 100px; max-height: 100px;"><br>

        <button type="submit">Salva Modifiche</button>
    </form>
      <?php include "../../partial/footer.php"; ?>
    </div>
</body>
</html>
