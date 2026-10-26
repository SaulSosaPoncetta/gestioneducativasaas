{{-- Niveles que ofrece la institución (alta/edición del superadmin y "Mi institución") --}}
@php
    $actuales = $institucion->exists
        ? $institucion->niveles->keyBy(fn ($n) => $n->nivel->value)
        : collect();
@endphp

@error('niveles')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
@enderror

<div class="vstack gap-2">
    @foreach (\App\Enums\NivelEducativo::cases() as $nivel)
        @php
            $v      = $nivel->value;
            $actual = $actuales->get($v);
            $activo = (bool) old("niveles.$v.activo", $actual?->activo ?? false);
        @endphp

        <div class="border rounded p-3 bloque-nivel {{ $activo ? 'border-primary-subtle bg-primary-subtle bg-opacity-25' : '' }}">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="niveles[{{ $v }}][activo]" value="0">
                <input class="form-check-input switch-nivel" type="checkbox" role="switch"
                       name="niveles[{{ $v }}][activo]" id="nivel_{{ $v }}" value="1" @checked($activo)>
                <label class="form-check-label fw-semibold" for="nivel_{{ $v }}">
                    <i class="bi {{ $nivel->icono() }} me-1"></i> {{ $nivel->label() }}
                </label>
            </div>

            <div class="row g-2 mt-1 campos-nivel {{ $activo ? '' : 'd-none' }}">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Denominación oficial</label>
                    <input type="text" name="niveles[{{ $v }}][denominacion]"
                           value="{{ old("niveles.$v.denominacion", $actual?->denominacion) }}"
                           class="form-control form-control-sm @error("niveles.$v.denominacion") is-invalid @enderror"
                           maxlength="150" placeholder="Ej: {{ $nivel->ejemploDenominacion() }}">
                    @error("niveles.$v.denominacion") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">CUE anexo</label>
                    <input type="text" name="niveles[{{ $v }}][cue_anexo]" inputmode="numeric" maxlength="12"
                           value="{{ old("niveles.$v.cue_anexo", $actual?->cue_anexo) }}"
                           class="form-control form-control-sm @error("niveles.$v.cue_anexo") is-invalid @enderror">
                    @error("niveles.$v.cue_anexo") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @unless ($nivel->usaCarreras())
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Cantidad de {{ Str::lower(Str::plural($nivel->unidadCurso())) }}</label>
                        <input type="number" name="niveles[{{ $v }}][duracion]" min="1" max="10"
                               value="{{ old("niveles.$v.duracion", $actual?->duracion ?? $nivel->duracionSugerida()) }}"
                               class="form-control form-control-sm @error("niveles.$v.duracion") is-invalid @enderror">
                        @error("niveles.$v.duracion") <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @else
                    <div class="col-md-3 d-flex align-items-end">
                        <span class="small text-muted">La duración se define en cada carrera.</span>
                    </div>
                @endunless
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.switch-nivel').forEach((sw) => {
            sw.addEventListener('change', () => {
                const bloque = sw.closest('.bloque-nivel');
                bloque.querySelector('.campos-nivel').classList.toggle('d-none', !sw.checked);
                bloque.classList.toggle('border-primary-subtle', sw.checked);
                bloque.classList.toggle('bg-primary-subtle', sw.checked);
                bloque.classList.toggle('bg-opacity-25', sw.checked);
            });
        });
    });
</script>
@endpush
