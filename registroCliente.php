<?php
// 1. Configuración de Sesión Segura
// Estas líneas deben ir ANTES de cualquier HTML o espacio en blanco.
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Generar Token CSRF si no existe
if (empty($_SESSION['csrf'])) {
    try {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// 3. Configuración de Seguridad (CSP y Headers)
// Generamos un "nonce" aleatorio para permitir solo nuestros scripts
try {
    $CSP_NONCE = base64_encode(random_bytes(16));
} catch (Exception $e) {
    $CSP_NONCE = base64_encode(openssl_random_pseudo_bytes(16));
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content Security Policy (CSP) en una sola línea concatenada
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "img-src 'self' data:; " .
    "style-src 'self' https://fonts.googleapis.com https://cdn.jsdelivr.net 'nonce-{$CSP_NONCE}'; " .
    "font-src https://fonts.gstatic.com; " .
    "script-src 'self' https://cdn.jsdelivr.net 'nonce-{$CSP_NONCE}';"
);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta de Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style nonce="<?= $CSP_NONCE ?>">
        :root {
            --brand: #4b41d9;
        }

        * {
            font-family: Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif;
        }

        .card-lite {
            border: 1px solid #eee;
            border-radius: 16px;
        }

        .pw-meter {
            height: 6px;
            border-radius: 6px;
            background: #eee;
            overflow: hidden;
        }

        .pw-meter>span {
            display: block;
            height: 100%;
            width: 0;
            background: var(--brand);
            transition: width .2s;
        }

        .req-list li {
            margin: .15rem 0;
        }

        .req-bad {
            color: #b42318;
        }

        .req-ok {
            color: #16794f;
        }

        details.tip {
            border: 1px dashed #d0d0ff;
            border-radius: 10px;
            padding: .75rem 1rem;
            background: #f8f9ff;
        }

        details.tip summary {
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-light">

    <header class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <span class="text-primary">Inmobiliaria</span>Segura
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <nav id="nav" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="inmuebles.php">Propiedades</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary ms-2" href="login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container my-5" style="max-width:880px">
        <div class="card card-lite p-4 p-md-5 mx-auto shadow-sm">
            <h1 class="h3 fw-bold text-center mb-2">Crear Cuenta de Cliente</h1>
            <p class="text-center text-muted mb-4">Regístrate para explorar propiedades y gestionar tus citas.</p>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><strong>Error:</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php elseif (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <div class="alert alert-info d-flex align-items-center gap-2 small">
                <div>
                    Registra tus datos tal como aparecen en tu documento. El <strong>usuario</strong> se generará
                    automáticamente.
                </div>
            </div>

            <form method="post" action="Logica/procesarRegistroCliente.php" novalidate autocomplete="off">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre</label>
                        <input name="nombre" maxlength="100" class="form-control" placeholder="Ej.: Juan Carlos"
                            required autocomplete="given-name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Primer Apellido</label>
                        <input name="apellido" maxlength="100" class="form-control" placeholder="Ej.: Pérez" required
                            autocomplete="family-name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Segundo Apellido</label>
                        <input name="apellido2" maxlength="100" class="form-control" placeholder="Ej.: Gómez" required
                            autocomplete="additional-name">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Usuario (Automático)</label>
                        <input name="usuario" id="usuario" class="form-control bg-light" readonly
                            placeholder="Se generará al escribir nombre..." tabindex="-1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo Electrónico</label>
                        <input name="correo" type="email" maxlength="100" class="form-control"
                            placeholder="nombre@ejemplo.com" required autocomplete="email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input name="telefono" pattern="\d{8}" maxlength="8" class="form-control" placeholder="12345678"
                            required inputmode="numeric">
                        <small class="text-muted" style="font-size:0.8em">Solo 8 dígitos numéricos.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input name="direccion" maxlength="150" class="form-control" placeholder="Calle, número, ciudad"
                            required autocomplete="street-address">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Contraseña</label>
                        <input name="password" id="password" type="password" class="form-control"
                            placeholder="Crea una contraseña segura" required autocomplete="new-password">

                        <div class="d-flex align-items-center mt-2 gap-2">
                            <div class="pw-meter flex-grow-1"><span id="pwBar"></span></div>
                            <small id="pwLabel" class="text-muted">Muy débil</small>
                        </div>

                        <ul class="req-list small mt-2 text-muted ps-0" style="list-style:none">
                            <li id="req_len" class="req-bad">Minimum 12 caracteres</li>
                            <li id="req_may" class="req-bad">Una letra mayúscula</li>
                            <li id="req_min" class="req-bad">Una letra minúscula</li>
                            <li id="req_num" class="req-bad">Un número</li>
                            <li id="req_sym" class="req-bad">Un símbolo (ej: ! @ #)</li>
                        </ul>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="togglePass">
                            <label class="form-check-label small" for="togglePass">Mostrar contraseña</label>
                        </div>

                        <details class="tip mt-3 small">
                            <summary>💡 Tip: Cómo crear una contraseña fuerte</summary>
                            <div class="mt-2">
                                Usa una frase que recuerdes y toma las iniciales. <br>
                                <em>"Me gusta Programar en PHP 2025"</em> -> <strong>MgPePHP-2025!</strong>
                            </div>
                        </details>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input name="password2" id="password2" type="password" class="form-control"
                            placeholder="Repite la contraseña" required>
                        <div id="match-feedback" class="form-text text-danger d-none">Las contraseñas no coinciden.
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button class="btn btn-primary w-100 py-2 fw-bold" type="submit">Crear Cuenta</button>
                    </div>
                </div>

                <div id="form-feedback" class="mt-3"></div>
            </form>

            <p class="text-center mt-3 mb-0 small">¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
        </div>
    </main>

    <footer class="py-4 mt-5 border-top bg-white">
        <div class="container text-center text-muted small">
            © <?= date('Y') ?> Inmobiliaria Segura. Todos los derechos reservados.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        nonce="<?= $CSP_NONCE ?>"></script>
    <script nonce="<?= $CSP_NONCE ?>">
        // 1. Lógica de Usuario Automático
        const inputsName = ['nombre', 'apellido', 'apellido2'].map(name => document.querySelector(`input[name="${name}"]`));
        const userInput = document.getElementById('usuario');

        function cleanStr(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z0-9]/g, "");
        }

        function updateUsername() {
            const n = cleanStr(inputsName[0].value.split(' ')[0] || '');
            const ap1 = cleanStr(inputsName[1].value.split(' ')[0] || '');

            if (n && ap1) {
                userInput.value = `${n}.${ap1}`;
            } else {
                userInput.value = '';
            }
        }
        inputsName.forEach(input => input.addEventListener('input', updateUsername));

        // 2. Medidor de Contraseña
        const pwd = document.getElementById('password');
        const pwd2 = document.getElementById('password2');
        const pwBar = document.getElementById('pwBar');
        const pwLabel = document.getElementById('pwLabel');
        const reqs = {
            len: document.getElementById('req_len'),
            may: document.getElementById('req_may'),
            min: document.getElementById('req_min'),
            num: document.getElementById('req_num'),
            sym: document.getElementById('req_sym')
        };

        pwd.addEventListener('input', () => {
            const v = pwd.value;
            const checks = {
                len: v.length >= 12,
                may: /[A-Z]/.test(v),
                min: /[a-z]/.test(v),
                num: /\d/.test(v),
                sym: /[^A-Za-z0-9]/.test(v)
            };

            let score = 0;
            for (const key in checks) {
                if (checks[key]) {
                    score++;
                    reqs[key].className = 'req-ok fw-bold';
                    reqs[key].innerHTML = '✓ ' + reqs[key].innerText.replace('✓ ', '');
                } else {
                    reqs[key].className = 'req-bad';
                    reqs[key].innerHTML = reqs[key].innerText.replace('✓ ', '');
                }
            }

            const width = (score / 5) * 100;
            pwBar.style.width = `${width}%`;

            const labels = ['Muy débil', 'Débil', 'Mejorando', 'Casi lista', 'Buena', 'Excelente'];
            pwLabel.innerText = labels[score];
        });

        // 3. Mostrar/Ocultar Pass
        document.getElementById('togglePass').addEventListener('change', function () {
            const type = this.checked ? 'text' : 'password';
            pwd.type = type;
            pwd2.type = type;
        });

        // 4. Validación básica al enviar
        document.querySelector('form').addEventListener('submit', function (e) {
            if (pwd.value !== pwd2.value) {
                e.preventDefault();
                document.getElementById('match-feedback').classList.remove('d-none');
                pwd2.classList.add('is-invalid');
            } else {
                document.getElementById('match-feedback').classList.add('d-none');
                pwd2.classList.remove('is-invalid');
            }
        });
    </script>

</body>

</html>