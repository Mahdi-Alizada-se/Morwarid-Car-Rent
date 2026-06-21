<button x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        }
    }" @click="toggle()" class="flex items-center justify-center w-9 h-9 rounded-lg
           text-gray-500 dark:text-gray-300
           hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors" title="Toggle dark mode">

    {{-- Sun icon (shown in dark mode, click to go light) --}}
    <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    {{-- Moon icon (shown in light mode, click to go dark) --}}
    <svg x-show="!isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>