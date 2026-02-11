-- Crear base de datos y tabla para Crud API
CREATE DATABASE IF NOT EXISTS `crud_api` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `crud_api`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fecha` DATE DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar una usuaria de prueba (Pilar Gonzalez) con contraseña conocida (123456)
INSERT IGNORE INTO `users` (nombre, apellido, email, password, fecha, telefono) VALUES (
  'Pilar', 'Gonzalez', 'pilar@example.com', '$2y$12$3PSCWMPq5cxrIiUm9nKub.lwo5sOJgRnaADt0Ojt4yzC60nleI5rq', CURDATE(), '600123456'
);
