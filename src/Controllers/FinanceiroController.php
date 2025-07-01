<?php
namespace Controllers;

use Models\Cobranca;
use Services\AsaasService;

class FinanceiroController
{
    protected $asaas;

    public function __construct()
    {
        $this->asaas = new AsaasService();
    }

    /**
     * Lista cobranças do mês atual e mostra resumo financeiro.
     */
    public function index(): void
    {
        $mesAtual = date('Y-m-01');
        $proximoMes = date('Y-m-01', strtotime('+1 month'));
        $cobrancas = Cobranca::all([
            'data_inicial' => $mesAtual,
            'data_final' => $proximoMes
        ]);
        $resumo = [
            'total_receita' => 0,
            'total_pendente' => 0,
            'total_vencido' => 0,
        ];
        foreach ($cobrancas as $c) {
            if ($c->status === 'RECEBIDO' || $c->status === 'PAID') {
                $resumo['total_receita'] += (float)$c->valor;
            } elseif ($c->status === 'PENDENTE' || $c->status === 'PENDING') {
                $resumo['total_pendente'] += (float)$c->valor;
            } elseif ($c->status === 'VENCIDO' || $c->status === 'OVERDUE') {
                $resumo['total_vencido'] += (float)$c->valor;
            }
        }
        $status = $_GET['status'] ?? null;
        if ($status) {
            $cobrancas = array_filter($cobrancas, fn($c) => $c->status === $status);
        }
        require __DIR__ . '/../../admin/financeiro/index.php';
    }

    /**
     * Exibe formulário de nova cobrança.
     */
    public function create(): void
    {
        require __DIR__ . '/../../admin/financeiro/create.php';
    }

    /**
     * Processa criação de cobrança ou assinatura.
     * @param array $post
     */
    public function store(array $post): void
    {
        $data = [
            'customer'    => $post['cliente_id'],
            'value'       => $post['valor'],
            'dueDate'     => $post['vencimento'],
            'description' => $post['descricao'] ?? '',
            'billingType' => $post['tipo_pagamento'] ?? 'UNDEFINED',
        ];
        if ($post['tipo'] === 'assinatura') {
            $data['cycle'] = $post['ciclo'] ?? 'MONTHLY';
            $data['nextDueDate'] = $post['vencimento'];
            $asaasResp = $this->asaas->createSubscription($data);
        } else {
            if (!empty($post['parcelas']) && $post['parcelas'] > 1) {
                $data['installmentCount'] = (int)$post['parcelas'];
                $data['totalValue'] = $post['valor'] * $post['parcelas'];
            }
            $asaasResp = $this->asaas->createPayment($data);
        }
        $cobranca = new Cobranca();
        $cobranca->asaas_payment_id = $asaasResp['id'] ?? null;
        $cobranca->cliente_id = $post['cliente_id'];
        $cobranca->valor = $asaasResp['value'] ?? $post['valor'];
        $cobranca->status = $asaasResp['status'] ?? 'PENDENTE';
        $cobranca->vencimento = $asaasResp['dueDate'] ?? $post['vencimento'];
        $cobranca->data_criacao = date('Y-m-d H:i:s');
        $cobranca->data_pagamento = $asaasResp['paymentDate'] ?? null;
        $cobranca->descricao = $asaasResp['description'] ?? $post['descricao'] ?? '';
        $cobranca->tipo = $post['tipo'];
        $cobranca->url_fatura = $asaasResp['invoiceUrl'] ?? null;
        $cobranca->parcela = $asaasResp['installment'] ?? null;
        $cobranca->assinatura_id = $asaasResp['subscription'] ?? null;
        $cobranca->save();
        header('Location: /admin/financeiro?msg=sucesso');
        exit;
    }

    /**
     * Webhook do Asaas: atualiza status da cobrança.
     */
    public function webhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        $dados = $this->asaas->handleWebhook($payload);
        $cobranca = Cobranca::all(['asaas_payment_id' => $dados['id']]);
        if ($cobranca && count($cobranca) > 0) {
            $c = $cobranca[0];
            $c->updateStatus($dados['status']);
        }
        http_response_code(200);
        echo 'OK';
    }
} 