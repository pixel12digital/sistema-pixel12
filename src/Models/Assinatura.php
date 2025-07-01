<?php
class Assinatura {
    public static function todas($mysqli) {
        $result = $mysqli->query("SELECT * FROM assinaturas");
        $assinaturas = [];
        while ($row = $result->fetch_assoc()) {
            $assinaturas[] = $row;
        }
        return $assinaturas;
    }
    public static function buscarPorId($mysqli, $id) {
        $stmt = $mysqli->prepare("SELECT * FROM assinaturas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
} 