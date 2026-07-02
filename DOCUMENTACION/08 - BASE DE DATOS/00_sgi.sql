-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema laravel_db
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema laravel_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `laravel_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `laravel_db` ;

-- -----------------------------------------------------
-- Table `laravel_db`.`paises`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`paises` (
  `id_pais` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_pais` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_pais`))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`localidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`localidades` (
  `id_localidad` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pais` INT UNSIGNED NOT NULL,
  `codigo_postal` INT NOT NULL,
  `nombre_localidad` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_localidad`),
  INDEX `localidades_id_pais_foreign` (`id_pais` ASC) VISIBLE,
  CONSTRAINT `localidades_id_pais_foreign`
    FOREIGN KEY (`id_pais`)
    REFERENCES `laravel_db`.`paises` (`id_pais`))
ENGINE = InnoDB
AUTO_INCREMENT = 21
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`roles` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`usuarios`
-- -----------------------------------------------------
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
  CONSTRAINT `usuarios_id_localidad_foreign`
    FOREIGN KEY (`id_localidad`)
    REFERENCES `laravel_db`.`localidades` (`id_localidad`),
  CONSTRAINT `usuarios_id_rol_foreign`
    FOREIGN KEY (`id_rol`)
    REFERENCES `laravel_db`.`roles` (`id_rol`))
