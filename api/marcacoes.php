<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$metodo = $_SERVER['REQUEST_METHOD'];

// ── CRIAR marcação ──────────────────────────────────────────────────────────
if ($metodo === "POST") {
    $dados = json_decode(file_get_contents("php://input"), true);

    $id_utilizador = intval($dados['id_utilizador'] ?? 0);
    $id_servico    = intval($dados['id_servico'] ?? 0);
    $data          = $dados['data'] ?? '';
    $hora          = $dados['hora'] ?? '';
    $reparticao    = $dados['reparticao'] ?? '';
    $divisao       = $dados['divisao'] ?? '';
    $cumprimento   = $dados['cumprimento'] ?? '';

    if (!$id_utilizador || !$id_servico || !$data || !$hora) {
        echo json_encode(["sucesso" => false, "erro" => "Campos obrigatórios em falta."]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO marcacoes (id_Utilizador, id_Servico, data, hora, estado, reparticao, divisao, cumprimento)
        VALUES (?, ?, ?, ?, 'pendente', ?, ?, ?)
    ");
    $stmt->bind_param("iisssss", $id_utilizador, $id_servico, $data, $hora, $reparticao, $divisao, $cumprimento);

    if ($stmt->execute()) {
        echo json_encode(["sucesso" => true, "id" => $conn->insert_id]);
    } else {
        echo json_encode(["sucesso" => false, "erro" => "Erro ao guardar marcação."]);
    }
    exit;
}

// ── LER marcações do utilizador ─────────────────────────────────────────────
if ($metodo === "GET") {
    $id_utilizador = intval($_GET['id_utilizador'] ?? 0);

    if (!$id_utilizador) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT
            m.id,
            s.nome AS servico,
            m.data,
            m.hora,
            m.estado,
            m.reparticao,
            m.divisao,
            m.cumprimento
        FROM marcacoes m
        JOIN servicos s ON m.id_Servico = s.id
        WHERE m.id_Utilizador = ?
        ORDER BY m.data DESC, m.hora DESC
    ");
    $stmt->bind_param("i", $id_utilizador);
    $stmt->execute();
    $result = $stmt->get_result();

    $marcacoes = [];
    while ($row = $result->fetch_assoc()) {
        $marcacoes[] = $row;
    }

    echo json_encode($marcacoes);
    exit;
}

http_response_code(405);
echo json_encode(["erro" => "Método não suportado."]);
?>
