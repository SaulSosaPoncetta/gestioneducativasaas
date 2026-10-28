{{-- Campos del ciclo lectivo (alta y edición) --}}
<div class="row g-3">
    <div class="col-4">
        <label for="anio" class="form-label">Año <span class="text-danger">*</span></label>
        <input type="number" name="anio" id="anio" min="2000" max="2100" value="{{ old('anio', $ciclo->anio) }}"
               class="form-control @error('anio') is-invalid @enderror" required>
        @error('anio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-8">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" name="nombre" id="nombre" maxlength="80" value="{{ old('nombre', $ciclo->nombre) }}"
               class="form-control @error('nombre') is-invalid @enderror" placeholder="Ciclo lectivo {{ $ciclo->anio }}">
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-6">
        <label for="fecha_inicio" class="form-label">Inicio de clases <span class="text-danger">*</span></label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio', $ciclo->fecha_inicio?->format('Y-m-d')) }}"
               class="form-control @error('fecha_inicio') is-invalid @enderror" required>
        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-6">
        <label for="fecha_fin" class="form-label">Fin de clases <span class="text-danger">*</span></label>
        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin', $ciclo->fecha_fin?->format('Y-m-d')) }}"
               class="form-control @error('fecha_fin') is-invalid @enderror" required>
        @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if (($institucionActual ?? null)?->nivelesActivos()->contains(fn ($n) => $n->usaCarreras()))
        <div class="col-12">
            <div class="small fw-semibold mt-2">Nivel superior: inscripción a cursadas desde el portal del estudiante</div>
        </div>
        <div class="col-6">
            <label for="inscripcion_cursadas_desde" class="form-label small">Desde</label>
            <input type="date" name="inscripcion_cursadas_desde" id="inscripcion_cursadas_desde"
                   value="{{ old('inscripcion_cursadas_desde', $ciclo->inscripcion_cursadas_desde?->format('Y-m-d')) }}"
                   class="form-control @error('inscripcion_cursadas_desde') is-invalid @enderror">
            @error('inscripcion_cursadas_desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-6">
            <label for="inscripcion_cursadas_hasta" class="form-label small">Hasta</label>
            <input type="date" name="inscripcion_cursadas_hasta" id="inscripcion_cursadas_hasta"
                   value="{{ old('inscripcion_cursadas_hasta', $ciclo->inscripcion_cursadas_hasta?->format('Y-m-d')) }}"
                   class="form-control @error('inscripcion_cursadas_hasta') is-invalid @enderror">
            @error('inscripcion_cursadas_hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif
</div>
