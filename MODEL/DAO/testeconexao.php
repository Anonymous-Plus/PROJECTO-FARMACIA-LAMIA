<?php
require_once __DIR__ . '/conexao.php';

try {
    $conn = Conn::getInstance();
    echo "✅ Conexão com a base de dados 'sgf' estabelecida com sucesso!";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>