ENGINE = InnoDB
AUTO_INCREMENT = 31
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`administradores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`administradores` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administradores_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`docentes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`docentes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `nro_legajo` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `docentes_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`administracion_docente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`administracion_docente` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_docente`, `fecha_hora`),
  INDEX `administracion_docente_id_usuario_docente_foreign` (`id_usuario_docente` ASC) VISIBLE,
  CONSTRAINT `administracion_docente_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `laravel_db`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_docente_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `laravel_db`.`docentes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`estudiantes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`estudiantes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `estudiantes_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`administracion_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`administracion_estudiante` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `administracion_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `administracion_estudiante_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `laravel_db`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_estudiante_id_usuario_estudiante_foreign`
    FOREIGN KEY (`id_usuario_estudiante`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`ciclos_lectivos`
-- -----------------------------------------------------
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
  CONSTRAINT `ciclos_lectivos_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`carreras`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`carreras` (
  `id_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_carrera`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`tipos_materia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_materia` (
  `id_tipo_materia` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo_materia` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_materia`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`materias`
-- -----------------------------------------------------
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
  CONSTRAINT `materias_id_carrera_foreign`
    FOREIGN KEY (`id_carrera`)
    REFERENCES `laravel_db`.`carreras` (`id_carrera`),
  CONSTRAINT `materias_id_tipo_materia_foreign`
    FOREIGN KEY (`id_tipo_materia`)
    REFERENCES `laravel_db`.`tipos_materia` (`id_tipo_materia`),
  CONSTRAINT `materias_id_usuario_administrador_foreign`
    FOREIGN KEY (`id_usuario_administrador`)
    REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 16
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`tipos_mesa_examen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_mesa_examen` (
  `id_tipo_mesa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_mesa`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`mesas_examen`
-- -----------------------------------------------------
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
  CONSTRAINT `mesas_examen_id_ciclo_foreign`
    FOREIGN KEY (`id_ciclo`)
    REFERENCES `laravel_db`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `mesas_examen_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `mesas_examen_id_tipo_mesa_foreign`
    FOREIGN KEY (`id_tipo_mesa`)
    REFERENCES `laravel_db`.`tipos_mesa_examen` (`id_tipo_mesa`),
  CONSTRAINT `mesas_examen_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `laravel_db`.`administradores` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 21
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`tipos_archivo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_archivo` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `archivo` BLOB NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`archivo_examen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`archivo_examen` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `id_mesa` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`, `id_usuario`),
  INDEX `archivo_examen_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  INDEX `archivo_examen_id_mesa_foreign` (`id_mesa` ASC) VISIBLE,
  CONSTRAINT `archivo_examen_id_mesa_foreign`
    FOREIGN KEY (`id_mesa`)
    REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `archivo_examen_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `archivo_examen_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`archivo_materia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`archivo_materia` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`, `id_materia`),
  INDEX `archivo_materia_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  CONSTRAINT `archivo_materia_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `archivo_materia_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`categorias_auditoria`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`categorias_auditoria` (
  `id_categoria_auditoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_categoria` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_categoria_auditoria`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`auditorias`
-- -----------------------------------------------------
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
  CONSTRAINT `auditorias_id_categoria_auditoria_foreign`
    FOREIGN KEY (`id_categoria_auditoria`)
    REFERENCES `laravel_db`.`categorias_auditoria` (`id_categoria_auditoria`),
  CONSTRAINT `auditorias_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 201
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`correlativas`
-- -----------------------------------------------------
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
  CONSTRAINT `correlativas_id_materia_correlativa_foreign`
    FOREIGN KEY (`id_materia_correlativa`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `correlativas_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`tipos_dni`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_dni` (
  `id_tipo_dni` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_dni`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`dnis`
-- -----------------------------------------------------
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
  CONSTRAINT `dnis_id_tipo_dni_foreign`
    FOREIGN KEY (`id_tipo_dni`)
    REFERENCES `laravel_db`.`tipos_dni` (`id_tipo_dni`),
  CONSTRAINT `dnis_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`docente_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`docente_estudiante` (
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_docente`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `docente_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `docente_estudiante_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `docente_estudiante_id_usuario_estudiante_foreign`
    FOREIGN KEY (`id_usuario_estudiante`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`estados_carrera`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`estados_carrera` (
  `id_estado_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_carrera`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`estados_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`estados_estudiante` (
  `id_estado_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_estudiante`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`estudiante_carrera`
-- -----------------------------------------------------
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
  CONSTRAINT `estudiante_carrera_id_carrera_foreign`
    FOREIGN KEY (`id_carrera`)
    REFERENCES `laravel_db`.`carreras` (`id_carrera`),
  CONSTRAINT `estudiante_carrera_id_estado_carrera_foreign`
    FOREIGN KEY (`id_estado_carrera`)
    REFERENCES `laravel_db`.`estados_carrera` (`id_estado_carrera`),
  CONSTRAINT `estudiante_carrera_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`tipos_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`tipos_estudiante` (
  `id_tipo_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_estudiante`))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`inscripciones_materia`
-- -----------------------------------------------------
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
  CONSTRAINT `inscripciones_materia_id_ciclo_foreign`
    FOREIGN KEY (`id_ciclo`)
    REFERENCES `laravel_db`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `inscripciones_materia_id_estado_estudiante_foreign`
    FOREIGN KEY (`id_estado_estudiante`)
    REFERENCES `laravel_db`.`estados_estudiante` (`id_estado_estudiante`),
  CONSTRAINT `inscripciones_materia_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `inscripciones_materia_id_tipo_estudiante_foreign`
    FOREIGN KEY (`id_tipo_estudiante`)
    REFERENCES `laravel_db`.`tipos_estudiante` (`id_tipo_estudiante`),
  CONSTRAINT `inscripciones_materia_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`materia_docente`
-- -----------------------------------------------------
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
  CONSTRAINT `materia_docente_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `materia_docente_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `materia_docente_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`mesa_examen_docente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`mesa_examen_docente` (
  `id_mesa` INT UNSIGNED NOT NULL,
  `id_docente` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_mesa`, `id_docente`),
  INDEX `mesa_examen_docente_id_docente_foreign` (`id_docente` ASC) VISIBLE,
  CONSTRAINT `mesa_examen_docente_id_docente_foreign`
    FOREIGN KEY (`id_docente`)
    REFERENCES `laravel_db`.`docentes` (`id_usuario`),
  CONSTRAINT `mesa_examen_docente_id_mesa_foreign`
    FOREIGN KEY (`id_mesa`)
    REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`migrations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 39
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`notas_cuatrimestre`
-- -----------------------------------------------------
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
  CONSTRAINT `notas_cuatrimestre_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `notas_cuatrimestre_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_cuatrimestre_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 101
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`notas_final`
-- -----------------------------------------------------
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
  CONSTRAINT `notas_final_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `laravel_db`.`materias` (`id_materia`),
  CONSTRAINT `notas_final_id_mesa_foreign`
    FOREIGN KEY (`id_mesa`)
    REFERENCES `laravel_db`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `notas_final_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_final_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
AUTO_INCREMENT = 101
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`planes_estudio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`planes_estudio` (
  `id_plan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_plan`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`resoluciones_carrera`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`resoluciones_carrera` (
  `id_resolucion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_resolucion`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`plan_resolucion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`plan_resolucion` (
  `id_resolucion` INT UNSIGNED NOT NULL,
  `id_plan` INT UNSIGNED NOT NULL,
  `id_carrera` INT UNSIGNED NOT NULL,
  `id_tipo_archivo` INT UNSIGNED NULL DEFAULT NULL,
  `total_horas` INT NULL DEFAULT NULL,
  `fecha` DATE NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_resolucion`, `id_plan`, `id_carrera`),
  INDEX `plan_resolucion_id_plan_foreign` (`id_plan` ASC) VISIBLE,
  INDEX `plan_resolucion_id_carrera_foreign` (`id_carrera` ASC) VISIBLE,
  INDEX `plan_resolucion_id_tipo_archivo_foreign` (`id_tipo_archivo` ASC) VISIBLE,
  CONSTRAINT `plan_resolucion_id_carrera_foreign`
    FOREIGN KEY (`id_carrera`)
    REFERENCES `laravel_db`.`carreras` (`id_carrera`),
  CONSTRAINT `plan_resolucion_id_plan_foreign`
    FOREIGN KEY (`id_plan`)
    REFERENCES `laravel_db`.`planes_estudio` (`id_plan`),
  CONSTRAINT `plan_resolucion_id_resolucion_foreign`
    FOREIGN KEY (`id_resolucion`)
    REFERENCES `laravel_db`.`resoluciones_carrera` (`id_resolucion`),
  CONSTRAINT `plan_resolucion_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `laravel_db`.`tipos_archivo` (`id_tipo_archivo`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index` (`user_id` ASC) VISIBLE,
  INDEX `sessions_last_activity_index` (`last_activity` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `laravel_db`.`telefonos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_db`.`telefonos` (
  `telefono` VARCHAR(255) NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`telefono`, `id_usuario`),
  INDEX `telefonos_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `telefonos_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `laravel_db`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
