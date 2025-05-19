-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-05-2025 a las 08:58:52
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_compras`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `idCompra` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `idProveedor` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`idCompra`, `idUsuario`, `idProveedor`, `fecha`, `total`) VALUES
(1, 1, 1, '2025-05-01 09:00:00', 260.00),
(2, 1, 2, '2025-05-10 12:30:00', 190.00),
(3, 2, 4, '2025-05-03 11:45:00', 170.00),
(4, 2, 5, '2025-05-12 16:00:00', 240.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

CREATE TABLE `detalle_compra` (
  `idDetalle` int(11) NOT NULL,
  `idCompra` int(11) NOT NULL,
  `idProducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioUnitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_compra`
--

INSERT INTO `detalle_compra` (`idDetalle`, `idCompra`, `idProducto`, `cantidad`, `precioUnitario`, `subtotal`) VALUES
(1, 1, 1, 5, 20.00, 100.00),
(2, 1, 2, 4, 18.00, 72.00),
(3, 1, 3, 4, 22.00, 88.00),
(4, 2, 4, 3, 15.00, 45.00),
(5, 2, 5, 12, 12.00, 144.00),
(6, 3, 6, 4, 17.00, 68.00),
(7, 3, 7, 10, 2.50, 25.00),
(8, 3, 9, 5, 6.00, 30.00),
(9, 4, 6, 3, 17.00, 51.00),
(10, 4, 8, 10, 3.50, 35.00),
(11, 4, 10, 40, 1.50, 60.00),
(12, 4, 9, 6, 6.00, 36.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `idProducto` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precioCompra` decimal(10,2) NOT NULL,
  `precioVenta` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`idProducto`, `idUsuario`, `nombre`, `precioCompra`, `precioVenta`, `stock`) VALUES
(1, 1, 'Aceite 1L', 20.00, 28.00, 50),
(2, 1, 'Arroz 1kg', 18.00, 25.00, 80),
(3, 1, 'Frijol negro 1kg', 22.00, 30.00, 70),
(4, 1, 'Detergente polvo 1kg', 15.00, 22.00, 60),
(5, 1, 'Refresco 2L', 12.00, 18.00, 100),
(6, 2, 'Cuaderno profesional', 17.00, 24.00, 40),
(7, 2, 'Lápiz HB', 2.50, 5.00, 200),
(8, 2, 'Bolígrafo azul', 3.50, 6.00, 150),
(9, 2, 'Marcador permanente', 6.00, 10.00, 80),
(10, 2, 'Folder tamaño carta', 1.50, 3.00, 300),
(11, 4, 'Galletas', 10.50, 15.00, 80),
(12, 4, 'Sopas', 14.00, 200.00, 33),
(13, 4, 'Maiz', 1.00, 80.00, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `idProveedor` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`idProveedor`, `idUsuario`, `nombre`, `contacto`, `telefono`) VALUES
(1, 1, 'Distribuidora Abarrotes del Sur', 'Carlos Gutiérrez', '9981234567'),
(2, 1, 'Refrescos Quintana Roo', 'Luis Herrera', '9989876543'),
(3, 1, 'Frutas y Verduras Rivas', 'Julia Rivas', '9985678123'),
(4, 2, 'Papelería Central', 'Ana Tovar', '9993456712'),
(5, 2, 'Súper Regalos S.A.', 'Miguel Sánchez', '9992314455'),
(6, 2, 'Artículos de Oficina Peninsular', 'Laura Pérez', '9997788990'),
(8, 6, 'Proveedor del Norte', 'Luis Ramírez', '999999999'),
(9, 6, 'Proveedor del Norte 2', 'Luis Ramírez', '999999999');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `claveApi` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `correo`, `contrasena`, `claveApi`) VALUES
(1, 'Francisco Rosales', 'fran@example.com', '$2y$10$BK00xxRIb9Bq4eS6ckLDYONh8U3gERz2soconzf9jp/wTNp0eleR2', '9e3b2ab4157ddc52bcf9dc2ee5dd57e6584675a91'),
(2, 'María López', 'maria@example.com', '$2y$10$BK00xxRIb9Bq4eS6ckLDYONh8U3gERz2soconzf9jp/wTNp0eleR2', 'claveApi_maria_456'),
(4, 'paco falso', 'fran2@example.com', '$2y$10$0U9c4rL/MzoCbLDN1t.9GeeuVD/XASJJo7t4iPh23ggCsRt0P21eS', '9e3b2ab4157ddc52bcf9dc2ee5dd57e6584675a9'),
(6, 'paco falso', 'fran3@example.com', '$2y$10$Niyqe3TJs9fy2PQF8HP2JOEmF8NkAq3Kyl4x3uty.wn6s.tCMbUSu', 'fb50b588786bf752f87952babd62aacb12ad0eae');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`idCompra`),
  ADD KEY `fk_compras_usuario` (`idUsuario`),
  ADD KEY `fk_compras_proveedor` (`idProveedor`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`idDetalle`),
  ADD KEY `fk_detalle_compra_compra` (`idCompra`),
  ADD KEY `fk_detalle_compra_producto` (`idProducto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idProducto`),
  ADD KEY `fk_productos_usuario` (`idUsuario`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idProveedor`),
  ADD KEY `fk_proveedores_usuario` (`idUsuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `claveApi` (`claveApi`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `idCompra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `idDetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idProducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idProveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`idProveedor`) REFERENCES `proveedores` (`idProveedor`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_detalle_compra_compra` FOREIGN KEY (`idCompra`) REFERENCES `compras` (`idCompra`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_compra_producto` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`idProducto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `fk_proveedores_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
