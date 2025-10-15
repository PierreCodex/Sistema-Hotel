function init(){
    $("#mantenimiento_form").on("submit",function(e){
        guardaryeditar(e);
    });
    combo_rol();
    
    // Inicializar validaciones Bootstrap
    initBootstrapValidation();
    
    // Inicializar validador de fortaleza de contraseña
    initPasswordStrengthValidator();
}

// Función para manejar las validaciones de Bootstrap
function initBootstrapValidation() {
    // Validación en tiempo real para campos de texto
    $('#usu_nom, #usu_ape').on('input', function() {
        validateTextField(this, 2, 50);
    });
    
    // Validación para DNI
    $('#usu_dni').on('input', function() {
        validateDNI(this);
    });
    
    // Validación para email
    $('#usu_correo').on('input', function() {
        validateEmail(this);
    });
    
    // Validación para contraseña
    $('#usu_pass').on('input', function() {
        validatePassword(this);
    });
    
    // Validación para select
    $('#rol_id').on('change', function() {
        validateSelect(this);
    });
}

// Validar campos de texto
function validateTextField(field, minLength, maxLength) {
    const value = field.value.trim();
    const isValid = value.length >= minLength && value.length <= maxLength;
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// Validar DNI
function validateDNI(field) {
    const value = field.value.trim();
    const dniPattern = /^[0-9]{8}$/; // Exactamente 8 dígitos
    const isValid = dniPattern.test(value);
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// Validar email
function validateEmail(field) {
    const value = field.value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailPattern.test(value);
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// Validar contraseña
function validatePassword(field) {
    const value = field.value.trim();
    
    if (value === '') {
        field.classList.remove('is-valid', 'is-invalid');
        return false;
    }
    
    // Si el campo contiene solo asteriscos, es válido (contraseña existente)
    if (/^\*+$/.test(value)) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
    
    // Usar la validación de fortaleza para contraseñas nuevas
    const strength = validatePasswordStrength(value);
    const isValid = strength >= 3; // Requiere al menos fortaleza "Regular"
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// Validar select
function validateSelect(field) {
    const value = field.value;
    const isValid = value !== '' && value !== null;
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

function guardaryeditar(e){
    e.preventDefault();
    
    // Validar todos los campos antes de enviar
    const form = document.getElementById('mantenimiento_form');
    const isFormValid = validateForm();
    
    if (!isFormValid) {
        // Mostrar mensaje de error si el formulario no es válido
        swal.fire({
            title: 'Error de Validación',
            text: 'Por favor, corrija los errores en el formulario antes de continuar.',
            icon: 'error'
        });
        
        // Agregar clase was-validated para mostrar todos los errores
        form.classList.add('was-validated');
        return;
    }
    
    var formData = new FormData($("#mantenimiento_form")[0]);
    
    /**
     * FUNCIONALIDAD FRONTEND: PRESERVACIÓN DE CONTRASEÑA EN MODO EDICIÓN
     * 
     * PROBLEMA ORIGINAL:
     * Al editar un usuario, el campo contraseña (que muestra asteriscos) se enviaba
     * vacío al backend, causando que la contraseña se borrara en la base de datos.
     * 
     * SOLUCIÓN IMPLEMENTADA:
     * 1. Detectar si estamos en modo edición usando el atributo 'data-editing'
     * 2. Si es edición: ELIMINAR completamente el campo 'usu_pass' del FormData
     * 3. Si es nuevo registro: MANTENER el campo 'usu_pass' en el FormData
     * 
     * RESULTADO:
     * - Modo edición: Backend recibe datos SIN campo contraseña → usa método sin contraseña
     * - Modo nuevo: Backend recibe datos CON campo contraseña → usa método normal
     */
    
    // Detectar si estamos en modo edición basado en el atributo del campo contraseña
    const isEditing = $('#usu_pass').attr('data-editing') === 'true';
    
    if (isEditing) {
        // MODO EDICIÓN: Eliminar campo contraseña para preservar la contraseña original
        formData.delete('usu_pass');
        console.log('Modo edición: Campo contraseña eliminado del FormData - Se preservará contraseña original');
    } else {
        // MODO NUEVO REGISTRO: Mantener campo contraseña para crear usuario con contraseña
        console.log('Modo nuevo registro: Contraseña incluida en FormData - Se creará usuario con nueva contraseña');
    }
    
    $.ajax({
        url:"../../controller/usuario.php?op=guardaryeditar",
        type:"POST",
        data:formData,
        contentType:false,
        processData:false,
        success:function(data){
            $('#table_data').DataTable().ajax.reload();
            $('#modalmantenimiento').modal('hide');
            
            // Limpiar clases de validación
            form.classList.remove('was-validated');
            clearValidationClasses();

            swal.fire({
                title:'Usuario',
                text: 'Registro Confirmado',
                icon: 'success'
            });
        },
        error: function(xhr, status, error) {
            swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.',
                icon: 'error'
            });
        }
    });
}

// Función para validar todo el formulario
function validateForm() {
    const nomValid = validateTextField(document.getElementById('usu_nom'), 2, 50);
    const apeValid = validateTextField(document.getElementById('usu_ape'), 2, 50);
    const dniValid = validateDNI(document.getElementById('usu_dni'));
    const emailValid = validateEmail(document.getElementById('usu_correo'));
    const passValid = validatePassword(document.getElementById('usu_pass'));
    const rolValid = validateSelect(document.getElementById('rol_id'));
    
    return nomValid && apeValid && dniValid && emailValid && passValid && rolValid;
}

// Función para limpiar clases de validación
function clearValidationClasses() {
    const fields = ['usu_nom', 'usu_ape', 'usu_dni', 'usu_correo', 'usu_pass', 'rol_id'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-valid', 'is-invalid');
        }
    });
}

function combo_rol(){
    $.post("../../controller/usuario.php?op=combo_rol",function(data){
        $('#rol_id').html(data);
    });
}

$(document).ready(function(){

    $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
        ],
        "ajax":{
            url:"../../controller/usuario.php?op=listar",
            type:"post"
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo":true,
        "iDisplayLength": 10,
        "order": [[ 0, "desc" ]],
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
    });

});

