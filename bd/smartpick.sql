-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2026 a las 18:05:08
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
-- Base de datos: `smartpick`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atiende`
--

CREATE TABLE `atiende` (
  `ID_pedido` int(11) NOT NULL,
  `CI_operario` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `atiende`
--

INSERT INTO `atiende` (`ID_pedido`, `CI_operario`) VALUES
(21, '45000001'),
(23, '45000001'),
(25, '45000001'),
(29, '45000001'),
(1, '45000002'),
(3, '45000002'),
(5, '45000002'),
(7, '45000002'),
(11, '45000002'),
(16, '45000002'),
(18, '45000002'),
(2, '45000003'),
(4, '45000003'),
(6, '45000003'),
(8, '45000003'),
(12, '45000003'),
(14, '45000003'),
(19, '49224029'),
(42, '49224029'),
(43, '49224029'),
(44, '49224029'),
(47, '49224029'),
(48, '49224029'),
(9, '56423159'),
(13, '56423159'),
(15, '56423159'),
(20, '56423159'),
(24, '56423159'),
(26, '56423159'),
(28, '56423159'),
(30, '56423159'),
(31, '56423159'),
(32, '56423159'),
(33, '56423159'),
(34, '56423159'),
(35, '56423159'),
(36, '56423159'),
(38, '56423159'),
(39, '56423159'),
(40, '56423159'),
(41, '56423159');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `CI_cliente` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`CI_cliente`) VALUES
('12345678'),
('49224029'),
('51000001'),
('51000002'),
('51000003'),
('51000004'),
('51000005'),
('54284967'),
('59992223');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contiene`
--

