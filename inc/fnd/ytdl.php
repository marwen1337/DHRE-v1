<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 04.11.2019
 * Time: 15:43
 */

if(!$core->isUserLoggedIn()) header("Location: login");


if(!empty($_GET['action'])){
    if($_GET['action'] == "getUserVideos"){
        $stmt = $core->getPDO()->prepare("SELECT ytdl_id, ytdl_token, ytdl_title, ytdl_extension, ytdl_converted FROM ytdl WHERE ytdl_uid = :uid");
        $stmt->execute(array(":uid" => $core->getUserID()));
        $rows = array($stmt->fetchAll(PDO::FETCH_ASSOC));
        $rows['quota'] = $core->getUserData()['user_ytdlquota'];
        print_r(json_encode($rows));
        exit;
        exit;
    }else if($_GET['action'] == "addVideo"){
        $token = $_GET['vtoken'];

        $stmt = $core->getPDO()->prepare("SELECT ytdl_id FROM ytdl WHERE ytdl_uid = :uid AND ytdl_token = :token");
        $stmt->execute(array(":uid" => $core->getUserID(), ":token" => $token));
        if($stmt->rowCount() >= 1){
            print_r(json_encode(array("error" => false)));
            exit;
        }

        $stmt = $core->getPDO()->prepare("SELECT ytdl_id FROM ytdl WHERE ytdl_token = :token");
        $stmt->execute(array(":token" => $token));

        $converted = 0;
        if($stmt->rowCount() >= 1){
            $converted = 1;
        }

        $stmt = $core->getPDO()->prepare("INSERT INTO ytdl (ytdl_uid, ytdl_token, ytdl_created, ytdl_extension, ytdl_converted) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array($core->getUserID(), $token, time(), "mp3", $converted));
        print_r(json_encode(array("error" => false)));

        exit;

    }else if ($_GET['action'] == "download"){
        $stmt = $core->getPDO()->prepare("SELECT ytdl_title, ytdl_extension FROM ytdl WHERE ytdl_uid = :ytdl_uid AND ytdl_token = :ytdl_token AND ytdl_converted = true");
        $stmt->execute(array(":ytdl_uid" => $core->getUserID(), "ytdl_token" => $_GET['vtoken']));
        if($stmt->rowCount() >= 1){

            $item = $stmt->fetch();
            $fileName = $_GET['vtoken'] . "." . $item['ytdl_extension'];
            $downloadName = $item['ytdl_title'] . "." . $item["ytdl_extension"];
            $file = dirname(__FILE__) . "/../../ytdl/downloads/" . $fileName;
            echo $file;
            if(file_exists($file)){
                echo true;
                header("Cache-Control: private");
                header("Content-type: audio/mpeg3");
                header("Content-Transfer-Encoding: binary");
                header("Content-Disposition: attachment; filename=\"$downloadName\"");
                header("Content-Length: " . filesize($file));

                readfile($file);
                exit;
            }
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
    <link rel="stylesheet" href="<?=$core->getWebUrl();?>/assets/css/chart.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title>YouTube Downloader</title>

    <script>
        var lang = <?php echo json_encode($core->fullLang());?>;
    </script>
    <style>

    </style>
</head>
<body>

<?php include "navbar.php";?>

<div class="bg-light" id="loader" style="position: fixed;top: 0; left: 0;height: 100%; width: 100%;z-index: 10000000;">
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
                    <h3 class="card-title" lang="your_youtube_downloads"></h3>
                    <table class="table table-hover borderless mt-3 mb-0" id="table_ytdl">
                        <thead>
                        <tr>
                            <th scope="col" lang="video_id"></th>
                            <th scope="col" lang="video_title"></th>
                            <th scope="col" lang="format"></th>
                            <th scope="col" lang="converted"></th>
                            <th scope="col" lang="download"></th>
                        </tr>
                        </thead>
                        <tbody id="table_ytdl_body">
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col">
                            <div class="text-left">
                                <div class="row"><div class="col-12"><small class="text-warning"><i class="fa fa-info-circle"></i></small> <small lang="video_convert_info"></small></div></div>
                                <div class="row"><div class="col-12"><small class="text-warning"><i class="fa fa-info-circle"></i></small> <small lang="video_convert_betainfo"></small></div></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-right">
                                <small id="table_ytdl_count"></small>
                                <small lang="converted_videos"></small>
                                <small id="table_ytdl_quota"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card m-3" id="card_addvideo">
                <div id="form_add_accordion">
                    <div id="collapse_add_form">
                        <div class="collapse show" data-parent="#form_add_accordion">
                            <div class="card-body">
                                <h3 lang="convert_video"></h3>
                                <div class="card-text">
                                    <form id="form_add">
                                        <div class="form-group">
                                            <label for="form_add_url" lang="youtube_url"></label>
                                            <input class="form-control" type="url" id="form_add_url" name="form_add_url" placeholder="https://youtube.com/watch?v=test">
                                            <div class="valid-feedback">
                                                <span lang="url_provided_is_valid"></span>
                                            </div>
                                            <div class="invalid-feedback">
                                                <span lang="url_provided_is_invalid"></span>
                                            </div>
                                        </div>
                                        <input class="btn btn-primary w-100" type="submit" id="form_create_submit" lang="create_new_shortlink" lang_scope="value">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="collapse_add_success">
                        <div class="collapse" data-parent="#form_add_accordion">
                            <div class="card-body text-center">
                                <div class="card-text">
                                    <h3 class="card-title" lang="video_in_queue"></h3>
                                    <button class="btn btn-primary w-100" lang="continue" onclick="$('#form_add').trigger('reset');$('#form_add').find('input').removeClass('is-valid is-invalid');$('#form_add_accordion').find('.collapse:not(#collapse_add_customtoken)').collapse('show');"></button>
                                    <small lang="video_in_queue_long"></small>
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
            <div class="card m-3" id="card_downloadextension">
                <div class="card-body">
                    <h3 class="card-title" lang="download_extension"></h3>
                    <table class="table table-hover borderless mt-3 mb-0" id="table_extensions">
                        <thead>
                        <tr>
                            <th scope="col" class="col-11" lang="platform"></th>
                            <th scope="col" lang="download"></th>
                        </tr>
                        </thead>
                        <tbody id="table_extensions_body">
                        <tr>
                            <td>Chrome</td>
                            <td><a class="text-primary" href="downloads/download?file=ytdl-chrome.crx"><i class="fa fa-download"></i></button></a></td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col">
                            <div class="text-left">
                                <div class="row"><div class="col-12"><small class="text-warning"><i class="fa fa-info-circle"></i></small> <small>More Platforms will be added soon</small></div></div>
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
<script src="<?=$core->getWebUrl()?>/assets/js/jquery.fileDownload.js"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
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
            style: {}
        });

        $.urlParam = function(name, url){
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
            if (results==null) {
                return null;
            }
            return decodeURI(results[1]) || 0;
        };

        var loadVideos = function (){
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getUserVideos"},
                success: function (data) {
                    const json = JSON.parse(data);
                    var s = "";
                    $.each(json[0], function (i, item) {
                        if(item.ytdl_id != null){
                            let converted = item.ytdl_converted == true ? "<span class='text-success'><i class='fa fa-check'></i></span>" : "<span class='text-danger'><i class='fa fa-times'></i></span>";
                            s += '<tr id="table_ytdl_content_' + item.ytdl_id + '"><td><a target="_blank" href="https://youtube.com/watch?v=' + item.ytdl_token + '">' + item.ytdl_token + '</a></td><td>' + item.ytdl_title + '</td><td>' + item.ytdl_extension + '</td><td>' + converted + '</td><td><a class="ytdl-download" target="_blank" href="?action=download&vtoken=' + item.ytdl_token + '"><i class="fa fa-download"></i></a></td></tr>';
                        }
                    });
                    $('#table_ytdl_body').html(s).collapse('show');
                    $('#table_ytdl_count').html(json[0].length);
                    var quota = json.quota >= 0 ? json.quota : "Unlimited" ;
                    $('#table_ytdl_quota').html("(Quota: " + quota + ")");
                    doLanguage();
                }
            });
        };

        console.log();

        $('#form_add').on('submit', function (event) {
            event.preventDefault();

            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "addVideo", "vtoken": $.urlParam("v", $('#form_add_url').val())},
                success: function (data) {
                    const json = JSON.parse(data);
                    if(!json.error){
                        $('#collapse_add_success').find('.collapse').collapse('show');
                        loadVideos();
                    }else {
                        toast.alert("Error!");
                    }
                }
            });
        });

        loadVideos();

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
