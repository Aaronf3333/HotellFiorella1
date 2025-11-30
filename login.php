<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Si ya hay una sesión, redirigir al panel correspondiente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 1) { // Administrador
        header('Location: admin/');
    } else { // Cliente
        header('Location: client/');
    }
    exit();
}
include('includes/header_public.php'); 
?>

<style>
    /* Variables y reset */
    :root {
        --primary-gradient: linear-gradient(135deg, #4ecca3 0%, #3ba886 100%);
        --secondary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --card-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        --hover-shadow: 0 25px 70px rgba(78, 204, 163, 0.2);
    }

    /* Contenedor principal */
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }

    /* Decoración de fondo */
    .login-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(78, 204, 163, 0.05) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Card de login */
    .login-box {
        background: white;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        padding: 3rem;
        width: 100%;
        max-width: 450px;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.6s ease;
        transition: all 0.3s ease;
    }

    .login-box:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header del login */
    .login-box h2 {
        text-align: center;
        margin-bottom: 2rem;
        font-size: 2rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        padding-bottom: 1rem;
    }

    .login-box h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .login-box h2 i {
        display: block;
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #4ecca3;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* Mensajes de notificación */
    .alert-message {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        animation: slideInDown 0.5s ease;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-message i {
        font-size: 1.3rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #e7f8f3 0%, #d4f1e8 100%);
        border: 2px solid #4ecca3;
        color: #0f5132;
    }

    .alert-info i {
        color: #4ecca3;
    }

    .alert-error {
        background: linear-gradient(135deg, #ffe7e7 0%, #ffd4d4 100%);
        border: 2px solid #dc3545;
        color: #721c24;
    }

    .alert-error i {
        color: #dc3545;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 2px solid #28a745;
        color: #155724;
    }

    .alert-success i {
        color: #28a745;
    }

    /* Formulario */
    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-group label i {
        color: #4ecca3;
        font-size: 1.1rem;
    }

    .form-group input {
        width: 100%;
        padding: 0.9rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-group input:focus {
        outline: none;
        border-color: #4ecca3;
        box-shadow: 0 0 0 0.25rem rgba(78, 204, 163, 0.15);
        background: #f8fff8;
    }

    .form-group input::placeholder {
        color: #adb5bd;
    }

    /* Botón de submit */
    .btn-primary {
        width: 100%;
        padding: 1rem 2rem;
        background: var(--primary-gradient);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(78, 204, 163, 0.3);
        position: relative;
        overflow: hidden;
        margin-top: 1rem;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(78, 204, 163, 0.4);
    }

    .btn-primary:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-primary:active {
        transform: translateY(-1px);
    }

    .btn-primary i {
        margin-right: 8px;
    }

    /* Links */
    .links {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #f0f0f0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .links a {
        color: #4ecca3;
        text-decoration: none;
        font-weight: 600;
        text-align: center;
        padding: 0.8rem;
        border-radius: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: rgba(78, 204, 163, 0.05);
        position: relative;
        overflow: hidden;
    }

    .links a::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 3px;
        background: var(--primary-gradient);
        transition: width 0.3s ease;
    }

    .links a:hover {
        background: rgba(78, 204, 163, 0.1);
        color: #3ba886;
        transform: translateX(5px);
    }

    .links a:hover::before {
        width: 100%;
    }

    .links a i {
        font-size: 1.1rem;
    }

    /* Decoración adicional */
    .login-box::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(78, 204, 163, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: -1;
    }

    .login-box::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(78, 204, 163, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: -1;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .login-box {
            padding: 2rem 1.5rem;
            margin: 20px;
        }

        .login-box h2 {
            font-size: 1.5rem;
        }

        .login-box h2 i {
            font-size: 2.5rem;
        }

        .btn-primary {
            font-size: 1rem;
            padding: 0.9rem 1.5rem;
        }

        .alert-message {
            padding: 0.8rem 1rem;
            font-size: 0.9rem;
        }
    }

    /* Efecto de carga en el botón */
    .btn-primary.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-primary.loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid white;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<div class="login-container">
    <div class="login-box">
        <h2>
            <i class="fas fa-hotel"></i>
            Iniciar Sesión
        </h2>
        
        <?php if(isset($_GET['notice']) && $_GET['notice'] == 'login_required'): ?>
            <div class="alert-message alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Por favor, inicia sesión o regístrate para poder hacer una reserva.</span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert-message alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>El correo o la contraseña son incorrectos.</span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert-message alert-success">
                <i class="fas fa-check-circle"></i>
                <span>¡Registro exitoso! Por favor, inicia sesión.</span>
            </div>
        <?php endif; ?>

        <form action="actions/login_process.php" method="POST" id="loginForm">
            <?php if (isset($_GET['redirect_to'])): ?>
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_GET['redirect_to']); ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Correo Electrónico
                </label>
                <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Contraseña
                </label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-primary" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i>
                Ingresar
            </button>
        </form>
        
        <div class="links">
            <a href="register.php">
                <i class="fas fa-user-plus"></i>
                ¿No tienes cuenta? Regístrate aquí
            </a>
            <a href="https://media.tenor.com/aSkdq3IU0g0AAAAM/laughing-cat.gif" target="_blank">
                <i class="fas fa-question-circle"></i>
                Olvidé mi contraseña
            </a>
        </div>
    </div>
</div>

<script>
    // Efecto de carga en el botón al enviar
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';
    });

    // Animación de entrada en los inputs
    document.querySelectorAll('.form-group input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Auto-cerrar alertas después de 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-message');
        alerts.forEach(alert => {
            alert.style.animation = 'slideOutUp 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Animación de salida
    const keyframes = `
        @keyframes slideOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    `;
    const style = document.createElement('style');
    style.textContent = keyframes;
    document.head.appendChild(style);
</script>

<?php include('includes/footer.php'); ?>