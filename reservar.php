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
// 🛑 LÓGICA DE VALIDACIÓN (PHP) ANTES DE REDIRIGIR A PAGO
// ===================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_entrada = $_POST['fecha_entrada'] ?? null;
    $fecha_salida = $_POST['fecha_salida'] ?? null;
    $error_message = null; 

    if ($fecha_entrada && $fecha_salida) {
        try {
            // Conversión y validación de fechas
            $f_ingreso = new DateTime($fecha_entrada);
            $f_salida = new DateTime($fecha_salida);
            $hoy = new DateTime('today');

            // Clonamos para la comparación de fechas sin hora
            $f_salida_sin_hora = clone $f_salida;
            $f_salida_sin_hora->setTime(0, 0, 0); 

            // 1. Validar Fechas Pasadas (Fecha de Salida)
            if ($f_salida_sin_hora < $hoy) {
                $error_message = "❌ La fecha de salida no puede ser anterior al día de hoy.";
            }

            // 2. Validar Duración Mínima (Salida debe ser al menos 1 día después del Ingreso)
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
        // Guardamos el error en sesión para mostrar el "toast"
        $_SESSION['toast_error'] = $error_message;
        // La ejecución continúa para mostrar el formulario con el error
    } else {
        // Si todo es válido, enviamos el POST a pago.php
        // Usaremos una redirección simple ya que pago.php espera los datos POST
        // Dado que la validación pasó, permitimos que el formulario se envíe con los datos POST
        // Puedes redirigir a una página intermedia o simplemente dejar que el formulario se envíe a "pago.php"
        // En este caso, haremos que el formulario continúe su acción original (action="pago.php")
    }
}
// ===================================================
// 🟢 FIN DE LA VALIDACIÓN PHP 🟢
// ===================================================


// Obtenemos el nombre de la persona desde la BD
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

// Cargamos datos para los menús desplegables
$habitaciones_sql = "SELECT h.HabitacionID, h.NumeroHabitacion, th.N_TipoHabitacion, h.PrecioPorNoche FROM Habitaciones h JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID WHERE h.Estado_HabitacionID = 1";
$stmt_hab = $pdo->query($habitaciones_sql);
$habitaciones_disponibles = $stmt_hab->fetchAll(PDO::FETCH_ASSOC);

$metodos_pago_sql = "SELECT MetodoPagoID, NombreMetodo FROM MetodosPago WHERE Estado = '1'";
$stmt_mp = $pdo->query($metodos_pago_sql);
$metodos_pago = $stmt_mp->fetchAll(PDO::FETCH_ASSOC);


// 3. Incluimos el encabezado HTML, ya que el control de sesión pasó
include('includes/header_public.php');
?>
<style>
    .form-container { display: flex; justify-content: center; padding: 40px 0; }
    .form-box { padding: 30px; background: #fff; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px; width: 600px; }
    .form-box h2 { text-align: center; color: var(--primary-color); margin-bottom: 25px; }
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
        <h2>Realizar una Reserva</h2>
        <p>Estás reservando como: <strong><?php echo htmlspecialchars($nombre_persona); ?></strong></p>
        
        <form action="pago.php" method="POST">
            <div class="form-group">
                <label for="habitacion_id">Selecciona una Habitación:</label>
                <select name="habitacion_id" id="habitacion_id" required>
                    <option value="">-- Habitaciones Disponibles --</option>
                    <?php foreach($habitaciones_disponibles as $hab): ?>
                        <option value="<?php echo $hab['HabitacionID']; ?>" <?php echo (isset($_POST['habitacion_id']) && $_POST['habitacion_id'] == $hab['HabitacionID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($hab['N_TipoHabitacion'] . " (#" . $hab['NumeroHabitacion'] . ") - S/ " . number_format($hab['PrecioPorNoche'], 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_entrada">Fecha de Entrada:</label>
                <input type="date" id="fecha_entrada" name="fecha_entrada" required value="<?php echo htmlspecialchars($_POST['fecha_entrada'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="fecha_salida">Fecha de Salida:</label>
                <input type="date" id="fecha_salida" name="fecha_salida" required value="<?php echo htmlspecialchars($_POST['fecha_salida'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="metodo_pago_id">Método de Pago:</label>
                <select name="metodo_pago_id" id="metodo_pago_id" required>
                     <?php foreach($metodos_pago as $mp): ?>
                         <option value="<?php echo $mp['MetodoPagoID']; ?>" <?php echo (isset($_POST['metodo_pago_id']) && $_POST['metodo_pago_id'] == $mp['MetodoPagoID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mp['NombreMetodo']); ?>
                         </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-accent" style="width:100%; margin-top:20px; padding: 12px; font-size: 1.1em;">Confirmar Reserva</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaEntrada = document.getElementById('fecha_entrada');
    const fechaSalida = document.getElementById('fecha_salida');
    
    // ----------------------------------------------------
    // A. LÓGICA DE FECHAS EN EL CLIENTE
    // ----------------------------------------------------
    
    // 1. Establecer la fecha mínima de entrada a HOY
    const today = new Date();
    // Ajuste para el offset de la zona horaria y obtener la fecha de hoy en formato AAAA-MM-DD
    const todayStr = today.toISOString().split('T')[0];
    fechaEntrada.setAttribute('min', todayStr);

    // 2. Función para validar la duración y actualizar la fecha de salida
    function actualizarFechas() {
        if (!fechaEntrada.value) return;

        // Crear objeto Date basado en el valor del campo
        let fechaIn = new Date(fechaEntrada.value + 'T00:00:00'); // Añadir T00:00:00 para evitar problemas de zona horaria
        let fechaMinimaSalida = new Date(fechaIn);
        
        // La salida debe ser al menos el día siguiente (+1 día)
        fechaMinimaSalida.setDate(fechaIn.getDate() + 1); 
        
        const minSalidaStr = fechaMinimaSalida.toISOString().split('T')[0];
        
        // Establecer el mínimo de la fecha de salida (para duración mínima)
        fechaSalida.setAttribute('min', minSalidaStr);
        
        // Si la fecha de salida seleccionada es anterior a la nueva fecha mínima, la corrige automáticamente
        if (fechaSalida.value && fechaSalida.value < minSalidaStr) {
            fechaSalida.value = minSalidaStr;
        }
    }

    // Ejecutar la función al cambiar las fechas
    fechaEntrada.addEventListener('change', actualizarFechas);
    
    // Ejecutar al cargar la página si ya hay valores
    if (fechaEntrada.value) {
        actualizarFechas();
    }
    
    // ----------------------------------------------------
    // B. LÓGICA DE TOASTS (para mostrar mensajes bonitos)
    // ----------------------------------------------------
    
    // Función para mostrar el Toast
    function showToast(message) {
        const toastId = 'app-toast';
        let toast = document.getElementById(toastId);
        
        if (!toast) {
            toast = document.createElement('div');
            toast.id = toastId;
            // Se añaden los estilos en la sección <style> arriba
            document.body.appendChild(toast);
        }
        
        toast.innerHTML = message;
        // Mostrar el toast
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        
        setTimeout(() => {
            // Ocultar el toast
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(100%)';
        }, 5000);
    }
    
    // Manejar el error de PHP/Sesión si existe
    <?php if (isset($_SESSION['toast_error'])): ?>
        showToast("<?php echo htmlspecialchars($_SESSION['toast_error']); ?>");
        <?php unset($_SESSION['toast_error']); // Borrar el mensaje después de mostrarlo ?>
    <?php endif; ?>
});
</script>
