<?php

$captcha_response = $_POST["g-recaptcha-response"] ?? ''; // Usamos ?? '' para evitar error si no viene
$secretKey = $_ENV["RECAPTCHA_SECRET_KEY"] ?? getenv('RECAPTCHA_SECRET_KEY');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret' => $secretKey,
    'response' => $captcha_response
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 

$response = curl_exec($ch);

if ($response === false) {
    // En lugar de echo, logueamos el error en el servidor
    error_log("Error cURL en CAPTCHA: " . curl_error($ch));
    $captcha_result = ['success' => false];
} else {
    $captcha_result = json_decode($response, true);
}

curl_close($ch);
