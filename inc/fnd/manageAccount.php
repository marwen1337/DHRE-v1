<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 13.11.2019
 * Time: 21:17
 */

if(!$core->isUserLoggedIn()) header("Location: login");

if(!empty($_POST['action'])){
    if($_POST['action'] == "passwordChange"){

        $stmt = $core->getPDO()->prepare("UPDATE users SET user_password = :user_password WHERE user_id = :user_id");
        $stmt->execute(array(":user_password" => $core->hash($_POST['password']), ":user_id" => $core->getUserID()));
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
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/siiimpletoast.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/chart.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="manage_account"></title>

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
           <div class="card m-3">
               <div class="card-body">
                   <h3 class="card-title" lang="change_password"></h3>
                   <span class="card-subtitle" lang="change_password_long"></span>
                   <div class="card-text mt-3">
                       <form id="password_change_form">
                           <div class="form-group">
                               <input class="form-control" type="password" id="passwordform_newpassword" name="passwordform_newpassword" lang="new_password" lang_scope="placeholder" required>
                               <div class="invalid-feedback" lang="passwords_different"></div>
                           </div>
                           <div class="form-group">
                               <input class="form-control" type="password" id="passwordform_newpassword_repeat" name="passwordform_newpassword_repeat" lang="new_password_repeat" lang_scope="placeholder" required>
                               <div class="invalid-feedback" lang="passwords_different"></div>
                           </div>
                           <input class="btn btn-primary w-100" type="submit" id="passwordform_submit" lang="change_password" lang_scope="value">
                       </form>
                   </div>
               </div>
           </div>
       </div>
   </div>
    <div class="row">
        <div class="col">
            <div class="card m-3">
                <div class="card-body">
                    <h3 class="card-title" lang="manage_startpage"></h3>
                    <span class="card-subtitle" lang="manage_startpage_long"></span>
                    <div class="card-text mt-3">
                        <a class="stretched-link" href="manageStartpage"><button class="btn btn-primary w-100" lang="manage_startpage"></button></a>
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


        $('#password_change_form').on('submit', function (event) {
           event.preventDefault();

           if($('#passwordform_newpassword').hasClass('is-valid')){
               $.ajax({
                   url: window.location,
                   type: "post",
                   data: {action: "passwordChange", password: $('#passwordform_newpassword').val()},
                   success: function (data) {
                       var json = JSON.parse(data);
                       if(!json.error){
                           bootbox.alert({
                               size: "medium",
                               title: lang['password_changed'],
                               message: lang['password_changed_long']
                           });
                           setTimeout(function () {
                               window.location.href = "logout";
                           }, 3000);
                       }else {
                           toast.alert("Error");
                       }
                   }
               })
           }
        });

        $('#passwordform_newpassword, #passwordform_newpassword_repeat').on('keyup', function (event) {
           var matters = $('#passwordform_newpassword').val() === $('#passwordform_newpassword_repeat').val();
           if(matters) $('#passwordform_newpassword, #passwordform_newpassword_repeat').removeClass('is-invalid').addClass('is-valid');
           else $('#passwordform_newpassword, #passwordform_newpassword_repeat').removeClass('is-valid').addClass('is-invalid');
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
