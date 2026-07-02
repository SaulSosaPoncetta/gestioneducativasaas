USE `laravel_db` ;

SET FOREIGN_KEY_CHECKS=0;

-- Tabla: tipos_archivo
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_archivo` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `archivo` BLOB NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: archivo_examen
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

-- Tabla: archivo_materia
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

-- Tabla: categorias_auditoria
CREATE TABLE IF NOT EXISTS `laravel_db`.`categorias_auditoria` (
  `id_categoria_auditoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_categoria` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_categoria_auditoria`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: auditorias
CREATE TABLE IF NOT EXISTS `laravel_db`.`auditorias` (
  `id_auditoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_categoria_auditoria` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `nombre_tabla` VARCHAR(255) NOT NULL,
  `id_registro` INT NOT NULL,
  `accion` VARCHAR(255) NOT NULL,
  `descripcion` VARCHAR(255) NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  INDEX `auditorias_id_categoria_auditoria_foreign` (`id_categoria_auditoria` ASC) VISIBLE,
  INDEX `auditorias_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `auditorias_id_categoria_auditoria_foreign` FOREIGN KEY (`id_categoria_auditoria`) REFERENCES `laravel_db`.`categorias_auditoria` (`id_categoria_auditoria`),
  CONSTRAINT `auditorias_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB AUTO_INCREMENT = 201 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;