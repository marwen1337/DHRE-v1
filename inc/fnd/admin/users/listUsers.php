<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 17.11.2019
 * Time: 00:11
 */

if(!$core->getUserData()['user_isAdmin']) header("Location: " . $core->getWebUrl());

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
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                    <h3 class="card-title">User List</h3>
                    <div class="card-text">
                        <table class="table table-hover mt-3 mb-0" id="table_users" style="word-wrap: break-word;">
                            <thead>
                            <tr>
                                <th scope="col">User ID</th>
                                <th scope="col">User Name</th>
                                <th scope="col">User Email</th>
                                <th scope="col">User Shortquota</th>
                                <th scope="col">User Imagequota</th>
                                <th scope="col">User Customtoken</th>
                                <th scope="col">User Active</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody id="table_users_body">
                            <?php

                            $stmt = $core->getPDO()->prepare("SELECT * FROM users");
                            $stmt->execute();
                            foreach ($stmt->fetchAll() as $item){

                                $stmt = $core->getPDO()->prepare("SELECT COUNT(*) AS current_quota FROM shortlinks WHERE short_creator = :user_id");
                                $stmt->execute(array(":user_id" => $item['user_id']));
                                if($item['user_shortquota'] != 0){
                                    $shortQuota = $item['user_shortquota'] == "-1" ? "Unlimited" : round(($stmt->fetch()['current_quota'] / $item['user_shortquota']) * 100, 1) . "%";
                                }else{
                                    $shortQuota = 0;
                                }

                                $stmt = $core->getPDO()->prepare("SELECT COUNT(*) AS current_quota FROM images WHERE img_creator = :user_id");
                                $stmt->execute(array(":user_id" => $item['user_id']));
                                if($item['user_imgquota'] != 0){
                                    $imgQuota = $item['user_imgquota'] == "-1" ? "Unlimited" : round(($stmt->fetch()['current_quota'] / $item['user_imgquota']) * 100, 1) . "%";
                                }else{
                                    $imgQuota = 0;
                                }

                                $customtoken = $item['user_customtoken'] == true ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>';
                                $userActive = $item['user_active'] == true ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>';

                                $s = '<tr class=""><td>' . $item['user_id'] . '</td><td>' . $item['user_name'] . '</td><td>' . $item['user_email'] . '</td><td>' . $shortQuota . '</td><td>' . $imgQuota . '</td><td>' . $customtoken . '</td><td>' . $userActive . '</td><td><a href="users/editUser?uid=' . $item['user_id'] . '"><i class="fa fa-user-edit"></i></a></td></tr>';

                                echo $s;
                            }

                            ?>
                            </tbody>
                        </table>
                        <a class="" href="users/createUser"><button class="btn btn-primary w-100 mt-3">Create User</button></a>
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

        $('#loader').delay(200).fadeOut(400);
    });
</script>
</body>
</html>
