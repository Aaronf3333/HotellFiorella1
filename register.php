<?php include('includes/header_public.php'); ?>

<style>
    /* Variables y reset */
    :root {
        --primary-gradient: linear-gradient(135deg, #4ecca3 0%, #3ba886 100%);
        --secondary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --card-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 25px 70px rgba(78, 204, 163, 0.15);
        --input-border: #e0e0e0;
        --text-muted: #6c757d;
    }

    /* Container principal */
    .registro-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 60px 20px;
    }

    /* Card principal mejorado */
    .card-registro {
        border: none;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        background: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-registro:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }

    /* Header del card */
    .card-header-registro {
        background: var(--primary-gradient);
        color: white;
        padding: 2rem 1.5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .card-header-registro::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .card-header-registro h3 {
        position: relative;
        z-index: 1;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-header-registro p {
        position: relative;
        z-index: 1;
        margin: 0;
        font-size: 1rem;
    }

    /* Selector de tipo de cuenta */
    .selector-wrapper {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        border: 2px dashed #dee2e6;
    }

    .selector-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #495057;
        margin-bottom: 1rem;
        display: block;
        text-align: center;
    }

    .selector-group {
        display: flex;
        gap: 15px;
    }

    .selector-group .btn-outline-primary {
        flex: 1;
        padding: 1.5rem 1rem;
        border: 2px solid #dee2e6;
        background: white;
        color: #495057;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }

    .selector-group .btn-outline-primary i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
        transition: transform 0.3s ease;
    }

    .selector-group .btn-outline-primary:hover {
        background: #f8f9fa;
        border-color: #4ecca3;
        color: #4ecca3;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(78, 204, 163, 0.15);
    }

    .selector-group .btn-outline-primary:hover i {
        transform: scale(1.1);
    }

    .selector-group .btn-check:checked + .btn-outline-primary {
        background: var(--primary-gradient);
        color: white;
        border-color: #4ecca3;
        box-shadow: 0 10px 25px rgba(78, 204, 163, 0.3);
        transform: translateY(-3px);
    }

    .selector-group .btn-check:checked + .btn-outline-primary i {
        transform: scale(1.15);
    }

    /* Secciones del formulario */
    .form-section-heading {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #4ecca3;
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 3px solid #4ecca3;
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section-heading::before {
        content: '';
        width: 4px;
        height: 100%;
        background: var(--primary-gradient);
        position: absolute;
        left: -1.5rem;
        border-radius: 2px;
    }

    /* Inputs flotantes mejorados */
    .form-floating {
        position: relative;
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        border: 2px solid var(--input-border);
        border-radius: 10px;
        padding: 1rem 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-floating > .form-control:focus,
    .form-floating > .form-select:focus {
        border-color: #4ecca3;
        box-shadow: 0 0 0 0.25rem rgba(78, 204, 163, 0.15);
        background: #f8fff8;
    }

    .form-floating > label {
        padding: 1rem 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label,
    .form-floating > .form-select ~ label {
        color: #4ecca3;
        font-weight: 600;
    }

    /* Alert personalizado */
    .alert-info {
        background: linear-gradient(135deg, #e7f8f3 0%, #d4f1e8 100%);
        border: 2px solid #4ecca3;
        border-radius: 10px;
        color: #0f5132;
        padding: 1rem;
    }

    .alert-info i {
        color: #4ecca3;
        font-size: 1.2rem;
    }

    /* Botón de submit mejorado */
    .btn-submit {
        background: var(--primary-gradient);
        border: none;
        border-radius: 15px;
        padding: 1.2rem 2rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 10px 30px rgba(78, 204, 163, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-submit::before {
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

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(78, 204, 163, 0.4);
    }

    .btn-submit:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-submit:active {
        transform: translateY(-1px);
    }

    /* Link de login */
    .login-link {
        margin-top: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 10px;
        text-align: center;
    }

    .login-link a {
        color: #4ecca3;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }

    .login-link a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #4ecca3;
        transition: width 0.3s ease;
    }

    .login-link a:hover::after {
        width: 100%;
    }

    .login-link a:hover {
        color: #3ba886;
    }

    /* Animaciones */
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

    .card-registro {
        animation: fadeInUp 0.6s ease;
    }

    #form-persona,
    #form-empresa {
        animation: fadeInUp 0.4s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .registro-container {
            padding: 40px 15px;
        }

        .card-header-registro h3 {
            font-size: 1.5rem;
        }

        .selector-group {
            flex-direction: column;
        }

        .selector-group .btn-outline-primary {
            padding: 1.2rem 1rem;
        }

        .form-section-heading::before {
            left: -1rem;
        }

        .btn-submit {
            font-size: 1rem;
            padding: 1rem 1.5rem;
        }
    }

    /* Estados de validación */
    .form-control:invalid:not(:placeholder-shown) {
        border-color: #dc3545;
    }

    .form-control:valid:not(:placeholder-shown) {
        border-color: #4ecca3;
    }

    /* Tooltips personalizados */
    [data-tooltip] {
        position: relative;
        cursor: help;
    }

    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background: #1a1a2e;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        z-index: 1000;
    }

    [data-tooltip]:hover::after {
        opacity: 1;
    }
</style>

<div class="registro-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="card card-registro">
                    <div class="card-header-registro">
                        <h3 class="mb-1 fw-bold">
                            <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                        </h3>
                        <p class="mb-0 opacity-90">Complete el formulario para registrarse en Hotel Fiorella</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="selector-wrapper">
                            <label class="selector-label">
                                <i class="fas fa-user-tag me-2"></i>Seleccione tipo de cuenta
                            </label>
                            <div class="selector-group">
                                <input type="radio" class="btn-check" name="btnradio" id="btnPersona" autocomplete="off" checked onclick="toggleForm('persona')">
                                <label class="btn btn-outline-primary" for="btnPersona">
                                    <i class="fas fa-user"></i>
                                    <div>Persona Natural</div>
                                </label>

                                <input type="radio" class="btn-check" name="btnradio" id="btnEmpresa" autocomplete="off" onclick="toggleForm('empresa')">
                                <label class="btn btn-outline-primary" for="btnEmpresa">
                                    <i class="fas fa-building"></i>
                                    <div>Empresa (RUC)</div>
                                </label>
                            </div>
                        </div>

                        <form action="actions/register_process.php" method="POST" id="registerForm">
                            <input type="hidden" name="tipo_registro" id="tipo_registro" value="persona">

                            <div id="form-persona">
                                <div class="form-section-heading">
                                    <i class="fas fa-id-card"></i>
                                    Datos Personales
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="nombres" id="nombres" placeholder="Nombres">
                                            <label><i class="fas fa-user me-2"></i>Nombres *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="ape_paterno" id="ape_paterno" placeholder="Apellido Paterno">
                                            <label><i class="fas fa-user me-2"></i>Apellido Paterno *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="ape_materno" id="ape_materno" placeholder="Apellido Materno">
                                            <label><i class="fas fa-user me-2"></i>Apellido Materno</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="doc_identidad" id="doc_identidad" placeholder="DNI" maxlength="8" pattern="[0-9]{8}">
                                            <label><i class="fas fa-id-card me-2"></i>DNI / Documento *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <?php $fecha_maxima = date('Y-m-d', strtotime('-18 years')); ?>
                                            <input type="date" class="form-control" name="fec_nacimiento" id="fec_nacimiento" max="<?php echo $fecha_maxima; ?>">
                                            <label><i class="fas fa-calendar me-2"></i>Fecha de Nacimiento *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" name="estado_civil" id="estado_civil">
                                                <option value="Soltero/a">Soltero/a</option>
                                                <option value="Casado/a">Casado/a</option>
                                                <option value="Divorciado/a">Divorciado/a</option>
                                                <option value="Viudo/a">Viudo/a</option>
                                            </select>
                                            <label><i class="fas fa-heart me-2"></i>Estado Civil *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" name="genero">
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                            <label><i class="fas fa-venus-mars me-2"></i>Género</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="celular" placeholder="Celular" maxlength="9" pattern="[0-9]{9}">
                                            <label><i class="fas fa-phone me-2"></i>Celular</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="direccion_persona" placeholder="Dirección">
                                            <label><i class="fas fa-map-marker-alt me-2"></i>Dirección Domiciliaria</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" name="correo" id="correo" placeholder="Email">
                                            <label><i class="fas fa-envelope me-2"></i>Correo Electrónico (Será su Usuario) *</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="form-empresa" style="display: none;">
                                <div class="form-section-heading">
                                    <i class="fas fa-briefcase"></i>
                                    Datos Corporativos
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="ruc" id="ruc" placeholder="RUC" maxlength="11" pattern="[0-9]{11}">
                                            <label><i class="fas fa-file-invoice me-2"></i>R.U.C. (11 dígitos) *</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="razon_social" id="razon_social" placeholder="Razón Social">
                                            <label><i class="fas fa-building me-2"></i>Razón Social *</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="direccion_empresa" id="direccion_empresa" placeholder="Dirección Fiscal">
                                            <label><i class="fas fa-map-marked-alt me-2"></i>Dirección Fiscal *</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" name="telefono_empresa" placeholder="Teléfono" maxlength="9" pattern="[0-9]{9}">
                                            <label><i class="fas fa-phone-alt me-2"></i>Teléfono de Contacto</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" name="correo_empresa" id="correo_empresa" placeholder="Email Empresa">
                                            <label><i class="fas fa-envelope-open-text me-2"></i>Correo de la Empresa (Usuario) *</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-heading mt-4">
                                <i class="fas fa-lock"></i>
                                Seguridad
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong> Su nombre de usuario será su <strong>Correo Electrónico</strong>.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" name="contrasena" id="contrasena" placeholder="Contraseña" required minlength="6">
                                        <label><i class="fas fa-key me-2"></i>Contraseña (mínimo 6 caracteres) *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-user-check me-2"></i>
                                    Registrarme Ahora
                                </button>
                            </div>
                            
                            <div class="login-link">
                                <p class="text-muted mb-0">
                                    ¿Ya tienes cuenta? 
                                    <a href="login.php">
                                        <i class="fas fa-sign-in-alt me-1"></i>Inicia Sesión
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleForm(tipo) {
    document.getElementById('tipo_registro').value = tipo;
    
    const formPersona = document.getElementById('form-persona');
    const formEmpresa = document.getElementById('form-empresa');
    
    const reqPersona = ['nombres', 'ape_paterno', 'doc_identidad', 'fec_nacimiento', 'estado_civil', 'correo'];
    const reqEmpresa = ['ruc', 'razon_social', 'direccion_empresa', 'correo_empresa'];

    if (tipo === 'persona') {
        formPersona.style.display = 'block';
        formEmpresa.style.display = 'none';
        
        reqPersona.forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.required = true;
        });
        reqEmpresa.forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.required = false;
        });
    } else {
        formPersona.style.display = 'none';
        formEmpresa.style.display = 'block';
        
        reqPersona.forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.required = false;
        });
        reqEmpresa.forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.required = true;
        });
    }
}

// Inicializar formulario
toggleForm('persona');

// Validación en tiempo real
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('contrasena');
    if (password.value.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        password.focus();
    }
});

// Validación de DNI
const dniInput = document.getElementById('doc_identidad');
if (dniInput) {
    dniInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
    });
}

// Validación de RUC
const rucInput = document.getElementById('ruc');
if (rucInput) {
    rucInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
}

// Validación de celular
document.querySelectorAll('input[name="celular"], input[name="telefono_empresa"]').forEach(input => {
    input.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
    });
});
</script>

<?php include('includes/footer.php'); ?>