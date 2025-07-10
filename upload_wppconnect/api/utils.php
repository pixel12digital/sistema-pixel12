<?php
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function get_json_input() {
    return json_decode(file_get_contents('php://input'), true);
} 