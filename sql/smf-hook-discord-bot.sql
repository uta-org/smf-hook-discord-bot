-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-12-2019 a las 12:43:04
-- Versión del servidor: 10.1.41-MariaDB-0+deb9u1
-- Versión de PHP: 7.3.12-1+0~20191128.49+debian9~1.gbp24559b

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `smf-hook-discord-bot`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `smf_discord_instances`
--

CREATE TABLE `smf_discord_instances` (
  `id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `smf_url` text NOT NULL,
  `board_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `smf_discord_news`
--

CREATE TABLE `smf_discord_news` (
  `id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `link` text NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `smf_discord_instances`
--
ALTER TABLE `smf_discord_instances`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `smf_discord_news`
--
ALTER TABLE `smf_discord_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instance_id` (`instance_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `smf_discord_instances`
--
ALTER TABLE `smf_discord_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `smf_discord_news`
--
ALTER TABLE `smf_discord_news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `smf_discord_instances`
--
ALTER TABLE `smf_discord_instances`
  ADD CONSTRAINT `smf_discord_instances_ibfk_1` FOREIGN KEY (`id`) REFERENCES `smf_discord_news` (`instance_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
