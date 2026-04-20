/* ================================================================
   SportLink - JavaScript del cliente
   Maneja interacciones simples del UI: chips de dias, validacion de
   formularios, atajos de teclado en el buscador.
   ================================================================ */

(function () {
    'use strict';

    // --- Chips de dias: mantener input hidden sincronizado ---
    document.querySelectorAll('.chip-group').forEach(group => {
        const hiddenName = group.dataset.chipGroup || 'dias';
        const hidden = document.querySelector(`input[type="hidden"][name="${hiddenName}"]`)
                    || group.parentElement.querySelector(`input[type="hidden"][name="${hiddenName}"]`);

        const sync = () => {
            const vals = [...group.querySelectorAll('input[type="checkbox"]:checked')]
                .map(i => i.value);
            if (hidden) hidden.value = vals.join(',');
        };

        group.addEventListener('change', e => {
            if (e.target.matches('input[type="checkbox"]')) {
                e.target.closest('.chip').classList.toggle('active', e.target.checked);
                sync();
            }
        });
        sync();
    });

    // --- Atajo: Ctrl/Cmd + K enfoca el campo "deporte" en el buscador ---
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const field = document.querySelector('input[name="deporte"]');
            if (field) { e.preventDefault(); field.focus(); field.select(); }
        }
    });

    // --- Auto-ocultar toasts despues de 5s ---
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => {
            t.style.transition = 'opacity .4s';
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 400);
        }, 5000);
    });

    // --- Confirmar acciones destructivas ---
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
})();
