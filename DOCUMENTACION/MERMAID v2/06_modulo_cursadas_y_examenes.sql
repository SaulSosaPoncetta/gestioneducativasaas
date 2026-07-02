USE `laravel_db` ;

CREATE TABLE IF NOT EXISTS `laravel_db`.`estados_estudiante` (
  `id_estado_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_estudiante`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_estudiante` (
  `id_tipo_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_estudiante`))
ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`inscripciones_materia` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `id_ciclo` INT UNSIGNED NOT NULL,
  `fecha_inscripcion` DATE NULL DEFAULT NULL,
  `id_tipo_estudiante` INT UNSIGNED NOT NULL,
  `id_estado_estudiante` INT UNSIGNED NOT NULL,
  `contador_finales` INT NOT NULL DEFAULT '0',
  `fecha_vencimiento` DATE NULL DEFAULT NULL,
  `libro` VARCHAR(255) NULL DEFAULT NULL,
  `folio` VARCHAR(255) NULL DEFAULT NULL,
  `division` INT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`, `id_materia`, `id_ciclo`),
  INDEX `inscripciones_materia_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  INDEX `inscripciones_materia_id_ciclo_foreign` (`id_ciclo` ASC) VISIBLE,
  INDEX `inscripciones_materia_id_tipo_estudiante_foreign` (`id_tipo_estudiante` ASC) VISIBLE,
  INDEX `inscripciones_materia_id_estado_estudiante_foreign` (`id_estado_estudiante` ASC) VISIBLE,
  CONSTRAINT `inscripciones_materia_id_ciclo_foreign` FOREIGN KEY (`id_ciclo`) REFERENCES `laravel_db`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `inscripciones_materia_id_estado_estudiante_foreign` FOREIGN KEY (`id_estado_estudiante`) REFERENCES `laravel_db`.`estados_estudiante` (`id_estado_estudiante`),
  CONSTRAINT `inscripciones_materia_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `inscripciones_materia_id_tipo_estudiante_foreign` FOREIGN KEY (`id_tipo_estudiante`) REFERENCES `laravel_db`.`tipos_estudiante` (`id_tipo_estudiante`),
  CONSTRAINT `inscripciones_materia_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_mesa_examen` (
  `id_tipo_mesa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_mesa`))
ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`mesas_examen` (
  `id_mesa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME NOT NULL,
  `id_tipo_mesa` INT UNSIGNED NOT NULL,
  `habilitado` TINYINT(1) NOT NULL DEFAULT '1',
  `id_materia` INT UNSIGNED NOT NULL,
  `aula` VARCHAR(255) NULL DEFAULT NULL,
  `id_usuario_administracion` INT UNSIGNED NULL DEFAULT NULL,
  `id_ciclo` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_mesa`),
  INDEX `mesas_examen_id_tipo_mesa_foreign` (`id_tipo_mesa` ASC) VISIBLE,
  INDEX `mesas_examen_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  INDEX `mesas_examen_id_usuario_administracion_foreign` (`id_usuario_administracion` ASC) VISIBLE,
  INDEX `mesas_examen_id_ciclo_foreign` (`id_ciclo` ASC) VISIBLE,
  CONSTRAINT `mesas_examen_id_ciclo_foreign` FOREIGN KEY (`id_ciclo`) REFERENCES `laravel_db`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `mesas_examen_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `mesas_examen_id_tipo_mesa_foreign` FOREIGN KEY (`id_tipo_mesa`) REFERENCES `laravel_db`.`tipos_mesa_examen` (`id_tipo_mesa`),
  CONSTRAINT `mesas_examen_id_usuario_administracion_foreign` FOREIGN KEY (`id_usuario_administracion`) REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 21 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`mesa_examen_docente` (
  `id_mesa` INT UNSIGNED NOT NULL,
  `id_docente` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_mesa`, `id_docente`),
  INDEX `mesa_examen_docente_id_docente_foreign` (`id_docente` ASC) VISIBLE,
  CONSTRAINT `mesa_examen_docente_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `mesa_examen_docente_id_mesa_foreign` FOREIGN KEY (`id_mesa`) REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Almacenamiento de archivos y notas vinculadas
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_archivo` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `archivo` BLOB NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`archivo_examen` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_mesa` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`, `id_usuario`),
  INDEX `archivo_examen_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  INDEX `archivo_examen_id_mesa_foreign` (`id_mesa` ASC) VISIBLE,
  CONSTRAINT `archivo_examen_id_mesa_foreign` FOREIGN KEY (`id_mesa`) REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `archivo_examen_id_tipo_archivo_foreign` FOREIGN KEY (`id_tipo_archivo`) REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `archivo_examen_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`archivo_materia` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`, `id_materia`),
  INDEX `archivo_materia_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  CONSTRAINT `archivo_materia_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `archivo_materia_id_tipo_archivo_foreign` FOREIGN KEY (`id_tipo_archivo`) REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`notas_cuatrimestre` (
  `id_nota_cuatrimestre` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `valor` DECIMAL(5,2) NOT NULL,
  `fecha_carga` DATETIME NOT NULL,
  `id_tipo_archivo` INT UNSIGNED NULL DEFAULT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_nota_cuatrimestre`),
  INDEX `notas_cuatrimestre_id_tipo_archivo_foreign` (`id_tipo_archivo` ASC) VISIBLE,
  INDEX `notas_cuatrimestre_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  INDEX `notas_cuatrimestre_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  CONSTRAINT `notas_cuatrimestre_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `notas_cuatrimestre_id_tipo_archivo_foreign` FOREIGN KEY (`id_tipo_archivo`) REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_cuatrimestre_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 101 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `laravel_db`.`notas_final` (
  `id_nota` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_mesa` INT UNSIGNED NOT NULL,
  `valor` DECIMAL(5,2) NULL DEFAULT NULL,
  `id_tipo_archivo` INT UNSIGNED NULL DEFAULT NULL,
  `fecha_carga` DATETIME NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_nota`),
  INDEX `notas_final_id_mesa_foreign` (`id_mesa` ASC) VISIBLE,
  INDEX `notas_final_id_tipo_archivo_foreign` (`id_tipo_archivo` ASC) VISIBLE,
  INDEX `notas_final_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  INDEX `notas_final_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  CONSTRAINT `notas_final_id_materia_foreign` FOREIGN KEY (`id_materia`) REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `notas_final_id_mesa_foreign` FOREIGN KEY (`id_mesa`) REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `notas_final_id_tipo_archivo_foreign` FOREIGN KEY (`id_tipo_archivo`) REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_final_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 101 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;