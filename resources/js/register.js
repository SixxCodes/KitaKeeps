// ==================== Register (Error handling only, no register logic)====================
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            hardwareName: '',
            username: '',
            firstname: '',
            lastname: '',
            email: '',
            password: '',
            confirmPassword: '',
            rememberMe: false,
            showPassword: false,

            hardwareNameError: '',
            usernameError: '',
            firstnameError: '',
            lastnameError: '',
            emailError: '',
            passwordError: '',
            confirmPasswordError: '',
            loading: false,
        }
    },
    methods: {
        togglePassword() {
            this.showPassword = !this.showPassword;
        },
        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        submitRegister() {
            // Reset all errors
            this.hardwareNameError = '';
            this.usernameError = '';
            this.firstnameError = '';
            this.lastnameError = '';
            this.emailError = '';
            this.passwordError = '';
            this.confirmPasswordError = '';
            this.loading = false;

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
            if (!this.firstname) {
                this.firstnameError = 'First Name is required.';
            } else if (!/^[A-Za-z\s-]+$/.test(this.firstname)) {
                this.firstnameError = 'First Name can only contain letters, spaces, and dashes.';
            }

            // Last Name validation
            if (!this.lastname) {
                this.lastnameError = 'Last Name is required.';
            } else if (!/^[A-Za-z\s-]+$/.test(this.lastname)) {
                this.lastnameError = 'Last Name can only contain letters, spaces, and dashes.';
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
            } else if (!/(?=.*[A-Z])(?=.*\d)(?=.*[ !"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~])/.test(this.password)) {
                this.passwordError = 'Password must include at least one uppercase letter, one number, and one special character.';
            }

            // Confirm password validation
            if (!this.confirmPassword) {
                this.confirmPasswordError = 'Please confirm your password.';
            } else if (this.password && this.confirmPassword !== this.password) {
                this.confirmPasswordError = 'Passwords do not match.';
            }

            // Stop submission if any errors exist
            if (
                this.hardwareNameError ||
                this.usernameError ||
                this.firstnameError ||
                this.lastnameError ||
                this.emailError ||
                this.passwordError ||
                this.confirmPasswordError
            ) {
                return;
            }

            this.loading = true;

            fetch('/register-frontend', {  // your backend endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest', // triggers AJAX detection
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hardwareName: this.hardwareName,
                    username: this.username,
                    firstname: this.firstname,
                    lastname: this.lastname,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.confirmPassword
                })
            })
            .then(res => res.text())
            .then(text => {
                this.loading = false;
                let data;

                try {
                    data = JSON.parse(text);  // try parsing JSON
                } catch (err) {
                    console.error('Non-JSON response:', text);
                    // alert('Server returned an unexpected response. Check console for details.');
                    return;
                }

                if (!data.success) {
                    // Backend validation errors
                    this.hardwareNameError = data.errors.hardwareName || '';
                    this.usernameError = data.errors.username || '';
                    this.firstnameError = data.errors.firstname || '';
                    this.lastnameError = data.errors.lastname || '';
                    this.emailError = data.errors.email || '';
                    this.passwordError = data.errors.password || '';
                    this.confirmPasswordError = data.errors.confirmPassword || '';
                } else {
                    // Registration successful
                    // alert(`Successfully registered ${this.username}! 🎉`);
                    window.location.href = data.redirect;
                    
                    // Clear form
                    this.hardwareName = '';
                    this.username = '';
                    this.firstname = '';
                    this.lastname = '';
                    this.email = '';
                    this.password = '';
                    this.confirmPassword = '';
                }
            })
            .catch(err => {
                this.loading = false;
                console.error('Error submitting registration:', err);
                // alert('An error occurred. Please try again later.');
            });
        }
    }
});

const el = document.getElementById('register-app');
if (el) {
    app.mount('#register-app');
}