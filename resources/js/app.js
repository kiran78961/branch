const themeToggle = document.querySelector('[data-theme-toggle]');

if (themeToggle instanceof HTMLButtonElement) {
    const themeLabel = themeToggle.querySelector('[data-theme-label]');
    const themeIcon = themeToggle.querySelector('[data-theme-icon]');

    const updateThemeToggle = () => {
        const isDark = document.documentElement.classList.contains('dark');

        themeToggle.setAttribute('aria-pressed', String(isDark));

        if (themeLabel) {
            themeLabel.textContent = isDark ? 'Light mode' : 'Dark mode';
        }

        if (themeIcon) {
            themeIcon.textContent = isDark ? '☀' : '☾';
        }
    };

    updateThemeToggle();

    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');

        localStorage.setItem('branch-theme', isDark ? 'dark' : 'light');
        updateThemeToggle();
    });
}
