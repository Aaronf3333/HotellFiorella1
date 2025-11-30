<?php
include('includes/header_public.php');
include('includes/db.php');

$sql = "SELECT TOP 3
            h.NumeroHabitacion, h.PrecioPorNoche,
            th.N_TipoHabitacion, th.Descripcion AS Capacidad,
            eh.Descripcion AS Estado
        FROM Habitaciones h
        JOIN TiposHabitacion th ON h.TipoHabitacionID = th.TipoHabitacionID
        JOIN Estado_Habitacion eh ON h.Estado_HabitacionID = eh.Estado_HabitacionID
        LEFT JOIN Reservas r ON h.HabitacionID = r.HabitacionID
        WHERE h.Estado = '1'
        GROUP BY h.NumeroHabitacion, h.PrecioPorNoche, th.N_TipoHabitacion, th.Descripcion, eh.Descripcion
        ORDER BY COUNT(r.HabitacionID) DESC";
$stmt = $pdo->query($sql);
$habitaciones_destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!function_exists('getStatusClass')) {
    function getStatusClass($status) {
        switch (strtolower($status)) {
            case 'disponible': return 'available';
            case 'ocupada': return 'occupied';
            case 'limpieza': return 'cleaning';
            case 'mantenimiento': return 'maintenance';
            default: return 'secondary';
        }
    }
}
?>

<style>
    /* Variables globales */
    :root {
        --primary-gradient: linear-gradient(135deg, #4ecca3 0%, #3ba886 100%);
        --secondary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --card-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 20px 50px rgba(78, 204, 163, 0.2);
        --text-dark: #1a1a2e;
        --text-light: #6c757d;
    }

    /* Hero Section Mejorado */
    .hero-section {
        position: relative;
        height: 85vh;
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(26, 26, 46, 0.85) 0%, rgba(22, 33, 62, 0.85) 100%),
                    url('img/465122074.jpg') center/cover no-repeat;
        color: white;
        text-align: center;
        overflow: hidden;
        margin-top: -80px;
        padding-top: 80px;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 50%, rgba(78, 204, 163, 0.1) 0%, transparent 70%);
        animation: pulse-hero 4s ease-in-out infinite;
    }

    @keyframes pulse-hero {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        padding: 2rem;
        animation: fadeInUp 1s ease;
    }

    .hero-content h1 {
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        background: linear-gradient(135deg, #ffffff 0%, #4ecca3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
    }

    .hero-content p {
        font-size: 1.5rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        font-weight: 300;
        letter-spacing: 0.5px;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Sección de Habitaciones Destacadas */
    .featured-rooms-section {
        padding: 100px 20px;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
    }

    .featured-rooms-section h2 {
        text-align: center;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-dark);
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }

    .featured-rooms-section h2::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 5px;
        background: var(--primary-gradient);
        border-radius: 3px;
    }

    /* Grid de habitaciones */
    .room-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 40px;
        margin: 60px 0 50px;
    }

    /* Cards de habitaciones */
    .room-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .room-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: var(--hover-shadow);
    }

    .room-card img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .room-card:hover img {
        transform: scale(1.1);
    }

    .room-card h3 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 1.5rem 1.5rem 0.5rem;
    }

    .room-card p {
        color: var(--text-light);
        margin: 0.5rem 1.5rem;
        font-size: 1rem;
        line-height: 1.6;
    }

    .room-card p:last-of-type {
        font-size: 1.3rem;
        font-weight: 700;
        color: #4ecca3;
        margin-top: 1rem;
    }

    /* Estado de habitación */
    .room-status {
        display: inline-block;
        padding: 0.5rem 1.2rem;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 1rem 1.5rem;
    }

    .room-status.available {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 2px solid #28a745;
    }

    .room-status.occupied {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 2px solid #dc3545;
    }

    .room-status.cleaning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
        border: 2px solid #ffc107;
    }

    .room-status.maintenance {
        background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
        color: #383d41;
        border: 2px solid #6c757d;
    }

    /* Botones */
    .room-card .btn {
        margin: auto 1.5rem 1.5rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }

    .btn-secondary {
        background: var(--secondary-gradient);
        color: white;
        border: 2px solid transparent;
    }

    .btn-secondary:hover {
        background: var(--primary-gradient);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(78, 204, 163, 0.3);
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 1.2rem 3rem;
        font-size: 1.1rem;
        box-shadow: 0 10px 30px rgba(78, 204, 163, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(78, 204, 163, 0.4);
    }

    .text-center {
        text-align: center;
        margin-top: 40px;
    }

    /* Sección de Google Maps */
    .google-maps-section {
        padding: 100px 20px;
        background: white;
    }

    .google-maps-section h2 {
        text-align: center;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-dark);
        position: relative;
        display: inline-block;
        left: 50%;
        transform: translateX(-50%);
    }

    .google-maps-section h2::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 5px;
        background: var(--primary-gradient);
        border-radius: 3px;
    }

    #google-map-placeholder {
        margin: 60px 0 30px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    #google-map-placeholder:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-5px);
    }

    #google-map-placeholder iframe {
        display: block;
        width: 100%;
        height: 500px;
        border: none;
    }

    .map-info {
        text-align: center;
        font-size: 1.2rem;
        color: var(--text-light);
        margin-top: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .map-info::before {
        content: '\f3c5';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #4ecca3;
        font-size: 1.5rem;
    }

    /* Animaciones adicionales */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .room-card:nth-child(1) { animation: fadeInUp 0.6s ease 0.1s both; }
    .room-card:nth-child(2) { animation: fadeInUp 0.6s ease 0.2s both; }
    .room-card:nth-child(3) { animation: fadeInUp 0.6s ease 0.3s both; }

    /* Responsive */
    @media (max-width: 1200px) {
        .room-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
    }

    @media (max-width: 768px) {
        .hero-section {
            height: 70vh;
            min-height: 500px;
        }

        .hero-content h1 {
            font-size: 2.5rem;
        }

        .hero-content p {
            font-size: 1.2rem;
        }

        .featured-rooms-section h2,
        .google-maps-section h2 {
            font-size: 2rem;
        }

        .featured-rooms-section,
        .google-maps-section {
            padding: 60px 20px;
        }

        .room-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        #google-map-placeholder iframe {
            height: 350px;
        }
    }

    @media (max-width: 480px) {
        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1rem;
        }

        .room-card h3 {
            font-size: 1.3rem;
        }

        .btn-primary {
            padding: 1rem 2rem;
            font-size: 1rem;
        }
    }

    /* Efecto parallax sutil en el hero */
    @media (min-width: 768px) {
        .hero-section {
            background-attachment: fixed;
        }
    }

    /* Indicador de scroll */
    .scroll-indicator {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        animation: bounce 2s infinite;
    }

    .scroll-indicator i {
        font-size: 2rem;
        color: #4ecca3;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
        40% { transform: translateX(-50%) translateY(-15px); }
        60% { transform: translateX(-50%) translateY(-10px); }
    }
