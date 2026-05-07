<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$dados         = json_decode(file_get_contents("php://input"), true);
$id            = intval($dados['id'] ?? 0);
$id_utilizador = intval($dados['id_utilizador'] ?? 0);

if (!$id || !$id_utilizador) {
    echo json_encode(["sucesso" => false, "erro" => "Dados inválidos."]);
    exit;
}

$stmt = $conn->prepare("UPDATE marcacoes SET estado = 'cancelado' WHERE id = ? AND id_Utilizador = ?");
$stmt->bind_param("ii", $id, $id_utilizador);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao cancelar ou marcação não encontrada."]);
}
?>
