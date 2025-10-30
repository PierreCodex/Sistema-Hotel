<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="hab_id" id="hab_id"/>

                    <div class="row gy-3">
                        <!-- Número de Habitación -->
                        <div class="col-md-6">
                            <div>
                                <label for="hab_num" class="form-label">Número de Habitación <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="hab_num" 
                                       name="hab_num" 
                                       maxlength="10"
                                       placeholder="Ej: 101, A-201, etc."
                                       autocomplete="off"
                                       required/>
                                <div class="form-text">
                                    <small class="text-muted">Máximo 10 caracteres. Debe ser único.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Precio -->
                        <div class="col-md-6">
                            <div>
                                <label for="hab_pre" class="form-label">Precio por Noche <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="hab_pre" 
                                           name="hab_pre" 
                                           step="0.01"
                                           min="0.01"
                                           placeholder="0.00"
                                           autocomplete="off"
                                           required/>
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">Precio en soles (S/). Debe ser mayor a 0.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-12">
                            <div>
                                <label for="hab_det" class="form-label">Descripción <span class="text-danger">*</span></label>
                                <textarea class="form-control" 
                                          id="hab_det" 
                                          name="hab_det" 
                                          rows="3"
                                          maxlength="100"
                                          placeholder="Descripción de la habitación, características, etc."
                                          autocomplete="off"
                                          required></textarea>
                                <div class="form-text">
                                    <small class="text-muted">Máximo 100 caracteres. Describe las características de la habitación.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Piso -->
                        <div class="col-md-4">
                            <div>
                                <label for="hab_piso_id" class="form-label">Piso <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="hab_piso_id" 
                                        name="hab_piso_id" 
                                        required>
                                    <option value="">Seleccione un piso</option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">Seleccione el piso donde se encuentra la habitación.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-4">
                            <div>
                                <label for="hab_cat_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" 
                                        id="hab_cat_id" 
                                        name="hab_cat_id" 
                                        required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">Tipo de habitación (Simple, Doble, Suite, etc.).</small>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de Habitación - OCULTO: Se asigna automáticamente como "Disponible" -->
                        <input type="hidden" id="hab_est_id" name="hab_est_id" value="0" />
                        
                        <!-- Espacio adicional para información -->
                        <div class="col-md-4">
                            <div class="alert alert-success" role="alert">
                                <i class="mdi mdi-check-circle-outline me-2"></i>
                                <strong>Estado:</strong> Las habitaciones nuevas se marcan automáticamente como <strong>"Disponible"</strong>.
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="col-md-12">
                            <div class="alert alert-info" role="alert">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <strong>Información:</strong> Los campos marcados con <span class="text-danger">*</span> son obligatorios. 
                                El número de habitación debe ser único en el sistema.
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cerrar
                    </button>
                    <button type="submit" name="action" value="add" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>