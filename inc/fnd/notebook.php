<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 04.11.2019
 * Time: 15:43
 */

if(!$core->isUserLoggedIn()) header("Location: login");


if(!empty($_POST)){
    $nid = $_POST['nid'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $core->getPDO()->prepare("UPDATE notebook SET note_title = :note_title, note_content = :note_content, note_lastedit = :note_lastedit WHERE note_id = :note_id AND note_creator = :user_id");
    $stmt->execute(array(":note_title" => $title, ":note_content" => $content, ":note_lastedit" => time(), ":note_id" => $nid, ":user_id" => $core->getUserID()));
    print_r(json_encode(array("error" => false)));
    exit;
}

if(!empty($_GET['action'])){
    if($_GET['action'] == "getNotes"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM notebook WHERE note_creator = :user_id ORDER BY note_lastedit DESC");
        $stmt->execute(array(":user_id" => $core->getUserID()));
        $rows = array($stmt->fetchAll(PDO::FETCH_ASSOC));
        print_r(json_encode(array('error' => false, 'data' => $rows)));
        exit;
    }else if($_GET['action'] == "getNote"){
        if(!empty($_GET['nid'])){
            $stmt = $core->getPDO()->prepare("SELECT * FROM notebook WHERE note_creator = :user_id AND note_id = :note_id");
            $stmt->execute(array(":user_id" => $core->getUserID(), ":note_id" => $_GET['nid']));
            if($stmt->rowCount() == 1){
                print_r(json_encode(array("error" => false, "data" => $stmt->fetch())));
                exit;
            }else{
                $core->printError("no_note_found");
                exit;
            }
        }
    }else if($_GET['action'] == "createNote"){
        $stmt = $core->getPDO()->prepare("INSERT INTO notebook (note_creator, note_title, note_content, note_lastedit) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($core->getUserID(), "", "", time()));
        print_r(json_encode(array("error" => false)));
        exit;
    }else if($_GET['action'] == "deleteNote"){
        $stmt = $core->getPDO()->prepare("DELETE FROM notebook WHERE note_id = :note_id AND note_creator = :user_id");
        $stmt->execute(array(":note_id" => $_GET['nid'], ":user_id" => $core->getUserID()));
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">
    <link rel="shortcut icon" href="https://files.slowloris.de/images/favicon/favicon.ico">
    <title lang="notes"></title>

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

<div class="container-fluid h-100">
    <div class="row ml-3 mr-3 h-100 context" id="notebook_wrapper">
        <div class="col-12">
            <div class="row h-100 justify-content-center align-items-center" id="editfield_nothingopened">
                <div class="col-12 text-center">
                    <h3 class="text-muted" lang="no_notes_found" style="user-select:none;"></h3>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
<script>

    $(document).ready(function (event) {

        var softwareURL = '<?=$core->getWebUrl();?>';
        var autosave_timer;

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

        $.contextMenu({
            selector: '#notebook_wrapper',
            items: {
                "create_note": {
                    name: "Create",
                    icon: "fas fa-plus-circle",
                    callback: function (itemKey, opt, e) {
                        console.log(itemKey);
                    }
                }
            }
        });

        var loadNotes = function () {
            var nid = $('#list_listgroup').find('.active').attr('nid');
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "getNotes"},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        if(json.data == ""){

                        }else {
                            $('#list_loader').hide();
                            var s = "";
                            $.each(json.data[0], function (i, item) {
                                var content = item.note_content.replace(/<[^>]*>?/gm, '').substr(0, 50);
                                var contentDots = content.length == 50 ? "..." : "";
                                s += '<div class="list-group-item list-group-item-action" nid="' + item.note_id + '" style="user-select:none;"><div class="d-flex w-100 justify-content-between"> <h5 class="mb-1">' + item.note_title + '</h5> <small>' + moment.unix(item.note_lastedit).fromNow() + '</small> </div> <p class="mb-1 content">' + content + contentDots + '</p></div>';
                            });
                            $('#list_listgroup').html(s);
                            $("#list_listgroup").find("[nid='" + nid + "']").addClass('active');
                        }

                    }else {
                        toast.alert("Error");
                    }
                }
            });
        };

        loadNotes();

        $('#list_searchform').on('submit', function (event) {
            event.preventDefault();
        });

        $('#list_search').on('keyup', function (event) {
            var query = $(this).val();
            if(query !== ""){
                $('#list_listgroup .list-group-item').hide();
                $('#list_listgroup .list-group-item').each(function () {
                    var keyword_title = $(this).find("h5").text();
                    var keyword_content = $(this).find(".content").text();
                    if(keyword_title.indexOf(query) >= 0 || keyword_content.indexOf(query) >= 0){
                        $(this).show();
                    }
                })
            }else {
                $('#list_listgroup .list-group-item').show();
            }
        });

        $('#list_listgroup').on('click', '.list-group-item', function (event) {
            var form = $('#editfield_form');
            var nid = $(this).attr('nid');
            $('#list_listgroup').find('.list-group-item').removeClass('active');
            $(this).addClass('active');
            $('#editfield_nothingopened').hide();
            $('#editfield_button_save').text(lang['save']);
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: 'getNote', nid: $(this).attr('nid')},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        form.find("#editfield_form_title").val(json.data.note_title);
                        form.find("#editfield_form_content").val(json.data.note_content);
                        $('#editfield_button_save').attr('nid', nid);
                        form.show();
                    }else {
                        toast.alert("Error");
                    }
                }
            });
        });

        $('#editfield_button_save').on('click', function (event) {
            event.preventDefault();
            var nid = $(this).attr('nid');
            $.ajax({
                url: window.location,
                type: 'post',
                data: {action: "saveNote", nid: nid, title: $('#editfield_form_title').val(), content: $('#editfield_form_content').val()},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        $('#editfield_button_save').html(lang['saved']);
                        loadNotes();
                    }else {
                        toast.alert("Error");
                    }
                }
            });
        });

        $('#editfield_form_content, #editfield_form_title').on('input propertychange change', function() {
            if($('#editfield_button_save').text() === lang['saved']) $('#editfield_button_save').text(lang['save']);
            clearTimeout(autosave_timer);
            autosave_timer = setTimeout(function() {
                $('#editfield_button_save').trigger("click");
            }, 500);
        });

        $('#editfield_button_delete').on('click', function (event) {
            event.preventDefault();
            var nid = $(this).parent().find('#editfield_button_save').attr('nid');
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "deleteNote", nid: nid},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        loadNotes();
                        $('#editfield_form').fadeOut(200);
                        $('#editfield_nothingopened').show();
                    }else {
                        toast.alert("Error");
                    }
                }
            })
        });

        $('#list_button_sync').on('click', function (event) {
            event.preventDefault();
            loadNotes();
        });

        $('#list_button_create').on('click', function (event) {
            $.ajax({
                url: window.location,
                type: 'get',
                data: {action: "createNote"},
                success: function (data) {
                    var json = JSON.parse(data);
                    if(!json.error){
                        $.when(loadNotes()).done($('#list_listgroup').find('>:first-child').click());
                    }else{
                        toast.alert("Error");
                    }
                }
            })
        });

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
