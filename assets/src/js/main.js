import '../scss/main.scss';

document.addEventListener('DOMContentLoaded', () => {
    const gradientBg = document.querySelector('#weather-station-app .gradient-bg');
    const sidebar = document.querySelector('#weather-station-app .weather-station-sidebar');
    const weatherSection = document.getElementById('weather-station-app');
    const heroLogo = document.querySelector('.hero-logo-container');
    const sidebarLogo = document.querySelector('.sidebar-logo-container');

    if (!gradientBg || !sidebar || !weatherSection || !heroLogo || !sidebarLogo) return;

    const handleScroll = () => {
        // Create fullwidth for tablet and bellow.
        if (window.innerWidth <= 768) {
            gradientBg.style.opacity = 1;
            sidebar.style.width = '100%';
            heroLogo.style.opacity = 1;
            sidebarLogo.style.opacity = 0;
            return;
        }

        const rect = weatherSection.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const progress = (windowHeight - rect.top) / windowHeight;
        const clampedProgress = Math.min(Math.max(progress, 0), 1);
        const easedProgress = clampedProgress * clampedProgress;

        gradientBg.style.opacity = 1 - easedProgress;
        const sidebarWidth = clampedProgress * 300;
        sidebar.style.width = `${sidebarWidth}px`;
        heroLogo.style.opacity = 1 - easedProgress;
        sidebarLogo.style.opacity = easedProgress;
    };

    // Expose the function to the global window object
    window.ws_handleScroll = handleScroll;

    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', handleScroll);
    handleScroll();
});