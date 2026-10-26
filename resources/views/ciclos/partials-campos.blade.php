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
</div>
