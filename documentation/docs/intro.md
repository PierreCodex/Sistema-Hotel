---
sidebar_position: 1
---

# Introducción

Bienvenido a la documentación oficial del **Sistema Hotel**, un sistema de gestión hotelera completo desarrollado en PHP.

## ¿Qué es Sistema Hotel?

Sistema Hotel es una aplicación web diseñada para facilitar la administración integral de un hotel, incluyendo:

- 🏨 **Gestión de Habitaciones**: Control de pisos, categorías, estados y tarifas
- 👥 **Recepción**: Check-in/Check-out de huéspedes
- 💳 **Facturación**: Generación de boletas y facturas
- 📦 **Productos**: Inventario y ventas adicionales
- 📊 **Reportes**: Estadísticas y análisis del negocio
- 👤 **Usuarios**: Sistema de roles y permisos

## Arquitectura

El sistema está construido utilizando una arquitectura **MVC (Modelo-Vista-Controlador)**:

```
SistemaHotel-PHP/
├── controller/     # Controladores (lógica de negocio)
├── models/         # Modelos (acceso a datos)
├── view/           # Vistas (interfaces de usuario)
├── config/         # Configuración de la aplicación
├── assets/         # Recursos estáticos (CSS, JS, imágenes)
└── tests/          # Pruebas unitarias (PHPUnit + Jest)
```

## Tecnologías Utilizadas

| Categoría | Tecnología |
|-----------|------------|
| Backend | PHP 8.x |
| Frontend | HTML5, CSS3, JavaScript |
| Base de Datos | MySQL |
| Testing PHP | PHPUnit |
| Testing JS | Jest |
| Servidor | Apache (XAMPP) |

## Comenzar

Para empezar a usar Sistema Hotel, consulta la [Guía de Instalación](/docs/getting-started/installation).
