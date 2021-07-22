<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 12.02.2020
 * Time: 21:11
 */

if(!$core->isUserLoggedIn()) header("Location: login");

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
    <title lang="manage_startpage"></title>

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
                    <h3 class="card-title" lang="startpage_title"></h3>
                    <span class="card-subtitle" lang="startpage_title_long"></span>
                    <div class="card-text mt-3">
                        <form id="title_change_form">
                            <div class="form-group">
                                <input class="form-control" type="password" id="startpage_titleform_title" name="startpage_titleform_title" lang="startpage_title" lang_scope="placeholder" required>
                            </div>
                            <input class="btn btn-primary w-100" type="submit" id="startpage_titleform_submit" lang="change_title" lang_scope="value">
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
                    <h3 class="card-title" lang="startpage_items"></h3>
                    <span class="card-subtitle" lang="startpage_items_long"></span>
                    <div class="card-text">
                        <form id="icons_change_form">
                            <div class="currentIcons accordion" id="accordionExample">
                                <div class="card" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="box-shadow: none !important;">

                                    <div class="card-body">
                                        <div class="card-title mb-0" id="headingOne">
                                            <div class="row">
                                                <div class="col-11">
                                                    <h2>Test</h2>
                                                </div>
                                                <div class="col">
                                                    <button class="btn btn-success" type="button"><i class="fa fa-arrow-down"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                                            <div class="card-text">
                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center"><button class="btn btn-primary m-3" type="button"><i class="fa fa-plus"></i></button></div>
                            <input class="btn btn-primary w-100" type="submit" id="startpage_itemsform_submit" lang="change_items" lang_scope="value">
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

