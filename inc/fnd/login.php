<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 08.08.2019
 * Time: 15:51
 */

if($core->isUserLoggedIn()) header("Location: dash");

if(!empty($_POST)){
    $username = $_POST['form_username'];
    $password = $_POST['form_password'];
    $remain = !empty($_POST['form_remain']);
    $stmt = $core->getPDO()->prepare("SELECT * FROM users WHERE user_name = :user_name AND user_password = :user_password AND user_active = 1 LIMIT 1");
    $stmt->execute(array(":user_name" => $username, ":user_password" => $core->hash($password)));
    if($stmt->rowCount() == 1){
        $row = $stmt->fetch();
        $_SESSION['uid'] = $row['user_id'];

        if(!empty($remain)){
            $data = array("rowEmpty" => true, "token" => 0, "pass" => 0);
            while (true){
                $data['token'] = $core->randomString(32);
                $data['pass'] = $core->randomString();
                $stmt = $core->getPDO()->prepare("SELECT login_id FROM autologin WHERE login_token = :token");
                $stmt->execute(array(":token" => $data['token']));
                $rowEmpty = $stmt->rowCount() == 0;
                if($rowEmpty){
                    $stmt = $core->getPDO()->prepare("INSERT INTO autologin (login_owner, login_token, login_pass, login_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute(array($core->getUserID(), $data['token'], $data['pass'], "normal"));
                    setcookie("autologin_token", $data['token'], time() + 3600 * 24 * 365);
                    setcookie("autologin_pass", $data['pass'], time() + 3600 * 24 * 365);
                    break;
                }
            }
        }
        print_r(json_encode(array("error" => false)));
    }else $core->printError("wrong_login");
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
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/fontawesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $core->getWebUrl();?>/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="login"></title>

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
                <div class="card text-left">
                    <div id="accordion">
                        <div class="card-body m-3">
                            <div id="collapse_login">
                                <div class="collapse show" data-parent="#accordion">
                                    <h3 class="card-title" lang="login"></h3>
                                    <div class="card-text">
                                        <form id="form_login">
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
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" id="form_remain" type="checkbox" name="form_remain">
                                                <label class="custom-control-label" for="form_remain" lang="remain_signed_in"></label>
                                            </div>
                                            <input class="btn btn-primary w-100 mt-3 btn-loading" id="form_submit" type="submit" name="form_submit" lang="login" lang_scope="value">
                                            <a class="" href="resetPassword" lang="forgot_password"></a>
                                        </form>
                                        <!--
                                        <div class="row">
                                            <div class="col"><hr></div>
                                            <div class="col-auto"><span lang="or"></span></div>
                                            <div class="col"><hr></div>
                                        </div>
                                        <a href="register"><button class="btn btn-info w-100" lang="register"></button></a>
                                        -->
                                    </div>
                                </div>
                            </div>
                            <div class="collapse_success">
                                <div class="collapse" data-parent="#accordion">
                                    <h3 class="card-title" lang="login_successful"></h3>
                                    <div class="card-text">
                                        <a href="dash"><button class="btn btn-success w-100" lang="continue"></button></a>
                                        <small lang="login_successful_long"></small>
                                    </div>
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
    $(document).ready(function (event) {

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

                    if(!json.error){
                        $('.collapse').collapse('show').find('button').focus();
                    }else {
                        $("#form_login input:not(input[type=checkbox])").addClass("is-invalid");
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
            var input = $('#form_password');
            var attr = input.attr("type");

            if(attr === "text") input.attr("type", "password");
            else input.attr("type", "text");

            $(this).find('i.fa-eye, i.fa-eye-slash').toggleClass("fa-eye-slash fa-eye");
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
