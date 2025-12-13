-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-12-2025 a las 16:52:24
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
-- Base de datos: `droca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `idArea` int(11) NOT NULL,
  `NombreArea` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`idArea`, `NombreArea`, `Descripcion`) VALUES
(1, 'Sistemas', 'Departamento de tecnología y sistemas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `idCargo` int(11) NOT NULL,
  `NombreCargo` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idArea` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`idCargo`, `NombreCargo`, `Descripcion`, `idArea`) VALUES
(2, 'OSI', 'Operador de Sistema Integral', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cita`
--

CREATE TABLE `cita` (
  `idCita` int(11) NOT NULL,
  `FechaVisita` date NOT NULL,
  `FechaTrato` date DEFAULT NULL,
  `HoraInicio` time NOT NULL,
  `HoraFin` time NOT NULL,
  `esTrato` tinyint(1) DEFAULT NULL,
  `MontoOfrecido` decimal(50,5) DEFAULT NULL,
  `Trabajador_idTrabajador` int(11) DEFAULT NULL,
  `Estado_idEstado` int(11) NOT NULL,
  `Vivienda_idVivienda` int(11) NOT NULL,
  `Cliente_idCliente` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Telefono` char(8) NOT NULL,
  `Direccion` varchar(150) NOT NULL,
  `EstadoCuenta` enum('Activo','Bloqueado') NOT NULL DEFAULT 'Activo',
  `IntentosFallidos` int(11) NOT NULL DEFAULT 0,
  `locked_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `password_expires_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rcvPass_token` varchar(255) DEFAULT NULL,
  `rcvPass_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idCliente`, `Nombre`, `Apellido`, `Usuario`, `Correo`, `Telefono`, `Direccion`, `EstadoCuenta`, `IntentosFallidos`, `locked_at`, `last_login_at`, `password_expires_at`, `is_deleted`, `created_at`, `updated_at`, `rcvPass_token`, `rcvPass_token_expires`) VALUES
(1, 'Natalia', 'Urrutia', 'natalia.urrutia', 'natalia.urrutia1325@gmail.com', '73097719', 'Avenida Ecuador #738', 'Activo', 0, NULL, '2025-12-13 10:00:39', '2026-02-11 15:00:18', 0, '2025-12-13 10:00:18', '2025-12-13 10:02:26', 'a4b70b7eab268adde7dcba05a2a93f55afdc764240d78359266d0fdf1d0c1ca5c0da697b66b62fb35ef3e6855137ce68cb15', '2025-12-13 16:02:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `idEstado` int(11) NOT NULL,
  `Estado` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_history`
--

CREATE TABLE `password_history` (
  `idHistorial` int(11) NOT NULL,
  `user_type` enum('trabajador','cliente') NOT NULL,
  `user_id` int(11) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `FechaCambio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_history`
--

INSERT INTO `password_history` (`idHistorial`, `user_type`, `user_id`, `PasswordHash`, `FechaCambio`) VALUES
(1, 'trabajador', 1, '$2y$10$QW5zsyXn5Ertt6XOWN9N0eqsxTycMRsGv.ylvgveHB3eSaf2j6Eby', '2025-12-13 08:12:25'),
(2, 'cliente', 1, '$2y$10$1LyC7gdq2t5V0efaYrQ4o.jA/t5RC4Jx7pM0cYwJUqEVS3YGv7By2', '2025-12-13 10:00:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `idPermiso` int(11) NOT NULL,
  `Modulo` varchar(100) NOT NULL,
  `Accion` enum('ver','crear','editar','eliminar','bloquear') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`idPermiso`, `Modulo`, `Accion`) VALUES
(6, 'empleados', 'ver'),
(7, 'empleados', 'crear'),
(8, 'empleados', 'editar'),
(9, 'empleados', 'eliminar'),
(10, 'empleados', 'bloquear'),
(16, 'reportes', 'ver'),
(17, 'reportes', 'crear'),
(18, 'reportes', 'editar'),
(19, 'reportes', 'eliminar'),
(20, 'reportes', 'bloquear'),
(11, 'roles', 'ver'),
(12, 'roles', 'crear'),
(13, 'roles', 'editar'),
(14, 'roles', 'eliminar'),
(15, 'roles', 'bloquear'),
(1, 'usuarios', 'ver'),
(2, 'usuarios', 'crear'),
(3, 'usuarios', 'editar'),
(4, 'usuarios', 'eliminar'),
(5, 'usuarios', 'bloquear');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idRol` int(11) NOT NULL,
  `NombreRol` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idRol`, `NombreRol`, `Descripcion`) VALUES
(1, 'OSI', 'Rol para operaciones del sistema OSI');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `idRol` int(11) NOT NULL,
  `idPermiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`idRol`, `idPermiso`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipooferta`
--

CREATE TABLE `tipooferta` (
  `idTipoO` int(11) NOT NULL,
  `Oferta` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipovivienda`
--

CREATE TABLE `tipovivienda` (
  `idTipoV` int(11) NOT NULL,
  `Vivienda` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `idTrabajador` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Telefono` char(8) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `idCargo` int(11) DEFAULT NULL,
  `idRol` int(11) DEFAULT NULL,
  `EstadoCuenta` enum('Activo','Bloqueado') NOT NULL DEFAULT 'Activo',
  `IntentosFallidos` int(11) NOT NULL DEFAULT 0,
  `locked_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `password_expires_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajador`
--

INSERT INTO `trabajador` (`idTrabajador`, `Nombre`, `Apellido`, `Usuario`, `Telefono`, `Correo`, `idCargo`, `idRol`, `EstadoCuenta`, `IntentosFallidos`, `locked_at`, `last_login_at`, `password_expires_at`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Kami', 'Jimenez', 'kami.jimenez', '79502237', 'kami@droca.com', 2, 1, 'Activo', 0, NULL, '2025-12-13 10:17:52', NULL, 0, '2025-12-13 08:10:28', '2025-12-13 10:17:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador_permiso`
--

CREATE TABLE `trabajador_permiso` (
  `idTrabajador` int(11) NOT NULL,
  `idPermiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajador_permiso`
--

INSERT INTO `trabajador_permiso` (`idTrabajador`, `idPermiso`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vivienda`
--

CREATE TABLE `vivienda` (
  `idVivienda` int(11) NOT NULL,
  `Direccion` varchar(150) NOT NULL,
  `MontoPedido` int(11) NOT NULL,
  `Vendido` tinyint(1) NOT NULL DEFAULT 0,
  `Zonas_idZona` int(11) NOT NULL,
  `TipoVivienda_idTipoV` int(11) NOT NULL,
  `TipoOferta_idTipoO` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas`
--

CREATE TABLE `zonas` (
  `idZona` int(11) NOT NULL,
  `Zona` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`idArea`),
  ADD UNIQUE KEY `uq_area_nombre` (`NombreArea`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`idCargo`),
  ADD UNIQUE KEY `uq_cargo_nombre` (`NombreCargo`),
  ADD KEY `fk_cargo_area` (`idArea`);

--
-- Indices de la tabla `cita`
--
ALTER TABLE `cita`
  ADD PRIMARY KEY (`idCita`),
  ADD KEY `idx_cita_cliente` (`Cliente_idCliente`),
  ADD KEY `idx_cita_estado` (`Estado_idEstado`),
  ADD KEY `idx_cita_trab` (`Trabajador_idTrabajador`),
  ADD KEY `idx_cita_viv` (`Vivienda_idVivienda`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idCliente`),
  ADD UNIQUE KEY `uq_cliente_usuario` (`Usuario`),
  ADD UNIQUE KEY `uq_cliente_correo` (`Correo`),
  ADD KEY `idx_cliente_estado` (`EstadoCuenta`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`idEstado`),
  ADD UNIQUE KEY `uq_estado` (`Estado`);

--
-- Indices de la tabla `password_history`
--
ALTER TABLE `password_history`
  ADD PRIMARY KEY (`idHistorial`),
  ADD KEY `idx_pwdhist_user` (`user_type`,`user_id`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`idPermiso`),
  ADD UNIQUE KEY `uq_permiso` (`Modulo`,`Accion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idRol`),
  ADD UNIQUE KEY `uq_rol_nombre` (`NombreRol`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`idRol`,`idPermiso`),
  ADD KEY `fk_rp_permiso` (`idPermiso`);

--
-- Indices de la tabla `tipooferta`
--
ALTER TABLE `tipooferta`
  ADD PRIMARY KEY (`idTipoO`),
  ADD UNIQUE KEY `uq_tipooferta` (`Oferta`);

--
-- Indices de la tabla `tipovivienda`
--
ALTER TABLE `tipovivienda`
  ADD PRIMARY KEY (`idTipoV`),
  ADD UNIQUE KEY `uq_tipovivienda` (`Vivienda`);

--
-- Indices de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD PRIMARY KEY (`idTrabajador`),
  ADD UNIQUE KEY `uq_trabajador_usuario` (`Usuario`),
  ADD UNIQUE KEY `uq_trabajador_correo` (`Correo`),
  ADD KEY `idx_trabajador_cargo` (`idCargo`),
  ADD KEY `idx_trabajador_rol` (`idRol`);

--
-- Indices de la tabla `trabajador_permiso`
--
ALTER TABLE `trabajador_permiso`
  ADD PRIMARY KEY (`idTrabajador`,`idPermiso`),
  ADD KEY `idPermiso` (`idPermiso`);

--
-- Indices de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD PRIMARY KEY (`idVivienda`),
  ADD KEY `idx_vivienda_zona` (`Zonas_idZona`),
  ADD KEY `idx_vivienda_tipov` (`TipoVivienda_idTipoV`),
  ADD KEY `idx_vivienda_tipoo` (`TipoOferta_idTipoO`);

--
-- Indices de la tabla `zonas`
--
ALTER TABLE `zonas`
  ADD PRIMARY KEY (`idZona`),
  ADD UNIQUE KEY `uq_zona` (`Zona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `idArea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `idCargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cita`
--
ALTER TABLE `cita`
  MODIFY `idCita` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `idEstado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_history`
--
ALTER TABLE `password_history`
  MODIFY `idHistorial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `idPermiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipooferta`
--
ALTER TABLE `tipooferta`
  MODIFY `idTipoO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipovivienda`
--
ALTER TABLE `tipovivienda`
  MODIFY `idTipoV` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  MODIFY `idTrabajador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vivienda`
--
ALTER TABLE `vivienda`
  MODIFY `idVivienda` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zonas`
--
ALTER TABLE `zonas`
  MODIFY `idZona` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD CONSTRAINT `fk_cargo_area` FOREIGN KEY (`idArea`) REFERENCES `area` (`idArea`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `cita`
--
ALTER TABLE `cita`
  ADD CONSTRAINT `fk_cita_cliente` FOREIGN KEY (`Cliente_idCliente`) REFERENCES `cliente` (`idCliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cita_estado` FOREIGN KEY (`Estado_idEstado`) REFERENCES `estado` (`idEstado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cita_trabajador` FOREIGN KEY (`Trabajador_idTrabajador`) REFERENCES `trabajador` (`idTrabajador`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cita_vivienda` FOREIGN KEY (`Vivienda_idVivienda`) REFERENCES `vivienda` (`idVivienda`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`idPermiso`) REFERENCES `permiso` (`idPermiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `fk_trabajador_cargo` FOREIGN KEY (`idCargo`) REFERENCES `cargo` (`idCargo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trabajador_rol` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `trabajador_permiso`
--
ALTER TABLE `trabajador_permiso`
  ADD CONSTRAINT `trabajador_permiso_ibfk_1` FOREIGN KEY (`idTrabajador`) REFERENCES `trabajador` (`idTrabajador`) ON DELETE CASCADE,
  ADD CONSTRAINT `trabajador_permiso_ibfk_2` FOREIGN KEY (`idPermiso`) REFERENCES `permiso` (`idPermiso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vivienda`
--
ALTER TABLE `vivienda`
  ADD CONSTRAINT `fk_vivienda_tipooferta` FOREIGN KEY (`TipoOferta_idTipoO`) REFERENCES `tipooferta` (`idTipoO`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vivienda_tipovivienda` FOREIGN KEY (`TipoVivienda_idTipoV`) REFERENCES `tipovivienda` (`idTipoV`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vivienda_zona` FOREIGN KEY (`Zonas_idZona`) REFERENCES `zonas` (`idZona`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
