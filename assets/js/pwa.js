/**
 * Village Foods PWA Helper
 * Handles Service Worker registration and Install Prompt
 */

let deferredPrompt;

// 1. Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const scriptTag = document.querySelector('script[src*="pwa.js"]');
        let swUrl = '/service-worker.js'; // Production root
        
        if (scriptTag) {
            const pwaJsPath = scriptTag.getAttribute('src');
            // Extract the base path by removing 'assets/js/pwa.js' from the full script src
            const rootPath = pwaJsPath.split('assets/js/pwa.js')[0];
            
            // Construct swUrl dynamically
            if (rootPath.startsWith('http')) {
                // If it's an absolute URL, use it
                swUrl = rootPath + 'service-worker.js';
            } else if (rootPath !== "") {
                // If it's a relative path starting with / or ../
                swUrl = rootPath + 'service-worker.js';
            } else {
                // Default fallback to root
                swUrl = '/service-worker.js';
            }
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
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

    if (isStandalone) {
        if (typeof Toast !== 'undefined') Toast.show('App is already installed!');
        else alert('App is already installed!');
        return;
    }

    if (isIOS) {
        const msg = 'To install: Tap the <b>Share</b> button in Safari browser and select <b>"Add to Home Screen"</b>. <br><br> (Safari-la irundhu Share button-ah click panni "Add to Home Screen" select pannunga machan)';
        if (typeof Toast !== 'undefined') Toast.show(msg, 'info');
        else alert('iOS: Tap Share -> Add to Home Screen');
        return;
    }

    if (!deferredPrompt) {
        const msg = 'Install prompt not ready yet. Try refreshing or wait a moment. On Chrome mobile, you can also use <b>Settings (three dots) > Install app</b>.';
        if (typeof Toast !== 'undefined') Toast.show(msg, 'info');
        else alert('Prompt not ready. Use browser menu to Install.');
        return;
    }

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
