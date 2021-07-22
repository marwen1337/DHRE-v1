<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 17.11.2019
 * Time: 00:11
 */

if(!$core->getUserData()['user_isAdmin']) header("Location: " . $core->getWebUrl());

if(!empty($_GET['uid'])){
    $stmt = $core->getPDO()->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(array(":user_id" => $_GET['uid']));
    $userdata = $stmt->fetch();
}else{
    header("Location: " . $core->getWebUrl());
    exit;
}

if(!empty($_POST)){
    $action = $_POST['action'];
    if($action == "setUserActive"){
        $stmt = $core->getPDO()->prepare("UPDATE users SET user_active = :val WHERE user_id = :user_id");
        $stmt->execute(array(":val" => boolval($_POST['value']), ":user_id" => $_POST['uid']));

        if(!boolval($_POST['value'])){
            $stmt = $core->getPDO()->prepare("DELETE FROM autologin WHERE login_owner = :user_id");
            $stmt->execute(array(":user_id" => $_POST['uid']));
        }

        print_r(json_encode(array("error" => false, "current_active" => boolval($_POST['value']))));
        exit;
    }else if($action == "editUser"){
        $uid = $_POST['form_edituser_id'];
        $email = $_POST['form_edituser_email'];
        $shortquota = $_POST['form_edituser_shortquota'];
        $imgquota = $_POST['form_edituser_imgquota'];
        $customtoken = isset($_POST['form_edituser_customtoken']);

        $stmt = $core->getPDO()->prepare("UPDATE users SET user_email = :user_email, user_shortquota = :user_shortquota, user_imgquota = :user_imgquota, user_customtoken = :user_customtoken WHERE user_id = :user_id");
        $stmt->execute(array(":user_email" => $email, ":user_shortquota" => $shortquota, ":user_imgquota" => $imgquota, ":user_customtoken" => $customtoken, ":user_id" => $uid));

        print_r(json_encode(array("error" => false)));
        exit;
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
                        <li class="breadcrumb-item active" aria-current="page">Manage User</li>
                    </ol>
                    <div class="row">
                        <div class="col">
                            <h3 class="card-title">Manage User "<?=$userdata['user_name']?>" <?=$userdata['user_active'] ? "" : "(disabled)"?></h3>
                        </div>
                        <div class="col text-right">
                            <?=$userdata['user_active'] ? '<button class="btn btn-danger" id="btn_setuseractive" data-setactive="0"><i class="fa fa-user-slash"></i> Deactivate User</button>' : '<button class="btn btn-danger" id="btn_setuseractive" data-setactive="1"><i class="fa fa-user"></i> Activate User</button>'?>
                        </div>
                    </div>
                    <div class="card-text">
                        <form id="form_edituser">
                            <fieldset <?=$userdata['user_active'] ? '' : 'disabled="true"'?>>
                                <div class="form-group">
                                    <label for="form_edituser_username">Username</label>
                                    <input class="form-control" type="text" id="form_edituser_username" name="form_edituser_username" placeholder="Username" value="<?=$userdata['user_name']?>" disabled>
                                    <small>Set the users Username to login with</small>
                                </div>
                                <div class="form-group">
                                    <label for="form_edituser_email">Email</label>
                                    <input class="form-control" type="email" id="form_edituser_email" name="form_edituser_email" placeholder="Email" value="<?=$userdata['user_email']?>" required>
                                    <small>Set the users Email. It will be used for password resets and the first password exchange</small>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="form_edituser_username">Shortlinkquota (-1 for Unlimited)</label>
                                            <input class="form-control" type="number" id="form_edituser_shortquota" name="form_edituser_shortquota"  value="<?=$userdata['user_shortquota']?>" min="-1" max="1000000000"  required>
                                            <small>Set the max. Shortlinks, the user should be able to create</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="form_edituser_username">Imagequota (-1 for Unlimited)</label>
                                            <input class="form-control" type="text" id="form_edituser_imgquota" name="form_edituser_imgquota"  value="<?=$userdata['user_imgquota']?>" min="-1" max="1000000000" required>
                                            <small>Set the max. Images, the user should be able to upload</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" id="form_edituser_customtoken" type="checkbox" name="form_edituser_customtoken" <?=$userdata['user_customtoken'] ? "checked" : ""?>>
                                    <label class="custom-control-label" for="form_edituser_customtoken"> Customtoken</label><br>
                                    <small>Decide, if the user should be able to use customtokens for his links</small>
                                </div>
                                <input type="hidden" name="action" value="editUser">
                                <input type="hidden" name="form_edituser_id" value="<?=$userdata['user_id']?>">
                                <input class="btn btn-primary" type="submit" value="Save changes">
                            </fieldset>
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

        $('#form_edituser').on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: window.location,
                type: "post",
                data: $('#form_edituser').serialize(),
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        toast.success("Changes were saved!");
                    }else{
                        toast.alert(json.reason);
                    }
                }
            })
        });

        $('#btn_setuseractive').on('click', function (event) {
            event.preventDefault();
            $.ajax({
                url: window.location,
                type: "post",
                data: {action: "setUserActive", uid: "<?=$userdata['user_id']?>", value: $('#btn_setuseractive').attr('data-setactive')},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        if(json.current_active){
                            $('#btn_setuseractive').html('<i class="fa fa-user-slash"></i> Deactivate User');
                            $('#btn_setuseractive').attr('data-setactive', 0);
                            $('fieldset').removeAttr("disabled");
                        }else {
                            $('#btn_setuseractive').html('<i class="fa fa-user"></i> Activate User');
                            $('#btn_setuseractive').attr('data-setactive', 1);
                            $('fieldset').attr("disabled", true);
                        }
                    }else {
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

