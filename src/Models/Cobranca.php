<?php
namespace Models;

require_once __DIR__ . '/../Database.php';
use PDO;
use Exception;

class Cobranca
{
    // Propriedades públicas correspondentes às colunas da tabela
    public $id;
    public $asaas_payment_id;
    public $cliente_id;
    public $valor;
    public $status;
    public $vencimento;
    public $data_criacao;
    public $data_pagamento;
    public $descricao;
    public $tipo;
    public $url_fatura;
    public $parcela;
    public $assinatura_id;

    /**
     * Retorna todas as cobranças, aplicando filtros opcionais.
     * @param array $filters Filtros: cliente_id, status, data_inicial, data_final
     * @return array
     * @throws Exception
     */
    public static function all(array $filters = []): array
    {
        $db = \Database::getInstance();
        $sql = "SELECT * FROM cobrancas WHERE 1=1";
        $params = [];
        if (!empty($filters['cliente_id'])) {
            $sql .= " AND cliente_id = :cliente_id";
            $params[':cliente_id'] = $filters['cliente_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['data_inicial'])) {
            $sql .= " AND vencimento >= :data_inicial";
            $params[':data_inicial'] = $filters['data_inicial'];
        }
        if (!empty($filters['data_final'])) {
            $sql .= " AND vencimento <= :data_final";
            $params[':data_final'] = $filters['data_final'];
        }
        $sql .= " ORDER BY vencimento DESC";
        $stmt = $db->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new Exception('Erro ao buscar cobranças: ' . implode(' | ', $stmt->errorInfo()));
        }
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $c = new self();
            foreach ($row as $k => $v) $c->$k = $v;
            $result[] = $c;
        }
        return $result;
    }

    /**
     * Busca uma cobrança por ID.
     * @param int $id
     * @return Cobranca|null
     * @throws Exception
     */
    public static function find(int $id): ?Cobranca
    {
        $db = \Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cobrancas WHERE id = :id LIMIT 1");
        if (!$stmt->execute([':id' => $id])) {
            throw new Exception('Erro ao buscar cobrança: ' . implode(' | ', $stmt->errorInfo()));
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $c = new self();
        foreach ($row as $k => $v) $c->$k = $v;
        return $c;
    }

    /**
     * Insere novo registro (quando id for null).
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        if ($this->id) {
            throw new Exception('Cobrança já possui ID, use update()');
        }
        $db = \Database::getInstance();
        $sql = "INSERT INTO cobrancas (asaas_payment_id, cliente_id, valor, status, vencimento, data_criacao, data_pagamento, descricao, tipo, url_fatura, parcela, assinatura_id)
                VALUES (:asaas_payment_id, :cliente_id, :valor, :status, :vencimento, :data_criacao, :data_pagamento, :descricao, :tipo, :url_fatura, :parcela, :assinatura_id)";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute([
            ':asaas_payment_id' => $this->asaas_payment_id,
            ':cliente_id'       => $this->cliente_id,
            ':valor'            => $this->valor,
            ':status'           => $this->status,
            ':vencimento'       => $this->vencimento,
            ':data_criacao'     => $this->data_criacao,
            ':data_pagamento'   => $this->data_pagamento,
            ':descricao'        => $this->descricao,
            ':tipo'             => $this->tipo,
            ':url_fatura'       => $this->url_fatura,
            ':parcela'          => $this->parcela,
            ':assinatura_id'    => $this->assinatura_id,
        ]);
        if (!$ok) {
            throw new Exception('Erro ao inserir cobrança: ' . implode(' | ', $stmt->errorInfo()));
        }
        $this->id = $db->lastInsertId();
        return true;
    }

    /**
     * Atualiza campos passados no DB e nas propriedades do objeto.
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function update(array $data): bool
    {
        if (!$this->id) {
            throw new Exception('Cobrança não possui ID para update');
        }
        $db = \Database::getInstance();
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = :$k";
            $params[":$k"] = $v;
        }
        $params[':id'] = $this->id;
        $sql = "UPDATE cobrancas SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);
        if (!$ok) {
            throw new Exception('Erro ao atualizar cobrança: ' . implode(' | ', $stmt->errorInfo()));
        }
        foreach ($data as $k => $v) $this->$k = $v;
        return true;
    }

    /**
     * Atualiza apenas o campo status.
     * @param string $status
     * @return bool
     * @throws Exception
     */
    public function updateStatus(string $status): bool
    {
        return $this->update(['status' => $status]);
    }

    /**
     * Remove o registro do DB.
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception('Cobrança não possui ID para delete');
        }
        $db = \Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cobrancas WHERE id = :id");
        $ok = $stmt->execute([':id' => $this->id]);
        if (!$ok) {
            throw new Exception('Erro ao remover cobrança: ' . implode(' | ', $stmt->errorInfo()));
        }
        return true;
    }
} 