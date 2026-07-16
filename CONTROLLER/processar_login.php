<?php
header('Content-Type: application/json');
require_once __DIR__ . '/AuthController.php';

$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (!$dados || !isset($dados['username']) || !isset($dados['senha'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$authController = new AuthController();
$resultado = $authController->login($dados['username'], $dados['senha']);
echo json_encode($resultado);