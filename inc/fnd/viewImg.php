<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 08.08.2019
 * Time: 15:52
 */

?>
<!doctype html>
<html lang="<?= $_COOKIE['lang']; ?>">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/fontawesome/css/all.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/styles.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/siiimpletoast.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/chart.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">

    <!-- Primary Meta Tags -->
    <title>Image on dhre</title>
    <meta name="title" content="Image on dhre">
    <meta name="description" content="View this image on dhre">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://dhre.de">
    <meta property="og:title" content="Image on dhre">
    <meta property="og:description" content="View this image on dhre">
    <meta property="og:image" content="<?=strtok($core->getUrl(), '?') . '?raw'?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://dhre.de">
    <meta property="twitter:title" content="Image on dhre">
    <meta property="twitter:description" content="View this image on dhre">
    <meta property="twitter:image" content="<?=strtok($core->getUrl(), '?') . '?raw'?>">

    <script>
        var lang = <?php echo json_encode($core->fullLang());?>;
    </script>

</head>
<body>

<?php include "navbar.php";?>

<div class="bg-light" id="loader" style="position: fixed;top: 0; left: 0;height: 100%; width: 100%;z-index: 1000000;">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-12 text-center">
            <div class="spinner-grow text-primary" style="height: 3rem; width: 3rem;">
                <span class="sr-only"></span>
            </div>
        </div>
    </div>
</div>

<div class="ajaxIndicator" style="position: fixed;bottom: 10px;right: 10px;display: none;">
    <div class="spinner-border text-primary">
        <span class="sr-only"></span>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col">
            <a href="<?=strtok($core->getUrl(), '?') . '?raw'?>">
                <img class="img-fluid mx-auto d-block rounded m-5" src="<?=strtok($core->getUrl(), '?') . '?raw'?>" alt="Image on DHRE.DE" data-toggle="lightbox">
            </a>
        </div>
    </div>
</div>

<script src="<?=$core->getWebUrl();?>assets/js/jquery.min.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/popper.min.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/bootstrap.min.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/script.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/siimpletoast.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/bootbox.all.min.js"></script>
<script src="<?=$core->getWebUrl();?>assets/js/chart.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.waitforimages/1.5.0/jquery.waitforimages.min.js"></script>
<script>
    $(document).ready(function () {

        doLanguage();

        $('.img-fluid').waitForImages(function () {
            $('#loader').delay(200).fadeOut(400);
        });
    })
</script>
</body>
</html>
