// Session keep-alive helper
// Pings the server every 15 minutes to keep the session active

let keepAliveInterval = null;

export function startSessionKeepAlive() {
    // Clear any existing interval
    if (keepAliveInterval) {
        clearInterval(keepAliveInterval);
    }
    
    // Ping server every 15 minutes (900000 ms)
    keepAliveInterval = setInterval(() => {
        fetch('/api/session/keepalive', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }).catch(error => {
            console.warn('Session keep-alive ping failed:', error);
        });
    }, 900000); // 15 minutes
}

export function stopSessionKeepAlive() {
    if (keepAliveInterval) {
        clearInterval(keepAliveInterval);
        keepAliveInterval = null;
    }
}

// Auto-start on admin pages
if (window.location.pathname.startsWith('/admin')) {
    startSessionKeepAlive();
}