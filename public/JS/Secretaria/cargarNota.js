document.addEventListener('DOMContentLoaded', () => {


    // ─── Autocomplete Materia ─────────────────────────────────────────────
    const MATERIAS = [
        'Análisis Matemático I', 'Análisis Matemático II',
        'Álgebra y Geometría Analítica', 'Física I', 'Física II',
        'Química General', 'Programación I', 'Programación II',
        'Estructuras de Datos', 'Base de Datos', 'Sistemas Operativos',
        'Redes de Computadoras', 'Ingeniería de Software',
        'Arquitectura de Computadoras', 'Probabilidad y Estadística',
        'Investigación Operativa', 'Electrónica Digital',
        'Circuitos Eléctricos', 'Resistencia de Materiales', 'Termodinámica',
    ];

    const materiaInput = document.getElementById('materia-input');
    const materiaList  = document.getElementById('materia-list');
    let activeIndex    = -1;

    function escapeRe(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlight(text, query) {
        if (!query) return text;
        return text.replace(new RegExp(`(${escapeRe(query)})`, 'gi'), '<strong>$1</strong>');
    }

    function showDropdown() { materiaList.classList.add('show'); }
    function hideDropdown() { materiaList.classList.remove('show'); activeIndex = -1; }

    function renderMateria(query) {
        const q = query.trim();
        if (!q) { hideDropdown(); return; }

        const results = MATERIAS.filter(m => m.toLowerCase().includes(q.toLowerCase()));
        materiaList.innerHTML = '';

        if (results.length === 0) {
            materiaList.innerHTML = `<div class="dropdown-item disabled">Sin resultados para "<strong>${q}</strong>"</div>`;
        } else {
            results.forEach((m, i) => {
                const item = document.createElement('div');
                item.className = 'dropdown-item';
                item.innerHTML = `<span>${highlight(m, q)}</span>`;
                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    materiaInput.value = m;
                    hideDropdown();
                });
                materiaList.appendChild(item);
            });
        }

        activeIndex = -1;
        showDropdown();
    }

    materiaInput?.addEventListener('input', () => renderMateria(materiaInput.value));
    materiaInput?.addEventListener('focus', () => { if (materiaInput.value.trim()) renderMateria(materiaInput.value); });
    materiaInput?.addEventListener('blur', () => setTimeout(hideDropdown, 150));

    materiaInput?.addEventListener('keydown', (e) => {
        const items = materiaList.querySelectorAll('.dropdown-item:not(.disabled)');
        if (!materiaList.classList.contains('show')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            materiaInput.value = items[activeIndex].querySelector('span').textContent;
            hideDropdown();
        } else if (e.key === 'Escape') {
            hideDropdown();
        }

        items.forEach((item, i) => item.classList.toggle('active', i === activeIndex));
        if (activeIndex >= 0) items[activeIndex]?.scrollIntoView({ block: 'nearest' });
    });

    // ─── Dropdown opciones de nota ────────────────────────────────────────
    document.querySelectorAll('.arrow-btn').forEach(btn => {
        const menu = btn.nextElementSibling;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            // Cierra todos los demás
            document.querySelectorAll('.dropdown-menu-notas.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });
    });

    // Click fuera cierra todos los dropdowns de notas
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu-notas.show').forEach(m => m.classList.remove('show'));
    });

    // Seleccionar opción de nota
    document.querySelectorAll('.opcion-nota').forEach(opcion => {
        opcion.addEventListener('click', (e) => {
            e.preventDefault();
            const valor   = opcion.textContent.trim();
            const wrapper = opcion.closest('.nota-wrapper');
            const menu    = opcion.closest('.dropdown-menu-notas');

            // Elimina badge anterior
            wrapper.querySelector('.nota-badge')?.remove();

            // Crea badge
            const badge = document.createElement('span');
            badge.className = `nota-badge nota-badge-${valor.toLowerCase()}`;
            badge.textContent = valor;
            wrapper.querySelector('.arrow-btn').insertAdjacentElement('afterend', badge);

            menu.classList.remove('show');
        });
    });

    // ─── Botón Limpiar ────────────────────────────────────────────────────
    document.getElementById('btn-limpiar')?.addEventListener('click', () => {
        document.querySelectorAll('.nota-input').forEach(i => i.value = '');
        document.querySelectorAll('.nota-badge').forEach(b => b.remove());
    });

    // ─── Botón Guardar ────────────────────────────────────────────────────
    document.getElementById('btn-guardar')?.addEventListener('click', () => {
        const btn = document.getElementById('btn-guardar');
        btn.innerHTML = '<i class="ri-check-line"></i> Guardado';
        btn.style.background = '#16a34a';
        setTimeout(() => {
            btn.innerHTML = '<i class="ri-save-line"></i> Guardar';
            btn.style.background = '';
        }, 2000);
    });

});
