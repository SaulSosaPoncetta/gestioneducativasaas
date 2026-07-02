USE `laravel_db` ;

CREATE TABLE IF NOT EXISTS `laravel_db`.`categorias_auditoria` (
  `id_categoria_auditoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `descripcion_categoria` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_categoria_auditoria`))
ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

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

-- Reestablecer configuraciones globales de MySQL
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET SQL_MODE=@OLD_SQL_MODE;