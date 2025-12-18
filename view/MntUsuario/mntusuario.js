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
    
    // Manejar campo de contraseña en modo edición
    $('#usu_pass').on('focus', function() {
        if ($(this).attr('data-editing') === 'true') {
            // En modo edición, el campo funciona normalmente
            // La contraseña se muestra con puntos por ser tipo password
        }
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
    const duplicateMessage = document.getElementById('dni-duplicate-message');
    const dniFeedback = document.getElementById('dni-feedback');
    
    // Limpiar estados previos
    duplicateMessage.style.display = 'none';
    dniFeedback.textContent = '';
    
    // Validar si el campo está vacío (solo marcar como inválido, SIN mensaje de formato)
    if (value === '') {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        // NO mostrar mensaje de formato cuando está vacío
        return false;
    }
    
    // Validar formato de DNI
    if (!dniPattern.test(value)) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        dniFeedback.textContent = 'DEBE SER EXACTAMENTE 8 CARACTERES';
        return false;
    }
    
    // Si el formato es válido, verificar duplicados
    if (value !== '') {
        // Verificar si estamos en modo edición
        const usuId = $('#usu_id').val();
        const isEditing = usuId !== '';
        
        // Preparar datos para enviar
        const postData = { usu_dni: value };
        if (isEditing) {
            postData.usu_id = usuId;
        }
        
        $.ajax({
            url: "../../controller/usuario.php?op=validar_dni",
            type: "POST",
            data: postData,
            dataType: "json",
            success: function(response) {
                if (response.existe) {
                    // DNI ya existe
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                    duplicateMessage.style.display = 'block';
                    dniFeedback.textContent = 'DNI ya existente';
                } else {
                    // DNI disponible
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                    duplicateMessage.style.display = 'none';
                }
            },
            error: function() {
                // En caso de error, solo validar formato
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                duplicateMessage.style.display = 'none';
            }
        });
    }
    
    return dniPattern.test(value);
}