function editar(usu_id){
    // Limpiar clases de validación antes de cargar datos
    const form = document.getElementById('mantenimiento_form');
    form.classList.remove('was-validated');
    clearValidationClasses();
    
    // Resetear indicador de fortaleza de contraseña
    resetPasswordStrengthIndicator();
    
    $.post("../../controller/usuario.php?op=mostrar",{usu_id:usu_id},function(data){
        data=JSON.parse(data);
        $('#usu_id').val(data.USU_ID);
        $('#usu_nom').val(data.USU_NOM);
        $('#usu_ape').val(data.USU_APE);
        $('#usu_dni').val(data.USU_DNI);
        $('#usu_correo').val(data.USU_CORREO);
        $('#rol_id').val(data.ROL_ID);
        
        // Configurar campos editables según las reglas de negocio
        // Solo permitir editar: Nombre, Apellido, DNI, Correo, Rol
        // La contraseña se muestra con asteriscos pero no se modifica en BD
        
        // Mostrar asteriscos equivalentes a la longitud de la contraseña (solo visual)
        const passwordLength = data.USU_PASS ? data.USU_PASS.length : 8;
        $('#usu_pass').val('*'.repeat(passwordLength));
        
        // Marcar que estamos en modo edición y bloquear el campo de contraseña
        $('#usu_pass').attr('data-editing', 'true');
        $('#usu_pass').attr('data-original-length', passwordLength);
        $('#usu_pass').prop('readonly', true);
        $('#usu_pass').addClass('bg-light');
        $('#usu_pass').attr('title', 'La contraseña no se modifica durante la edición');
        
        console.log('Modo edición activado: Contraseña protegida');
        
        // Validar campos cargados para mostrar estado válido
        setTimeout(() => {
            validateTextField(document.getElementById('usu_nom'), 2, 50);
            validateTextField(document.getElementById('usu_ape'), 2, 50);
            validateDNI(document.getElementById('usu_dni'));
            validateEmail(document.getElementById('usu_correo'));
            validateSelect(document.getElementById('rol_id'));
        }, 100);
    });
    $('#lbltitulo').html('Editar Registro');
    $('#modalmantenimiento').modal('show');
}

function eliminar(usu_id){
    swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/usuario.php?op=eliminar",{usu_id:usu_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Usuario',
                text: 'Registro Eliminado',
                icon: 'success'
            });
        }
    });
}

function activar(usu_id){
    swal.fire({
        title:"Activar!",
        text:"Desea Activar el Registro?",
        icon: "question",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/usuario.php?op=activar",{usu_id:usu_id},function(data){
                console.log(data);
            });

            $('#table_data').DataTable().ajax.reload();

            swal.fire({
                title:'Usuario',
                text: 'Registro Activado',
                icon: 'success'
            });
        }
    });
}

