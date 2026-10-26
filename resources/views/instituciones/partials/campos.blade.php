{{-- Datos de la institución (lo usan el alta/edición del superadmin y "Mi institución") --}}
<div class="row g-3">
    <div class="col-md-8">
        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $institucion->nombre) }}"
               class="form-control @error('nombre') is-invalid @enderror" maxlength="150" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="nombre_corto" class="form-label">Nombre corto</label>
        <input type="text" name="nombre_corto" id="nombre_corto" value="{{ old('nombre_corto', $institucion->nombre_corto) }}"
               class="form-control @error('nombre_corto') is-invalid @enderror" maxlength="60" placeholder="Ej: San Martín">
        @error('nombre_corto') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="cue" class="form-label">CUE</label>
        <input type="text" name="cue" id="cue" value="{{ old('cue', $institucion->cue) }}"
               class="form-control @error('cue') is-invalid @enderror" inputmode="numeric" maxlength="12">
        @error('cue') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="cuit" class="form-label">CUIT</label>
        <input type="text" name="cuit" id="cuit" value="{{ old('cuit', $institucion->cuit) }}"
               class="form-control @error('cuit') is-invalid @enderror" inputmode="numeric" maxlength="13" placeholder="30-12345678-9">
        @error('cuit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="tipo_gestion" class="form-label">Gestión <span class="text-danger">*</span></label>
        <select name="tipo_gestion" id="tipo_gestion" class="form-select @error('tipo_gestion') is-invalid @enderror">
            @foreach (['privada' => 'Privada', 'estatal' => 'Estatal'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('tipo_gestion', $institucion->tipo_gestion) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        @error('tipo_gestion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="provincia" class="form-label">Provincia <span class="text-danger">*</span></label>
        <select name="provincia" id="provincia" class="form-select @error('provincia') is-invalid @enderror">
            @foreach (\App\Models\Institucion::PROVINCIAS as $prov)
                <option value="{{ $prov }}" @selected(old('provincia', $institucion->provincia) === $prov)>{{ $prov }}</option>
            @endforeach
        </select>
        @error('provincia') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="localidad" class="form-label">Localidad</label>
        <input type="text" name="localidad" id="localidad" value="{{ old('localidad', $institucion->localidad) }}"
               class="form-control @error('localidad') is-invalid @enderror" maxlength="100">
        @error('localidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="domicilio" class="form-label">Domicilio</label>
        <input type="text" name="domicilio" id="domicilio" value="{{ old('domicilio', $institucion->domicilio) }}"
               class="form-control @error('domicilio') is-invalid @enderror" maxlength="150">
        @error('domicilio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $institucion->telefono) }}"
               class="form-control @error('telefono') is-invalid @enderror" maxlength="40">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8">
        <label for="email" class="form-label">Email de contacto</label>
        <input type="email" name="email" id="email" value="{{ old('email', $institucion->email) }}"
               class="form-control @error('email') is-invalid @enderror" maxlength="150">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="logo" class="form-label">Logo</label>
        <div class="d-flex align-items-center gap-3">
            @if ($institucion->logo_url)
                <img src="{{ $institucion->logo_url }}" alt="Logo" class="rounded border bg-white" style="height:64px;width:64px;object-fit:contain">
            @endif
            <div class="flex-grow-1">
                <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/webp"
                       class="form-control @error('logo') is-invalid @enderror">
                <div class="form-text">PNG, JPG o WEBP de hasta 1 MB. Se usa en constancias y boletines.</div>
                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($institucion->logo_url)
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="quitar_logo" id="quitar_logo" value="1">
                        <label class="form-check-label small" for="quitar_logo">Quitar el logo actual</label>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
