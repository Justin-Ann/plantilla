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
            const response = await fetch('/api/auth.php?action=login', {
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
                return true;
            }
            return false;
        } catch (error) {
            console.error('Login error:', error);
            return false;
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
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const success = await Auth.login(email, password);
    if (!success) {
        alert('Invalid credentials');
    }
});

document.getElementById('logout-btn').addEventListener('click', (e) => {
    e.preventDefault();
    Auth.logout();
});
