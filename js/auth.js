const Auth = {
    checkAuth() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            this.showLoginScreen();
            return false;
        }
        return true;
    },

    showLoginScreen() {
        document.getElementById('login-screen').style.display = 'block';
        document.getElementById('main-content').style.display = 'none';
    },

    showMainContent() {
        document.getElementById('login-screen').style.display = 'none';
        document.getElementById('main-content').style.display = 'flex';
    },

    async login(email, password) {
        try {
            const response = await fetch('/HRIS/api/auth.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            const data = await response.json();
            if (data.success) {
                localStorage.setItem('auth_token', data.token);
                localStorage.setItem('user_role', data.role);
                this.showMainContent();
                window.location.href = '/HRIS/dashboard.php'; // Add redirect
                return true;
            }
            return false;
        } catch (error) {
            console.error('Login error:', error);
            return false;
        }
    },

    async register(fullName, email, password) {
        try {
            const response = await fetch('/HRIS/api/auth.php?action=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    full_name: fullName, 
                    email, 
                    password 
                })
            });

            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                this.toggleForms('login');
                return true;
            } else {
                alert(data.message || 'Registration failed. Please try again.');
                return false;
            }
        } catch (error) {
            console.error('Registration error:', error);
            alert('Server error. Please try again later.');
            return false;
        }
    },

    toggleForms(form) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const authTitle = document.getElementById('auth-title');
        
        if (form === 'register') {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            authTitle.textContent = 'Create Account';
        } else {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            authTitle.textContent = 'Welcome Back';
        }
    },

    logout() {
        if (confirm('Are you sure you want to logout?')) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_role');
            this.showLoginScreen();
        }
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegisterBtn = document.getElementById('show-register');
    const showLoginBtn = document.getElementById('show-login');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        await Auth.login(email, password);
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fullName = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        await Auth.register(fullName, email, password);
    });

    showRegisterBtn.addEventListener('click', (e) => {
        e.preventDefault();
        Auth.toggleForms('register');
    });

    showLoginBtn.addEventListener('click', (e) => {
        e.preventDefault();
        Auth.toggleForms('login');
    });
});
