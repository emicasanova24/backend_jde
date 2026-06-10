-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 25-05-2026 a las 17:03:25
-- Versión del servidor: 5.6.51
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `juegpdee_base`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`juegpdee`@`localhost` PROCEDURE `_add_col` (IN `tbl` VARCHAR(100), IN `col` VARCHAR(100), IN `def` TEXT)   BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END$$

CREATE DEFINER=`juegpdee`@`localhost` PROCEDURE `_add_unique` (IN `tbl` VARCHAR(100), IN `idx` VARCHAR(100), IN `cols` TEXT)   BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` ADD UNIQUE KEY `', idx, '` (', cols, ')');
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END$$

CREATE DEFINER=`juegpdee`@`localhost` PROCEDURE `_drop_idx` (IN `tbl` VARCHAR(100), IN `idx` VARCHAR(100))   BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @_sql = CONCAT('ALTER TABLE `', tbl, '` DROP INDEX `', idx, '`');
        PREPARE _stmt FROM @_sql;
        EXECUTE _stmt;
        DEALLOCATE PREPARE _stmt;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_account`
--

CREATE TABLE `admin_account` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(60) NOT NULL DEFAULT '',
  `bio` varchar(1000) NOT NULL DEFAULT '',
  `role_label` varchar(120) NOT NULL DEFAULT 'Administrador Principal',
  `admin_code` varchar(32) NOT NULL DEFAULT 'ADMIN-001',
  `avatar_url` varchar(255) NOT NULL DEFAULT '/assets/logo.png',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `live_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `developer_mode` tinyint(1) NOT NULL DEFAULT '0',
  `member_since` varchar(16) NOT NULL DEFAULT '2023',
  `verified` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `admin_account`
--

INSERT INTO `admin_account` (`id`, `full_name`, `email`, `password`, `phone`, `bio`, `role_label`, `admin_code`, `avatar_url`, `email_notifications`, `live_notifications`, `developer_mode`, `member_since`, `verified`, `updated_at`) VALUES
(1, 'Emiliano Casanova', 'admin@juegodeequipo.com', '$2y$12$nhS9481PRS3iLOrE8sXVOOPzN6bIp9k1f0/Cpxc9s0Dg/Z2jVd/ea', '02392529428', 'Gestión integral de la plataforma administrativa y supervisión de ligas regionales.', 'Administrador Principal', 'ADMIN-001', '/uploads/2026-04/956bafdd-8315-45d0-a908-9f95afd7307f.jpg', 1, 1, 0, '2023', 1, '2026-05-25 16:08:21'),
(2, 'Facundo Ugarte', 'quifu@juegodeequipo.com', '$2y$12$XjH7Yvxbs9ED44bIkRAfXekIFux4Fyr1HabkOIpfB651IighHsPVu', '2394 415151', '', 'Administrador', 'ADMIN-002', '/uploads/2026-05/10332fd0-226c-4129-b215-e0d7155472fa.jpg', 1, 1, 0, '2026', 1, '2026-05-25 16:06:44'),
(3, 'Gonzalo Gonzalez', 'gonzalo.gonzalez.g7@gmail.com', '$2y$12$.k9QeCdhhiHvqw/vkNBAXuXerjL48x6147ojeh9uR5xWawd8AMsV.', '', '', 'Editor', 'ADMIN-003', '/assets/logo.png', 1, 1, 0, '2026', 1, '2026-04-20 22:37:15'),
(5, 'Andrea Bustamante', 'andrea@juegodeequipo.com', '$2y$12$Dah3DuzqFywAmlHdfvJ/W.jpEhK/ahX6GtWmND6UzYAPlZy8qwmBa', '', '', 'Editor', 'ADMIN-005', '/uploads/2026-05/63cb9a3a-4f75-458d-896b-ce708a79056b.jpg', 1, 1, 0, '2026', 1, '2026-05-25 16:04:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estadio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `escudo` text COLLATE utf8mb4_unicode_ci,
  `categoria` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Primera',
  `temporada_id` int(11) NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre`, `estadio`, `escudo`, `categoria`, `temporada_id`) VALUES
