USE `laravel_db` ;

SET FOREIGN_KEY_CHECKS=0;

-- Tabla: administradores
CREATE TABLE IF NOT EXISTS `laravel_db`.`administradores` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administradores_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: docentes
CREATE TABLE IF NOT EXISTS `laravel_db`.`docentes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `nro_legajo` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `docentes_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: estudiantes
CREATE TABLE IF NOT EXISTS `laravel_db`.`estudiantes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `estudiantes_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: administracion_docente
CREATE TABLE IF NOT EXISTS `laravel_db`.`administracion_docente` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_docente`, `fecha_hora`),
  INDEX `administracion_docente_id_usuario_docente_foreign` (`id_usuario_docente` ASC) VISIBLE,
  CONSTRAINT `administracion_docente_id_usuario_administracion_foreign` FOREIGN KEY (`id_usuario_administracion`) REFERENCES `laravel_db`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_docente_id_usuario_docente_foreign` FOREIGN KEY (`id_usuario_docente`) REFERENCES `laravel_db`.`docentes` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: administracion_estudiante
CREATE TABLE IF NOT EXISTS `laravel_db`.`administracion_estudiante` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `administracion_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `administracion_estudiante_id_usuario_administracion_foreign` FOREIGN KEY (`id_usuario_administracion`) REFERENCES `laravel_db`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_estudiante_id_usuario_estudiante_foreign` FOREIGN KEY (`id_usuario_estudiante`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: docente_estudiante
CREATE TABLE IF NOT EXISTS `laravel_db`.`docente_estudiante` (
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_docente`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `docente_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `docente_estudiante_id_usuario_docente_foreign` FOREIGN KEY (`id_usuario_docente`) REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `docente_estudiante_id_usuario_estudiante_foreign` FOREIGN KEY (`id_usuario_estudiante`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;