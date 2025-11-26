# Configuración del Certificado Digital

## Pasos para configurar el certificado:

1. **Obtener certificado digital de SUNAT**
   - Descarga tu certificado .pfx desde SUNAT
   - Convierte el .pfx a .pem usando OpenSSL

2. **Convertir certificado (si tienes .pfx)**

```bash
# Extraer certificado
openssl pkcs12 -in certificado.pfx -out certificado.pem -nodes

# O si prefieres con clave
openssl pkcs12 -in certificado.pfx -out certificado.pem
```

3. **Colocar el archivo**
   - Guarda el archivo `certificado.pem` en esta carpeta `config/`
   - Asegúrate de que el archivo tenga permisos de lectura

4. **Configurar credenciales SOL**
   - Edita el archivo `models/Factura.php`
   - Busca el método `configurarSee()`
   - Actualiza:
     - RUC de tu empresa
     - Usuario SOL
     - Clave SOL

## Para pruebas (Beta SUNAT)

Puedes usar las credenciales de prueba de SUNAT:
- RUC: 20000000001
- Usuario: MODDATOS
- Clave: moddatos

## Importante

⚠️ NO subas el certificado .pem a tu repositorio Git
⚠️ Agrega `config/certificado.pem` a tu .gitignore
