<?php
require_once(__DIR__ . '/../config.php');
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Fornitori</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- Lightbox2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/fornitori/style/style.css" rel="stylesheet">
    
    <!-- Common styles -->
    <style>
        .content-page-fornitori {
            padding: 20px 10px 80px 10px;
            width: 90%;
        }
        .content-page-fornitori p, 
        .content-page-fornitori h1, 
        .content-page-fornitori h2, 
        .content-page-fornitori h3, 
        .content-page-fornitori h4, 
        .content-page-fornitori div, 
        .content-page-fornitori span {
            font-family: "Cormorant Garamond";
        }
        .content-page-fornitori h1 {
            font-size: 60px;
        }
        .content-page-fornitori p {
            font-size: 22px;
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
        .category-card .card-img-top {
            width: 100%;
            aspect-ratio: 9 / 14;
            overflow: hidden;
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
    </style>
    
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
<?php getWordPressHeader(); ?> 