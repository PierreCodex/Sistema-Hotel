/**
 * Gestor de sesiones del lado del cliente
 * Maneja la verificación automática, renovación y notificaciones de expiración de sesión
 */

class SessionManager {
    constructor(config = {}) {
        this.config = {
            checkInterval: config.checkInterval || 60000, // Verificar cada minuto
            warningTime: config.warningTime || 300, // Advertir 5 minutos antes
            baseUrl: config.baseUrl || '',
            renewUrl: config.renewUrl || 'middleware/SessionMiddleware.php',
            loginUrl: config.loginUrl || 'index.php',
            ...config
        };
        
        this.isWarningShown = false;
        this.warningDialog = null;
        this.checkTimer = null;
        this.countdownTimer = null;
        
        this.init();
    }
    
    /**
     * Inicializa el gestor de sesiones
     */
    init() {
        this.startSessionCheck();
        this.bindEvents();
        console.log('SessionManager inicializado');
    }
    
    /**
     * Inicia la verificación periódica de sesión
     */
    startSessionCheck() {
        this.checkTimer = setInterval(() => {
            this.checkSession();
        }, this.config.checkInterval);
        
        // Verificar inmediatamente
        this.checkSession();
    }
    
    /**
     * Detiene la verificación de sesión
     */
    stopSessionCheck() {
        if (this.checkTimer) {
            clearInterval(this.checkTimer);
            this.checkTimer = null;
        }
        
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
            this.countdownTimer = null;
        }
    }
    
    /**
     * Verifica el estado de la sesión
     */
    async checkSession() {
        try {
            const url = this.config.baseUrl.endsWith('/') ? 
                this.config.baseUrl + this.config.renewUrl : 
                this.config.baseUrl + '/' + this.config.renewUrl;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check_session'
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            this.handleSessionStatus(data);
            
        } catch (error) {
            console.error('Error verificando sesión:', error);
        }
    }
    
    /**
     * Maneja el estado de la sesión recibido del servidor
     */
    handleSessionStatus(data) {
        if (!data.authenticated) {
            this.handleSessionExpired();
            return;
        }
        
        const timeLeft = data.time_left;
        
        if (timeLeft <= this.config.warningTime && timeLeft > 0) {
            if (!this.isWarningShown) {
                this.showExpirationWarning(timeLeft);
            }
        } else {
            this.hideExpirationWarning();
        }
        
        // Actualizar información de sesión en el DOM si existe
        this.updateSessionInfo(data);
    }
    
    /**
     * Muestra advertencia de expiración de sesión
     */
    showExpirationWarning(timeLeft) {
        this.isWarningShown = true;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        this.warningDialog = Swal.fire({
            title: '⚠️ Sesión por expirar',
            html: `
                <div class="session-warning">
                    <p>Su sesión expirará en:</p>
                    <div class="countdown-display">
                        <span id="countdown-timer">${minutes}:${seconds.toString().padStart(2, '0')}</span>
                    </div>
                    <p>¿Desea extender su sesión?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Extender sesión',
            cancelButtonText: 'Cerrar sesión',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'session-warning-popup'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.renewSession();
            } else {
                this.logout();
            }
        });
        
        // Iniciar countdown
        this.startCountdown(timeLeft);
    }
    
    /**
     * Inicia el contador regresivo
     */
    startCountdown(initialTime) {
        let timeLeft = initialTime;
        
        this.countdownTimer = setInterval(() => {
            timeLeft--;
            
            if (timeLeft <= 0) {
                clearInterval(this.countdownTimer);
                this.handleSessionExpired();
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            const countdownElement = document.getElementById('countdown-timer');
            if (countdownElement) {
                countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }, 1000);
    }
    
    /**
     * Oculta la advertencia de expiración
     */
    hideExpirationWarning() {
        if (this.isWarningShown && this.warningDialog) {
            Swal.close();
            this.isWarningShown = false;
            this.warningDialog = null;
        }
        
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
            this.countdownTimer = null;
        }
    }
    
    /**
     * Renueva la sesión
     */
    async renewSession() {
        try {
            const url = this.config.baseUrl.endsWith('/') ? 
                this.config.baseUrl + this.config.renewUrl : 
                this.config.baseUrl + '/' + this.config.renewUrl;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=renew_session'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.hideExpirationWarning();
                
                Swal.fire({
                    title: '✅ Sesión renovada',
                    text: 'Su sesión ha sido extendida exitosamente',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                console.log('Sesión renovada exitosamente');
            } else {
                this.handleSessionExpired();
            }
            
        } catch (error) {
            console.error('Error renovando sesión:', error);
            this.handleSessionExpired();
        }
    }
    
    /**
     * Maneja la expiración de sesión
     */
    handleSessionExpired() {
        this.stopSessionCheck();
        
        Swal.fire({
            title: '🔒 Sesión expirada',
            text: 'Su sesión ha expirado. Será redirigido al login.',
            icon: 'error',
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: 'Ir al login'
        }).then(() => {
            this.redirectToLogin();
        });
    }
    
    /**
     * Cierra sesión y redirige al login
     */
    logout() {
        window.location.href = this.config.baseUrl + 'view/html/logout.php';
    }
    
    /**
     * Redirige al login
     */
    redirectToLogin() {
        window.location.href = this.config.baseUrl + this.config.loginUrl;
    }
    
    /**
     * Actualiza información de sesión en el DOM
     */
    updateSessionInfo(data) {
        // Actualizar elementos que muestren información de sesión
        const sessionInfoElements = document.querySelectorAll('[data-session-info]');
        
        sessionInfoElements.forEach(element => {
            const infoType = element.getAttribute('data-session-info');
            
            switch (infoType) {
                case 'time-left':
                    const minutes = Math.floor(data.time_left / 60);
                    element.textContent = `${minutes} min`;
                    break;
                case 'status':
                    element.textContent = data.status;
                    break;
            }
        });
    }
    
    /**
     * Vincula eventos del DOM
     */
    bindEvents() {
        // Renovar sesión en actividad del usuario
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        let lastActivity = Date.now();
        
        activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                const now = Date.now();
                // Solo renovar si han pasado más de 5 minutos desde la última actividad
                if (now - lastActivity > 300000) { // 5 minutos
                    this.renewSessionSilently();
                    lastActivity = now;
                }
            }, true);
        });
        
        // Manejar visibilidad de la página
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Verificar sesión cuando la página vuelve a ser visible
                this.checkSession();
            }
        });
    }
    
    /**
     * Renueva la sesión silenciosamente (sin mostrar notificaciones)
     */
    async renewSessionSilently() {
        try {
            const url = this.config.baseUrl.endsWith('/') ? 
                this.config.baseUrl + this.config.renewUrl : 
                this.config.baseUrl + '/' + this.config.renewUrl;
            
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=renew_session'
            });
        } catch (error) {
            console.error('Error renovando sesión silenciosamente:', error);
        }
    }
    
    /**
     * Destruye el gestor de sesiones
     */
    destroy() {
        this.stopSessionCheck();
        this.hideExpirationWarning();
    }
}

// CSS para el diálogo de advertencia
const sessionWarningCSS = `
<style>
.session-warning {
    text-align: center;
    font-family: Arial, sans-serif;
}

.countdown-display {
    margin: 20px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #ffc107;
}

#countdown-timer {
    font-size: 2em;
    font-weight: bold;
    color: #dc3545;
    font-family: 'Courier New', monospace;
}

.session-warning-popup {
    border-radius: 15px !important;
}
</style>
`;

// Inyectar CSS
document.head.insertAdjacentHTML('beforeend', sessionWarningCSS);

// Exportar para uso global
window.SessionManager = SessionManager;