USE `laravel_db` ;

CREATE TABLE IF NOT EXISTS `laravel_db`.`paises` (
  `id_pais` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_pais` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_pais`))
ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`localidades` (
  `id_localidad` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pais` INT UNSIGNED NOT NULL,
  `codigo_postal` INT NOT NULL,
  `nombre_localidad` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_localidad`),
  INDEX `localidades_id_pais_foreign` (`id_pais` ASC) VISIBLE,
  CONSTRAINT `localidades_id_pais_foreign` FOREIGN KEY (`id_pais`) REFERENCES `laravel_db`.`paises` (`id_pais`))
ENGINE = InnoDB AUTO_INCREMENT = 21 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`roles` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`))
ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`usuarios` (
  `id_usuario` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `apellido` VARCHAR(255) NOT NULL,
  `nombre_usuario` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `id_rol` INT UNSIGNED NOT NULL,
  `genero` VARCHAR(255) NULL DEFAULT NULL,
  `id_localidad` INT UNSIGNED NULL DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `laravel_db`.`dnis` (
  `dni` VARCHAR(255) NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `tipo_dni` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`dni`, `id_usuario`),
  INDEX `dnis_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `dnis_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`administradores` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administradores_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`docentes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `nro_legajo` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `docentes_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`estudiantes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `estudiantes_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

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