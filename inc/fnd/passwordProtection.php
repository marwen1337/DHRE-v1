<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 18.12.2019
 * Time: 21:46
 */

if(!empty($_COOKIE['passwordProtection'])){
    $cookie = json_decode($_COOKIE['passwordProtection'], true);
    if(!empty($cookie[$_GET['id']])){

        $stmt = $core->getPDO()->prepare("SELECT short_id, short_targeturl from shortlinks WHERE short_password = :short_password AND short_token = :short_token");
        $stmt->execute(array(":short_token" => $_GET['id'], ":short_password" => $cookie[$_GET['id']]));
        $passwordCorrect = ($stmt->rowCount() > 0);
        if($passwordCorrect){
            header("Location: " . $stmt->fetch()['short_targeturl']);
            exit;
        }

    }
}


if(!empty($_POST['password']) && !empty($_GET['id'])){
    $stmt = $core->getPDO()->prepare("SELECT short_id, short_targeturl from shortlinks WHERE short_password = :short_password AND short_token = :short_token");
    $stmt->execute(array(":short_token" => $_GET['id'], ":short_password" => $core->hash($_POST['password'])));
    $passwordCorrect = ($stmt->rowCount() > 0);
    if($passwordCorrect){
        $cookie = empty($_COOKIE['passwordProtection']) ? array() : json_decode($_COOKIE['passwordProtection'], true);

        $cookie[$_GET['id']] = $core->hash($_POST['password']);

        setcookie("passwordProtection", json_encode($cookie), time() + 3600);

        print_r(json_encode(array("passwordCorrect" => true, "url" => $stmt->fetch()['short_targeturl'])));
    }else{
        print_r(json_encode(array("passwordCorrect" => false)));
    }
    exit;
}

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
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/siiimpletoast.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="password_needed"></title>

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

<div class="container-fluid h-100">
    <div class="content text-center h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-12 col-sm-11 col-md-8 col-lg-4">
                <div class="collapse show">
                    <div class="card text-left">
                        <div class="card-body">
                            <h3 class="card-title" lang="password_needed"></h3>
                            <div class="card-text">
                                <form id="form_verify">
                                    <div class="form-group">
                                        <label for="form_password" lang="password"></label>
                                        <div class="input-group">
                                            <input class="form-control" id="form_password" name="form_password" type="password" lang="password" lang_scope="placeholder" autofocus required autocomplete="off">
                                            <div class="invalid-feedback">
                                                <span lang="password_invalid"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <input class="btn btn-primary w-100 mt-3" id="form_submit" type="submit" name="form_submit" lang="verify_password" lang_scope="value" autocomplete="off">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=$core->getWebUrl();?>/assets/js/jquery.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/popper.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/bootstrap.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/script.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/siimpletoast.js"></script>
<script src="<?=$core->getWebUrl()?>/assets/js/bootbox.all.min.js"></script>
<script src="<?=$core->getWebUrl()?>/assets/js/chart.min.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script>

    $(document).ready(function (event) {

        var softwareURL = '<?=$core->getWebUrl();?>';

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

        $('#form_verify').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: window.location,
                type: "post",
                data: {action: "verifyPassword", password: $('#form_password').val()},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(json.passwordCorrect){
                        $('.collapse').collapse('hide');
                        window.location.href = json.url;
                    }else {
                        $('#form_password').addClass('is-invalid');
                    }
                }
            })
        });

        $('#form_password').on('input', function (event) {
           $('#form_verify').trigger('submit');
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>


