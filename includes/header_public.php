<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Fiorella</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    
    <style>
        /* Reset y variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #4ecca3;
            --secondary-color: #1a1a2e;
            --dark-bg: #16213e;
            --light-text: #e8e8e8;
            --hover-color: #45b591;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Header Principal */
        .main-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--dark-bg) 100%);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--primary-color);
        }

        .main-header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 80px;
            gap: 30px;
        }

        /* Logo */
        .logo {
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo a {
            display: flex;
            align-items: center;
        }

        .logo img {
            max-height: 60px;
            filter: drop-shadow(0 2px 8px rgba(78, 204, 163, 0.3));
            transition: filter 0.3s ease;
        }

        .logo:hover img {
            filter: drop-shadow(0 4px 12px rgba(78, 204, 163, 0.5));
        }

        /* Navegación */
        .main-nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .menu {
            display: flex;
            list-style: none;
            gap: 10px;
            margin: 0;
            padding: 0;
        }

        .menu li a {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: var(--light-text);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu li a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .menu li a:hover {
            background: rgba(78, 204, 163, 0.1);
            color: var(--primary-color);
        }

        .menu li a:hover::before {
            width: 80%;
        }

        .menu li a.active {
            background: rgba(78, 204, 163, 0.15);
            color: var(--primary-color);
        }

        /* Botones de autenticación */
        .auth-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-shrink: 0;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .btn i {
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--secondary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--hover-color);
            border-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 204, 163, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--light-text);
            border-color: var(--light-text);
        }

        .btn-secondary:hover {
            background: var(--light-text);
            color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(232, 232, 232, 0.3);
        }

        /* Menú toggle (hamburguesa) */
        .menu-toggle {
            display: none;
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 1.5rem;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            background: var(--primary-color);
            color: var(--secondary-color);
            transform: rotate(90deg);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-header .container {
                flex-wrap: wrap;
            }

            .menu {
                gap: 5px;
            }

            .menu li a {
                padding: 10px 18px;
                font-size: 0.95rem;
            }

            .auth-buttons .btn {
                padding: 8px 18px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
                order: 3;
            }

            .main-nav {
                order: 4;
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease;
            }

            .main-nav.active {
                max-height: 300px;
                padding: 20px 0;
            }

            .menu {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .menu li a {
                justify-content: center;
                padding: 14px 20px;
                background: rgba(78, 204, 163, 0.05);
            }

            .auth-buttons {
                order: 2;
                gap: 8px;
            }

            .auth-buttons .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .main-header .container {
                min-height: 70px;
                padding: 15px 20px;
            }

            .logo img {
                max-height: 50px;
            }
        }

        @media (max-width: 480px) {
            .main-header .container {
                gap: 15px;
            }

            .auth-buttons {
                flex-wrap: wrap;
                width: auto;
            }

            .auth-buttons .btn {
                padding: 7px 14px;
                font-size: 0.8rem;
            }

            .logo img {
                max-height: 45px;
            }
        }

        /* Animación de entrada */
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .main-header {
            animation: slideDown 0.5s ease;
        }

        /* Scroll effect */
        .main-header.scrolled {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="img/logo.png" alt="Hotel Fiorella">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul class="menu">
                    <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="habitaciones.php"><i class="fas fa-bed"></i> Habitaciones</a></li>
                    <li><a href="reservar.php"><i class="fas fa-calendar-check"></i> Reservar</a></li>
                </ul>
            </nav>

            <button class="menu-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="auth-buttons">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <?php if ($_SESSION['rol_id'] == 1): ?>
                        <a href="admin/" class="btn btn-primary">
                            <i class="fas fa-user-shield"></i> Panel Admin
                        </a>
                    <?php else: ?>
                        <a href="client/" class="btn btn-primary">
                            <i class="fas fa-user"></i> Mi Cuenta
                        </a>
                    <?php endif; ?>
                    <a href="actions/logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Ingresar
                    </a>
                    <a href="register.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            const header = document.querySelector('.main-header');

            // Toggle menú móvil
            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    
                    // Cambiar icono hamburguesa/cerrar
                    const icon = this.querySelector('i');
                    if (mainNav.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                // Cerrar menú al hacer clic en un enlace
                const menuLinks = mainNav.querySelectorAll('a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            mainNav.classList.remove('active');
                            const icon = menuToggle.querySelector('i');
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    });
                });
            }

            // Efecto scroll en header
            let lastScroll = 0;
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                lastScroll = currentScroll;
            });

            // Marcar página actual en menú
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const menuLinks = document.querySelectorAll('.menu a');
            
            menuLinks.forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>