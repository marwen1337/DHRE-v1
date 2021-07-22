<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 08.08.2019
 * Time: 15:52
 */

if(!$core->isUserLoggedIn()) header("Location: login");

if(!empty($_POST)){

    if(!empty($_POST['form_create_targeturl'])){
        if($core->getUserData()['user_shortquota'] >= 0){
            $stmt = $core->getPDO()->prepare("SELECT short_id FROM shortlinks WHERE short_creator = :user_id");
            $stmt->execute(array(":user_id" => $core->getUserID()));
            $shortlinks_count = $stmt->rowCount();
            if($core->getUserData()['user_shortquota'] <= $shortlinks_count){
                $core->printError("shortlink_quota_reached");
                exit;
            }
        }

        $short_target = $_POST['form_create_targeturl'];
        $data = array("token" => 0);
        while (true){
            $rnd = $core->randomString(5);
            $stmt = $core->getPDO()->prepare("SELECT short_id FROM shortlinks WHERE short_token = :short_token");
            $stmt->execute(array(":short_token" => $rnd));
            if($stmt->rowCount() == 0){
                $data['token'] = $rnd;
                break;
            }
        }
        $short_token = $data['token'];

        if(!empty($_POST['form_create_customtoken_customtoken']) && $core->getUserData()['user_customtoken']) {
            $custom_token = $_POST['form_create_customtoken_customtoken'];
            $stmt = $core->getPDO()->prepare("SELECT short_id FROM shortlinks WHERE short_token = :custom_token");
            $stmt->execute(array(":custom_token" => $custom_token));
            if ($stmt->rowCount() == 0) {
                $short_token = $custom_token;
            } else {
                $core->printError("custom_shortlink_in_use");
                exit;
            }
        }

        $stmt = $core->getPDO()->prepare("INSERT INTO shortlinks (short_creator, short_token, short_targeturl, short_created, short_stats) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array($core->getUserID(), $short_token, $short_target, time(), '{"' . date("Y-m-d") . '":{"views":0}}'));
        print_r(json_encode(array("error" => false)));
        exit;
    }else if(!empty($_FILES['form_upload_file'])){
        if($core->getUserData()['user_imgquota'] >= 0){
            $stmt = $core->getPDO()->prepare("SELECT img_id FROM images WHERE img_creator = :user_id");
            $stmt->execute(array(":user_id" => $core->getUserID()));
            $shortlinks_count = $stmt->rowCount();
            if($core->getUserData()['user_imgquota'] <= $shortlinks_count){
                $core->printError("image_quota_reached");
                exit;
            }
        }

        $data = array("token" => 0);
        while (true){
            $rnd = $core->randomString(5);
            $stmt = $core->getPDO()->prepare("SELECT img_id FROM images WHERE img_token = :img_token");
            $stmt->execute(array(":img_token" => $rnd));
            if($stmt->rowCount() == 0){
                $data['token'] = $rnd;
                break;
            }
        }
        $img_token = $data['token'];

        if(!empty($_POST['form_upload_customtoken_customtoken']) && $core->getUserData()['user_customtoken']) {
            $custom_token = $_POST['form_upload_customtoken_customtoken'];
            $stmt = $core->getPDO()->prepare("SELECT img_id FROM images WHERE img_token = :custom_token");
            $stmt->execute(array(":custom_token" => $custom_token));
            if ($stmt->rowCount() == 0) {
                $img_token = $custom_token;
            } else {
                $core->printError("custom_image_in_use");
                exit;
            }
        }

        $file = $core->getRootDir() . "/images/" . $img_token . "." . pathinfo($_FILES['form_upload_file']['name'], PATHINFO_EXTENSION);
        $filetype = pathinfo($file, PATHINFO_EXTENSION);

        $valid_extensions = array("jpg", "jpeg" ,"png", "gif");
        /* Check file extension */
        if(!in_array(strtolower($filetype),$valid_extensions)) {
            $core->printError("wrong_file_type");
            exit;
        }

        if(move_uploaded_file($_FILES['form_upload_file']['tmp_name'], $file)){
            chmod($file, 0777);

            if(in_array($filetype, array('jpg', 'jpeg', 'png'))){
                $core->correctImageOrientation($file);
                switch (pathinfo($file, PATHINFO_EXTENSION)){
                    case 'jpeg':
                    case 'jpg':
                        $image = imagecreatefromjpeg($file);
                        break;
                    case 'png':
                        $image = imagecreatefrompng($file);
                        break;
                }

                /* Watermark
                 * if(imagesx($image) > imagesy($image)){
                    $watermark_s = imagesy($image) * 0.2;
                }else{
                    $watermark_s = imagesx($image) * 0.2;
                }

                $watermark = imagecreatefrompng($core->getRootDir() . "/images/watermark.png");
                $watermark = imagescale($watermark, $watermark_s);
                $mg_right = 10;
                $mg_bottom = 10;
                $watermark_h = imagesy($watermark);
                $watermark_w = imagesx($watermark);

                imagecopy($image, $watermark, imagesx($image) - $watermark_w - $mg_right, imagesy($image) - $watermark_w - $mg_bottom, 0, 0, imagesx($watermark), imagesy($watermark));
                imagepng($image, $file);*/
            }

            $stmt = $core->getPDO()->prepare("INSERT INTO images (img_creator, img_token, img_file, img_created) VALUES (?, ?, ?, ?)");
            $stmt->execute(array($core->getUserID(), $img_token, $file, time()));
            echo json_encode(array("error" => false));
            exit;
        }else{
            $core->printError("file_write_error");
            exit;
        }
    }else if($_POST['action'] == "editShortlink"){
        $sid = $_POST['sid'];
        $password = strlen($_POST['password']) == 0 ? "" : $core->hash($_POST['password']);

        $stmt = $core->getPDO()->prepare("UPDATE shortlinks set short_password = :password WHERE short_id = :sid");
        $stmt->execute(array(":password" => $password, ":sid" => $sid));
        print_r(json_encode(array("error" => false)));
        exit;
    }

}

if(!empty($_GET['action'])){
    if($_GET['action'] == "getShortlinks"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM shortlinks WHERE short_creator = :user_id");
        $stmt->execute(array(":user_id" => $core->getUserID()));
        $rows = array($stmt->fetchAll(PDO::FETCH_ASSOC));
        $rows['quota'] = $core->getUserData()['user_shortquota'];
        print_r(json_encode($rows));
        exit;
    }else if($_GET['action'] == "checkToken"){
        $stmt = $core->getPDO()->prepare("SELECT short_id FROM shortlinks WHERE short_token = :custom_token");
        $stmt->execute(array(":custom_token" => $_GET['token']));
        print_r(json_encode(array("error" => false, "available" => ($stmt->rowCount() == 0 ? true : false))));
        exit;
    }else if($_GET['action'] == "getShortlinkInfo"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM shortlinks WHERE short_creator = :user_id AND short_id = :short_id");
        $stmt->execute(array(":user_id" => $core->getUserID(), ":short_id" => $_GET['sid']));
        if($stmt->rowCount() == 1){
            print_r(json_encode($stmt->fetch()));
            exit;
        }else{
            $core->printError("no_shortlink_found");
            exit;
        }
    }else if($_GET['action'] == "getExternLogin"){
        $stmt = $core->getPDO()->prepare("SELECT login_token, login_pass FROM autologin WHERE login_owner = :user_id AND login_type = 'ext'");
        $stmt->execute(array(":user_id" => $core->getUserID()));
        if($stmt->rowCount() == 0){
            $data = array("token" => 0, "pass" => 0);
            while (true){
                $data['token'] = $core->randomString(32);
                $data['pass'] = $core->randomString();
                $stmt = $core->getPDO()->prepare("SELECT login_id FROM autologin WHERE login_token = :autologin_token");
                $stmt->execute(array(":autologin_token" => $data['token']));
                $rowEmpty = $stmt->rowCount() == 0;
                if($rowEmpty){
                    $stmt = $core->getPDO()->prepare("INSERT INTO autologin (login_owner, login_token, login_pass, login_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute(array($core->getUserID(), $data['token'], $data['pass'], "ext"));
                    break;
                }
            }
            print_r(json_encode(array("token" => $data['token'], "pass" => $data['pass'])));
            exit;
        }else{
            $row = $stmt->fetch();
            print_r(json_encode(array("token" => $row['login_token'], "pass" => $row['login_pass'])));
            exit;
        }
    }else if($_GET['action'] == "deleteShortlink"){
        $stmt = $core->getPDO()->prepare("DELETE FROM shortlinks WHERE short_id = :short_id AND short_creator = :user_id");
        $stmt->execute(array(":short_id" => $_GET['sid'], ":user_id" => $core->getUserID()));
        $stmt = $core->getPDO()->prepare("DELETE FROM stats WHERE stats_sid = :short_id");
        $stmt->execute(array(":short_id" => $_GET['sid']));
        print_r(json_encode(array("error" => false, "sid" => $_GET['sid'], "uid" => $core->getUserID())));
        exit;
    }else if($_GET['action'] == "getShortlinkStats"){
        $stmt = $core->getPDO()->prepare("SELECT short_id, short_token, short_stats FROM shortlinks WHERE short_id = :short_id AND short_creator = :user_id");
        $stmt->execute(array(":short_id" => $_GET['sid'], ":user_id" => $core->getUserID()));
        $data = $stmt->fetch();
        $data['short_stats'] = json_decode($data['short_stats']);
        print_r(json_encode(array("error" => false, "data" => $data)));
        exit;
    }else if($_GET['action'] == "getImages"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM images WHERE img_creator = :user_id");
        $stmt->execute(array(":user_id" => $core->getUserID()));
        $rows = array($stmt->fetchAll(PDO::FETCH_ASSOC));
        $rows['quota'] = $core->getUserData()['user_imgquota'];
        print_r(json_encode($rows));
        exit;
    }else if($_GET['action'] == "deleteImage"){
        $stmt = $core->getPDO()->prepare("SELECT img_file FROM images WHERE img_id = :img_id AND img_creator = :user_id");
        $stmt->execute(array(":img_id" => $_GET['iid'], ":user_id" => $core->getUserID()));
        $file = $stmt->fetch()['img_file'];
        $stmt = $core->getPDO()->prepare("DELETE FROM images WHERE img_id = :img_id AND img_creator = :user_id");
        $stmt->execute(array(":img_id" => $_GET['iid'], ":user_id" => $core->getUserID()));
        unlink($file);
        print_r(json_encode(array("error" => false, "iid" => $_GET['iid'], "uid" => $core->getUserID())));
        exit;
    }else if($_GET['action'] == "checkImageToken"){
        $stmt = $core->getPDO()->prepare("SELECT img_id FROM images WHERE img_token = :custom_token");
        $stmt->execute(array(":custom_token" => $_GET['token']));
        print_r(json_encode(array("error" => false, "available" => ($stmt->rowCount() == 0 ? true : false))));
        exit;
    }else if($_GET['action'] == "getImageInfo"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM images WHERE img_creator = :user_id AND img_id = :img_id");
        $stmt->execute(array(":user_id" => $core->getUserID(), ":img_id" => $_GET['iid']));
        if($stmt->rowCount() == 1){
            print_r(json_encode($stmt->fetch()));
            exit;
        }else{
            $core->printError("no_image_found");
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
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/fontawesome/css/all.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/styles.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/siiimpletoast.css">
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>assets/css/chart.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="dashboard"></title>

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
                    <h3 lang="your_shortened_links"></h3>
                    <div class="card-text">
                        <table class="table table-hover borderless mt-3 mb-0" id="table_shortlinks" style="word-wrap: break-word;">
                            <thead>
                            <tr>
                                <th scope="col" lang="short_url"></th>
                                <th scope="col" lang="target_url"></th>
                                <th scope="col" lang="created"></th>
                                <th scope="col" lang="info"></th>
                            </tr>
                            </thead>
                            <tbody class="collapse" id="table_shortlinks_body">

                            </tbody>
                        </table>

                        <div class="text-center m-3" id="preloader_shortlinks">
                            <span class="spinner-grow text-primary"></span>
                        </div>

                        <?php
                        $stmt = $core->getPDO()->prepare("SELECT COUNT(short_id) as count_total FROM shortlinks WHERE short_creator = :user_id");
                        $stmt->execute(array(":user_id" => $core->getUserID()));
                        $shortlinks_count = $stmt->fetch()['count_total'];
                        ?>
                        <div class="text-right">
                            <small id="table_shortlinks_count"></small>
                            <small lang="shortened_links"></small>
                            <small id="table_shortlinks_quota"></small>
                        </div>
                        <button class="btn btn-primary w-100 mt-3" lang="create_new_shortlink" onclick="$('html,body').animate({scrollTop: $('#card_createshortlink').offset().top}, 200, function() {$('#form_create_targeturl').focus();});"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3" id="card_createshortlink">
                <div id="form_create_accordion">
                    <div id="collapse_create_form">
                        <div class="collapse show" data-parent="#form_create_accordion">
                            <div class="card-body">
                                <h3 lang="create_new_shortlink"></h3>
                                <div class="card-text">
                                    <form id="form_create">
                                        <div class="form-group">
                                            <label for="form_create_targeturl" lang="target_url"></label>
                                            <input class="form-control" type="text" id="form_create_targeturl" name="form_create_targeturl" placeholder="https://example.com">
                                            <div class="valid-feedback">
                                                <span lang="url_provided_is_valid"></span>
                                            </div>
                                            <div class="invalid-feedback">
                                                <span lang="url_provided_is_invalid"></span>
                                            </div>
                                        </div>
                                        <?php if($core->getUserData()['user_customtoken']){ ?>
                                        <button class="btn btn-info mb-3 w-100" type="button" data-toggle="collapse" data-target="#collapse_create_customtoken" aria-expanded="false" aria-controls="collapse_create_customtoken" lang="use_customtoken"></button>
                                        <div class="collapse" id="collapse_create_customtoken">
                                            <div class="form-group">
                                                <label for="form_create_customtoken_customtoken" lang="customtoken"></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><?= $core->getWebUrl() ?></span>
                                                    </div>
                                                    <input class="form-control" type="text" id="form_create_customtoken_customtoken" name="form_create_customtoken_customtoken">
                                                    <div class="valid-feedback">
                                                        <span lang="shortlink_is_available"></span>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        <span lang="shortlink_is_forgiven"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            <?php } ?>
                                        <input class="btn btn-primary w-100" type="submit" id="form_create_submit" lang="create_new_shortlink" lang_scope="value">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="collapse_create_success">
                        <div class="collapse" data-parent="#form_create_accordion">
                            <div class="card-body text-center">
                                <div class="card-text">
                                    <h3 class="card-title" lang="shortlink_created"></h3>
                                    <button class="btn btn-primary w-100" lang="continue" onclick="$('#form_create').trigger('reset');$('#form_create').find('input').removeClass('is-valid is-invalid');$('#form_create_accordion').find('.collapse:not(#collapse_create_customtoken)').collapse('show');"></button>
                                    <small lang="shortlink_created_long"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3">
                <div class="card-body">
                    <h3 lang="your_uploaded_images"></h3>
                    <div class="card-text">
                        <table class="table table-hover mt-3 mb-0" id="table_images" style="word-wrap: break-word;">
                            <thead>
                            <tr>
                                <th scope="col" lang="img_url"></th>
                                <th scope="col" lang="created"></th>
                                <th scope="col" lang="info"></th>
                            </tr>
                            </thead>
                            <tbody class="collapse" id="table_images_body">

                            </tbody>
                        </table>

                        <div class="text-center m-3" id="preloader_images">
                            <span class="spinner-grow text-primary"></span>
                        </div>

                        <?php
                        $stmt = $core->getPDO()->prepare("SELECT COUNT(img_id) as count_total FROM images WHERE img_creator = :user_id");
                        $stmt->execute(array(":user_id" => $core->getUserID()));
                        $shortlinks_count = $stmt->fetch()['count_total'];
                        ?>
                        <div class="text-right">
                            <small id="table_images_count"></small>
                            <small lang="uploaded_images"></small>
                            <small id="table_images_quota"></small>
                        </div>
                        <button class="btn btn-primary w-100 mt-3" lang="upload_image" onclick="$('html,body').animate({scrollTop: $('#card_uploadimage').offset().top}, 200, function() {$('#form_upload_image').trigger('click');});"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3" id="card_uploadimage">
                <div id="form_upload_accordion">
                    <div id="collapse_upload_form">
                        <div class="collapse show" data-parent="#form_upload_accordion">
                            <div class="card-body">
                                <h3 lang="upload_new_image"></h3>
                                <div class="card-text">
                                    <form id="form_upload">
                                        <div class="custom-file mb-3">
                                            <input type="file" class="custom-file-input" id="form_upload_file" name="form_upload_file" required>
                                            <label class="custom-file-label" for="form_upload_file">Choose file</label>
                                            <div class="valid-feedback">
                                                <span lang="file_provided_is_valid"></span>
                                            </div>
                                            <div class="invalid-feedback">
                                                <span lang="file_provided_is_invalid"></span>
                                            </div>
                                        </div>
                                        <?php if($core->getUserData()['user_customtoken']){ ?>
                                            <button class="btn btn-info mb-3 w-100" type="button" data-toggle="collapse" data-target="#collapse_upload_customtoken" aria-expanded="false" aria-controls="collapse_create_customtoken" lang="use_customtoken"></button>
                                            <div class="collapse" id="collapse_upload_customtoken">
                                                <div class="form-group">
                                                    <label for="form_upload_customtoken_customtoken" lang="customtoken"></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><?= $core->getWebUrl() ?>img/</span>
                                                        </div>
                                                        <input class="form-control" type="text" id="form_upload_customtoken_customtoken" name="form_upload_customtoken_customtoken">
                                                        <div class="valid-feedback">
                                                            <span lang="shortlink_is_available"></span>
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            <span lang="shortlink_is_forgiven"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <input class="btn btn-primary w-100" type="submit" id="form_upload_submit" lang="upload_new_image" lang_scope="value">
                                        <div class="progress mt-3 mb-3">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="form_upload_progress" role="progressbar" style="width: 0;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="collapse_create_success">
                        <div class="collapse" data-parent="#form_upload_accordion">
                            <div class="card-body text-center">
                                <div class="card-text">
                                    <h3 class="card-title" lang="image_uploaded"></h3>
                                    <button class="btn btn-primary w-100" lang="continue" onclick="$('#form_upload').trigger('reset');$('#form_upload').find('input').removeClass('is-valid is-invalid');$('#form_upload_accordion').find('.collapse:not(#collapse_upload_customtoken)').collapse('show');"></button>
                                    <small lang="image_uploaded_long"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3">
                <div class="card-body">
                    <h3 class="card-title" lang="ext_use"></h3>
                    <span class="card-subtitle" lang="ext_use_long"></span>
                    <div class="card-text mt-3">
                        <h4 class="text-center mb-3" lang="how_to_use_ext_api"></h4>
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col m-3" id="ext_use_step1">
                                        <h5 lang="how_to_use_ext_api_explain_title1"></h5>
                                        <p lang="how_to_use_ext_api_explain_step1"></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col m-3" id="ext_use_step3">
                                        <h5 lang="how_to_use_ext_api_explain_title3"></h5>
                                        <p lang="how_to_use_ext_api_explain_step3"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col m-3" id="ext_use_step2">
                                <h5 lang="how_to_use_ext_api_explain_title2"></h5>
                                <p lang="how_to_use_ext_api_explain_step2"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3 mb-5">
                <div class="card-body">
                    <h3 class="card-title" lang="manage_account"></h3>
                    <span class="card-subtitle" lang="manage_account_long"></span>
                    <div class="card-text mt-3">
                        <a class="stretched-link" href="manageAccount"><button class="btn btn-primary w-100" lang="manage_account"></button></a>
                    </div>
                </div>
            </div>
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

        var loadShortlinks = function (){
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getShortlinks"},
                success: function (data) {
                    var json = JSON.parse(data);
                    var s = "";
                    $.each(json[0], function (i, item) {
                        if(item.short_id != null){
                            //TODO: Info Collapse fertig machen, dass diese generiert werden.
                            s += '<tr id="table_shortlinks_content_' + item.short_id + '"><td><a class="text-break" href="' + softwareURL + item.short_token + '" target="_blank">' + softwareURL + item.short_token + '</a></td><td class="text-break">' + item.short_targeturl + '</td><td id="table_shortlinks_content_' + item.short_id + '">' + moment.unix(item.short_created).fromNow() + '</td><td class="openShortlinkInfo" data-id="' + item.short_id + '"><div class="dropdown"><i class="dropdown-toggle fa fa-cog text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i><div class="dropdown-menu"><div class="dropdown-item shortlink-edit" data-sid="' + item.short_id + '"><span class="fa fa-edit"></span>&nbsp;<span lang="edit"></span></div><div class="dropdown-item shortlink-stats" data-sid="' + item.short_id + '"><span class="fa fa-chart-bar"></span>&nbsp;<span lang="statistic"></span></div><div class="dropdown-item text-danger shortlink-delete" data-sid="' + item.short_id + '"><span class="fa fa-trash"></span>&nbsp;<span lang="delete"></span></div></div></div></td></tr>';
                        }
                    });
                    $('#table_shortlinks_body').html(s).collapse('show');
                    $('#table_shortlinks_count').html(json[0].length);
                    var quota = json.quota >= 0 ? json.quota : "Unlimited" ;
                    $('#table_shortlinks_quota').html("(Quota: " + quota + ")");
                    $('#preloader_shortlinks').hide();
                    doLanguage();
                }
            });
        };

        var loadImages = function (){
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getImages"},
                success: function (data) {
                    var json = JSON.parse(data);
                    var s = "";
                    $.each(json[0], function (i, item) {
                        if(item.img_id != null){
                            s += '<tr id="table_images_content_' + item.img_id + '"><td><a class="text-break" href="' + softwareURL + "img/" + item.img_token + '" target="_blank">' + softwareURL + "img/" + item.img_token + '</a></td><td id="table_images_content_' + item.img_id + '">' + moment.unix(item.img_created).fromNow() + '</td><td class="openImageInfo" data-id="' + item.img_id + '"><div class="dropdown"><i class="dropdown-toggle fa fa-cog text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i><div class="dropdown-menu"><div class="dropdown-item text-danger image-delete" data-iid="' + item.img_id + '"><span class="fa fa-trash"></span>&nbsp;<span lang="delete"></span></div></div></div></td></tr>';
                        }
                    });
                    $('#table_images_body').html(s).collapse('show');
                    $('#table_images_count').html(json[0].length);
                    var quota = json.quota >= 0 ? json.quota : "Unlimited" ;
                    $('#table_images_quota').html("(Quota: " + quota + ")");
                    $('#preloader_images').hide();
                    doLanguage();
                }
            });
        };

        loadShortlinks();
        loadImages();


        $.ajax({
           url: window.location,
           type: 'get',
           data: {action: "getExternLogin"},
           success: function (data) {
               var json = JSON.parse(data);
               if(!json.error){
                   $('#ext_use_step2').find("p").html($('#ext_use_step2').find("p").html().replace("%url%", softwareURL + "app/ext?action=createShortlink&token=" + json.token + "&pass=" + json.pass));
               }
           }
        });


        $('#form_create').on('submit', function (event) {
            event.preventDefault();

            var targeturl = $('#form_create_targeturl');

            if(targeturl.hasClass('is-valid')){

                $.ajax({
                    url: window.location,
                    type: 'post',
                    data: $(this).serialize(),
                    success: function (data) {
                        var json = JSON.parse(data);
                        if(!json.error){
                            loadShortlinks();
                            toast.success(lang['shortlink_created']);
                            $("#form_create_accordion").find('.collapse:not(#collapse_create_customtoken)').collapse('show');
                        }else {
                            if(json.reason === "custom_shortlink_in_use"){
                                $('#form_create_customtoken_customtoken').addClass("is-invalid");
                            }else if(json.reason === "shortlink_quota_reached"){
                                toast.alert(lang['shortquota_reached']);
                            }
                        }
                    },
                    error: function (data) {
                        alert("Error");
                    }
                });
            }else targeturl.addClass('is-invalid');
        });

        $('#form_create_targeturl').on('input', function (event) {
            var valid = validURL($(this).val());
            if(!valid) $(this).addClass('is-invalid').removeClass('is-valid');
            else $(this).addClass('is-valid').removeClass('is-invalid');
        });

        $('#form_create_customtoken_customtoken').on('input', function (event) {
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "checkToken", "token": $(this).val()},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        if(json.available){
                            $('#form_create_customtoken_customtoken').removeClass('is-invalid').addClass('is-valid');
                        }else if(!json.available){
                            $('#form_create_customtoken_customtoken').removeClass('is-valid').addClass('is-invalid');
                        }
                    }
                }
            })
        });

        $('#table_shortlinks').on('click', '.shortlink-delete', function () {
            $this = $(this);

            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getShortlinkInfo", sid: $(this).attr("data-sid")},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        bootbox.confirm({
                            title: lang['delete_shortlink'],
                            message: lang['delete_shortlink_long'].replace("%s", softwareURL + json.short_token),
                            buttons: {
                                cancel: {
                                    label: '<i class="fa fa-times"></i> ' + lang['cancel'],
                                    className: 'btn-noborder'
                                },
                                confirm: {
                                    label: '<i class="fa fa-trash"></i> ' + lang['delete'],
                                    className: 'btn-danger'
                                }
                            },
                            callback: function(result){
                                if(result){
                                    $.ajax({
                                        url: window.location,
                                        type: 'get',
                                        data: {action: "deleteShortlink", sid: $this.attr("data-sid")},
                                        success: function (data) {
                                            var json = JSON.parse(data);
                                            if(!json.error){
                                                loadShortlinks();
                                                toast.success(lang['shortlink_deleted']);
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            });
        });

        $('#table_shortlinks').on('click', '.shortlink-edit', function () {

            var sid = $(this).attr("data-sid");

            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getShortlinkInfo", sid: sid},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        bootbox.dialog({
                            size: "large",
                            title: lang['edit_shortlink'] + " (" + softwareURL + json.short_token + ")",
                            message: '<div><input class="form-control" id="edit_shortlink_password" type="password" placeholder="Password Protection"></div>',
                            buttons: {
                                cancel: {
                                    label: "<i class='fa fa-times'></i> " + lang['cancel'],
                                    className: "btn-danger"
                                },
                                save: {
                                    label: "<i class='fa fa-check'></i> " + lang['save'],
                                    className: "btn-success",
                                    callback: function () {
                                        $.ajax({
                                            url: window.location,
                                            type: 'post',
                                            data: {action: "editShortlink", sid: sid, password: $("#edit_shortlink_password").val()},
                                            success: function (data) {
                                                if(!json.error){
                                                    toast.success(lang['changes_were_saved']);
                                                }
                                            }
                                        })
                                    }
                                }
                            }
                        });
                    }
                }
            });
        });

        $('#table_shortlinks').on('click', '.shortlink-stats', function () {
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getShortlinkStats", sid: $(this).attr("data-sid")},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        bootbox.alert({
                            size: "large",
                            title: lang['statistic'] + " (" + softwareURL + json.data.short_token + ")",
                            message: '<canvas class="shortlink_stats_chart" id="shortlink_stats_chart-' + json.data.short_id + '" width="400" height="400"></canvas>'
                        });

                        var labels = [];
                        var values = [];
                        //Split into labels and values
                        $.each(json.data.short_stats, function (key, value) {
                            labels.push(key);
                            values.push(value.views);
                        });

                        var chart = new Chart($('#shortlink_stats_chart-' + json.data.short_id)[0], {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Clicks',
                                    data: values,
                                    backgroundColor: [
                                        'rgba(247, 218, 100, 0.3)'
                                    ],
                                    borderColor: [
                                        'rgba(0, 0, 0, 0.2)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                    }
                }
            });
        });

        $('#form_upload').on('submit', function (event) {
            event.preventDefault();
            var formdata = new FormData();
            formdata.append('form_upload_file', $('#form_upload_file')[0].files[0]);
            formdata.append('form_upload_customtoken_customtoken', $('#form_upload_customtoken_customtoken').val());
            uploadImage(formdata);
        });

        function uploadImage(formdata){

            $.ajax({
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = Math.round(percentComplete * 100) + '%';
                            $('#form_upload_progress').css({
                                width: percentComplete
                            }).html(percentComplete);
                        }
                    }, false);
                    xhr.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = Math.round(percentComplete * 100) + '%';
                            $('#form_upload_progress').css({
                                width: percentComplete
                            }).html(percentComplete);
                        }
                    }, false);
                    return xhr;
                },
                url: window.location,
                type: 'post',
                data: formdata,
                contentType: false,
                processData: false,
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        loadImages();
                        toast.success(lang['image_uploaded']);
                        $("#form_upload_accordion").find('.collapse:not(#collapse_upload_customtoken)').collapse('show');
                    }else {
                        if(json.reason === "custom_image_in_use"){
                            $('#form_upload_customtoken_customtoken').addClass("is-invalid");
                        }else if(json.reason === "image_quota_reached"){
                            toast.alert(lang['imagequota_reached']);
                        }else if(json.reason === "wrong_file_type"){
                            toast.alert(lang['wrong_filetype']);
                        }else if(json.reason === "file_write_error"){
                            toast.alert("Error! Please contact Admin!");
                        }
                    }
                    $('#form_upload_progress').css("width", "0%").html("");
                },
                error: function (data) {
                    alert("Error");
                }
            });
        }

        $('#form_upload_file').on('change', function (event) {
            if($(this)[0].files[0] == null){
                $(this).parent().find('label').html("Choose File");
                $(this).addClass('is-invalid').removeClass('is-valid');
            }else{
                $(this).parent().find('label').html($(this)[0].files[0].name);
                $(this).addClass('is-valid').removeClass('is-invalid');
            }
        });

        $('#form_upload_customtoken_customtoken').on('input', function (event) {
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "checkImageToken", "token": $(this).val()},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        if(json.available){
                            $('#form_upload_customtoken_customtoken').removeClass('is-invalid').addClass('is-valid');
                        }else if(!json.available){
                            $('#form_upload_customtoken_customtoken').removeClass('is-valid').addClass('is-invalid');
                        }
                    }
                }
            })
        });

        $('#card_uploadimage').on('dragenter dragover', function (e) {
            e.stopPropagation();
            e.preventDefault();
        });

        $('#card_uploadimage').on('drop', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var file = e.originalEvent.dataTransfer.files;
            var formdata = new FormData();
            formdata.append('form_upload_file', file[0]);
            formdata.append('form_upload_customtoken_customtoken', $('#form_upload_customtoken_customtoken').val());
            uploadImage(formdata);
        });

        $('#table_images').on('click', '.image-delete', function () {
            $this = $(this);

            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getImageInfo", iid: $(this).attr("data-iid")},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        bootbox.confirm({
                            title: lang['delete_image'],
                            message: lang['delete_image_long'].replace("%s", softwareURL + "img/" + json.img_token),
                            buttons: {
                                cancel: {
                                    label: '<i class="fa fa-times"></i> ' + lang['cancel'],
                                    className: 'btn-noborder'
                                },
                                confirm: {
                                    label: '<i class="fa fa-trash"></i> ' + lang['delete'],
                                    className: 'btn-danger'
                                }
                            },
                            callback: function(result){
                                if(result){
                                    $.ajax({
                                        url: window.location,
                                        type: 'get',
                                        data: {action: "deleteImage", iid: $this.attr("data-iid")},
                                        success: function (data) {
                                            var json = JSON.parse(data);
                                            if(!json.error){
                                                loadImages();
                                                toast.success(lang['image_deleted']);
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            });
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
