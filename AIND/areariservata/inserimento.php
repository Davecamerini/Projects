<?php
	include('includedb.php');
		
	$username = $_GET['username'];
	
	// CONTROLLO SE L'UTENTE E' GIA' STATO INSERITO
	$query = "SELECT * FROM area_ris WHERE IG_user = '$username'";
	$result = mysqli_query($conn,$query);
    $esiste = mysqli_fetch_array($result);
	
	// SE NON LO TROVO LO INSERISCO
    if (mysqli_num_rows($result) == 0){
		$queryins = "INSERT INTO area_ris(ID,IG_user,iscrizione) values(NULL,'$username',DEFAULT)";
		$result = mysqli_query($conn,$queryins);
        header('Location: https://www.artisnotdead.it/areariservata/admin.php');
	}
	else{
		header('Location: https://www.artisnotdead.it/areariservata/artista.php?id='.$esiste['ID']);	
    }
?>