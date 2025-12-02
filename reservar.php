<?php
include('includes/db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si no hay una sesión de usuario activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?notice=login_required&redirect_to=reservar.php');
    exit(); 
}

// ===================================================
// 🛑 LÓGICA DE VALIDACIÓN (PHP) ANTES DE REDIRIGIR A PAGO (SE MANTIENE IGUAL)
// ===================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_entrada = $_POST['fecha_entrada'] ?? null;
    $fecha_salida = $_POST['fecha_salida'] ?? null;
    $error_message = null; 

    if ($fecha_entrada && $fecha_salida) {
        try {
            $f_ingreso = new DateTime($fecha_entrada);
            $f_salida = new DateTime($fecha_salida);
            $hoy = new DateTime('today');

            $f_salida_sin_hora = clone $f_salida;
            $f_salida_sin_hora->setTime(0, 0, 0); 

            // 1. Validar Fechas Pasadas
            if ($f_salida_sin_hora < $hoy) {
                $error_message = "❌ La fecha de salida no puede ser anterior al día de hoy.";
            }

            // 2. Validar Duración Mínima
            $f_ingreso_mas_un_dia = clone $f_ingreso;
            $f_ingreso_mas_un_dia->modify('+1 day');

            if ($f_salida < $f_ingreso_mas_un_dia) {
                $error_message = "📅 Debe reservar por lo menos una noche (la fecha de salida debe ser al día siguiente o posterior a la entrada).";
            }
            
        } catch (Exception $e) {
            $error_message = "🚨 Error de formato de fecha. Por favor, revisa tus fechas.";
        }
    } else {
        $error_message = "⚠️ Por favor, complete todos los campos de fecha y seleccione una habitación.";
    }

    if ($error_message) {
        $_SESSION['toast_error'] = $error_message;
    } 
}
// ===================================================
// 🟢 FIN DE LA VALIDACIÓN PHP 🟢
// ===================================================


// Obtenemos el nombre de la persona y cargamos datos (SE MANTIENE IGUAL)
try {
    $sql_nombre = "SELECT p.Nombres FROM Persona p JOIN Clientes c ON p.PersonaID = c.PersonaID WHERE c.ClienteID = ?";
    $stmt_nombre = $pdo->prepare($sql_nombre);
    $stmt_nombre->execute([$_SESSION['cliente_id']]); 
    $resultado = $stmt_nombre->fetch(PDO::FETCH_ASSOC);
    $nombre_persona = $resultado ? $resultado['Nombres'] : 'Cliente';
    $_SESSION['nombre_persona'] = $nombre_persona;
} catch (PDOException $e) {
    $nombre_persona = 'Cliente';
}

$habitaciones_sql = "SELECT h.HabitacionID, h.NumeroHabitacion, th.N_TipoHabitacion, h.PrecioPorNoche FROM Habitaciones h JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID WHERE h.Estado_HabitacionID = 1";
$stmt_hab = $pdo->query($habitaciones_sql);
$habitaciones_disponibles = $stmt_hab->fetchAll(PDO::FETCH_ASSOC);

$metodos_pago_sql = "SELECT MetodoPagoID, NombreMetodo FROM MetodosPago WHERE Estado = '1'";
$stmt_mp = $pdo->query($metodos_pago_sql);
$metodos_pago = $stmt_mp->fetchAll(PDO::FETCH_ASSOC);


// 3. Incluimos el encabezado HTML
include('includes/header_public.php');
?>
<style>
    /* Estilos del contenedor principal */
    .form-container { display: flex; justify-content: center; padding: 40px 0; }
    .form-box { padding: 40px; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border-radius: 12px; width: 600px; max-width: 90%; }
    .form-box h2 { 
        text-align: center; 
        color: var(--primary-color); 
        margin-bottom: 30px; 
        border-bottom: 2px solid var(--accent-color);
        padding-bottom: 10px;
    }
    .form-box p {
        text-align: center;
        margin-bottom: 20px;
        color: #555;
    }
    
    /* MEJORA DE LOS SELECTORES Y CAMPOS DE ENTRADA */
    .form-group label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
        color: #333;
    }
    .form-group input[type="date"],
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box; /* Crucial para padding */
        transition: border-color 0.3s, box-shadow 0.3s;
        /* Estilo para parecerse a Bootstrap/campos modernos */
        background-color: #f9f9f9;
        -webkit-appearance: none; /* Quitar estilo nativo en Chrome/Safari */
        -moz-appearance: none;    /* Quitar estilo nativo en Firefox */
        appearance: none;
    }
    .form-group input[type="date"]:focus,
    .form-group select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.2);
        outline: none;
    }

    /* MEJORA: Diseño de Fechas en dos columnas */
    .date-group {
        display: flex;
        gap: 20px; /* Espacio entre los dos campos de fecha */
        margin-bottom: 20px;
    }
    .date-group .form-group {
        flex: 1; /* Ambos campos ocupan el mismo espacio */
        margin-bottom: 0;
    }

    /* Estilos base para el Toast */
    #app-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #d9534f;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1050;
        transition: opacity 0.5s, transform 0.5s;
        opacity: 0;
        transform: translateY(100%);
    }
