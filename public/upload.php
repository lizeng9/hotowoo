<?php

/*

#distribute upload services.


#nginx config.
location ^~ /uploads/{

    if ($request_method = DELETE) {
          rewrite ^/(.*)$ /upload.php?action=delete&url=$1 last;
      }

    }

location ^~ /thumbs/{
    try_files $uri /upload.php?action=resize&url=$request_uri;
}

*/

require_once __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;
use Firebase\JWT\JWT;

define("UPLOAD_SIZE_ERROR", 1001);
define("UPLOAD_TYPE_ERROR", 1002);

define("UPLOAD_FILE_KEY", "uploadFile");
define("BASE_UPLOAD_DIR", "uploads");
define("BASE_THUMB_DIR", "thumbs");
define("MAX_THUMB_FILES", 3);

/**
 * Resource upload, resize, delete
 */
class Resource
{
    protected $allow_sizes = [
        "png"  => 2*1024*1024,
        "gif"  => 2*1024*1024,
        "jpeg" => 2*1024*1024,
        "jpg"  => 2*1024*1024,
        "pdf"  => 20*1024*1024,
        "mp3"  => 20*1024*1024,
    ];

    protected $allow_types = [
        'png'  => 'image/png',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'pdf'  => 'application/pdf',
        'mp3'  => 'audio/mpeg',
    ];

    protected $domain      = "http://127.0.0.1:8081";

    protected $ext = "png";

    protected $uid = 0;

    protected $auth = null;

    public function __construct($allow_types = null, $allow_sizes = null, $domain = null)
    {
        if ($allow_types) {
            $this->allow_types = $allow_types;
        }

        if ($allow_sizes) {
            $this->allow_sizes = $allow_sizes;
        }

        if ($domain) {
            $this->domain = $domain;
        }

    }

    public function set_auth($auth){
        $this->auth = $auth;
    }

    public function run()
    {
        $this->enable_cors();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $image_url = $_POST["image_url"] ?? "";
            if($image_url){
                $action = "update";
            }else{
                $action = "create";
            }
        }else{
            $action = $_GET["action"] ?? "";
            $url = $_GET["url"] ?? "";
            $url = $this->input_path_filter($url);
        }

        if(in_array($action, ["create","update","delete"])){

            if($this->auth){
                $jwt = $this->auth->check();
                if($jwt === false){
                    $this->response_code(401,"auth error");
                }else{
                    $this->uid = $jwt["uid"];
                }
            }

        }

