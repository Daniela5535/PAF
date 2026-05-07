<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
require "../config.php";

$dados = json_decode(file_get_contents("php://input"), true);
$id            = intval($dados['id']);
$id_utilizador = intval($dados['id_utilizador']);

$stmt = $conn->prepare("UPDATE marcacoes SET estado = 'cancelado' WHERE id = ? AND id_Utilizador = ?");
$stmt->bind_param("ii", $id, $id_utilizador);

if ($stmt->execute()) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao cancelar."]);
}
?>