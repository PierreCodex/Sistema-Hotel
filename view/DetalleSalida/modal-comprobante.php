<!-- Modal de Comprobante -->
<div id="modal-comprobante" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
            <div class="modal-header p-3">
                <h4 class="card-title mb-0">Comprobante de Salida</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="alert alert-success rounded-0 mb-0">
                <p class="mb-0">Salida confirmada exitosamente</p>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="ri-checkbox-circle-line text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Salida registrada exitosamente</h5>
                    <p class="text-muted">Seleccione el tipo de comprobante a generar</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tipo de Comprobante</label>
                    <select id="tipo_comprobante" class="form-select">
                        <option value="01">Factura Electrónica</option>
                        <option value="03" selected>Boleta de Venta</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Formato de Impresión</label>
                    <select id="formato_impresion" class="form-select">
                        <option value="80mm" selected>Ticket 80mm (Estándar)</option>
                        <option value="50mm">Ticket 50mm (Compacto)</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="button" id="btn_generar_factura" class="btn btn-success waves-effect waves-light">
                        <i class="ri-file-text-line"></i> Generar Comprobante Electrónico
                    </button>
                 
                </div>
                
                <div id="mensaje_factura" class="mt-3" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->