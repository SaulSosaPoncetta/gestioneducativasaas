CREATE SCHEMA IF NOT EXISTS `laravel_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `laravel_db` ;

SET FOREIGN_KEY_CHECKS=0;

-- Tabla: roles
CREATE TABLE IF NOT EXISTS `laravel_db`.`roles` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`))
ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: usuarios
CREATE TABLE IF NOT EXISTS `laravel_db`.`usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `apellido` VARCHAR(255) NOT NULL,
  `nombre_usuario` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `id_rol` INT UNSIGNED NOT NULL,
  `genero` VARCHAR(255) NULL DEFAULT NULL,
  `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `id_localidad` INT UNSIGNED NULL DEFAULT NULL,
  `fecha_nacimiento` DATE NULL DEFAULT NULL,
  `domicilio` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `usuarios_email_unique` (`email` ASC) VISIBLE,
  INDEX `usuarios_id_rol_foreign` (`id_rol` ASC) VISIBLE,
  INDEX `usuarios_id_localidad_foreign` (`id_localidad` ASC) VISIBLE,
  CONSTRAINT `usuarios_id_localidad_foreign` FOREIGN KEY (`id_localidad`) REFERENCES `laravel_db`.`localidades` (`id_localidad`),
  CONSTRAINT `usuarios_id_rol_foreign` FOREIGN KEY (`id_rol`) REFERENCES `laravel_db`.`roles` (`id_rol`))
ENGINE = InnoDB AUTO_INCREMENT = 31 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: tipos_dni
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_dni` (
  `id_tipo_dni` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_dni`))
ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: dnis
CREATE TABLE IF NOT EXISTS `laravel_db`.`dnis` (
  `dni` VARCHAR(255) NOT NULL,
  `cuil` VARCHAR(13) NULL DEFAULT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_tipo_dni` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`dni`, `id_usuario`),
  INDEX `dnis_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  INDEX `dnis_id_tipo_dni_foreign` (`id_tipo_dni` ASC) VISIBLE,
  CONSTRAINT `dnis_id_tipo_dni_foreign` FOREIGN KEY (`id_tipo_dni`) REFERENCES `laravel_db`.`tipos_dni` (`id_tipo_dni`),
  CONSTRAINT `dnis_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: migrations
CREATE TABLE IF NOT EXISTS `laravel_db`.`migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB AUTO_INCREMENT = 39 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;