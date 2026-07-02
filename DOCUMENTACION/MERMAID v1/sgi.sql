-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema sgi
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema sgi
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `sgi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `sgi` ;

-- -----------------------------------------------------
-- Table `sgi`.`paises`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`paises` (
  `id_pais` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_pais` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_pais`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`localidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`localidades` (
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
    REFERENCES `sgi`.`paises` (`id_pais`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`roles` (
  `id_rol` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_rol`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`usuarios` (
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
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `usuarios_email_unique` (`email` ASC) VISIBLE,
  INDEX `usuarios_id_rol_foreign` (`id_rol` ASC) VISIBLE,
  INDEX `usuarios_id_localidad_foreign` (`id_localidad` ASC) VISIBLE,
  CONSTRAINT `usuarios_id_localidad_foreign`
    FOREIGN KEY (`id_localidad`)
    REFERENCES `sgi`.`localidades` (`id_localidad`),
  CONSTRAINT `usuarios_id_rol_foreign`
    FOREIGN KEY (`id_rol`)
    REFERENCES `sgi`.`roles` (`id_rol`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`administradores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`administradores` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `administradores_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`docentes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`docentes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `nro_legajo` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `docentes_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`administracion_docente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`administracion_docente` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_docente`, `fecha_hora`),
  INDEX `administracion_docente_id_usuario_docente_foreign` (`id_usuario_docente` ASC) VISIBLE,
  CONSTRAINT `administracion_docente_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `sgi`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_docente_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `sgi`.`docentes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`estudiantes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`estudiantes` (
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `estudiantes_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`administracion_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`administracion_estudiante` (
  `id_usuario_administracion` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_administracion`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `administracion_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `administracion_estudiante_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `sgi`.`administradores` (`id_usuario`),
  CONSTRAINT `administracion_estudiante_id_usuario_estudiante_foreign`
    FOREIGN KEY (`id_usuario_estudiante`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`ciclos_lectivos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`ciclos_lectivos` (
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
    REFERENCES `sgi`.`administradores` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`carreras`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`carreras` (
  `id_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_carrera`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`tipos_materia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`tipos_materia` (
  `id_tipo_materia` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo_materia` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_materia`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`materias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`materias` (
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
    REFERENCES `sgi`.`carreras` (`id_carrera`),
  CONSTRAINT `materias_id_tipo_materia_foreign`
    FOREIGN KEY (`id_tipo_materia`)
    REFERENCES `sgi`.`tipos_materia` (`id_tipo_materia`),
  CONSTRAINT `materias_id_usuario_administrador_foreign`
    FOREIGN KEY (`id_usuario_administrador`)
    REFERENCES `sgi`.`administradores` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`tipos_mesa_examen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`tipos_mesa_examen` (
  `id_tipo_mesa` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_mesa`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`mesas_examen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`mesas_examen` (
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
    REFERENCES `sgi`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `mesas_examen_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `mesas_examen_id_tipo_mesa_foreign`
    FOREIGN KEY (`id_tipo_mesa`)
    REFERENCES `sgi`.`tipos_mesa_examen` (`id_tipo_mesa`),
  CONSTRAINT `mesas_examen_id_usuario_administracion_foreign`
    FOREIGN KEY (`id_usuario_administracion`)
    REFERENCES `sgi`.`administradores` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`tipos_archivo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`tipos_archivo` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` VARCHAR(255) NOT NULL,
  `archivo` BLOB NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`archivo_examen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`archivo_examen` (
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
    REFERENCES `sgi`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `archivo_examen_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `sgi`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `archivo_examen_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`archivo_materia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`archivo_materia` (
  `id_tipo_archivo` INT UNSIGNED NOT NULL,
  `id_materia` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_archivo`, `id_materia`),
  INDEX `archivo_materia_id_materia_foreign` (`id_materia` ASC) VISIBLE,
  CONSTRAINT `archivo_materia_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `archivo_materia_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `sgi`.`tipos_archivo` (`id_tipo_archivo`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`categorias_auditoria`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`categorias_auditoria` (
  `id_categoria_auditoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_categoria` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_categoria_auditoria`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`auditorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`auditorias` (
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
    REFERENCES `sgi`.`categorias_auditoria` (`id_categoria_auditoria`),
  CONSTRAINT `auditorias_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`correlativas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`correlativas` (
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
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `correlativas_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `sgi`.`materias` (`id_materia`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`dnis`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`dnis` (
  `dni` VARCHAR(255) NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `tipo_dni` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`dni`, `id_usuario`),
  INDEX `dnis_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `dnis_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`docente_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`docente_estudiante` (
  `id_usuario_docente` INT UNSIGNED NOT NULL,
  `id_usuario_estudiante` INT UNSIGNED NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario_docente`, `id_usuario_estudiante`, `fecha_hora`),
  INDEX `docente_estudiante_id_usuario_estudiante_foreign` (`id_usuario_estudiante` ASC) VISIBLE,
  CONSTRAINT `docente_estudiante_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `sgi`.`docentes` (`id_usuario`),
  CONSTRAINT `docente_estudiante_id_usuario_estudiante_foreign`
    FOREIGN KEY (`id_usuario_estudiante`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`estados_carrera`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`estados_carrera` (
  `id_estado_carrera` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_carrera`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`estados_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`estados_estudiante` (
  `id_estado_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_estado` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_estado_estudiante`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`estudiante_carrera`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`estudiante_carrera` (
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
    REFERENCES `sgi`.`carreras` (`id_carrera`),
  CONSTRAINT `estudiante_carrera_id_estado_carrera_foreign`
    FOREIGN KEY (`id_estado_carrera`)
    REFERENCES `sgi`.`estados_carrera` (`id_estado_carrera`),
  CONSTRAINT `estudiante_carrera_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`tipos_estudiante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`tipos_estudiante` (
  `id_tipo_estudiante` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_tipo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_tipo_estudiante`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`inscripciones_materia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`inscripciones_materia` (
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
    REFERENCES `sgi`.`ciclos_lectivos` (`id_ciclo`),
  CONSTRAINT `inscripciones_materia_id_estado_estudiante_foreign`
    FOREIGN KEY (`id_estado_estudiante`)
    REFERENCES `sgi`.`estados_estudiante` (`id_estado_estudiante`),
  CONSTRAINT `inscripciones_materia_id_materia_foreign`
    FOREIGN KEY (`id_materia`)
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `inscripciones_materia_id_tipo_estudiante_foreign`
    FOREIGN KEY (`id_tipo_estudiante`)
    REFERENCES `sgi`.`tipos_estudiante` (`id_tipo_estudiante`),
  CONSTRAINT `inscripciones_materia_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`materia_docente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`materia_docente` (
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
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `materia_docente_id_usuario_docente_foreign`
    FOREIGN KEY (`id_usuario_docente`)
    REFERENCES `sgi`.`docentes` (`id_usuario`),
  CONSTRAINT `materia_docente_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`mesa_examen_docente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`mesa_examen_docente` (
  `id_mesa` INT UNSIGNED NOT NULL,
  `id_docente` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_mesa`, `id_docente`),
  INDEX `mesa_examen_docente_id_docente_foreign` (`id_docente` ASC) VISIBLE,
  CONSTRAINT `mesa_examen_docente_id_docente_foreign`
    FOREIGN KEY (`id_docente`)
    REFERENCES `sgi`.`docentes` (`id_usuario`),
  CONSTRAINT `mesa_examen_docente_id_mesa_foreign`
    FOREIGN KEY (`id_mesa`)
    REFERENCES `sgi`.`mesas_examen` (`id_mesa`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`migrations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 37
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`notas_cuatrimestre`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`notas_cuatrimestre` (
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
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `notas_cuatrimestre_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `sgi`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_cuatrimestre_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`notas_final`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`notas_final` (
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
    REFERENCES `sgi`.`materias` (`id_materia`),
  CONSTRAINT `notas_final_id_mesa_foreign`
    FOREIGN KEY (`id_mesa`)
    REFERENCES `sgi`.`mesas_examen` (`id_mesa`),
  CONSTRAINT `notas_final_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `sgi`.`tipos_archivo` (`id_tipo_archivo`),
  CONSTRAINT `notas_final_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`estudiantes` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`planes_estudio`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`planes_estudio` (
  `id_plan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_plan`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`resoluciones_carrera`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`resoluciones_carrera` (
  `id_resolucion` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_resolucion`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`plan_resolucion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`plan_resolucion` (
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
    REFERENCES `sgi`.`carreras` (`id_carrera`),
  CONSTRAINT `plan_resolucion_id_plan_foreign`
    FOREIGN KEY (`id_plan`)
    REFERENCES `sgi`.`planes_estudio` (`id_plan`),
  CONSTRAINT `plan_resolucion_id_resolucion_foreign`
    FOREIGN KEY (`id_resolucion`)
    REFERENCES `sgi`.`resoluciones_carrera` (`id_resolucion`),
  CONSTRAINT `plan_resolucion_id_tipo_archivo_foreign`
    FOREIGN KEY (`id_tipo_archivo`)
    REFERENCES `sgi`.`tipos_archivo` (`id_tipo_archivo`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sgi`.`telefonos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sgi`.`telefonos` (
  `telefono` VARCHAR(255) NOT NULL,
  `id_usuario` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`telefono`, `id_usuario`),
  INDEX `telefonos_id_usuario_foreign` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `telefonos_id_usuario_foreign`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `sgi`.`usuarios` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
