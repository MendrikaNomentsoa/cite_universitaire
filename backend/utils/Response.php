<?php
// utils/Response.php

class Response {
    public static function success(mixed $data = null, string $message = 'Succès'): void {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}