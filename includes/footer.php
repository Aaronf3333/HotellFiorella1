<footer class="main-footer">
    <div class="container footer-grid-layout">
        <div class="footer-col creators-col">
            <h3>Nuestro Equipo</h3>
            <ul>
                <li>
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <a href="integrante.php?id=david" class="team-member-link">Gamboa Juscaymayta David Fernando</a>
                    <img src="img/david.png" alt="Avatar David" class="avatar">
                </li>
                <li>
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <a href="integrante.php?id=cesar" class="team-member-link">Gavilano Falla Cesar Augusto</a>
                    <img src="img/cesar.jpg" alt="Avatar Cesar" class="avatar">
                </li>
                <li>
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <a href="integrante.php?id=brayan" class="team-member-link">Martinez Herrera Brayan Benjamin</a>
                    <img src="img/brayan.jpg" alt="Avatar Brayan" class="avatar">
                </li>
                <li>
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <a href="integrante.php?id=ruben" class="team-member-link">Quintanilla Ochoa Ruben Yholino</a>
                    <img src="img/ruben.png" alt="Avatar Ruben" class="avatar">
                </li>
                <li>
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <a href="integrante.php?id=jesus" class="team-member-link">Aaron Isai Fernandez de la Cruz</a>
                    <img src="img/aaron.jpg" alt="Avatar Jesus" class="avatar">
                </li>
                <li class="instructor">
                    <span class="icon"><i class="fas fa-user-tie"></i></span> 
                    <strong>Ing. Luis Alfredo Castillon Siguas</strong>
                    <a href="mailto:luis.castillon@upsjb.edu.pe" class="email">luis.castillon@upsjb.edu.pe</a>
                </li>
            </ul>
        </div>
        
        <div class="footer-col hotel-info-col">
            <div class="hotel-header">
                <img src="img/logo.png" alt="Hotel Fiorella Logo" class="hotel-logo">
                <div>
                    <h3>Hotel Fiorella</h3>
                    <p class="hotel-slogan">Tu estancia perfecta comienza aquí.</p>
                </div>
            </div>
            
            <div class="contact-info">
                <p><i class="fas fa-map-marker-alt"></i> Av Paracas mz a lote 4, Paracas</p>
                <p><i class="fas fa-phone"></i> (056) 545134</p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:hotelfiorella@hotmail.com">hotelfiorella@hotmail.com</a></p>
                <p class="whatsapp-link">
                    <i class="fab fa-whatsapp"></i>
                    <a href="https://wa.me/51933234624?text=Necesito%20Ayuda" target="_blank">Ayuda al Cliente</a>
                </p>
            </div>
            
            <div class="resources">
                <h4>Recursos y Documentación</h4>
                <div class="resource-links">
                    <a href="https://upsjb-my.sharepoint.com/:b:/g/personal/brayanb_martinez_upsjb_edu_pe/EV4Ia_qN_4BAmwKBpvwdja4BrKYagTlv5jCYSchqWjsGrQ?e=9O0tr8" target="_blank" class="resource-btn">
                        <i class="fa-solid fa-book"></i> Manual de Usuario
                    </a>
                    <a href="https://upsjb-my.sharepoint.com/:b:/g/personal/brayanb_martinez_upsjb_edu_pe/EVBeotxwBOlBmgLt0C8U7UsBf_ZcnEXtY9nJs67EXMti3A?e=6mwbxu" target="_blank" class="resource-btn">
                        <i class="fa-solid fa-user-shield"></i> Manual de Administrador
                    </a>
                    <a href="https://upsjb-my.sharepoint.com/:b:/g/personal/brayanb_martinez_upsjb_edu_pe/EVBBru-jJEdKthyVCX-IHncB_mQdptF12KGZs3LlKPJuFQ?e=lliPud" target="_blank" class="resource-btn">
                        <i class="fa-solid fa-code"></i> Manual de Programador
                    </a>
                    <a href="https://upsjb-my.sharepoint.com/:f:/g/personal/brayanb_martinez_upsjb_edu_pe/Ej3iFb340CBEqlc7WSaM8ygBqA6xQN5HFEVAPWVjqj3R7w?e=QDknLr" target="_blank" class="resource-btn">
                        <i class="fa-solid fa-folder-open"></i> Archivos
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-copyright">
        <p>© 2025 Hotel Fiorella. Todos los derechos reservados.</p>
    </div>
