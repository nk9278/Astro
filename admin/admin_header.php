<!-- Admin Header -->
<header class="sticky top-0 z-[999] w-full bg-white/90 backdrop-blur-xl border-b border-gray-200 transition-colors duration-300 dark:bg-gray-900/90 dark:border-gray-800" style="font-family: 'Space Grotesk', sans-serif;">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-center relative">

        <!-- Left Logo & Brand -->
        <div class="absolute left-4 sm:left-6 lg:left-8 flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center overflow-hidden dark:border-gray-700 dark:bg-gray-800">
                <img src="/assets/images/FP%20Logo.png" alt="FP Logo" class="h-full w-full object-contain p-0.5">
            </div>
            <span class="hidden md:block font-black text-lg tracking-tight text-gray-900 dark:text-white">
                Fortune<span class="text-red-600">Parth</span>
            </span>
        </div>

        <!-- Center Title -->
        <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight dark:text-white">
            Admin <span class="text-red-600">Panel</span>
        </div>

        <!-- Right Controls (Theme Toggle) -->
        <div class="absolute right-4 sm:right-6 lg:right-8 flex items-center">
            <button id="adminThemeToggle" class="p-2 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-red-600 transition-all shadow-sm active:scale-95 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-red-400 focus:outline-none" title="Toggle Dark/Light Mode">
                <svg id="adminThemeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <!-- Default Moon Icon -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
        </div>

    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('adminThemeToggle');
        const themeIcon = document.getElementById('adminThemeIcon');
        
        // Check initial theme from localStorage or body class
        const isDark = document.body.classList.contains('dark-theme') || localStorage.getItem('theme') === 'dark';
        if (isDark) {
            document.body.classList.add('dark-theme');
            setSunIcon();
        } else {
            setMoonIcon();
        }

        // Toggle Event Listener
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            const darkEnabled = document.body.classList.contains('dark-theme');
            localStorage.setItem('theme', darkEnabled ? 'dark' : 'light');
            
            if (darkEnabled) {
                setSunIcon();
            } else {
                setMoonIcon();
            }
        });

        function setSunIcon() {
            // Sun icon for dark mode (click to switch to light)
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
        }

        function setMoonIcon() {
            // Moon icon for light mode (click to switch to dark)
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
        }
    });
</script>