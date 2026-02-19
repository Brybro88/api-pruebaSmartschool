# SmartSchool API Backend 🏫

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## Sobre el Proyecto

**SmartSchool** es un sistema integral multiplataforma diseñado para la gestión automatizada de asistencia escolar, monitoreo de alumnos y comunicación institucional. Este repositorio contiene el **Backend (API REST)** desarrollado en Laravel, el cual sirve como motor principal para la aplicación móvil (React Native).

Este módulo fue diseñado e implementado como parte del proyecto de Residencia Profesional para la empresa **GMStore**.

## Características Principales (Features) 🚀

El sistema expone una API segura que maneja las siguientes funcionalidades:

* **Autenticación y Seguridad (Sanctum):** Login y control de sesiones mediante tokens.
* **Segregación de Datos (Multi-rol):** * *Modo Admin/Escuela:* Acceso global a los datos de la institución.
    * *Modo Padre de Familia:* Privacidad estricta; visualización exclusiva de los alumnos vinculados a su cuenta.
* **Control de Asistencia:** Reloj checador automatizado con validación de registros duplicados por día.
* **Gestión de Alumnos (CRUD):** Registro de perfiles incluyendo carga y almacenamiento de fotografías (Digital ID).
* **Dashboard Ejecutivo:** Endpoints analíticos para la generación de estadísticas en tiempo real (Total de alumnos, tasas de asistencia y ausencias).
* **Autorizaciones de Salida (Exit Passes):** [En Desarrollo] Módulo para solicitar y aprobar permisos de salida institucionales.

## Stack Tecnológico 🛠️

* **Framework:** Laravel 11 (PHP)
* **Base de Datos:** MySQL
* **Autenticación:** Laravel Sanctum
* **Almacenamiento:** Local Storage System (para manejo de imágenes `multipart/form-data`)

## Requisitos Previos

Para ejecutar este proyecto de forma local, necesitas tener instalado:

* PHP >= 8.2
* Composer
* MySQL o MariaDB
* Node.js & NPM (opcional para compilar assets)

## Instalación y Configuración Local

Sigue estos pasos para levantar el entorno de desarrollo:

1. Clonar el repositorio:
   ```bash
   git clone [https://github.com/Brybro88/smartschool-api.git](https://github.com/Brybro88/smartschool-api.git)
