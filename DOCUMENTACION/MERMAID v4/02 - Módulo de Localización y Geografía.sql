USE `laravel_db` ;

SET FOREIGN_KEY_CHECKS=0;

-- Tabla: paises
CREATE TABLE IF NOT EXISTS `laravel_db`.`paises` (
  `id_pais` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_pais` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_pais`))
ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabla: localidades
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

SET FOREIGN_KEY_CHECKS=1;