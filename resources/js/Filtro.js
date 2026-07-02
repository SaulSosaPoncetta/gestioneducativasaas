import $ from 'jquery';
import DataTable from 'datatables.net-dt';

// Enlazamos obligatoriamente DataTables con el jQuery global de Vite
window.jQuery = $;
window.$ = $;

$(document).ready(function () {

    // =========================================================================
    // TABLA ADMINISTRADORES / DOCENTES (selector dinámico)
    // =========================================================================

    const selectorTabla = $('#tablaAdministradores').length
        ? '#tablaAdministradores'
        : '#tablaDocentes';

    if ($(selectorTabla).length) {
        $(selectorTabla).DataTable({
            order: [[0, 'asc']],
            columnDefs: [{
                orderable: false,
                targets: [1, 2, 3, 4]
            }],
            dom: 'tp',
            pageLength: 10,
            language: {
                info:         "Mostrando _START_–_END_ de _TOTAL_ registros",
                infoEmpty:    "Sin resultados",
                infoFiltered: "(filtrado de _MAX_ registros)",
                zeroRecords:  "No se encontraron resultados",
            }
        });
    }

    const tablaActiva = $(selectorTabla).length
        ? $(selectorTabla).DataTable()
        : null;

    if (tablaActiva) {
        // Filtro por texto (Nombre, Apellido o DNI)
        $(document).on('input', '#filtro-nombre, input[name="nombre_apellido"]', function () {
            tablaActiva.search(this.value).draw();
        });

        // Filtro por Estado — col 3 = Estado en administradores y docentes
        $(document).on('change', '#filtro-estado', function () {
            const valor = this.value;
            if (valor === '') {
                tablaActiva.column(2).search('').draw();
            } else {
                tablaActiva.column(2).search('^' + valor + '$', true, false).draw();
            }
        });
    }

    // =========================================================================
    // TABLA ALUMNOS
    // col 0 = Nombre | col 1 = DNI | col 2 = Teléfono | col 3 = Estado | col 4 = Perfil
    // =========================================================================

    if ($('#tablaAlumnos').length) {
        const tablaAlumnos = $('#tablaAlumnos').DataTable({
            order: [[0, 'asc']],
            columnDefs: [{
                orderable: false,
                targets: [1, 2, 3, 4]
            }],
            dom: 'tp',
            pageLength: 15,
            language: {
                info:         "Mostrando _START_–_END_ de _TOTAL_ registros",
                infoEmpty:    "Sin resultados",
                zeroRecords:  "No se encontraron alumnos",
                paginate: { first: "«", last: "»", next: "›", previous: "‹" }
            }
        });

        // Filtro por texto — mismo id que usan administradores (#filtro-nombre)
        $('#filtro-nombre').on('input', function () {
            tablaAlumnos.search(this.value).draw();
        });

        // Filtro por Estado — col 3
        $('#filtro-estado').on('change', function () {
            const valor = this.value;
            if (valor === '') {
                tablaAlumnos.column(2).search('').draw();
            } else {
                tablaAlumnos.column(2).search('^' + valor + '$', true, false).draw();
            }
        });
    }

    // =========================================================================
    // TABLA MATERIAS
    // =========================================================================

    if ($('#tablaMaterias').length) {
        $('#tablaMaterias').DataTable({
            order: [[0, 'asc']],
            dom: 'tp',
            pageLength: 5,
            language: {
                info:        "Mostrando _START_–_END_ de _TOTAL_ registros",
                infoEmpty:   "Sin materias",
                zeroRecords: "No se encontraron materias",
                paginate: { first: "«", last: "»", next: "›", previous: "‹" }
            }
        });
    }

});