(1, 'Cecil A. Roberts', 'Estadio de Cecil A. Roberts', '/assets-equipos/logos/roberts.png', 'Primera', 2),
(2, 'Unión Deportiva', 'Estadio \"Azul\" Francisco Morero', '/assets-equipos/logos/uniontl.png', 'Primera', 2),
(3, 'Unión de Bonifacio', 'Estadio de Unión de Bonifacio', '/assets-equipos/logos/bonifacio.png', 'Primera', 2),
(4, 'Deportivo Garré', 'Estadio de Deportivo Garré', '/assets-equipos/logos/garre.png', 'Primera', 2),
(5, 'Deportivo Maza', 'Estadio Demetrio Carmelo Loyarte', '/assets-equipos/logos/maza.png', 'Primera', 2),
(6, 'Deportivo Argentino', 'Estadio de Deportivo Argentino', '/assets-equipos/logos/deportivoarg.png', 'Primera', 2),
(7, 'Atlético Quenumá', 'Estadio Roberto \"Tito\" Castaneira', '/assets-equipos/logos/quenuma.png', 'Primera', 2),
(8, 'Atlético Argentino', 'Estadio José Sardon', '/assets-equipos/logos/argentinos.png', 'Primera', 2),
(9, 'Salazar FC', 'Estadio Martin \"Pato\" Pichetto', '/assets-equipos/logos/salazar.png', 'Primera', 2),
(10, 'Jorge Newbery', 'Estadio Edgardo Marduel', '/assets-equipos/logos/newbery.png', 'Primera', 2),
(11, 'El Ceibo', 'Estadio Doctor Pippo', '/assets-equipos/logos/ceibo.png', 'Primera', 2),
(12, 'La Gloria', 'Estadio Raul Changazzo', '/assets-equipos/logos/lagloria.png', 'Primera', 2),
(13, 'Deportivo 17', 'Estadio de Deportivo 17', '/assets-equipos/logos/17.png', 'Primera', 2),
(14, 'Juventud Unida', 'Estadio Artemio Gastaldi', '/assets-equipos/logos/juventud.png', 'Primera', 2),
(15, 'Cecil A. Roberts', 'Estadio de Cecil A. Roberts', '/assets-equipos/logos/roberts.png', 'Tercera', 2),
(16, 'Unión Deportiva', 'Estadio \"Azul\" Francisco Morero', '/assets-equipos/logos/uniontl.png', 'Tercera', 2),
(17, 'Unión de Bonifacio', 'Estadio de Unión de Bonifacio', '/assets-equipos/logos/bonifacio.png', 'Tercera', 2),
(18, 'Deportivo Garré', 'Estadio de Deportivo Garré', '/assets-equipos/logos/garre.png', 'Tercera', 2),
(19, 'Deportivo Maza', 'Estadio Demetrio Carmelo Loyarte', '/assets-equipos/logos/maza.png', 'Tercera', 2),
(20, 'Deportivo Argentino', 'Estadio de Deportivo Argentino', '/assets-equipos/logos/deportivoarg.png', 'Tercera', 2),
(21, 'Atlético Quenumá', 'Estadio Roberto \"Tito\" Castaneira', '/assets-equipos/logos/quenuma.png', 'Tercera', 2),
(22, 'Atlético Argentino', 'Estadio José Sardon', '/assets-equipos/logos/argentinos.png', 'Tercera', 2),
(23, 'Salazar FC', 'Estadio Martin \"Pato\" Pichetto', '/assets-equipos/logos/salazar.png', 'Tercera', 2),
(24, 'Jorge Newbery', 'Estadio Edgardo Marduel', '/assets-equipos/logos/newbery.png', 'Tercera', 2),
(25, 'El Ceibo', 'Estadio Doctor Pippo', '/assets-equipos/logos/ceibo.png', 'Tercera', 2),
(26, 'La Gloria', 'Estadio Raul Changazzo', '/assets-equipos/logos/lagloria.png', 'Tercera', 2),
(27, 'Deportivo 17', 'Estadio de Deportivo 17', '/assets-equipos/logos/17.png', 'Tercera', 2),
(28, 'Juventud Unida', 'Estadio Artemio Gastaldi', '/assets-equipos/logos/juventud.png', 'Tercera', 2),
(29, 'Cecil A. Roberts', 'Estadio de Cecil A. Roberts', '/assets-equipos/logos/roberts.png', 'Senior', 2),
(30, 'Unión Deportiva', 'Estadio \"Azul\" Francisco Morero', '/assets-equipos/logos/uniontl.png', 'Senior', 2),
(31, 'Unión de Bonifacio', 'Estadio de Unión de Bonifacio', '/assets-equipos/logos/bonifacio.png', 'Senior', 2),
(32, 'Deportivo Garré', 'Estadio de Deportivo Garré', '/assets-equipos/logos/garre.png', 'Senior', 2),
(33, 'Deportivo Maza', 'Estadio Demetrio Carmelo Loyarte', '/assets-equipos/logos/maza.png', 'Senior', 2),
(34, 'Deportivo Argentino', 'Estadio de Deportivo Argentino', '/assets-equipos/logos/deportivoarg.png', 'Senior', 2),
(35, 'Ferro', 'Estadio Doctor Pippo', '/assets-equipos/logos/ferro.png', 'Senior', 2),
(36, 'Atlético Argentino', 'Estadio José Sardon', '/assets-equipos/logos/argentinos.png', 'Senior', 2),
(37, 'Salazar FC', 'Estadio Martin \"Pato\" Pichetto', '/assets-equipos/logos/salazar.png', 'Senior', 2),
(38, 'Jorge Newbery', 'Estadio Edgardo Marduel', '/assets-equipos/logos/newbery.png', 'Senior', 2),
(39, 'El Ceibo', 'Estadio Doctor Pippo', '/assets-equipos/logos/ceibo.png', 'Senior', 2),
(40, 'La Gloria', 'Estadio Raul Changazzo', '/assets-equipos/logos/lagloria.png', 'Senior', 2),
(41, 'Deportivo 17', 'Estadio de Deportivo 17', '/assets-equipos/logos/17.png', 'Senior', 2),
(42, 'Juventud Unida', 'Estadio Artemio Gastaldi', '/assets-equipos/logos/juventud.png', 'Senior', 2),
(43, 'Villa del Parque', 'Estadio Walter \"Chocho\" Orsi', '/assets-equipos/logos/villa.png', 'Primera', 2),
(44, 'Villa del Parque', 'Estadio Walter \"Chocho\" Orsi', '/assets-equipos/logos/villa.png', 'Tercera', 2),
(49, 'Villa del Parque', 'Estadio Walter \"Chocho\" Orsi', '/assets-equipos/logos/villa.png', 'Senior', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `partido_id` int(11) DEFAULT NULL,
  `jugador_id` int(11) DEFAULT NULL,
  `equipo_id` int(11) DEFAULT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minuto` int(11) DEFAULT NULL,
  `temporada_id` int(11) NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `partido_id`, `jugador_id`, `equipo_id`, `tipo`, `minuto`, `temporada_id`) VALUES
(49, 3, 233, 13, 'gol', NULL, 2),
(50, 3, 234, 13, 'gol', NULL, 2),
(51, 3, 235, 13, 'gol', NULL, 2),
(52, 3, 77, 6, 'gol', NULL, 2),
(53, 3, 227, 13, 'amarilla', NULL, 2),
(54, 3, 85, 6, 'amarilla', NULL, 2),
(55, 4, 46, 4, 'gol', NULL, 2),
(56, 4, 48, 4, 'gol', NULL, 2),
(57, 4, 48, 4, 'gol', NULL, 2),
(58, 4, 91, 14, 'amarilla', NULL, 2),
(59, 4, 90, 14, 'amarilla', NULL, 2),
(60, 4, 96, 14, 'amarilla', NULL, 2),
(61, 4, 102, 14, 'amarilla', NULL, 2),
(62, 4, 39, 4, 'amarilla', NULL, 2),
(63, 4, 42, 4, 'amarilla', NULL, 2),
(64, 4, 45, 4, 'amarilla', NULL, 2),
(65, 4, 52, 4, 'amarilla', NULL, 2),
(66, 6, 13, 5, 'gol', NULL, 2),
(67, 6, 192, 8, 'amarilla', NULL, 2),
(68, 6, 198, 8, 'amarilla', NULL, 2),
(69, 6, 193, 8, 'amarilla', NULL, 2),
(70, 6, 722, 5, 'amarilla', NULL, 2),
(71, 7, 183, 2, 'gol', NULL, 2),
(72, 7, 126, 11, 'amarilla', NULL, 2),
(73, 7, 132, 11, 'amarilla', NULL, 2),
(74, 7, 177, 2, 'amarilla', NULL, 2),
(179, 5, 734, 1, 'gol', NULL, 2),
(180, 5, 731, 1, 'gol', NULL, 2),
(181, 5, 217, 3, 'gol', NULL, 2),
(182, 5, 729, 1, 'amarilla', NULL, 2),
(183, 5, 734, 1, 'amarilla', NULL, 2),
(184, 5, 210, 3, 'amarilla', NULL, 2),
(185, 5, 213, 3, 'amarilla', NULL, 2),
(186, 5, 212, 3, 'amarilla', NULL, 2),
(187, 5, 223, 3, 'amarilla', NULL, 2),
(188, 5, 742, 3, 'roja', NULL, 2),
(189, 5, 743, 3, 'roja', NULL, 2),
(190, 8, 36, 7, 'gol', NULL, 2),
(191, 8, 148, 12, 'gol', NULL, 2),
(192, 8, 22, 7, 'amarilla', NULL, 2),
(193, 8, 30, 7, 'amarilla', NULL, 2),
(194, 8, 35, 7, 'amarilla', NULL, 2),
(195, 8, 141, 12, 'amarilla', NULL, 2),
(196, 8, 147, 12, 'amarilla', NULL, 2),
(197, 8, 145, 12, 'amarilla', NULL, 2),
(198, 9, 60, 9, 'amarilla', NULL, 2),
(199, 9, 61, 9, 'amarilla', NULL, 2),
(200, 9, 105, 43, 'amarilla', NULL, 2),
(201, 9, 117, 43, 'amarilla', NULL, 2),
(202, 9, 723, 43, 'roja', NULL, 2),
(203, 9, 744, 9, 'amarilla', NULL, 2),
(204, 10, 183, 2, 'gol', NULL, 2),
(205, 10, 181, 2, 'gol', NULL, 2),
(206, 10, 197, 8, 'gol', NULL, 2),
(207, 10, 177, 2, 'amarilla', NULL, 2),
(208, 10, 178, 2, 'amarilla', NULL, 2),
(209, 10, 187, 2, 'amarilla', NULL, 2),
(210, 10, 193, 8, 'amarilla', NULL, 2),
(211, 10, 194, 8, 'amarilla', NULL, 2),
(212, 10, 197, 8, 'roja', NULL, 2),
(213, 10, 196, 8, 'amarilla', NULL, 2),
(214, 10, 195, 8, 'amarilla', NULL, 2),
(215, 11, 95, 14, 'gol', NULL, 2),
(216, 11, 92, 14, 'gol', NULL, 2),
(217, 11, 71, 6, 'amarilla', NULL, 2),
(218, 11, 70, 6, 'amarilla', NULL, 2),
(219, 11, 73, 6, 'amarilla', NULL, 2),
(220, 11, 75, 6, 'amarilla', NULL, 2),
(221, 11, 77, 6, 'amarilla', NULL, 2),
(222, 11, 93, 14, 'amarilla', NULL, 2),
(223, 11, 94, 14, 'amarilla', NULL, 2),
(224, 11, 90, 14, 'amarilla', NULL, 2),
(225, 11, 91, 14, 'amarilla', NULL, 2),
(226, 11, 92, 14, 'amarilla', NULL, 2),
(227, 12, 28, 7, 'gol', NULL, 2),
(228, 12, 26, 7, 'gol', NULL, 2),
(229, 12, 14, 5, 'amarilla', NULL, 2),
(230, 12, 11, 5, 'amarilla', NULL, 2),
(231, 12, 25, 7, 'amarilla', NULL, 2),
(232, 12, 22, 7, 'amarilla', NULL, 2),
(233, 13, 128, 11, 'gol', NULL, 2),
(234, 13, 108, 43, 'gol', NULL, 2),
(235, 13, 110, 43, 'amarilla', NULL, 2),
(236, 13, 111, 43, 'amarilla', NULL, 2),
(237, 13, 128, 11, 'amarilla', NULL, 2),
(238, 13, 132, 11, 'amarilla', NULL, 2),
(239, 13, 135, 11, 'amarilla', NULL, 2),
(240, 14, 216, 3, 'gol', NULL, 2),
(241, 14, 234, 13, 'gol', NULL, 2),
(242, 14, 210, 3, 'amarilla', NULL, 2),
(243, 14, 211, 3, 'amarilla', NULL, 2),
(244, 14, 216, 3, 'amarilla', NULL, 2),
(245, 14, 215, 3, 'amarilla', NULL, 2),
(246, 14, 228, 13, 'amarilla', NULL, 2),
(247, 14, 229, 13, 'amarilla', NULL, 2),
(248, 14, 232, 13, 'amarilla', NULL, 2),
(249, 14, 235, 13, 'amarilla', NULL, 2),
(250, 15, 46, 4, 'gol', NULL, 2),
(251, 15, 52, 4, 'gol', NULL, 2),
(252, 15, 64, 9, 'gol', NULL, 2),
(253, 15, 64, 9, 'gol', NULL, 2),
(254, 15, 63, 9, 'gol', NULL, 2),
(255, 15, 40, 4, 'amarilla', NULL, 2),
(256, 15, 42, 4, 'amarilla', NULL, 2),
(257, 15, 50, 4, 'amarilla', NULL, 2),
(258, 15, 52, 4, 'amarilla', NULL, 2),
(259, 15, 56, 9, 'amarilla', NULL, 2),
(260, 15, 58, 9, 'amarilla', NULL, 2),
(261, 15, 60, 9, 'amarilla', NULL, 2),
(262, 15, 61, 9, 'amarilla', NULL, 2),
(263, 16, 165, 10, 'gol', NULL, 2),
(264, 16, 143, 12, 'amarilla', NULL, 2),
(265, 17, 175, 2, 'gol', NULL, 2),
(266, 17, 175, 2, 'gol', NULL, 2),
(267, 17, 181, 2, 'gol', NULL, 2),
(268, 17, 182, 2, 'gol', NULL, 2),
(269, 17, 183, 2, 'gol', NULL, 2),
(270, 17, 21, 7, 'amarilla', NULL, 2),
(271, 17, 26, 7, 'amarilla', NULL, 2),
(272, 17, 23, 7, 'roja', NULL, 2),
(273, 17, 25, 7, 'roja', NULL, 2),
(274, 17, 187, 2, 'amarilla', NULL, 2),
(275, 17, 189, 2, 'amarilla', NULL, 2),
(276, 18, 136, 11, 'gol', NULL, 2),
(277, 18, 46, 4, 'gol', NULL, 2),
(278, 18, 130, 11, 'roja', NULL, 2),
(279, 18, 40, 4, 'amarilla', NULL, 2),
(280, 18, 42, 4, 'amarilla', NULL, 2),
(281, 18, 45, 4, 'amarilla', NULL, 2),
(282, 19, 732, 1, 'gol', NULL, 2),
(283, 19, 732, 1, 'gol', NULL, 2),
(284, 19, 725, 1, 'gol', NULL, 2),
(285, 19, 230, 13, 'amarilla', NULL, 2),
(286, 19, 232, 13, 'amarilla', NULL, 2),
(287, 19, 234, 13, 'amarilla', NULL, 2),
(288, 19, 725, 1, 'amarilla', NULL, 2),
(296, 20, 10, 5, 'gol', NULL, 2),
(297, 20, 162, 10, 'gol', NULL, 2),
(298, 20, 157, 10, 'gol', NULL, 2),
(299, 20, 162, 10, 'gol', NULL, 2),
(300, 20, 9, 5, 'gol', NULL, 2),
(301, 20, 164, 10, 'gol', NULL, 2),
(302, 20, 162, 10, 'gol', NULL, 2),
(303, 20, 4, 5, 'amarilla', NULL, 2),
(304, 20, 9, 5, 'amarilla', NULL, 2),
(305, 21, 105, 43, 'gol', NULL, 2),
(306, 21, 106, 43, 'gol', NULL, 2),
(307, 21, 192, 8, 'amarilla', NULL, 2),
(308, 21, 200, 8, 'amarilla', NULL, 2),
(309, 21, 118, 43, 'amarilla', NULL, 2),
(310, 21, 110, 43, 'amarilla', NULL, 2),
(311, 22, 60, 9, 'gol', NULL, 2),
(312, 22, 64, 9, 'gol', NULL, 2),
(313, 22, 80, 6, 'gol', NULL, 2),
(314, 22, 80, 6, 'gol', NULL, 2),
(315, 22, 60, 9, 'amarilla', NULL, 2),
(316, 22, 56, 9, 'roja', NULL, 2),
(317, 22, 71, 6, 'amarilla', NULL, 2),
(318, 23, 89, 14, 'gol', NULL, 2),
(319, 23, 94, 14, 'gol', NULL, 2),
(320, 23, 98, 14, 'amarilla', NULL, 2),
(321, 23, 96, 14, 'amarilla', NULL, 2),
(322, 23, 213, 3, 'amarilla', NULL, 2),
(323, 23, 218, 3, 'roja', NULL, 2),
(324, 24, 94, 14, 'gol', NULL, 2),
(325, 24, 92, 14, 'gol', NULL, 2),
(326, 24, 732, 1, 'gol', NULL, 2),
(327, 24, 725, 1, 'amarilla', NULL, 2),
(328, 24, 726, 1, 'amarilla', NULL, 2),
(329, 24, 731, 1, 'amarilla', NULL, 2),
(330, 24, 732, 1, 'amarilla', NULL, 2),
(331, 24, 734, 1, 'amarilla', NULL, 2),
(332, 24, 737, 1, 'amarilla', NULL, 2),
(333, 24, 90, 14, 'amarilla', NULL, 2),
(334, 25, 165, 10, 'gol', NULL, 2),
(335, 25, 165, 10, 'gol', NULL, 2),
(336, 25, 164, 10, 'gol', NULL, 2),
(337, 25, 162, 10, 'gol', NULL, 2),
(338, 25, 189, 2, 'amarilla', NULL, 2),
(339, 25, 186, 2, 'amarilla', NULL, 2),
(340, 25, 164, 10, 'amarilla', NULL, 2),
(341, 25, 160, 10, 'amarilla', NULL, 2),
(342, 25, 165, 10, 'amarilla', NULL, 2),
(343, 27, 46, 4, 'gol', NULL, 2),
(344, 27, 43, 4, 'gol', NULL, 2),
(345, 27, 199, 8, 'gol', NULL, 2),
(346, 27, 40, 4, 'amarilla', NULL, 2),
(347, 27, 51, 4, 'amarilla', NULL, 2),
(348, 27, 203, 8, 'amarilla', NULL, 2),
(349, 28, 78, 6, 'gol', NULL, 2),
(350, 28, 79, 6, 'gol', NULL, 2),
(351, 28, 132, 11, 'gol', NULL, 2),
(352, 28, 71, 6, 'amarilla', NULL, 2),
(353, 28, 77, 6, 'amarilla', NULL, 2),
(354, 28, 76, 6, 'amarilla', NULL, 2),
(355, 28, 78, 6, 'amarilla', NULL, 2),
(356, 29, 7, 5, 'gol', NULL, 2),
(357, 29, 10, 5, 'gol', NULL, 2),
(358, 29, 12, 5, 'gol', NULL, 2),
(359, 29, 9, 5, 'gol', NULL, 2),
(360, 29, 150, 12, 'gol', NULL, 2),
(361, 29, 150, 12, 'gol', NULL, 2),
(362, 29, 7, 5, 'amarilla', NULL, 2),
(363, 29, 6, 5, 'amarilla', NULL, 2),
(364, 29, 722, 5, 'amarilla', NULL, 2),
(365, 30, 55, 9, 'gol', NULL, 2),
(366, 30, 217, 3, 'gol', NULL, 2),
(367, 30, 217, 3, 'gol', NULL, 2),
(368, 30, 209, 3, 'amarilla', NULL, 2),
(369, 30, 59, 9, 'amarilla', NULL, 2),
(370, 30, 62, 9, 'amarilla', NULL, 2),
(371, 30, 63, 9, 'amarilla', NULL, 2),
(385, 188, 142, 12, 'amarilla', NULL, 2),
(386, 188, 143, 12, 'amarilla', NULL, 2),
(387, 188, 148, 12, 'gol', NULL, 2),
(388, 188, 150, 12, 'gol', NULL, 2),
(389, 188, 181, 2, 'gol', NULL, 2),
(390, 188, 745, 12, 'amarilla', NULL, 2),
(391, 188, 183, 2, 'amarilla', NULL, 2),
(392, 188, 177, 2, 'amarilla', NULL, 2),
(393, 188, 746, 2, 'amarilla', NULL, 2),
(413, 187, 37, 7, 'gol', NULL, 2),
(414, 187, 27, 7, 'roja', NULL, 2),
(415, 187, 33, 7, 'amarilla', NULL, 2),
(416, 187, 51, 4, 'amarilla', NULL, 2),
(417, 187, 46, 4, 'amarilla', NULL, 2),
(418, 187, 47, 4, 'amarilla', NULL, 2),
(419, 189, 78, 6, 'gol', NULL, 2),
(420, 189, 747, 8, 'amarilla', NULL, 2),
(421, 189, 197, 8, 'amarilla', NULL, 2),
(422, 189, 199, 8, 'amarilla', NULL, 2),
(423, 189, 208, 8, 'amarilla', NULL, 2),
(424, 189, 71, 6, 'amarilla', NULL, 2),
(425, 189, 79, 6, 'amarilla', NULL, 2),
(426, 189, 85, 6, 'amarilla', NULL, 2),
(427, 190, 98, 14, 'gol', NULL, 2),
(428, 190, 233, 13, 'gol', NULL, 2),
(429, 190, 232, 13, 'gol', NULL, 2),
(430, 190, 98, 14, 'amarilla', NULL, 2),
(431, 190, 96, 14, 'amarilla', NULL, 2),
(432, 190, 226, 13, 'amarilla', NULL, 2),
(433, 190, 227, 13, 'amarilla', NULL, 2),
(434, 190, 228, 13, 'amarilla', NULL, 2),
(435, 190, 233, 13, 'amarilla', NULL, 2),
(436, 185, 170, 10, 'gol', NULL, 2),
(437, 185, 165, 10, 'gol', NULL, 2),
(438, 185, 158, 10, 'gol', NULL, 2),
(439, 185, 161, 10, 'amarilla', NULL, 2),
(440, 185, 166, 10, 'amarilla', NULL, 2),
(441, 185, 117, 43, 'amarilla', NULL, 2),
(442, 185, 109, 43, 'amarilla', NULL, 2),
(443, 185, 114, 43, 'amarilla', NULL, 2),
(444, 186, 732, 1, 'gol', NULL, 2),
(445, 186, 732, 1, 'gol', NULL, 2),
(446, 186, 55, 9, 'amarilla', NULL, 2),
(447, 186, 67, 9, 'roja', NULL, 2),
(448, 186, 59, 9, 'amarilla', NULL, 2),
(449, 186, 744, 9, 'amarilla', NULL, 2),
(450, 186, 724, 1, 'amarilla', NULL, 2),
(451, 186, 726, 1, 'amarilla', NULL, 2),
(452, 186, 729, 1, 'amarilla', NULL, 2),
(453, 191, 128, 11, 'gol', NULL, 2),
(454, 191, 128, 11, 'gol', NULL, 2),
(455, 191, 128, 11, 'gol', NULL, 2),
(456, 191, 132, 11, 'gol', NULL, 2),
(457, 191, 212, 3, 'gol', NULL, 2),
(458, 191, 124, 11, 'amarilla', NULL, 2),
(459, 191, 134, 11, 'amarilla', NULL, 2),
(460, 191, 125, 11, 'amarilla', NULL, 2),
(461, 191, 136, 11, 'amarilla', NULL, 2),
(462, 191, 126, 11, 'amarilla', NULL, 2),
(463, 191, 139, 11, 'gol', NULL, 2),
(464, 191, 211, 3, 'amarilla', NULL, 2),
(465, 191, 222, 3, 'amarilla', NULL, 2),
(509, 194, 79, 6, 'gol', NULL, 2),
(510, 194, 79, 6, 'gol', NULL, 2),
(511, 194, 37, 7, 'gol', NULL, 2),
(512, 194, 70, 6, 'amarilla', NULL, 2),
(513, 194, 73, 6, 'amarilla', NULL, 2),
(514, 194, 78, 6, 'amarilla', NULL, 2),
(515, 194, 22, 7, 'amarilla', NULL, 2),
(516, 194, 24, 7, 'amarilla', NULL, 2),
(517, 194, 26, 7, 'amarilla', NULL, 2),
(518, 195, 182, 2, 'gol', NULL, 2),
(519, 195, 182, 2, 'gol', NULL, 2),
(520, 195, 12, 5, 'gol', NULL, 2),
(521, 195, 175, 2, 'amarilla', NULL, 2),
(522, 195, 176, 2, 'amarilla', NULL, 2),
(523, 195, 10, 5, 'amarilla', NULL, 2),
(524, 195, 722, 5, 'roja', NULL, 2),
(525, 195, 9, 5, 'amarilla', NULL, 2),
(526, 196, 743, 3, 'gol', NULL, 2),
(527, 196, 200, 8, 'gol', NULL, 2),
(528, 196, 213, 3, 'roja', NULL, 2),
(529, 196, 215, 3, 'amarilla', NULL, 2),
(530, 196, 195, 8, 'amarilla', NULL, 2),
(531, 196, 197, 8, 'amarilla', NULL, 2),
(532, 196, 198, 8, 'amarilla', NULL, 2),
(533, 198, 148, 12, 'gol', NULL, 2),
(534, 198, 118, 43, 'amarilla', NULL, 2),
(535, 198, 146, 12, 'amarilla', NULL, 2),
(536, 198, 745, 12, 'amarilla', NULL, 2),
(537, 192, 732, 1, 'gol', NULL, 2),
(538, 192, 136, 11, 'gol', NULL, 2),
(539, 192, 726, 1, 'amarilla', NULL, 2),
(540, 192, 728, 1, 'roja', NULL, 2),
(541, 192, 127, 11, 'amarilla', NULL, 2),
(542, 192, 126, 11, 'amarilla', NULL, 2),
(543, 192, 124, 11, 'roja', NULL, 2),
(544, 192, 730, 1, 'amarilla', NULL, 2),
(545, 192, 732, 1, 'amarilla', NULL, 2),
(546, 192, 131, 11, 'amarilla', NULL, 2),
(547, 197, 231, 13, 'gol', NULL, 2),
(548, 197, 748, 9, 'gol', NULL, 2),
(549, 197, 230, 13, 'amarilla', NULL, 2),
(550, 197, 226, 13, 'amarilla', NULL, 2),
(551, 197, 66, 9, 'amarilla', NULL, 2),
(552, 197, 68, 9, 'amarilla', NULL, 2),
(553, 193, 52, 4, 'gol', NULL, 2),
(554, 193, 161, 10, 'gol', NULL, 2),
(555, 193, 40, 4, 'amarilla', NULL, 2),
(556, 193, 41, 4, 'amarilla', NULL, 2),
(557, 193, 42, 4, 'amarilla', NULL, 2),
(558, 193, 47, 4, 'amarilla', NULL, 2),
(559, 193, 46, 4, 'amarilla', NULL, 2),
(560, 193, 159, 10, 'amarilla', NULL, 2),
(561, 193, 165, 10, 'amarilla', NULL, 2),
(562, 193, 166, 10, 'amarilla', NULL, 2),
(563, 193, 162, 10, 'amarilla', NULL, 2),
(681, 203, 233, 13, 'gol', NULL, 2),
(682, 203, 229, 13, 'gol', NULL, 2),
(683, 203, 228, 13, 'gol', NULL, 2),
(736, 200, 162, 10, 'gol', NULL, 2),
(737, 200, 162, 10, 'gol', NULL, 2),
(738, 200, 80, 6, 'amarilla', NULL, 2),
(739, 200, 73, 6, 'amarilla', NULL, 2),
(740, 200, 74, 6, 'amarilla', NULL, 2),
(741, 200, 163, 10, 'amarilla', NULL, 2),
(742, 200, 77, 6, 'amarilla', NULL, 2),
(743, 201, 734, 1, 'gol', NULL, 2),
(744, 201, 724, 1, 'amarilla', NULL, 2),
(745, 201, 730, 1, 'amarilla', NULL, 2),
(746, 201, 729, 1, 'amarilla', NULL, 2),
(747, 201, 732, 1, 'amarilla', NULL, 2),
(748, 201, 734, 1, 'amarilla', NULL, 2),
(749, 201, 192, 8, 'amarilla', NULL, 2),
(750, 201, 200, 8, 'amarilla', NULL, 2),
(751, 201, 203, 8, 'amarilla', NULL, 2),
(752, 202, 37, 7, 'gol', NULL, 2),
(753, 202, 26, 7, 'gol', NULL, 2),
(754, 202, 743, 3, 'gol', NULL, 2),
(755, 202, 30, 7, 'gol', NULL, 2),
(756, 202, 24, 7, 'amarilla', NULL, 2),
(757, 202, 222, 3, 'amarilla', NULL, 2),
(758, 202, 742, 3, 'amarilla', NULL, 2),
(759, 202, 215, 3, 'amarilla', NULL, 2),
(760, 202, 212, 3, 'gol', NULL, 2),
(761, 204, 92, 14, 'gol', NULL, 2),
(762, 204, 102, 14, 'gol', NULL, 2),
(763, 204, 92, 14, 'gol', NULL, 2),
(764, 204, 93, 14, 'amarilla', NULL, 2),
(765, 204, 95, 14, 'amarilla', NULL, 2),
(766, 204, 744, 9, 'amarilla', NULL, 2),
(767, 204, 58, 9, 'amarilla', NULL, 2),
(768, 205, 52, 4, 'gol', NULL, 2),
(769, 205, 44, 4, 'gol', NULL, 2),
(770, 205, 46, 4, 'gol', NULL, 2),
(771, 205, 749, 12, 'gol', NULL, 2),
(772, 205, 52, 4, 'gol', NULL, 2),
(773, 205, 53, 4, 'gol', NULL, 2),
(774, 205, 148, 12, 'gol', NULL, 2),
(775, 205, 147, 12, 'amarilla', NULL, 2),
(776, 205, 750, 12, 'amarilla', NULL, 2),
(777, 205, 45, 4, 'amarilla', NULL, 2),
(778, 206, 12, 5, 'gol', NULL, 2),
(779, 206, 109, 43, 'gol', NULL, 2),
(780, 206, 5, 5, 'amarilla', NULL, 2),
(781, 206, 9, 5, 'amarilla', NULL, 2),
(782, 206, 12, 5, 'amarilla', NULL, 2),
(783, 206, 108, 43, 'amarilla', NULL, 2),
(844, 213, 89, 14, 'gol', NULL, 2),
(845, 213, 126, 11, 'amarilla', NULL, 2),
(846, 213, 129, 11, 'amarilla', NULL, 2),
(847, 213, 92, 14, 'amarilla', NULL, 2),
(848, 213, 86, 14, 'amarilla', NULL, 2),
(857, 211, 178, 2, 'gol', NULL, 2),
(858, 211, 189, 2, 'gol', NULL, 2),
(859, 211, 178, 2, 'gol', NULL, 2),
(860, 211, 183, 2, 'gol', NULL, 2),
(861, 211, 183, 2, 'amarilla', NULL, 2),
(862, 211, 117, 43, 'amarilla', NULL, 2),
(863, 211, 108, 43, 'amarilla', NULL, 2),
(864, 211, 113, 43, 'amarilla', NULL, 2),
(888, 215, 46, 4, 'gol', NULL, 2),
(889, 215, 46, 4, 'gol', NULL, 2),
(890, 215, 46, 4, 'gol', NULL, 2),
(891, 215, 53, 4, 'gol', NULL, 2),
(892, 215, 182, 2, 'gol', NULL, 2),
(893, 215, 52, 4, 'roja', NULL, 2),
(894, 215, 42, 4, 'gol', NULL, 2),
(895, 215, 182, 2, 'gol', NULL, 2),
(896, 215, 43, 4, 'autogol', NULL, 2),
(897, 215, 51, 4, 'amarilla', NULL, 2),
(898, 215, 175, 2, 'amarilla', NULL, 2),
(899, 215, 177, 2, 'amarilla', NULL, 2),
(900, 210, 166, 10, 'gol', NULL, 2),
(901, 210, 162, 10, 'gol', NULL, 2),
(902, 210, 166, 10, 'gol', NULL, 2),
(903, 210, 213, 3, 'roja', NULL, 2),
(904, 210, 222, 3, 'roja', NULL, 2),
(905, 210, 159, 10, 'amarilla', NULL, 2),
(910, 207, 75, 6, 'gol', NULL, 2),
(911, 207, 80, 6, 'gol', NULL, 2),
(912, 207, 80, 6, 'gol', NULL, 2),
(913, 207, 84, 6, 'gol', NULL, 2),
(914, 209, 726, 1, 'amarilla', NULL, 2),
(915, 209, 27, 7, 'roja', NULL, 2),
(916, 209, 30, 7, 'amarilla', NULL, 2),
(917, 209, 21, 7, 'amarilla', NULL, 2),
(918, 209, 24, 7, 'amarilla', NULL, 2),
(919, 209, 732, 1, 'gol', NULL, 2),
(920, 209, 733, 1, 'amarilla', NULL, 2),
(921, 209, 731, 1, 'amarilla', NULL, 2),
(922, 209, 23, 7, 'amarilla', NULL, 2),
(923, 209, 29, 7, 'amarilla', NULL, 2),
(924, 209, 26, 7, 'roja', NULL, 2),
(931, 212, 241, 13, 'gol', NULL, 2),
(932, 212, 751, 8, 'gol', NULL, 2),
(933, 212, 197, 8, 'gol', NULL, 2),
(934, 212, 228, 13, 'amarilla', NULL, 2),
(935, 212, 751, 8, 'amarilla', NULL, 2),
(936, 212, 208, 8, 'amarilla', NULL, 2),
(937, 214, 7, 5, 'gol', NULL, 2),
(938, 214, 6, 5, 'amarilla', NULL, 2),
(939, 214, 13, 5, 'amarilla', NULL, 2),
(1048, 218, 95, 14, 'gol', NULL, 2),
(1049, 218, 102, 14, 'gol', NULL, 2),
(1050, 218, 194, 8, 'amarilla', NULL, 2),
(1051, 218, 751, 8, 'amarilla', NULL, 2),
(1052, 218, 200, 8, 'amarilla', NULL, 2),
(1053, 218, 206, 8, 'amarilla', NULL, 2),
(1054, 218, 754, 8, 'amarilla', NULL, 2),
(1060, 229, 132, 11, 'gol', NULL, 2),
(1061, 229, 131, 11, 'gol', NULL, 2),
(1062, 229, 752, 9, 'gol', NULL, 2),
(1063, 229, 124, 11, 'amarilla', NULL, 2),
(1064, 229, 124, 11, 'gol', NULL, 2),
(1065, 229, 59, 9, 'amarilla', NULL, 2),
(1066, 229, 61, 9, 'amarilla', NULL, 2),
(1067, 216, 164, 10, 'gol', NULL, 2),
(1068, 216, 160, 10, 'amarilla', NULL, 2),
(1069, 216, 733, 1, 'amarilla', NULL, 2),
(1070, 216, 733, 1, 'gol', NULL, 2),
(1071, 216, 731, 1, 'gol', NULL, 2),
(1072, 216, 160, 10, 'gol', NULL, 2),
(1073, 216, 731, 1, 'amarilla', NULL, 2),
(1074, 216, 161, 10, 'amarilla', NULL, 2),
(1075, 216, 753, 1, 'roja', NULL, 2),
(1076, 216, 161, 10, 'roja', NULL, 2),
(1077, 216, 156, 10, 'amarilla', NULL, 2),
(1078, 216, 163, 10, 'amarilla', NULL, 2),
(1079, 216, 165, 10, 'amarilla', NULL, 2),
(1080, 216, 157, 10, 'amarilla', NULL, 2),
(1081, 216, 732, 1, 'amarilla', NULL, 2),
(1082, 228, 148, 12, 'gol', NULL, 2),
(1083, 228, 210, 3, 'gol', NULL, 2),
(1084, 228, 143, 12, 'roja', NULL, 2),
(1085, 228, 224, 3, 'amarilla', NULL, 2),
(1086, 228, 743, 3, 'amarilla', NULL, 2),
(1087, 219, 78, 6, 'gol', NULL, 2),
(1088, 219, 16, 5, 'amarilla', NULL, 2),
(1089, 219, 6, 5, 'amarilla', NULL, 2),
(1090, 219, 7, 5, 'amarilla', NULL, 2),
(1091, 219, 722, 5, 'amarilla', NULL, 2),
(1092, 219, 12, 5, 'amarilla', NULL, 2),
(1093, 219, 13, 5, 'amarilla', NULL, 2),
(1094, 219, 8, 5, 'amarilla', NULL, 2),
(1095, 219, 78, 6, 'amarilla', NULL, 2),
(1096, 219, 75, 6, 'amarilla', NULL, 2),
(1097, 219, 76, 6, 'roja', NULL, 2),
(1098, 219, 71, 6, 'amarilla', NULL, 2),
(1099, 217, 233, 13, 'gol', NULL, 2),
(1100, 217, 238, 13, 'gol', NULL, 2),
(1101, 217, 233, 13, 'gol', NULL, 2),
(1102, 217, 37, 7, 'amarilla', NULL, 2),
(1103, 217, 23, 7, 'amarilla', NULL, 2),
(1104, 217, 226, 13, 'amarilla', NULL, 2),
(1105, 217, 228, 13, 'amarilla', NULL, 2),
(1106, 217, 230, 13, 'roja', NULL, 2),
(1230, 257, 200, 8, 'gol', NULL, 2),
(1231, 257, 198, 8, 'gol', NULL, 2),
(1232, 257, 128, 11, 'gol', NULL, 2),
(1233, 257, 128, 11, 'gol', NULL, 2),
(1234, 253, 74, 6, 'gol', NULL, 2),
(1235, 253, 79, 6, 'gol', NULL, 2),
(1236, 253, 181, 2, 'gol', NULL, 2),
(1237, 253, 75, 6, 'gol', NULL, 2),
(1238, 253, 79, 6, 'gol', NULL, 2),
(1239, 253, 177, 2, 'amarilla', NULL, 2),
(1240, 253, 181, 2, 'amarilla', NULL, 2),
(1241, 253, 182, 2, 'amarilla', NULL, 2),
(1242, 253, 183, 2, 'amarilla', NULL, 2),
(1243, 251, 733, 1, 'gol', NULL, 2),
(1244, 251, 733, 1, 'gol', NULL, 2),
(1245, 251, 729, 1, 'amarilla', NULL, 2),
(1246, 251, 144, 12, 'gol', NULL, 2),
(1247, 251, 144, 12, 'amarilla', NULL, 2),
(1248, 251, 145, 12, 'amarilla', NULL, 2),
(1249, 251, 142, 12, 'amarilla', NULL, 2),
(1250, 251, 734, 1, 'gol', NULL, 2),
(1251, 251, 152, 12, 'amarilla', NULL, 2),
(1256, 250, 102, 14, 'gol', NULL, 2),
(1257, 250, 95, 14, 'gol', NULL, 2),
(1258, 250, 103, 14, 'gol', NULL, 2),
(1259, 250, 22, 7, 'amarilla', NULL, 2),
(1260, 250, 758, 7, 'amarilla', NULL, 2),
(1261, 254, 57, 9, 'gol', NULL, 2),
(1262, 254, 64, 9, 'gol', NULL, 2),
(1263, 254, 756, 9, 'gol', NULL, 2),
(1264, 254, 64, 9, 'amarilla', NULL, 2),
(1265, 254, 197, 8, 'amarilla', NULL, 2),
(1266, 254, 193, 8, 'amarilla', NULL, 2),
(1267, 255, 43, 4, 'gol', NULL, 2),
(1268, 255, 46, 4, 'gol', NULL, 2),
(1269, 255, 114, 43, 'gol', NULL, 2),
(1270, 255, 48, 4, 'amarilla', NULL, 2),
(1271, 255, 39, 4, 'roja', NULL, 2),
(1272, 255, 107, 43, 'amarilla', NULL, 2),
(1273, 255, 110, 43, 'amarilla', NULL, 2),
(1274, 255, 120, 43, 'roja', NULL, 2),
(1275, 252, 162, 10, 'gol', NULL, 2),
(1276, 252, 164, 10, 'gol', NULL, 2),
(1277, 252, 232, 13, 'gol', NULL, 2),
(1278, 252, 757, 13, 'gol', NULL, 2),
(1279, 252, 235, 13, 'amarilla', NULL, 2),
(1280, 252, 162, 10, 'amarilla', NULL, 2),
(1281, 252, 164, 10, 'amarilla', NULL, 2),
(1289, 256, 224, 3, 'gol', NULL, 2),
(1290, 256, 743, 3, 'gol', NULL, 2),
(1291, 256, 210, 3, 'amarilla', NULL, 2),
(1292, 256, 212, 3, 'amarilla', NULL, 2),
(1293, 256, 224, 3, 'amarilla', NULL, 2),
(1294, 256, 743, 3, 'amarilla', NULL, 2),
(1295, 256, 7, 5, 'amarilla', NULL, 2),
(1296, 256, 759, 5, 'amarilla', NULL, 2),
(1348, 262, 233, 13, 'gol', NULL, 2),
(1349, 262, 241, 13, 'gol', NULL, 2),
(1353, 263, 761, 43, 'gol', NULL, 2),
(1354, 263, 74, 6, 'gol', NULL, 2),
(1355, 263, 75, 6, 'gol', NULL, 2),
(1362, 258, 91, 14, 'amarilla', NULL, 2),
(1363, 258, 159, 10, 'amarilla', NULL, 2),
(1364, 258, 92, 14, 'amarilla', NULL, 2),
(1365, 258, 96, 14, 'amarilla', NULL, 2),
(1366, 258, 161, 10, 'amarilla', NULL, 2),
(1367, 258, 94, 14, 'gol', NULL, 2),
(1368, 259, 12, 5, 'gol', NULL, 2),
(1369, 259, 729, 1, 'gol', NULL, 2),
(1370, 259, 731, 1, 'gol', NULL, 2),
(1371, 259, 731, 1, 'gol', NULL, 2),
(1372, 259, 755, 1, 'gol', NULL, 2),
(1373, 259, 9, 5, 'gol', NULL, 2),
(1374, 261, 743, 3, 'gol', NULL, 2),
(1375, 261, 176, 2, 'gol', NULL, 2),
(1376, 260, 36, 7, 'gol', NULL, 2),
(1377, 260, 760, 9, 'gol', NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `home_config`
--

CREATE TABLE `home_config` (
  `clave` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_string` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `home_config`
--

INSERT INTO `home_config` (`clave`, `valor_string`, `updated_at`) VALUES
('live_scores_override_ids', '258,259,260,261,262,263', '2026-05-24 18:33:17'),
('live_scores_widget_enabled', '0', '2026-05-24 20:53:30'),
('partido_proximo_override:all:apertura', '185', '2026-04-11 19:47:53'),
('partido_proximo_override:primera:apertura', '265', '2026-05-24 23:19:22'),
('partido_proximo_override:senior:apertura', '164', '2026-05-03 21:23:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posicion` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipo` int(11) DEFAULT NULL,
  `goles` int(11) NOT NULL DEFAULT '0',
  `amarillas` int(11) NOT NULL DEFAULT '0',
  `rojas` int(11) NOT NULL DEFAULT '0',
  `temporada_id` int(11) NOT NULL DEFAULT '2',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id`, `nombre`, `posicion`, `equipo`, `goles`, `amarillas`, `rojas`, `temporada_id`, `estado`) VALUES
(4, 'Angel Arboleda', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(5, 'Matias Sarasola', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(6, 'Juan Labín', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(7, 'Genaro Gertner', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(8, 'Benjamín Fernandez', 'MC', 5, 0, 0, 0, 2, 'activo'),
(9, 'Lucio Moggia', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(10, 'Sebastian Duckardt', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(11, 'Jeremias Risso', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(12, 'Angel Kowalsuk', 'DC', 5, 0, 0, 0, 2, 'activo'),
(13, 'Matías Rodi', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(14, 'Juan Diser', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(15, 'Cayetano Rivero', 'PT', 5, 0, 0, 0, 2, 'activo'),
(16, 'Francisco Alvarez', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(17, 'Fernando Gonzalez', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(18, 'Santos Paturlane', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(19, 'Facundo Herrera', 'PEND', 5, 0, 0, 0, 2, 'activo'),
(20, 'Agustín Marduel', 'PT', 7, 0, 0, 0, 2, 'activo'),
(21, 'Francisco Cotignola', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(22, 'Maximiliano Montes', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(23, 'Felipe Pescatori', 'CT', 7, 0, 0, 0, 2, 'activo'),
(24, 'Nicolas Alvarez', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(25, 'Mateo Ibañez', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(26, 'Tobias Garcia', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(27, 'David Ibañez', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(28, 'Lucas Cepeda', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(29, 'Nicolás Boquín', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(30, 'Federico Maya', 'MC', 7, 0, 0, 0, 2, 'activo'),
(31, 'Iñaky Vidaurre', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(32, 'Bruno Wilchez', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(33, 'Rodrigo Bermejo', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(34, 'Kevin Bornes', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(35, 'Cristian Cerna', 'EXT', 7, 0, 0, 0, 2, 'activo'),
(36, 'Ignacio Quiroga', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(37, 'Federico Perroud', 'PEND', 7, 0, 0, 0, 2, 'activo'),
(38, 'Jordan García', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(39, 'Enzo Avalos', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(40, 'Lucas Vela', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(41, 'Angel Lopez', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(42, 'Elias Caceres', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(43, 'Joaquín Lara', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(44, 'Miguel Martinez', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(45, 'Fabricio Candia', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(46, 'Tomás Rodríguez', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(47, 'Juan Sanchez', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(48, 'Tobias Calvo', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(49, 'Herberto Gonzalez', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(50, 'Axel Sica', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(51, 'Federico Prienza', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(52, 'Benicio Mascioli', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(53, 'Nicolas Silva', 'PEND', 4, 0, 0, 0, 2, 'activo'),
(54, 'Franco Barni', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(55, 'Juan Mendez', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(56, 'Jaime Correa', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(57, 'Santiago Gallardo', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(58, 'Matías Colagioia', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(59, 'Manuel Ducasse', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(60, 'Carlos Benito', 'DC', 9, 0, 0, 0, 2, 'activo'),
(61, 'Mariano Contreras', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(62, 'Santiago Espinola', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(63, 'Mariano Mansilla', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(64, 'Lucas Vides', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(65, 'Enzo Di Palma', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(66, 'Franco Anania', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(67, 'Agustín Caris', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(68, 'Gonzalo Prieto', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(69, 'Valentin Olaverria', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(70, 'Exequiel Reale', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(71, 'Bautista Álvarez', 'CT', 6, 0, 0, 0, 2, 'activo'),
(72, 'German Fernandez', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(73, 'Milton Suarez', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(74, 'Alejandro Silva', 'MCD', 6, 0, 0, 0, 2, 'activo'),
(75, 'Esteban Herrero', 'CT', 6, 0, 0, 0, 2, 'activo'),
(76, 'Ignacio Proetto', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(77, 'Dardo González', 'PEND', 6, 1, 0, 0, 2, 'activo'),
(78, 'Franco Calabaza', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(79, 'Jonatan Silva', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(80, 'Sandro Saucedo', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(81, 'Ignacio Taborda', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(82, 'Braian Rodríguez', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(83, 'Axel Mac', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(84, 'Emiliano Mang', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(85, 'Ignacio Castillo', 'PEND', 6, 0, 0, 0, 2, 'activo'),
(86, 'Federico Ghinzani', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(87, 'Braian Álvarez', 'CT', 14, 0, 0, 0, 2, 'activo'),
(88, 'Richard Soto', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(89, 'Lautaro Souza', 'PEND', 14, 1, 0, 0, 2, 'activo'),
(90, 'Nahuel Flores', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(91, 'Adrián Gómez', 'CT', 14, 0, 0, 0, 2, 'activo'),
(92, 'Rocco Monteventi', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(93, 'Geremías Domínguez', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(94, 'Gerónimo Culasso', 'PEND', 14, 1, 0, 0, 2, 'activo'),
(95, 'Daniel Flores', 'SD', 14, 0, 0, 0, 2, 'activo'),
(96, 'Francisco Calabaza', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(97, 'Federico Correa', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(98, 'Federico Pérez', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(99, 'Federico Rosales', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(100, 'Franco Calabaza', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(101, 'Agustín Briozzo', 'LAT', 14, 0, 0, 0, 2, 'activo'),
(102, 'Damián Olivares', 'DC', 14, 0, 0, 0, 2, 'activo'),
(103, 'Agustín Correa', 'PEND', 14, 0, 0, 0, 2, 'activo'),
(104, 'Joel Luquez', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(105, 'Matías Horbal', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(106, 'Julian Bertolotti', 'PEND', 43, 1, 0, 0, 2, 'activo'),
(107, 'Marcos Ponce', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(108, 'Julian Sosa', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(109, 'Alejandro Aquino', 'CT', 43, 0, 0, 0, 2, 'activo'),
(110, 'Ulises Sandoval', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(111, 'Matias Diego', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(112, 'Julian Aul', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(113, 'Leo Argañin', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(114, 'Martin Romero', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(115, 'Facundo Gonzalez', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(116, 'Carlos Villarreta', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(117, 'Julian Gonzalez', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(118, 'Cristian Lucero', 'MCD', 43, 0, 0, 0, 2, 'activo'),
(119, 'Fermín Marañon', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(120, 'Tomaa Sanchez', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(121, 'Matías Calvos', 'PEND', 43, 0, 0, 0, 2, 'activo'),
(122, 'Juan Argañin', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(123, 'Franco Puente', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(124, 'Lautaro Sanchez', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(125, 'Juan Galván ', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(126, 'Boris Gramajo', 'MCD', 11, 0, 0, 0, 2, 'activo'),
(127, 'Agustin Barrera', 'CT', 11, 0, 0, 0, 2, 'activo'),
(128, 'Claudio Ometto', 'INT', 11, 0, 0, 0, 2, 'activo'),
(129, 'Gastón Cerjenko', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(130, 'Marcelo Soto', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(131, 'Martín Di Paula', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(132, 'Maximiliano Barros', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(133, 'Sebastian Gonzalez', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(134, 'Fermín Puente', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(135, 'Vicente Marroquín', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(136, 'Maximo Romero', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(137, 'Matías Bellegia', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(138, 'Jeremias Barrientos', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(139, 'Mateo Ometto', 'PEND', 11, 0, 0, 0, 2, 'activo'),
(140, 'Gabriel Medina', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(141, 'Alexis Santander', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(142, 'Daniel Córdoba ', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(143, 'Luciano Martino', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(144, 'Braian Luna', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(145, 'Elías Petti', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(146, 'Paulo Huerta', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(147, 'Ciro Méndez', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(148, 'Gaspar Aguirre', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(149, 'Joaquín Galeano', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(150, 'Gabriel Digiacomo', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(151, 'Agustin Calvo', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(152, 'Yenien Serra', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(153, 'Jorge Coña', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(154, 'Sebastian Palomeque', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(155, 'Christian Gonzalez', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(156, 'Lucas Troya', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(157, 'Sebastián Domínguez', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(158, 'Ignacio Peñas', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(159, 'Kevin Caicedo', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(160, 'Juan Cuevas', 'MCD', 10, 0, 0, 0, 2, 'activo'),
(161, 'Luciano Ramos', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(162, 'Cesar Medina', 'EXT', 10, 0, 0, 0, 2, 'activo'),
(163, 'Enzo Candia', 'LAT', 10, 0, 0, 0, 2, 'activo'),
(164, 'Nicolás Ibañez', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(165, 'Hector Cardozo', 'MP', 10, 0, 0, 0, 2, 'activo'),
(166, 'Tobias Barrios', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(167, 'Sebastián Illesca', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(168, 'Ignacio Siri', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(169, 'Donato Herrero', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(170, 'Juan Corral', 'EXT', 10, 0, 0, 0, 2, 'activo'),
(171, 'Alejo Palacios', 'DC', 10, 0, 0, 0, 2, 'activo'),
(172, 'Sergio Aranda', 'PEND', 10, 0, 0, 0, 2, 'activo'),
(173, 'Emanuel Paterlini', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(174, 'Jeremías Ávila', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(175, 'Alan Farias', 'PEND', 2, 2, 0, 0, 2, 'activo'),
(176, 'Gonzalo Martín', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(177, 'Braian Semino', 'MCD', 2, 0, 0, 0, 2, 'activo'),
(178, 'Braian Changazzo', 'LAT', 2, 0, 0, 0, 2, 'activo'),
(179, 'Joaquín Zárate', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(180, 'Diego Ramírez', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(181, 'Uriel Tomasello', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(182, 'Cruz Zárate', 'SD', 2, 0, 0, 0, 2, 'activo'),
(183, 'Agustin Barrio', 'EXT', 2, 4, 0, 0, 2, 'activo'),
(184, 'Julian Carnero', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(185, 'Pedro Toledo', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(186, 'Facundo Toribio', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(187, 'Carlos Villagra', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(188, 'Leonel Burrieza', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(189, 'Matías Del Arco', 'PEND', 2, 0, 0, 0, 2, 'activo'),
(190, 'Enzo Pelosi', 'DC', 2, 0, 0, 0, 2, 'activo'),
(191, 'Matias Ibañez', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(192, 'Joaquín Navarro', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(193, 'Valentín Aro', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(194, 'Elias Arenas', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(195, 'Maximo Gonzalez', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(196, 'Joaquín Lopez', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(197, 'Matías Hoyos', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(198, 'José Sardon', 'SD', 8, 0, 0, 0, 2, 'activo'),
(199, 'Michael Martín', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(200, 'Thiago Fraile', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(201, 'Joaquín Borregon', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(202, 'Joaquín Guerrero', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(203, 'Julian Toscano', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(204, 'Juan Enrriquez', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(205, 'Simon Arancivia', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(206, 'Roman Gúzman', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(207, 'Manuel Moralejo', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(208, 'Alexis Fraile', 'DC', 8, 0, 0, 0, 2, 'activo'),
(209, 'Agustín Gómez', 'PT', 3, 0, 0, 0, 2, 'activo'),
(210, 'Agustín Duarte', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(211, 'Juan Feloy', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(212, 'Felipe Borniego', 'CT', 3, 0, 0, 0, 2, 'activo'),
(213, 'Francisco Ramírez', 'MCD', 3, 0, 0, 0, 2, 'activo'),
(214, 'Mariano Leiva', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(215, 'Uriel Martinez', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(216, 'Juan Achaval', 'PEND', 3, 1, 0, 0, 2, 'activo'),
(217, 'Diego Achaval', 'DC', 3, 0, 0, 0, 2, 'activo'),
(218, 'Michel Fernandez', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(219, 'Yaco Leiva', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(220, 'Felipe Cepeda', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(221, 'Julian Torres', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(222, 'Gonzalo Achaval', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(223, 'Santiago Satarain', 'PEND', 3, 0, 0, 0, 2, 'activo'),
(224, 'Alejandro Gallego', 'DC', 3, 0, 0, 0, 2, 'activo'),
(225, 'Antonio Coña', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(226, 'Joel Roman', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(227, 'Kevin Toribio', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(228, 'Julian Coña', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(229, 'Ezequiel Ibañez', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(230, 'Ignacio Martins', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(231, 'Joaquín Barrio', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(232, 'Diego Gomez', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(233, 'Nahuel Sandalich', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(234, 'Joan Benavidez', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(235, 'Benjamin Guiyelo', 'PEND', 13, 1, 0, 0, 2, 'activo'),
(236, 'Tomas Bravo', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(237, 'Cristian Miranda', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(238, 'Agustín Coña ', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(239, 'Juan Esteban', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(240, 'Ricardo Del Río', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(241, 'Martín Root', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(242, 'Tiago Rodriguez', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(721, 'Suarez Leonel', 'LAT', 8, 0, 0, 0, 2, 'activo'),
(722, 'Suarez Leonel', 'MCD', 5, 0, 0, 0, 2, 'activo'),
(723, 'Maximiliano Rios', 'DC', 43, 0, 0, 0, 2, 'activo'),
(724, 'Fabricio Cerutti', 'PT', 1, 0, 0, 0, 2, 'activo'),
(725, 'Julian Cocimano', 'CT', 1, 0, 0, 0, 2, 'activo'),
(726, 'Emanuel Klundt', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(727, 'Luciano Duarte', 'LAT', 1, 0, 0, 0, 2, 'activo'),
(728, 'Lucas Avellaneda', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(729, 'Julian Ressia', 'CT', 1, 0, 0, 0, 2, 'activo'),
(730, 'Joel Carrizo', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(731, 'Tomas Altamiranda', 'EXT', 1, 0, 0, 0, 2, 'activo'),
(732, 'Lucas Rivas', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(733, 'Enzo Alonso', 'SD', 1, 0, 0, 0, 2, 'activo'),
(734, 'Robert Campaz', 'DC', 1, 0, 0, 0, 2, 'activo'),
(735, 'Enzo Pellegrini', 'PT', 1, 0, 0, 0, 2, 'activo'),
(736, 'Benjamin Vega', 'LAT', 1, 0, 0, 0, 2, 'activo'),
(737, 'Justino Gomez', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(738, 'Benjamin Natali', 'EXT', 1, 0, 0, 0, 2, 'activo'),
(739, 'Luis Duche', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(740, 'Nahuel Mercadin', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(741, 'Maximiliano Seminth', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(742, 'Denis Ramírez', 'MC', 3, 0, 0, 0, 2, 'activo'),
(743, 'Román Aispuru', 'EXT', 3, 0, 0, 0, 2, 'activo'),
(744, 'Alexis Campestrini', 'EXT', 9, 0, 0, 0, 2, 'activo'),
(745, 'Genaro Galeano', 'CRR', 12, 0, 0, 0, 2, 'activo'),
(746, 'Francisco Fauris', 'PT', 2, 0, 0, 0, 2, 'activo'),
(747, 'Benjamín Correa', 'CT', 8, 0, 0, 0, 2, 'activo'),
(748, 'Agustin Gallardo', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(749, 'Alejandro Digiacom', 'PEND', 12, 0, 0, 0, 2, 'activo'),
(750, 'Mauricio Pallero', 'MCD', 12, 0, 0, 0, 2, 'activo'),
(751, 'Matias Alvarez', 'PEND', 8, 0, 0, 0, 2, 'activo'),
(752, 'Julian Campestrini', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(753, 'Gaston Molina', 'PEND', 1, 0, 0, 0, 2, 'activo'),
(754, 'Kevin Benvenuto', 'LAT', 8, 0, 0, 0, 2, 'activo'),
(755, 'Roman Schap', 'DC', 1, 0, 0, 0, 2, 'activo'),
(756, 'Germán Silva', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(757, 'Facundo Coña', 'PEND', 13, 0, 0, 0, 2, 'activo'),
(758, 'Maximiliano Ernst', 'CT', 7, 0, 0, 0, 2, 'activo'),
(759, 'Leandro Alvarez', 'MC', 5, 0, 0, 0, 2, 'activo'),
(760, 'Marcelo Mancilla', 'PEND', 9, 0, 0, 0, 2, 'activo'),
(761, 'Jeremias Ybalo', 'PEND', 43, 0, 0, 0, 2, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legacy_goleadores_rescatados`
--

CREATE TABLE `legacy_goleadores_rescatados` (
  `id` int(11) NOT NULL,
  `jugador_nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipo_nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipo_raw` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `goles` int(11) NOT NULL DEFAULT '0',
  `aliases_json` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `legacy_goleadores_rescatados`
--

INSERT INTO `legacy_goleadores_rescatados` (`id`, `jugador_nombre`, `equipo_nombre`, `equipo_raw`, `goles`, `aliases_json`, `created_at`) VALUES
(1, 'Agustin Barrio', 'Unión Deportiva', 'Union Deportiva - Tres Lomas', 4, '[\"Barrio Agustin\"]', '2026-04-09 19:04:45'),
(2, 'Cesar Medina', 'Jorge Newbery', 'Jorge Newbery', 3, '[]', '2026-04-09 19:04:45'),
(3, 'Tomas Rodriguez', 'Deportivo Garré', 'Deportivo Garre', 3, '[]', '2026-04-09 19:04:45'),
(4, 'Lucas Vides', 'Salazar FC', 'Salazar FC', 3, '[]', '2026-04-09 19:04:45'),
(5, 'Joan Benavidez', 'Deportivo 17', 'Deportivo 17', 2, '[]', '2026-04-09 19:04:45'),
(6, 'Alana Farias', 'Unión Deportiva', 'Union Deportiva - Tres Lomas', 2, '[\"Farias Alan\"]', '2026-04-09 19:04:45'),
(7, 'Uriel Tomasello', 'Unión Deportiva', 'Union Deportiva - Tres Lomas', 2, '[]', '2026-04-09 19:04:45'),
(8, 'Cruz Zarate', 'Unión Deportiva', 'Union Deportiva - Tres Lomas', 2, '[]', '2026-04-09 19:04:45'),
(9, 'Lucas Rivas', 'Cecil A. Roberts', 'Roberts', 2, '[]', '2026-04-09 19:04:45'),
(10, 'Maico Saucedo', 'Deportivo Argentino', 'Deportivo Argentino', 2, '[]', '2026-04-09 19:04:45'),
(11, 'Tobias Calvo', 'Deportivo Garré', 'Deportivo Garre', 2, '[]', '2026-04-09 19:04:45'),
(12, 'Benjamin Guiyello', 'Deportivo 17', 'Deportivo 17', 1, '[\"Guiyelo Benjamin\"]', '2026-04-09 19:04:45'),
(13, 'Nahuel Sandalich', 'Deportivo 17', 'Deportivo 17', 1, '[]', '2026-04-09 19:04:45'),
(14, 'Dardo Gonzales', 'Deportivo Argentino', 'Deportivo Argentino', 1, '[\"González Dardo\",\"Gonzalez Dardo\"]', '2026-04-09 19:04:45'),
(15, 'Robert Campaz', 'Cecil A. Roberts', 'Roberts', 1, '[]', '2026-04-09 19:04:45'),
(16, 'Tomas Altamiranda', 'Cecil A. Roberts', 'Roberts', 1, '[]', '2026-04-09 19:04:45'),
(17, 'Diego Achaval', 'Unión de Bonifacio', 'Union Deportiva - Bonifacio', 1, '[]', '2026-04-09 19:04:45'),
(18, 'Matias Rodi', 'Deportivo Maza', 'Deportivo Maza', 1, '[]', '2026-04-09 19:04:45'),
(19, 'Matias Hoyos', 'Atlético Argentino', 'Atletico Argentino', 1, '[]', '2026-04-09 19:04:45'),
(20, 'Daniel Flores', 'Juventud Unida', 'Juventud Unida', 1, '[]', '2026-04-09 19:04:45'),
(21, 'Rocco Monteventi', 'Juventud Unida', 'Juventud Unida', 1, '[]', '2026-04-09 19:04:45'),
(22, 'Lucas Cepeda', 'Atlético Quenumá', 'Atletico Quenuma', 1, '[]', '2026-04-09 19:04:45'),
(23, 'Tobias Garcia', 'Atlético Quenumá', 'Atletico Quenuma', 1, '[]', '2026-04-09 19:04:45'),
(24, 'Julian Sosa', 'Villa del Parque', 'Villa del Parque', 1, '[]', '2026-04-09 19:04:45'),
(25, 'Claudio Ometto', 'El Ceibo', 'El Ceibo', 1, '[]', '2026-04-09 19:04:45'),
(26, 'Juan Shaffer', 'Unión de Bonifacio', 'Union Deportiva - Bonifacio', 1, '[\"Achaval Juan\"]', '2026-04-09 19:04:45'),
(27, 'Benicio Mascioli', 'Deportivo Garré', 'Deportivo Garre', 1, '[]', '2026-04-09 19:04:45'),
(28, 'Marcelo Mansilla', 'Salazar FC', 'Salazar FC', 1, '[]', '2026-04-09 19:04:45'),
(29, 'Hector Cardozo', 'Jorge Newbery', 'Jorge Newbery', 1, '[]', '2026-04-09 19:04:45'),
(30, 'Ignacio Quiroga', 'Atlético Quenumá', 'Atletico Quenuma', 1, '[]', '2026-04-09 19:04:45'),
(31, 'Leo Aguirre', 'La Gloria', 'La Gloria', 1, '[]', '2026-04-09 19:04:45'),
(32, 'Maximo Romero', 'El Ceibo', 'El Ceibo', 1, '[]', '2026-04-09 19:04:45'),
(33, 'Nicolas Ibanez', 'Jorge Newbery', 'Jorge Newbery', 1, '[]', '2026-04-09 19:04:45'),
(34, 'Sebastian Duckardt', 'Deportivo Maza', 'Deportivo Maza', 1, '[]', '2026-04-09 19:04:45'),
(35, 'Lucio Moggia', 'Deportivo Maza', 'Deportivo Maza', 1, '[]', '2026-04-09 19:04:45'),
(36, 'Matias Horbal', 'Villa del Parque', 'Villa del Parque', 1, '[]', '2026-04-09 19:04:45'),
(37, 'Julian Bertoloti', 'Villa del Parque', 'Villa del Parque', 1, '[\"Bertolotti Julian\"]', '2026-04-09 19:04:45'),
(38, 'Julian Cocimano', 'Cecil A. Roberts', 'Roberts', 1, '[]', '2026-04-09 19:04:45'),
(39, 'Carlos Benito', 'Salazar FC', 'Salazar FC', 1, '[]', '2026-04-09 19:04:45'),
(40, 'Lautaro Sousa', 'Juventud Unida', 'Juventud Unida', 1, '[\"Souza Lautaro\"]', '2026-04-09 19:04:45'),
(41, 'Geronimo Culaso', 'Juventud Unida', 'Juventud Unida', 1, '[\"Culasso Gerónimo\",\"Culasso Geronimo\"]', '2026-04-09 19:04:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `volanta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copete` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen_alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `autor_nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Redacción Juego de Equipo',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `destacada` tinyint(1) NOT NULL DEFAULT '0',
  `tipo_contenido` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comun',
  `boletin_pdf_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `galeria_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `orden_home` int(11) NOT NULL DEFAULT '999',
  `temporada_id` int(11) NOT NULL DEFAULT '2',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `slug`, `titulo`, `volanta`, `copete`, `contenido`, `imagen_url`, `imagen_alt`, `tag`, `categoria`, `autor_nombre`, `estado`, `destacada`, `tipo_contenido`, `boletin_pdf_url`, `galeria_json`, `orden_home`, `temporada_id`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'apertura-2025-26-comenzo-con-triunfo-de-cecil-roberts', 'Apertura 2025/26: Cecil A. Roberts abrió la campaña con una victoria clave', NULL, 'El arranque del torneo dejó un primer golpe de autoridad y marcó el tono competitivo de la temporada activa.', '<p>Cecil A. Roberts abrió la temporada con una actuación firme y un triunfo que ya lo posiciona entre los equipos a seguir.</p>', '/uploads/2026-04/d4734c66-5ec9-4578-bae4-a54243da599b.jpg', 'Inicio de la temporada Apertura 2025/26', 'Apertura', 'Crónica', 'Gonzalo Gonzalez', 'publicada', 1, 'comun', NULL, NULL, 1, 2, '2026-04-02 09:00:00', '2026-04-09 18:30:58', '2026-04-10 21:50:36', '2026-04-10 18:50:36'),
(2, 'mercado-de-planteles-la-liga-empieza-a-mirar-el-recambio', 'Mercado de planteles: la liga empieza a mirar el recambio y el valor del histórico', NULL, 'El nuevo ciclo obliga a distinguir entre protagonistas actuales y jugadores que ya forman parte del patrimonio estadístico del torneo.', '<p>La profesionalización del registro deportivo permite seguir a los futbolistas activos, detectar retiros y conservar trayectorias sin perder la historia de cada campaña.</p>', '/assets/logo.png', 'Jugadores y recambio generacional en la liga', 'Planteles', 'Análisis', 'Equipo editorial JDE', 'publicada', 1, 'comun', NULL, NULL, 2, 2, '2026-04-03 10:30:00', '2026-04-09 18:30:58', '2026-04-10 21:50:33', '2026-04-10 18:50:33'),
(3, 'semanario-digital-una-nueva-portada-para-la-liga', 'Semanario digital: una nueva portada para contar la liga con otra jerarquía', NULL, 'El sitio incorpora una narrativa más periodística para combinar resultados, contexto y contenido destacado en una misma experiencia.', '<p>La próxima etapa del producto va a integrar noticias destacadas, notas relacionadas y temporadas históricas en la home.</p>', '/assets/logo.png', 'Nueva portada editorial de Juego de Equipo', 'Editorial', 'Institucional', 'Dirección Juego de Equipo', 'publicada', 1, 'comun', NULL, NULL, 3, 2, '2026-04-04 08:15:00', '2026-04-09 18:30:58', '2026-04-10 21:50:31', '2026-04-10 18:50:31'),
(4, 'noticia', 'Triple liderazgo y goleadas en una Fecha 4 vibrante del Apertura', NULL, 'La cuarta fecha del Torneo Apertura 2026 de Primera División dejó una jornada cargada de goles, resultados ajustados y un liderazgo compartido en la tabla de posiciones, con tres equipos en lo más alto tras los encuentros disputados el domingo.', '<h3>Resultados de la Fecha 4</h3><p><span style=\"white-space-collapse: preserve;\">La jornada tuvo un partido postergado y seis encuentros que se resolvieron con marcadores intensos, varios de ellos definidos por la mínima diferencia.</span></p><ul><li><p><span style=\"white-space-collapse: preserve;\"><strong>Postergado:</strong>  \nVilla del Parque vs. Atlético Quenumá</span></p></li><li><p><span style=\"white-space-collapse: preserve;\"><strong>Domingo:</strong>  \nCecil A. Roberts 1 – 2 Juventud Unida\nUnión de Bonifacio 2 – 1 Salazar FC\nDeportivo Argentino 2 – 1 El Ceibo\nDeportivo Garré 2 – 1 Atlético Argentino\nUnión Deportiva 0 – 4 Jorge Newbery\nDeportivo Maza 4 – 2 La Gloria</span></p></li></ul><p><span style=\"white-space-collapse: preserve;\">Además, <strong>Deportivo 17</strong> tuvo fecha libre.</span></p><div></div><h3>📊 Tabla de posiciones: triple liderazgo</h3><p><span style=\"white-space-collapse: preserve;\">Disputadas cuatro fechas, el campeonato muestra una paridad marcada en la cima. <strong>Jorge Newbery</strong>, <strong>Unión Deportiva</strong> y <strong>Juventud Unida</strong> comparten el primer lugar con 9 puntos, aunque con distinto recorrido en cantidad de partidos jugados.</span></p><ul><li><p><span style=\"white-space-collapse: preserve;\"><strong>Jorge Newbery</strong> lidera por diferencia de gol (+8) y mantiene puntaje ideal en tres presentaciones, destacándose la goleada como visitante ante Unión Deportiva.</span></p></li><li><p><span style=\"white-space-collapse: preserve;\"><strong>Unión Deportiva</strong>, pese a la dura caída en la fecha, sigue en lo más alto tras haber disputado un partido más.</span></p></li><li><p><span style=\"white-space-collapse: preserve;\"><strong>Juventud Unida</strong> se afirma como protagonista tras su victoria fuera de casa ante Cecil A. Roberts.</span></p></li></ul><p><span style=\"white-space-collapse: preserve;\">Más atrás aparecen <strong>Deportivo Garré</strong> (7 puntos) y un grupo numeroso de equipos que se mantiene expectante en la mitad de la tabla, mientras que <strong>Atlético Argentino</strong> cierra las posiciones sin unidades.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\">La competencia avanza con un torneo abierto, donde cada fecha comienza a ser determinante y ningún equipo logra despegarse definitivamente. A la espera de la reprogramación del partido postergado, el Apertura 2026 promete seguir ofreciendo emociones fuertes en cada jornada.</span></p>', '/uploads/2026-04/e755bf1d-f881-4b90-802c-976af13c8610.jpg', '', 'Apertura 2026', 'Fútbol local', 'Emiliano Casanova', 'publicada', 1, 'comun', NULL, NULL, 1, 2, '2026-04-09 18:43:00', '2026-04-09 21:44:18', '2026-04-10 21:52:18', '2026-04-10 18:52:18');
INSERT INTO `noticias` (`id`, `slug`, `titulo`, `volanta`, `copete`, `contenido`, `imagen_url`, `imagen_alt`, `tag`, `categoria`, `autor_nombre`, `estado`, `destacada`, `tipo_contenido`, `boletin_pdf_url`, `galeria_json`, `orden_home`, `temporada_id`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'triple-liderazgo-y-goleadas-en-una-fecha-4-vibrante-del-apertura', 'Triple liderazgo y goleadas en una Fecha 4 vibrante del Apertura', 'Fecha 4', 'La cuarta fecha del Torneo Apertura 2026 de Primera División dejó una jornada cargada de goles, resultados ajustados y un liderazgo compartido en la tabla de posiciones, con tres equipos en lo más alto tras los encuentros disputados el domingo.', '<h3 style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\">Resultados de la Fecha 4</h3><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\">La jornada tuvo un partido postergado y seis encuentros que se resolvieron con marcadores intensos, varios de ellos definidos por la mínima diferencia.</span></p><ul style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><li style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Postergado:</span>  \nVilla del Parque vs. Atlético Quenumá</span></p></li><li style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Domingo:</span>  \nCecil A. Roberts 1 – 2 Juventud Unida\nUnión de Bonifacio 2 – 1 Salazar FC\nDeportivo Argentino 2 – 1 El Ceibo\nDeportivo Garré 2 – 1 Atlético Argentino\nUnión Deportiva 0 – 4 Jorge Newbery\nDeportivo Maza 4 – 2 La Gloria</span></p></li></ul><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\">Además, <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Deportivo 17</span> tuvo fecha libre.</span></p><div style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"></div><h3 style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\">📊 Tabla de posiciones: triple liderazgo</h3><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\">Disputadas cuatro fechas, el campeonato muestra una paridad marcada en la cima. <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Jorge Newbery</span>, <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Unión Deportiva</span> y <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Juventud Unida</span> comparten el primer lugar con 9 puntos, aunque con distinto recorrido en cantidad de partidos jugados.</span></p><ul style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><li style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Jorge Newbery</span> lidera por diferencia de gol (+8) y mantiene puntaje ideal en tres presentaciones, destacándose la goleada como visitante ante Unión Deportiva.</span></p></li><li style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Unión Deportiva</span>, pese a la dura caída en la fecha, sigue en lo más alto tras haber disputado un partido más.</span></p></li><li style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Juventud Unida</span> se afirma como protagonista tras su victoria fuera de casa ante Cecil A. Roberts.</span></p></li></ul><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\">Más atrás aparecen <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Deportivo Garré</span> (7 puntos) y un grupo numeroso de equipos que se mantiene expectante en la mitad de la tabla, mientras que <span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; font-weight: bolder;\">Atlético Argentino</span> cierra las posiciones sin unidades.</span></p><div style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"></div><p style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ;\"><span style=\"--tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; white-space-collapse: preserve;\">La competencia avanza con un torneo abierto, donde cada fecha comienza a ser determinante y ningún equipo logra despegarse definitivamente. A la espera de la reprogramación del partido postergado, el Apertura 2026 promete seguir ofreciendo emociones fuertes en cada jornada.</span></p>', '/uploads/2026-04/40754c11-48d5-467f-9482-2c29cdef9f17.jpg', 'PH: Benjamin Lorenzo', 'Apertura 2026', 'Fútbol local', 'Emiliano Casanova', 'publicada', 1, 'comun', NULL, NULL, 999, 2, '2026-04-10 21:53:26', '2026-04-10 21:53:26', '2026-04-10 21:53:26', NULL);
INSERT INTO `noticias` (`id`, `slug`, `titulo`, `volanta`, `copete`, `contenido`, `imagen_url`, `imagen_alt`, `tag`, `categoria`, `autor_nombre`, `estado`, `destacada`, `tipo_contenido`, `boletin_pdf_url`, `galeria_json`, `orden_home`, `temporada_id`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'newbery-viaja-a-defender-su-liderazgo-roberts-union-y-juventud-no-quieren-perder-pisada-al-lider', 'Newbery viaja a defender su liderazgo. Roberts, Unión y Juventud no quieren perder pisada al líder.', 'La sexta del Apertura', 'El Apertura 2026 ofrece un fin de semana cargado de duelos clave en la pelea por los primeros puestos. Con un adelanto esta tarde y el resto de la fecha mañana, los equipos buscan afirmarse, recuperarse o no perderle pisada al líder en una jornada que puede empezar a marcar tendencias en la tabla.', '<h3>⚽ <strong>Apertura 2026 – La previa del fin de semana</strong></h3><p><span style=\"white-space: pre-wrap;\">El Apertura 2026 sigue su marcha con un adelanto esta tarde y el grueso de la actividad mañana domingo.</span></p><p><span style=\"white-space: pre-wrap;\"><strong>Hoy</strong>, en su casa, <strong>Deportivo 17</strong> intentará sumar tres puntos clave ante <strong>Salazar</strong> para acercarse a los puestos de arriba. La visita llega con la necesidad de recuperarse tras la caída frente a Roberts y buscará dar el golpe fuera de casa.</span></p><div></div><h3>🔴 <strong>Roberts vs El Ceibo – Transmisión de Juego de Equipo</strong></h3><p><span style=\"white-space: pre-wrap;\">La acción continúa <strong>mañana</strong> con un duelo atractivo en Salliqueló.\n<strong>Cecil A. Roberts</strong> quiere mantenerse firme en la pelea y volver a ganar como local después del traspié ante Juventud en la cuarta fecha.\n<strong>El Ceibo</strong>, que viene de una goleada convincente frente a Unión de Bonifacio, buscará sostener su envión y seguir escalando en la tabla.</span></p><div></div><h3>🟢 <strong>El líder viaja a Garré</strong></h3><p><span style=\"white-space: pre-wrap;\"><strong>Jorge Newbery</strong>, puntero con puntaje ideal, visita a <strong>Deportivo Garré</strong> en un partido que puede marcar tendencia.\nLos dirigidos por Moriamé tienen la oportunidad de descontar de manera directa y meterse de lleno en la conversación grande del campeonato.</span></p><div></div><h3>🔵 <strong>Duelo de aspirantes en 30 de Agosto</strong></h3><p><span style=\"white-space: pre-wrap;\">En 30 de Agosto, <strong>Deportivo Argentino</strong> recibe al campeón 2025. Ambos llegan con victorias en la fecha anterior y buscan no perder el tren de los de arriba en un choque que promete intensidad y puntos pesados.</span></p><div></div><h3>🟡 <strong>Unión TL busca aire; Maza quiere sostener la racha</strong></h3><p><span style=\"white-space: pre-wrap;\">En Tres Lomas, <strong>Unión</strong> necesita cortar la seguidilla negativa tras dos derrotas consecutivas.\nEnfrente estará <strong>Deportivo Maza</strong>, que llega con confianza luego de vencer a La Gloria y descansar en la quinta fecha. Un duelo clave para las aspiraciones de ambos.</span></p><div></div><h3>⚪ <strong>Bonifacio: duelo de urgencias</strong></h3><p><span style=\"white-space: pre-wrap;\">En Bonifacio se cruzan dos equipos necesitados.\n<strong>Unión</strong>, golpeado por la dura derrota ante El Ceibo, recibe a <strong>Atlético Argentino</strong>, que aún no sumó en el año. Por historia y peso propio, el conjunto de Tres Lomas necesita reaccionar, aunque incluso ganando seguirá en la 15ª posición.</span></p><div></div><h3>🟣 <strong>Villa del Parque vs La Gloria – Choque de mitad de tabla</strong></h3><p><span style=\"white-space: pre-wrap;\">En el Orsi de Casbas, <strong>Villa del Parque</strong> buscará recuperarse tras la caída ante Newbery.\n<strong>La Gloria</strong>, por su parte, llega con la motivación de haber vencido a Unión TL y quiere sostener la senda ganadora para seguir trepando.</span></p>', '/uploads/2026-04/8a297eb4-3ce7-4831-8159-e36e18c6e7e5.jpg', '', 'Apertura 2026', 'Fútbol local', 'Facundo Ugarte', 'publicada', 1, 'comun', NULL, NULL, 999, 2, '2026-04-18 16:30:03', '2026-04-18 16:30:04', '2026-04-18 16:30:04', NULL),
(7, 'sigue-solo-arriba', 'Sigue solo arriba', 'APERTURA FECHA 6', 'Newbery se trajo un punto para Salliqueló y sigue siendo el único líder del Apertura. A Roberts se le escapó en el final y no pudo descontar al puntero.', '<p><span style=\"white-space-collapse: preserve;\"><strong>Newbery viajó a Garré y rescató un punto</strong> que lo deja solo arriba del campeonato.\nGarré lo ganaba con gol de Mascioli en la primera parte y, en el complemento, Luciano Ramos estableció la igualdad.\nNewbery mantiene el liderazgo en soledad y continúa siendo el único invicto del año.\nPor su parte, Garré desperdició la chance de acortar distancias.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\"><strong>A Roberts se le escapó en el final.</strong>  \nLo ganaba 1–0 con gol de penal de Rivas y, sobre la hora, Maxi Romero silenció a los rojos.\nRoberts sigue a tres de Newbery, pero volvió a ceder puntos en casa.\nEl Ceibo continúa en la segunda mitad de la tabla con una sola victoria en el año.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\"><strong>Unión de Tres Lomas volvió a sonreír</strong> con la victoria ante Maza.\nLuego de dos derrotas al hilo, el pituco se hizo fuerte y, de la mano de Cruz Zárate, venció 2–1 y es único escolta.\nLos de Etchezar y Nennhuber siguen en la zona baja con dos victorias y tres derrotas.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\"><strong>Deportivo sube y se ilusiona.</strong>  \nLe ganó 2–1 al campeón 2025 con doblete de Jona Silva.\nEl descuento de Perroud no alcanzó y así Quenumá dejó pasar la oportunidad de descontar, cuando todavía debe su partido con Villa del Parque por la cuarta fecha.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\"><strong>La Gloria ganó y toma un poco de aire.</strong>  \nEn el Orsi de Casbas venció 1–0 a Villa con gol de Aguirre y salió de la zona baja.\nVilla, por su parte, cosechó su segunda derrota consecutiva.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\"><strong>En Bonifacio, los del fondo hicieron tablas.</strong>  \nAispuru abrió la cuenta para los de Arbe, pero enseguida empató Thiago Fraile para el tocinero.\nUnión y Argentino siguen 14° y 15° respectivamente.</span></p><div></div><p><span style=\"white-space-collapse: preserve;\">El sábado había comenzado la acción con el <strong>empate entre Deportivo 17 y Salazar</strong> en Tres Lomas.\nLo ganaba la visita con gol de Agustín Gallardo y lo empató en el complemento Joaquín Barrio.\nAmbos siguen deambulando por la zona media/baja del Apertura.</span></p>', '/uploads/2026-04/4b9ed902-725d-4697-bad4-80869cf96faf.jpg', '', 'Fecha 6', 'General', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 03:32:17', '2026-04-20 18:45:30', NULL),
(8, 'asdfadsfadsfadsfasdf', 'asdfadsfadsfadsfasdf', 'asdfadsfa', 'asdfasdf', 'asdfasdfasdfadf', '', 'sdfasdfasdf', 'sdfasdfa', 'asdfasd', 'fasdfa', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:08:23', '2026-04-20 18:42:36', '2026-04-20 15:42:36'),
(9, 'sdfasdfas', 'sdfasdfas', 'asfadsfa', 'asdfasdfasdf', 'asdfasdf', '/uploads/2026-04/b402ed21-27ab-450e-be66-d11dd362a6b7.png', 'dfasdfasdfasdf', 'dfasdfasd', 'sdfas', 'dfasdfa', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:09:21', '2026-04-20 18:43:08', '2026-04-20 15:43:08'),
(10, 'sdfasdfa', 'SDFASDFA', 'ASDFASDFASDF', 'DFASDFASASDF', 'ASDFASDF', '', 'FASDF', 'ASDFASDDF', 'ASDFA', 'ADAFSDF', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:23:12', '2026-04-20 18:43:07', '2026-04-20 15:43:07'),
(11, 'asdfasdfasdfasdfasdf', 'ASDFASDFASDFASDFASDF', NULL, 'ASDFASDFASDFASDFASDFASDF', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:25:22', '2026-04-20 18:43:05', '2026-04-20 15:43:05'),
(12, 'asdfasdfa', 'asdfasdfa', 'zsdfasdf', '', '', '', 'asdfasdf', 'sdfasdf', 'fa', 'asdfasd', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:33:16', '2026-04-20 18:43:04', '2026-04-20 15:43:04'),
(13, 'dasdfasdfadsfa', 'dasdfasdfadsfa', 'sddd', '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:32', '2026-04-20 18:43:03', '2026-04-20 15:43:03'),
(14, 'borrador-69e6672f406f5', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:35', '2026-04-20 18:42:59', '2026-04-20 15:42:59'),
(15, 'borrador-69e6672fa7ac9', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:35', '2026-04-20 18:43:01', '2026-04-20 15:43:01'),
(16, 'borrador-69e6672fddc14', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:35', '2026-04-20 18:43:02', '2026-04-20 15:43:02'),
(17, 'borrador-69e6673000f86', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:36', '2026-04-20 18:42:54', '2026-04-20 15:42:54'),
(18, 'borrador-69e6673029d15', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:36', '2026-04-20 18:42:55', '2026-04-20 15:42:55'),
(19, 'borrador-69e667304ee1c', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:36', '2026-04-20 18:42:56', '2026-04-20 15:42:56'),
(20, 'borrador-69e6673076924', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:36', '2026-04-20 18:42:57', '2026-04-20 15:42:57'),
(21, 'borrador-69e667336a906', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:39', '2026-04-20 18:42:52', '2026-04-20 15:42:52'),
(22, 'borrador-69e667339b86e', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 17:49:39', '2026-04-20 18:42:53', '2026-04-20 15:42:53'),
(25, 'fasdfasdf', 'fasdfasdf', 'asdfad', 'asdfasdfasdfasdfasd', '', '', 'dfasdfasdfa', 'asdfas', 'asdfasd', 'fasdfasdf', 'borrador', 0, 'comun', NULL, NULL, 999, 2, NULL, '2026-04-20 18:25:57', '2026-04-20 18:42:50', '2026-04-20 15:42:50'),
(26, 'triple-liderazgo-y-goleadas-en-una-fecha-4-vibrante-del-aperturaasdfasdfasdfasdasdfa', 'Triple liderazgo y goleadas en una Fecha 4 vibrante del Aperturaasdfasdfasdfasdasdfa', 'La sexta del Apertura', 'asdfasdf', 'asdfasdfasdf', '', 'asdfasdfas', 'adfasd', 'sdf', 'fasdfasdfa', 'publicada', 0, 'comun', NULL, NULL, 999, 2, '2026-04-20 18:26:23', '2026-04-20 18:26:23', '2026-04-20 18:26:27', '2026-04-20 15:26:27'),
(29, 'resultados-posiciones-goleadores-y-sanciones-del-fin-de-semana', 'Resultados, posiciones, goleadores y sanciones del fin de semana', 'Boletín Oficial Nº 013', 'La Liga Cultural y Deportiva publicó el Boletín Oficial Nº 013 con todos los resultados de las jornadas del 11 y 12 de abril, las tablas actualizadas de cada división, el listado de goleadores y las resoluciones del Tribunal de Penas. Jorge Newbery lidera en Primera con puntaje ideal, mientras que las divisiones formativas y Senior también dejaron movimientos importantes en la clasificación.', '<p><span style=\"white-space-collapse: preserve;\">La Liga Cultural y Deportiva dio a conocer el <strong>Boletín Oficial Nº 013</strong>, correspondiente a los encuentros disputados los días <strong>11 y 12 de abril de 2026</strong>, donde se detallan los resultados de todas las categorías, las tablas de posiciones actualizadas, los goleadores del certamen y las sanciones aplicadas por el Tribunal de Penas.</span></p><h2><strong>Resultados del fin de semana</strong></h2><p><span style=\"white-space-collapse: preserve;\">En <strong>Primera División</strong>, La Gloria venció a Unión TL, Newbery goleó a Villa del Parque, Quenumá superó a Garré y Deportivo Argentino se impuso ante Atlético Argentino. El Ceibo logró una amplia victoria ante Unión LA, mientras que Roberts ganó en su visita a Salazar y Deportivo 17 se llevó los tres puntos frente a Juventud Unida.</span></p><p><span style=\"white-space-collapse: preserve;\">Las divisiones <strong>Tercera, Quinta, Sexta, Séptima, Octava y Novena</strong> también completaron su actividad con una gran cantidad de goles y partidos parejos, destacándose triunfos importantes de Unión TL en Tercera, Atlético Argentino en Quinta, Unión LA en Sexta, Roberts en Séptima, Atlético Argentino y Salazar en Octava, y Juventud Unida en Novena.</span></p><p><span style=\"white-space-collapse: preserve;\">En <strong>Senior</strong>, Unión TL venció a La Gloria, Villa del Parque derrotó a Newbery y Garré se impuso ante Ferro. Atlético Argentino, El Ceibo y Roberts también sumaron victorias en la categoría.</span></p><h2><strong>Tablas de posiciones</strong></h2><p><span style=\"white-space-collapse: preserve;\">El boletín incluye las tablas completas de cada división. En <strong>Primera</strong>, Jorge Newbery se mantiene como líder con puntaje ideal tras cuatro fechas, seguido por Roberts, Unión TL y Juventud Unida. Las categorías formativas muestran panoramas variados, con Atlético Argentino y Salazar dominando Octava, Juventud Unida firme en Novena y Roberts liderando Séptima.</span></p><p><span style=\"white-space-collapse: preserve;\">En <strong>Senior</strong>, Atlético Argentino encabeza la tabla, seguido de cerca por Roberts y El Ceibo.</span></p><h2><strong>Tabla de goleadores</strong></h2><p><span style=\"white-space-collapse: preserve;\">El documento también detalla a los máximos anotadores de cada división. En <strong>Primera</strong>, Lucas Rivas (Roberts) encabeza la tabla con 5 goles. En Tercera, Benjamín Pérez (Garré) lidera con 5 tantos, mientras que en Quinta, Santino Miner (Salazar) es el máximo artillero. Las categorías Sexta, Séptima, Octava, Novena y Senior también presentan sus respectivos goleadores destacados.</span></p><h2><strong>Tribunal de Penas</strong></h2><p><span style=\"white-space-collapse: preserve;\">El Boletín Nº 013 incluye el listado de jugadores amonestados, así como las sanciones aplicadas por el Tribunal de Penas. Se registran suspensiones provisorias, sanciones por artículos específicos del reglamento y expulsiones correspondientes a los encuentros del fin de semana.</span></p><div></div>', '/uploads/2026-04/9e6fb05d-75c6-48d8-942f-0369ff02d454.jpg', '', 'Boletín', 'Boletines Oficiales', 'Emiliano Casanova', 'archivada', 1, 'boletin', '/uploads/2026-04/1f719357-a1cb-47a5-8eaa-7e167fce22a1.pdf', '[]', 996, 2, '2026-04-20 19:12:59', '2026-04-20 19:10:58', '2026-04-22 02:31:38', '2026-04-21 23:31:38'),
(30, 'prueba-testtesttest', 'prueba testtesttest', 'test', 'testtesttesttest', '<figure class=\"gallery-item\"><img src=\"/uploads/2026-04/23658bb9-b18e-4d19-bd07-8d139742aab5.jpg\" alt=\"\" loading=\"lazy\" /></figure>\n<figure class=\"gallery-item\"><img src=\"/uploads/2026-04/2057d446-4243-44b0-bc86-cf210e2093f2.png\" alt=\"\" loading=\"lazy\" /></figure>\n<figure class=\"gallery-item\"><img src=\"/uploads/2026-04/e6b7fe46-2985-4f9c-90cd-21ecdfdc0fa4.png\" alt=\"\" loading=\"lazy\" /></figure>\n<figure class=\"gallery-item\"><img src=\"/uploads/2026-04/df9da4ad-1a59-4d6c-ab48-d315dd244cc4.png\" alt=\"\" loading=\"lazy\" /></figure>\n<figure class=\"gallery-item\"><img src=\"/uploads/2026-04/c8a0288d-b969-4902-850b-5ff5f7e5aebb.png\" alt=\"\" loading=\"lazy\" /></figure>', '', '', 'test', 'test', 'test', 'publicada', 0, 'galeria', NULL, '[{\"imagenUrl\":\"/uploads/2026-04/23658bb9-b18e-4d19-bd07-8d139742aab5.jpg\",\"epigrafe\":\"\"},{\"imagenUrl\":\"/uploads/2026-04/2057d446-4243-44b0-bc86-cf210e2093f2.png\",\"epigrafe\":\"\"},{\"imagenUrl\":\"/uploads/2026-04/e6b7fe46-2985-4f9c-90cd-21ecdfdc0fa4.png\",\"epigrafe\":\"\"},{\"imagenUrl\":\"/uploads/2026-04/df9da4ad-1a59-4d6c-ab48-d315dd244cc4.png\",\"epigrafe\":\"\"},{\"imagenUrl\":\"/uploads/2026-04/c8a0288d-b969-4902-850b-5ff5f7e5aebb.png\",\"epigrafe\":\"\"}]', 999, 2, '2026-04-20 19:26:25', '2026-04-20 19:26:25', '2026-04-20 19:26:52', '2026-04-20 16:26:52'),
(31, 'resultados-posiciones-sanciones-y-proxima-fecha-la-liga-publico-su-boletin-oficial-n-015', 'Resultados, posiciones, sanciones y próxima fecha: la Liga publicó su Boletín Oficial Nº 015', 'Boletín Oficial Nº 015', 'La Liga Cultural y Deportiva de Tres Lomas difundió el Boletín Nº 015 correspondiente a la actividad del fin de semana del 18 y 19 de abril. El documento incluye los resultados de todas las categorías, las tablas de posiciones actualizadas, el detalle del Tribunal de Penas y la programación oficial para la próxima jornada en Primera, Tercera, Senior e Inferiores.', '<p><span style=\"white-space: pre-wrap;\">La Liga Cultural y Deportiva de Tres Lomas dio a conocer el Boletín Oficial Nº 015, fechado el 21 de abril de 2026, con toda la información oficial de la jornada disputada los días 18 y 19 de abril en las distintas categorías del fútbol liguista.</span></p><div></div><p></p>', '/uploads/2026-04/e9b989e6-3728-4e1d-9ae3-64a5d2dccf4c.jpg', 'PH: YAMI ROSAS', 'Boletín Oficial', 'Fútbol local', 'Redacción Juego de Equipo', 'publicada', 1, 'boletin', '/uploads/2026-04/e5be5925-b3e6-4b06-a7c8-045da065c4af.pdf', '[]', 999, 2, '2026-04-22 02:17:13', '2026-04-22 02:17:12', '2026-04-22 02:31:47', NULL),
(32, 'domingo-con-siete-partidos', 'Domingo con siete partidos', 'APERTURA FECHA 7', 'El apasionante Apertura sigue adelante y mañana nos trae partidos que pueden resultar bisagras para el desarrollo del campeonato. Se destaca Newbery vs Deportivo Argentino, transmisión de Juego de Equipo.', '<p class=\"MsoNormal\">El líder del campeonato recibe en el Marduel a Deportivo\nArgentino que continúa en plena alza en el Apertura luego de comenzar perdiendo\nen las dos primeras fechas y quiere alcanzar al verde en lo más alto. Los de Cuevas\nson los únicos invictos y quieren alejarse de Unión de Tres Lomas que queda\nlibre en esta jornada. El partido será transmisión de Juego de Equipo con\nFacundo “Quifu” Ugarte, Mauricio López y Emiliano Casanova.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En el José Sardón de Tres Lomas, Roberts visita al Toci en\nbusca de la recuperación. Después de sumar un punto de los últimos seis jugados\nde local, necesita volver a la senda victoriosa fuera de casa. No contará con\nLucas Avellaneda, expulsado ante El Ceibo y debe cumplir dos fechas de suspensión.\nLa actualidad de Atlético Argentino es muy distinta. Mientras su rival intenta\ndiscutir con Newbery, Unión y Deportivo, los dirigidos por Lucas Rimoldi no\nconocen la victoria en este 2026 y vienen de sumar la primera unidad en\nBonifacio. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Atlético Quenumá recibe en el Castañeira a Unión de Bonifacio\nque quiere salir de la zona baja. Los campeones 2025, que adeudan el partido de\nla cuarta fecha con Villa del Parque, se encuentran en la mitad de la tabla de posiciones\ny deben escalar para volver a ser el equipo del Apertura y el triangular del año\npasado. Los de Arbe, por su lado, no ganan desde la cuarta fecha ante Salazar (única\nvictoria del año), y no contará con Francisco Ramírez, expulsado ante Atlético\nArgentino y deberá cumplir en la séptima.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En Casbas, El Ceibo recibe a Deportivo 17 en el Dr. Pippo.\nCon la baja de Lautaro Sánchez, expulsado ante Roberts (vuelve en la 10ma ante\nSalazar), buscará ganar otra vez en casa. Tiene una sola victoria en el\ncampeonato y fue en la 5ta ante Unión de Bonifacio. Los de Sepúlveda quieren meterse\nentre los ocho mejores. El Colono, que viene de empatar en casa ante Salazar,\nno gana desde la 5ta ante Juventud en 30 de Agosto. Los de Valle, quieren ganar\npara seguir subiendo en este arranque irregular.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En Salazar, el local y Juventud se ven las caras. Salazar\nque no tiene un buen campeonato, sólo una victoria en la 2da fecha en Garré,\nquiere sumar de a tres en casa ante un Juventud que quiere ser protagonista, pero\nque solo le sirve sumar de a tres, luego de caer como local ante Deportivo 17\nen la 5ta fecha y quedar libre en la sexta. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">La Gloria y Dep. Garré se ven las caras en el Raúl Changazzo.\nDe campañas similares, un punto de diferencia a favor de los dirigidos por\nMoriamé, los locales se quieren meter en zona de liguilla, y para eso no pueden\nceder puntos en casa, vienen de sumar dos victorias al hilo, Unión de Tres\nLomas (L) y Villa del Parque (V). Garré por su parte, si bien viene de igualar\ncon Newbery como local, sumó 5 de los últimos 12 puntos disputados. El poder de\ngol de Tomás Rodríguez no es suficiente para este equipo que podría estar más\narriba. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Maza y Villa del Parque es un partido casi de fondo de\ntabla. Maza no conoce le empate, y de local sólo ganó un partido, a La Gloria 4\na 2. Ganar de local es imprescindible, para un equipo con pocos refuerzos y que\nquiere subir en la tabla. Villa del Parque, sin Ríos y tratando de encontrar el\nmejor once posible por las lesiones, intentará volver a la victoria, situación que\nno se da desde el 2 a 0 en la 3ra fecha en la visita a Atlético Argentino. &nbsp;&nbsp;&nbsp;&nbsp;<o:p></o:p></p>', '/uploads/2026-04/b2b83dc8-25b2-44ca-8278-1832674844aa.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-04-25 05:37:14', '2026-04-25 05:37:16', '2026-04-25 05:40:03', NULL),
(33, 'borrador-69ec53120761e', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, '[]', 999, 2, NULL, '2026-04-25 05:37:22', '2026-04-25 05:37:22', NULL),
(34, 'newbery-sigue-firme-roberts-no-le-pierde-pisada', 'Newbery sigue firme. Roberts no le pierde pisada.', 'APERTURA FECHA 7', 'Newbery volvió a ganar en casa ante Deportivo con doblete de Medina. Roberts triunfó en Tres Lomas y sigue a tres puntos del verde. Juventud, Unión, Garré y Deportivo 17, un poquito más abajo.', '<div><p><span style=\"white-space: pre-wrap;\">Se fue la séptima del Apertura que confirma a Newbery como líder absoluto tras derrotar a Deportivo Argentino 2 a 0 con dos goles de César Medina (goleador del campeonato), uno en cada tiempo, manteniendo el invicto en el campeonato. En un partido que nunca terminó de armarse, Newbery, lejos de brillar, fue mejor que Deportivo, que extrañó a Jona Silva, lesionado el último viernes en la práctica.</span></p><p><span style=\"white-space: pre-wrap;\">En Tres Lomas, Roberts se trajo tres puntos de oro sobre el final del partido. Campaz fue el autor del único gol del encuentro tras un error del asistente, que confundió a muchos al levantar el banderín después de un saque desde el arco. Los defensas de Argentino se frenaron al ver el banderín levantado, pero el árbitro, atento al error, dio continuidad y Rivas terminó asistiendo a Campaz. En el complemento, Rivas había desperdiciado un penal que dio en el travesaño. Argentino sigue sin ganar, no suma en casa y continúa último en el campeonato.</span></p><p><span style=\"white-space: pre-wrap;\">Juventud no tuvo piedad de Salazar y le ganó 3 a 0 a domicilio. Luego de quedar libre, los de Benítez volvieron al ruedo y lo hicieron sumando de a tres, quedando a cuatro puntos de Newbery.</span></p><p><span style=\"white-space: pre-wrap;\">La goleada de la fecha se la propinó Garré a La Gloria en 30 de Agosto. Fue un 5 a 2 que deja a los de Morirme expectantes en este Apertura, atentos a lo que suceda en la zona alta de la tabla.</span></p><p><span style=\"white-space: pre-wrap;\">Deportivo 17 se volvió a casa con tres puntos claves de Casbas para seguir trepando en la tabla. Venció a El Ceibo y volvió a ganar de visitante, como hace dos fechas atrás. El Ceibo, con marcha irregular, sigue fuera de los ocho.</span></p><p><span style=\"white-space: pre-wrap;\">Atlético Quenumá ganó en casa ante Unión de Bonifacio y sostiene el ascenso en la tabla. El campeón, que no viene con la mejor campaña, ganó en su cancha y se acerca a los puestos de arriba con un partido pendiente. Los de Arbe no pueden salir de la zona baja del campeonato.</span></p><p></p></div>', '/uploads/2026-04/08017069-9d97-4330-ae37-cf3522dcef41.jpg', 'Benjamín Lorenzo', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-04-27 03:14:52', '2026-04-27 03:14:51', '2026-04-27 13:39:45', NULL),
(35, 'resultados-posiciones-sanciones-y-proxima-fecha-la-liga-publico-su-boletin-oficial-n-016', 'Resultados, posiciones, sanciones y próxima fecha: la Liga publicó su Boletín Oficial Nº 016', 'Boletín oficial N° 016', 'La Liga Cultural y Deportiva de Tres Lomas difundió el Boletín Nº 016 correspondiente a la actividad del fin de semana del 25 y 26 de abril. El documento incluye los resultados de todas las categorías, las tablas de posiciones actualizadas, el detalle del Tribunal de Penas y la programación oficial para la próxima jornada en Primera, Tercera, Senior e Inferiores.', 'La Liga Cultural y Deportiva de Tres Lomas dio a conocer el Boletín Oficial Nº 016, fechado el 28 de abril de 2026, con toda la información oficial de la jornada disputada los días 25 y 26 de abril en las distintas categorías del fútbol liguista.', '/uploads/2026-04/c94d877c-4e10-49a7-aebc-26b9cca95a92.jpg', 'PH Silvina Perez', 'Boletín Oficial', 'Fútbol Local', 'Redacción Juego de Equipo', 'publicada', 1, 'boletin', '/uploads/2026-04/7e707b7c-41f4-442f-bc3b-88f3e4ef8d77.pdf', '[]', 999, 2, '2026-04-29 01:39:50', '2026-04-29 01:39:49', '2026-04-29 01:39:49', NULL),
(36, 'sigue-el-apertura', 'Sigue el Apertura', 'APERFTURA FECHA 8', 'Con partidos que destacan, se juega la octava del Apertura que comienza este sábado y el domingo se completa con seis partidos. Roberts vs Atl. Quenuma, será el partido de Juego de Equipo.', '<p class=\"MsoNormal\">Mañana comienza a jugarse la octava, en 30 de Agosto,\nDeportivo y La Gloria abren una nueva fecha del Apertura. Ambos llegan de\nsendas derrotas, Deportivo 0 – 2 con Newbery y La Gloria 2 – 5 con Garré, y su\nla última victoria de ambos se dio en la sexta. Buscarán recuperarse para no\nalejarse del tren de arriba en el caso de Deportivo y La Gloria con el objetivo\nde meterse entre los ocho mejores.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">El domingo Newbery viaja a Bonifacio a defender el\nliderazgo. Si bien, Unión de Bonifacio se encuentra muy abajo en la tabla con\nuna sola victoria (2 a 1 a Salazar en la cuarta como local), está invicto en\nesa condición con otros dos empates. Viene de perder en Quenuma y buscará de la\nmano de Aispuru y con el regreso de Francisco Ramírez quitarle el invicto a Newbery,\nque viene de ganarle a Deportivo Argentino, pero sin jugar bien ni despeinarse.\nCon Medina, uno de los goleadores del torneo, se espera por el destape de\nIbáñez, y unos minutos de Domínguez de cara al clásico de la novena.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Roberts y Atl. Quenuma, sea de los partidos más atrapantes\nde esta fecha. Roberts, escolta de Newbery, viene de ganar en Tres Lomas un\npartido durísimo ante Argentino, y vuelve a su casa a tratar de ganar. De los\ntres compromisos, ganó uno, perdió y empató. Con Rivas y Altamiranda como abanderados\ndel ataque y con la ausencia de Avellaneda (suspendido) quiere llegar ganador\nal clásico del domingo 10 de mayo. Los dirigidos por Larroque, que vienen de\nganarle a Unión de Bonifacio y tienen un partido menos con Villa del Parque, fuera\nde casa ganaron uno perdieron otro. Busca afianzarse en la tabla, y esperar un\ntropiezo de Newbery para sumarse a la pelea del campeonato. Será transmisión de\nJuego de Equipo desde 15:30 del domingo.&nbsp;&nbsp;&nbsp; <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Villa del Parque recibe a Unión de Tres Lomas. Los de\nAlsogaray que en los últimos tres partidos apenas sumaron un punto, intentarán\nganar en el “Chocho” Orsi por primera vez en la temporada (un empate y una\nderrota como local). Los Pitucos, que vuelven a jugar luego de quedar libre,\nviajan a Casbas por una victoria que los mantenga en la lucha del Apertura. Están\na cuatro de Newbery y de los últimos tres partidos ganó uno y perdió dos. De\nvisitante, dos victorias y una derrota.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Deportivo 17 y Argentino se encuentran en un partido de\nactualidades opuestas. Los de Valle, cerca de la pelea y buscando confirmar la\nracha que de los últimos tres partidos ganó dos y empató uno. El Tocinero,\núltimo en el Apertura, busca su primera victoria del año, la necesita como el agua\ny nada como una victoria ante el Colono podría darle a los de Rimoldi el empujón\nanímico para ser lo que se espera de Argentino. <o:p></o:p></p>\n\n<p class=\"MsoNormal\">Juventud y El Ceibo se encuentran en el Gastaldi. La Juve,\nque está en la pelea, necesita hacerse fuerte en casa si quiere ser candidato.\nDos derrotas y una victoria como local registran los de Tincho Benítez que\nbuscan ganar para seguir en la parte alta de la tabla, sabiendo que en la\nundécima enfrentan a Newbery. El Ceibo, lejos de la pelea, quiere trepar en la\ntabla. Para eso deberá ganar, situación que no sucedió todavía fuera del Dr.\nPippo, dos empates y una derrota de visitante es la cosecha de los de Sepúlveda\nque necesitan sumar de a tres.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Garré y Maza es un partido muy interesante. Porque los rojos\nestán cerca de la pelea, y no ganar, representaría casi la resignación a la disputa\ndel campeonato, debido a que no le ganó al líder y debe quedar libre. De los\núltimos cuatro, ganó dos, empató uno y perdió el restante. Esa irregularidad lo\ntiene a la expectativa a los de Moriamé. Por su parte, Maza, con campaña\nirregular con dos triunfos, de visitante ganó un partido y perdió dos, buscará\nacercarse al grupo de los ocho, para no quedar lejos en la tabla.&nbsp;<o:p></o:p></p>', '/uploads/2026-05/4af5b8ee-78ff-4758-b709-1e331c4fb892.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-01 12:25:48', '2026-05-01 12:25:49', '2026-05-01 12:25:49', NULL),
(37, 'bien-arriba', 'Bien arriba', 'LA 8VA DEL APERTURA', 'Newbery volvió a ganar, esta vez en Bonifacio para seguir en lo mas alto del campeonato. Roberts, Unión TL y Juventud, también ganaron y siguen sentados en la mesa de los candidatos.', '<p class=\"MsoNormal\"><span lang=\"ES\">Newbery ganó en\nBonifacio ante Unión 3 a 0 y sigue liderando el Apertura. Barrios en dos\noportunidades y Medina, uno de los goleadores del campeonato le dieron al verde\nuna victoria sólida de cara al clásico del próximo domingo. Unión de Bonifacio\nterminó el partido con nueve hombres y en la próxima visita a La Gloria.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Roberts ganó 1 a 0\na Atl. Quenumá y continúa a tres puntos de Newbery. Rivas, el otro goleador del\nApertura, fue el autor de la única conquista que le permitió a Roberts sumar de\na tres, no perder terreno ante Newbery y ya pensar en el clásico de la novena\nfecha. Terminado el partido, el ábitro Luis Frías, de mal arbitraje, expulsó a\nTobías García por agresión a uno de los asistentes del equipo arbitral. Los de\nLarroque en la novena reciben a Deportivo 17.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Unión de Tres\nLomas continúa en la pelea. Goleo 4 a 0 a domicilio a Villa del Parque y sigue\na cuatro puntos de Newbery. Los goles los convirtieron Braian Changazzo en dos\noportunidades, Matías del Arco y Agustín Barrio. El próximo sábado recibe a\nGarré para seguir presionando a Newbery y Roberts. Villa no despega y se hunde\nen la tabla, en la novena descansa.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Juventud ganó de\nlocal y sigue peleando el Apertura. La víctima fue El Ceibo y el gol de Lautaro\nSouza bastó para sumar de a tres y seguir a cuatro puntos de Newbery (se\nenfrentan en la 11°) y en la próxima visita al tocinero. El Ceibo sólo ganó un\npartido y continúa bajando en la tabla. En la próxima fecha recibe a Salazar.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">El sábado, Deportivo\nArgentino goleó en el clásico a La Gloria 4 a 0 para ubicarse en el quinto lugar,\npero lejos de la pelea porque todavía no quedó libre. Esteban Herrero, Sandro Saucedo\nx 2 y Emiliano Mang fueron los goleadores del Depo que se afirma en la tabla.\nEn la próxima viaja a Maza. La Gloria que solo ganó dos partidos, sufrió una\ndura derrota y deberá levantar cabeza en la próxima cuando reciba a Unión de\nBonifacio.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Argentino se\nrecuperó a tiempo y obtuvo su primera victoria en el año. Lo perdía ante\nDeportivo 17 por el gol de Martín Root, pero Matías Álvarez y Matías Hoyos le\ndieron la primera alegría del año al Tocinero que no ganaba desde el clásico del\ntriangular disputado en 2025 en cancha de Roberts. El Colono desperdició la\nchance de seguir, por ahora, discutiendo el Apertura. Argentino en la próxima\nrecibe a Juventud y Deportivo 17 viaja a Quenumá.<o:p></o:p></span></p>\n\n<span lang=\"ES\" style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;\nmso-ascii-theme-font:minor-latin;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\nminor-latin;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:&quot;Times New Roman&quot;;\nmso-bidi-theme-font:minor-bidi;mso-ansi-language:ES;mso-fareast-language:EN-US;\nmso-bidi-language:AR-SA\">En Garré, Maza dio la nota. Con gol de Genaro Gertner\nse llevó tres puntos a casa que le permiten, después de ocho fechas, estar en\nel grupo de los ocho. Por su parte, Garré dejó pasar la posibilidad de seguir a\nla expectativa en la tabla. Los de Moriamé viajan el sábado a Tres Lomas para\nenfrentar a Unión, mientras que Maza recibirá al siempre peligroso Deportivo\nArgentino. &nbsp;</span>', '/uploads/2026-05/a7d73418-2698-409b-a8ea-40cd5033c866.jpg', 'PH Benjamín Lorenzo', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-04 02:17:23', '2026-05-04 02:17:24', '2026-05-04 02:19:38', NULL),
(38, 'gano-roberts-y-suena', 'Ganó Roberts y sueña', 'APERFTURA FECHA 8', 'Con gol de Lucas Rivas, Roberts se reencontró con la victoria en casa y ahora buscará igualar a Newbery en el clásico de la próxima fecha.', '<p class=\"MsoNormal\"><span lang=\"ES\">Roberts se quedó\ncon tres puntos que eran impostergables. Primero porque jugaba de local,\nsegundo porque lo debía hacer si quería seguir siendo parte de los candidatos y\ntercero, porque Newbery no se podía escapar. <o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Los dirigidos por\nPilo Fernández tienen una identidad de juego. Ir a buscar el gol. Lo hace de\nmanera sistemática en cada inicio de partido, pero la falta de contundencia\ndeja vivo al rival, de hecho, en los últimos tres partidos convirtió tres\ngoles. Genera mucho peligro, pero no logra concretar. Eso hace que termine los\npartidos más apretado de lo que el juego, por momentos, muestra. No obstante,\nayer, Atlético Quenumá en la primera parte tuvo mayor profundidad que Roberts, haciendo\nque el cordobés Cerutti tenga que responder en más de una oportunidad con\nremates de media distancia. En tanto, Roberts con Rivas y Altamiranda como\nabanderados del ataque, trataron de llevar peligro al arco defendido por Marduel.\nSi bien, tuvo un par de pelotas paradas que pudo cabecear Rivas, Marduel no\ntuvo atajadas de importancia en la primera parte. En el final del primer tiempo,Cotignola\nno pudo despejar una pelota que cayó en Altamiranda y el de Toay se fue contra\nMarduel para asistir a Rivas que con el arco vacío, como Campaz contra\nArgentino siete días antes, estampe el 1 a 0. <o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">En el complemento\nse vio a un Roberts que volvió a cargar contra el arco quenumense. De arranque\nnomás, una palomita hermosa de Ressia terminó rebotando en el travesaño del\narco de los vestuarios, ahogando el grito de gol de los hinchas rojos que\nestuvieron presente en la tarde del domingo. Luego, una innumerable cantidad de\nposibilidades que Roberts no supo definir desbordando las bandas y centrando de\nmanera equivocada. O iba muy corta, o muy fuerte o muy alta o aparecía un\ndefensor, o las manos del arquero. <o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">La expulsión de\nIbáñez en Quenumá, bien expulsado por infracción a Alonso, pareció darle una\nmejor oportunidad para rematar el partido a los de Salliqueló. Pero como suele\nsuceder, Roberts, once contra diez no logra terminar de hacerse dueño del partido,\ny no fue la excepción. Pasaron los minutos y Quenumá realizó cambios ofensivos\nque llevaron a Roberts a defenderse cada vez más atrás pero con poco peligro para el arco defendido por Cerutti. El partido se diluyó y Roberts\nse quedó con un triunfo que lo mantiene como escolta de Newbery y el próximo domingo,\nnuestro pueblo se detiene. En el Edgardo Marduel, Newbery y Roberts, líder y\nescolta se ven las caras en un contexto que hacía rato no sucedía.<o:p></o:p></span></p>\n\n<p class=\"MsoNormal\"><span lang=\"ES\">Parrafo a parte\npara la mala actuación del árbitro Frías, que debió expulsar a Cotignola en la primera\nparte y a Quiroga en el final del partido. Tuvo varios errores que provocaron\nla bronca del banco y de la hinchada quenumense. De todas maneras\nnada justifica la agresión de Tobías García a uno de los asistentes del cuerpo\narbitral, situación que generó la expulsión de García y posterior catarata de insultos\nhacia el mismo Frías. No terminó como uno esperaba la tarde, aunque esperemos,\ndesde las dos partes, jugadores y árbitros, el clima de cada partido se adecúe\na lo que realmente debe ser un partido de fútbol.&nbsp;<o:p></o:p></span></p>', '/uploads/2026-05/a2c58826-6d26-4725-862c-59272716ddf9.jpg', 'PH Paula Duedra', '', 'FUTBOL LOCAL', 'Facundo Ugarte', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-04 03:15:55', '2026-05-04 18:37:05', NULL),
(39, 'resultados-posiciones-sanciones-y-proxima-fecha-la-liga-publico-su-boletin-oficial-n-17', 'Resultados, posiciones, sanciones y próxima fecha: la Liga publicó su Boletín Oficial Nº 17', 'Boletin Oficial N 17', 'La Liga Cultural y Deportiva de Tres Lomas difundió el Boletín Nº 17 correspondiente a la actividad del fin de semana del 2 y 3 de mayo. El documento incluye los resultados de todas las categorías, las tablas de posiciones actualizadas, el detalle del Tribunal de Penas y la programación oficial para la próxima jornada en Primera, Tercera, Senior e Inferiores.', 'La Liga Cultural y Deportiva de Tres Lomas dio a conocer el Boletín Oficial Nº 17, fechado el 5 de mayo de 2026, con toda la información oficial de la jornada disputada los días 2 y 3 de mayo en las distintas categorías del fútbol liguista.', '/uploads/2026-05/8e675d2e-02ee-4820-b165-a402885ec432.jpg', 'PH Paula Duedra', 'BOLETIN OFICIAL', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'boletin', '/uploads/2026-05/c5a98846-d828-41c0-8974-f25833ca9c60.pdf', '[]', 999, 2, NULL, '2026-05-05 20:58:40', '2026-05-06 01:02:56', NULL),
(40, 'borrador-69fd5c0302a21', 'La 9na arranca el sábado y el domingo, el clásico', 'APERTURA FECHA 9', 'Esperando el domingo con el clásico salliquelense, mañana la fecha con Unión vs Garre en Tres Lomas. Argentino y Juventud, el otro destacado.', '<p class=\"MsoNormal\">Unión de Tres Lomas recibe a Garré, donde intentará\nconseguir los tres puntos para meter presión en el clásico de Salliqueló y\ncontinuar siendo candidato al Apertura. El pituco se recuperó con dos victorias\nal hilo. Garré sumó cuatro de los últimos 9 puntos y quiere volver a la senda\ndel triunfo. &nbsp;<o:p></o:p></p><p class=\"MsoNormal\">Todas las miradas estarán posadas en Salliqueló. Como hace\nrato no sucedía, chocan Newbery y Roberts siendo primero y segundo del\ncampeonato. Newbery llega invicto, siendo el equipo más goleador y al que menos\nle convirtieron. Roberts tiene menos poder de gol, es quinto en el ranking pero\nes el segundo con la&nbsp; valla menos\nvencida. Se espera por la vuelta de Domínguez en Newbery que se recuperó de la\nlesión muscular y el retorno de Avellaneda en Roberts que vuelve de cumplir dos\nfechas de suspensión.<o:p></o:p></p><p class=\"MsoNormal\">En Tres Lomas, Juventud visita a Argentino que viene de\nganar su primer partido del año. Los de Tincho Benítez saben bien que no pueden\nceder puntos porque representaría alejarse en el campeonato, quieren ganar por\nsexta vez en el torneo y mantenerse en la pelea de arriba. El Toci, por su lado,\nquiere confirmar la levantada del domingo pasado para salir del incómodo último\nlugar. <o:p></o:p></p><p class=\"MsoNormal\">En Quenumá, los dirigidos por Larroque reciben a un peligroso\nDeportivo 17, que viene de perder ante Argentino y quiere volver a ganar para\nalejarse de Quenumá en la lucha por estar en el grupo de los ocho. Atlético\nQuenumá sin Ibáñez ni García, ambos expulsados en la octava fecha, también\nquiere volver a ganar para superar en la tabla al Colono y posicionarse mejor\npara recuperar la confianza perdida. <o:p></o:p></p><p class=\"MsoNormal\">Deportivo Maza y Deportivo Argentino se encuentran para\nseguir trepando en la tabla. Los de la dupla Etchezar y Nennhuber trataran de\nigualar la línea de su rival en la tabla, y así confirmar su buen momento, en\ntanto que los de Rey, con menos chances en el campeonato, sueñan con pelear\ntodavía el Apertura.<o:p></o:p></p><p class=\"MsoNormal\">Por la zona media baja, La Gloria será local de Unión de\nBonifacio que volverá a no contar con Francisco Ramírez ni con Gonzalo Achával.\nSituación que La Gloria intentará aprovechar para sumar de a tres en el\nChangazzo y así alejarse de la zona baja y afirmarse a la mitad de la tabla. Unión\nobtuvo su única victoria en la cuarta fecha y La Gloria no gana desde la sexta\njornada con dos derrotas consecutivas.<o:p></o:p></p><p class=\"MsoNormal\">\n\n\n\n\n\n\n\n\n\n\n\n<span style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;\nmso-ascii-theme-font:minor-latin;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\nminor-latin;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:&quot;Times New Roman&quot;;\nmso-bidi-theme-font:minor-bidi;mso-ansi-language:ES-AR;mso-fareast-language:\nEN-US;mso-bidi-language:AR-SA\">El Ceibo y Salazar se encontraran en el Dr. Pippo\ny son dos equipos con un partido ganado cada uno y la misma cantidad de puntos,\nseis. Una victoria los acercará a la zona de los ocho, de lo contrario,\nquedaran demasiado atrás. El Ceibo no gana desde la quinta fecha, en tanto que\nSalazar no lo hace desde la segunda.&nbsp;</span></p>', '/uploads/2026-05/0611464a-4040-4ddb-bf13-179cda4af5ac.jpg', 'PH Benjamín Lorenzo PH Paula Duedra', 'FECHA 9', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-08 03:44:03', '2026-05-08 18:22:13', NULL),
(41, 'empate-poco-futbol-y-arriba-se-juntan', 'Empate, poco fútbol y arriba se juntan', 'APERTURA FECHA 9', 'Un clásico caliente que terminó empatado en dos.  Juventud es nuevo escolta, los de atrás se acercan y Unión de Tres Lomas fue el perdedor de la fecha.', 'Newbery y Roberts empataron a dos y se mantiene la diferencia de Tres unidades con la salvedad que Juventud está en el medio de los dos. Poco fútbol, mucho roce, discusiones varias y un árbitro errático desmejoraron el espectáculo. Newbery continúa como líder e invicto, pero se le acercó Juventud que le respira bien de cerca. Roberts, por su parte se mantiene en la pelea pese a no conseguir los tres puntos que fue a buscar. El verde viaja a Tres Lomas en la décima para enfrentar a Deportivo 17 y Roberts reciba a La Gloria.&nbsp;<div>Juventud volvió a ganar a domicilio, fue 2 a 0 ante Argentino de Tres Lomas, dejándolo último y afirmándose como nuevo escolta a dos puntos de Newbery, con quien jugará en dos fechas. El Toci por su lado, viajará a Salazar para salir del último lugar, mientras que Juventud recibirá al golpeado Quenumá.&nbsp;</div><div>Atlético Quenumá cayo como local ante Deportivo 17 3 a 0, sigue fuera del grupo de los ocho y suma 3 puntos de los últimos 4 PJ con una sola victoria. El Colono por su parte, se recuperó de la derrota ante Argentino en el clásico y se coloca a seis puntos del líder Newbery a quien recibe en la décima. Los dirigidos por Santiago Larroque, por su parte viajan a 30 de Agosto a enfrentar a Juventud, el nuevo escolta<span style=\"font-size: 0.875rem;\">.</span></div><div>El sábado, Garré goleo a domicilio a Unión de Tres Lomas 5 a 3 y lo alejó del campeonato. Los rojos con un Tomas Rodríguez intratable convirtiendo un triplete le descontaron al puntero, pero todavía no quedaron libre. La próxima, los de Moriame reciben&nbsp; a Villa del Parque que esta fecha descansó. Unión, en cambio viaja a 30 de Agosto a la casa de Deportivo Argentino que sigue subiendo en la tabla.&nbsp;</div><div><br></div><div>Deportivo Argentino continúa en franco ascenso después de ganar en Maza ante Deportivo. El 1 a 0 fue suficiente para volver a a ganar y ponerse a 4 Pts de Newbery aunque todavía deben la fecha libre. Maza, por su lado, no pudo continuar con el buen momento y cedió puntos en casa. De todas maneras se mantiene entre los 8 mejores. En la próxima, Maza viaja a Bonifacio y Deportivo recibe a Unión de Tres Lomas.&nbsp;</div><div>La Gloria y Unión de Bonifacio hicieron tablas en uno en 30 de Agosto ambos siguen sin sumar puntos gordos en el campeonato. En la décima La Glorias viaja a Salliqueló para enfrentar a Roberts y Unión en Bonifacio espera por Maza.</div><div>En Casbas, El Ceibo volvió a ganar. Fue 3 a 1 ante Salazar y ento le permite alejarse de la zona baja, lugar en El que sí se encuentra Salazar. El próximo fin de semana El Ceibo descansa y vuelve en la undécima visitando a Atlético Argentino, mientras que Salazar en la décima recibirá al Tocinero.&nbsp;</div>', '/uploads/2026-05/81711400-4edd-4d8b-8695-0e85be91d0f8.jpg', 'PH Benjamín Lorenzo', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-11 03:41:33', '2026-05-11 03:41:32', '2026-05-11 03:41:32', NULL),
(42, 'borrador-6a022d004a5c0', '', NULL, '', 'El partido comenzó lleno de ansias, nervios, fricciones, golpes y poco fútbol. Antes de los diez el DT de Roberts, Pilo Fernández, vio la roja por protestar una infracción sobre Rivas que el árbitro Valentin Pompei sancionó pero no amonestó a Barrios. No logro recordar un partido en el que Roberts haya tenido la iniciativa de juego durante más de un tiempo enfrentando a Newbery. Pero ese dominio no se vio reflejado en peligro contra la&nbsp; valla de Troya. Un cabezazo de Rivas con el partido 1 a 0 para Newbery y otro de Cocimano cuando ya estaba 1 a 1. Newbery no tuvo la pelota pero fue profundo, tal es así que tuvo en Medina la chance de abrir el marcador pero Cerutti le dijo que no.&nbsp;', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-11 19:24:48', '2026-05-11 19:24:48', NULL),
(43, 'resultados-posiciones-sanciones-y-proxima-fecha-la-liga-publico-su-boletin-oficial-n-18', 'Resultados, posiciones, sanciones y próxima fecha: la Liga publicó su Boletín Oficial Nº 18', 'Boletin Oficial N 18', 'La Liga Cultural y Deportiva de Tres Lomas difundió el Boletín Nº 18 correspondiente a la actividad del fin de semana del 9 y 10 de mayo. El documento incluye los resultados de todas las categorías, las tablas de posiciones actualizadas, el detalle del Tribunal de Penas y la programación oficial para la próxima jornada en Primera, Tercera, Senior e Inferiores.', 'La Liga Cultural y Deportiva de Tres Lomas dio a conocer el Boletín Oficial Nº 18, fechado el 12 de mayo de 2026, con toda la información oficial de la jornada disputada los días 9 y 10 de mayo en las distintas categorías del fútbol liguista.', '/uploads/2026-05/4f1c4850-8f7f-4b11-8989-a98b95638661.jpg', 'PH Benjamín Lorenzo', 'BOLETIN OFICIAL', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'boletin', '/uploads/2026-05/9520aa84-fead-4828-b1d1-26690209a22f.pdf', '[]', 999, 2, NULL, '2026-05-12 22:45:49', '2026-05-13 00:10:28', NULL);
INSERT INTO `noticias` (`id`, `slug`, `titulo`, `volanta`, `copete`, `contenido`, `imagen_url`, `imagen_alt`, `tag`, `categoria`, `autor_nombre`, `estado`, `destacada`, `tipo_contenido`, `boletin_pdf_url`, `galeria_json`, `orden_home`, `temporada_id`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(44, 'arranca-la-10ma', 'Arranca la 10ma', 'APERTURA FECHA 10', 'Hoy comienza a disputarse la décima fecha con Juventud vs Quenumá. Mañana se juega el resto. Newbery viaja a Tres Lomas y Roberts es local ante La Gloria y será transmisión de Juego de Equipo.', 'Hoy Juventud recibe a Atlético Quenumá, buscando llegar a lo más alto del campeonato y esperar el domingo. La Juve ganó los últimos tres partidos y Quenumá ganó uno y perdió dos.<div><br><div>Newbery visita a Deportivo 17 en un encuentro al que llegarán sabiendo el resultado de Juventud pero sobre todo con un Colono que está a seis puntos. Si bien no tendrá a Martins suspendido, buscará mantener una regularidad que le permita estar arriba. Newbery, sin Cardozo por lesión ni Ramos suspendido después del clásico intentará seguir en lo más alto. Está invicto y el poder de gol es el arma más fuerte que tiene el verde con Medina, Barrios e Ibañez.&nbsp;</div><div><br></div><div>Roberts recibe a La Gloria con la intención de volver a la victoria para mantenerse en la pelea. Si bien es cierto que los de 30 de Agosto no están peleando, siempre es un rival de cuidado para Roberts. Después de la derrota en la cuarta fecha con Juventud, ganó tres y empató en dos oportunidades. La Gloria no gana desde la sexta fecha en su visita a Villa del Parque. Con un empate y dos derrotas llega a Salliqueló con la sería intensión de arruinarle el domingo a los de Fernández.&nbsp;</div><div>Deportivo Argentino y Unión es otro partido caliente de la décima. El Depo sigue trepando, aunque debe quedar libre todavía, llega con una racha de cinco victorias y una derrota. Unión viene de recibir un golpe muy duro en su casa ante Garré. Perdió la gran chance de acercarse a Newbery y ahora el trabajo se hace más difícil. Viaja a e0 de Agosto a buscar regularidad. Ganó dos y perdió dos en sus últimos cuatro juegos.</div><div><br></div><div>Garré quiere seguir en la racha ganadora, aprovechar el envión anímico a Tomás Rodríguez que está dulce de goles. Dos victorias, un empate y dos derrotas es la racha de los últimos cinco juegos.&nbsp;</div><div>Villa vuelve después de quedar libre e intentará volver con una victoria necesaria para salir del fondo. Su única victoria fue el la fecha tres ante Argentino en Tres Lomas.&nbsp;</div><div>Unión de Bonifacio recibe a Maza. Una buena oportunidad para empezar a despegar aunque no todavía tiene bajas por suspensión. No gana desde la fecha cuatro con dos empates y tres derrotas. Maza sabe que es un partido para sumar de a tres y mantenerse en zona de los ocho. Es irregular por eso tiene en los últimos cuatro partidos una victoria, un empate y dos derrotas.&nbsp;</div><div><br></div><div>Salazar y Argentino es el partido de abajo de la tabla. Dos con campañas flojas, quieren salir del fondo. Salazar solo ganó en la segunda fecha y Argentino lo hizo en la octava.</div></div>', '/uploads/2026-05/d3e42ef8-44e7-4c05-8d67-6c51a6f2deb5.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-16 09:36:57', '2026-05-18 19:15:00', NULL),
(45, 'el-apertura-arde', 'El Apertura arde', 'APERTURA FECHA 10', 'Newbery empató, ganaron Juventud,  Roberts y Deportivo Argentino.  Los cuatro equipos en dos puntos. En la próxima, los punteros se ven las caras en el Marduel.', '<div>Newbery lo ganaba 2 a 0 en Tres Lomas con goles de Medina e Ibañez y Deportivo 17 en el segundo tiempo lo empató por Coña y Gómez. El verde sigue invicto y puntero, peronya no está solo, lo alcanzóJuventud y la proxima fecha se ven las caras. Por su lado el Colono vuelve a demostrar su buen momento y sigue sumando en la undécima va a La Gloria.</div><div><br></div><div>Juventud ganó el sábado ante Atlético Quenumá 3 a 0 y se subió a lo más alto. Los goles los marxaron Olivares, Flores y Correa. Newbery con el empate lo alcanzó y ahora lideran el Apertura. La próxima, Juventud viaja a Salliqueló para enfrentar a Newbery. Por su parte, Quenumá no levanta cabeza&nbsp; y sigue lejos de parecerse al de 2025. En la siguiente recibe a Salazar en un choque clave por el octavo lugar.&nbsp;</div><div>Roberts en su casa le ganó 3 a 1 a La Gloria y quedó a un punto de los líderes. Dos de Alonso y el restante de Campaz sellaron el triunfo. Luna descontó para La Gloria que sigue cayendo en la tabla y ahora recibe al difícil Deportivo 17. Roberts viaja a Maza.</div><div><br></div><div>Deportivo Argentino se aprovechó del mal momento pituco y lo despachó con un 4 a 1 contundente. Dos de Jona Silva, uno de su hermano Alejandro y el restante de Herrero, descontó Tomasello en Unión,&nbsp; dejan a Deportivo a dos unidades de los punteros pero con un partido más los dirigidos por Rey. Unión, que venía animando el campeonato, se desinfló y sigue bajando en la tabla. El Depo viaja a Casbas para enfrentar a Villa, mientras que Unión, recibirá a Unión de Bonifacio por la decimoprimer fecha.&nbsp;</div><div><br></div><div>Garré ganó y se acerca a los de arriba. Fue 2 a 1 sobre Villa del Parque, los goles los marcaron Lara de penal y Tomas Rodríguez para el local y descontó Martín Romero para los de Alsogaray. Garré sube, sueña, pero está un poco lejos. Villa por su lado, continúa sin levantar cabeza. Garre descansa en la once y Villa espera por Deportivo Argentino.</div><div><br></div><div>El que ganó fue Unión de Bonifacio, fue 2 a 0 sobre Deportivo Maza y sale del fondo para acercarse al puesto ocho. Gallego y Aispuru marcaron los goles del local. Maza por su lado, desaprovechó la posibilidad de seguir subiendo en la tabla. Los de Arbe van a Tres Lomas con el otro Unión y Maza recibe a Roberts.&nbsp;</div><div><br></div><div>Salazar volvió a ganar y hunde aún más a Argentino. Fue 3 a 0 por los goles de Gallardo, Vides y Silva. Ahora se acerca a los puestos de liguilla y el Toci naufraga en la última posición. Salazar visita a Quenumá el próximo domingo y un día antes, Argentino recibe a El Ceibo.</div>', '/uploads/2026-05/55290283-3388-45d4-ab2f-a77582a940bf.jpg', 'PH Benjamín Lorenzo  y Manu Gorg', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-18 04:04:10', '2026-05-18 04:04:10', '2026-05-18 19:14:46', NULL),
(46, 'gano-roberts-y-quedo-a-un-punto', 'Ganó Roberts y quedó a un punto.', 'APERTURA FECHA 10', 'Roberts ganó bien pero demoró en liquidar. Con el sistema de tres delanteros tuvo autoridad, La Gloria casi no le llegó\n  a Cerutti. Alonso y Campaz fueron los goleadores .', 'Roberts le ganó a La Gloria 3 a 1 y se quedó con tres puntos que ilusionan. El empate de Newbery provocó el acercamiento de los dirigidos por Fernández a la punta del campeonato y ahora Roberts está a una&nbsp; unidad de Newbery y Juventud.<div>Como cada partido Roberts salió a buscar el resultado. A los seis minutos Alonso abrió el marcador con un golazo desde el borde del área y empezó a encaminar el partido. Con Schap como delantero centro y sin Cocimano por lesión, con Carrizo como central, Roberts jugó nuevamente con tres Delanteros.</div><div><br></div><div>Ante La Gloria, Roberts tuvo la profundidad que no lograba en los últimos partidos pero careció de contundencia después del 2 a 0 que llegó también a través de Alonso, esta vez de cabeza a los 23 de la etapa inicial.A partir del segundo gol, Roberts manejó todo y tenía el partido controlado, pero comenzó a desperdiciar chances. Schap y Rivas tuvieron una muy clara casa uno.</div><div><br></div><div>El segundo tiempo Roberts arrancó dormido y La Gloria aprovechó una pelota quieta para desconectar con un cabezazo de Luna. Los locales tardaron en reaccionar pero cuando lo hicieron, las chances empezaron a dilapidarse. Schap, Altamiranda dos veces,&nbsp;Rivas, Carrizo, Campaz que había ingresado por Schap, Mercadín que sustituyó a Altamiranda, entre otroas oportunidades, no sentenciaron. Esa falta de contundencia generó nervios en jugadores e hinchas porque la diferencia era de apenas un gol. La Gloria llegó al área con tiros largos pero sin rematar al arco local y en tiempo de descuento Campaz se sacó la mufa convirtiendo el 3 a 1 tras asistencia de Rivas.&nbsp;</div><div><br></div><div>Roberts ganó con autoridad pero la tranquilidad llegó en el final. Fue más profundo pero con poca contundencia, no pasó sobresaltos y está en la pelea, a un punto de los punteros Newbery y Juventud que se ven las caras en la próxima jornada. Ahora viaja a Maza, a la casa de Deportivo con la ilusión de seguir ganado a la espera del tropiezo de los de arriba.</div>', '/uploads/2026-05/fbf824de-305d-4087-b4a9-0371eaada5fd.jpg', 'P H Yamila Rosas', '', 'FUTBOL LOCAL', 'Facundo Ugarte', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-18 18:41:04', '2026-05-18 18:41:04', '2026-05-18 19:14:35', NULL),
(47, 'salazar-se-refuerza', 'Salazar se refuerza', 'REFUERZOS TORNEO CLAUSURA', 'Salazar FC, a través de su presidente, Marcos Juan, ya confirmó los refuerzos para el Clsusura 2026.', '<div>Marcos Juan, en declaraciones en \"Domingo de Cancha\" a cinco fechas del final del Apertura las dos incorporaciones que se pueden hacer entre un torneo y el otro. Rosendo Barni y Walter Acuña.</div><div>Barni, nacido en Diaeraux, es media punta e hizo inferiores en Racing y jugó en Sacachispas, en el ascenso. También tiene paso por la liga de&nbsp; Pehuajó, donde jugó en Deportivo Argentino, Defensores del Este y Atlético Mones Cazón y en La Niña en la liga de Nueve de Julio</div><div>Acuña es delantero oriundo de San Nicolás y tiene pasado en Rosario Central, Olimpo de Bahía Blanca y Colón en Primera División entre los años 2012 y 2017. Luego pasó por el ascenso argentino defendiendo las camisetas de Juventud de Gualeguaychu, Villa Mitre de Bahía Blanca, Deportivo Riestra, Alte. Brown, Estudiantes ee Río IV y Ciudad de Bolivar. Viene de jugar en el fútbol dominicano y ahora vestirá los colores de Salazar FC, que se refuerza en la delantera de cara a la segunda mitad de año.</div>', '/uploads/2026-05/197ba721-a673-47a3-b1bc-2740b31865b8.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-19 02:44:33', '2026-05-19 15:18:48', NULL),
(48, 'borrador-6a0bce94f21b8', '', NULL, '', '', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-19 02:44:36', '2026-05-19 02:44:36', NULL),
(49, 'el-tocinero-se-quedo-sin-dt', 'El Tocinero se quedó sin DT', 'ATLETICO ARGENTINO', 'Es el primer entrenador en dejar el cargo en esta temporada.', 'Atlético Argentino confirmó la salida de su DT Lucas Rimoldi luego de la derrota 3 a 0 ante Salazar. Rimoldi deja su cargo luego de disputar 10 partidos con una victoria, un empate y ocho derrotas, ocupando el último lugar en la tabla. La dirigencia de manera interina designó a Gustavo Salvá quien fuese en tecnico en 2025 y campeón de la Liguilla de la temporada anterior.&nbsp;', '/uploads/2026-05/c7f61a25-a595-499f-b887-475b473dd651.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-19 03:12:50', '2026-05-19 15:30:23', NULL),
(50, 'resultados-posiciones-sanciones-y-proxima-fecha-la-liga-publico-su-boletin-oficial-n-19', 'Resultados, posiciones, sanciones y próxima fecha: la Liga publicó su Boletín Oficial Nº 19', 'Boletin Oficial N 19', 'La Liga Cultural y Deportiva de Tres Lomas difundió el Boletín Nº 19 correspondiente a la actividad del fin de semana del 16 y 17 de mayo. El documento incluye los resultados de todas las categorías, las tablas de posiciones actualizadas, el detalle del Tribunal de Penas y la programación oficial para la próxima jornada en Primera, Tercera, Senior e Inferiores.', 'La Liga Cultural y Deportiva de Tres Lomas dio a conocer el Boletín Oficial Nº 19, fechado el 19 de mayo de 2026, con toda la información oficial de la jornada disputada los días 16 y 17 de mayo en las distintas categorías del fútbol liguista.', '/uploads/2026-05/2dd501ff-b328-4782-9a39-3eafeeb7befc.jpg', 'P H Manuel Fernández Gorg', 'BOLETIN OFICIAL', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'boletin', '/uploads/2026-05/a567d245-c602-47eb-bfb5-799d60647429.pdf', '[]', 999, 2, '2026-05-20 03:38:28', '2026-05-20 03:38:27', '2026-05-20 03:38:27', NULL),
(51, 'quien-festejara', '¿Quién festejará?', 'COMIENZA LA 11MA', 'El Apertura entra en la recta final, y nada está dicho. Por la lucha del campeonato, el partido de la fecha es Newbery vs Juventud, Roberts viaja a Maza pero sabe que no es nada fácil y Deportivo va a Casbas contra Villa para seguir soñando.', '<p class=\"MsoNormal\">Esta tarde Argentino recibe a El Ceibo en un partido de necesitados.\nEl Toci con Salvá de interino quiere volver a la victoria con el aire renovado\ny así salir del fondo, esperando por Ramón Benicelli. El Ceibo, bastante más arriba, necesita los tres puntos\npara ir en búsqueda de los lugares de liguilla. &nbsp;<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Los ojos estarán posados nuevamente en el Marduel de\nSalliequeló. Ahora en el duelo de punteros. Newbery y Juventud, ambos con 21\npuntos quieren ser el único líder. Para cumplir el objetivo deben ganarle a su\nrival para llegar a 24 y acercarse más al objetivo.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Roberts, en cambio, viaja a Maza para continuar en la pelea.\nSabe que con un empate en Salliqueló puede dar la noticia, pero Maza es difícil\ny Roberts viaja después de ganarle a La Gloria. Maza por su lado, dede la 8v a\nque no suma de a de tres en su visita&nbsp; a\nGarrare<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En el Orsi de Casbas, Villa es local de Deportivo e intentará\nno quedar último en la tabla, ya que desde la 3ra que no gana ante Argentino.\nEl Decano quiere seguir soñando con la pelea, es difícil. A parte del triunfo\npropio, necesita que los de arriba sumen lo menos posible.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Unión de Tres Lomas necesita recuperarse ante Unión de Bonifacio.\nLos dirigidos por Farías buscan la recuperación ya que si bien el campeonato está\nmuy lejos, ganando van a trepar mucho. Por su parte, Unión de Bonifacio va en\nbuscar de una victoria que lo acerque a puestos de liguilla. Viene de ganarle a\nMaza en la última jornada y espera repetir. Por el lado de los de Arbe una\nvictoria los acercaría a un liguilla.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">La Gloria recibe a Deportivo 17 en el Raúl Changazzo. Los\nrojos del 30 que vienen de perder en Salliqueló con Roberts necesitan regularidad\ny quieren sorprender. Los de Valle, en cambio quiere seguir trepando en la\ntabla.<o:p></o:p></p>\n\n<span style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;\nmso-ascii-theme-font:minor-latin;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\nminor-latin;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:&quot;Times New Roman&quot;;\nmso-bidi-theme-font:minor-bidi;mso-ansi-language:ES-AR;mso-fareast-language:\nEN-US;mso-bidi-language:AR-SA\">Atlético Quenuma es local de Salazar e intentará\nganar para no alejarse del grupo de los ocho. Salazar, en cambio va en busca de\ntres puntos importates de cara a su preparción para el Clausura.</span>', '/uploads/2026-05/7f6ef5db-67f9-472d-a21a-0d3e3aae3509.jpg', 'PH Benjamín Lorenzo y Manuel Fernandez Gorg', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-23 05:02:53', '2026-05-23 05:02:53', '2026-05-23 05:02:53', NULL),
(52, 'benicelli-es-nuevo-entrenador', 'Benicelli es nuevo entrenador', 'DT TOCINERO', 'Después de la salida de Rimoldi y el interinato de Salvá por un solo partido, Ramón Benicelli es el nuevo técnico de Argentino de Tres Lomas.', 'Ramón Benicelli es nuevo entrenador de Argentino. El oriundo de Zárate se se hará cargo de primera y tercera división debutando en la fecha 13, ya que en la decimo segunda jornada del Apertura, el equipo Tocinero quedará libre. Los compromisos que dirigirá Benicelli, campeón con Unión de Bonifacio en 2023 y con San Marín de Carhué en 2024 en la Liga Regional de Coronel Suárez, será debutando ante Atlético Quenumá como visitante, recibiendo a Newbery y cerrando ante La Gloria en 30 de Agosto. Benicelli había dejado su cargo de DT este año en Atlético Huanguelén en la Liga de Coronel Suárez, donde había llegado a principio de la temporada.', '/uploads/2026-05/5b175f89-6b59-4b99-9650-078592f09f5f.jpg', '', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-23 05:28:04', '2026-05-23 19:19:29', NULL),
(53, 'juventud-es-el-lider', 'Juventud es el líder', 'APERFTURA FECHA 11', 'Juventud se subió a lo más alto sobre el final del partido, cuando Roberts, con su victoria estaba coqueteando con la primera posición. Newbery se quedó sin invicto y ya no depende de sí mismo. También ganaron Deportivo 17, y Deportivo Argentino que todavía sueña.', '<p class=\"MsoNormal\">Juventud le ganó 2 a 1 a Newbery y es el único líder del\ncampeonato. El gol de Culasso sobre el final del partido dejó a Newbery sin\ninvicto y a Juventud en lo más alto. Los dirigidos por Benítez siguen mostrando\nun gran nivel y el momento anímico está en las nubes. Newbery tendrá que recuperarse\ny ahora ya depende de otros resultados.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Roberts en Maza empezó perdiendo desde el vestuario por el\ngol de Kowalsuk, pero lo dio vuelta en el primer tiempo con los festejos de\nRessia y Altamiranda, y en el complemento lo liquidó a través de Altamiranda que\nvolvió a marcar y Román Schap. Quedó tiempo para que Moggia descontara y establecer\nel 4 a 2 definitivo para Roberts que quedó a un punto de Juventud por el\nagónico gol de Culasso en Salliqueló.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">A Deportivo Argentino le costó muchísimo en Casbas. El gol\nde Ybalo puso en ventaja a Villa que estaba jugando con diez hombres desde el\nprimer tiempo por la expulsión de Ponce. Pero la reacción de Deportivo llegó y\nAlejandro Silva igualó las acciones para que en el final del partrido, Esteban\nHerrero convirtiera el gol de la victoria y así llegar al tercer lugar del\nApertura pero con menos posibilidades porque todavía debe quedar libre.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Deportivo 17 sigue de buena racha y continua trepando en la\ntabla. A domicilio le ganó a La Gloria por los goles de Sandalich y Root y se ubica\nen la quinta ubicación del Apertura a seis puntos del líder. La Gloria, por su\nparte no puede salir de la zona baja de la tabla.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En Tres Lomas Unión igualó en uno con Unión de Bonifacio. Lo\nganaba el equipo que dirige Arbe por el gol de Aispuru y en el final del\npartido apareció Gonzalo Martín para definir el resultado en tablas. Unión\nsigue sin ganar y no puede trepar en el escalafón. Por su parte, a Unión de\nBonifacio se le escapó la chance de trepar en la tabla y se coloca en la\ndecimosegunda ubicación.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">Atlético Quenumá y Salazar igualaron en uno por los goles de\nQuiroga para el local y Mansilla para la visita. Le sirvió de poco porque perdieron\nla posibilidad de ganar posiciones en la tabla. Ambos se ubican en la parte media\nde la tabla de posiciones, tratando de meterse en zona de liguilla.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <o:p></o:p></p>\n\n<span style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;\nmso-ascii-theme-font:minor-latin;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\nminor-latin;mso-hansi-theme-font:minor-latin;mso-bidi-font-family:&quot;Times New Roman&quot;;\nmso-bidi-theme-font:minor-bidi;mso-ansi-language:ES-AR;mso-fareast-language:\nEN-US;mso-bidi-language:AR-SA\">El sábado a Argentino de Tres Lomas se le escapó\nel partido ante El Ceibo. Lo ganaba 2 a 0 por los goles de Thiago Fraile y José\nSardón de penal. El Ceibo reaccionó y el aporte de Ometto fue determinante.\nDescontó y luego de penal igualó el partido para llevarse un punto a casa. El\nCeibo se mantiene en la zona media, mientras que Argentino a la espera de\nBenicelli no puede salir del último lugar.</span>', '/uploads/2026-05/348fe4fb-2c2b-479a-ae07-bc4766ca3d3c.jpg', 'PH Benjamín Lorenzo', '', 'FUTBOL LOCAL', 'Redacción Juego de Equipo', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-25 12:50:11', '2026-05-25 12:50:11', '2026-05-25 12:51:15', NULL),
(54, 'un-empate-que-suma-poco', 'Un empate que suma poco', '1 A 1  EN QUENUMA', 'En Quenumá no hubo vencedores. Atlético y Salazar hicieron tablas y no salen de la zona media.', '<div>Atlético Quenumá y Salazar iguala<span style=\"font-size: 0.875rem;\">ron 1 a 1 en un encuentro muy disputado.</span></div><div><span style=\"font-size: 0.875rem;\"><br></span></div><div>En el primer tiempo, Atlético fue superior y logró abrir el marcador en los primeros minutos tras una asistencia de Nicolás Boquín para que Nacho Quiroga convierta el 1 a 0. El equipo generó varias llegadas y mostró un gran nivel durante gran parte de la etapa inicial.</div><div><br></div><div>Sin embargo, cerca del final del primer tiempo llegó el empate de Salazar con gol de Marcelo Mansilla, tras una asistencia de Germán Silva, dejando el marcador 1 a 1 antes del descanso.</div><div><br></div><div>En el segundo tiempo no hubo goles, aunque Atlético volvió a dominar gran parte del juego y manejó más la pelota, pero sin tantas llegadas claras al arco rival.</div><div><br></div><div>Finalmente, el encuentro terminó igualado 1 a 1</div>', '/uploads/2026-05/fb7582ea-2552-43d3-8851-61fe3d0311a1.jpg', 'PH Paula Duedra y Chiqui Pernas', '', 'FUTBOL LOCAL', 'Andrea Bustamante - Domingo de Cancha', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-25 13:06:51', '2026-05-25 13:06:51', '2026-05-25 14:07:21', NULL),
(55, 'de-cabeza-a-la-punta', 'De cabeza a la punta', 'UNICO PUNTERO', 'Juventud fue más que Newbery y sobre el final del partido el gol de Culasso lo depositó en lo más alto de la tabla.', '<p class=\"MsoNormal\">El equipo de Tincho Benítez fue más que Newbery en líneas\ngenerales durante los 90 minutos. Con poca posesión, fue determinante al\nmomento de atacar. Fue profundo cuando pudo y generó chances muy nítidas como\nel tiro al ángulo de Culasso o el tiro de esquina de Olivares que se metía y\nque Troya sacó de manera notable. Hubo dos posibilidades más para Juventud,\npero la defensa de Newbery reaccionó a tiempo. Newbery no preocupó al arquero\nGhinzani por la falta de profundidad.<o:p></o:p></p>\n\n<p class=\"MsoNormal\">En el complemento, ya con Cardozo en cancha, Newbery mejoró\nun poco, fue profundo pero sin provocar atajadas de Ghinzani. Haciendo un repaso\nrápido, Medina, Ibáñez, Barrios, Cardozo y Corral que ingresó en la segunda\nparte, no remataron al arco, y eso marca un poco también, por qué al verde le\nestá costando ganar.<o:p></o:p></p>\n\n<span style=\"line-height: 115%;\">Juventud mantuvo el orden en cancha y fue\npaciente. La victoria de Roberts en Maza generó en el Marduel la necesidad de\nganar en ambos equipos. Los caminos fueron distintos. Newbery desde la posesión\ny Juventud quiso aprovechar los espacios. Los ataques no fueron claros pero sobre\nlos 44 minutos del complemento, Calabaza, Correa, y Daniel Flores combinaron\npor derecha para que Franco Calabaza centrara para el cabezazo letal de\nGerónimo Culasso, provocando el delirio en sus hinchas y ponga a Juventud\nmirando a todos desde lo más alto. Ahora la Juve depende de sí mismos en los\ncuatro partidos que restan, pero sabe que no puede tropezar porque Roberts,\nDeportivo y Newbery están al acecho.</span>', '/uploads/2026-05/9cf29436-a2c4-4f96-becc-35adba6dfde7.jpg', 'PH Benjamín Lorenzo', '', 'FUTBOL LOCAL', 'Facundo Ugarte', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-25 13:53:55', '2026-05-25 13:53:55', '2026-05-25 16:12:31', NULL),
(56, 'borrador-6a1471ba1ade4', '', NULL, '', '<blockquote>asdfa</blockquote><blockquote><div class=\"pull-quote\">emiliano</div></blockquote>', '', '', '', 'General', 'Redacción Juego de Equipo', 'borrador', 0, 'comun', NULL, '[]', 999, 2, NULL, '2026-05-25 15:58:50', '2026-05-25 15:58:50', NULL),
(57, 'atletico-quenuma-fue-superior-pero-igualo-ante-salazar', 'Atlético Quenumá fue superior, pero igualó ante Salazar', 'Liga Cultural y Deportiva de  Tres Lomas.', 'Atlético Quenumá y Salazar empataron 1 a 1 en un partido intenso y parejo. Nacho Quiroga abrió el marcador para el local, mientras que Marcelo Mansilla igualó para Salazar antes del cierre del primer tiempo.', '<div>Atlético Quenumá y Salazar igualaron 1 a 1 en un encuentro muy disputado.</div><div><br></div><div>En el primer tiempo, Atlético fue superior y logró abrir el marcador en los primeros minutos tras una asistencia de Nicolás Boquín para que Nacho Quiroga convierta el 1 a 0. El equipo generó varias llegadas y mostró un gran nivel durante gran parte de la etapa inicial.</div><div><br></div><div>Sin embargo, cerca del final del primer tiempo llegó el empate de Salazar con gol de Marcelo Mansilla, tras una asistencia de Germán Silva, dejando el marcador 1 a 1 antes del descanso.</div><div><br></div><div>En el segundo tiempo no hubo goles, aunque Atlético volvió a dominar gran parte del juego y manejó más la pelota, pero sin tantas llegadas claras al arco rival.</div><div><br></div><div>Finalmente, el encuentro terminó igualado 1 a 1</div>', '/uploads/2026-05/0b1403c2-66e0-4ebf-b125-07310d0da62c.jpg', 'Andrea Bustamante. Domingo de cancha', '', 'Primera división', 'Andrea Bustamante', 'publicada', 1, 'comun', NULL, '[]', 999, 2, '2026-05-25 17:06:23', '2026-05-25 17:06:22', '2026-05-25 17:06:22', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias_relacionadas`
--

CREATE TABLE `noticias_relacionadas` (
  `noticia_id` int(11) NOT NULL,
  `noticia_relacionada_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `noticias_relacionadas`
--

INSERT INTO `noticias_relacionadas` (`noticia_id`, `noticia_relacionada_id`) VALUES
(2, 1),
(1, 2),
(3, 2),
(2, 3),
(6, 5),
(55, 51),
(55, 53),
(55, 54);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estadio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `local` int(11) DEFAULT NULL,
  `visitante` int(11) DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `goleslocal` int(11) NOT NULL DEFAULT '0',
  `golesvisitante` int(11) NOT NULL DEFAULT '0',
  `amarillas` int(11) NOT NULL DEFAULT '0',
  `rojas` int(11) NOT NULL DEFAULT '0',
  `torneo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Apertura',
  `categoria` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Primera',
  `jornada` int(11) DEFAULT NULL,
  `broadcast` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temporada_id` int(11) NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `fecha`, `hora`, `estadio`, `local`, `visitante`, `estado`, `goleslocal`, `golesvisitante`, `amarillas`, `rojas`, `torneo`, `categoria`, `jornada`, `broadcast`, `temporada_id`) VALUES
(3, '2026-03-09', '15:00:00', 'Estadio de Deportivo 17', 13, 6, 'jugado', 3, 1, 2, 0, 'Apertura', 'Primera', 1, NULL, 2),
(4, '2026-03-09', '15:00:00', 'Estadio Artemio Gastaldi', 14, 4, 'jugado', 0, 3, 8, 0, 'Apertura', 'Primera', 1, NULL, 2),
(5, '2026-03-09', '15:00:00', 'Estadio de Cecil A. Roberts', 1, 3, 'jugado', 2, 1, 6, 2, 'Apertura', 'Primera', 1, NULL, 2),
(6, '2026-03-09', '15:00:00', 'Estadio José Sardon', 8, 5, 'jugado', 0, 1, 4, 0, 'Apertura', 'Primera', 1, NULL, 2),
(7, '2026-03-09', '15:00:00', 'Estadio Doctor Pippo', 11, 2, 'jugado', 0, 1, 3, 0, 'Apertura', 'Primera', 1, NULL, 2),
(8, '2026-03-09', '15:00:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 12, 'jugado', 1, 1, 6, 0, 'Apertura', 'Primera', 1, NULL, 2),
(9, '2026-03-09', '15:00:00', 'Estadio Martin \"Pato\" Pichetto', 9, 43, 'jugado', 0, 0, 5, 1, 'Apertura', 'Primera', 1, NULL, 2),
(10, '2026-03-16', '15:00:00', 'Estadio \"Azul\" Francisco Morero', 2, 8, 'jugado', 2, 1, 7, 1, 'Apertura', 'Primera', 2, NULL, 2),
(11, '2026-03-16', '15:00:00', 'Estadio de Deportivo Argentino', 6, 14, 'jugado', 0, 2, 10, 0, 'Apertura', 'Primera', 2, NULL, 2),
(12, '2026-03-16', '15:00:00', 'Estadio Demetrio Carmelo Loyarte', 5, 7, 'jugado', 0, 2, 4, 0, 'Apertura', 'Primera', 2, NULL, 2),
(13, '2026-03-16', '15:00:00', 'Estadio Local', 43, 11, 'jugado', 1, 1, 5, 0, 'Apertura', 'Primera', 2, NULL, 2),
(14, '2026-03-16', '15:00:00', 'Estadio de Unión de Bonifacio', 3, 13, 'jugado', 1, 1, 8, 0, 'Apertura', 'Primera', 2, NULL, 2),
(15, '2026-03-16', '15:00:00', 'Estadio de Deportivo Garré', 4, 9, 'jugado', 2, 3, 8, 0, 'Apertura', 'Primera', 2, NULL, 2),
(16, '2026-03-16', '15:00:00', 'Estadio Raul Changazzo', 12, 10, 'jugado', 0, 1, 1, 0, 'Apertura', 'Primera', 2, NULL, 2),
(17, '2026-03-23', '15:00:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 2, 'jugado', 0, 5, 4, 2, 'Apertura', 'Primera', 3, NULL, 2),
(18, '2026-03-23', '15:00:00', 'Estadio Doctor Pippo', 11, 4, 'jugado', 1, 1, 3, 1, 'Apertura', 'Primera', 3, NULL, 2),
(19, '2026-03-23', '15:00:00', 'Estadio de Deportivo 17', 13, 1, 'jugado', 0, 3, 4, 0, 'Apertura', 'Primera', 3, NULL, 2),
(20, '2026-03-23', '15:00:00', 'Estadio Edgardo Marduel', 10, 5, 'jugado', 5, 2, 2, 0, 'Apertura', 'Primera', 3, NULL, 2),
(21, '2026-03-23', '15:00:00', 'Estadio José Sardon', 8, 43, 'jugado', 0, 2, 4, 0, 'Apertura', 'Primera', 3, NULL, 2),
(22, '2026-03-23', '15:00:00', 'Estadio Martin \"Pato\" Pichetto', 9, 6, 'jugado', 2, 2, 2, 1, 'Apertura', 'Primera', 3, NULL, 2),
(23, '2026-03-23', '15:00:00', 'Estadio Artemio Gastaldi', 14, 3, 'jugado', 2, 0, 3, 1, 'Apertura', 'Primera', 3, NULL, 2),
(24, '2026-03-30', '15:00:00', 'Estadio de Cecil A. Roberts', 1, 14, 'jugado', 1, 2, 7, 0, 'Apertura', 'Primera', 4, NULL, 2),
(25, '2026-03-30', '15:00:00', 'Estadio \"Azul\" Francisco Morero', 2, 10, 'jugado', 0, 4, 5, 0, 'Apertura', 'Primera', 4, NULL, 2),
(26, '2026-03-30', '15:00:00', 'Estadio Local', 43, 7, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 4, NULL, 2),
(27, '2026-03-30', '15:00:00', 'Estadio de Deportivo Garré', 4, 8, 'jugado', 2, 1, 3, 0, 'Apertura', 'Primera', 4, NULL, 2),
(28, '2026-03-30', '15:00:00', 'Estadio de Deportivo Argentino', 6, 11, 'jugado', 2, 1, 4, 0, 'Apertura', 'Primera', 4, NULL, 2),
(29, '2026-03-30', '15:00:00', 'Estadio Demetrio Carmelo Loyarte', 5, 12, 'jugado', 4, 2, 3, 0, 'Apertura', 'Primera', 4, NULL, 2),
(30, '2026-03-30', '15:00:00', 'Estadio de Unión de Bonifacio', 3, 9, 'jugado', 2, 1, 4, 0, 'Apertura', 'Primera', 4, NULL, 2),
(31, '2026-03-16', '15:00:00', 'Estadio Raul Changazzo', 26, 24, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(32, '2026-03-16', '15:00:00', 'Estadio Demetrio Carmelo Loyarte', 19, 21, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(33, '2026-03-16', '15:00:00', 'Estadio \"Azul\" Francisco Morero', 16, 22, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(34, '2026-03-16', '15:00:00', 'Estadio de Unión de Bonifacio', 17, 27, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(35, '2026-03-16', '15:00:00', 'Estadio de Deportivo Argentino', 20, 28, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(36, '2026-03-16', '15:00:00', 'Estadio Local', 44, 25, 'jugado', 1, 4, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(37, '2026-03-16', '15:00:00', 'Estadio de Deportivo Garré', 18, 23, 'jugado', 4, 3, 0, 0, 'Apertura', 'Tercera', 2, NULL, 2),
(38, '2026-03-23', '15:00:00', 'Estadio Edgardo Marduel', 24, 19, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(39, '2026-03-23', '15:00:00', 'Estadio de Deportivo 17', 27, 15, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(40, '2026-03-23', '15:00:00', 'Estadio José Sardon', 22, 44, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(41, '2026-03-23', '15:00:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 16, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(42, '2026-03-23', '15:00:00', 'Estadio Artemio Gastaldi', 28, 17, 'jugado', 2, 1, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(43, '2026-03-23', '15:00:00', 'Estadio Martin \"Pato\" Pichetto', 23, 20, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(44, '2026-03-23', '15:00:00', 'Estadio Doctor Pippo', 25, 18, 'jugado', 2, 3, 0, 0, 'Apertura', 'Tercera', 3, NULL, 2),
(45, '2026-03-30', '15:00:00', 'Estadio de Cecil A. Roberts', 15, 28, 'jugado', 3, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(46, '2026-03-30', '15:00:00', 'Estadio \"Azul\" Francisco Morero', 16, 24, 'jugado', 4, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(47, '2026-03-30', '15:00:00', 'Estadio Local', 44, 21, 'postergado', 0, 0, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(48, '2026-03-30', '15:00:00', 'Estadio de Deportivo Garré', 18, 22, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(49, '2026-03-30', '15:00:00', 'Estadio de Deportivo Argentino', 20, 25, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(50, '2026-03-30', '15:00:00', 'Estadio Demetrio Carmelo Loyarte', 19, 26, 'jugado', 3, 4, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(51, '2026-03-30', '15:00:00', 'Estadio de Unión de Bonifacio', 17, 23, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 4, NULL, 2),
(150, '2026-03-15', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 17, 'jugado', 8, 0, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(151, '2026-03-15', '13:30:00', 'Roberto Tito Castañeira, Quenuma', 21, 26, 'jugado', 6, 1, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(152, '2026-03-15', '13:30:00', 'Dr. Pippo, Casbas', 25, 16, 'jugado', 0, 7, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(153, '2026-03-15', '13:30:00', 'José Sardón, Tres Lomas', 22, 19, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(154, '2026-03-14', '13:30:00', 'Deportivo 17, Tres Lomas', 27, 20, 'jugado', 3, 0, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(155, '2026-03-15', '13:30:00', 'Artemio Gastaldi, 30 de Agosto', 28, 18, 'jugado', 4, 1, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(156, '2026-03-15', '13:30:00', 'Salazar', 23, 44, 'jugado', 0, 4, 0, 0, 'Apertura', 'Tercera', 1, NULL, 2),
(157, '2026-03-15', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 31, 'jugado', 4, 0, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(158, '2026-03-15', '11:30:00', 'Ferro', 35, 40, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(159, '2023-03-15', '11:30:00', 'Dr. Pippo, Casbas', 39, 30, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(160, '2026-03-15', '11:30:00', 'José Sardón, Tres Lomas', 36, 33, 'jugado', 1, 1, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(161, '2026-03-14', '11:30:00', 'Deportivo 17, Tres Lomas', 41, 34, 'jugado', 1, 6, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(162, '2026-03-15', '11:30:00', 'Artemio Gastaldi, 30 de Agosto', 42, 32, 'jugado', 5, 1, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(163, '2026-03-15', '11:30:00', 'Salazar', 37, 49, 'jugado', 1, 3, 0, 0, 'Apertura', 'Senior', 1, NULL, 2),
(164, '2026-03-21', '11:30:00', 'Raul Changazzo, 30 de Agosto', 40, 38, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 2, '', 2),
(165, '2026-03-22', '11:30:00', 'Unión, Tres Lomas', 30, 36, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(166, '2026-03-22', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 41, 'jugado', 2, 0, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(167, '2026-03-22', '11:30:00', 'Polideportivo, Villa Maza', 33, 35, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(168, '2026-03-22', '11:30:00', 'Polideportivo, Garré', 32, 37, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(169, '2026-03-22', '11:30:00', 'Walter \"Chocho\" Orsi', 49, 39, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(170, '2026-03-22', '11:30:00', 'Deportivo Argentino, 30 de Agosto', 34, 42, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 2, NULL, 2),
(171, '2026-03-29', '11:30:00', 'Edgardo Marduel, Sallqueló', 38, 33, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(172, '2026-03-29', '11:30:00', 'Deportivo 17, Tres Lomas', 41, 29, 'jugado', 0, 1, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(173, '2026-03-29', '11:30:00', 'Ferro, Tres Lomas', 35, 30, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(174, '2026-03-28', '11:30:00', 'José Sardón, Tres Lomas', 36, 49, 'jugado', 2, 0, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(175, '2026-03-29', '11:30:00', 'Artemio Gastaldi, 30 de Agosto', 42, 31, 'jugado', 6, 0, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(176, '2026-03-29', '11:30:00', 'Salazar', 37, 34, 'jugado', 0, 1, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(177, '2026-03-29', '11:30:00', 'Dr. Pippo, Casbas', 39, 32, 'jugado', 1, 1, 0, 0, 'Apertura', 'Senior', 3, NULL, 2),
(178, '2026-04-05', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 42, 'jugado', 4, 2, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(179, '2026-04-05', '11:30:00', 'Unión, Tres Lomas', 30, 38, 'jugado', 0, 3, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(180, '2026-04-05', '11:30:00', 'Polideportivo, Garré', 32, 36, 'jugado', 0, 1, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(181, '2026-04-05', '11:30:00', 'Walter \"Chocho\" Orsi', 49, 35, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(182, '2026-04-05', '11:30:00', 'Deportivo Argentino, 30 de Agosto', 34, 39, 'jugado', 1, 0, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(183, '2026-04-05', '11:30:00', 'Polideportivo, Villa Maza', 33, 40, 'jugado', 4, 0, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(184, '2026-04-05', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 37, 'jugado', 1, 3, 0, 0, 'Apertura', 'Senior', 4, NULL, 2),
(185, '2026-04-12', '15:30:00', 'Edgardo Marduel, Sallqueló', 10, 43, 'jugado', 3, 0, 5, 0, 'Apertura', 'Primera', 5, 'https://youtube.com/live/tF5xaSvrFwA?feature=share', 2),
(186, '2026-04-12', '15:30:00', 'Salazar', 9, 1, 'jugado', 0, 2, 6, 1, 'Apertura', 'Primera', 5, NULL, 2),
(187, '2026-04-12', '15:30:00', 'Roberto Tito Castañeira, Quenuma', 7, 4, 'jugado', 1, 0, 4, 1, 'Apertura', 'Primera', 5, NULL, 2),
(188, '2026-04-11', '15:30:00', 'Raul Changazzo, 30 de Agosto', 12, 2, 'jugado', 2, 1, 6, 0, 'Apertura', 'Primera', 5, NULL, 2),
(189, '2026-04-12', '15:30:00', 'José Sardón, Tres Lomas', 8, 6, 'jugado', 0, 1, 7, 0, 'Apertura', 'Primera', 5, NULL, 2),
(190, '2026-04-12', '15:30:00', 'Artemio Gastaldi, 30 de Agosto', 14, 13, 'jugado', 1, 2, 6, 0, 'Apertura', 'Primera', 5, NULL, 2),
(191, '2026-04-12', '15:30:00', 'Dr. Pippo, Casbas', 11, 3, 'jugado', 5, 1, 7, 0, 'Apertura', 'Primera', 5, NULL, 2),
(192, '2026-04-19', '15:30:00', 'Estadio de Cecil A. Roberts', 1, 11, 'jugado', 1, 1, 6, 2, 'Apertura', 'Primera', 6, 'https://youtube.com/live/xChPLIIVapI?feature=share', 2),
(193, '2026-04-19', '15:30:00', 'Polideportivo, Garré', 4, 10, 'jugado', 1, 1, 9, 0, 'Apertura', 'Primera', 6, NULL, 2),
(194, '2026-04-19', '15:30:00', 'Deportivo Argentino, 30 de Agosto', 6, 7, 'jugado', 2, 1, 6, 0, 'Apertura', 'Primera', 6, NULL, 2),
(195, '2026-04-19', '15:30:00', 'Unión, Tres Lomas', 2, 5, 'jugado', 2, 1, 4, 1, 'Apertura', 'Primera', 6, NULL, 2),
(196, '2026-04-19', '15:30:00', 'Estadio de Unión de Bonifacio', 3, 8, 'jugado', 1, 1, 4, 1, 'Apertura', 'Primera', 6, NULL, 2),
(197, '2026-04-18', '15:30:00', 'Deportivo 17, Tres Lomas', 13, 9, 'jugado', 1, 1, 4, 0, 'Apertura', 'Primera', 6, NULL, 2),
(198, '2026-04-19', '15:30:00', 'Walter \"Chocho\" Orsi', 43, 12, 'jugado', 0, 1, 3, 0, 'Apertura', 'Primera', 6, NULL, 2),
(200, '2026-04-26', '15:30:00', 'Estadio Edgardo Marduel', 10, 6, 'jugado', 2, 0, 5, 0, 'Apertura', 'Primera', 7, 'https://youtube.com/live/v6kMkc5ds3s?feature=share', 2),
(201, '2026-04-26', '15:30:00', 'Estadio José Sardon', 8, 1, 'jugado', 0, 1, 8, 0, 'Apertura', 'Primera', 7, '', 2),
(202, '2026-04-26', '15:30:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 3, 'jugado', 3, 2, 4, 0, 'Apertura', 'Primera', 7, '', 2),
(203, '2026-04-26', '15:30:00', 'Estadio Doctor Pippo', 11, 13, 'jugado', 0, 3, 0, 0, 'Apertura', 'Primera', 7, NULL, 2),
(204, '2026-04-26', '15:30:00', 'Estadio Martin \"Pato\" Pichetto', 9, 14, 'jugado', 0, 3, 4, 0, 'Apertura', 'Primera', 7, NULL, 2),
(205, '2026-04-26', '15:30:00', 'Estadio Raul Changazzo', 12, 4, 'jugado', 2, 5, 3, 0, 'Apertura', 'Primera', 7, '', 2),
(206, '2026-04-26', '15:30:00', 'Estadio Demetrio Carmelo Loyarte', 5, 43, 'jugado', 1, 1, 4, 0, 'Apertura', 'Primera', 7, '', 2),
(207, '2026-05-02', '15:30:00', 'Estadio de Deportivo Argentino', 6, 12, 'jugado', 4, 0, 0, 0, 'Apertura', 'Primera', 8, NULL, 2),
(209, '2026-05-03', '15:30:00', 'Estadio de Cecil A. Roberts', 1, 7, 'jugado', 1, 0, 8, 2, 'Apertura', 'Primera', 8, 'https://youtube.com/live/nJOW0tjf8bE?feature=share', 2),
(210, '2026-05-03', '15:30:00', 'Estadio de Unión de Bonifacio', 3, 10, 'jugado', 0, 3, 1, 2, 'Apertura', 'Primera', 8, '', 2),
(211, '2026-05-03', '15:30:00', 'Estadio Walter \"Chocho\" Orsi', 43, 2, 'jugado', 0, 4, 4, 0, 'Apertura', 'Primera', 8, '', 2),
(212, '2026-05-03', '15:30:00', 'Estadio de Deportivo 17', 13, 8, 'jugado', 1, 2, 3, 0, 'Apertura', 'Primera', 8, '', 2),
(213, '2026-05-03', '15:30:00', 'Estadio Artemio Gastaldi', 14, 11, 'jugado', 1, 0, 4, 0, 'Apertura', 'Primera', 8, '', 2),
(214, '2026-05-03', '15:30:00', 'Estadio de Deportivo Garré', 4, 5, 'jugado', 0, 1, 2, 0, 'Apertura', 'Primera', 8, '', 2),
(215, '2026-05-09', '15:30:00', 'Estadio \"Azul\" Francisco Morero', 2, 4, 'jugado', 3, 5, 3, 1, 'Apertura', 'Primera', 9, NULL, 2),
(216, '2026-05-10', '15:30:00', 'Estadio Edgardo Marduel', 10, 1, 'jugado', 2, 2, 9, 2, 'Apertura', 'Primera', 9, 'https://youtube.com/live/P5Vi0ih91OY?feature=share', 2),
(217, '2026-05-10', '15:30:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 13, 'jugado', 0, 3, 4, 1, 'Apertura', 'Primera', 9, '', 2),
(218, '2026-05-10', '15:30:00', 'Estadio José Sardon', 8, 14, 'jugado', 0, 2, 5, 0, 'Apertura', 'Primera', 9, '', 2),
(219, '2026-05-10', '15:30:00', 'Estadio Demetrio Carmelo Loyarte', 5, 6, 'jugado', 0, 1, 10, 1, 'Apertura', 'Primera', 9, '', 2),
(228, '2026-05-10', '15:30:00', 'Estadio Raul Changazzo', 12, 3, 'jugado', 1, 1, 2, 1, 'Apertura', 'Primera', 9, '', 2),
(229, '2026-05-10', '15:30:00', 'Estadio Doctor Pippo', 11, 9, 'jugado', 3, 1, 3, 0, 'Apertura', 'Primera', 9, '', 2),
(230, '2026-04-11', '13:30:00', 'Estadio Raul Changazzo', 26, 16, 'jugado', 0, 5, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(231, '2026-04-12', '13:30:00', 'Estadio Edgardo Marduel', 24, 44, 'jugado', 0, 3, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(235, '2026-04-12', '13:30:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 18, 'jugado', 2, 1, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(236, '2026-04-12', '13:30:00', 'Estadio José Sardon', 22, 20, 'jugado', 3, 3, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(237, '2026-04-12', '13:30:00', 'Estadio Artemio Gastaldi', 28, 27, 'jugado', 0, 4, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(238, '2026-04-12', '13:30:00', 'Estadio Doctor Pippo', 25, 17, 'jugado', 2, 2, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(239, '2026-04-11', '11:30:00', 'Estadio Raul Changazzo', 40, 30, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(243, '2026-04-12', '11:30:00', 'Estadio Edgardo Marduel', 38, 49, 'jugado', 0, 3, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(244, '2026-04-12', '11:30:00', 'Estadio Martin \"Pato\" Pichetto', 37, 29, 'jugado', 1, 3, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(245, '2026-04-12', '11:30:00', 'Estadio José Sardon', 36, 34, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(246, '2026-04-12', '11:30:00', 'Estadio Artemio Gastaldi', 42, 41, 'jugado', 1, 0, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(247, '2026-04-12', '11:30:00', 'Estadio Doctor Pippo', 35, 32, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(248, '2026-04-12', '11:30:00', 'Estadio Doctor Pippo', 39, 31, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 5, NULL, 2),
(249, '2026-04-12', '13:30:00', 'Estadio Martin \"Pato\" Pichetto', 23, 15, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 5, NULL, 2),
(250, '2027-05-16', '15:30:00', 'Estadio Artemio Gastaldi', 14, 7, 'jugado', 3, 0, 2, 0, 'Apertura', 'Primera', 10, '', 2),
(251, '2026-05-17', '15:30:00', 'Estadio de Cecil A. Roberts', 1, 12, 'jugado', 3, 1, 5, 0, 'Apertura', 'Primera', 10, 'https://youtube.com/live/u9AUhSNt5Ng?feature=share', 2),
(252, '2026-05-17', '15:30:00', 'Estadio de Deportivo 17', 13, 10, 'jugado', 2, 2, 3, 0, 'Apertura', 'Primera', 10, '', 2),
(253, '2026-05-17', '15:30:00', 'Estadio de Deportivo Argentino', 6, 2, 'jugado', 4, 1, 4, 0, 'Apertura', 'Primera', 10, '', 2),
(254, '2026-05-17', '15:30:00', 'Estadio Martin \"Pato\" Pichetto', 9, 8, 'jugado', 3, 0, 3, 0, 'Apertura', 'Primera', 10, '', 2),
(255, '2026-05-17', '15:30:00', 'Estadio de Deportivo Garré', 4, 43, 'jugado', 2, 1, 3, 2, 'Apertura', 'Primera', 10, '', 2),
(256, '2026-05-17', '15:30:00', 'Estadio de Unión de Bonifacio', 3, 5, 'jugado', 2, 0, 6, 0, 'Apertura', 'Primera', 10, '', 2),
(257, '2026-05-23', '15:30:00', 'Estadio José Sardon', 8, 11, 'jugado', 2, 2, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(258, '2026-05-24', '15:30:00', 'Estadio Edgardo Marduel', 10, 14, 'jugado', 0, 1, 5, 0, 'Apertura', 'Primera', 11, 'https://youtube.com/live/--eAhReHsQA?feature=share', 2),
(259, '2026-05-24', '15:30:00', 'Estadio Demetrio Carmelo Loyarte', 5, 1, 'jugado', 2, 4, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(260, '2026-05-24', '15:30:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 9, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(261, '2026-05-24', '15:30:00', 'Estadio \"Azul\" Francisco Morero', 2, 3, 'jugado', 1, 1, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(262, '2026-05-24', '15:30:00', 'Estadio Raul Changazzo', 12, 13, 'jugado', 1, 2, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(263, '2026-05-24', '15:30:00', 'Estadio Walter \"Chocho\" Orsi', 43, 6, 'jugado', 1, 2, 0, 0, 'Apertura', 'Primera', 11, NULL, 2),
(264, '2026-05-30', '15:30:00', 'Estadio Artemio Gastaldi', 14, 12, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(265, '2026-05-31', '15:30:00', 'Estadio de Cecil A. Roberts', 1, 2, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, '', 2),
(266, '2026-05-31', '15:30:00', 'Estadio Martin \"Pato\" Pichetto', 9, 10, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(267, '2026-05-31', '15:30:00', 'Estadio Doctor Pippo', 11, 7, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(268, '2026-05-31', '15:30:00', 'Estadio de Deportivo 17', 13, 5, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(269, '2026-05-31', '15:30:00', 'Estadio de Deportivo Argentino', 6, 4, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(270, '2026-05-31', '15:30:00', 'Estadio de Unión de Bonifacio', 3, 43, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 12, NULL, 2),
(271, '2026-06-07', '15:30:00', 'Estadio Edgardo Marduel', 10, 11, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(272, '2026-06-07', '15:30:00', 'Estadio Walter \"Chocho\" Orsi', 43, 1, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(273, '2026-06-07', '15:30:00', 'Estadio Roberto \"Tito\" Castaneira', 7, 8, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(274, '2026-06-07', '15:30:00', 'Estadio \"Azul\" Francisco Morero', 2, 13, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(275, '2026-06-07', '15:30:00', 'Estadio Demetrio Carmelo Loyarte', 5, 14, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(276, '2026-06-07', '15:30:00', 'Estadio Raul Changazzo', 12, 9, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(277, '2026-06-07', '15:30:00', 'Estadio de Deportivo Garré', 4, 3, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 13, NULL, 2),
(278, '2026-06-13', '12:30:00', 'Estadio de Deportivo 17', 13, 43, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(279, '2026-06-14', '15:30:00', 'Estadio de Cecil A. Roberts', 1, 4, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(280, '2026-06-14', '15:30:00', 'Estadio José Sardon', 8, 10, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(281, '2026-06-14', '15:30:00', 'Estadio Artemio Gastaldi', 14, 2, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(285, '2026-06-14', '15:30:00', 'Estadio de Unión de Bonifacio', 3, 6, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(286, '2026-06-14', '15:30:00', 'Estadio Martin \"Pato\" Pichetto', 9, 5, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(287, '2026-06-20', '15:30:00', 'Estadio de Deportivo Argentino', 6, 1, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(288, '2026-06-21', '15:30:00', 'Estadio Edgardo Marduel', 10, 7, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(289, '2026-06-21', '15:30:00', 'Estadio \"Azul\" Francisco Morero', 2, 9, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(290, '2026-06-21', '15:30:00', 'Estadio Raul Changazzo', 12, 8, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(291, '2026-06-21', '15:30:00', 'Estadio de Deportivo Garré', 4, 13, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, '', 2),
(292, '2026-06-21', '15:30:00', 'Estadio Walter \"Chocho\" Orsi', 43, 14, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(293, '2026-06-21', '15:30:00', 'Estadio Demetrio Carmelo Loyarte', 5, 11, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 15, NULL, 2),
(294, '2026-04-18', '13:30:00', 'Estadio de Deportivo 17', 27, 23, 'jugado', 4, 0, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(295, '2026-04-19', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 25, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(296, '2026-04-19', '13:30:00', 'Estadio de Deportivo Garré', 18, 24, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(297, '2026-04-19', '13:30:00', 'Estadio de Deportivo Argentino', 20, 21, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(298, '2026-04-19', '13:30:00', 'Estadio \"Azul\" Francisco Morero', 16, 19, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(299, '2026-04-19', '13:30:00', 'Estadio de Unión de Bonifacio', 17, 22, 'jugado', 0, 5, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(300, '2026-04-19', '13:30:00', 'Estadio Walter \"Chocho\" Orsi', 44, 26, 'jugado', 2, 3, 0, 0, 'Apertura', 'Tercera', 6, NULL, 2),
(301, '2026-04-26', '13:30:00', 'Estadio Edgardo Marduel', 24, 20, 'jugado', 3, 5, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(302, '2026-04-26', '13:30:00', 'Estadio José Sardon', 22, 15, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(303, '2026-04-26', '13:30:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 17, 'jugado', 4, 1, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(304, '2026-04-26', '13:30:00', 'Estadio Doctor Pippo', 25, 27, 'jugado', 0, 3, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(305, '2026-04-26', '13:30:00', 'Estadio Martin \"Pato\" Pichetto', 23, 28, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(306, '2026-04-26', '13:30:00', 'Estadio Raul Changazzo', 26, 18, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(307, '2026-04-26', '13:30:00', 'Estadio Demetrio Carmelo Loyarte', 19, 44, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 7, NULL, 2),
(308, '2026-05-02', '13:30:00', 'Estadio de Deportivo Argentino', 20, 26, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(309, '2026-05-03', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 21, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(310, '2026-05-03', '13:30:00', 'Estadio de Unión de Bonifacio', 17, 24, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(311, '2026-05-03', '13:30:00', 'Estadio Walter \"Chocho\" Orsi', 44, 16, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(312, '2026-05-03', '13:30:00', 'Estadio de Deportivo 17', 27, 22, 'jugado', 1, 1, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(313, '2026-05-03', '13:30:00', 'Estadio Artemio Gastaldi', 28, 25, 'jugado', 4, 0, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(314, '2026-05-03', '13:30:00', 'Estadio de Deportivo Garré', 18, 19, 'jugado', 5, 0, 0, 0, 'Apertura', 'Tercera', 8, NULL, 2),
(315, '2026-05-09', '13:30:00', 'Estadio \"Azul\" Francisco Morero', 16, 18, 'jugado', 4, 1, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(316, '2026-05-10', '13:30:00', 'Estadio Edgardo Marduel', 24, 15, 'jugado', 0, 1, 0, 0, 'Apertura', 'Tercera', 9, '', 2),
(318, '2026-05-10', '13:30:00', 'Estadio José Sardon', 22, 28, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(319, '2026-05-10', '13:30:00', 'Estadio Demetrio Carmelo Loyarte', 19, 20, 'jugado', 1, 5, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(320, '2026-05-10', '13:30:00', 'Estadio Raul Changazzo', 26, 17, 'jugado', 1, 0, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(321, '2026-05-10', '13:30:00', 'Estadio Doctor Pippo', 25, 23, 'jugado', 5, 1, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(322, '2026-05-16', '13:30:00', 'Estadio Artemio Gastaldi', 28, 21, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(323, '2026-05-17', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 26, 'jugado', 2, 0, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(324, '2026-10-17', '13:30:00', 'Estadio de Deportivo 17', 27, 24, 'jugado', 3, 0, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(325, '2026-10-17', '13:30:00', 'Estadio de Deportivo Argentino', 20, 16, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(326, '2026-05-17', '13:30:00', 'Estadio Martin \"Pato\" Pichetto', 23, 22, 'jugado', 1, 2, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(327, '2026-05-17', '13:30:00', 'Estadio de Deportivo Garré', 18, 44, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(328, '2026-10-17', '13:30:00', 'Estadio de Unión de Bonifacio', 17, 19, 'jugado', 0, 0, 0, 0, 'Apertura', 'Tercera', 10, NULL, 2),
(329, '2026-05-23', '13:30:00', 'Estadio José Sardon', 22, 25, 'jugado', 2, 2, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(330, '2026-05-24', '13:30:00', 'Estadio Edgardo Marduel', 24, 28, 'jugado', 1, 3, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(331, '2026-05-24', '13:30:00', 'Estadio Demetrio Carmelo Loyarte', 19, 15, 'jugado', 0, 5, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(332, '2026-05-24', '13:30:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 23, 'jugado', 2, 1, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(333, '2026-05-24', '13:30:00', 'Estadio \"Azul\" Francisco Morero', 16, 17, 'jugado', 5, 0, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(334, '2026-05-24', '13:30:00', 'Estadio Raul Changazzo', 26, 27, 'jugado', 0, 2, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(335, '2026-05-24', '13:30:00', 'Estadio Walter \"Chocho\" Orsi', 44, 20, 'jugado', 2, 2, 0, 0, 'Apertura', 'Tercera', 11, NULL, 2),
(336, '2026-05-30', '13:30:00', 'Estadio Artemio Gastaldi', 28, 26, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(337, '2026-05-31', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 16, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(338, '2026-05-31', '13:30:00', 'Estadio Martin \"Pato\" Pichetto', 23, 24, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(339, '2026-05-31', '13:30:00', 'Estadio Doctor Pippo', 25, 21, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(340, '2026-05-31', '13:30:00', 'Estadio de Deportivo 17', 27, 19, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(341, '2026-05-31', '13:30:00', 'Estadio de Deportivo Argentino', 20, 18, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(342, '2026-05-31', '13:30:00', 'Estadio de Unión de Bonifacio', 17, 44, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 12, NULL, 2),
(343, '2026-06-07', '13:30:00', 'Estadio Edgardo Marduel', 24, 25, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(344, '2026-06-07', '13:30:00', 'Estadio Walter \"Chocho\" Orsi', 44, 15, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(345, '2026-06-07', '13:30:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 22, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(346, '2026-06-07', '13:30:00', 'Estadio \"Azul\" Francisco Morero', 16, 27, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(347, '2026-06-07', '13:30:00', 'Estadio Demetrio Carmelo Loyarte', 19, 28, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(348, '2026-06-07', '13:30:00', 'Estadio Raul Changazzo', 26, 23, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(349, '2026-06-07', '13:30:00', 'Estadio de Deportivo Garré', 18, 17, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 13, NULL, 2),
(350, '2026-06-13', '13:30:00', 'Estadio de Deportivo 17', 27, 44, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(351, '2026-06-14', '13:30:00', 'Estadio de Cecil A. Roberts', 15, 18, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(352, '2026-06-14', '13:30:00', 'Estadio José Sardon', 22, 24, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(353, '2026-06-14', '13:30:00', 'Estadio Artemio Gastaldi', 28, 16, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(354, '2026-06-14', '13:30:00', 'Estadio de Unión de Bonifacio', 17, 20, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, '', 2),
(355, '2026-06-14', '13:30:00', 'Estadio Martin \"Pato\" Pichetto', 23, 19, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(356, '2026-06-14', '13:30:00', 'Estadio Doctor Pippo', 25, 26, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 14, NULL, 2),
(357, '2026-06-14', '15:30:00', 'Estadio Doctor Pippo', 11, 12, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Primera', 14, NULL, 2),
(358, '2026-06-20', '13:30:00', 'Estadio de Deportivo Argentino', 20, 15, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(359, '2026-06-21', '13:30:00', 'Estadio Edgardo Marduel', 24, 21, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(360, '2026-06-21', '13:30:00', 'Estadio \"Azul\" Francisco Morero', 16, 23, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(361, '2026-06-21', '13:30:00', 'Estadio Raul Changazzo', 26, 22, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(362, '2026-06-21', '13:30:00', 'Estadio Walter \"Chocho\" Orsi', 44, 28, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(363, '2026-06-21', '13:30:00', 'Estadio Demetrio Carmelo Loyarte', 19, 25, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(364, '2026-06-21', '13:30:00', 'Estadio de Deportivo Garré', 18, 27, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Tercera', 15, NULL, 2),
(365, '2026-04-18', '11:30:00', 'Estadio de Deportivo 17', 41, 37, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(366, '2026-04-19', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 39, 'jugado', 2, 2, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(368, '2026-04-19', '11:30:00', 'Estadio de Deportivo Garré', 32, 38, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(369, '2026-04-19', '11:30:00', 'Estadio de Deportivo Argentino', 34, 35, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(370, '2026-04-19', '11:30:00', 'Estadio \"Azul\" Francisco Morero', 30, 33, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(371, '2026-04-19', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 36, 'jugado', 0, 1, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(372, '2026-04-19', '11:30:00', 'Estadio Walter \"Chocho\" Orsi', 49, 40, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 6, NULL, 2),
(373, '2026-04-26', '11:30:00', 'Estadio Edgardo Marduel', 38, 34, 'jugado', 2, 2, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(374, '2026-04-26', '11:30:00', 'Estadio José Sardon', 36, 29, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(375, '2026-04-26', '11:30:00', 'Estadio Doctor Pippo', 35, 31, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(376, '2026-04-26', '11:30:00', 'Estadio Doctor Pippo', 39, 41, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(377, '2026-04-26', '11:30:00', 'Estadio Martin \"Pato\" Pichetto', 37, 42, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(378, '2026-04-26', '11:30:00', 'Estadio Raul Changazzo', 40, 32, 'jugado', 1, 3, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(379, '2026-04-26', '11:30:00', 'Estadio Demetrio Carmelo Loyarte', 33, 49, 'jugado', 1, 4, 0, 0, 'Apertura', 'Senior', 7, NULL, 2),
(380, '2026-05-02', '11:30:00', 'Estadio de Deportivo Argentino', 34, 40, 'jugado', 4, 0, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(381, '2026-05-03', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 35, 'jugado', 4, 0, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(382, '2026-05-02', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 38, 'jugado', 1, 1, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(383, '2026-05-03', '11:30:00', 'Estadio Walter \"Chocho\" Orsi', 49, 30, 'jugado', 2, 0, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(384, '2026-05-03', '11:30:00', 'Estadio de Deportivo 17', 41, 36, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(385, '2026-05-03', '11:30:00', 'Estadio Artemio Gastaldi', 42, 39, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(386, '2026-05-03', '11:30:00', 'Estadio de Deportivo Garré', 32, 33, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 8, NULL, 2),
(387, '2026-05-09', '11:30:00', 'Estadio \"Azul\" Francisco Morero', 30, 32, 'jugado', 1, 1, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(388, '2026-05-10', '11:30:00', 'Estadio Edgardo Marduel', 38, 29, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(389, '2026-05-10', '11:30:00', 'Estadio Doctor Pippo', 35, 41, 'jugado', 3, 1, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(390, '2026-05-10', '11:30:00', 'Estadio José Sardon', 36, 42, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(391, '2026-05-10', '11:30:00', 'Estadio Demetrio Carmelo Loyarte', 33, 34, 'jugado', 0, 0, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(392, '2026-05-10', '11:30:00', 'Estadio Raul Changazzo', 40, 31, 'jugado', 2, 2, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(393, '2026-05-10', '11:30:00', 'Estadio Doctor Pippo', 39, 37, 'jugado', 3, 3, 0, 0, 'Apertura', 'Senior', 9, NULL, 2),
(394, '2026-05-10', '13:30:00', 'Estadio Roberto \"Tito\" Castaneira', 21, 27, 'jugado', 3, 0, 0, 0, 'Apertura', 'Tercera', 9, NULL, 2),
(395, '2026-05-16', '11:30:00', 'Estadio Artemio Gastaldi', 42, 35, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(396, '2026-05-17', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 40, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(397, '2026-05-17', '11:30:00', 'Estadio de Deportivo 17', 41, 38, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(398, '2026-05-17', '11:30:00', 'Estadio de Deportivo Argentino', 34, 30, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(399, '2026-05-17', '11:30:00', 'Estadio Martin \"Pato\" Pichetto', 37, 36, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(400, '2026-05-17', '11:30:00', 'Estadio de Deportivo Garré', 32, 49, 'jugado', 0, 1, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(401, '2026-05-17', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 33, 'jugado', 1, 3, 0, 0, 'Apertura', 'Senior', 10, NULL, 2),
(402, '2026-05-23', '11:30:00', 'Estadio José Sardon', 36, 39, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(403, '2026-05-24', '11:30:00', 'Estadio Edgardo Marduel', 38, 42, 'jugado', 2, 3, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(404, '2026-05-24', '11:30:00', 'Estadio Demetrio Carmelo Loyarte', 33, 29, 'jugado', 0, 2, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(405, '2026-05-24', '11:30:00', 'Estadio Doctor Pippo', 35, 37, 'jugado', 1, 5, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(406, '2026-05-24', '11:30:00', 'Estadio \"Azul\" Francisco Morero', 30, 31, 'jugado', 2, 1, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(407, '2026-05-24', '11:30:00', 'Estadio Raul Changazzo', 40, 41, 'jugado', 3, 0, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(408, '2026-05-24', '11:30:00', 'Estadio Walter \"Chocho\" Orsi', 49, 34, 'jugado', 1, 2, 0, 0, 'Apertura', 'Senior', 11, NULL, 2),
(409, '2026-05-30', '11:30:00', 'Estadio Artemio Gastaldi', 42, 40, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(410, '2026-05-31', '11:30:00', 'Estadio de Cecil A. Roberts', 29, 30, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(411, '2026-05-31', '11:30:00', 'Estadio Martin \"Pato\" Pichetto', 37, 38, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(412, '2026-05-31', '11:30:00', 'Estadio Doctor Pippo', 39, 35, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(414, '2026-05-31', '11:30:00', 'Estadio de Deportivo 17', 41, 33, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(416, '2026-05-31', '11:30:00', 'Estadio de Deportivo Argentino', 34, 32, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2),
(417, '2026-05-31', '11:30:00', 'Estadio de Unión de Bonifacio', 31, 49, 'pendiente', 0, 0, 0, 0, 'Apertura', 'Senior', 12, NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prode_codigos`
--

CREATE TABLE `prode_codigos` (
  `id` int(11) NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_at` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prode_codigos`
--

INSERT INTO `prode_codigos` (`id`, `telefono`, `codigo`, `expira_at`, `usado`, `created_at`) VALUES
(1, '+5492392529428', '482070', '2026-05-25 16:32:18', 0, '2026-05-25 19:22:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prode_fechas`
--

CREATE TABLE `prode_fechas` (
  `id` int(11) NOT NULL,
  `jornada` int(11) NOT NULL,
  `categoria` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Primera',
  `torneo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Apertura',
  `temporada_id` int(11) NOT NULL DEFAULT '2',
  `estado` enum('abierta','cerrada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierta',
  `puntos_calculados` tinyint(1) NOT NULL DEFAULT '0',
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prode_participantes`
--

CREATE TABLE `prode_participantes` (
  `id` int(11) NOT NULL,
  `alias` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `verificado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prode_participantes`
--

INSERT INTO `prode_participantes` (`id`, `alias`, `telefono`, `verificado`, `created_at`, `email`) VALUES
(1, 'Emiliano Casanova', '+5492392529428', 0, '2026-05-25 19:22:18', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prode_pronosticos`
--

CREATE TABLE `prode_pronosticos` (
  `id` int(11) NOT NULL,
  `participante_id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `jornada` int(11) NOT NULL,
  `temporada_id` int(11) NOT NULL DEFAULT '2',
  `goles_local` int(11) NOT NULL,
  `goles_visitante` int(11) NOT NULL,
  `puntos_obtenidos` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prode_sesiones`
--

CREATE TABLE `prode_sesiones` (
  `id` int(11) NOT NULL,
  `participante_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporadas`
--

CREATE TABLE `temporadas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio_inicio` smallint(6) NOT NULL,
  `anio_fin` smallint(6) NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `temporadas`
--

INSERT INTO `temporadas` (`id`, `nombre`, `slug`, `anio_inicio`, `anio_fin`, `estado`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2024/25', '2024-25', 2024, 2025, 'archivada', 0, '2026-04-09 18:30:58', '2026-04-09 18:30:58'),
(2, '2025/26', '2025-26', 2025, 2026, 'activa', 1, '2026-04-09 18:30:58', '2026-04-09 18:30:58');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipos_nombre_categoria_unique` (`nombre`,`categoria`,`temporada_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_eventos_partido` (`partido_id`),
  ADD KEY `fk_eventos_jugador` (`jugador_id`),
  ADD KEY `fk_eventos_equipo` (`equipo_id`);

--
-- Indices de la tabla `home_config`
--
ALTER TABLE `home_config`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jugadores_nombre_equipo_unique` (`nombre`,`equipo`,`temporada_id`),
  ADD KEY `fk_jugadores_equipo` (`equipo`);

--
-- Indices de la tabla `legacy_goleadores_rescatados`
--
ALTER TABLE `legacy_goleadores_rescatados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_goleadores_unique` (`jugador_nombre`,`equipo_nombre`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `noticias_slug_unique` (`slug`),
  ADD KEY `noticias_estado_idx` (`estado`),
  ADD KEY `noticias_temporada_idx` (`temporada_id`),
  ADD KEY `noticias_destacada_idx` (`destacada`);

--
-- Indices de la tabla `noticias_relacionadas`
--
ALTER TABLE `noticias_relacionadas`
  ADD PRIMARY KEY (`noticia_id`,`noticia_relacionada_id`),
  ADD KEY `fk_noticias_relacionadas_secundaria` (`noticia_relacionada_id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `partidos_unico` (`fecha`,`hora`,`local`,`visitante`,`torneo`,`temporada_id`),
  ADD KEY `fk_partidos_local` (`local`),
  ADD KEY `fk_partidos_visitante` (`visitante`);

--
-- Indices de la tabla `prode_codigos`
--
ALTER TABLE `prode_codigos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prode_codigos_telefono` (`telefono`);

--
-- Indices de la tabla `prode_fechas`
--
ALTER TABLE `prode_fechas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prode_fechas` (`jornada`,`categoria`,`torneo`,`temporada_id`);

--
-- Indices de la tabla `prode_participantes`
--
ALTER TABLE `prode_participantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prode_participantes_telefono` (`telefono`),
  ADD UNIQUE KEY `uq_prode_participantes_alias` (`alias`);

--
-- Indices de la tabla `prode_pronosticos`
--
ALTER TABLE `prode_pronosticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prode_pronosticos` (`participante_id`,`partido_id`),
  ADD KEY `fk_prode_pronosticos_partido` (`partido_id`);

--
-- Indices de la tabla `prode_sesiones`
--
ALTER TABLE `prode_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prode_sesiones_token` (`token`),
  ADD KEY `fk_prode_sesiones_participante` (`participante_id`);

--
-- Indices de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `temporadas_nombre_unique` (`nombre`),
  ADD UNIQUE KEY `temporadas_slug_unique` (`slug`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1378;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=762;

--
-- AUTO_INCREMENT de la tabla `legacy_goleadores_rescatados`
--
ALTER TABLE `legacy_goleadores_rescatados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=418;

--
-- AUTO_INCREMENT de la tabla `prode_codigos`
--
ALTER TABLE `prode_codigos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `prode_fechas`
--
ALTER TABLE `prode_fechas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prode_participantes`
--
ALTER TABLE `prode_participantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `prode_pronosticos`
--
ALTER TABLE `prode_pronosticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prode_sesiones`
--
ALTER TABLE `prode_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_eventos_equipo` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_eventos_jugador` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_eventos_partido` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `fk_jugadores_equipo` FOREIGN KEY (`equipo`) REFERENCES `equipos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `noticias_relacionadas`
--
ALTER TABLE `noticias_relacionadas`
  ADD CONSTRAINT `fk_noticias_relacionadas_principal` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_noticias_relacionadas_secundaria` FOREIGN KEY (`noticia_relacionada_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `fk_partidos_local` FOREIGN KEY (`local`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_partidos_visitante` FOREIGN KEY (`visitante`) REFERENCES `equipos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prode_pronosticos`
--
ALTER TABLE `prode_pronosticos`
  ADD CONSTRAINT `fk_prode_pronosticos_participante` FOREIGN KEY (`participante_id`) REFERENCES `prode_participantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prode_pronosticos_partido` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prode_sesiones`
--
ALTER TABLE `prode_sesiones`
  ADD CONSTRAINT `fk_prode_sesiones_participante` FOREIGN KEY (`participante_id`) REFERENCES `prode_participantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
