USE `laravel_db` ;

CREATE TABLE IF NOT EXISTS `laravel_db`.`ciclos_lectivos` (
  `id_ciclo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `id_usuario_administracion` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_ciclo`),
  INDEX `ciclos_lectivos_id_usuario_administracion_foreign` (`id_usuario_administracion` ASC) VISIBLE,
  CONSTRAINT `ciclos_lectivos_id_usuario_administracion_foreign` FOREIGN KEY (`id_usuario_administracion`) REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`carreras` (
  `id_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_carrera`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`planes_estudio` (
  `id_plan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_plan`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`resoluciones_carrera` (
  `id_resolucion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_resolucion`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`estados_carrera` (
  `id_estado_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_carrera`))
ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`estudiante_carrera` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_carrera` INT UNSIGNED NOT NULL,
  `cohorte` DATE NOT NULL,
  `id_estado_carrera` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`, `id_carrera`, `cohorte`),
  INDEX `estudiante_carrera_id_carrera_foreign` (`id_carrera` ASC) VISIBLE,
  INDEX `estudiante_carrera_id_estado_carrera_foreign` (`id_estado_carrera` ASC) VISIBLE,
  CONSTRAINT `estudiante_carrera_id_carrera_foreign` FOREIGN KEY (`id_carrera`) REFERENCES `laravel_db`.`carreras` (`id_carrera`),
  CONSTRAINT `estudiante_carrera_id_estado_carrera_foreign` FOREIGN KEY (`id_estado_carrera`) REFERENCES `laravel_db`.`estados_carrera` (`id_estado_carrera`),
  CONSTRAINT `estudiante_carrera_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_materia` (
  `id_tipo_materia` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo_materia` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_materia`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`materias` (
  `id_materia` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_tipo_materia` INT UNSIGNED NOT NULL,
  `nota_promocionable` DECIMAL(5,2) NULL DEFAULT NULL,
  `nota_aprobada` DECIMAL(5,2) NULL DEFAULT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `anio` SMALLINT NULL DEFAULT NULL,
  `id_usuario_administrador` INT UNSIGNED NULL DEFAULT NULL,
  `id_carrera` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_materia`),
  INDEX `materias_id_tipo_materia_foreign` (`id_tipo_materia` ASC) VISIBLE,
  INDEX `materias_id_usuario_administrador_foreign` (`id_usuario_administrador` ASC) VISIBLE,
  INDEX `materias_id_carrera_foreign` (`id_carrera` ASC) VISIBLE,
  CONSTRAINT `materias_id_carrera_foreign` FOREIGN KEY (`id_carrera`) REFERENCES `laravel_db`.`carreras` (`id_carrera`),
  CONSTRAINT `materias_id_tipo_materia_foreign` FOREIGN KEY (`id_tipo_materia`) REFERENCES `laravel_db`.`tipos_materia` (`id_tipo_materia`),
  CONSTRAINT `materias_id_usuario_administrador_foreign` FOREIGN KEY (`id_usuario_administrador`) REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 16 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`correlativas` (
  `id_materia` INT UNSIGNED NOT NULL,
  `id_materia_correlativa` INT UNSIGNED NOT NULL,
  `correlativa_cursada` TINYINT(1) NOT NULL DEFAULT '0',
  `correlativa_final` TINYINT(1) NOT NULL DEFAULT '0',
  `descripcion` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_materia`, `id_materia_correlativa`),
  INDEX `correlativas_id_materia_correlativa_foreign` (`id_materia_correlativa` ASC) VISIBLE,
  CONSTRAINT `correlativas_id_materia_correlativa_foreign` FOREIGN KEY (`id_materia_correlativa`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `correlativas_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`materia_docente` (
  `id_materia` INT UNSIGNED NOT NULL,
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NULL DEFAULT NULL,
  `situacion_revista` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_materia`, `id_usuario_docente`),
  INDEX `materia_docente_id_usuario_docente_foreign` (`id_usuario_docente` ASC) VISIBLE,
  INDEX `materia_docente_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `materia_docente_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `materia_docente_id_usuario_docente_foreign` FOREIGN KEY (`id_usuario_docente`) REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `materia_docente_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;