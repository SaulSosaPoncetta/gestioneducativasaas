/*
 * Punto de entrada JS (Vite).
 * Bootstrap queda disponible globalmente como window.bootstrap para usarlo
 * desde scripts de las vistas (@push('scripts')), ej. new bootstrap.Modal(...).
 * Como este archivo es módulo (se ejecuta diferido), en las vistas usarlo
 * dentro de document.addEventListener('DOMContentLoaded', ...).
 */
import './bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    // Tooltips: <span data-bs-toggle="tooltip" title="...">
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach((el) => new bootstrap.Tooltip(el));

    // Confirmación simple: <form data-confirmar="¿Eliminar el registro?">
    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirmar)) {
                e.preventDefault();
            }
        });
    });

    // Mostrar / ocultar contraseña: <button data-ver-clave="#password">
    document.querySelectorAll('[data-ver-clave]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.verClave);
            const ver = input.type === 'password';
            input.type = ver ? 'text' : 'password';
            btn.innerHTML = ver ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });
});
