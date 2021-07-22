<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 08.08.2019
 * Time: 15:33
 */

class AppCore{

    private $config_file;
    private $rootDir;
    private $webInstance;
    private $pdo;
    private $lang;
    private $userdata;

    /**
     * AppCore constructor.
     * @param $config_file
     * @param $rootDir
     * @param $webInstance
     */
    public function __construct($config_file, $rootDir){
        $this->init($config_file);
        $this->rootDir = $rootDir;
    }

    /**
     * @param $config_file
     */
    private function init($config_file){
        if(!$this->isCommandLineInterface()){
            session_start();
        }
        require_once $config_file;
        $this->config_file = $config_file;

        $this->pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DATABASE, MYSQL_USERNAME, MYSQL_PASSWORD);

        if(!$this->isCommandLineInterface()){
            if(isset($_COOKIE['lang'])){
                $json_lang = file_get_contents(dirname($config_file) . "/lang/" . $_COOKIE['lang'] . ".json");
            }else{
                setcookie("lang", "gb", time() + 3600 * 24 * 365 * 10);
                $json_lang = file_get_contents(dirname($config_file) . "/lang/gb.json");
            }
            $this->lang = json_decode($json_lang, true);

            if($this->isUserLoggedIn()){
                $statement = $this->getPDO()->prepare("SELECT * FROM users WHERE user_id = :uid");
                $statement->execute(array(":uid" => $this->getUserID()));
                $this->userdata = $statement->fetch();
            }
        }
    }

    /**
     *
     */
    public function reinit(){
        $this->init($this->config_file);
    }

    /**
     * @return string
     */
    public function lang($s){
        return $this->lang[$s];
    }

    /**
     * @return array
     */
    public function fullLang(){
        return $this->lang;
    }

    public function languages(){
        return json_decode(file_get_contents(dirname($this->config_file) . "/lang/languages.json"), true);
    }

    /**
     * @return PDO
     */
    public function getPDO(){
        return $this->pdo;
    }

    /**
     * @return string
     */
    public function hash($s){
        return hash('sha512', $s);
    }

    /**
     * @return string
     */
    public function getUrl(){
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * @return string
     */
    public function getWebRoot(){
        return WEB_ROOT;
    }

    /**
     * @return string
     */
    public function getWebRewrite(){
        return WEB_REWRITE;
    }

    /**
     * @return string
     */
    public function getWebUrl(){
        return $this->getWebRoot() . "/" . $this->getWebRewrite();
    }

    /**
     * @param $reason
     * @return mixed
     */
    public function printError($reason){
        return print_r(json_encode(array("error" => true, "reason" => $reason)));
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn(){
        return (!empty($_SESSION['uid']));
    }

    /**
     * @return |null
     */
    public function getUserID(){
        return $this->isUserLoggedIn() ? $_SESSION['uid'] : null;
    }

    /**
     * @param int $length
     * @return string
     */
    public function randomString($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return mixed
     */
    public function getUserData(){
        return $this->userdata;
    }

    /**
     * @return string
     */
    public function getRootDir(){
        return $this->rootDir;
    }

    /**
     * @return boolean
     * */
    function isCommandLineInterface(){
        return (php_sapi_name() === 'cli');
    }

    /**
     * @return PHPMailer
     */
    public function getMailer(){
        require_once dirname(__FILE__) . '/../config/mailconfig.php';
        require_once dirname(__FILE__) . '/PHPMailer/PHPMailerAutoload.php';
        $mail = new PHPMailer;

        $mail->IsSMTP();
        $mail->CharSet = 'utf-8';
        $mail->Host = MAIL_HOST;
        $mail->Port = MAIL_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        //$mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->IsHTML(true);
        $mail->From = MAIL_ADRESS;
        $mail->FromName = MAIL_SENDER;

        return $mail;
    }

    /**
     *
     */
    function correctImageOrientation($filename) {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    // then rewrite the rotated image back to the disk as $filename
                    imagejpeg($img, $filename, 95);
                } // if there is some rotation necessary
            } // if have the exif orientation info
        } // if function exists
    }
}
