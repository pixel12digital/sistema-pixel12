<?php
set_time_limit(60);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    echo "<h2>Erro: sessão expirada. Faça login novamente.</h2>";
    exit;
}
require_once __DIR__ . '/config.php';
require_once 'db.php';
$config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
$api_key = $config ? $config['valor'] : '';

function getAsaas($endpoint) {
    global $api_key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $api_key
    ]);
    $result = curl_exec($ch);
    if ($result === false) {
        echo "<h2>Erro ao conectar à API do Asaas: " . curl_error($ch) . "</h2>";
        curl_close($ch);
        exit;
    }
    curl_close($ch);
    return json_decode($result, true);
}

// Parâmetros de busca/filtro/ordenação
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
$sort = $order === 'asc' ? 'dueDate' : '-dueDate';

// Filtros conforme print
$status = 'PENDING,OVERDUE';
$billingType = 'BOLETO_BANCARIO,CREDIT_CARD,DEBIT_CARD,PIX,DEPOSIT,TRANSFER,BANK_TRANSFER,UNDEFINED';

// Busca real por nome/email
$customerIds = [];
if ($busca) {
    // Buscar clientes pelo nome ou email
    $buscaEncoded = urlencode($busca);
    $respClientes = getAsaas("/customers?name=$buscaEncoded&limit=50");
    if (!empty($respClientes['data'])) {
        foreach ($respClientes['data'] as $cli) {
            $customerIds[] = $cli['id'];
        }
    }
    // Se não achou pelo nome, tenta pelo email
    if (empty($customerIds)) {
        $respClientes = getAsaas("/customers?email=$buscaEncoded&limit=50");
        if (!empty($respClientes['data'])) {
            foreach ($respClientes['data'] as $cli) {
                $customerIds[] = $cli['id'];
            }
        }
    }
}