</style>

<div class="form-container">
    <div class="form-box">
        <h2><i class="fas fa-calendar-alt"></i> Realizar una Reserva</h2>
        <p>Estás reservando como: <span class="badge bg-primary text-white p-2"><strong><?php echo htmlspecialchars($nombre_persona); ?></strong></span></p>
        
        <form action="pago.php" method="POST">
            
            <div class="form-group">
                <label for="habitacion_id">🛌 Tipo de Habitación:</label>
                <select name="habitacion_id" id="habitacion_id" required>
                    <option value="">-- Selecciona una Opción --</option>
                    <?php foreach($habitaciones_disponibles as $hab): ?>
                        <option value="<?php echo $hab['HabitacionID']; ?>" <?php echo (isset($_POST['habitacion_id']) && $_POST['habitacion_id'] == $hab['HabitacionID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($hab['N_TipoHabitacion'] . " (#" . $hab['NumeroHabitacion'] . ") - S/ " . number_format($hab['PrecioPorNoche'], 2) . " / Noche"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="date-group">
                <div class="form-group">
                    <label for="fecha_entrada">➡️ Fecha de Entrada:</label>
                    <input type="date" id="fecha_entrada" name="fecha_entrada" required value="<?php echo htmlspecialchars($_POST['fecha_entrada'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_salida">⬅️ Fecha de Salida:</label>
                    <input type="date" id="fecha_salida" name="fecha_salida" required value="<?php echo htmlspecialchars($_POST['fecha_salida'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="metodo_pago_id">💳 Método de Pago:</label>
                <select name="metodo_pago_id" id="metodo_pago_id" required>
                     <option value="">-- Selecciona un Método --</option>
                     <?php foreach($metodos_pago as $mp): ?>
                         <option value="<?php echo $mp['MetodoPagoID']; ?>" <?php echo (isset($_POST['metodo_pago_id']) && $_POST['metodo_pago_id'] == $mp['MetodoPagoID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mp['NombreMetodo']); ?>
                         </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-accent" style="width:100%; margin-top:20px; padding: 14px; font-size: 1.2em; font-weight: bold;">
                <i class="fas fa-arrow-circle-right"></i> Ir a Pago
            </button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaEntrada = document.getElementById('fecha_entrada');
    const fechaSalida = document.getElementById('fecha_salida');
    
    // ----------------------------------------------------
    // A. LÓGICA DE FECHAS EN EL CLIENTE (Se mantiene igual)
    // ----------------------------------------------------
    
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    fechaEntrada.setAttribute('min', todayStr);

    function actualizarFechas() {
        if (!fechaEntrada.value) return;

        let fechaIn = new Date(fechaEntrada.value + 'T00:00:00'); 
        let fechaMinimaSalida = new Date(fechaIn);
        
        fechaMinimaSalida.setDate(fechaIn.getDate() + 1); 
        
        const minSalidaStr = fechaMinimaSalida.toISOString().split('T')[0];
        
        fechaSalida.setAttribute('min', minSalidaStr);
        
        if (fechaSalida.value && fechaSalida.value < minSalidaStr) {
            fechaSalida.value = minSalidaStr;
        }
    }

    fechaEntrada.addEventListener('change', actualizarFechas);
    
    if (fechaEntrada.value) {
        actualizarFechas();
    }
    
    // ----------------------------------------------------
    // B. LÓGICA DE TOASTS (Se mantiene igual)
    // ----------------------------------------------------
    
    function showToast(message) {
        const toastId = 'app-toast';
        let toast = document.getElementById(toastId);
        
        if (!toast) {
            toast = document.createElement('div');
            toast.id = toastId;
            document.body.appendChild(toast);
        }
        
        toast.innerHTML = message;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(100%)';
        }, 5000);
    }
    
    <?php if (isset($_SESSION['toast_error'])): ?>
        showToast("<?php echo htmlspecialchars($_SESSION['toast_error']); ?>");
        <?php unset($_SESSION['toast_error']); ?>
    <?php endif; ?>
});
</script>