function buscar(){
    var buscar = $('#txt_buscar').val();
    if(buscar.length >= 3){
        $('#table_data').DataTable().destroy();
        $('#table_data').DataTable({
            "aProcessing": true,
            "aServerSide": true,
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
            ],
            "ajax":{
                url:"../../controller/usuario.php?op=buscar",
                type:"post",
                data:{buscar:buscar}
            },
            "bDestroy": true,
            "responsive": true,
            "bInfo":true,
            "iDisplayLength": 10,
            "order": [[ 0, "desc" ]],
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
        });
    } else {
        // Si el texto de búsqueda es menor a 3 caracteres, recargar tabla normal
        $('#table_data').DataTable().destroy();
        location.reload();
    }
}

function listar_todos(){
    $('#table_data').DataTable().destroy();
    $('#table_data').DataTable({
        "aProcessing": true,
        "aServerSide": true,
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
        ],
        "ajax":{
            url:"../../controller/usuario.php?op=listar_todos",
            type:"post"
        },
        "bDestroy": true,
        "responsive": true,
        "bInfo":true,
        "iDisplayLength": 10,
        "order": [[ 0, "desc" ]],
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
    });
}

$(document).on("click","#btnnuevo",function(){
    $('#usu_id').val('');
    $('#usu_nom').val('');
    $('#usu_ape').val('');
    $('#usu_dni').val('');
    $('#usu_correo').val('');
    $('#usu_pass').val('');
    $('#rol_id').val('');
    $('#lbltitulo').html('Nuevo Registro');
    $("#mantenimiento_form")[0].reset();
    
    // Limpiar atributos de edición de contraseña y habilitar el campo
    $('#usu_pass').removeAttr('data-editing');
    $('#usu_pass').removeAttr('data-original-length');
    $('#usu_pass').removeAttr('title');
    $('#usu_pass').prop('readonly', false);
    $('#usu_pass').removeClass('bg-light');
    
    console.log('Modo nuevo registro: Campo contraseña habilitado para edición');
    
    // Limpiar clases de validación
    const form = document.getElementById('mantenimiento_form');
    form.classList.remove('was-validated');
    clearValidationClasses();
    
    // Resetear indicador de fortaleza de contraseña
    resetPasswordStrengthIndicator();
    
    combo_rol();
    $('#modalmantenimiento').modal('show');
});

$(document).on("keyup","#txt_buscar",function(){
    buscar();
});

$(document).on("click","#btn_todos",function(){
    listar_todos();
});

$(document).on("click","#btn_activos",function(){
    location.reload();
});

// Funciones para validación de contraseña en tiempo real
function initPasswordStrengthValidator() {
    const passwordInput = document.getElementById('usu_pass');
    const toggleButton = document.getElementById('togglePassword');
    const strengthContainer = document.getElementById('passwordStrengthContainer');
    
    if (passwordInput) {
        // Mostrar contenedor cuando el usuario haga foco en el campo
        passwordInput.addEventListener('focus', function() {
            // Solo limpiar si no estamos en modo edición
            const isEditing = this.getAttribute('data-editing') === 'true';
            
            if (!isEditing && /^\*+$/.test(this.value)) {
                this.value = '';
                resetPasswordStrengthIndicator();
            }
            
            // Solo mostrar el contenedor si no estamos en modo edición
            if (!isEditing && strengthContainer) {
                strengthContainer.style.display = 'block';
            }
        });
        
        // Evento para validación en tiempo real
        passwordInput.addEventListener('input', function() {
            // Si el campo contiene solo asteriscos, no mostrar indicador de fortaleza
            if (/^\*+$/.test(this.value)) {
                if (strengthContainer) {
                    strengthContainer.style.display = 'none';
                }
                return;
            }
            
            if (strengthContainer) {
                strengthContainer.style.display = 'block';
            }
            validatePasswordStrength(this.value);
        });
        
        // Evento para mostrar/ocultar contraseña
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                togglePasswordVisibility();
            });
        }
    }
}

function validatePasswordStrength(password) {
    const requirements = {
        length: password.length >= 8 && password.length <= 20,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };
    
    // Actualizar indicadores visuales de requisitos
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    updateRequirement('req-special', requirements.special);
    
    // Calcular fortaleza
    const strength = calculatePasswordStrength(requirements);
    updateStrengthBar(strength);
    updateStrengthText(strength);
    
    return strength;
}

