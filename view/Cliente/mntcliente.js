function init(){
 
  
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
            url:"../../controller/cliente.php?op=listar",
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
                console.log(data);
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