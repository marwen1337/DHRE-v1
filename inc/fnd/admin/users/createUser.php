<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 17.11.2019
 * Time: 00:11
 */

if(!$core->getUserData()['user_isAdmin']) header("Location: " . $core->getWebUrl());

if(!empty($_POST)){
    $username = $_POST['form_createuser_username'];
    $email = $_POST['form_createuser_email'];
    $password = $core->randomString(12);
    $shortquota = intval($_POST['form_createuser_shortquota']);
    $imgquota = intval($_POST['form_createuser_imgquota']);
    $customtoken = isset($_POST['form_createuser_customtoken']);

    $stmt = $core->getPDO()->prepare("SELECT user_id FROM users WHERE user_name = :user_name OR user_email = :user_email");
    $stmt->execute(array(":user_name" => $username, ":user_email" => $email));
    if($stmt->rowCount() > 0){
        $core->printError("Username or Email already exists");
        exit;
    }
    $data = array($username, $email, $core->hash($password), $shortquota, $imgquota, $customtoken, false, true);
    $stmt = $core->getPDO()->prepare("INSERT INTO users (user_name, user_email, user_password, user_shortquota, user_imgquota, user_customtoken, user_isAdmin, user_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute($data);

    $mail = $core->getMailer();
    $mail->addAddress($email);
    $mail->Subject = $core->lang('email_welcome_subject');
    $body = $core->lang('email_welcome_content');
    $body = str_replace("%username%", $username, $body);
    $body = str_replace("%password%", $password, $body);
    $body = str_replace("%loginUrl%", $core->getWebUrl() . "app/login", $body);
    $mail->Body = $body;

    if(!$mail->send()){
        echo $mail->ErrorInfo;
        $core->printError("email_error");
        exit;
    }
    print_r(json_encode(array("error" => false, "data" => $data)));
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
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/siiimpletoast.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title>Administrator Interface</title>

    <script>
        var lang = <?php echo json_encode($core->fullLang());?>;
    </script>

</head>
<body>

<?php include dirname(__FILE__) . "/../../navbar.php";?>

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
                    <ol class="breadcrumb bg-white p-0">
                        <li class="breadcrumb-item"><a href="dash">Admin</a></li>
                        <li class="breadcrumb-item"><a href="users/listUsers">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create User</li>
                    </ol>
                    <h3 class="card-title">Create User</h3>
                    <div class="card-text">
                        <form id="form_createuser">
                            <div class="form-group">
                                <label for="form_createuser_username">Username</label>
                                <input class="form-control" type="text" id="form_createuser_username" name="form_createuser_username" placeholder="Username" required>
                                <small>Set the users Username to login with</small>
                            </div>
                            <div class="form-group">
                                <label for="form_createuser_email">Email</label>
                                <input class="form-control" type="email" id="form_createuser_email" name="form_createuser_email" placeholder="Email" required>
                                <small>Set the users Email. It will be used for password resets and the first password exchange</small>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="form_createuser_username">Shortlinkquota (-1 for Unlimited)</label>
                                        <input class="form-control" type="number" id="form_createuser_shortquota" name="form_createuser_shortquota" value="10" min="-1" max="1000000000" required>
                                        <small>Set the max. Shortlinks, the user should be able to create</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="form_createuser_username">Imagequota (-1 for Unlimited)</label>
                                        <input class="form-control" type="text" id="form_createuser_imgquota" name="form_createuser_imgquota" value="10" min="-1" max="1000000000" required>
                                        <small>Set the max. Images, the user should be able to upload</small>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" id="form_createuser_customtoken" type="checkbox" name="form_createuser_customtoken">
                                <label class="custom-control-label" for="form_createuser_customtoken"> Customtoken</label><br>
                                <small>Decide, if the user should be able to use customtokens for his links</small>
                            </div>
                            <input class="btn btn-primary" type="submit" value="Create User">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=$core->getWebUrl();?>/assets/js/jquery.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/popper.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/bootstrap.min.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/siimpletoast.js"></script>
<script src="<?=$core->getWebUrl();?>/assets/js/bootbox.all.min.js"></script>
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

        $('#form_createuser').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: window.location,
                type: "post",
                data: $('#form_createuser').serialize(),
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        bootbox.alert({
                            message: "The user was created!",
                            size: "small",
                            callback: function () {
                                window.location.href = adminUrl + "users/listUsers";
                            }
                        });
                    }else{
                        toast.alert(json.reason);
                    }
                }
            })
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
