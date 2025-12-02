<?php
include('includes/header_public.php');
include('includes/db.php');

// --- CONSULTA SQL CORREGIDA Y MEJORADA ---
// Usaremos alias claros para compatibilidad con el diseño moderno
$sql = "SELECT 
            h.HabitacionID,
            h.NumeroHabitacion,
            th.N_TipoHabitacion,
            eh.Descripcion AS EstadoHabitacion,
            h.PrecioPorNoche, -- Asumo que el precio se llama 'PrecioPorNoche' para ser compatible con el diseño anterior.
            th.Descripcion as DescripcionTipo
        FROM Habitaciones h
        JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
        JOIN Estado_Habitacion eh ON h.Estado_HabitacionID = eh.Estado_HabitacionID
        WHERE h.Estado = '1'
        ORDER BY h.NumeroHabitacion";

$stmt = $pdo->query($sql);
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* --- ESTILOS DE DISEÑO (TARJETAS MODERNAS) --- */
.content {
    padding: 50px 20px;
    max-width: 1200px;
    margin: 0 auto;
}
.content h2 {
    text-align: center;
    margin-bottom: 40px;
    color: var(--primary-color);
    font-size: 2.5em;
    font-weight: 700;
}
.habitaciones-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}
.habitacion-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex;
    flex-direction: column;
}
.habitacion-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
.habitacion-card img {
    width: 100%; 
    height: 220px; 
    object-fit: cover; 
    border-bottom: 4px solid var(--accent-color);
}
.card-details {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.card-details h4 {
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.5em;
    border-bottom: 1px dashed #eee;
    padding-bottom: 5px;
}
.card-details p {
    margin-bottom: 8px;
    font-size: 0.95em;
    color: #555;
}
.price {
    font-size: 1.4em;
    font-weight: bold;
    color: green;
    margin-top: 10px;
}
.status-pill {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9em;
    margin-top: 5px;
}
.status-available {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.status-occupied {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.btn-reserve {
    margin-top: auto;
    padding: 12px;
    border-radius: 0 0 12px 12px;
    border: none;
    cursor: pointer;
    font-size: 1.1em;
    font-weight: bold;
    transition: background-color 0.3s;
}
.btn-success {
    background-color: var(--accent-color, #28a745);
    color: white;
}
.btn-success:hover {
    background-color: #1e7e34;
}
.btn-disabled {
    background-color: #6c757d;
    color: #fff;
    cursor: not-allowed;
}

</style>

<div class="content">
    <h2><i class="fas fa-bed"></i> Nuestras Habitaciones</h2>
    <div class="habitaciones-container">
        <?php foreach ($habitaciones as $habitacion): ?>
            <div class="habitacion-card">
                <?php
                    // --- LÓGICA DE IMAGEN CORREGIDA ---
                    // Se usa la lógica del código anterior, pero más limpia
                    $nombre_tipo = strtolower($habitacion['N_TipoHabitacion']);
                    $partes = explode(' ', $nombre_tipo);
                    $tipo_base = $partes[0];
                    
                    // Si el primer elemento es 'habitación', usamos el segundo elemento (e.g., 'simple')
                    if ($tipo_base == 'habitación' && count($partes) > 1) {
                        $tipo_base = $partes[1];
                    }
                    
                    $imagen_src = "img/" . $tipo_base . ".jpg";
                    
                    // Definir clases y texto basado en el estado
                    $es_disponible = ($habitacion['EstadoHabitacion'] == 'Disponible');
                    $status_class = $es_disponible ? 'status-available' : 'status-occupied';
                    $btn_disabled = $es_disponible ? '' : 'disabled';
                    $btn_class = $es_disponible ? 'btn-success' : 'btn-disabled';
                    $btn_text = $es_disponible ? 'Reservar Ahora' : 'No Disponible';
                ?>
                <img src="<?php echo $imagen_src; ?>" alt="<?php echo htmlspecialchars($habitacion['N_TipoHabitacion']); ?>">
                
                <div class="card-details">
                    <h4><?php echo htmlspecialchars($habitacion['N_TipoHabitacion']); ?> (#<?php echo htmlspecialchars($habitacion['NumeroHabitacion']); ?>)</h4>
                    
                    <p>
                        <i class="fas fa-info-circle"></i> Descripción: <?php echo htmlspecialchars($habitacion['DescripcionTipo']); ?>
                    </p>
                    
                    <p>
                        <i class="fas fa-tag"></i> Precio por noche: 
                        <span class="price">S/ <?php echo number_format($habitacion['PrecioPorNoche'], 2); ?></span>
                    </p>
                    
                    <p>
                        <i class="fas fa-circle-notch"></i> Estado:
                        <span class="status-pill <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($habitacion['EstadoHabitacion']); ?>
                        </span>
                    </p>
                    
                    <?php if ($es_disponible): ?>
                        <form action="reservar.php" method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="habitacion_id" value="<?php echo htmlspecialchars($habitacion['HabitacionID']); ?>">
                            <button type="submit" class="btn-reserve <?php echo $btn_class; ?>" style="width: 100%;">
                                <i class="fas fa-cart-plus"></i> <?php echo $btn_text; ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn-reserve <?php echo $btn_class; ?>" disabled style="width: 100%; margin-top: 15px;">
                            <i class="fas fa-times-circle"></i> <?php echo $btn_text; ?>
                        </button>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
