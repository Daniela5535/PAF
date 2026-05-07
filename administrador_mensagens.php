<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$metodo = $_SERVER['REQUEST_METHOD'];

// ── LER todas as mensagens ──────────────────────────────────────────────────
if ($metodo === "GET") {
    $stmt = $conn->prepare("
        SELECT m.id, m.nome, m.email, m.assunto, m.mensagem,
               m.lida, m.resposta, m.resposta_data,
               DATE_FORMAT(m.enviado_em, '%d/%m/%Y') AS data,
               DATE_FORMAT(m.enviado_em, '%H:%i') AS hora
        FROM mensagens m
        ORDER BY m.enviado_em DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $msgs = [];
    while ($row = $result->fetch_assoc()) $msgs[] = $row;
    echo json_encode($msgs);
    exit;
}

// ── ATUALIZAR mensagem (marcar lida / responder) ────────────────────────────
if ($metodo === "POST") {
    $dados  = json_decode(file_get_contents("php://input"), true);
    $id     = intval($dados['id'] ?? 0);
    $acao   = $dados['acao'] ?? '';

    if (!$id) { echo json_encode(["sucesso" => false, "erro" => "ID inválido."]); exit; }

    if ($acao === 'lida') {
        $stmt = $conn->prepare("UPDATE mensagens SET lida = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["sucesso" => true]);
        exit;
    }

    if ($acao === 'responder') {
        $resposta = trim($dados['resposta'] ?? '');
        if (!$resposta) { echo json_encode(["sucesso" => false, "erro" => "Resposta vazia."]); exit; }
        $agora = date('d/m/Y \à\s H:i');
        $stmt = $conn->prepare("UPDATE mensagens SET resposta = ?, resposta_data = ?, lida = 1 WHERE id = ?");
        $stmt->bind_param("ssi", $resposta, $agora, $id);
        $stmt->execute();
        echo json_encode(["sucesso" => true, "respostaData" => $agora]);
        exit;
    }

    if ($acao === 'apagar') {
        $stmt = $conn->prepare("DELETE FROM mensagens WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["sucesso" => $stmt->affected_rows > 0]);
        exit;
    }

    echo json_encode(["sucesso" => false, "erro" => "Ação desconhecida."]);
    exit;
}

http_response_code(405);
echo json_encode(["erro" => "Método não suportado."]);
?>