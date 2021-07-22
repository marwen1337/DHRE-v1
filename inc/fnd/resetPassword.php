<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 15.11.2019
 * Time: 15:08
 */

if($core->isUserLoggedIn()) header("Location: dash");


if(!empty($_POST['action'])){

    $mail = $core->getMailer();

    $action = $_POST['action'];

    if($action == "sendToken"){
        $email = $_POST['email'];
        $stmt = $core->getPDO()->prepare("SELECT user_id FROM users WHERE user_email = :email");
        $stmt->execute(array(":email" => $email));
        $uid = $stmt->fetch()['user_id'];
        $stmt = $core->getPDO()->prepare("INSERT INTO passwordReset (reset_creator, reset_token, reset_created) VALUES (?, ?, ?)");
        $token = $core->randomString(8);
        $stmt->execute(array($uid, $token, time()));


        $mail->AddAddress($email);
        $mail->Subject = 'Password Reset';
        $body = str_replace("%token%", $token, $core->lang("reset_password_email_content"));
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        if(!$mail->Send()) {
            $core->printError("email_error");
            exit;
        }
        print_r(json_encode(array("error" => false)));
        exit;
    }else if($action == "checkTokenAndSendPassword"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM passwordReset WHERE reset_token = :token");
        $stmt->execute(array(":token" => $_POST['token']));
        $row = $stmt->fetch();
        if($stmt->rowCount() == 1){

            $stmt = $core->getPDO()->prepare("DELETE FROM passwordReset WHERE reset_token = :token");
            $stmt->execute(array(":token" => $_POST['token']));

            $stmt = $core->getPDO()->prepare("SELECT user_email FROM users WHERE user_id = :uid");
            $stmt->execute(array(":uid" => $row['reset_creator']));
            $email = $stmt->fetch()['user_email'];

            $new_password = $core->randomString(16);
            $stmt = $core->getPDO()->prepare("UPDATE users SET user_password = :password WHERE user_id = :uid");
            $stmt->execute(array(":password" => $core->hash($new_password), ":uid" => $row['reset_creator']));

            $mail->AddAddress($email);
            $mail->Subject = 'New Password';
            $body = "Your new Password: <strong>" . $new_password . "</strong>";
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            if(!$mail->Send()) {
                $core->printError("email_error");
                exit;
            }

            print_r(json_encode(array("error" => false)));
            exit;
        }else{
            $core->printError("invalid_token");
            exit;
        }
    }
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
    <title lang="reset_password"></title>

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
                    <div class="card-body">
                        <h3 class="card-title" lang="reset_password"></h3>
                        <div class="card-text">
                            <div id="accordion">
                                <div id="collapse_email">
                                    <div class="collapse show" data-parent="#accordion">
                                        <form id="form_reset">
                                            <div class="form-group">
                                                <label for="form_email" lang="email_adress"></label>
                                                <div class="input-group">
                                                    <input class="form-control" id="form_email" name="form_email" type="text" lang="email_adress" lang_scope="placeholder" autofocus required autocomplete="off">
                                                    <div class="invalid-feedback">
                                                        <span lang="email_invalid"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input class="btn btn-primary w-100 mt-3" id="form_submit" type="submit" name="form_submit" lang="reset_password" lang_scope="value" autocomplete="off">
                                        </form>
                                    </div>
                                </div>
                                <div id="collapse_token">
                                    <div class="collapse" data-parent="#accordion">
                                        <form id="form_validate">
                                            <div class="form-group">
                                                <label for="form_token" lang="reset_token"></label>
                                                <div class="input-group">
                                                    <input class="form-control" id="form_token" name="form_token" type="text" lang="token" lang_scope="placeholder" autofocus required>
                                                    <div class="invalid-feedback">
                                                        <span lang="email_invalid"></span>
                                                    </div>
                                                </div>
                                                <small lang="token_from_email_long"></small>
                                                <a href="#" id="no_email_received" onclick="$('#form_reset').trigger('submit');"><small lang="no_email_received"></small></a>
                                            </div>
                                        </form>
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

        $('#form_email').on('keyup', function (event) {
            var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
            if(email_regex.test($('#form_email').val())){
                $('#form_email').removeClass('is-invalid').addClass('is-valid');
            }else {
                $('#form_email').removeClass('is-valid').addClass('is-invalid');
            }
        });

        $('#form_reset').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: window.location,
                type: "post",
                data: {action: "sendToken", email: $('#form_email').val()},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        $('.collapse').collapse('show');
                    }else {
                        toast.alert("Error");
                    }
                }
            })
        });

        $('#form_validate').on('submit', function (event) {
           event.preventDefault();
           $.ajax({
               url: window.location,
               type: "post",
               data: {action: "checkTokenAndSendPassword", token: $('#form_token').val()},
               success: function (data) {
                   var json = JSON.parse(data);
                   if(!json.error){
                       bootbox.alert({
                           message: lang['new_password_sent'],
                           callback: function (result) {
                             window.location.href = "login";
                           }
                       });
                   }else {
                       $('#form_token').prop('disabled', false);
                       toast.alert(lang['invalid_reset_token']);
                   }
               }
           })
        });

        $('#form_token').on('input', function (event) {
           if($(this).val().length === 8){
               console.log("Test");
               $(this).prop('disabled', true);
               $('#form_validate').trigger('submit');
           }
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>

