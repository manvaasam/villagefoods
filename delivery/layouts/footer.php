    <!-- Utility Scripts -->
    <script src="../assets/js/utils.js"></script>
    <?php 
    if (isset($extraScripts) && is_string($extraScripts) && trim($extraScripts) !== '') {
        echo $extraScripts;
    } 
    ?>
    
    <script>
        // Use a lightweight toast internal to delivery if needed or mock Toast.show
        const Toast = {
            show: (msg, type = 'success') => {
                const container = document.getElementById('toast-container');
                if(!container) return;
                const t = document.createElement('div');
                t.style.cssText = `
                    background: #1e293b; color: white; padding: 12px 20px; 
                    border-radius: 12px; margin-bottom: 10px; font-size: 14px;
                    border-left: 4px solid ${type === 'success' ? '#10b981' : '#f43f5e'};
                    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
                    animation: slideIn 0.3s ease-out;
                `;
                t.innerHTML = msg;
                container.appendChild(t);
                setTimeout(() => t.remove(), 3000);
            }
        };

        // Keyframe for toast
        const style = document.createElement('style');
        style.innerHTML = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
        document.head.appendChild(style);

        // Initialize Lucide
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
