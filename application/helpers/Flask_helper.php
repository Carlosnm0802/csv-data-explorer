<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Llama al endpoint /analyze de Flask
 * y devuelve el array de resultados o FALSE si falla
 */
function call_flask_analyze($filepath) {
    $flask_url = 'http://localhost:5000/analyze';

    $payload = json_encode(['filepath' => $filepath]);

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST,           TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || $response === FALSE) {
        return FALSE;
    }

    return json_decode($response, TRUE);
}