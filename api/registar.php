<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$dados    = json_decode(file_get_contents("php://input"), true);
$nome     = trim($dados['nome'] ?? '');
$email    = trim($dados['email'] ?? '');
$password = $dados['password'] ?? '';
$telefone = trim($dados['telefone'] ?? '');

if (!$nome || !$email || !$password) {
    echo json_encode(["sucesso" => false, "erro" => "Preencha todos os campos."]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["sucesso" => false, "erro" => "Este email já está registado."]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO utilizadores (nome, email, password, telefone) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $email, $hash, $telefone);

if ($stmt->execute()) {
    echo json_encode(["sucesso" => true, "id" => $conn->insert_id, "nome" => $nome, "email" => $email]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao criar conta."]);
}
?>
