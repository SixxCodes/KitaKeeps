// ==================== Login (Error handling only, no login logic)====================
new Vue({
    el: '#app',
    data: {
        email: '',
        password: '',
        rememberMe: false,
        showPassword: false,

        emailError: '',
        passwordError: '',
        loading: false,
    },
    methods: {
        togglePassword() {
            this.showPassword = !this.showPassword;
        },
        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        submitLogin() {
            this.emailError = '';
            this.passwordError = '';

            if (!this.email) {
                this.emailError = 'Email is required.';
            } else if (!this.validateEmail(this.email)) {
                this.emailError = 'Please enter a valid email address.';
            }

            if (!this.password) {
                this.passwordError = 'Password is required.';
            } else if (this.password.length < 8) {
                this.passwordError = 'Password must be at least 8 characters.';
            }

            if (this.emailError || this.passwordError) {
                return;
            }

            this.loading = true;

            // Simulate async login
            setTimeout(() => {
                alert(`Logged in as ${this.email}.\nRemember me: ${this.rememberMe ? 'Yes' : 'No'}`);
                this.loading = false;
            }, 2000);
        }
    }
});