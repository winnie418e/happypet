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

$host = 'localhost';
$dbname = 'happypet_DB';
$user = 'root';
$password = '';

$key = "your_secret_key"; // 共享密鑰
$issuedAt = time();
$expirationTime = $issuedAt + 600;  // JWT 有效期 (例如 10 分鐘)
$refreshExpirationTime = $issuedAt + 604800;  // 刷新令牌有效期 (例如 7 天)
// $expirationTime = $issuedAt + 10;  // JWT 有效期 (例如 10 秒)
// $refreshExpirationTime = $issuedAt + 604800;  // 刷新令牌有效期 (例如 7 天)

header('Content-Type: application/json');

// $data = json_decode(file_get_contents("php://input"));
// $username = $data->username;
// $password = $data->password;

$username = $_REQUEST['account'];
$pwd = hash('sha256', $_REQUEST['password']);

$db = new PDO("mysql:host=${host};dbname=${dbname};charset=utf8mb4", $user);
$sql = "select id, permission, ahref from back_userinfo where account = ? and password = ?;";
$stmt =$db->prepare($sql);
$stmt->execute([$username, $pwd]);
$rows = $stmt->fetch(PDO::FETCH_ASSOC);

// 假設用戶名和密碼驗證已經通過
if ($rows) {
    $payload = array(
        "iss" => "http://yourdomain.com",
        "aud" => "http://yourdomain.com",
        "iat" => $issuedAt,
        "nbf" => $issuedAt,
        "exp" => $expirationTime,
        "data" => array(
            "username" => $username
        )
    );

    $refreshPayload = array(
        "iss" => "http://yourdomain.com",
        "aud" => "http://yourdomain.com",
        "iat" => $issuedAt,
        "nbf" => $issuedAt,
        "exp" => $refreshExpirationTime,
        "data" => array(
            "username" => $username
        )
    );

    $jwt = JWT::encode($payload, $key, 'HS256');
    $refreshToken = JWT::encode($refreshPayload, $key, 'HS256');

    echo json_encode(
        array(
            "message" => "Successful login.",
            "token" => $jwt,
            "refreshToken" => $refreshToken,
            "permission" => $rows["permission"],
            "id" => $rows["id"],
            "ahref" => $rows['ahref']
        )
    );
} else {
    echo json_encode(array("message" => "Login failed."));
}
?>
