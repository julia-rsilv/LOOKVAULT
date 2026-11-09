// Menu mobile
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('menuToggle');
    const nav = document.getElementById('nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => nav.classList.toggle('active'));
    }
});