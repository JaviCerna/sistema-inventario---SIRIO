-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-10-2025 a las 13:55:50
-- Versión del servidor: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- Versión de PHP: 8.1.2-1ubuntu2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventory_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodegas`
--

CREATE TABLE `bodegas` (
  `id_bodega` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `direccion1` varchar(160) DEFAULT NULL,
  `ciudad` varchar(80) DEFAULT NULL,
  `pais` varchar(80) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bodegas`
--

INSERT INTO `bodegas` (`id_bodega`, `codigo`, `nombre`, `direccion1`, `ciudad`, `pais`, `activa`, `creado_en`, `actualizado_en`) VALUES
(1, '0001', 'Bodega A', 'San Salvador', 'San Salvador', 'El Salvador', 1, '2025-09-25 02:43:10', '2025-09-25 02:43:10'),
(3, 'Prueba11', 'Prueba3', 'Prueba2', 'Prueba4', 'Prueba5', 1, '2025-09-25 02:44:12', '2025-10-14 23:04:48'),
(5, '5646', 'Prueba11', 'aaaaaaaaaaaaaaa', 'aaaaaaaaaa', 'aaaaaaaaaa', 1, '2025-10-23 04:12:35', '2025-10-23 04:12:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizada_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `activa`, `creada_en`, `actualizada_en`) VALUES
(1, 'Camisas', 'Aquí debe ir la descripción de las camisas', 1, '2025-09-16 03:10:43', '2025-09-16 03:10:43'),
(2, 'Pantalones', 'Aqui debe de ir la descripción de los pantalones', 1, '2025-09-16 03:31:43', '2025-09-16 03:31:48'),
(4, 'Shorts', 'Aquí va la descripción de los shorts', 0, '2025-09-16 04:06:59', '2025-09-16 04:06:59'),
(6, 'Sudaderas', 'Aqui va la descripción', 1, '2025-09-21 17:07:27', '2025-09-21 17:09:46'),
(8, 'aaaadasdsada', 'aaaaaaaaaaaaaaaa', 1, '2025-10-14 23:39:54', '2025-10-23 03:10:33'),
(9, 'Probando sonido 122', 'aaaaaaaaaaaaaaaaaaa', 1, '2025-10-23 03:10:46', '2025-10-23 03:12:51'),
(14, '1111111111111112', 'aaaaaaaaaa222', 1, '2025-10-23 03:21:01', '2025-10-23 03:31:26'),
(15, 'Ropa de Mujer', 'Prendas de vestir diseñadas exclusivamente para el público femenino, abarcando desde el torso hasta los pies, incluyendo artículos formales, casuales, íntimos y de abrigo.', 1, '2025-10-23 04:19:22', '2025-10-23 04:19:22'),
(16, 'Ropa de Hombre', 'Prendas de vestir diseñadas exclusivamente para el público masculino, incluyendo vestimenta para el torso, parte inferior, sastrería y ropa íntima.', 1, '2025-10-23 04:19:49', '2025-10-23 04:19:49'),
(17, 'Ropa Infantil', 'Prendas de vestir diseñadas para bebés, niños y niñas, agrupando todas las necesidades de vestuario, desde recién nacidos hasta la adolescencia temprana.', 1, '2025-10-23 04:20:03', '2025-10-23 04:20:03'),
(18, 'Accesorios', 'Artículos complementarios al vestuario, incluyendo calzado, elementos de marroquinería (bolsos, carteras) y otros complementos decorativos o funcionales (joyería, cinturones, bufandas).', 1, '2025-10-23 04:20:26', '2025-10-23 04:20:26'),
(19, 'Ropa de Ocasión / Especial', 'Prendas destinadas a eventos, temporadas o usos específicos que no son parte del inventario regular durante todo el año (ej. uniformes o colecciones de temporada).', 1, '2025-10-23 04:20:43', '2025-10-23 04:20:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colores`
--

CREATE TABLE `colores` (
  `id_color` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `hex` char(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `colores`
--

INSERT INTO `colores` (`id_color`, `nombre`, `hex`) VALUES
(1, 'Negro', '#000000'),
(3, 'Blanco', '#ffffff'),
(4, 'Aqua', '#1eddce'),
(5, 'Verde', '#06e53e');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `existencias`
--

CREATE TABLE `existencias` (
  `id_existencia` bigint(20) UNSIGNED NOT NULL,
  `id_bodega` bigint(20) UNSIGNED NOT NULL,
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `cantidad` decimal(14,3) NOT NULL DEFAULT 0.000,
  `stock_minimo` decimal(14,3) NOT NULL DEFAULT 0.000,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `existencias`
--

INSERT INTO `existencias` (`id_existencia`, `id_bodega`, `id_producto`, `cantidad`, `stock_minimo`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, '11.000', '0.000', '2025-09-25 04:18:06', '2025-09-25 14:22:57'),
(2, 3, 1, '80.000', '0.000', '2025-09-25 04:18:31', '2025-09-25 04:18:31'),
(6, 3, 3, '3.000', '0.000', '2025-09-28 13:03:15', '2025-09-28 13:03:15'),
(7, 1, 5, '50.000', '0.000', '2025-09-28 16:19:52', '2025-09-28 16:19:52'),
(8, 3, 4, '30.000', '0.000', '2025-10-02 21:14:33', '2025-10-02 21:14:33'),
(10, 3, 8, '34.000', '0.000', '2025-10-23 04:14:06', '2025-10-23 04:14:06'),
(11, 5, 8, '34.000', '0.000', '2025-10-23 04:14:37', '2025-10-23 04:14:37'),
(12, 5, 3, '100.000', '0.000', '2025-10-23 15:24:35', '2025-10-23 15:29:45'),
(20, 1, 3, '2.000', '0.000', '2025-10-23 15:30:16', '2025-10-23 15:30:16'),
(22, 1, 4, '2.000', '0.000', '2025-10-23 15:33:00', '2025-10-23 15:33:00'),
(23, 5, 5, '90.000', '0.000', '2025-10-23 15:34:23', '2025-10-23 15:34:23'),
(25, 5, 1, '2.000', '0.000', '2025-10-23 15:36:25', '2025-10-23 15:36:25'),
(27, 3, 5, '2.000', '0.000', '2025-10-23 15:39:18', '2025-10-23 15:39:18'),
(29, 5, 4, '2.000', '0.000', '2025-10-23 15:40:01', '2025-10-23 15:40:02'),
(31, 1, 9, '2.000', '0.000', '2025-10-23 15:40:41', '2025-10-23 15:40:41'),
(33, 5, 9, '1.000', '0.000', '2025-10-23 15:42:46', '2025-10-23 15:42:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_auditoria`
--

CREATE TABLE `log_auditoria` (
  `id_log` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tabla_afectada` varchar(60) NOT NULL,
  `registro_id` bigint(20) NOT NULL,
  `accion` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
  `detalle_cambios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalle_cambios`)),
  `ip_origen` varchar(45) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `log_auditoria`
--

INSERT INTO `log_auditoria` (`id_log`, `id_usuario`, `tabla_afectada`, `registro_id`, `accion`, `detalle_cambios`, `ip_origen`, `fecha_hora`) VALUES
(1, 2, 'categorias', 7, 'CREATE', '{\"nombre\":\"Calcetines\",\"descripcion\":\"\",\"activa\":1}', '::1', '2025-10-14 23:15:12'),
(2, 2, 'categorias', 7, 'UPDATE', '{\"descripcion\":{\"old\":\"\",\"new\":\"Aqui va la descripción\"}}', '::1', '2025-10-14 23:16:26'),
(3, 2, 'categorias', 7, 'DELETE', '{\"registro_eliminado\":\"Calcetines\"}', '::1', '2025-10-14 23:16:37'),
(4, 2, 'subcategorias', 5, 'CREATE', '{\"id_categoria\":1,\"nombre\":\"Prueba\",\"activa\":1}', '::1', '2025-10-14 23:25:52'),
(5, 2, 'subcategorias', 5, 'UPDATE', '{\"nombre\":{\"old\":\"Prueba\",\"new\":\"1Prueba\"}}', '::1', '2025-10-14 23:27:11'),
(6, 2, 'bodegas', 4, 'CREATE', '{\"codigo\":\"346567\",\"nombre\":\"Prueba56789\",\"ciudad\":\"San Salvador\",\"pais\":\"El Salvador\",\"activa\":1}', '::1', '2025-10-14 23:32:13'),
(7, 2, 'bodegas', 4, 'UPDATE', '{\"direccion1\":{\"old\":\"La Paz\",\"new\":\"La Pazzzzzzzz\"}}', '::1', '2025-10-14 23:32:40'),
(8, 2, 'bodegas', 4, 'DELETE', '{\"registro_eliminado\":\"Prueba56789\",\"codigo\":\"346567\"}', '::1', '2025-10-14 23:32:48'),
(9, 2, 'categorias', 8, 'CREATE', '{\"nombre\":\"adasdsada\",\"descripcion\":\"aaaaaaaaaaaaaaaa\",\"activa\":1}', '::1', '2025-10-14 23:39:54'),
(10, 2, 'subcategorias', 5, 'UPDATE', '{\"id_categoria\":{\"old\":1,\"new\":8}}', '::1', '2025-10-14 23:43:20'),
(11, 2, 'productos', 6, 'CREATE', '{\"sku\":\"88888888888888\",\"nombre\":\"Producto\",\"costo_base\":56,\"activo\":1}', '::1', '2025-10-14 23:44:26'),
(12, 2, 'productos', 6, 'UPDATE', '{\"nombre\":{\"old\":\"Producto\",\"new\":\"Producto23\"},\"costo_base\":{\"old\":\"56.00\",\"new\":\"56\"}}', '::1', '2025-10-14 23:53:38'),
(13, 2, 'subcategorias', 6, 'CREATE', '{\"id_categoria\":1,\"nombre\":\"gffffffffffffff\",\"activa\":1}', '::1', '2025-10-14 23:54:08'),
(14, 2, 'productos', 7, 'CREATE', '{\"sku\":\"3243536\",\"nombre\":\"rewrwer\",\"costo_base\":5646.5,\"activo\":1}', '::1', '2025-10-14 23:57:23'),
(15, 2, 'productos', 7, 'UPDATE', '{\"nombre\":{\"old\":\"rewrwer\",\"new\":\"rewrweraaaa\"},\"costo_base\":{\"old\":\"5646.50\",\"new\":\"5646.5\"}}', '::1', '2025-10-14 23:57:39'),
(16, 2, 'productos', 7, 'DELETE', '{\"registro_eliminado\":\"rewrweraaaa\",\"sku\":\"3243536\"}', '::1', '2025-10-14 23:57:50'),
(17, 2, 'users', 4, 'CREATE', '{\"email\":\"newcuentahsr@gmail.com\",\"full_name\":\"Dain\",\"role\":\"admin\"}', '::1', '2025-10-15 00:22:51'),
(18, 2, 'users', 5, 'CREATE', '{\"email\":\"prueba@gmail.com\",\"full_name\":\"Prueba\",\"role\":\"admin\"}', '::1', '2025-10-16 23:09:14'),
(19, 2, 'users', 5, 'UPDATE', '{\"role\":{\"old\":\"admin\",\"new\":\"employee\"}}', '::1', '2025-10-16 23:09:23'),
(20, 2, 'users', 6, 'CREATE', '{\"email\":\"pedro1235@gmail.com\",\"full_name\":\"Pedro\",\"role\":\"admin\"}', '::1', '2025-10-17 00:34:26'),
(21, 2, 'categorias', 8, 'UPDATE', '{\"nombre\":{\"old\":\"adasdsada\",\"new\":\"aaaadasdsada\"}}', '127.0.0.1', '2025-10-23 03:10:33'),
(22, 2, 'categorias', 9, 'CREATE', '{\"nombre\":\"Probando sonido 123\",\"descripcion\":\"aaaaaaaaaaaaaaaaaaa\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:10:46'),
(23, 2, 'categorias', 9, 'UPDATE', '{\"nombre\":{\"old\":\"Probando sonido 123\",\"new\":\"Probando sonido 12\"}}', '127.0.0.1', '2025-10-23 03:12:35'),
(24, 2, 'categorias', 9, 'UPDATE', '{\"nombre\":{\"old\":\"Probando sonido 12\",\"new\":\"Probando sonido 122\"}}', '::1', '2025-10-23 03:12:51'),
(25, 2, 'categorias', 10, 'CREATE', '{\"nombre\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt\",\"descripcion\":\"tttttttttttttttttttttttttttttttttttttttff\",\"activa\":1}', '::1', '2025-10-23 03:13:12'),
(26, 2, 'categorias', 10, 'UPDATE', '{\"nombre\":{\"old\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt\",\"new\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt22\"}}', '127.0.0.1', '2025-10-23 03:14:03'),
(27, 2, 'categorias', 10, 'UPDATE', '{\"nombre\":{\"old\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt22\",\"new\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt225\"}}', '127.0.0.1', '2025-10-23 03:14:27'),
(28, 2, 'categorias', 11, 'CREATE', '{\"nombre\":\"Jaaaaa\",\"descripcion\":\"aaaaaaaaaaaaa\",\"activa\":0}', '127.0.0.1', '2025-10-23 03:15:10'),
(29, 2, 'categorias', 11, 'DELETE', '{\"registro_eliminado\":\"Jaaaaa\"}', '127.0.0.1', '2025-10-23 03:15:15'),
(30, 2, 'categorias', 12, 'CREATE', '{\"nombre\":\"dddddddddddddd\",\"descripcion\":\"\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:15:28'),
(31, 2, 'categorias', 12, 'DELETE', '{\"registro_eliminado\":\"dddddddddddddd\"}', '127.0.0.1', '2025-10-23 03:15:33'),
(32, 2, 'subcategorias', 7, 'CREATE', '{\"id_categoria\":10,\"nombre\":\"sgsgdfgdfgdg\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:15:51'),
(33, 2, 'categorias', 10, 'DELETE', '{\"registro_eliminado\":\"TTTTTtttttttttttttttttttttttttttttttttttttttttttt225\"}', '127.0.0.1', '2025-10-23 03:15:57'),
(34, 2, 'categorias', 13, 'CREATE', '{\"nombre\":\"11111111111111\",\"descripcion\":\"\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:16:19'),
(35, 2, 'subcategorias', 8, 'CREATE', '{\"id_categoria\":13,\"nombre\":\"111111111111111111111111111111\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:16:29'),
(36, 2, 'categorias', 13, 'DELETE', '{\"registro_eliminado\":\"11111111111111\"}', '127.0.0.1', '2025-10-23 03:16:37'),
(37, 2, 'categorias', 14, 'CREATE', '{\"nombre\":\"111111111111111\",\"descripcion\":\"\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:21:01'),
(38, 2, 'subcategorias', 9, 'CREATE', '{\"id_categoria\":14,\"nombre\":\"111111111111111111\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:21:11'),
(39, 2, 'categorias', 14, 'UPDATE', '{\"descripcion\":{\"old\":\"\",\"new\":\"aaaaaaaaaa\"}}', '127.0.0.1', '2025-10-23 03:22:52'),
(40, 2, 'productos', 8, 'CREATE', '{\"sku\":\"5343252\",\"nombre\":\"Prueba\",\"costo_base\":34,\"activo\":1}', '127.0.0.1', '2025-10-23 03:23:25'),
(41, 2, 'categorias', 14, 'UPDATE', '{\"nombre\":{\"old\":\"111111111111111\",\"new\":\"1111111111111112\"}}', '127.0.0.1', '2025-10-23 03:23:38'),
(42, 2, 'categorias', 14, 'UPDATE', '{\"descripcion\":{\"old\":\"aaaaaaaaaa\",\"new\":\"aaaaaaaaaa222\"}}', '127.0.0.1', '2025-10-23 03:31:26'),
(43, 2, 'subcategorias', 10, 'CREATE', '{\"id_categoria\":14,\"nombre\":\"2222222222\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:39:35'),
(44, 2, 'subcategorias', 10, 'UPDATE', '{\"nombre\":{\"old\":\"2222222222\",\"new\":\"12222222222\"}}', '127.0.0.1', '2025-10-23 03:40:09'),
(45, 2, 'subcategorias', 11, 'CREATE', '{\"id_categoria\":14,\"nombre\":\"5555555\",\"activa\":1}', '127.0.0.1', '2025-10-23 03:40:18'),
(46, 2, 'subcategorias', 10, 'DELETE', '{\"registro_eliminado\":\"12222222222\",\"id_categoria\":14}', '127.0.0.1', '2025-10-23 03:40:29'),
(47, 2, 'bodegas', 5, 'CREATE', '{\"codigo\":\"5646\",\"nombre\":\"Prueba1\",\"ciudad\":\"aaaaaaaaaa\",\"pais\":\"aaaaaaaaaa\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:12:35'),
(48, 2, 'bodegas', 5, 'UPDATE', '{\"nombre\":{\"old\":\"Prueba1\",\"new\":\"Prueba11\"}}', '127.0.0.1', '2025-10-23 04:12:50'),
(49, 2, 'bodegas', 6, 'CREATE', '{\"codigo\":\"333333333\",\"nombre\":\"111111111111111\",\"ciudad\":\"111111111111\",\"pais\":\"1\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:13:02'),
(50, 2, 'bodegas', 6, 'DELETE', '{\"registro_eliminado\":\"111111111111111\",\"codigo\":\"333333333\"}', '127.0.0.1', '2025-10-23 04:13:07'),
(51, 2, 'movimientos_inventario', 10, 'CREATE', '{\"id_bodega\":3,\"id_producto\":8,\"tipo\":\"IN\",\"cantidad\":34}', '127.0.0.1', '2025-10-23 04:14:06'),
(52, 2, 'movimientos_inventario', 11, 'CREATE', '{\"id_bodega\":5,\"id_producto\":8,\"tipo\":\"IN\",\"cantidad\":34}', '127.0.0.1', '2025-10-23 04:14:37'),
(53, 2, 'categorias', 15, 'CREATE', '{\"nombre\":\"Ropa de Mujer\",\"descripcion\":\"Prendas de vestir diseñadas exclusivamente para el público femenino, abarcando desde el torso hasta los pies, incluyendo artículos formales, casuales, íntimos y de abrigo.\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:19:22'),
(54, 2, 'categorias', 16, 'CREATE', '{\"nombre\":\"Ropa de Hombre\",\"descripcion\":\"Prendas de vestir diseñadas exclusivamente para el público masculino, incluyendo vestimenta para el torso, parte inferior, sastrería y ropa íntima.\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:19:49'),
(55, 2, 'categorias', 17, 'CREATE', '{\"nombre\":\"Ropa Infantil\",\"descripcion\":\"Prendas de vestir diseñadas para bebés, niños y niñas, agrupando todas las necesidades de vestuario, desde recién nacidos hasta la adolescencia temprana.\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:20:03'),
(56, 2, 'categorias', 18, 'CREATE', '{\"nombre\":\"Accesorios\",\"descripcion\":\"Artículos complementarios al vestuario, incluyendo calzado, elementos de marroquinería (bolsos, carteras) y otros complementos decorativos o funcionales (joyería, cinturones, bufandas).\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:20:26'),
(57, 2, 'categorias', 19, 'CREATE', '{\"nombre\":\"Ropa de Ocasión \\/ Especial\",\"descripcion\":\"Prendas destinadas a eventos, temporadas o usos específicos que no son parte del inventario regular durante todo el año (ej. uniformes o colecciones de temporada).\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:20:43'),
(58, 2, 'subcategorias', 12, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Parte Superior\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:21:23'),
(59, 2, 'subcategorias', 13, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Parte Inferior\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:22:13'),
(60, 2, 'subcategorias', 14, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Vestidos y Monos\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:22:30'),
(61, 2, 'subcategorias', 15, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Abrigos y Chaquetas\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:22:48'),
(62, 2, 'subcategorias', 16, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Ropa Interior y de Dormir\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:23:06'),
(63, 2, 'subcategorias', 17, 'CREATE', '{\"id_categoria\":15,\"nombre\":\"Ropa Deportiva\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:23:30'),
(64, 2, 'productos', 6, 'DELETE', '{\"registro_eliminado\":\"Producto23\",\"sku\":\"88888888888888\"}', '127.0.0.1', '2025-10-23 04:24:59'),
(65, 2, 'subcategorias', 11, 'DELETE', '{\"registro_eliminado\":\"5555555\",\"id_categoria\":14}', '127.0.0.1', '2025-10-23 04:25:35'),
(66, 2, 'subcategorias', 6, 'DELETE', '{\"registro_eliminado\":\"gffffffffffffff\",\"id_categoria\":1}', '127.0.0.1', '2025-10-23 04:25:41'),
(67, 2, 'subcategorias', 5, 'DELETE', '{\"registro_eliminado\":\"1Prueba\",\"id_categoria\":8}', '127.0.0.1', '2025-10-23 04:25:44'),
(68, 2, 'subcategorias', 18, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Parte Superior\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:27:26'),
(69, 2, 'subcategorias', 19, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Parte Inferior\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:27:50'),
(70, 2, 'subcategorias', 20, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Trajes y Sastrería\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:28:05'),
(71, 2, 'subcategorias', 21, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Abrigos y Chaquetas\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:28:27'),
(72, 2, 'subcategorias', 22, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Ropa Interior y de Dormir\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:28:42'),
(73, 2, 'subcategorias', 23, 'CREATE', '{\"id_categoria\":16,\"nombre\":\"Ropa Deportiva\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:29:19'),
(74, 2, 'subcategorias', 25, 'CREATE', '{\"id_categoria\":17,\"nombre\":\"Bebés (0-24 meses)\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:30:37'),
(75, 2, 'subcategorias', 26, 'CREATE', '{\"id_categoria\":17,\"nombre\":\"Niña\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:30:58'),
(76, 2, 'subcategorias', 27, 'CREATE', '{\"id_categoria\":17,\"nombre\":\"Niño\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:31:16'),
(77, 2, 'subcategorias', 28, 'CREATE', '{\"id_categoria\":17,\"nombre\":\"Calzado infantil\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:31:38'),
(78, 2, 'subcategorias', 29, 'CREATE', '{\"id_categoria\":18,\"nombre\":\"Calzado\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:32:57'),
(79, 2, 'subcategorias', 30, 'CREATE', '{\"id_categoria\":18,\"nombre\":\"Marroquinería\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:33:21'),
(80, 2, 'subcategorias', 31, 'CREATE', '{\"id_categoria\":18,\"nombre\":\"Complementos\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:33:44'),
(81, 2, 'subcategorias', 32, 'CREATE', '{\"id_categoria\":18,\"nombre\":\"Joyería\\/Bisutería\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:34:01'),
(82, 2, 'subcategorias', 33, 'CREATE', '{\"id_categoria\":19,\"nombre\":\"Temporada\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:34:26'),
(83, 2, 'subcategorias', 34, 'CREATE', '{\"id_categoria\":19,\"nombre\":\"Uniforme\",\"activa\":1}', '127.0.0.1', '2025-10-23 04:34:39'),
(84, 2, 'productos', 2, 'DELETE', '{\"registro_eliminado\":\"Prueba\",\"sku\":\"000000367\"}', '127.0.0.1', '2025-10-23 04:44:59'),
(85, 2, 'movimientos_inventario', 12, 'CREATE', '{\"id_bodega\":5,\"id_producto\":3,\"tipo\":\"IN\",\"cantidad\":45}', '127.0.0.1', '2025-10-23 15:24:35'),
(86, 2, 'movimientos_inventario', 13, 'CREATE', '{\"id_bodega\":5,\"id_producto\":3,\"tipo\":\"IN\",\"cantidad\":5}', '127.0.0.1', '2025-10-23 15:24:58'),
(87, 2, 'movimientos_inventario', 14, 'DELETE', '{\"id_bodega\":5,\"id_producto\":3,\"tipo\":\"OUT\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:26:44'),
(88, 2, 'movimientos_inventario', 15, 'CREATE', '{\"id_bodega\":5,\"id_producto\":3,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:29:45'),
(89, 2, 'movimientos_inventario', 16, 'CREATE', '{\"id_bodega\":1,\"id_producto\":3,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:30:16'),
(90, 2, 'movimientos_inventario', 17, 'CREATE', '{\"id_bodega\":1,\"id_producto\":4,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:33:00'),
(91, 2, 'movimientos_inventario', 18, 'CREATE', '{\"id_bodega\":5,\"id_producto\":5,\"tipo\":\"IN\",\"cantidad\":45}', '127.0.0.1', '2025-10-23 15:34:23'),
(92, 2, 'movimientos_inventario', 19, 'CREATE', '{\"id_bodega\":5,\"id_producto\":1,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:36:25'),
(93, 2, 'movimientos_inventario', 20, 'CREATE', '{\"id_bodega\":3,\"id_producto\":5,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:39:18'),
(94, 2, 'movimientos_inventario', 21, 'CREATE', '{\"id_bodega\":5,\"id_producto\":4,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:40:02'),
(95, 2, 'productos', 9, 'CREATE', '{\"sku\":\"44555555555\",\"nombre\":\"1111111111\",\"costo_base\":45,\"activo\":1}', '127.0.0.1', '2025-10-23 15:40:30'),
(96, 2, 'movimientos_inventario', 22, 'CREATE', '{\"id_bodega\":1,\"id_producto\":9,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:40:41'),
(97, 2, 'movimientos_inventario', 23, 'CREATE', '{\"id_bodega\":5,\"id_producto\":9,\"tipo\":\"IN\",\"cantidad\":1}', '127.0.0.1', '2025-10-23 15:42:46'),
(98, 2, 'productos', 9, 'UPDATE', '{\"costo_base\":{\"old\":\"45.00\",\"new\":\"45\"}}', '127.0.0.1', '2025-10-23 18:16:42'),
(99, 2, 'productos', 9, 'UPDATE', '{\"sku\":{\"old\":\"44555555555\",\"new\":\"445555555552\"},\"costo_base\":{\"old\":\"45.00\",\"new\":\"45\"}}', '127.0.0.1', '2025-10-23 18:20:50'),
(100, 2, 'productos', 9, 'UPDATE', '{\"sku\":{\"old\":\"445555555552\",\"new\":\"44555555555\"},\"costo_base\":{\"old\":\"45.00\",\"new\":\"45\"}}', '127.0.0.1', '2025-10-23 18:22:01'),
(101, 2, 'users', 7, 'CREATE', '{\"email\":\"1234@gmail.com\",\"full_name\":\"Dain\",\"role\":\"admin\"}', '127.0.0.1', '2025-10-23 18:51:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id_movimiento` bigint(20) UNSIGNED NOT NULL,
  `id_bodega` bigint(20) UNSIGNED NOT NULL,
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `tipo` enum('IN','OUT','AJUSTE') NOT NULL,
  `cantidad` decimal(14,3) NOT NULL,
  `costo_unitario` decimal(12,4) DEFAULT NULL,
  `motivo` varchar(160) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_evento` datetime NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id_movimiento`, `id_bodega`, `id_producto`, `tipo`, `cantidad`, `costo_unitario`, `motivo`, `id_usuario`, `fecha_evento`, `creado_en`) VALUES
(1, 1, 1, 'IN', '30.000', '5.0000', 'Dan ganas', NULL, '2025-09-24 22:17:00', '2025-09-25 04:18:06'),
(2, 3, 1, 'IN', '80.000', '5.0000', 'Dan ganas', NULL, '2025-09-24 22:18:00', '2025-09-25 04:18:31'),
(3, 1, 1, 'AJUSTE', '10.000', '5.0000', NULL, NULL, '2025-09-25 07:55:00', '2025-09-25 13:55:08'),
(4, 1, 1, 'OUT', '30.000', '5.0000', NULL, NULL, '2025-09-25 07:55:00', '2025-09-25 13:55:24'),
(5, 1, 1, 'AJUSTE', '1.000', '5.0000', NULL, NULL, '2025-09-25 08:22:00', '2025-09-25 14:22:57'),
(6, 3, 3, 'IN', '3.000', '25.0000', NULL, NULL, '2025-09-28 07:03:00', '2025-09-28 13:03:15'),
(7, 1, 5, 'IN', '50.000', '5.0000', 'Ventas del dia', NULL, '2025-09-28 10:19:00', '2025-09-28 16:19:52'),
(8, 3, 4, 'IN', '30.000', '45.0000', NULL, NULL, '2025-10-02 15:14:00', '2025-10-02 21:14:33'),
(12, 5, 3, 'IN', '45.000', '56.0000', NULL, 2, '2025-10-22 03:43:00', '2025-10-23 15:24:35'),
(13, 5, 3, 'IN', '5.000', '60.0000', NULL, 2, '2025-10-15 03:24:00', '2025-10-23 15:24:58'),
(14, 5, 3, 'OUT', '1.000', '56.0000', NULL, 2, '2025-10-15 03:24:00', '2025-10-23 15:26:44'),
(15, 5, 3, 'IN', '1.000', '56.0000', NULL, 2, '2025-10-15 04:35:00', '2025-10-23 15:29:45'),
(16, 1, 3, 'IN', '1.000', '5.0000', NULL, 2, '2025-10-15 02:32:00', '2025-10-23 15:30:16'),
(17, 1, 4, 'IN', '1.000', '34.0000', NULL, 2, '2025-10-23 03:24:00', '2025-10-23 15:33:00'),
(18, 5, 5, 'IN', '45.000', '5.0000', NULL, 2, '2025-10-08 05:34:00', '2025-10-23 15:34:23'),
(19, 5, 1, 'IN', '1.000', '1.0000', NULL, 2, '3453-05-31 04:55:00', '2025-10-23 15:36:25'),
(20, 3, 5, 'IN', '1.000', '56.0000', NULL, 2, '2025-10-23 05:46:00', '2025-10-23 15:39:18'),
(21, 5, 4, 'IN', '1.000', '4.0000', NULL, 2, '2025-10-07 23:45:00', '2025-10-23 15:40:01'),
(22, 1, 9, 'IN', '1.000', '56.0000', NULL, 2, '2025-10-07 03:45:00', '2025-10-23 15:40:41'),
(23, 5, 9, 'IN', '1.000', '5.0000', NULL, 2, '2025-10-23 04:35:00', '2025-10-23 15:42:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 2, '619b4fca0bacea51a1cd8caaa7aa4189674ec9af3bc45e34', '2025-08-30 19:40:33', 0, '2025-08-30 23:40:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precios_producto`
--

CREATE TABLE `precios_producto` (
  `id_precio` bigint(20) UNSIGNED NOT NULL,
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `tipo_precio` enum('RETAIL','MAYOREO','PROMO') NOT NULL DEFAULT 'RETAIL',
  `moneda` char(3) NOT NULL DEFAULT 'USD',
  `precio` decimal(12,2) NOT NULL,
  `vigente_desde` datetime NOT NULL DEFAULT current_timestamp(),
  `vigente_hasta` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` bigint(20) UNSIGNED NOT NULL,
  `id_categoria` bigint(20) UNSIGNED NOT NULL,
  `id_subcategoria` bigint(20) UNSIGNED NOT NULL,
  `unidades` int(11) NOT NULL DEFAULT 0,
  `id_talla` bigint(20) UNSIGNED DEFAULT NULL,
  `id_color` bigint(20) UNSIGNED DEFAULT NULL,
  `sku` varchar(40) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `costo_base` decimal(12,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `id_subcategoria`, `unidades`, `id_talla`, `id_color`, `sku`, `nombre`, `descripcion`, `costo_base`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, 93, 2, 3, '00001', 'Camisa', 'Aqui va la descripción', '5.00', 1, '2025-09-25 04:16:06', '2025-10-23 15:36:25'),
(3, 2, 3, 105, 3, 1, '00004567', 'Jeans', 'Aquí va la descripción', '25.00', 1, '2025-09-28 13:01:16', '2025-10-23 15:30:16'),
(4, 6, 4, 34, 2, 4, '4568677', 'Sueter', 'Aquí va la descripción', '50.00', 0, '2025-09-28 13:04:00', '2025-10-23 15:40:02'),
(5, 1, 1, 142, 2, 5, '0000089', 'Camisas manga larga', 'Aqui va la descripción', '5.00', 1, '2025-09-28 16:18:53', '2025-10-23 15:39:18'),
(8, 14, 9, 569, 4, 3, '5343252', 'Prueba', 'aaaaaaaaaa', '34.00', 1, '2025-10-23 03:23:25', '2025-10-23 03:23:25'),
(9, 14, 9, 3, 3, 1, '44555555555', '1111111111', NULL, '45.00', 1, '2025-10-23 15:40:30', '2025-10-23 18:22:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id_subcategoria` bigint(20) UNSIGNED NOT NULL,
  `id_categoria` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizada_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subcategorias`
--

INSERT INTO `subcategorias` (`id_subcategoria`, `id_categoria`, `nombre`, `descripcion`, `activa`, `creada_en`, `actualizada_en`) VALUES
(1, 1, 'Camisa para niño', 'Aqui va la descripción', 1, '2025-09-16 18:23:05', '2025-09-16 18:23:32'),
(3, 2, 'Pantalones de vestir', 'Pantalones de vestir', 1, '2025-09-18 02:29:49', '2025-09-18 02:29:49'),
(4, 6, 'Cárdigan', 'Tipos de sudadera', 1, '2025-09-28 13:02:32', '2025-09-28 13:02:32'),
(9, 14, '111111111111111111', '', 1, '2025-10-23 03:21:11', '2025-10-23 03:21:11'),
(12, 15, 'Parte Superior', 'Incluye todas las prendas diseñadas para cubrir el torso y la parte superior del cuerpo, como camisetas básicas, elegantes blusas, suéteres para el frío y tops de moda.', 1, '2025-10-23 04:21:23', '2025-10-23 04:21:23'),
(13, 15, 'Parte Inferior', 'Abarca las prendas destinadas a cubrir la parte inferior del cuerpo y las piernas, como pantalones (de vestir, casuales, denim), faldas de diferentes largos y shorts.', 1, '2025-10-23 04:22:13', '2025-10-23 04:22:13'),
(14, 15, 'Vestidos y Monos', 'Constituida por prendas de una sola pieza que cubren desde el torso hasta la parte inferior. Incluye vestidos para diversas ocasiones y monos/enterizos (jumpsuits).', 1, '2025-10-23 04:22:29', '2025-10-23 04:22:29'),
(15, 15, 'Abrigos y Chaquetas', 'Reúne las prendas de vestir exteriores que se usan para protección contra el clima (frío o lluvia) o como complemento de estilo, como abrigos pesados, cazadoras y blazers.', 1, '2025-10-23 04:22:48', '2025-10-23 04:22:48'),
(16, 15, 'Ropa Interior y de Dormir', 'Comprende prendas íntimas, de soporte y las diseñadas específicamente para usarse durante el descanso o en casa, como sujetadores, bragas, lencería y pijamas.', 1, '2025-10-23 04:23:06', '2025-10-23 04:23:06'),
(17, 15, 'Ropa Deportiva', 'Incluye prendas funcionales y cómodas diseñadas específicamente para la práctica de ejercicio y actividades físicas, como leggings técnicos, tops y sudaderas deportivas.', 1, '2025-10-23 04:23:30', '2025-10-23 04:23:30'),
(18, 16, 'Parte Superior', 'Artículos para cubrir el torso masculino, que van desde camisetas casuales (t-shirts) y polos, hasta camisas de vestir formales y suéteres de punto.', 1, '2025-10-23 04:27:26', '2025-10-23 04:27:26'),
(19, 16, 'Parte Inferior', 'Prendas que cubren la parte inferior del cuerpo, incluyendo pantalones de mezclilla (jeans), pantalones chinos, pantalones de vestir y bermudas/shorts.', 1, '2025-10-23 04:27:50', '2025-10-23 04:27:50'),
(20, 16, 'Trajes y Sastrería', 'Piezas formales y coordinadas diseñadas para ocasiones de negocios o eventos especiales, como trajes de dos o tres piezas, sacos y chalecos.', 1, '2025-10-23 04:28:05', '2025-10-23 04:28:05'),
(21, 16, 'Abrigos y Chaquetas', 'Prendas exteriores masculinas para diversas temperaturas y estilos, como parkas, gabardinas, chaquetas de cuero y abrigos de lana.', 1, '2025-10-23 04:28:27', '2025-10-23 04:28:27'),
(22, 16, 'Ropa Interior y de Dormir', 'Prendas íntimas y las destinadas al descanso, como bóxers, calzoncillos y conjuntos de pijama.', 1, '2025-10-23 04:28:42', '2025-10-23 04:28:42'),
(23, 16, 'Ropa Deportiva', 'Ropa especializada para actividades físicas, incluyendo camisetas técnicas, pantalones de chándal y ropa de compresión.', 1, '2025-10-23 04:29:19', '2025-10-23 04:29:19'),
(25, 17, 'Bebés (0-24 meses)', 'Bodies, Peleles, Conjuntos, Pijamas, Accesorios de Bebé.', 1, '2025-10-23 04:30:37', '2025-10-23 04:30:37'),
(26, 17, 'Niña', 'Vestidos, Faldas, Camisetas, Leggings, Conjuntos.', 1, '2025-10-23 04:30:58', '2025-10-23 04:30:58'),
(27, 17, 'Niño', 'Camisetas, Pantalones, Sudaderas, Ropa de Exterior.', 1, '2025-10-23 04:31:16', '2025-10-23 04:31:16'),
(28, 17, 'Calzado infantil', 'Zapatillas, Sandalias, Botas.', 1, '2025-10-23 04:31:38', '2025-10-23 04:31:38'),
(29, 18, 'Calzado', 'Artículos para cubrir y proteger el pie, que se venden en pares. Incluye zapatillas, botas, zapatos formales, sandalias y tacones.', 1, '2025-10-23 04:32:57', '2025-10-23 04:32:57'),
(30, 18, 'Marroquinería', 'Artículos de cuero o materiales similares, utilizados para portar objetos personales, como bolsos, carteras, mochilas y monederos.', 1, '2025-10-23 04:33:21', '2025-10-23 04:33:21'),
(31, 18, 'Complementos', 'Artículos que se utilizan para complementar un atuendo o para protección contra el clima, como cinturones, gorros, bufandas y guantes.', 1, '2025-10-23 04:33:44', '2025-10-23 04:33:44'),
(32, 18, 'Joyería/Bisutería', 'Objetos decorativos que se usan en el cuerpo, como collares, pulseras, pendientes y anillos.', 1, '2025-10-23 04:34:01', '2025-10-23 04:34:01'),
(33, 19, 'Temporada', 'Navidad, Verano/Playa (Trajes de Baño), Halloween.', 1, '2025-10-23 04:34:26', '2025-10-23 04:34:26'),
(34, 19, 'Uniforme', 'Ropa de Trabajo, Uniformes Escolares.', 1, '2025-10-23 04:34:39', '2025-10-23 04:34:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

CREATE TABLE `tallas` (
  `id_talla` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(16) NOT NULL,
  `nombre` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tallas`
--

INSERT INTO `tallas` (`id_talla`, `codigo`, `nombre`) VALUES
(1, '0001', 'X'),
(2, '0002', 'S'),
(3, '0003', 'M'),
(4, '0004', 'L'),
(5, '0005', 'XS'),
(6, '0006', 'XL'),
(7, '66666', 'XXL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidades`
--

CREATE TABLE `unidades` (
  `id_unidad` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(16) NOT NULL,
  `nombre` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` enum('admin','employee') DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `role`, `created_at`) VALUES
(2, 'javier13coc@gmail.com', '$2y$10$o3x4FC4QBgzbhybYmF6AqOwR2cBb.yKKChHLB3r.Az4W/NetgkO.m', 'Javier Cerna', 'admin', '2025-08-30 23:35:04'),
(3, 'javier19coc@gmail.com', '$2y$10$o3x4FC4QBgzbhybYmF6AqOwR2cBb.yKKChHLB3r.Az4W/NetgkO.m', 'Antonio Cornejo', 'employee', '2025-08-30 23:39:46'),
(4, 'newcuentahsr@gmail.com', '$2y$10$ehmK1zO6X0zoSGLi8ywC.efYLVpRgNq/ZF0RDRPY9Px7Y1QIksqlu', 'Dain', 'admin', '2025-10-15 00:22:51'),
(5, 'prueba@gmail.com', '$2y$10$ccQL.cOz8gAXQR5n7wuWRO6SWrO6Q8NwgPsIEwOT/CXAJ2/pMFrSm', 'Prueba', 'employee', '2025-10-16 23:09:14'),
(6, 'pedro1235@gmail.com', '$2y$10$3iJl/XhUye7qjDSa73T1vuhc9.cfvVcebpVK0jIPO8l/rSzKc0.yy', 'Pedro', 'admin', '2025-10-17 00:34:26'),
(7, '1234@gmail.com', '$2y$10$rTsD.EkEbukjwkyZOhbZ8.hicp01B7.StF6VRil5XIzNoTOnamXYe', 'Dain', 'admin', '2025-10-23 18:51:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  ADD PRIMARY KEY (`id_bodega`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `colores`
--
ALTER TABLE `colores`
  ADD PRIMARY KEY (`id_color`);

--
-- Indices de la tabla `existencias`
--
ALTER TABLE `existencias`
  ADD PRIMARY KEY (`id_existencia`),
  ADD UNIQUE KEY `uq_existencia` (`id_bodega`,`id_producto`),
  ADD UNIQUE KEY `unique_existencia` (`id_bodega`,`id_producto`),
  ADD KEY `fk_exi_producto` (`id_producto`);

--
-- Indices de la tabla `log_auditoria`
--
ALTER TABLE `log_auditoria`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_usuario` (`id_usuario`),
  ADD KEY `idx_log_tabla_registro` (`tabla_afectada`,`registro_id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_mov_bodega` (`id_bodega`),
  ADD KEY `fk_mov_producto` (`id_producto`),
  ADD KEY `idx_mov_fecha` (`fecha_evento`),
  ADD KEY `fk_mov_usuario` (`id_usuario`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `precios_producto`
--
ALTER TABLE `precios_producto`
  ADD PRIMARY KEY (`id_precio`),
  ADD KEY `idx_precio` (`id_producto`,`tipo_precio`,`vigente_desde`,`vigente_hasta`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `uq_producto_sku` (`sku`),
  ADD KEY `fk_prod_categoria` (`id_categoria`),
  ADD KEY `fk_prod_subcategoria` (`id_subcategoria`),
  ADD KEY `fk_prod_talla` (`id_talla`),
  ADD KEY `fk_prod_color` (`id_color`);

--
-- Indices de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id_subcategoria`),
  ADD UNIQUE KEY `id_categoria` (`id_categoria`,`nombre`);

--
-- Indices de la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD PRIMARY KEY (`id_talla`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `unidades`
--
ALTER TABLE `unidades`
  ADD PRIMARY KEY (`id_unidad`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  MODIFY `id_bodega` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `colores`
--
ALTER TABLE `colores`
  MODIFY `id_color` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `existencias`
--
ALTER TABLE `existencias`
  MODIFY `id_existencia` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `log_auditoria`
--
ALTER TABLE `log_auditoria`
  MODIFY `id_log` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id_movimiento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `precios_producto`
--
ALTER TABLE `precios_producto`
  MODIFY `id_precio` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id_subcategoria` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id_talla` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `unidades`
--
ALTER TABLE `unidades`
  MODIFY `id_unidad` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `existencias`
--
ALTER TABLE `existencias`
  ADD CONSTRAINT `fk_exi_bodega` FOREIGN KEY (`id_bodega`) REFERENCES `bodegas` (`id_bodega`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exi_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `log_auditoria`
--
ALTER TABLE `log_auditoria`
  ADD CONSTRAINT `fk_log_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_mov_bodega` FOREIGN KEY (`id_bodega`) REFERENCES `bodegas` (`id_bodega`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `precios_producto`
--
ALTER TABLE `precios_producto`
  ADD CONSTRAINT `fk_precio_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_color` FOREIGN KEY (`id_color`) REFERENCES `colores` (`id_color`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_subcategoria` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategorias` (`id_subcategoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_talla` FOREIGN KEY (`id_talla`) REFERENCES `tallas` (`id_talla`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `fk_subcategorias_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
