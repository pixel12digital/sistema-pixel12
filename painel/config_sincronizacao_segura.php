<?php
/**
 * Configuração da Sincronização Segura
 * Ajuste os parâmetros conforme necessário
 */

// Configurações de proteção de dados
$CONFIG_SINCRONIZACAO = [
    // Tempo de proteção (em horas) - dados editados neste período não serão sobrescritos
    'protecao_horas' => 24,
    
    // Campos críticos que NUNCA devem ser sobrescritos pela sincronização
    'campos_criticos' => [
        'nome',
        'email', 
        'cpf_cnpj',
        'telefone',
        'celular'
    ],
    
    // Campos que podem ser atualizados apenas se estiverem vazios
    'campos_apenas_vazios' => [
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'pais',
        'razao_social',
        'observacoes',
        'referencia_externa'
    ],
    
    // Campos que podem ser atualizados normalmente (não críticos)
    'campos_normais' => [
        'notificacao_desativada',
        'emails_adicionais'
    ],
    
    // Configurações de log
    'log_detalhado' => true,
    'arquivo_log' => '../logs/sincronizacao_segura.log',
    
    // Configurações de performance
    'limite_paginas_clientes' => 50,
    'limite_paginas_cobrancas' => 30,
    'registros_por_pagina' => 100,
    
    // Configurações de API
    'timeout_api' => 30,
    'tentativas_api' => 3,
    
    // Configurações de segurança
    'preservar_edicoes_recentes' => true,
    'verificar_timestamp' => true,
    'backup_antes_sincronizacao' => false
];

// Função para obter configuração
function getConfigSincronizacao($chave = null) {
    global $CONFIG_SINCRONIZACAO;
    
    if ($chave === null) {
        return $CONFIG_SINCRONIZACAO;
    }
    
    return $CONFIG_SINCRONIZACAO[$chave] ?? null;
}

// Função para verificar se um campo é crítico
function isCampoCritico($campo) {
    $campos_criticos = getConfigSincronizacao('campos_criticos');
    return in_array($campo, $campos_criticos);
}

// Função para verificar se um campo deve ser atualizado apenas se vazio
function isCampoApenasVazio($campo) {
    $campos_vazios = getConfigSincronizacao('campos_apenas_vazios');
    return in_array($campo, $campos_vazios);
}

// Função para verificar se dados foram editados recentemente
function foiEditadoRecentemente($data_atualizacao) {
    if (!$data_atualizacao) {
        return false;
    }
    
    $protecao_horas = getConfigSincronizacao('protecao_horas');
    $ultima_edicao = new DateTime($data_atualizacao);
    $agora = new DateTime();
    $diferenca = $agora->diff($ultima_edicao);
    
    // Converter para horas
    $horas_diferenca = ($diferenca->days * 24) + $diferenca->h;
    
    return $horas_diferenca < $protecao_horas;
}

// Função para determinar se um campo deve ser atualizado
function deveAtualizarCampo($campo, $valor_atual, $valor_asaas) {
    // Se o campo é crítico, nunca atualizar se já tem valor
    if (isCampoCritico($campo) && !empty($valor_atual)) {
        return false;
    }
    
    // Se o campo deve ser atualizado apenas se vazio
    if (isCampoApenasVazio($campo) && !empty($valor_atual)) {
        return false;
    }
    
    // Se o campo atual está vazio e o valor do Asaas não está vazio
    if (empty($valor_atual) && !empty($valor_asaas)) {
        return true;
    }
    
    // Para campos normais, atualizar se for diferente
    if (!empty($valor_atual) && !empty($valor_asaas) && $valor_atual !== $valor_asaas) {
        return true;
    }
    
    return false;
}

// Função para gerar relatório de proteção
function gerarRelatorioProtecao($clientes_processados, $clientes_sincronizados, $clientes_preservados) {
    $relatorio = [
        'timestamp' => date('Y-m-d H:i:s'),
        'resumo' => [
            'total_processados' => $clientes_processados,
            'sincronizados' => $clientes_sincronizados,
            'preservados' => $clientes_preservados,
            'taxa_preservacao' => $clientes_processados > 0 ? round(($clientes_preservados / $clientes_processados) * 100, 2) : 0
        ],
        'configuracao' => [
            'protecao_horas' => getConfigSincronizacao('protecao_horas'),
            'campos_criticos' => getConfigSincronizacao('campos_criticos'),
            'campos_apenas_vazios' => getConfigSincronizacao('campos_apenas_vazios')
        ]
    ];
    
    return $relatorio;
}

// Interface para ajustar configurações
if (isset($_GET['ajustar_config'])) {
    echo "<h2>Configuração da Sincronização Segura</h2>";
    echo "<form method='post'>";
    echo "<h3>Proteção de Dados</h3>";
    echo "<label>Horas de proteção: <input type='number' name='protecao_horas' value='" . getConfigSincronizacao('protecao_horas') . "' min='1' max='168'></label><br>";
    echo "<small>Dados editados neste período não serão sobrescritos</small><br><br>";
    
    echo "<h3>Campos Críticos (Nunca sobrescritos)</h3>";
    $campos_criticos = getConfigSincronizacao('campos_criticos');
    foreach ($campos_criticos as $campo) {
        echo "<input type='checkbox' name='campos_criticos[]' value='$campo' checked> $campo<br>";
    }
    
    echo "<h3>Campos Apenas Vazios</h3>";
    $campos_vazios = getConfigSincronizacao('campos_apenas_vazios');
    foreach ($campos_vazios as $campo) {
        echo "<input type='checkbox' name='campos_vazios[]' value='$campo' checked> $campo<br>";
    }
    
    echo "<br><input type='submit' value='Salvar Configuração'>";
    echo "</form>";
}

// Processar alterações de configuração
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['protecao_horas'])) {
        $CONFIG_SINCRONIZACAO['protecao_horas'] = intval($_POST['protecao_horas']);
    }
    
    if (isset($_POST['campos_criticos'])) {
        $CONFIG_SINCRONIZACAO['campos_criticos'] = $_POST['campos_criticos'];
    }
    
    if (isset($_POST['campos_vazios'])) {
        $CONFIG_SINCRONIZACAO['campos_apenas_vazios'] = $_POST['campos_vazios'];
    }
    
    // Salvar configuração (você pode implementar persistência em arquivo ou banco)
    echo "<p>Configuração atualizada com sucesso!</p>";
    echo "<a href='?ajustar_config'>Voltar à configuração</a>";
}
?> 