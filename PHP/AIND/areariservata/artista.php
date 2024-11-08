<!doctype html>
<html lang="it">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width" />
        <title>Artista</title>
        <link href="artista.css" rel="stylesheet" />
    </head>
    <body>
        <div class="header">
            <a href="https://www.artisnotdead.it" target="_blank">
                <img style="width: 150px;" src="https://artisnotdead.it/wp-content/uploads/2024/03/aind.png" />
            </a>
            <!--<br>
            <span class="sottotitolo">www.artisnotdead.it</span>-->
        </div>
        
        <div class="username">
            <?php
                include('includedb.php');
            
            $id=$_GET['id'];
            $result=mysqli_query($conn, "SELECT IG_user FROM area_ris where id='$id'");
            $row=mysqli_fetch_array($result);
            
            echo '<a href="https://www.instagram.com/'.$row['IG_user'].'" class="bottone">@'.$row['IG_user'].'</a>';
            ?>
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