        switch ($action) {
            case 'create':
                $this->create($_FILES,$_POST);
                break;
            case 'update':
                $this->update($_FILES,$_POST);
                break;
            case 'resize':
                $this->resize($url);
                break;
            case 'delete':
                $this->delete($url);
                break;
            default:
                $this->response_code(501,"action not support");
                break;
        }


    }

    private function update($_files, $_post)
    {

        $width   = $_post["width"] ?? 0;
        $height  = $_post["height"] ?? 0;

        if (!isset($_files[UPLOAD_FILE_KEY])) {
            $this->response_code(401,"upload file key wrong");
        }
        $uploaded = $_files[UPLOAD_FILE_KEY];
        $this->validate($uploaded);

        $ext = $this->get_file_ext($uploaded["name"]);

        //image_url is raw target url
        $image_url = $_post["image_url"];

        $image_fullname = trim(parse_url($image_url, PHP_URL_PATH),'/');
        $dst_ext = $this->get_file_ext($image_fullname);

        if($ext != $dst_ext){
            $this->response_code(501,"file type is not right for image_url");
        }

        if (!file_exists($image_fullname)) {
           $this->response_code(400,"update image_url is not exists");
        }

        $path_uid = basename(dirname($image_url));

        if($this->uid != $path_uid){
            $this->response_code(401,"auth error");
        }

        $dst = $image_fullname;

        //delete file
        @unlink($dst) or $this->response_code(501,"delete file error");

        //delete thumb files
        $thumb_folder = $this->get_thumb_folder($image_url);
        if (file_exists($thumb_folder)) {
            $this->delete_dir($thumb_folder);
        }

        //copy file
        @move_uploaded_file($uploaded["tmp_name"], $dst) or $this->response_code(501,"move file error");

        if ($this->is_image($ext) && $width > 0 && $height > 0) {

            $img = Image::make($dst);

            // add callback functionality to retain maximal original image size
            $img->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });

            $img->save($dst);

        }

        $ret = [
            "url"  => $this->domain."/$dst",
            "name" => $uploaded['name'],
            "size" => $uploaded['size'],
            "ext"  => $ext
        ];
        $this->response(0, "ok", $ret);
    }

    private function create($_files, $_post)
    {
        $catalog = $_post["catalog"] ?? "default";

        $catalog = $this->input_path_filter($catalog);

        $width   = $_post["width"] ?? 0;
        $height  = $_post["height"] ?? 0;


        if (!isset($_files[UPLOAD_FILE_KEY])) {
            $this->response_code(401,"upload file key wrong");
        }

        $uploaded = $_files[UPLOAD_FILE_KEY];
        $this->validate($uploaded);
        $ext = $this->get_file_ext($uploaded["name"]);


        $new_path = BASE_UPLOAD_DIR . "/" .$catalog . "/" . date("Y/m/d/") . $this->uid;

        if (!file_exists($new_path)) {
            @mkdir($new_path, 0755, true) or $this->response_code(501,"create folder error");
        }

        $uuid = $this->url_uuid();

        if(!$this->is_safety_path($new_path)){
            $this->response_code(501,"path is unsafe");
        }


        $dst = $new_path . "/$uuid" . "." . $ext;

        @move_uploaded_file($uploaded["tmp_name"], $dst) or $this->response_code(501,"move file error");

        if ($this->is_image($ext) && $width > 0 && $height > 0) {

            $img = Image::make($dst);

            // add callback functionality to retain maximal original image size
            $img->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });

            $img->save($dst);

        }

        $width =random_int(1, 5) * 100;

        $new_path =  str_replace(BASE_UPLOAD_DIR, BASE_THUMB_DIR, $new_path);


        $ret = [
            "url"  => $this->domain."/$dst",
            # "delete-url"  => $this->domain . "/upload.php?action=delete&url=" . $this->domain."/$dst",
            # "resize-url"  => $this->domain . "/upload.php?action=resize&url=" . $this->domain."/$new_path/$uuid/${width}x200." . $ext,
            "name" => $uploaded['name'],
            "size" => $uploaded['size'],
            "ext"  => $ext
        ];
        $this->response(0, "ok", $ret);
    }

    private function resize($img_url)
    {
        $info   = $this->get_thumb_info($img_url);
        $ext    = $info["ext"];
        $dst    = $info["dst"];
        $src    = $info["src"];
        $folder = $info["folder"];
        $width  = $info["width"];
        $height = $info["height"];


        if (!$this->is_image($ext)) {
            $this->response_code(501,"$ext is not image");
        }

        if (!file_exists($src)) {
            $this->response_code(501,"file is not exists");
        }

        if (file_exists($info["dst"])) {
            header('Content-type: ' . $this->allow_types[$ext]);
            @readfile($dst);
            return;
        }

        if (!file_exists($folder)) {
            @mkdir($folder, 0755, true) or $this->response_code(501,"create folder error");
        }else{
            $files = @scandir($folder) or $this->response_code(501,"scan thumbs limit error");
            if(count($files)-2 > MAX_THUMB_FILES){
                $this->response_code(501,"max thumbs limit");
            }
        }


        if(!$this->is_safety_path($folder)){
            $this->response_code(501,"path is unsafe");
        }

        $img = Image::make($src);

        // add callback functionality to retain maximal original image size
        $img->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        $img->save($dst);

        echo $img->response();

    }

    private function delete($url)
    {

        $src = strstr($url, BASE_UPLOAD_DIR);

        if (!file_exists($src)) {
            $this->response(0, "ok");
        }

        if(!$this->is_safety_path($src)){
            $this->response_code(501,"path is unsafe");
        }

        $path_uid = basename(dirname($url));

        if($this->uid != $path_uid){
            $this->response_code(401,"auth error");
        }

        @unlink($src) or $this->response_code(501,"delete file error");

        //delete thumb files
        $thumb_folder = $this->get_thumb_folder($url);
        if (file_exists($thumb_folder)) {
            $this->delete_dir($thumb_folder);
        }

        $this->response(0, "ok");
    }

    private function get_thumb_folder($url)
    {
        $src  = strstr($url, BASE_UPLOAD_DIR);
        $path = BASE_THUMB_DIR . strstr($src, "/");
        $path = substr($path, 0, strpos($path, "."));
        return $path;
    }

    private function get_file_ext($filename)
    {
        $ext       = pathinfo($filename, PATHINFO_EXTENSION);
        $ext       = strtolower($ext);
        return $ext;
    }

    private function validate($uploaded)
    {

        $ext = $this->get_file_ext($uploaded["name"]);

        $mime_type = mime_content_type($uploaded["tmp_name"]);

        $data = [
            "name"        => $uploaded['name'],
            "size"        => $uploaded['size'],
            "ext"         => $ext,
            "type"        => $mime_type,
            "max_size"    => $this->allow_sizes[$ext],
            "allow_exts"  => array_keys($this->allow_types),
            "allow_types" => array_values($this->allow_types),
        ];

        if (!isset($this->allow_types[$ext])) {
            $this->response(UPLOAD_TYPE_ERROR, "type error", $data);
        }

        if (!in_array($mime_type, $this->allow_types)) {
            $this->response(UPLOAD_TYPE_ERROR, "type error", $data);
        }

        $max_size = isset($this->allow_sizes[$ext]) ? $this->allow_sizes[$ext] : 0;

        if ($uploaded['size'] > $max_size) {
            $this->response(UPLOAD_SIZE_ERROR, "size error", $data);
        }
    }

    private function is_image($ext)
    {
        return in_array($ext, ["png", "gif", "jpeg", "jpg"]);
    }

    private function base64url_encode($data)
    {
        $data = str_replace(array('+', '/'), array('-', '_'), base64_encode($data));
        $data = rtrim($data, '=');
        return $data;
    }
    private function base64url_decode($data)
    {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
    }

    private function url_uuid()
    {
        return $this->base64url_encode(random_bytes(20));
    }

    private function get_thumb_info($thumb_url)
    {

        $dst      = strstr($thumb_url, BASE_THUMB_DIR);
        $folder   = dirname($dst);
        $basename = basename($dst);
        preg_match("/(\d+)x(\d+)\.([a-z]+$)/", $basename, $match);
        $width  = $match[1] ?? 0;
        $height = $match[2] ?? 0;
        $ext    = $match[3] ?? "";

        $src = "uploads" . strstr($folder, "/") . "." . $ext;

        return [
            "dst"    => $dst,
            "src"    => $src,
            "folder" => $folder,
            "width"  => $width,
            "height" => $height,
            "ext"    => $ext,
        ];
    }

    private function delete_dir($src)
    {
        $files = @scandir($src) or $this->response_code(501,"scan delete dir error");
        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                @unlink($full) or $this->response_code(501, "delete file error");
            }
        }
        @rmdir($src) or $this->response_code(501,"delete dir error");
    }

    private function is_safety_path($path)
    {
        $real_base = realpath(".") . "/";
        $real_input_path = realpath($path);
        if($real_input_path === false || strpos($real_input_path . "/", $real_base) !==0){
            return false;
        }else{
            return true;
        }

    }

    private function input_path_filter($str)
    {
        //remove .. % space.
        return str_replace([".."," ","%"], "", $str);
    }

    private function enable_cors(){

        header('Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE');
        if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
            header('Access-Control-Allow-Headers: ' .  $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        }
        header('Access-Control-Allow-Origin: *');

        header('Last-modified: '. gmdate('D, d M Y H:i:s T',time()));

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    private function response_code($code,$payload=null)
    {
        header('Content-Type: application/json');
        $ret = [
            "code" => $code,
            "msg"  => $payload
        ];
        echo json_encode($ret);
        exit;
    }

    private function response($code, $msg, $data = "")
    {
        header('Content-Type: application/json');
        $ret = [
            "code" => $code,
            "msg"  => $msg,
            "data" => $data,
        ];
        echo json_encode($ret);
        exit;
    }

}

