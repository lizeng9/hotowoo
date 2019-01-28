<?php

require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;

$key = "hawrk880ZEPvJyliGXKsZJ4Hk2zaSc0u";
$token = array(
    "uid" => "6",
    "gid" => "1",
    "exp" => time() + 3600*24*7
);


$jwt = JWT::encode($token, $key);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>test upload file</title>
</head>
<body>
<form action="./upload.php" method="post" enctype="multipart/form-data">
	<label for="file">Filename:</label>
	<input type="file" name="uploadFile" id="uploadFile" />
    <br>
    <label for="file">Update image url:(only need for update image)</label>
    <input type="text" name="image_url" />
    <input type="hidden" name="jwt" value="<?php echo $jwt ?>">
	<input type="hidden" name="catalog" value="inn">
	<br />
	<input type="submit" name="submit" value="Submit" />
</form>
	
</body>
</html>