</footer>

<style>
/* Footer Principal */
.main-footer {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #e8e8e8;
    padding: 60px 20px 0;
    margin-top: 80px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.footer-grid-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-bottom: 40px;
}

/* Columnas */
.footer-col h3 {
    color: #4ecca3;
    font-size: 1.5rem;
    margin-bottom: 25px;
    font-weight: 600;
    border-bottom: 3px solid #4ecca3;
    padding-bottom: 10px;
    display: inline-block;
}

.footer-col h4 {
    color: #4ecca3;
    font-size: 1.1rem;
    margin: 30px 0 15px;
    font-weight: 600;
}

/* Sección de Equipo */
.creators-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.creators-col li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    margin-bottom: 10px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.creators-col li:hover {
    background: rgba(78, 204, 163, 0.1);
    border-left-color: #4ecca3;
    transform: translateX(5px);
}

.creators-col .icon {
    color: #4ecca3;
    font-size: 1.1rem;
    min-width: 20px;
}

.creators-col .avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #4ecca3;
    margin-left: auto;
}

.team-member-link {
    color: #e8e8e8;
    text-decoration: none;
    flex: 1;
    transition: color 0.3s ease;
}

.team-member-link:hover {
    color: #4ecca3;
}

.creators-col .instructor {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(78, 204, 163, 0.3);
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
}

.creators-col .instructor strong {
    color: #4ecca3;
}

/* Sección Hotel */
.hotel-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(78, 204, 163, 0.2);
}

.hotel-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(78, 204, 163, 0.3));
}

.hotel-slogan {
    color: #b0b0b0;
    font-style: italic;
    font-size: 0.95rem;
    margin-top: 5px;
}

.contact-info {
    margin-bottom: 30px;
}

.contact-info p {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 12px 0;
    padding: 8px 0;
}

.contact-info i {
    color: #4ecca3;
    font-size: 1.1rem;
    min-width: 20px;
}

.contact-info a {
    color: #e8e8e8;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-info a:hover {
    color: #4ecca3;
}

.whatsapp-link {
    margin-top: 15px !important;
    padding: 12px !important;
    background: rgba(37, 211, 102, 0.1);
    border-radius: 8px;
    border-left: 3px solid #25D366;
}

.whatsapp-link i {
    color: #25D366 !important;
    font-size: 1.3rem !important;
}

.whatsapp-link a {
    color: #25D366 !important;
    font-weight: 600;
}

/* Recursos */
.resources {
    background: rgba(255, 255, 255, 0.03);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid rgba(78, 204, 163, 0.2);
}

.resource-links {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.resource-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    background: rgba(78, 204, 163, 0.1);
    border: 1px solid rgba(78, 204, 163, 0.3);
    border-radius: 6px;
    color: #e8e8e8;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.resource-btn:hover {
    background: rgba(78, 204, 163, 0.2);
    border-color: #4ecca3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(78, 204, 163, 0.2);
}

.resource-btn i {
    color: #4ecca3;
    font-size: 1.1rem;
}

/* Copyright */
.footer-copyright {
    text-align: center;
    padding: 25px 0;
    border-top: 1px solid rgba(78, 204, 163, 0.2);
    background: rgba(0, 0, 0, 0.2);
}

.footer-copyright p {
    margin: 0;
    color: #b0b0b0;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 992px) {
    .footer-grid-layout {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .resource-links {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .main-footer {
        padding: 40px 15px 0;
    }
    
    .hotel-header {
        flex-direction: column;
        text-align: center;
    }
    
    .creators-col li {
        flex-wrap: wrap;
    }
    
    .creators-col .avatar {
        margin-left: 0;
    }
}
</style>

<script src="assets/js/main.js"></script>
</body>
</html>