/**
* jwt auth class
*/
class Auth
{
    private $secret;
    function __construct($secret)
    {
        $this->secret=$secret;

    }

    public function check()
    {
        $jwt = $this->get_jwt();
        return $this->decode_token($jwt);
    }

    public function get_jwt()
    {
        if(isset($_SERVER['HTTP_AUTHORIZATION'])){
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            list($jwt) = sscanf($auth, 'Bearer %s');
        }else{
            $jwt = $_POST["jwt"] ?? $_GET["jwt"] ?? "";
        }
        return $jwt;
    }

    public function encode_token($data)
    {
        $jwt = JWT::encode($data, $this->secret, 'HS256');
        return $jwt;
    }

    public function decode_token($token)
    {
         if(!$token) return false;

         JWT::$leeway = 300;
         try {
            $decoded = JWT::decode($token, $this->secret, array('HS256'));
            return  (array) $decoded;
          } catch (\Exception $e) {
            return false;
          }
    }

}


$secret_key = "hawrk880ZEPvJyliGXKsZJ4Hk2zaSc0u";
$url_host =$_SERVER['REQUEST_SCHEME'] . "://" .  $_SERVER['HTTP_HOST'];

$r = new Resource(null,null,$url_host);
$a = new Auth($secret_key);

$r->set_auth($a);

$r->run();
