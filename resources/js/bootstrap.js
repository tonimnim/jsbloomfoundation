import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF token to all requests
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Handle 419 CSRF token expiration globally
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            // Session expired, refresh the page
            alert('Your session has expired. The page will refresh.');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