CREATE TABLE `contiene` (
  `ID_pedido` int(11) NOT NULL,
  `ID_producto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contiene`
--

INSERT INTO `contiene` (`ID_pedido`, `ID_producto`, `Cantidad`) VALUES
(1, 15, 1),
(2, 17, 1),
(3, 16, 1),
(4, 18, 1),
(5, 19, 1),
(6, 20, 1),
(7, 21, 1),
(8, 22, 1),
(9, 23, 1),
(10, 24, 1),
(11, 25, 1),
(12, 26, 1),
(13, 15, 1),
(14, 17, 1),
(15, 21, 1),
(16, 1, 4),
(16, 4, 3),
(16, 10, 2),
(17, 5, 5),
(17, 7, 4),
(17, 8, 2),
(18, 2, 6),
(18, 11, 3),
(18, 13, 2),
(19, 3, 4),
(19, 6, 2),
(19, 14, 3),
(20, 4, 4),
(20, 9, 2),
(20, 12, 3),
(21, 7, 2),
(23, 2, 2),
(23, 9, 3),
(24, 7, 3),
(25, 8, 5),
(26, 27, 15),
(29, 7, 3),
(30, 1, 10),
(30, 27, 2),
(31, 32, 1),
(32, 34, 1),
(33, 8, 4),
(34, 35, 1),
(35, 27, 5),
(36, 8, 3),
(37, 36, 1),
(38, 9, 11),
(38, 27, 1),
(39, 3, 3),
(40, 2, 8),
(41, 8, 4),
(42, 1, 2),
(43, 1, 4),
(44, 1, 4),
(45, 1, 4),
(46, 2, 3),
(47, 13, 2),
(48, 3, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deposito`
--

CREATE TABLE `deposito` (
  `ID_deposito` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Direccion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deposito`
--

INSERT INTO `deposito` (`ID_deposito`, `Nombre`, `Direccion`) VALUES
(1, 'Depósito Central SmartPick', 'Av. Manuel Oribe 2500');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `esta`
--

CREATE TABLE `esta` (
  `ID_producto` int(11) NOT NULL,
  `ID_zona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `esta`
--

INSERT INTO `esta` (`ID_producto`, `ID_zona`) VALUES
(1, 1),
(2, 1),
(3, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(4, 2),
(5, 2),
(6, 2),
(7, 3),
(8, 3),
(9, 3),
(18, 3),
(24, 3),
(27, 3),
(20, 4),
(34, 4),
(17, 5),
(22, 5),
(26, 5),
(16, 6),
(19, 6),
(23, 6),
(32, 6),
(36, 6),
(15, 7),
(21, 7),
(25, 7),
(33, 7),
(35, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operario`
--

CREATE TABLE `operario` (
  `CI_operario` varchar(20) NOT NULL,
  `Rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `operario`
--

INSERT INTO `operario` (`CI_operario`, `Rol`) VALUES
('45000001', 'Administrador'),
('45000002', 'Operario'),
('45000003', 'Operario'),
('49224029', 'Administrador'),
('56423159', 'Administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `ID_pedido` int(11) NOT NULL,
  `Fecha_entregado` date DEFAULT NULL,
  `Fecha_ingresado` date NOT NULL,
  `Estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`ID_pedido`, `Fecha_entregado`, `Fecha_ingresado`, `Estado`) VALUES
(1, NULL, '2026-08-01', 'ALMACENADO'),
(2, '2026-08-10', '2026-08-02', 'RETIRADO'),
(3, NULL, '2026-08-15', 'ALMACENADO'),
(4, NULL, '2026-08-03', 'ALMACENADO'),
(5, '2026-08-12', '2026-08-04', 'RETIRADO'),
(6, NULL, '2026-08-20', 'ALMACENADO'),
(7, '2026-09-14', '2026-08-06', 'RETIRADO'),
(8, '2026-08-17', '2026-08-07', 'RETIRADO'),
(9, '2026-10-02', '2026-08-22', 'RETIRADO'),
(10, '2026-09-14', '2026-08-09', 'ALMACENADO'),
(11, '2026-08-21', '2026-08-10', 'RETIRADO'),
(12, NULL, '2026-08-25', 'ALMACENADO'),
(13, '2026-10-02', '2026-08-11', 'RETIRADO'),
(14, '2026-08-23', '2026-08-12', 'RETIRADO'),
(15, '2026-10-02', '2026-08-27', 'RETIRADO'),
(16, '2026-08-28', '2026-08-28', 'ENTREGADO'),
(17, NULL, '2026-08-29', 'PENDIENTE'),
(18, NULL, '2026-08-30', 'PENDIENTE'),
(19, NULL, '2026-09-01', 'PREPARANDO'),
(20, '2026-10-02', '2026-09-02', 'ENTREGADO'),
(21, '2026-09-14', '2026-09-14', 'ENTREGADO'),
(23, '2026-09-14', '2026-09-14', 'ENTREGADO'),
(24, '2026-09-15', '2026-09-15', 'ENTREGADO'),
(25, '2026-09-15', '2026-09-15', 'ENTREGADO'),
(26, '2026-09-15', '2026-09-15', 'ENTREGADO'),
(28, '2026-09-15', '2026-09-15', 'RETIRADO'),
(29, '2026-09-15', '2026-09-15', 'ENTREGADO'),
(30, '2026-09-18', '2026-09-18', 'ENTREGADO'),
(31, '2026-09-27', '2026-09-27', 'RETIRADO'),
(32, '2026-10-01', '2026-10-01', 'RETIRADO'),
(33, '2026-10-04', '2026-10-01', 'ENTREGADO'),
(34, '2026-10-04', '2026-10-04', 'RETIRADO'),
(35, '2026-10-04', '2026-10-04', 'ENTREGADO'),
(36, '2026-10-06', '2026-10-06', 'ENTREGADO'),
(37, NULL, '2026-10-06', 'PENDIENTE_RETIRO'),
(38, '2026-10-06', '2026-10-06', 'ENTREGADO'),
(39, '2026-10-07', '2026-10-07', 'ENTREGADO'),
(40, '2026-10-15', '2026-10-08', 'ENTREGADO'),
(41, '2026-10-15', '2026-10-15', 'ENTREGADO'),
(42, '2026-10-17', '2026-10-17', 'ENTREGADO'),
(43, '2026-10-23', '2026-10-17', 'ENTREGADO'),
(44, '2026-10-23', '2026-10-17', 'ENTREGADO'),
(45, NULL, '2026-10-27', 'PENDIENTE'),
(46, NULL, '2026-10-30', 'PENDIENTE'),
(47, NULL, '2026-10-30', 'PREPARANDO'),
(48, '2026-10-30', '2026-10-30', 'ENTREGADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `CI` varchar(20) NOT NULL,
  `Nombre_completo` varchar(100) NOT NULL,
  `Telefono` varchar(30) DEFAULT NULL,
  `Mail` varchar(100) DEFAULT NULL,
  `Direccion` varchar(150) DEFAULT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `Ultimo_acceso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`CI`, `Nombre_completo`, `Telefono`, `Mail`, `Direccion`, `Contrasena`, `Ultimo_acceso`) VALUES
('12345678', 'Silvia Méndez', '099123456', 'silviamendez@gmail.com', 'Brasil 1699', '', NULL),
('45000001', 'Martín Rodríguez', '099111111', 'martin@smartpick.com', 'Av. Artigas 1250', '$2y$10$vc7Z7EzMZmTH4ABNgj9N2uP.iUYoUexW2VKT7KqkvQj0DuRrPaloG', '2026-09-09 22:17:59'),
('45000002', 'Lucía Fernández', '099222222', 'lucia@smartpick.com', 'Uruguay 845', '$2y$10$CPMITOIQrX4pXWlkvrK6X.8TY6zS8wG.r9p44CKP4B/JxA1adt86e', '2026-09-09 21:19:36'),
('45000003', 'Carlos Méndez', '099333333', 'carlos@smartpick.com', 'Brasil 1540', '$2y$10$z9czHSItG0Mtl6c6l36g6./zo0nkAdAdNGd739Nk5PQQtBVI4FPwW', '2026-09-09 21:19:36'),
('49224029', 'Santiago Margall', '099681613', 'santimargall@hotmail.com', 'Brasil 1677', '$2y$10$95xIyEppxAChl2MUgJgQ7OxhnHKAWSd24Wxt.kxqR7trHzvHVz57u', NULL),
('51000001', 'Juan Pérez', '098111111', 'juan@gmail.com', 'Agraciada 450', '1234', NULL),
('51000002', 'María González', '098222222', 'maria@gmail.com', 'Artigas 890', '1234', NULL),
('51000003', 'Sofía Martínez', '098333333', 'sofia@gmail.com', 'Rivera 1250', '1234', NULL),
('51000004', 'Diego Silva', '098444444', 'diego@gmail.com', 'Brasil 620', '1234', NULL),
('51000005', 'Ana Pereira', '098555555', 'ana@gmail.com', 'Uruguay 1470', '1234', NULL),
('54284967', 'Josefina Alves', '099557035', 'josealves@gmail.com', 'Ciudad de Bahia Blanca 2517', '', NULL),
('56423159', 'Martín Ingold', '092198860', 'lalobolso2007@gmail.com', 'Bulevar España 1516', '$2y$10$Xl0l9LqdMCo8I40N0pkZKecR5f8/3MTC1zUcHYFnys.4kqzarrU7q', NULL),
('59992223', 'Guillermo Budez', '099123499', 'guillermo@gmail.com', 'Maldonado 1122', '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pickingdetalle`
--

CREATE TABLE `pickingdetalle` (
  `ID_pedido` int(11) NOT NULL,
  `ID_producto` int(11) NOT NULL,
  `Confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `Fecha_confirmacion` datetime DEFAULT NULL,
  `CI_operario` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pickingdetalle`
--

INSERT INTO `pickingdetalle` (`ID_pedido`, `ID_producto`, `Confirmado`, `Fecha_confirmacion`, `CI_operario`) VALUES
(9, 23, 1, '2026-09-29 18:52:45', '56423159'),
(13, 15, 1, '2026-10-02 08:48:11', '56423159'),
(15, 21, 1, '2026-10-02 08:47:57', '56423159'),
(19, 3, 0, NULL, NULL),
(19, 6, 0, NULL, NULL),
(19, 14, 0, NULL, NULL),
(20, 4, 1, '2026-10-02 08:48:03', '56423159'),
(20, 9, 1, '2026-10-02 08:48:05', '56423159'),
(20, 12, 1, '2026-10-02 08:48:02', '56423159'),
(21, 7, 1, '2026-09-14 18:42:49', '45000001'),
(23, 2, 1, '2026-09-14 18:28:55', '45000001'),
(23, 9, 1, '2026-09-14 18:28:57', '45000001'),
(24, 7, 1, '2026-09-15 10:02:53', '56423159'),
(25, 8, 1, '2026-09-15 09:16:47', '45000001'),
(26, 27, 1, '2026-09-15 09:42:24', '56423159'),
(28, 30, 1, '2026-09-15 10:18:50', '56423159'),
(29, 7, 1, '2026-09-15 11:48:21', '45000001'),
(30, 1, 1, '2026-09-18 13:34:30', '56423159'),
(30, 27, 1, '2026-09-18 13:34:33', '56423159'),
(31, 32, 1, '2026-09-27 16:17:00', '56423159'),
(32, 34, 1, '2026-10-01 12:10:30', '56423159'),
(33, 8, 1, '2026-10-04 23:06:30', '56423159'),
(34, 35, 1, '2026-10-04 23:17:59', '56423159'),
(35, 27, 1, '2026-10-04 23:17:54', '56423159'),
(36, 8, 1, '2026-10-06 10:16:05', '56423159'),
(38, 9, 1, '2026-10-06 11:49:07', '56423159'),
(38, 27, 1, '2026-10-06 11:47:27', '56423159'),
(39, 3, 1, '2026-10-07 12:32:15', '56423159'),
(40, 2, 1, '2026-10-08 08:09:54', '56423159'),
(41, 8, 1, '2026-10-15 13:01:18', '56423159'),
(42, 1, 1, '2026-10-17 22:16:16', '49224029'),
(43, 1, 1, '2026-10-17 22:34:15', '49224029'),
(44, 1, 1, '2026-10-17 22:35:57', '49224029'),
(47, 13, 0, NULL, NULL),
(48, 3, 1, '2026-10-30 19:59:23', '49224029');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_producto` int(11) NOT NULL,
  `Tipo` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(150) DEFAULT NULL,
  `Stock_actual` int(11) NOT NULL,
  `Stock_minimo` int(11) NOT NULL,
  `Categoria` varchar(50) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`ID_producto`, `Tipo`, `Descripcion`, `Stock_actual`, `Stock_minimo`, `Categoria`, `imagen`) VALUES
(1, 'ALMACEN', 'Lata de atún 170 g', 25, 10, 'Conservas', NULL),
(2, 'ALMACEN', 'Lata de arvejas 300 g', 25, 10, 'Conservas', NULL),
(3, 'ALMACEN', 'Lata de choclo 300 g', 15, 8, 'Conservas', NULL),
(4, 'ALMACEN', 'Agua mineral 2 L', 56, 15, 'Bebidas', NULL),
(5, 'ALMACEN', 'Refresco cola 2.25 L', 40, 12, 'Bebidas', NULL),
(6, 'ALMACEN', 'Jugo de naranja 1 L', 30, 8, 'Bebidas', NULL),
(7, 'ALMACEN', 'Hamburguesas congeladas', 17, 8, 'Congelados', NULL),
(8, 'ALMACEN', 'Papas congeladas 1 kg', 16, 10, 'Congelados', NULL),
(9, 'ALMACEN', 'Pizza congelada', 22, 6, 'Congelados', NULL),
(10, 'ALMACEN', 'Arroz 1 kg', 50, 15, 'Alimentos', NULL),
(11, 'ALMACEN', 'Fideos 500 g', 55, 15, 'Alimentos', NULL),
(12, 'ALMACEN', 'Harina 1 kg', 35, 10, 'Alimentos', NULL),
(13, 'ALMACEN', 'Aceite 1 L', 30, 8, 'Alimentos', 'uploads/productos/13bd11485cef99dc8923b78295814fee.png'),
(14, 'ALMACEN', 'Azúcar 1 kg', 42, 10, 'Alimentos', NULL),
(15, 'DEPOSITO', 'Caja cerrada pequeña', 1, 0, 'General', NULL),
(16, 'DEPOSITO', 'Caja grande cerrada', 1, 0, 'Voluminoso', NULL),
(17, 'DEPOSITO', 'Caja con objetos frágiles', 1, 0, 'Fragil', NULL),
(18, 'DEPOSITO', 'Caja térmica congelada', 1, 0, 'Congelado', NULL),
(19, 'DEPOSITO', 'Bulto grande embalado', 1, 0, 'Voluminoso', NULL),
(20, 'DEPOSITO', 'Caja refrigerada', 1, 0, 'Refrigerado', NULL),
(21, 'DEPOSITO', 'Caja mediana cerrada', 1, 0, 'General', NULL),
(22, 'DEPOSITO', 'Bulto frágil embalado', 1, 0, 'Fragil', NULL),
(23, 'DEPOSITO', 'Bulto grande envuelto en film', 1, 0, 'Voluminoso', NULL),
(24, 'DEPOSITO', 'Caja térmica pequeña', 1, 0, 'Congelado', NULL),
(25, 'DEPOSITO', 'Caja sellada', 1, 0, 'General', NULL),
(26, 'DEPOSITO', 'Caja de vidrio protegida', 1, 0, 'Fragil', NULL),
(27, 'Almacen', 'Nuggets de pollo', 19, 5, 'Congelados', NULL),
(30, 'DEPOSITO', 'Bulto', 1, 1, 'Voluminoso', NULL),
(32, 'DEPOSITO', 'Caja Grande', 1, 0, 'Voluminoso', NULL),
(33, 'ALMACEN', 'Repelentes 200ml', 50, 10, 'Otros', NULL),
(34, 'DEPOSITO', 'Bulto pequeño refrigerado', 1, 0, 'General', NULL),
(35, 'DEPOSITO', 'Caja en bolsa negra', 1, 0, 'General', NULL),
(36, 'DEPOSITO', 'caja grande', 1, 0, 'Voluminoso', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibe_entrega`
--

CREATE TABLE `recibe_entrega` (
  `ID_pedido` int(11) NOT NULL,
  `CI_cliente` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recibe_entrega`
--

INSERT INTO `recibe_entrega` (`ID_pedido`, `CI_cliente`) VALUES
(31, '12345678'),
(40, '12345678'),
(26, '49224029'),
(28, '49224029'),
(29, '49224029'),
(30, '49224029'),
(33, '49224029'),
(34, '49224029'),
(35, '49224029'),
(36, '49224029'),
(37, '49224029'),
(38, '49224029'),
(39, '49224029'),
(41, '49224029'),
(44, '49224029'),
(46, '49224029'),
(47, '49224029'),
(1, '51000001'),
(2, '51000001'),
(3, '51000001'),
(16, '51000001'),
(42, '51000001'),
(43, '51000001'),
(45, '51000001'),
(4, '51000002'),
(5, '51000002'),
(6, '51000002'),
(19, '51000002'),
(25, '51000002'),
(7, '51000003'),
(8, '51000003'),
(9, '51000003'),
(17, '51000003'),
(10, '51000004'),
(11, '51000004'),
(12, '51000004'),
(20, '51000004'),
(23, '51000004'),
(13, '51000005'),
(14, '51000005'),
(15, '51000005'),
(18, '51000005'),
(21, '51000005'),
(24, '51000005'),
(48, '54284967'),
(32, '59992223');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zona`
--

CREATE TABLE `zona` (
  `ID_zona` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Nivel` varchar(50) DEFAULT NULL,
  `Estante` varchar(50) DEFAULT NULL,
  `Descripcion` varchar(150) DEFAULT NULL,
  `ID_deposito` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `zona`
--

INSERT INTO `zona` (`ID_zona`, `Nombre`, `Nivel`, `Estante`, `Descripcion`, `ID_deposito`) VALUES
(1, 'Zona A - Alimentos', 'Nivel 1', 'A1', 'Alimentos secos y conservas', 1),
(2, 'Zona B - Bebidas', 'Nivel 1', 'B1', 'Bebidas y productos líquidos', 1),
(3, 'Zona C - Congelados', 'Nivel 1', 'C1', 'Productos y bultos que requieren congelación', 1),
(4, 'Zona D - Refrigerados', 'Nivel 1', 'D1', 'Productos y bultos que requieren refrigeración', 1),
(5, 'Zona E - Frágiles', 'Nivel 2', 'E1', 'Bultos y productos frágiles', 1),
(6, 'Zona F - Voluminosos', 'Nivel 1', 'F1', 'Bultos grandes o voluminosos', 1),
(7, 'Zona G - Depósito general', 'Nivel 2', 'G1', 'Bultos generales almacenados', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `atiende`
--
ALTER TABLE `atiende`
  ADD PRIMARY KEY (`ID_pedido`),
  ADD KEY `CI_operario` (`CI_operario`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`CI_cliente`);

--
-- Indices de la tabla `contiene`
--
ALTER TABLE `contiene`
  ADD PRIMARY KEY (`ID_pedido`,`ID_producto`),
  ADD KEY `ID_producto` (`ID_producto`);

--
-- Indices de la tabla `deposito`
--
ALTER TABLE `deposito`
  ADD PRIMARY KEY (`ID_deposito`);

--
-- Indices de la tabla `esta`
--
ALTER TABLE `esta`
  ADD PRIMARY KEY (`ID_producto`),
  ADD KEY `ID_zona` (`ID_zona`);

--
-- Indices de la tabla `operario`
--
ALTER TABLE `operario`
  ADD PRIMARY KEY (`CI_operario`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`ID_pedido`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`CI`),
  ADD UNIQUE KEY `Mail` (`Mail`);

--
-- Indices de la tabla `pickingdetalle`
--
ALTER TABLE `pickingdetalle`
  ADD PRIMARY KEY (`ID_pedido`,`ID_producto`),
  ADD KEY `ID_producto` (`ID_producto`),
  ADD KEY `CI_operario` (`CI_operario`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_producto`);

--
-- Indices de la tabla `recibe_entrega`
--
ALTER TABLE `recibe_entrega`
  ADD PRIMARY KEY (`ID_pedido`),
  ADD KEY `CI_cliente` (`CI_cliente`);

--
-- Indices de la tabla `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`ID_zona`),
  ADD KEY `ID_deposito` (`ID_deposito`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `deposito`
--
ALTER TABLE `deposito`
  MODIFY `ID_deposito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `ID_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `zona`
--
ALTER TABLE `zona`
  MODIFY `ID_zona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `atiende`
--
ALTER TABLE `atiende`
  ADD CONSTRAINT `atiende_ibfk_1` FOREIGN KEY (`ID_pedido`) REFERENCES `recibe_entrega` (`ID_pedido`),
  ADD CONSTRAINT `atiende_ibfk_2` FOREIGN KEY (`CI_operario`) REFERENCES `operario` (`CI_operario`);

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`CI_cliente`) REFERENCES `persona` (`CI`);

--
-- Filtros para la tabla `contiene`
--
ALTER TABLE `contiene`
  ADD CONSTRAINT `contiene_ibfk_1` FOREIGN KEY (`ID_pedido`) REFERENCES `pedido` (`ID_pedido`),
  ADD CONSTRAINT `contiene_ibfk_2` FOREIGN KEY (`ID_producto`) REFERENCES `producto` (`ID_producto`);

--
-- Filtros para la tabla `esta`
--
ALTER TABLE `esta`
  ADD CONSTRAINT `esta_ibfk_1` FOREIGN KEY (`ID_producto`) REFERENCES `producto` (`ID_producto`),
  ADD CONSTRAINT `esta_ibfk_2` FOREIGN KEY (`ID_zona`) REFERENCES `zona` (`ID_zona`);

--
-- Filtros para la tabla `operario`
--
ALTER TABLE `operario`
  ADD CONSTRAINT `operario_ibfk_1` FOREIGN KEY (`CI_operario`) REFERENCES `persona` (`CI`);

--
-- Filtros para la tabla `pickingdetalle`
--
ALTER TABLE `pickingdetalle`
  ADD CONSTRAINT `pickingdetalle_ibfk_1` FOREIGN KEY (`ID_pedido`) REFERENCES `pedido` (`ID_pedido`),
  ADD CONSTRAINT `pickingdetalle_ibfk_2` FOREIGN KEY (`ID_producto`) REFERENCES `producto` (`ID_producto`),
  ADD CONSTRAINT `pickingdetalle_ibfk_3` FOREIGN KEY (`CI_operario`) REFERENCES `operario` (`CI_operario`);

--
-- Filtros para la tabla `recibe_entrega`
--
ALTER TABLE `recibe_entrega`
  ADD CONSTRAINT `recibe_entrega_ibfk_1` FOREIGN KEY (`ID_pedido`) REFERENCES `pedido` (`ID_pedido`),
  ADD CONSTRAINT `recibe_entrega_ibfk_2` FOREIGN KEY (`CI_cliente`) REFERENCES `cliente` (`CI_cliente`);

--
-- Filtros para la tabla `zona`
--
ALTER TABLE `zona`
  ADD CONSTRAINT `zona_ibfk_1` FOREIGN KEY (`ID_deposito`) REFERENCES `deposito` (`ID_deposito`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