</style>

<section class="hero-section" id="booking-section">
    <div class="hero-content">
        <h1>Bienvenido al Hotel Fiorella</h1>
        <p>Habitaciones con baño privado, agua caliente y TV. Atención las 24 hrs.</p>
    </div>
    <div class="scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<section class="featured-rooms-section container">
    <h2>Nuestras Habitaciones Destacadas</h2>
    <div class="room-grid">
        <?php foreach ($habitaciones_destacadas as $habitacion): ?>
            <div class="room-card">
                <?php
                    $tipo_img_nombre = strtolower(explode(' ', $habitacion['N_TipoHabitacion'])[0]);
                     if ($tipo_img_nombre == 'habitación') {
                        $tipo_img_nombre = strtolower(explode(' ', $habitacion['N_TipoHabitacion'])[1]);
                    }
                ?>
                <img src="img/<?php echo $tipo_img_nombre; ?>.jpg" alt="<?php echo htmlspecialchars($habitacion['N_TipoHabitacion']); ?>">
                
                <h3><?php echo htmlspecialchars($habitacion['N_TipoHabitacion']); ?></h3>
                <p><i class="fas fa-users"></i> <?php echo htmlspecialchars($habitacion['Capacidad']); ?></p>
                <p><i class="fas fa-tag"></i> S/ <?php echo number_format($habitacion['PrecioPorNoche'], 2); ?> / Noche</p>
                <span class="room-status <?php echo getStatusClass($habitacion['Estado']); ?>">
                    <i class="fas fa-circle"></i> <?php echo htmlspecialchars($habitacion['Estado']); ?>
                </span>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-calendar-check"></i> Ver Detalles y Reservar
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center">
        <a href="habitaciones.php" class="btn btn-primary">
            <i class="fas fa-bed"></i> Ver Todas las Habitaciones
        </a>
    </div>
</section>

<section class="google-maps-section container">
    <h2>Encuéntranos en Google Maps</h2>
    <div id="google-map-placeholder">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15545.98696818165!2d-76.25556244458008!3d-13.843108999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x911062dfda22189b%3A0x3d2745a336de69e!2sParacas%2C%20Ica!5e0!3m2!1ses-419!2spe!4v1720301037759!5m2!1ses-419!2spe" width="100%" height="500" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <p class="map-info">Estamos ubicados en Av Paracas mz a lote 4, Paracas.</p>
</section>

<script>
    // Smooth scroll para el indicador
    document.querySelector('.scroll-indicator')?.addEventListener('click', function() {
        document.querySelector('.featured-rooms-section').scrollIntoView({ 
            behavior: 'smooth' 
        });
    });

    // Animación de entrada cuando los elementos son visibles
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observar las secciones
    document.querySelectorAll('.featured-rooms-section, .google-maps-section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'all 0.6s ease';
        observer.observe(section);
    });
</script>

<?php include('includes/footer.php'); ?>