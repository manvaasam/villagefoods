/**
 * Village Foods PWA Helper
 * Handles Service Worker registration and Install Prompt
 */

let deferredPrompt;

// 1. Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // 1. Determine the root path dynamically based on where pwa.js is located
        const scriptTag = document.querySelector('script[src*="pwa.js"]');
        let swUrl = '/service-worker.js'; // Fallback to root
        
        if (scriptTag) {
            const pwaJsPath = scriptTag.getAttribute('src');
            // Extract the part of the URL before 'assets/js/pwa.js'
            const rootPath = pwaJsPath.split('assets/js/pwa.js')[0];
            swUrl = rootPath + 'service-worker.js';
        }
        
        console.log('PWA: Current Hostname:', window.location.hostname);
        console.log('PWA: Attempting to register SW at:', swUrl);

        navigator.serviceWorker.register(swUrl)
            .then(reg => console.log('PWA: SW Registered Successfully. Scope:', reg.scope))
            .catch(err => console.error('PWA: SW Registration failed:', err));
    });
} else {
    console.warn('PWA: Service Workers are not supported in this browser.');
}

// 2. Handle Install Prompt
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
    // Update UI notify the user they can install the PWA
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
        installBtn.style.display = 'flex';
    }
});

// 3. Trigger Install Prompt
async function installPWA() {
    if (!deferredPrompt) return;
    // Show the install prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`User response to the install prompt: ${outcome}`);
    // We've used the prompt, and can't use it again, throw it away
    deferredPrompt = null;
    // Hide the install button
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
        installBtn.style.display = 'none';
    }
}

// 4. Handle Installed Event
window.addEventListener('appinstalled', (event) => {
    console.log('PWA was installed');
    // Hide the install button
    const installBtn = document.getElementById('pwa-install-btn');
    if (installBtn) {
        installBtn.style.display = 'none';
    }
});
