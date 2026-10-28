{{-- Modal para anular: el botón lleva data-accion (URL) y data-numero --}}
<div class="modal fade" id="modalAnular" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" id="formAnular">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h2 class="modal-title h5">Anular constancia <span data-numero></span></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">La constancia queda en el registro y, si alguien la verifica, figura como anulada.</p>
                <label class="form-label" for="motivo">Motivo</label>
                <input type="text" name="motivo" id="motivo" class="form-control" maxlength="200" required
                       placeholder="Ej.: datos incorrectos, se emitió una nueva">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger">Anular</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalAnular');
    modal?.addEventListener('show.bs.modal', (e) => {
        const btn = e.relatedTarget;
        document.getElementById('formAnular').action = btn.dataset.accion;
        modal.querySelector('[data-numero]').textContent = btn.dataset.numero;
    });
});
</script>
@endpush