// Validar email
function validateEmail(field) {
    const value = field.value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const duplicateMessage = document.getElementById('email-duplicate-message');
    const emailFeedback = document.getElementById('email-feedback');
    
    // Limpiar estados previos
    field.classList.remove('is-valid', 'is-invalid');
    duplicateMessage.style.display = 'none';
    emailFeedback.textContent = '';
    
    // Validar si el campo está vacío (solo marcar como inválido, SIN mensaje de formato)
    if (value === '') {
        field.classList.add('is-invalid');
        // NO mostrar mensaje de "email inválido" cuando está vacío
        return false;
    }
    
    // Validar formato de email (solo si no está vacío)
    if (!emailPattern.test(value)) {
        field.classList.add('is-invalid');
        emailFeedback.textContent = 'Por favor, ingrese un email válido';
        return false;
    }
    
    // Si el formato es válido, verificar duplicados
     if (value !== '') {
         // Verificar si estamos en modo edición
         const usuId = $('#usu_id').val();
         const isEditing = usuId !== '';
         
         // Preparar datos para enviar
         const postData = { usu_correo: value };
         if (isEditing) {
             postData.usu_id = usuId;
         }
         
         $.ajax({
             url: "../../controller/usuario.php?op=validar_email",
             type: "POST",
             data: postData,
             dataType: "json",
            success: function(response) {
                if (response.existe) {
                    // Email ya existe
                    field.classList.remove('is-valid');
                    field.classList.add('is-invalid');
                    duplicateMessage.style.display = 'block';
                    emailFeedback.textContent = 'Email ya existente';
                } else {
                    // Email disponible
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                    duplicateMessage.style.display = 'none';
                }
            },
            error: function() {
                // En caso de error, solo validar formato
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                duplicateMessage.style.display = 'none';
            }
        });
    }
    
    return emailPattern.test(value); // Retorna true para el formato, la validación de duplicados es asíncrona
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
// Función para guardar o editar un usuario
function guardaryeditar(e){
    e.preventDefault();
    
    // Validar todos los campos antes de enviar
    const form = document.getElementById('mantenimiento_form');
    const isFormValid = validateForm();
    
    // Verificar si hay email duplicado (solo si el mensaje de duplicado está visible)
    const duplicateMessage = document.getElementById('email-duplicate-message');
    const emailField = document.getElementById('usu_correo');
    
    if (duplicateMessage && duplicateMessage.style.display === 'block') {
        Swal.fire({
            title: 'Email Duplicado',
            text: 'El email ingresado ya existe en el sistema. Por favor, use otro email.',
            icon: 'error'
        });
        return;
    }
    
    // Verificar si hay DNI duplicado (solo si el mensaje de duplicado está visible)
    const dniDuplicateMessage = document.getElementById('dni-duplicate-message');
    const dniField = document.getElementById('usu_dni');
    
    if (dniDuplicateMessage && dniDuplicateMessage.style.display === 'block') {
        Swal.fire({
            title: 'DNI Duplicado',
            text: 'El DNI ingresado ya existe en el sistema. Por favor, use otro DNI.',
            icon: 'error'
        });
        return;
    }
    
    if (!isFormValid) {
        // Verificar si hay campos vacíos
        const emptyFields = hasEmptyRequiredFields();
        
        if (emptyFields.length > 0) {
            // Mostrar mensaje específico para campos vacíos con SweetAlert
            const fieldsText = emptyFields.length === 1 
                ? `el campo: ${emptyFields[0]}` 
                : `los campos: ${emptyFields.join(', ')}`;
                
            Swal.fire({
                title: 'Campos Obligatorios',
                text: `Por favor, complete ${fieldsText}.`,
                icon: 'warning'
            });
        } else {
            // Mostrar mensaje genérico para otros errores de validación
            Swal.fire({
                title: 'Error de Validación',
                text: 'Por favor, corrija los errores en el formulario antes de continuar.',
                icon: 'error'
            });
        }
        
        // NO agregar clase was-validated para evitar mensajes debajo de inputs
        return;
    }
    
    var formData = new FormData($("#mantenimiento_form")[0]);
    
  
    
    // Detectar si estamos en modo edición basado en el atributo del campo contraseña
    const isEditing = $('#usu_pass').attr('data-editing') === 'true';
    const currentPassword = $('#usu_pass').val();
    const originalPassword = $('#usu_pass').attr('data-original-password');
    
    if (isEditing) {
        // MODO EDICIÓN: Usar la contraseña actual del campo
        // Si no se modificó, será la original; si se modificó, será la nueva
        formData.set('usu_pass', currentPassword);
        
        if (currentPassword === originalPassword) {
        } else {
        }
    } else {
        // MODO NUEVO REGISTRO: Usar la contraseña ingresada
    }
    
    $.ajax({
        url:"../../controller/usuario.php?op=guardaryeditar",
        type:"POST",
        data:formData,
        contentType:false,
        processData:false,
        success:function(data){
            var resp = null;
            try { 
                resp = JSON.parse(data); 
            } catch (e) { 
                // Si no es JSON válido, asumir éxito (compatibilidad con código anterior)
                resp = { status: 'success', message: 'Registro Confirmado' };
            }
            
            if (resp && resp.status === 'error') {
                // Manejar errores del servidor (incluyendo email duplicado)
                Swal.fire({
                    title: 'Error de Validación',
                    text: resp.message || 'Error al procesar la solicitud',
                    icon: 'error'
                });
                
                // Si es error de email duplicado, resaltar el campo
                if (resp.message && resp.message.toLowerCase().includes('email')) {
                    const emailField = document.getElementById('usu_correo');
                    const duplicateMessage = document.getElementById('email-duplicate-message');
                    
                    emailField.classList.remove('is-valid');
                    emailField.classList.add('is-invalid');
                    duplicateMessage.style.display = 'block';
                }
                
                // Si es error de DNI duplicado, resaltar el campo
                if (resp.message && resp.message.toLowerCase().includes('dni')) {
                    const dniField = document.getElementById('usu_dni');
                    const dniDuplicateMessage = document.getElementById('dni-duplicate-message');
                    
                    dniField.classList.remove('is-valid');
                    dniField.classList.add('is-invalid');
                    dniDuplicateMessage.style.display = 'block';
                }
                return;
            }
            
            // Éxito: cerrar modal y actualizar tabla
            $('#table_data').DataTable().ajax.reload();
            $('#modalmantenimiento').modal('hide');
            
            // Limpiar clases de validación
            form.classList.remove('was-validated');
            clearValidationClasses();

            Swal.fire({
                title: 'Usuario',
                text: resp.message || 'Registro Confirmado',
                icon: 'success'
            });
        },
        error: function(xhr, status, error) {
            Swal.fire({
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

// Función para verificar si hay campos vacíos
function hasEmptyRequiredFields() {
    const requiredFields = [
        { id: 'usu_nom', name: 'Nombre' },
        { id: 'usu_ape', name: 'Apellido' },
        { id: 'usu_dni', name: 'DNI' },
        { id: 'usu_correo', name: 'Email' },
        { id: 'usu_pass', name: 'Contraseña' },
        { id: 'rol_id', name: 'Rol' }
    ];
    
    const emptyFields = [];
    
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            let value = element.value.trim();
            
            // Caso especial para contraseña en modo edición
            if (field.id === 'usu_pass') {
                const isEditing = element.getAttribute('data-editing') === 'true';
                // Si está en modo edición y tiene asteriscos, no es campo vacío
                if (isEditing && /^\*+$/.test(value)) {
                    return; // No agregar a campos vacíos
                }
            }
            
            // Verificar si está vacío
            if (value === '' || value === null || (field.id === 'rol_id' && value === '0')) {
                emptyFields.push(field.name);
            }
        }
    });
    
    return emptyFields;
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
// Función para combobox de roles
function combo_rol(){
    $.post("../../controller/rol.php?op=combo",function(data){
        $('#rol_id').html(data);
    });
}
// Función para listar todos los usuarios
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
// Función para editar un usuario
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
        // Solo permitir editar: Nombre, Apellido, DNI, Correo, Rol, Contraseña
        
        // Cargar la contraseña real pero mostrarla como tipo password (con puntos)
        $('#usu_pass').val(data.USU_PASS || '');
        
        // Marcar que estamos en modo edición y guardar la contraseña original
        $('#usu_pass').attr('data-editing', 'true');
        $('#usu_pass').attr('data-original-password', data.USU_PASS || '');
        $('#usu_pass').prop('readonly', false);
        $('#usu_pass').removeClass('bg-light');
        $('#usu_pass').attr('title', 'Puede modificar la contraseña o mantener la actual');
        $('#usu_pass').attr('placeholder', 'Modifique la contraseña si desea cambiarla');
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
// Función para eliminar un usuario
function eliminar(usu_id){
    Swal.fire({
        title:"Eliminar!",
        text:"Desea Eliminar el Registro?",
        icon: "error",
        confirmButtonText : "Si",
        showCancelButton : true,
        cancelButtonText: "No",
    }).then((result)=>{
        if (result.value){
            $.post("../../controller/usuario.php?op=eliminar",{usu_id:usu_id},function(data){
            });

            $('#table_data').DataTable().ajax.reload();

            Swal.fire({
                title:'Usuario',
                text: 'Registro Eliminado',
                icon: 'success'
            });
        }
    });
}

// Función para cambiar el estado del usuario via checkbox
function cambiarEstado(usu_id, estado) {
    var accion = estado ? 'activar' : 'desactivar';
    var titulo = estado ? 'Activar Usuario' : 'Desactivar Usuario';
    var texto = '¿Está seguro que desea ' + accion + ' este usuario?';
    
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../../controller/usuario.php?op=cambiar_estado", {
                usu_id: usu_id,
                estado: estado
            }, function(data) {
                var response = JSON.parse(data);
                if(response.status === 'success') {
                    $('#table_data').DataTable().ajax.reload();
                    Swal.fire({
                        title: 'Usuario',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }).fail(function() {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado',
                    icon: 'error'
                });
                // Revertir el checkbox en caso de error
                $('#switch' + usu_id).prop('checked', !estado);
            });
        } else {
            // Si el usuario cancela, revertir el checkbox
            $('#switch' + usu_id).prop('checked', !estado);
        }
    });
}

  // Función para limpiar el formulario y resetear el modal
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
    $('#usu_pass').removeAttr('data-original-password');
    $('#usu_pass').removeAttr('title');
    $('#usu_pass').removeAttr('placeholder');
    $('#usu_pass').prop('readonly', false);
    $('#usu_pass').removeClass('bg-light');
    const form = document.getElementById('mantenimiento_form');
    form.classList.remove('was-validated');
    clearValidationClasses();
    
    // Resetear indicador de fortaleza de contraseña
    resetPasswordStrengthIndicator();
    
    combo_rol();
    $('#modalmantenimiento').modal('show');
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