<!-- Modal Ver Detalles del Cliente -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 bg-primary">
                <h6 class="modal-title text-white mb-0" id="modalDetallesLabel">
                    <i class="ri-user-search-line me-1"></i>Detalles del Cliente
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Info Principal -->
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                    <div class="avatar-sm bg-soft-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-user-3-fill text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0" id="det_nombre_completo">-</h6>
                        <small class="text-muted">
                            <span id="det_tipo_doc">DNI</span>: <span id="det_documento">-</span>
                        </small>
                    </div>
                    <span id="det_estado_badge" class="badge bg-success">ACTIVO</span>
                </div>

                <!-- Datos en Grid Compacto -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Nombre</small>
                            <span class="fw-medium" id="det_nombre">-</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Apellido</small>
                            <span class="fw-medium" id="det_apellido">-</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light rounded p-2">
                            <small class="text-muted d-block">Dirección</small>
                            <span class="fw-medium" id="det_direccion">No registrada</span>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <h4 class="text-primary mb-0" id="det_total_visitas">0</h4>
                            <small class="text-muted">Visitas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 text-center">
                            <span class="text-success fw-medium" id="det_ultima_visita">-</span>
                            <small class="text-muted d-block">Última Visita</small>
                        </div>
                    </div>
                </div>

                <!-- Auditoría Compacta -->
                <div class="bg-warning bg-opacity-10 rounded p-2">
                    <div class="row g-1 small">
                        <div class="col-6">
                            <i class="ri-add-circle-line text-success me-1"></i>
                            <span class="text-muted">Creado:</span>
                            <span id="det_fecha_creacion" class="fw-medium">-</span>
                            <small class="d-block text-muted ps-3">por <span id="det_usuario_creacion">Sistema</span></small>
                        </div>
                        <div class="col-6">
                            <i class="ri-edit-circle-line text-primary me-1"></i>
                            <span class="text-muted">Modificado:</span>
                            <span id="det_fecha_modificacion" class="fw-medium">-</span>
                            <small class="d-block text-muted ps-3">por <span id="det_usuario_modificacion">N/A</span></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
