    </div> <!-- .admin-content -->
    <div class="sidebar-overlay" onclick="AdminPanel.toggleSidebar()"></div>
</main>
</div> <!-- .admin-layout -->
    
    <!-- AUDIO ALERT FOR NEW ORDERS -->
    <audio id="newOrderSound" src="../assets/audio/sound-01.wav" preload="auto"></audio>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    </script>
</body>
</html>
