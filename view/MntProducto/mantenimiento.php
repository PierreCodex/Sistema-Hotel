<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="pro_id" id="pro_id" />

                    <div class="row gy-2">
                        <div class="col-md-6">
                            <div>
                                <label for="pro_nom" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    id="pro_nom"
                                    name="pro_nom"

                                    maxlength="50"
                                    placeholder="Ingrese el nombre del producto"
                                    autocomplete="off" />
                                <div class="form-text">
                                    <small class="text-muted">Máximo 50 caracteres</small>
                                </div>
                            </div>
                        </div>
                                     <div class="col-md-6">
                            <div>
                                <label for="pro_pre" class="form-label">Precio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="text" id="pro_pre" name="pro_pre" class="form-control" aria-label="Precio en soles">
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">Máximo 50 caracteres</small>
                                </div>
                            </div>
                        </div>    

                        <div class="col-md-10">
                            <div>
                                <label for="pro_det" class="form-label">Detalle <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    id="pro_det"
                                    name="pro_det"

                                    maxlength="50"
                                    placeholder="Ingrese el detalle del producto"
                                    autocomplete="off" />
                                <div class="form-text">
                                    <small class="text-muted">Máximo 50 caracteres</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div>
                                <label for="pro_cant" class="form-label">Cantidad<span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    id="pro_cant"
                                    name="pro_cant"

                                    maxlength="50"
                                    placeholder="0"
                                    autocomplete="off" />
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