// Montar query de cobranças
$paymentsQuery = "/payments?status=$status&billingType=$billingType&limit=$limit&offset=$offset&sort=$sort";
if ($busca && !empty($customerIds)) {
    foreach ($customerIds as $cid) {
        $paymentsQuery .= "&customer=$cid";
    }
}
$api_url_debug = ASAAS_API_URL . $paymentsQuery;
$resp = getAsaas($paymentsQuery);
// Debug: Exibir URL e resposta bruta (sempre antes do exit)
if (isset($_GET['debug'])) {
    echo '<div style="background:#222;padding:16px;margin:16px 0;border:2px solid #a259e6;color:#fff;font-size:13px;overflow-x:auto;">';
    echo '<b>URL da requisição:</b><br>' . htmlspecialchars($api_url_debug) . '<br><br>';
    echo '<b>Resposta bruta da API:</b><br><pre style="white-space:pre-wrap;">' . htmlspecialchars(json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    echo '</div>';
}
if (empty($resp['data'])) {
    echo "<h2>Nenhuma cobrança retornada pela API do Asaas.</h2>";
    if (isset($resp['errors'])) {
        echo "<pre>";
        print_r($resp['errors']);
        echo "</pre>";
    }
    exit;
}
$totalCount = $resp['totalCount'] ?? 0;
$cobrancas = $resp['data'];

// Buscar dados do cliente para cada cobrança e filtrar tipos
$clientes = [];
// Unificar lógica: sempre ordenar em PHP, agora buscando todas as páginas
$todasCobrancas = [];
if ($busca && !empty($customerIds)) {
    // Buscar cobranças de todos os clientes encontrados (todas as páginas)
    foreach ($customerIds as $cid) {
        $offsetBusca = 0;
        do {
            $respPag = getAsaas("/payments?status=$status&billingType=$billingType&customer=$cid&limit=100&offset=$offsetBusca");
            if (!empty($respPag['data'])) {
                foreach ($respPag['data'] as $cob) {
                    $todasCobrancas[] = $cob;
                }
            }
            $offsetBusca += 100;
        } while (!empty($respPag['data']) && count($respPag['data']) === 100);
    }
} else {
    // Fluxo normal (sem busca): buscar todas as páginas
    $offsetBusca = 0;
    do {
        $respPag = getAsaas("/payments?status=$status&billingType=$billingType&limit=100&offset=$offsetBusca");
        if (!empty($respPag['data'])) {
            foreach ($respPag['data'] as $cob) {
                $todasCobrancas[] = $cob;
            }
        }
        $offsetBusca += 100;
    } while (!empty($respPag['data']) && count($respPag['data']) === 100);
}
// Ordenar todas as cobranças por dueDate (asc/desc)
usort($todasCobrancas, function($a, $b) use ($order) {
    $aDate = isset($a['dueDate']) ? strtotime($a['dueDate']) : 0;
    $bDate = isset($b['dueDate']) ? strtotime($b['dueDate']) : 0;
    return $order === 'asc' ? $aDate - $bDate : $bDate - $aDate;
});
// Debug: Exibir ordem e vencimentos
if (isset($_GET['debug'])) {
    echo '<div style="background:#222;padding:10px;margin:10px 0;border:2px solid #a259e6;color:#fff;font-size:13px;max-height:200px;overflow:auto;">';
    echo '<b>Order:</b> ' . htmlspecialchars($order) . '<br><b>Vencimentos ordenados:</b><br>';
    foreach ($todasCobrancas as $cob) {
        echo htmlspecialchars($cob['dueDate'] ?? '-') . ' | ';
    }
    echo '</div>';
}
// Paginar manualmente
$totalCount = count($todasCobrancas);
$cobrancas = array_slice($todasCobrancas, $offset, $limit);

// Buscar dados do cliente para cada cobrança e filtrar tipos
$clientes = [];
foreach ($cobrancas as $cob) {
    $isAvulsa = empty($cob['subscription']) && empty($cob['installment']);
    $isAssinatura = !empty($cob['subscription']);
    $isParcelada = !empty($cob['installment']);
    if (!($isAvulsa || $isAssinatura || $isParcelada)) continue;
    $cli = [];
    if (!empty($cob['customer'])) {
        $customer = getAsaas("/customers/" . $cob['customer']);
        $cli['name'] = $customer['name'] ?? '';
        $cli['phone'] = $customer['phone'] ?? '';
        $cli['email'] = $customer['email'] ?? '';
        $cli['plano'] = $customer['customFields'][0]['value'] ?? '';
    }
    $cli['status_cob'] = $cob['status'] ?? '';
    $cli['vencimento'] = $cob['dueDate'] ?? '';
    $clientes[] = $cli;
}

$filtro = $_GET['filtro'] ?? '';
$activeCobrancas = $filtro !== 'planos' ? 'active' : '';
$activePlanos = $filtro === 'planos' ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Clientes Asaas (Tempo Real)</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'menu_lateral.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="topbar-title">Painel Administrativo - Pixel 12 Digital <span style='color:#a259e6;font-weight:bold;'>| Cobranças</span></span>
    </div>
    <div class="clientes-asaas-container">
        <div class="filtros-bar">
            <form method="get" style="display:inline;">
                <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Procurar por nome ou email do cliente">
                <button type="submit">🔍</button>
            </form>
            <button type="button" class="btn-filtro" id="btnFiltro" onclick="toggleFiltros()">Filtros ▼</button>
            <div class="dropdown-filtros" id="dropdownFiltros">
                <b>Formas de pagamento:</b><br>
                <span style="color:#a259e6;">Boleto, Cartão de Crédito, Cartão de Débito, Pix, Depósito, Transferência, Pergunte ao cliente</span><br><br>
                <b>Tipos de cobrança:</b><br>
                <span style="color:#a259e6;">Avulsas, Assinaturas, Parceladas</span><br><br>
                <b>Situações:</b><br>
                <span style="color:#a259e6;">Aguardando pagamento, Vencida</span><br><br>
                <i>Filtros avançados em breve!</i>
            </div>
            <button class="btn-acao">Ações em lote ▼</button>
            <button class="btn" style="background:#1976d2;color:#fff;" onclick="abrirModalCobranca()">+ Adicionar cobrança</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th style="cursor:pointer;" onclick="window.location='?<?= http_build_query(array_merge($_GET, ['order'=>$order==='asc'?'desc':'asc'])) ?>'">Vencimento <?= $order==='asc'?'↑':'↓' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['plano'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['status_cob'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['vencimento'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="paginacao">
            <?php
            $totalPages = ceil($totalCount / $limit);
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = $i == $page ? 'active' : '';
                $params = $_GET;
                $params['page'] = $i;
                echo "<a href='?".http_build_query($params)."' class='$active'>$i</a> ";
            }
            ?>
        </div>
    </div>
</div>
    <!-- Modal de Cadastro de Cobrança -->
    <div id="modalCobranca" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(24,28,35,0.92);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#232836;padding:0;max-width:480px;width:95vw;border-radius:12px;box-shadow:0 2px 24px #000a;position:relative;">
            <button onclick="fecharModalCobranca()" style="position:absolute;top:10px;right:16px;background:none;border:none;color:#fff;font-size:1.7rem;cursor:pointer;">&times;</button>
            <iframe src="cobranca_add.php" style="width:100%;height:600px;border:none;border-radius:12px;"></iframe>
        </div>
    </div>
</body>
</html> 