<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require "../config.php";

$dados = json_decode(file_get_contents("php://input"), true);
$id_utilizador = intval($dados['id_utilizador'] ?? 0);
$nome          = trim($dados['nome'] ?? '');
$email         = trim($dados['email'] ?? '');
$assunto       = trim($dados['assunto'] ?? '');
$mensagem      = trim($dados['mensagem'] ?? '');

if (!$nome || !$email || !$mensagem) {
    echo json_encode(["sucesso" => false, "erro" => "Preencha todos os campos."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO mensagens (id_utilizador, nome, email, assunto, mensagem) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $id_utilizador, $nome, $email, $assunto, $mensagem);

if ($stmt->execute()) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao enviar mensagem."]);
}
?>