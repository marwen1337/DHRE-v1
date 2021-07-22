<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 17.11.2019
 * Time: 00:11
 */

if(!$core->getUserData()['user_isAdmin']) header("Location: " . $core->getWebUrl());

?>
<!doctype html>
<html lang="<?= $_COOKIE['lang']; ?>">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/fontawesome/css/all.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title>Administrator Interface</title>

    <script>
        var lang = <?php echo json_encode($core->fullLang());?>;
    </script>

</head>
<body>

<?php include dirname(__FILE__) . "/../navbar.php";?>

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
    <div class="row m-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <h3 class="card-title">Manage Users</h3>
                        </div>
                        <div class="col text-right justify-content-center align-self-center">
                            <h2><i class="fa fa-users-cog"></i></h2>
                        </div>
                    </div>
                    <a class="stretched-link w-100" href="users/listUsers"><button class="btn btn-primary w-100">Manage Users</button></a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <h3 class="card-title">Create User</h3>
                        </div>
                        <div class="col text-right justify-content-center align-self-center">
                            <h2><i class="fa fa-user-plus"></i></h2>
                        </div>
                    </div>
                    <a class="stretched-link w-100" href="users/createUser"><button class="btn btn-primary w-100">Create User</button></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row m-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <h3 class="card-title">Maintenance</h3>
                        </div>
                        <div class="col text-right justify-content-center align-self-center">
                            <h2><i class="fa fa-tools"></i></h2>
                        </div>
                    </div>
                    <a class="stretched-link w-100" href="system/maintenanceSystem"><button class="btn btn-primary w-100">Maintenance</button></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=$core->getWebUrl();?>/assets/js/jquery.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/popper.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/bootstrap.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/siimpletoast.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/script.js"></script>
<script>
    $(document).ready(function (event) {

        var adminUrl = '<?=$core->getWebUrl() . "app/admin/"?>';

        doLanguage();

        var toast = siiimpleToast.setOptions({
            container: 'body',
            class: 'siiimpleToast',
            position: 'top|right',
            margin: 15,
            delay: 0,
            duration: 3000,
            style: {},
        });

        $('a:not(.nav-link)').on('click', function (event) {
            event.preventDefault();
            window.location.href = adminUrl + $(this).attr('href');
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
