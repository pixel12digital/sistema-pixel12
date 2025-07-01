<?php
class Fatura {
    public static function todas($mysqli) {
        $result = $mysqli->query("SELECT * FROM faturas");
        $faturas = [];
        while ($row = $result->fetch_assoc()) {
            $faturas[] = $row;
        }
        return $faturas;
    }
    public static function paginadas($mysqli, $limite, $offset) {
        $stmt = $mysqli->prepare("SELECT * FROM faturas ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $limite, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $faturas = [];
        while ($row = $result->fetch_assoc()) {
            $faturas[] = $row;
        }
        $stmt->close();
        return $faturas;
    }
    public static function paginadasComFiltro($mysqli, $limite, $offset, $status, $date_from, $date_to) {
        $sql = "SELECT * FROM faturas WHERE 1=1";
        $params = [];
        $types = '';
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($date_from) {
            $sql .= " AND due_date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        if ($date_to) {
            $sql .= " AND due_date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $faturas = [];
        while ($row = $result->fetch_assoc()) {
            $faturas[] = $row;
        }
        $stmt->close();
        return $faturas;
    }
    public static function total($mysqli) {
        $result = $mysqli->query("SELECT COUNT(*) as total FROM faturas");
        $row = $result->fetch_assoc();
        return intval($row['total']);
    }
    public static function totalComFiltro($mysqli, $status, $date_from, $date_to) {
        $sql = "SELECT COUNT(*) as total FROM faturas WHERE 1=1";
        $params = [];
        $types = '';
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($date_from) {
            $sql .= " AND due_date >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        if ($date_to) {
            $sql .= " AND due_date <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        $stmt = $mysqli->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return intval($row['total']);
    }
    public static function buscarPorId($mysqli, $id) {
        $stmt = $mysqli->prepare("SELECT * FROM faturas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public static function buscarPorAsaasId($mysqli, $asaas_id) {
        $stmt = $mysqli->prepare("SELECT * FROM faturas WHERE asaas_id = ?");
        $stmt->bind_param('s', $asaas_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public static function atualizarStatus($mysqli, $asaas_id, $status, $updated_at = null) {
        if ($updated_at === null) {
            $updated_at = date('Y-m-d H:i:s');
        }
        $stmt = $mysqli->prepare("UPDATE faturas SET status = ?, updated_at = ? WHERE asaas_id = ?");
        $stmt->bind_param('sss', $status, $updated_at, $asaas_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
} 