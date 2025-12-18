<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="cli_id" id="cli_id" />

                    <div class="row gy-2">
                        <div class="col-md-6">
                            <div>
                                <label for="valueInput" class="form-label">Tipo Doc. Identidad</label>
                                <select type="text" class="form-control form-select" name="cli_tipo_doc" id="cli_tipo_doc" aria-label="Seleccionar">
                                    <option value="">Seleccionar</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div>
                                <label for="hab_pre" class="form-label">Numero <span class="text-danger">*</span></label>
                                <div class="input-group">
                     
                                    <div class="input-group">
                                        <input type="text" class="form-control" aria-label="Recipient's username" aria-describedby="button-addon2" id="cli_doc" name="cli_doc">
                                        <button class="btn btn-outline-success" type="button" id="btnBuscarDoc">Buscar</button>
                                    </div>
                                    <div class="invalid-feedback" id="cli_doc_feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row gy-2" id="row_nombre_apellido">
                        <div class="col-md-6" id="col_nombre">
                            <div>
                                <label for="cli_nom" class="form-label" id="lbl_nombre">Nombre</label>
                                <input type="text" class="form-control" id="cli_nom" name="cli_nom" />
                            </div>
                        </div>
                        <div class="col-md-6" id="col_apellido">
                            <div>
                                <label for="cli_ape" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="cli_ape" name="cli_ape" />
                            </div>
                        </div>
                    </div>
                    <div class="row gy-2" id="row_razon_social" style="display: none;">
                        <div class="col-md-12">
                            <div>
                                <label for="cli_razon_social" class="form-label">Razón Social / Nombre Completo</label>
                                <input type="text" class="form-control" id="cli_razon_social" name="cli_razon_social" />
                            </div>
                        </div>
                    </div>
                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="cli_direcc" name="cli_direcc" />
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