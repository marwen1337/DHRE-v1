<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 12.08.2019
 * Time: 15:56
 */

if(!empty($_GET['action'])){
    $ac = $_GET['action'];

    $user_id = "";

    if(!empty($_GET['auth_session'])){

        if(!empty($_SESSION['uid'])) {
            $user_id = $_SESSION['uid'];
        }else{
            $core->printError("wrong_login");
            exit;
        }
    }else{
        $login_token = $_GET['token'];
        $login_pass = $_GET['pass'];

        $stmt = $core->getPDO()->prepare("SELECT login_id, login_owner FROM autologin WHERE login_token = :login_token AND login_pass = :login_pass AND login_type = 'ext'");
        $stmt->execute(array(":login_token" => $login_token, ":login_pass" => $login_pass));
        if($stmt->rowCount() == 0){
            $core->printError("wrong_login");
            exit;
        }

        $user_id = $stmt->fetch()['login_owner'];
        $stmt = $core->getPDO()->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->execute(array(":user_id" => $user_id));
        $userdata = $stmt->fetch();
    }

    if($ac == "createShortlink"){
        if(!empty($_POST['target'])){
            $target = $_POST['target'];

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

            $stmt = $core->getPDO()->prepare("INSERT INTO shortlinks (short_creator, short_token, short_targeturl, short_created) VALUES (?, ?, ?, ?)");
            $stmt->execute(array($user_id, $short_token, $target, time()));
            print_r(json_encode(array("error" => false, "token" => $short_token, "url" => $core->getWebUrl() . $short_token, $user_id, $short_token, $target)));
            exit;
        } else{
            $core->printError("no_target_url");
            exit;
        }
    }else if($ac == "uploadImage"){
        if(!empty($_FILES['image'])){

            if($userdata['user_imgquota'] >= 0){
                $stmt = $core->getPDO()->prepare("SELECT img_id FROM images WHERE img_creator = :user_id");
                $stmt->execute(array(":user_id" => $user_id));
                $shortlinks_count = $stmt->rowCount();
                if($userdata['user_imgquota'] <= $shortlinks_count){
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

            $file = $core->getRootDir() . "/images/" . $img_token . "." . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filetype = pathinfo($file, PATHINFO_EXTENSION);

            $valid_extensions = array("jpg", "jpeg" ,"png", "gif");
            /* Check file extension */
            if(!in_array(strtolower($filetype),$valid_extensions)) {
                $core->printError("wrong_file_type");
                exit;
            }

            if(move_uploaded_file($_FILES['image']['tmp_name'], $file)){
                chmod($file, 0644);
                $stmt = $core->getPDO()->prepare("INSERT INTO images (img_creator, img_token, img_file, img_created) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($user_id, $img_token, $file, time()));
                print_r(json_encode(array("error" => false, "token" => $img_token, "url" => $core->getWebUrl() . "img/" . $img_token, $user_id, $img_token)));
                exit;
            }else{
                $core->printError("file_write_error");
                exit;
            }


        } else{
            $core->printError("wrong_args");
            exit;
        }
    }else if($ac == "addVideo"){
        if(!empty($_GET['vtoken'])){
            $token = $_GET['vtoken'];

            $stmt = $core->getPDO()->prepare("SELECT ytdl_id FROM ytdl WHERE ytdl_uid = :uid AND ytdl_token = :token");
            $stmt->execute(array(":uid" => $user_id, ":token" => $token));
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
            $stmt->execute(array($user_id, $token, time(), "mp3", $converted));
            print_r(json_encode(array("error" => false)));

            exit;
        }
    }else if($ac == "getShortlinks"){
        $stmt = $core->getPDO()->prepare("SELECT * FROM shortlinks WHERE short_creator = :user_id");
        $stmt->execute(array(":user_id" => $user_id));
        $rows = array($stmt->fetchAll(PDO::FETCH_ASSOC));
        $rows['quota'] = $core->getUserData()['user_shortquota'];
        print_r(json_encode($rows));
        exit;
    }else{
        $core->printError("wrong_args");
        exit;
    }
}else{
    $core->printError("wrong_args");
    exit;
}