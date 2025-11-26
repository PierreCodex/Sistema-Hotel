<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="tar_id" id="tar_id" />
                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="tar_desc" class="form-label">Descripción <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    id="tar_desc"
                                    name="tar_desc"
                                    maxlength="100"
                                    placeholder="Ingrese la descripción de la tarifa"
                                    autocomplete="off" />
                                <div class="form-text">
                                    <small class="text-muted">Máximo 50 caracteres</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <label for="tar_precio" class="form-label">Precio <span class="text-danger">*</span></label>
                                <input type="number"
                                    class="form-control"
                                    id="tar_precio"
                                    name="tar_precio"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ingrese el precio"
                                    autocomplete="off" />
                                <div class="form-text">
                                    <small class="text-muted">Use dos decimales, ej. 120.50</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" name="action" value="add" class="btn btn-primary ">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>