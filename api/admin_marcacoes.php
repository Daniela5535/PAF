<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$metodo = $_SERVER['REQUEST_METHOD'];

// ── LER todas as marcações ──────────────────────────────────────────────────
if ($metodo === "GET") {
    $estado = $_GET['estado'] ?? 'todas';

    $sql = "
        SELECT m.id, u.nome, u.email, s.nome AS servico,
               m.data, m.hora, m.estado, m.reparticao, m.divisao, m.cumprimento
        FROM marcacoes m
        JOIN utilizadores u ON m.id_Utilizador = u.id
        JOIN servicos s     ON m.id_Servico    = s.id
    ";

    if ($estado !== 'todas') {
        $stmt = $conn->prepare($sql . " WHERE m.estado = ? ORDER BY m.data DESC, m.hora DESC");
        $stmt->bind_param("s", $estado);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY m.data DESC, m.hora DESC");
    }

    $stmt->execute();
    $result    = $stmt->get_result();
    $marcacoes = [];
    while ($row = $result->fetch_assoc()) $marcacoes[] = $row;
    echo json_encode($marcacoes);
    exit;
}

// ── ALTERAR estado de uma marcação ──────────────────────────────────────────
if ($metodo === "POST") {
    $dados  = json_decode(file_get_contents("php://input"), true);
    $id     = intval($dados['id']   ?? 0);
    $estado = trim($dados['estado'] ?? '');

    $permitidos = ['pendente', 'confirmado', 'cancelado', 'rejeitado'];
    if (!$id || !in_array($estado, $permitidos)) {
        echo json_encode(["sucesso" => false, "erro" => "Dados inválidos."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE marcacoes SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["sucesso" => true]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Erro ao atualizar estado."]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["erro" => "Método não suportado."]);
?>
