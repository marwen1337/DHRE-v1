<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 29.08.2019
 * Time: 18:45
 */

if($core->isUserLoggedIn()) header("Location: dash");

if(!empty($_POST)){
    $username = $_POST['form_username'];
    $password = $_POST['form_password'];
}

?>
<!doctype html>
<html lang="<?= $_COOKIE['lang']; ?>">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/fontawesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="register"></title>

    <script>
        var lang = <?php echo json_encode($core->fullLang());?>;
    </script>

</head>
<body>

<?php include "navbar.php";?>

<div class="container-fluid h-100">
    <div class="content text-center h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-12 col-sm-11 col-md-8 col-lg-4">
                <div class="card text-left">
                    <div id="accordion">
                        <div class="card-body" id="collapse_register">
                            <div class="collapse show" data-parent="#accordion">
                                <h3 class="card-title" lang="register"></h3>
                                <div class="card-text">
                                    <form id="form_register">
                                        <div class="form-group">
                                            <label for="form_username" lang="username"></label>
                                            <div class="input-group">
                                                <input class="form-control" id="form_username" name="form_username" type="text" lang="username" lang_scope="placeholder" autofocus>
                                                <div class="invalid-feedback">
                                                    <span lang="username_invalid"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_password" lang="password"></label>
                                            <div class="input-group">
                                                <input class="form-control" id="form_password" name="form_password" type="password" lang="password" lang_scope="placeholder">
                                                <div class="input-group-append" id="form_password_peak">
                                                    <span class="input-group-text" id="basic-addon2"><i class="fa fa-eye-slash"></i></span>
                                                </div>
                                                <div class="invalid-feedback">
                                                    <span lang="password_invalid"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_password_confirm" lang="password_confirm"></label>
                                            <div class="input-group">
                                                <input class="form-control" id="form_password_confirm" name="form_password_confirm" type="password" lang="password_confirm" lang_scope="placeholder">
                                                <div class="input-group-append" id="form_password_peak">
                                                    <span class="input-group-text" id="basic-addon2"><i class="fa fa-eye-slash"></i></span>
                                                </div>
                                                <div class="invalid-feedback">
                                                    <span lang="password_invalid"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <input class="btn btn-primary w-100 mt-3" id="form_submit" type="submit" name="form_submit" lang="register" lang_scope="value">
                                    </form>
                                    <div class="row">
                                        <div class="col"><hr></div>
                                        <div class="col-auto"><span lang="or"></span></div>
                                        <div class="col"><hr></div>
                                    </div>
                                    <a href="login"><button class="btn btn-info w-100" lang="login"></button></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center" id="collapse_success">
                            <div class="collapse" data-parent="#accordion">
                                <h3 class="card-title" lang="register_successful"></h3>
                                <div class="card-text">
                                    <a href="login"><button class="btn btn-success w-100" lang="continue"></button></a>
                                    <small lang="register_successful_long"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $core->getWebUrl();?>/assets/js/jquery.min.js"></script>
<script src="<?php echo $core->getWebUrl();?>/assets/js/popper.min.js"></script>
<script src="<?php echo $core->getWebUrl();?>/assets/js/bootstrap.min.js"></script>
<script src="<?php echo $core->getWebUrl();?>/assets/js/script.js"></script>
<script>
    $(document).ready(function () {

        doLanguage();

        $('#form_login').on('submit', function (event) {
            event.preventDefault();

            var data = $(this).serialize();
            $.ajax({
                url: window.location,
                type: 'post',
                data: data,
                success: function (data) {
                    var json = JSON.parse(data);

                    if(json.error == true){
                        $("#form_login input:not(input[type=checkbox])").addClass("is-invalid");
                    }else {
                        $('.collapse').collapse('show').find('button').focus();
                    }
                },
                error: function (data) {

                }
            });
        });
        $('input').on('input', function (event) {
            if($(this).val().length === 0) $(this).addClass('is-invalid').removeClass('is-valid');
            else $(this).addClass('is-valid').removeClass('is-invalid');
        });
        $('#form_password_peak').on('click', function (event) {
            var input = $(this).closest('input[type=password]');
            var attr = input.attr("type");

            if(attr === "text") input.attr("type", "password");
            else input.attr("type", "text");

            $(this).find('i.fa-eye, i.fa-eye-slash').toggleClass("fa-eye-slash fa-eye");
        });
    });
</script>
</body>
</html>
