<!doctype html>
<html lang="it">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width" />
        <title>Admin Page</title>
        <link href="admin.css" rel="stylesheet" />
    </head>
    <body>
        <div class="header">
            <a href="https://www.artisnotdead.it" target="_blank">
                <img style="width: 150px;" src="https://artisnotdead.it/wp-content/uploads/2024/03/aind.png" />
            </a>
            <!--<br />
            <span class="sottotitolo">www.artisnotdead.it</span>-->
        </div>

        <div class="map">
            <table class="tabella">
                <tr>
                    <td class="cella">
                         <form action="inserimento.php" method="get">
                             <label for="username" class="campo_user">Inserisci il nome utente (senza l'@)</label><br />
                             <input type="text" name="username" class="user_input" /><br />
                             <input type="submit" value="Inserisci" class="form_bottone" />
                         </form>
                     </td>
                     <td class="cella">
                         <form action="cancellazione.php" method="get">
                             <label for="username" class="campo_user">Inserisci il nome utente da cancellare (senza l'@)</label><br />
                               <input type="text" name="username" class="user_input" /><br />
                               <input type="submit" value="Cancella" class="form_bottone" />
                         </form>
                    </td>
                </tr>
            </table>
            <table class="tabella" style="margin-top: 40px;">
                <tr>
                    <th class="cella_head">ID</th>
                    <th class="cella_head">Nome utente</th>
                    <th class="cella_head">Data iscrizione</th>
                </tr>
                <?php
                include ('includedb.php');

                // TIRO FUORI TUTTI I DATI DEL DB
                $query = "SELECT * FROM area_ris";
                $result = mysqli_query($conn, $query);

                while($row = mysqli_fetch_array($result)) {

                    $id = $row['ID'];
                    $username = $row['IG_user'];
                    $iscrizione = $row['iscrizione'];

                    echo '<tr>';
                    echo '<td class="tabella_id">'.$id.'</td>';
                    echo '<td class="tabella_user"><a style="text-decoration: none;color: white;" href="https://www.artisnotdead.it/areariservata/artista.php?id='.$id.'">'.$username.'</td>';
                    echo '<td class="tabella_data">'.$iscrizione.'</td>';
                    echo '</tr>';
                }
                ?>
            </table>
        </div>

        <div class="victory">
            <a href="https://www.instagram.com/artisnotdead.it/">
                <img
                id="victory"
                src="https://artisnotdead.it/wp-content/uploads/2024/05/favicon-1.png" />
            </a>
        </div>
    </body>
</html>