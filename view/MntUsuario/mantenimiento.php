<div id="modalmantenimiento" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lbltitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
            </div>
            <form method="post" id="mantenimiento_form">
                <div class="modal-body">
                    <input type="hidden" name="usu_id" id="usu_id"/>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="usu_nom" name="usu_nom" required/>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="usu_ape" name="usu_ape" required/>
                                
                            </div>
                        </div>
                    </div>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">DNI</label>
                                <input type="text" class="form-control" id="usu_dni" name="usu_dni" required maxlength="8" pattern="[0-9]{8}"/>
                             
                                <div class="invalid-feedback">
                                    DEBE SER EXACTAMENTE 8 CARACTERES
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="usu_correo" name="usu_correo" required/>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="usu_pass" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="usu_pass" name="usu_pass" required minlength="8" maxlength="20"/>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="ri-eye-line" id="toggleIcon"></i>
                                    </button>
                                </div>
                                
                                <!-- Indicador de fortaleza de contraseña -->
                                <div class="password-strength-container mt-2" id="passwordStrengthContainer" style="display: none;">
                                    <div class="password-strength-bar mb-2">
                                        <div class="strength-bar" id="strengthBar"></div>
                                    </div>
                                    <div class="password-requirements">
                                        <div class="requirement invalid" id="req-length">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Mínimo 8 caracteres</span>
                                        </div>
                                        <div class="requirement invalid" id="req-uppercase">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Al menos una mayúscula (A-Z)</span>
                                        </div>
                                        <div class="requirement invalid" id="req-lowercase">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Al menos una minúscula (a-z)</span>
                                        </div>
                                        <div class="requirement invalid" id="req-number">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Al menos un número (0-9)</span>
                                        </div>
                                        <div class="requirement invalid" id="req-special">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Al menos un carácter especial (!@#$%^&*)</span>
                                        </div>
                                    </div>
                                    <div class="strength-text mt-2">
                                        <small id="strengthText" class="text-muted">Fortaleza: <span id="strengthLevel">Muy débil</span></small>
                                    </div>
                                </div>
                                
                                <div class="valid-feedback">
                                    ¡Contraseña segura!
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, ingrese una contraseña que cumpla con todos los requisitos.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row gy-2">
                        <div class="col-md-12">
                            <div>
                                <label for="valueInput" class="form-label">Rol</label>
                                <select type="text" class="form-control form-select" name="rol_id" id="rol_id" aria-label="Seleccionar">
                                    <option selected>Seleccionar</option>

                                </select>
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