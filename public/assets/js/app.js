// Dark mode toggle. The saved theme is applied pre-paint in layout.php's <head>.
(function () {
    var toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
        var isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('pj-theme', isDark ? 'dark' : 'light');
    });
})();
