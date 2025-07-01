<?php
namespace App\Controllers\Financeiro;
require_once __DIR__ . '/../../../src/Models/Assinatura.php';

class AssinaturasController
{
    public function index()
    {
        global $mysqli;
        $assinaturas = \Assinatura::todas($mysqli);
        include __DIR__ . '/../../Views/financeiro/assinaturas/index.php';
    }

    public function show($id)
    {
        global $mysqli;
        $assinatura = \Assinatura::buscarPorId($mysqli, $id);
        include __DIR__ . '/../../Views/financeiro/assinaturas/show.php';
    }

    // O método sync pode ser adaptado depois para MySQLi puro se necessário
} 