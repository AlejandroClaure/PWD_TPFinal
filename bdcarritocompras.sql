-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 15:33:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdcarritocompras`
--
-- 1. Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bdcarritocompras DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Select the database for execution
USE bdcarritocompras;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `idcompra` bigint(20) NOT NULL,
  `cofecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `idusuario` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`idcompra`, `cofecha`, `idusuario`) VALUES
(71, '2025-11-24 23:23:49', 4),
(72, '2025-11-25 06:08:41', 4),
(73, '2025-11-25 06:09:46', 4),
(74, '2025-11-25 06:11:10', 4),
(75, '2025-11-25 06:11:12', 4),
(76, '2025-11-25 06:11:16', 4),
(77, '2025-11-25 06:12:21', 4),
(78, '2025-11-25 06:13:00', 4),
(79, '2025-11-25 06:21:09', 4),
(80, '2025-11-25 06:26:58', 4),
(81, '2025-11-25 06:27:49', 4),
(82, '2025-11-26 04:06:30', 23);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraestado`
--

CREATE TABLE `compraestado` (
  `idcompraestado` bigint(20) UNSIGNED NOT NULL,
  `idcompra` bigint(20) NOT NULL,
  `idcompraestadotipo` int(11) NOT NULL,
  `cefechaini` timestamp NOT NULL DEFAULT current_timestamp(),
  `cefechafin` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compraestado`
--

