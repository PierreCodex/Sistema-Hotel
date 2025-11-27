<!-- Modal de Comprobante -->
<div id="modal-comprobante" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
            <!-- Header con número de comprobante -->
            <div class="modal-header p-3 bg-primary">
                <h4 class="card-title mb-0 text-white" id="titulo_comprobante">
                    <i class="ri-file-list-3-line me-2"></i>Comprobante: <span id="numero_comprobante">---</span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Estado de la boleta -->
            <div id="estado_boleta" class="alert alert-info rounded-0 mb-0 d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span id="mensaje_estado">Generando comprobante electrónico...</span>
            </div>
            
            <div class="modal-body">
                <!-- Contenido mientras genera -->
                <div id="generando_contenido" class="text-center py-4">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Enviando a SUNAT...</p>
                </div>
                
                <!-- Contenido cuando ya se generó -->
                <div id="generado_contenido" style="display: none;">
                    <!-- Botones de formato de descarga -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <button type="button" class="btn btn-soft-primary w-100 py-3 btn-formato" data-formato="80mm">
                                <i class="ri-file-pdf-line fs-2 d-block mb-1"></i>
                                <span class="fw-semibold">80MM</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-soft-primary w-100 py-3 btn-formato" data-formato="50mm">
                                <i class="ri-file-pdf-line fs-2 d-block mb-1"></i>
                                <span class="fw-semibold">50MM</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Opción de enviar por WhatsApp o Email (futuro) -->
                    <div class="border-top pt-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">+51</span>
                                    <input type="text" id="whatsapp_numero" class="form-control" placeholder="Número de WhatsApp">
                                    <button type="button" id="btn_enviar_whatsapp" class="btn btn-success">
                                        <i class="ri-whatsapp-line"></i> Enviar PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mensaje de error -->
                <div id="error_contenido" style="display: none;">
                    <div class="text-center py-4">
                        <i class="ri-error-warning-line text-danger" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-danger">Error al generar comprobante</h5>
                        <p id="error_mensaje" class="text-muted"></p>
                        <button type="button" id="btn_reintentar" class="btn btn-warning">
                            <i class="ri-refresh-line"></i> Reintentar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>