function updateRequirement(elementId, isValid) {
    const element = document.getElementById(elementId);
    if (element) {
        const icon = element.querySelector('i');
        
        if (isValid) {
            element.classList.remove('invalid');
            element.classList.add('valid');
            icon.className = 'fas fa-check-circle';
        } else {
            element.classList.remove('valid');
            element.classList.add('invalid');
            icon.className = 'fas fa-times-circle';
        }
    }
}

function calculatePasswordStrength(requirements) {
    const validCount = Object.values(requirements).filter(Boolean).length;
    
    if (validCount === 0) return 0;
    if (validCount === 1) return 1;
    if (validCount === 2) return 2;
    if (validCount === 3) return 3;
    if (validCount === 4) return 4;
    if (validCount === 5) return 5;
    
    return 0;
}

function updateStrengthBar(strength) {
    const strengthBar = document.querySelector('.strength-bar');
    if (strengthBar) {
        // Remover clases anteriores
        strengthBar.className = 'strength-bar';
        
        // Agregar nueva clase según fortaleza
        switch (strength) {
            case 0:
                strengthBar.style.width = '0%';
                break;
            case 1:
                strengthBar.classList.add('strength-very-weak');
                break;
            case 2:
                strengthBar.classList.add('strength-weak');
                break;
            case 3:
                strengthBar.classList.add('strength-fair');
                break;
            case 4:
                strengthBar.classList.add('strength-good');
                break;
            case 5:
                strengthBar.classList.add('strength-strong');
                break;
        }
    }
}

function updateStrengthText(strength) {
    const strengthLevel = document.getElementById('strengthLevel');
    if (strengthLevel) {
        // Remover clases anteriores
        strengthLevel.className = '';
        
        switch (strength) {
            case 0:
                strengthLevel.textContent = '';
                break;
            case 1:
                strengthLevel.textContent = 'Muy Débil';
                strengthLevel.classList.add('strength-very-weak-text');
                break;
            case 2:
                strengthLevel.textContent = 'Débil';
                strengthLevel.classList.add('strength-weak-text');
                break;
            case 3:
                strengthLevel.textContent = 'Regular';
                strengthLevel.classList.add('strength-fair-text');
                break;
            case 4:
                strengthLevel.textContent = 'Buena';
                strengthLevel.classList.add('strength-good-text');
                break;
            case 5:
                strengthLevel.textContent = 'Fuerte';
                strengthLevel.classList.add('strength-strong-text');
                break;
        }
    }
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('usu_pass');
    const toggleIcon = document.querySelector('#togglePassword i');
    
    if (passwordInput && toggleIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }
}

// Función para validar contraseña en el formulario
function validatePasswordField() {
    const passwordInput = document.getElementById('usu_pass');
    if (passwordInput && passwordInput.value.trim() !== '') {
        const strength = validatePasswordStrength(passwordInput.value);
        return strength >= 3; // Requiere al menos fortaleza "Regular"
    }
    return passwordInput && passwordInput.value.trim() !== '';
}

// Función para resetear el indicador de fortaleza de contraseña
function resetPasswordStrengthIndicator() {
    const strengthContainer = document.getElementById('passwordStrengthContainer');
    const passwordInput = document.getElementById('usu_pass');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthLevel = document.getElementById('strengthLevel');
    
    // Ocultar el contenedor
    if (strengthContainer) {
        strengthContainer.style.display = 'none';
    }
    
    // Limpiar el campo de contraseña
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.classList.remove('is-valid', 'is-invalid');
    }
    
    // Resetear la barra de fortaleza
    if (strengthBar) {
        strengthBar.className = 'strength-bar';
        strengthBar.style.width = '0%';
    }
    
    // Resetear el texto de fortaleza
    if (strengthLevel) {
        strengthLevel.textContent = '';
        strengthLevel.className = '';
    }
    
    // Resetear todos los requisitos a estado inválido
    const requirements = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'];
    requirements.forEach(reqId => {
        const element = document.getElementById(reqId);
        if (element) {
            const icon = element.querySelector('i');
            element.classList.remove('valid');
            element.classList.add('invalid');
            if (icon) {
                icon.className = 'fas fa-times-circle';
            }
        }
    });
}

// Inicializar cuando el documento esté listo
$(document).ready(function(){
    init();
});