INSERT INTO `compraestado` (`idcompraestado`, `idcompra`, `idcompraestadotipo`, `cefechaini`, `cefechafin`) VALUES
(61, 72, 1, '2025-11-25 06:08:41', '2025-11-26 12:23:28'),
(62, 77, 1, '2025-11-25 06:12:21', '2025-11-25 21:54:05'),
(63, 78, 1, '2025-11-25 06:13:01', NULL),
(64, 80, 1, '2025-11-25 06:26:58', '2025-11-25 21:51:02'),
(65, 81, 1, '2025-11-25 06:27:50', '2025-11-25 21:47:24'),
(66, 81, 2, '2025-11-25 21:47:24', '2025-11-25 21:49:25'),
(67, 81, 4, '2025-11-25 21:49:25', NULL),
(68, 80, 4, '2025-11-25 21:51:02', NULL),
(69, 77, 4, '2025-11-25 21:54:05', NULL),
(70, 82, 1, '2025-11-26 04:06:31', NULL),
(71, 72, 2, '2025-11-26 12:23:28', '2025-11-26 12:23:36'),
(72, 72, 4, '2025-11-26 12:23:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraestadotipo`
--

CREATE TABLE `compraestadotipo` (
  `idcompraestadotipo` int(11) NOT NULL,
  `cetdescripcion` varchar(50) NOT NULL,
  `cetdetalle` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compraestadotipo`
--

INSERT INTO `compraestadotipo` (`idcompraestadotipo`, `cetdescripcion`, `cetdetalle`) VALUES
(1, 'iniciada', 'cuando el usuario : cliente inicia la compra de uno o mas productos del carrito'),
(2, 'aceptada', 'cuando el usuario administrador da ingreso a uno de las compras en estado = 1 '),
(3, 'enviada', 'cuando el usuario administrador envia a uno de las compras en estado =2 '),
(4, 'cancelada', 'un usuario administrador podra cancelar una compra en cualquier estado y un usuario cliente solo en estado=1 '),
(5, 'finalizada', 'cuando la compra ha sido recibida por el cliente y finaliza el proceso de envío');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraitem`
--

CREATE TABLE `compraitem` (
  `idcompraitem` bigint(20) UNSIGNED NOT NULL,
  `idproducto` bigint(20) NOT NULL,
  `idcompra` bigint(20) NOT NULL,
  `cicantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compraitem`
--

INSERT INTO `compraitem` (`idcompraitem`, `idproducto`, `idcompra`, `cicantidad`) VALUES
(91, 55, 77, 1),
(94, 55, 79, 1),
(95, 53, 79, 1),
(96, 55, 80, 1),
(97, 53, 80, 1),
(100, 55, 81, 2),
(101, 56, 81, 1),
(102, 57, 72, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `idmenu` bigint(20) NOT NULL,
  `menombre` varchar(50) NOT NULL COMMENT 'Nombre del item del menu',
  `melink` varchar(255) DEFAULT NULL,
  `medescripcion` varchar(124) NOT NULL COMMENT 'Descripcion mas detallada',
  `idpadre` bigint(20) DEFAULT NULL COMMENT 'Referencia al menú padre',
  `medeshabilitado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`idmenu`, `menombre`, `melink`, `medescripcion`, `idpadre`, `medeshabilitado`) VALUES
(179, 'celulares', 'celulares.php', 'celulares.php', NULL, '0000-00-00 00:00:00'),
(188, 'Samsung', 'celulares/samsung.php', 'celulares/samsung.php', 179, '0000-00-00 00:00:00'),
(189, 'Apple', 'celulares/apple.php', 'celulares/apple.php', 179, '0000-00-00 00:00:00'),
(191, 'Xiaomi', 'celulares/xiaomi.php', 'celulares/xiaomi.php', 179, '0000-00-00 00:00:00'),
(192, 'Accesorios', 'accesorios.php', 'accesorios.php', NULL, '0000-00-00 00:00:00'),
(193, 'Auriculares', 'accesorios/auriculares.php', 'accesorios/auriculares.php', 192, '0000-00-00 00:00:00'),
(194, 'Accesorios', 'celulares/samsung/accesorios.php', 'celulares/samsung/accesorios.php', 188, '0000-00-00 00:00:00'),
(195, 'Poco', 'celulares/poco.php', 'celulares/poco.php', 179, '0000-00-00 00:00:00'),
(196, 'Motorola', 'celulares/motorola.php', 'celulares/motorola.php', 179, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menurol`
--

CREATE TABLE `menurol` (
  `idmenu` bigint(20) NOT NULL,
  `idrol` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `idproducto` bigint(20) NOT NULL,
  `pronombre` varchar(100) NOT NULL,
  `prodetalle` varchar(512) NOT NULL DEFAULT '',
  `proprecio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `prooferta` int(11) DEFAULT 0,
  `profinoffer` datetime DEFAULT NULL,
  `proimagen` varchar(255) DEFAULT NULL,
  `procantstock` int(11) NOT NULL DEFAULT 0,
  `idusuario` bigint(20) NOT NULL,
  `prodeshabilitado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`idproducto`, `pronombre`, `prodetalle`, `proprecio`, `prooferta`, `profinoffer`, `proimagen`, `procantstock`, `idusuario`, `prodeshabilitado`) VALUES
(53, 'celulares_motorola_Moto Edge 50 Pro+', 'El Motorola Edge 50 Pro combina diseño y tecnología de punta para ofrecer una experiencia móvil superior .', 86000.00, 8, NULL, 'Moto_Edge_50_pro_1763822217.png', 10, 4, NULL),
(55, 'celulares_apple_ipad air', 'Apple iPad Air 11, chip M3, Wi-Fi, 128 GB, gris espacial - Distribuidor Autorizado', 1556999.00, 0, NULL, 'ipad_air_1763827015.png', 6, 4, NULL),
(56, 'celulares_samsung_Samsung Galaxy A55', 'alto samsung', 654645.00, 0, NULL, 'Samsung_Galaxy_A55_1763859572.webp', 10, 4, NULL),
(57, 'celulares_samsung_accesorios_SAMSUNG GALAXY BUDS', 'SAMSUNG Galaxy Buds FE True Auriculares Bluetooth inalámbricos, comodidad y seguridad en el oído, audio de interruptor automático, control táctil, asistente de voz integrado, grafito [versión de EE.\r\n4.34.3 de 5 estrellas (7.2 K)', 25000.00, 0, NULL, 'SAMSUNG_Galaxy_Buds_FE_1764100982.jpg', 3, 4, NULL),
(58, 'celulares_xiaomi_xiaomi 13', 'xiaomi weno', 800000.00, 0, NULL, 'xiaomi_13_1764105975.jpg', 8, 4, NULL),
(61, 'celulares_samsung_Samsung S24 Ultra', 'samsung ultra caro con IA', 900000.00, 0, NULL, 'Samsung_S24_Ultra_1764106552.webp', 9, 4, NULL),
(62, 'celulares_poco_Poco X6 Pro', 'buen celu', 180000.00, 0, NULL, 'Poco_X6_Pro_1764162715.jpg', 9, 4, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` bigint(20) NOT NULL,
  `rodescripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rodescripcion`) VALUES
(1, 'admin'),
(2, 'cliente'),
(3, 'deposito');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` bigint(20) NOT NULL,
  `usnombre` varchar(50) NOT NULL,
  `uspass` varchar(255) NOT NULL,
  `usmail` varchar(50) NOT NULL,
  `usdeshabilitado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `usnombre`, `uspass`, `usmail`, `usdeshabilitado`) VALUES
(4, 'ale.c', '$2y$10$ASSBVgrb9t7YGQifGPpuM.dCo/DchUPveYjg/DocsNg7/rv0rVXDm', 'alejandro.claure@est.fi.uncoma.edu.ar', NULL),
(21, 'cliente', '$2y$10$FZP0Q4SOyfwQ3P8obrgWp.4JIt7kDNl5plDMArYZUHdT4DyJzB9Ki', 'cliente@gmail.com', NULL),
(23, 'pepi', '$2y$10$jL9tYrY1qstziNVv4FJ/Y.M2Ylpt3dyR14VUJQ2hfcg/DLuGdXse6', 'pepi@gmail.com', NULL),
(26, 'pepi', '$2y$10$HWOpdrXIdPDfqgMXsLnAS.FXWI7omWHw2oXbqrUDB7rd0uax1kT3W', 'adm@gmail.com', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariorol`
--

CREATE TABLE `usuariorol` (
  `idusuario` bigint(20) NOT NULL,
  `idrol` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuariorol`
--

INSERT INTO `usuariorol` (`idusuario`, `idrol`) VALUES
(4, 1),
(4, 2),
(4, 3),
(21, 2),
(21, 1),
(23, 2),
(26, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`idcompra`),
  ADD KEY `fk_compra_usuario` (`idusuario`);

--
-- Indices de la tabla `compraestado`
--
ALTER TABLE `compraestado`
  ADD PRIMARY KEY (`idcompraestado`),
  ADD KEY `fk_compraestado_compra` (`idcompra`),
  ADD KEY `fk_compraestado_tipo` (`idcompraestadotipo`);

--
-- Indices de la tabla `compraestadotipo`
--
ALTER TABLE `compraestadotipo`
  ADD PRIMARY KEY (`idcompraestadotipo`);

--
-- Indices de la tabla `compraitem`
--
ALTER TABLE `compraitem`
  ADD PRIMARY KEY (`idcompraitem`),
  ADD KEY `fk_compraitem_compra` (`idcompra`),
  ADD KEY `fk_compraitem_producto` (`idproducto`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`idmenu`),
  ADD KEY `fk_menu_padre` (`idpadre`);

--
-- Indices de la tabla `menurol`
--
ALTER TABLE `menurol`
  ADD PRIMARY KEY (`idmenu`,`idrol`),
  ADD KEY `fk_menurol_rol` (`idrol`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`idproducto`),
  ADD KEY `fk_producto_usuario` (`idusuario`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`),
  ADD UNIQUE KEY `idrol` (`idrol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `idusuario` (`idusuario`),
  ADD UNIQUE KEY `usmail` (`usmail`);

--
-- Indices de la tabla `usuariorol`
--
ALTER TABLE `usuariorol`
  ADD PRIMARY KEY (`idusuario`,`idrol`),
  ADD KEY `fk_usuariorol_rol` (`idrol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `idcompra` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT de la tabla `compraestado`
--
ALTER TABLE `compraestado`
  MODIFY `idcompraestado` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `compraitem`
--
ALTER TABLE `compraitem`
  MODIFY `idcompraitem` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `idmenu` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `idproducto` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `fk_compra_usuario` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`idusuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `compraestado`
--
ALTER TABLE `compraestado`
  ADD CONSTRAINT `fk_compraestado_compra` FOREIGN KEY (`idcompra`) REFERENCES `compra` (`idcompra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compraestado_tipo` FOREIGN KEY (`idcompraestadotipo`) REFERENCES `compraestadotipo` (`idcompraestadotipo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `compraitem`
--
ALTER TABLE `compraitem`
  ADD CONSTRAINT `fk_compraitem_compra` FOREIGN KEY (`idcompra`) REFERENCES `compra` (`idcompra`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compraitem_producto` FOREIGN KEY (`idproducto`) REFERENCES `producto` (`idproducto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_padre` FOREIGN KEY (`idpadre`) REFERENCES `menu` (`idmenu`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `menurol`
--
ALTER TABLE `menurol`
  ADD CONSTRAINT `fk_menurol_menu` FOREIGN KEY (`idmenu`) REFERENCES `menu` (`idmenu`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_menurol_rol` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_producto_usuario` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`idusuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuariorol`
--
ALTER TABLE `usuariorol`
  ADD CONSTRAINT `fk_usuariorol_rol` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuariorol_usuario` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`idusuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;