<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$dados    = json_decode(file_get_contents("php://input"), true);
$email    = trim($dados['email'] ?? '');
$password = $dados['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["sucesso" => false, "erro" => "Preencha todos os campos."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nome, password FROM utilizadores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $nome, $hash);
$stmt->fetch();

if ($id && password_verify($password, $hash)) {
    echo json_encode(["sucesso" => true, "id" => $id, "nome" => $nome, "email" => $email]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Email ou palavra-passe incorretos."]);
}
?>
