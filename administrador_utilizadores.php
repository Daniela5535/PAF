<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require "../config.php";

$metodo = $_SERVER['REQUEST_METHOD'];

// ── LER todos os utilizadores ───────────────────────────────────────────────
if ($metodo === "GET") {
    $pesquisa = trim($_GET['pesquisa'] ?? '');

    if ($pesquisa) {
        $like = "%$pesquisa%";
        $stmt = $conn->prepare("
            SELECT u.id, u.nome, u.email, u.telefone,
                   COUNT(m.id) AS total_marcacoes,
                   DATE_FORMAT(u.criado_em, '%d/%m/%Y') AS criado_em
            FROM utilizadores u
            LEFT JOIN marcacoes m ON m.id_Utilizador = u.id
            WHERE u.nome LIKE ? OR u.email LIKE ?
            GROUP BY u.id
            ORDER BY u.criado_em DESC
        ");
        $stmt->bind_param("ss", $like, $like);
    } else {
        $stmt = $conn->prepare("
            SELECT u.id, u.nome, u.email, u.telefone,
                   COUNT(m.id) AS total_marcacoes,
                   DATE_FORMAT(u.criado_em, '%d/%m/%Y') AS criado_em
            FROM utilizadores u
            LEFT JOIN marcacoes m ON m.id_Utilizador = u.id
            GROUP BY u.id
            ORDER BY u.criado_em DESC
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    echo json_encode($users);
    exit;
}

// ── APAGAR utilizador ───────────────────────────────────────────────────────
if ($metodo === "POST") {
    $dados = json_decode(file_get_contents("php://input"), true);
    $id    = intval($dados['id'] ?? 0);
    $acao  = $dados['acao'] ?? '';

    if (!$id || $acao !== 'apagar') {
        echo json_encode(["sucesso" => false, "erro" => "Dados inválidos."]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM utilizadores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["sucesso" => $stmt->affected_rows > 0]);
    exit;
}

http_response_code(405);
echo json_encode(["erro" => "Método não suportado."]);
?>