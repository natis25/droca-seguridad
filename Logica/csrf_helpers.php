<?php
/**
 * CSRF Protection Helpers
 * 
 * Funciones centralizadas para protección Anti-CSRF en toda la aplicación.
 * Basado en las recomendaciones de OWASP para prevenir ataques CSRF.
 */

/**
 * Inicializa la sesión de forma segura si no está activa
 */
function csrf_init_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

/**
 * Genera un token CSRF único si no existe en la sesión
 * 
 * @return string El token CSRF generado o existente
 */
function csrf_generate_token()
{
    csrf_init_session();

    if (empty($_SESSION['csrf'])) {
        try {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Fallback para versiones antiguas de PHP
            $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }

    return $_SESSION['csrf'];
}

/**
 * Obtiene el token CSRF actual de la sesión
 * 
 * @return string El token CSRF o cadena vacía si no existe
 */
function csrf_get_token()
{
    csrf_init_session();
    return $_SESSION['csrf'] ?? '';
}

/**
 * Renderiza un campo hidden HTML con el token CSRF
 * 
 * @return string HTML del campo hidden
 */
function csrf_field()
{
    $token = csrf_generate_token();
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida el token CSRF recibido en una petición POST
 * 
 * @param string|null $redirect_url URL a la que redirigir en caso de error (opcional)
 * @param string|null $error_message Mensaje de error personalizado (opcional)
 * @return bool True si el token es válido, false en caso contrario
 */
function csrf_validate($redirect_url = null, $error_message = null)
{
    csrf_init_session();

    $session_token = $_SESSION['csrf'] ?? '';
    $post_token = $_POST['csrf'] ?? '';

    $is_valid = hash_equals($session_token, $post_token);

    if (!$is_valid && $redirect_url !== null) {
        $error_message = $error_message ?? 'Token CSRF inválido. Por favor, intenta nuevamente.';
        $_SESSION['error'] = $error_message;
        header("Location: {$redirect_url}");
        exit();
    }

    return $is_valid;
}

/**
 * Valida el token CSRF y termina la ejecución si es inválido
 * Útil para procesadores que siempre deben validar CSRF
 * 
 * @param string $redirect_url URL a la que redirigir en caso de error
 * @param string|null $error_message Mensaje de error personalizado (opcional)
 */
function csrf_validate_or_die($redirect_url, $error_message = null)
{
    if (!csrf_validate($redirect_url, $error_message)) {
        exit();
    }
}

/**
 * Regenera el token CSRF (útil después de login o cambios importantes)
 */
function csrf_regenerate_token()
{
    csrf_init_session();
    try {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}
