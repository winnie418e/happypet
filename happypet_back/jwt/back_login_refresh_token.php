<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "your_secret_key"; // 共享密鑰
$issuedAt = time();
// $expirationTime = $issuedAt + 600;  // JWT 有效期 (例如 10 分鐘)
$expirationTime = $issuedAt + 10;  // JWT 有效期 (例如 10 秒)

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));
$refreshToken = $data->refreshToken;

if ($refreshToken) {
    try {
        $decoded = JWT::decode($refreshToken, new key ($key, 'HS256'));
        
        // 生成新的短期 JWT
        $payload = array(
            "iss" => "http://yourdomain.com",
            "aud" => "http://yourdomain.com",
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            "exp" => $expirationTime,
            "data" => array(
                "username" => $decoded->data->username
            )
        );

        $jwt = JWT::encode($payload, $key, 'HS256');

        echo json_encode(
            array(
                "message" => "refresh token",
                "token" => $jwt
            )
        );
    } catch (Exception $e) {
        echo json_encode(array("message" => "Unauthorized", "error" => $e->getMessage()));
    }
} else {
    echo json_encode(array("message" => "Unauthorized"));
}
?>
