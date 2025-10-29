<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="est_hab_id" id="est_hab_id"/>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="est_hab_nom" class="form-label">Descripción <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="est_hab_nom" 
                                       name="est_hab_nom" 
                                   
                                       maxlength="50"
                                       placeholder="Ingrese la descripción del estado de habitación"
                                       autocomplete="off"/>
                                <div class="form-text">
                                    <small class="text-muted">Máximo 50 caracteres</small>
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