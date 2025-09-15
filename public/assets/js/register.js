// ==================== Login (Error handling only, no login logic)====================
new Vue({
    el: '#app',
    data: {
        hardwareName: '',
        username: '',
        firstName: '',
        lastName: '',
        email: '',
        password: '',
        confirmPassword: '',
        rememberMe: false,
        showPassword: false,

        hardwareNameError: '',
        usernameError: '',
        firstNameError: '',
        lastNameError: '',
        emailError: '',
        passwordError: '',
        confirmPasswordError: '',
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
            this.hardwareNameError = '';
            this.usernameError = '';
            this.firstNameError = '';
            this.lastNameError= '';
            this.emailError = '';
            this.passwordError = '';
            this.confirmPasswordError = '';

            // Hardware name validation
            if (!this.hardwareName) {
                this.hardwareNameError = 'Hardware name is required.';
            }

            // Username validation
            if (!this.username) {
                this.usernameError = 'Username is required.';
            } else if (this.username.length < 3) {
                this.usernameError = 'Username must be at least 3 characters long.';
            } else if (this.username.length > 20) {
                this.usernameError = 'Username cannot exceed 20 characters.';
            } else if (!/^[a-zA-Z0-9_]+$/.test(this.username)) {
                this.usernameError = 'Username can only contain letters, numbers, and underscores.';
            }

            // First Name validation
            if (!this.firstName) {
                this.firstNameError = 'First Name is required.';
            } else if (!/^[A-Za-z\s-]+$/.test(this.firstName)) {
                this.firstNameError = 'First Name can only contain letters, spaces, and dashes.';
            }

            // Last Name validation
            if (!this.lastName) {
                this.lastNameError = 'Last Name is required.';
            } else if (!/^[A-Za-z\s-]+$/.test(this.lastName)) {
                this.lastNameError = 'Last Name can only contain letters, spaces, and dashes.';
            }

            // Email validation
            if (!this.email) {
                this.emailError = 'Email is required.';
            } else if (!this.validateEmail(this.email)) {
                this.emailError = 'Please enter a valid email address.';
            }

            // Password validation
            if (!this.password) {
                this.passwordError = 'Password is required.';
            } else if (this.password.length < 8) {
                this.passwordError = 'Password must be at least 8 characters.';
            } else if (!/(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])/.test(this.password)) {
                this.passwordError = 'Password must include at least one uppercase letter, one number, and one special character.';
            }

            // Confirm password validation
            if (!this.confirmPassword) {
                this.confirmPasswordError = 'Please confirm your password.';
            } else if (this.password && this.confirmPassword !== this.password) {
                this.confirmPasswordError = 'Passwords do not match.';
            }

            // if (this.emailError || this.passwordError) {
            //     return;
            // }

            // Stop submission if errors exist
            if (
                this.hardwareNameError ||
                this.usernameError ||
                this.firstNameError ||
                this.lastNameError ||
                this.emailError ||
                this.passwordError ||
                this.confirmPasswordError
            ) {
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