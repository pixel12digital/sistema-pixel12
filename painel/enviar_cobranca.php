<?php
require_once __DIR__ . '/../config.php';
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$celular = $data['celular'] ?? '';
$msg = $data['msg'] ?? '';
if (!$celular || !$msg) { echo json_encode(['success'=>false,'error'=>'Dados incompletos']); exit; }
$canal = $mysqli->query("SELECT * FROM canais_comunicacao WHERE LOWER(nome_exibicao) = 'financeiro' AND status = 'conectado' LIMIT 1")->fetch_assoc();
if (!$canal) { echo json_encode(['success'=>false,'error'=>'Robô Financeiro não conectado']); exit; }
$porta = $canal['porta'];
$payload = json_encode(['to'=>$celular, 'message'=>$msg]);
$ch = curl_init("http://localhost:$porta/send");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) { echo json_encode(['success'=>false,'error'=>$err]); exit; }
echo json_encode(['success'=>true]); 