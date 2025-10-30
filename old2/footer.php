            </main>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const app = document.getElementById('app');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
        
        // Hide sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && !menuBtn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>
</body>
</html>
