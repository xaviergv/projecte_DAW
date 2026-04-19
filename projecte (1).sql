-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-04-2026 a las 20:28:31
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
-- Base de datos: `projecte`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `absencia`
--

CREATE TABLE `absencia` (
  `id_absencia` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `tipus` varchar(50) NOT NULL DEFAULT 'Altres',
  `data_inici` date NOT NULL,
  `data_fi` date NOT NULL,
  `aprovada` tinyint(1) DEFAULT 0,
  `document_justificatiu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `absencia`
--

INSERT INTO `absencia` (`id_absencia`, `id_treballador`, `tipus`, `data_inici`, `data_fi`, `aprovada`, `document_justificatiu`) VALUES
(1, 0, 'Baixa mèdica', '2025-11-04', '2025-11-26', 0, NULL),
(2, 7, 'Vacances', '2026-01-26', '2026-02-09', 1, NULL),
(7, 3, 'Baixa mèdica', '2026-02-05', '2026-02-21', 1, NULL),
(9, 26, 'Permís', '2026-03-06', '2026-03-20', 0, ''),
(10, 32, 'Vacances', '2026-04-15', '2026-04-17', 0, NULL),
(11, 33, 'Malaltia', '2026-04-19', '2026-04-23', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alerta`
--

CREATE TABLE `alerta` (
  `id_alerta` int(11) NOT NULL,
  `id_sector` int(11) DEFAULT NULL,
  `tipus_alerta` varchar(100) NOT NULL DEFAULT '',
  `data_generada` datetime NOT NULL DEFAULT current_timestamp(),
  `nivell_urgencia` enum('Baix','Mitjà','Alt','Crític') NOT NULL DEFAULT 'Mitjà',
  `missatge` text NOT NULL,
  `canal_notificacio` varchar(50) DEFAULT NULL,
  `estat` enum('Pendent','Vista','Resolta') NOT NULL DEFAULT 'Pendent',
  `id_usuari_destinatari` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `alerta`
--

INSERT INTO `alerta` (`id_alerta`, `id_sector`, `tipus_alerta`, `data_generada`, `nivell_urgencia`, `missatge`, `canal_notificacio`, `estat`, `id_usuari_destinatari`) VALUES
(26, NULL, 'Estoc baix', '2026-02-24 10:27:17', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'dsa\' té només 0.01 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Vista', NULL),
(27, NULL, 'Estoc baix', '2026-02-24 10:27:17', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'prova\' té només 1.00 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Vista', NULL),
(28, NULL, 'Estoc baix', '2026-03-03 09:24:36', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'aaa\' té només 0.00 unitats disponibles (mínim recomanat: 100.00). Compra més aviat!', 'Web', 'Vista', NULL),
(29, NULL, 'Estoc baix', '2026-03-10 09:15:10', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'aaaa\' té només 0.00 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Resolta', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `analisi_nutricional`
--

CREATE TABLE `analisi_nutricional` (
  `id_analisi` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `tipus_analisi` enum('sol','aigua','foliar') NOT NULL,
  `data_analisi` date NOT NULL,
  `resultats` longtext DEFAULT NULL,
  `tendencies` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `analisi_nutricional`
--

INSERT INTO `analisi_nutricional` (`id_analisi`, `id_sector`, `tipus_analisi`, `data_analisi`, `resultats`, `tendencies`) VALUES
(1, 0, 'aigua', '2025-11-12', NULL, 'dasd'),
(2, 3, 'aigua', '2026-04-01', '31  aad 2', 'das'),
(3, 2, 'aigua', '2026-04-18', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aplicacio_tractament`
--

CREATE TABLE `aplicacio_tractament` (
  `id_aplicacio` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `id_fila` int(11) DEFAULT NULL,
  `id_producte` int(11) NOT NULL,
  `id_usuari` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `quantitat_aplicada` decimal(10,2) DEFAULT NULL,
  `concentracio_nutrients` varchar(100) DEFAULT NULL,
  `metode_aplicacio` varchar(100) DEFAULT NULL,
  `condicions_ambientals` text DEFAULT NULL,
  `observacions` text DEFAULT NULL,
  `volum_caldo` decimal(10,2) DEFAULT NULL,
  `dosi_calculada` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `aplicacio_tractament`
--

INSERT INTO `aplicacio_tractament` (`id_aplicacio`, `id_sector`, `id_fila`, `id_producte`, `id_usuari`, `data_hora`, `quantitat_aplicada`, `concentracio_nutrients`, `metode_aplicacio`, `condicions_ambientals`, `observacions`, `volum_caldo`, `dosi_calculada`) VALUES
(2, 3, 2, 2, NULL, '2026-04-08 10:30:00', 20.00, NULL, 'polvoritzacio', NULL, 'a', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assignacio_treballador_tasca`
--

CREATE TABLE `assignacio_treballador_tasca` (
  `id_assignacio` int(11) NOT NULL,
  `id_tasca` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `es_cap_equip` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `assignacio_treballador_tasca`
--

INSERT INTO `assignacio_treballador_tasca` (`id_assignacio`, `id_tasca`, `id_treballador`, `es_cap_equip`) VALUES
(10, 7, 7, 1),
(11, 7, 1, 1),
(12, 7, 3, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendari_fitosanitari`
--

CREATE TABLE `calendari_fitosanitari` (
  `id_calendari` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `data_planificada` date NOT NULL,
  `estat_fenologic` varchar(100) DEFAULT NULL,
  `plaga_malaltia` varchar(100) DEFAULT NULL,
  `id_producte_recomanat` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `calendari_fitosanitari`
--

INSERT INTO `calendari_fitosanitari` (`id_calendari`, `id_sector`, `data_planificada`, `estat_fenologic`, `plaga_malaltia`, `id_producte_recomanat`, `notes`) VALUES
(2, 3, '2026-03-30', 'floració', 'mildiu', 2, 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_professional`
--

CREATE TABLE `categoria_professional` (
  `id_categoria` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `descripcio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categoria_professional`
--

INSERT INTO `categoria_professional` (`id_categoria`, `nom`, `descripcio`) VALUES
(1, 'preo', 'sadas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificacio_treballador`
--

CREATE TABLE `certificacio_treballador` (
  `id_certificacio` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `id_tipus_certificacio` int(11) NOT NULL,
  `data_obtencio` date NOT NULL,
  `data_caducitat` date DEFAULT NULL,
  `document_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `certificacio_treballador`
--

INSERT INTO `certificacio_treballador` (`id_certificacio`, `id_treballador`, `id_tipus_certificacio`, `data_obtencio`, `data_caducitat`, `document_pdf`) VALUES
(1, 0, 0, '2025-11-28', '2025-12-04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `collita`
--

CREATE TABLE `collita` (
  `id_collita` int(11) NOT NULL,
  `data_inici` date NOT NULL,
  `data_final` date DEFAULT NULL,
  `id_parcela` int(11) NOT NULL,
  `varietat` varchar(100) NOT NULL,
  `quantitat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unitat` varchar(30) NOT NULL DEFAULT 'kg',
  `treballadors` int(11) DEFAULT 0,
  `temperatura` decimal(5,2) DEFAULT NULL,
  `humitat` decimal(5,2) DEFAULT NULL,
  `estat_fruit` varchar(100) DEFAULT NULL,
  `incidencies` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `collita`
--

INSERT INTO `collita` (`id_collita`, `data_inici`, `data_final`, `id_parcela`, `varietat`, `quantitat`, `unitat`, `treballadors`, `temperatura`, `humitat`, `estat_fruit`, `incidencies`) VALUES
(1, '2026-04-17', '2026-04-18', 30, 'golden', 1500.00, 'kg', 2, 22.00, 67.00, 'Acceptable', 'Plujaaaa'),
(3, '1212-12-12', '1313-12-13', 32, 'Deli', 1111.00, 'litres', 1, 22.00, 22.00, 'Bo', 'ti'),
(4, '2026-04-30', '2026-05-18', 12, 'Deli', 122.00, 'kg', 2, 21.00, 11.00, 'Deficient', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contracte`
--

CREATE TABLE `contracte` (
  `id_contracte` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `id_tipus_contracte` int(11) DEFAULT NULL,
  `data_inici` date DEFAULT NULL,
  `data_fi` date DEFAULT NULL,
  `categoria_professional` varchar(100) DEFAULT NULL,
  `salari_brut_anual` decimal(10,2) DEFAULT NULL,
  `document_pdf` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `contracte`
--

INSERT INTO `contracte` (`id_contracte`, `id_treballador`, `id_tipus_contracte`, `data_inici`, `data_fi`, `categoria_professional`, `salari_brut_anual`, `document_pdf`) VALUES
(4, 7, NULL, NULL, NULL, 'Boss', 50000.00, NULL),
(5, 3, NULL, NULL, NULL, 'Administrador', 2000.00, NULL),
(8, 1, NULL, NULL, NULL, 'Administrador', 20000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cultiu`
--

CREATE TABLE `cultiu` (
  `id_cultiu` int(11) NOT NULL,
  `nom_comu` varchar(100) NOT NULL,
  `nom_cientific` varchar(150) DEFAULT NULL,
  `hores_fred` int(11) DEFAULT NULL COMMENT 'horas de frío necesarias necesarias',
  `necessitats_hidriques` varchar(100) DEFAULT NULL COMMENT 'mm/año o L/planta',
  `resistencia_malalties` text DEFAULT NULL,
  `cicle_vegetatiu` varchar(50) DEFAULT NULL,
  `pol·linitzacio` varchar(50) DEFAULT NULL,
  `productivitat_mitjana` decimal(10,2) DEFAULT NULL COMMENT 'kg/ha o unidades',
  `qualitats_fruit` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cultiu`
--

INSERT INTO `cultiu` (`id_cultiu`, `nom_comu`, `nom_cientific`, `hores_fred`, `necessitats_hidriques`, `resistencia_malalties`, `cicle_vegetatiu`, `pol·linitzacio`, `productivitat_mitjana`, `qualitats_fruit`) VALUES
(1, 'poma', 'pomacus', 433, '238', 'tetanus', '2 estacions', 'cada 4 setmanes', NULL, 'Bon aliment'),
(321315, 'prova', 'provacus', NULL, NULL, NULL, '2 estius', NULL, NULL, 'Perfecte'),
(321333, 'aaaaa', 'aaaa', NULL, NULL, NULL, '', NULL, NULL, NULL),
(321334, 'provavic', 'provavic', NULL, NULL, NULL, 'anual', 'creauda', NULL, 'dolç');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departament`
--

CREATE TABLE `departament` (
  `id_departament` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `id_cap` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `departament`
--

INSERT INTO `departament` (`id_departament`, `nom`, `id_cap`) VALUES
(1, 'magatzem', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_treballador`
--

CREATE TABLE `document_treballador` (
  `id_document` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `tipus_document` varchar(100) NOT NULL,
  `nom_fitxer` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `data_pujada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estoc`
--

CREATE TABLE `estoc` (
  `id_estoc` int(11) NOT NULL,
  `id_producte` int(11) NOT NULL,
  `quantitat_disponible` decimal(10,2) DEFAULT 0.00,
  `unitat_mesura` varchar(20) NOT NULL,
  `data_compra` date DEFAULT NULL,
  `proveidor` varchar(100) DEFAULT NULL,
  `numero_lot` varchar(50) DEFAULT NULL,
  `data_caducitat` date DEFAULT NULL,
  `ubicacio_magatzem` varchar(100) DEFAULT NULL,
  `preu` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `estoc`
--

INSERT INTO `estoc` (`id_estoc`, `id_producte`, `quantitat_disponible`, `unitat_mesura`, `data_compra`, `proveidor`, `numero_lot`, `data_caducitat`, `ubicacio_magatzem`, `preu`) VALUES
(1, 1, 0.01, 'L', '2025-11-10', 'ds', 'dsa', '2025-11-29', 'dsa', 0.01),
(2, 2, 1.00, 'kh', '2026-02-11', 'a', '2', '2026-03-07', 'a1', NULL),
(3, 1, 22.00, 'kg', NULL, '', '', NULL, '', NULL),
(4, 7, 111.00, '111', NULL, '', '', NULL, '', NULL),
(5, 9, 50.50, 'L', '2026-04-17', NULL, NULL, NULL, NULL, NULL),
(6, 9, 22.00, 'KG', '2026-04-18', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fila_arbres`
--

CREATE TABLE `fila_arbres` (
  `id_fila` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `numero_fila` int(11) NOT NULL,
  `coordenades_fila` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `fila_arbres`
--

INSERT INTO `fila_arbres` (`id_fila`, `id_sector`, `numero_fila`, `coordenades_fila`, `notes`) VALUES
(2, 2, 3, '12.23256,32.1561', 'molt verd'),
(3, 3, 2, '21.23313, 22.3432', 'zona poc verda');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fitxatge`
--

CREATE TABLE `fitxatge` (
  `id_fitxatge` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `data_hora_entrada` datetime NOT NULL,
  `data_hora_sortida` datetime DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `id_tasca` int(11) DEFAULT NULL,
  `observacions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `fitxatge`
--

INSERT INTO `fitxatge` (`id_fitxatge`, `id_treballador`, `data_hora_entrada`, `data_hora_sortida`, `latitud`, `longitud`, `id_tasca`, `observacions`) VALUES
(1, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'ddadsa'),
(2, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'dda'),
(3, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'd'),
(4, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'ddadsa'),
(5, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'd'),
(6, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'dda'),
(7, 24, '2026-04-02 08:15:00', NULL, 41.00000000, 1.00000000, 7, 'a'),
(8, 26, '2026-04-17 12:12:00', '2026-04-22 12:32:00', 0.00000000, 0.00000000, 6, ''),
(9, 32, '2026-04-15 12:00:00', '2026-05-17 12:00:00', 41.12345670, 0.12345670, 6, 'grandos gos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_cultiu`
--

CREATE TABLE `historial_cultiu` (
  `id_historial` int(11) NOT NULL,
  `id_parcela` int(11) NOT NULL,
  `id_varietat` int(11) NOT NULL,
  `data_inici` date NOT NULL,
  `data_fi` date DEFAULT NULL,
  `rendiment` decimal(12,2) DEFAULT NULL,
  `incidencies` text DEFAULT NULL,
  `tractaments` text DEFAULT NULL,
  `clima` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hores_treball`
--

CREATE TABLE `hores_treball` (
  `id_hora` int(11) NOT NULL,
  `treballador_id` int(11) NOT NULL,
  `tasca_id` int(11) DEFAULT NULL,
  `hora_inici` datetime NOT NULL,
  `hora_final` datetime DEFAULT NULL,
  `observacions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `hores_treball`
--

INSERT INTO `hores_treball` (`id_hora`, `treballador_id`, `tasca_id`, `hora_inici`, `hora_final`, `observacions`) VALUES
(1, 33, NULL, '2026-04-17 19:13:30', '2026-04-17 19:13:43', ''),
(2, 7, NULL, '2026-04-17 19:15:56', '2026-04-17 19:16:00', ''),
(3, 7, 3, '2026-04-17 19:16:10', '2026-04-18 02:08:35', '67');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventari`
--

CREATE TABLE `inventari` (
  `id_inventari` int(11) NOT NULL,
  `producte_id` int(11) NOT NULL,
  `quantitat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unitat_mesura` varchar(20) NOT NULL DEFAULT 'L',
  `data_compra` date DEFAULT NULL,
  `caducitat` date DEFAULT NULL,
  `proveidor` varchar(150) DEFAULT NULL,
  `preu_unitari` decimal(10,2) DEFAULT NULL,
  `numero_lot` varchar(50) DEFAULT NULL,
  `ubicacio` varchar(100) DEFAULT NULL,
  `observacions` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `inventari`
--

INSERT INTO `inventari` (`id_inventari`, `producte_id`, `quantitat`, `unitat_mesura`, `data_compra`, `caducitat`, `proveidor`, `preu_unitari`, `numero_lot`, `ubicacio`, `observacions`, `created_at`) VALUES
(1, 5, 50.00, 'L', '2026-04-17', NULL, '0', NULL, '', '', '', '2026-04-17 19:06:14'),
(2, 9, 8.00, 'L', '2026-04-17', NULL, '0', NULL, '', '', '', '2026-04-17 19:07:35'),
(3, 1, 10.00, 'L', '2026-04-18', NULL, '0', NULL, '', '', '', '2026-04-18 02:09:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lot`
--

CREATE TABLE `lot` (
  `id_lot` int(11) NOT NULL,
  `codi_lot` varchar(30) NOT NULL,
  `id_collita` int(11) NOT NULL,
  `id_parcela` int(11) NOT NULL,
  `data_collita` date NOT NULL,
  `qualitat` varchar(50) DEFAULT NULL,
  `observacions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `lot`
--

INSERT INTO `lot` (`id_lot`, `codi_lot`, `id_collita`, `id_parcela`, `data_collita`, `qualitat`, `observacions`) VALUES
(1, 'LOT-2026-0001', 1, 30, '2026-04-17', 'Premium', 'Lot destinant a exportacio'),
(2, 'LOT-2026-0002', 3, 32, '1212-12-12', 'Primera', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitoratge_plagues`
--

CREATE TABLE `monitoratge_plagues` (
  `id_monitoratge` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `id_fila` int(11) DEFAULT NULL,
  `data_observacio` date NOT NULL,
  `tipus_plaga` varchar(100) NOT NULL,
  `nivell_poblacio` varchar(50) NOT NULL,
  `tipus_trampa` varchar(100) DEFAULT NULL,
  `geolocalitzacio` varchar(100) DEFAULT NULL,
  `observacions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `monitoratge_plagues`
--

INSERT INTO `monitoratge_plagues` (`id_monitoratge`, `id_sector`, `id_fila`, `data_observacio`, `tipus_plaga`, `nivell_poblacio`, `tipus_trampa`, `geolocalitzacio`, `observacions`) VALUES
(3, 3, 2, '2026-04-02', 'mosca', 'Alt', 'raid', '', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parcel·la`
--

CREATE TABLE `parcel·la` (
  `id_parcela` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `superficie` decimal(10,2) NOT NULL COMMENT 'en hectáreas',
  `tipus_sol` varchar(50) DEFAULT NULL,
  `textura` varchar(50) DEFAULT NULL,
  `ph` decimal(4,2) DEFAULT NULL,
  `materia_organica` decimal(5,2) DEFAULT NULL COMMENT '%',
  `pendent` decimal(5,2) DEFAULT NULL COMMENT '%',
  `orientacio` varchar(20) DEFAULT NULL,
  `infraestructures` text DEFAULT NULL,
  `documentacio` text DEFAULT NULL,
  `coordenades` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `parcel·la`
--

INSERT INTO `parcel·la` (`id_parcela`, `nom`, `superficie`, `tipus_sol`, `textura`, `ph`, `materia_organica`, `pendent`, `orientacio`, `infraestructures`, `documentacio`, `coordenades`) VALUES
(10, 'prova', 12.00, NULL, 'sorrenc', NULL, NULL, NULL, NULL, NULL, NULL, '21.23313, 22.3432'),
(12, 'pomers', 12.00, NULL, 'sorrenca', NULL, NULL, NULL, NULL, NULL, NULL, '16.25415, 24.12412'),
(30, 'victor', 10.00, NULL, 'Franca', NULL, NULL, NULL, NULL, NULL, NULL, '12.23256,32.1562'),
(31, 'isaaquini', 2.00, NULL, 'Llimosa', NULL, NULL, NULL, NULL, NULL, NULL, '21.23313, 22.3432'),
(32, 'isaaquini', 2.00, NULL, 'Llimosa', NULL, NULL, NULL, NULL, NULL, NULL, '21.23313, 22.3432'),
(33, 'trrg', 21.00, NULL, 'Arenosa', NULL, NULL, NULL, NULL, NULL, NULL, '41.64998, 1.14086'),
(34, '21331231', 99999999.99, NULL, 'Arenosa', NULL, NULL, NULL, NULL, NULL, NULL, '[[41.64841,1.12848],[41.64867,1.12848],[41.64867,1.12933],[41.64841,1.12933]]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producte`
--

CREATE TABLE `producte` (
  `id_producte` int(11) NOT NULL,
  `nom_comercial` varchar(100) NOT NULL,
  `materia_activa` varchar(255) DEFAULT NULL,
  `tipus` varchar(50) NOT NULL DEFAULT '',
  `dosi_recomanada` varchar(255) DEFAULT NULL,
  `termini_seguretat` int(11) DEFAULT NULL COMMENT 'dies',
  `classificacio_tox` varchar(50) DEFAULT NULL,
  `restriccions` text DEFAULT NULL,
  `compatibilitat` varchar(500) DEFAULT NULL,
  `fabricant` varchar(100) DEFAULT NULL,
  `numero_registre` varchar(50) DEFAULT NULL,
  `mode_accio` varchar(255) DEFAULT NULL,
  `quantitat_minima` decimal(10,2) NOT NULL DEFAULT 10.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `producte`
--

INSERT INTO `producte` (`id_producte`, `nom_comercial`, `materia_activa`, `tipus`, `dosi_recomanada`, `termini_seguretat`, `classificacio_tox`, `restriccions`, `compatibilitat`, `fabricant`, `numero_registre`, `mode_accio`, `quantitat_minima`) VALUES
(1, 'dsa', 'awdsa', 'fertilitzant', '0.01', 1, 'das', 'dsa', 'integrada', 'dsa', 'das', 'ad', 10.00),
(5, 'a', NULL, 'Fertilitzant', '1', 0, NULL, NULL, '', NULL, '', NULL, 10.00),
(6, 'a', NULL, 'Fertilitzant', '1', 0, NULL, NULL, '', NULL, '', NULL, 10.00),
(7, 'aaa', NULL, 'Fertilitzant', 'aaa', 0, NULL, NULL, '', NULL, '', NULL, 100.00),
(8, 'aaaa', NULL, 'Insecticida', '222', 0, NULL, NULL, '', NULL, '', NULL, 10.00),
(9, 'victorprova', NULL, 'Insecticida', '2L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.00),
(10, 'aa', NULL, 'Insecticida', '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.00),
(11, 'vvvv', NULL, 'Herbicida', '23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.00),
(12, 'qqqq', NULL, 'Fungicida', '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qualitat_fruita`
--

CREATE TABLE `qualitat_fruita` (
  `id_qualitat` int(11) NOT NULL,
  `collita_id` int(11) NOT NULL,
  `mida` enum('Molt petita','Petita','Mitjana','Gran','Molt gran') NOT NULL DEFAULT 'Mitjana',
  `color` varchar(80) NOT NULL DEFAULT '',
  `defectes` text DEFAULT NULL,
  `sabor` enum('Excel·lent','Bo','Acceptable','Mediocre','Dolent') NOT NULL DEFAULT 'Bo',
  `textura` enum('Ferma','Cruixent','Suau','Farinosa','Tova') NOT NULL DEFAULT 'Ferma',
  `grau_brix` decimal(4,1) DEFAULT NULL COMMENT 'º Brix (dolçor)',
  `calibre_mm` decimal(5,1) DEFAULT NULL COMMENT 'diàmetre en mm',
  `pes_mitja` decimal(6,1) DEFAULT NULL COMMENT 'pes mitjà en grams',
  `pct_primera` decimal(5,2) DEFAULT NULL COMMENT '% categoria primera',
  `pct_segona` decimal(5,2) DEFAULT NULL COMMENT '% categoria segona',
  `pct_descart` decimal(5,2) DEFAULT NULL COMMENT '% descart',
  `inspector` varchar(100) DEFAULT NULL,
  `data_control` date NOT NULL,
  `observacions` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `qualitat_fruita`
--

INSERT INTO `qualitat_fruita` (`id_qualitat`, `collita_id`, `mida`, `color`, `defectes`, `sabor`, `textura`, `grau_brix`, `calibre_mm`, `pes_mitja`, `pct_primera`, `pct_segona`, `pct_descart`, `inspector`, `data_control`, `observacions`, `created_at`) VALUES
(1, 4, 'Gran', 'VERMELL', 'L', 'Excel·lent', 'Suau', 1.2, 55.0, 34.0, 23.00, 23.00, 2.00, 'VICTOR', '2026-04-18', 'WE', '2026-04-18 02:14:56'),
(2, 4, 'Mitjana', 'groc', '', 'Mediocre', 'Tova', 10.0, 67.0, 176.0, 76.00, 20.00, 5.00, 'victor', '2026-04-18', '', '2026-04-18 02:23:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sector_cultiu`
--

CREATE TABLE `sector_cultiu` (
  `id_sector` int(11) NOT NULL,
  `id_parcela` int(11) NOT NULL,
  `id_varietat` int(11) DEFAULT NULL,
  `data_plantacio` date NOT NULL,
  `marc_plantacio_files` varchar(50) NOT NULL,
  `marc_plantacio_arbres` varchar(50) NOT NULL,
  `num_arbres` int(11) DEFAULT NULL,
  `origen_material` varchar(100) DEFAULT NULL,
  `sistema_formacio` varchar(100) DEFAULT NULL,
  `inversio_inicial` decimal(12,2) DEFAULT NULL,
  `previsio_produccio` decimal(12,2) DEFAULT NULL COMMENT 'kg o unidades',
  `coordenades_sector` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `sector_cultiu`
--

INSERT INTO `sector_cultiu` (`id_sector`, `id_parcela`, `id_varietat`, `data_plantacio`, `marc_plantacio_files`, `marc_plantacio_arbres`, `num_arbres`, `origen_material`, `sistema_formacio`, `inversio_inicial`, `previsio_produccio`, `coordenades_sector`) VALUES
(2, 8, NULL, '2025-10-08', '3x6', '6x7', 150, NULL, NULL, NULL, 13000.00, NULL),
(3, 10, NULL, '2025-10-16', '5x5', '5x5', 23, NULL, NULL, NULL, 2.00, NULL),
(4, 8, NULL, '2026-01-14', '2x2', '1x1', 12, NULL, NULL, NULL, 123.00, NULL),
(15, 12, 3, '2026-04-17', '5x2', '6x7', 2, NULL, NULL, NULL, 100.00, '41.2222, 4.2222');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sensor`
--

CREATE TABLE `sensor` (
  `id_sensor` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `tipus_sensor` varchar(100) NOT NULL DEFAULT '',
  `ubicacio` varchar(255) DEFAULT NULL,
  `data_instalacio` date DEFAULT NULL,
  `lectures` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `sensor`
--

INSERT INTO `sensor` (`id_sensor`, `id_sector`, `tipus_sensor`, `ubicacio`, `data_instalacio`, `lectures`) VALUES
(2, 2, 'Temperatura ambiental', 'sector2', '2026-01-28', '12'),
(3, 2, 'Pluviòmetre', 'fila2', '2026-02-05', '1 ml/h'),
(5, 4, 'Temperatura sòl', 'aaa', '2026-02-26', 'aaa'),
(6, 15, 'pH sòl', 'Arbre victor 1', '2026-04-17', '67% humitat'),
(7, 15, 'Temperatura sòl', 'dasd', '1231-03-12', '11'),
(8, 15, 'Humitat ambiental', '23', '2323-02-23', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasca`
--

CREATE TABLE `tasca` (
  `id_tasca` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `descripcio` text DEFAULT NULL,
  `id_sector` int(11) DEFAULT NULL,
  `id_parcela` int(11) DEFAULT NULL,
  `data_inici_prevista` date DEFAULT NULL,
  `data_fi_prevista` date DEFAULT NULL,
  `hores_estimades` decimal(6,2) DEFAULT NULL,
  `finalitzada` tinyint(1) NOT NULL DEFAULT 0,
  `id_certificat_requerit` int(11) DEFAULT 0,
  `id_certificacio_requerida` int(11) DEFAULT NULL,
  `instruccions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tasca`
--

INSERT INTO `tasca` (`id_tasca`, `nom`, `descripcio`, `id_sector`, `id_parcela`, `data_inici_prevista`, `data_fi_prevista`, `hores_estimades`, `finalitzada`, `id_certificat_requerit`, `id_certificacio_requerida`, `instruccions`) VALUES
(6, 'plantar', '', 3, 10, NULL, NULL, 12.00, 0, 0, NULL, NULL),
(7, 'Plantacio', 'plantar mandarines', 2, 8, NULL, NULL, 123.00, 0, 0, NULL, NULL),
(8, 'plantar', 'plantar pomes', 2, 8, NULL, NULL, 123.00, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasques_gestio`
--

CREATE TABLE `tasques_gestio` (
  `id_tasca` int(11) NOT NULL,
  `tipus` varchar(80) NOT NULL,
  `parcela_id` int(11) DEFAULT NULL,
  `data_tasca` date NOT NULL,
  `durada_estimada` decimal(5,2) DEFAULT 0.00 COMMENT 'hores',
  `treballador_id` int(11) DEFAULT NULL,
  `descripcio` text DEFAULT NULL,
  `prioritat` enum('Baixa','Normal','Alta','Urgent') NOT NULL DEFAULT 'Normal',
  `estat` enum('Pendent','En curs','Completada','Cancel·lada') NOT NULL DEFAULT 'Pendent',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tasques_gestio`
--

INSERT INTO `tasques_gestio` (`id_tasca`, `tipus`, `parcela_id`, `data_tasca`, `durada_estimada`, `treballador_id`, `descripcio`, `prioritat`, `estat`, `created_at`) VALUES
(2, 'Poda', 32, '2026-04-17', 0.00, 33, '', 'Urgent', 'Completada', '2026-04-17 19:14:26'),
(3, 'Reg', 30, '2026-04-17', 23.00, 7, '', 'Alta', 'Completada', '2026-04-17 19:14:58'),
(4, 'Fertilització', 12, '2026-04-30', 50.00, 32, '', 'Baixa', 'Completada', '2026-04-17 19:15:24'),
(5, 'Poda', 31, '2026-04-18', 0.00, 32, '', 'Alta', 'En curs', '2026-04-18 02:09:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipus_certificacio`
--

CREATE TABLE `tipus_certificacio` (
  `id_tipus` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `periode_renovacio_mesos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipus_certificacio`
--

INSERT INTO `tipus_certificacio` (`id_tipus`, `nom`, `periode_renovacio_mesos`) VALUES
(1, 'das', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipus_contracte`
--

CREATE TABLE `tipus_contracte` (
  `id_tipus` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipus_contracte`
--

INSERT INTO `tipus_contracte` (`id_tipus`, `nom`) VALUES
(1, ''),
(2, 'indefinit'),
(3, 'formacio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tractamentsoficials`
--

CREATE TABLE `tractamentsoficials` (
  `id_tractament` int(11) NOT NULL,
  `data` date NOT NULL,
  `parcela_id` int(11) NOT NULL,
  `producte_id` int(11) NOT NULL,
  `dosi` decimal(10,2) NOT NULL,
  `operari` varchar(150) DEFAULT NULL,
  `maquina` varchar(100) DEFAULT NULL,
  `termini_seguretat` int(11) DEFAULT 0,
  `observacions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tractamentsoficials`
--

INSERT INTO `tractamentsoficials` (`id_tractament`, `data`, `parcela_id`, `producte_id`, `dosi`, `operari`, `maquina`, `termini_seguretat`, `observacions`) VALUES
(1, '2026-04-14', 30, 9, 12.00, 'isaac', 'tractor', 3, 'pluja'),
(2, '2026-09-18', 32, 9, 11.00, 'vicftor', 'a ma', 30, ''),
(3, '2222-02-15', 12, 6, 1.00, '', '', 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `treballador`
--

CREATE TABLE `treballador` (
  `id_treballador` int(11) NOT NULL,
  `nif` varchar(12) NOT NULL,
  `nom` varchar(80) NOT NULL,
  `cognoms` varchar(150) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `data_naixement` date DEFAULT NULL,
  `nacionalitat` varchar(50) DEFAULT NULL,
  `telefon` varchar(15) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `adreca` text DEFAULT NULL,
  `contacte_emergencia` varchar(150) DEFAULT NULL,
  `telefon_emergencia` varchar(15) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `num_ss` varchar(20) DEFAULT NULL,
  `data_alta` date NOT NULL DEFAULT curdate(),
  `data_baixa` date DEFAULT NULL,
  `actiu` tinyint(1) DEFAULT 1,
  `id_usuari` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `treballador`
--

INSERT INTO `treballador` (`id_treballador`, `nif`, `nom`, `cognoms`, `foto`, `data_naixement`, `nacionalitat`, `telefon`, `email`, `adreca`, `contacte_emergencia`, `telefon_emergencia`, `iban`, `num_ss`, `data_alta`, `data_baixa`, `actiu`, `id_usuari`) VALUES
(1, '12345678F', 'dsa', 'dsa', NULL, '2025-10-30', 'dsa', 'dsa', 'dsa@ads.dsad', 'dsa', 'dsa', 'das', 'dsadsa', 'dsadas', '2025-11-06', '2025-11-05', 0, 1),
(7, '14523678', 'victor', 'more', NULL, NULL, NULL, '587153982', NULL, NULL, NULL, NULL, NULL, NULL, '1579-01-10', NULL, 1, NULL),
(32, '48054999D', 'prova', 'victor', NULL, NULL, NULL, '722483508', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-17', NULL, 1, NULL),
(33, '48054998D', 'isaac', 'gos', NULL, NULL, NULL, '722483504', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-17', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `treballador_departament`
--

CREATE TABLE `treballador_departament` (
  `id` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `id_departament` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `es_cap` tinyint(1) DEFAULT 0,
  `data_inici` date NOT NULL,
  `data_fi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `treballador_departament`
--

INSERT INTO `treballador_departament` (`id`, `id_treballador`, `id_departament`, `id_categoria`, `es_cap`, `data_inici`, `data_fi`) VALUES
(1, 0, 0, 0, 1, '2025-11-04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuari`
--

CREATE TABLE `usuari` (
  `id_usuari` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasenya_hash` varchar(255) NOT NULL,
  `rol` enum('admin','tecnic','operari','consulta') DEFAULT 'consulta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuari`
--

INSERT INTO `usuari` (`id_usuari`, `nom`, `email`, `contrasenya_hash`, `rol`) VALUES
(1, 'dsa', 'dsa@dsa.dsa', '$2y$10$LhvNWPqMqvtG3Rkx3z.Ocunbri17vs1V3XPCHwlI0W19vKvs/Qesa', 'operari');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuaris`
--

CREATE TABLE `usuaris` (
  `id_usuari` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registre` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuaris`
--

INSERT INTO `usuaris` (`id_usuari`, `nom`, `email`, `password`, `data_registre`) VALUES
(1, 'VICTOR', 'victormore880@gmail.com', '$2y$10$2GlkstlRSa1bzqOT7iPx6eIVdYUIZTyRWcv95TICnZH/vF3agGt7y', '2026-04-18 00:07:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `varietat`
--

CREATE TABLE `varietat` (
  `id_varietat` int(11) NOT NULL,
  `id_cultiu` int(11) NOT NULL,
  `nom_varietat` varchar(100) NOT NULL,
  `caracteristiques` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `varietat`
--

INSERT INTO `varietat` (`id_varietat`, `id_cultiu`, `nom_varietat`, `caracteristiques`, `foto`) VALUES
(3, 1, 'ga', 'a', NULL),
(13, 321333, 'gos', 'groc', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `absencia`
--
ALTER TABLE `absencia`
  ADD PRIMARY KEY (`id_absencia`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `alerta`
--
ALTER TABLE `alerta`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_usuari_destinatari` (`id_usuari_destinatari`),
  ADD KEY `idx_alerta_tipus` (`tipus_alerta`);

--
-- Indices de la tabla `analisi_nutricional`
--
ALTER TABLE `analisi_nutricional`
  ADD PRIMARY KEY (`id_analisi`),
  ADD KEY `id_sector` (`id_sector`);

--
-- Indices de la tabla `aplicacio_tractament`
--
ALTER TABLE `aplicacio_tractament`
  ADD PRIMARY KEY (`id_aplicacio`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_fila` (`id_fila`),
  ADD KEY `id_producte` (`id_producte`),
  ADD KEY `id_usuari` (`id_usuari`),
  ADD KEY `idx_aplicacio_data` (`data_hora`);

--
-- Indices de la tabla `assignacio_treballador_tasca`
--
ALTER TABLE `assignacio_treballador_tasca`
  ADD PRIMARY KEY (`id_assignacio`),
  ADD KEY `id_tasca` (`id_tasca`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `calendari_fitosanitari`
--
ALTER TABLE `calendari_fitosanitari`
  ADD PRIMARY KEY (`id_calendari`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_producte_recomanat` (`id_producte_recomanat`);

--
-- Indices de la tabla `categoria_professional`
--
ALTER TABLE `categoria_professional`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `certificacio_treballador`
--
ALTER TABLE `certificacio_treballador`
  ADD PRIMARY KEY (`id_certificacio`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tipus_certificacio` (`id_tipus_certificacio`);

--
-- Indices de la tabla `collita`
--
ALTER TABLE `collita`
  ADD PRIMARY KEY (`id_collita`),
  ADD KEY `id_parcela` (`id_parcela`);

--
-- Indices de la tabla `contracte`
--
ALTER TABLE `contracte`
  ADD PRIMARY KEY (`id_contracte`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tipus_contracte` (`id_tipus_contracte`);

--
-- Indices de la tabla `cultiu`
--
ALTER TABLE `cultiu`
  ADD PRIMARY KEY (`id_cultiu`);

--
-- Indices de la tabla `departament`
--
ALTER TABLE `departament`
  ADD PRIMARY KEY (`id_departament`),
  ADD KEY `id_cap` (`id_cap`);

--
-- Indices de la tabla `document_treballador`
--
ALTER TABLE `document_treballador`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `estoc`
--
ALTER TABLE `estoc`
  ADD PRIMARY KEY (`id_estoc`),
  ADD KEY `idx_estoc_producte` (`id_producte`);

--
-- Indices de la tabla `fila_arbres`
--
ALTER TABLE `fila_arbres`
  ADD PRIMARY KEY (`id_fila`),
  ADD UNIQUE KEY `uniq_fila_sector` (`id_sector`,`numero_fila`);

--
-- Indices de la tabla `fitxatge`
--
ALTER TABLE `fitxatge`
  ADD PRIMARY KEY (`id_fitxatge`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tasca` (`id_tasca`);

--
-- Indices de la tabla `historial_cultiu`
--
ALTER TABLE `historial_cultiu`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_parcela` (`id_parcela`),
  ADD KEY `id_varietat` (`id_varietat`);

--
-- Indices de la tabla `hores_treball`
--
ALTER TABLE `hores_treball`
  ADD PRIMARY KEY (`id_hora`),
  ADD KEY `fk_ht_treballador` (`treballador_id`),
  ADD KEY `fk_ht_tasca` (`tasca_id`);

--
-- Indices de la tabla `inventari`
--
ALTER TABLE `inventari`
  ADD PRIMARY KEY (`id_inventari`),
  ADD KEY `fk_inventari_producte` (`producte_id`);

--
-- Indices de la tabla `lot`
--
ALTER TABLE `lot`
  ADD PRIMARY KEY (`id_lot`),
  ADD UNIQUE KEY `codi_lot` (`codi_lot`),
  ADD KEY `id_collita` (`id_collita`),
  ADD KEY `id_parcela` (`id_parcela`);

--
-- Indices de la tabla `monitoratge_plagues`
--
ALTER TABLE `monitoratge_plagues`
  ADD PRIMARY KEY (`id_monitoratge`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_fila` (`id_fila`);

--
-- Indices de la tabla `parcel·la`
--
ALTER TABLE `parcel·la`
  ADD PRIMARY KEY (`id_parcela`);

--
-- Indices de la tabla `producte`
--
ALTER TABLE `producte`
  ADD PRIMARY KEY (`id_producte`);

--
-- Indices de la tabla `qualitat_fruita`
--
ALTER TABLE `qualitat_fruita`
  ADD PRIMARY KEY (`id_qualitat`),
  ADD KEY `fk_qual_collita` (`collita_id`);

--
-- Indices de la tabla `sector_cultiu`
--
ALTER TABLE `sector_cultiu`
  ADD PRIMARY KEY (`id_sector`),
  ADD KEY `id_varietat` (`id_varietat`),
  ADD KEY `idx_sector_parcela` (`id_parcela`);

--
-- Indices de la tabla `sensor`
--
ALTER TABLE `sensor`
  ADD PRIMARY KEY (`id_sensor`),
  ADD KEY `id_sector` (`id_sector`);

--
-- Indices de la tabla `tasca`
--
ALTER TABLE `tasca`
  ADD PRIMARY KEY (`id_tasca`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_parcela` (`id_parcela`),
  ADD KEY `id_certificacio_requerida` (`id_certificacio_requerida`);

--
-- Indices de la tabla `tasques_gestio`
--
ALTER TABLE `tasques_gestio`
  ADD PRIMARY KEY (`id_tasca`),
  ADD KEY `fk_tg_parcela` (`parcela_id`),
  ADD KEY `fk_tg_treballador` (`treballador_id`);

--
-- Indices de la tabla `tipus_certificacio`
--
ALTER TABLE `tipus_certificacio`
  ADD PRIMARY KEY (`id_tipus`);

--
-- Indices de la tabla `tipus_contracte`
--
ALTER TABLE `tipus_contracte`
  ADD PRIMARY KEY (`id_tipus`);

--
-- Indices de la tabla `tractamentsoficials`
--
ALTER TABLE `tractamentsoficials`
  ADD PRIMARY KEY (`id_tractament`),
  ADD KEY `parcela_id` (`parcela_id`),
  ADD KEY `producte_id` (`producte_id`);

--
-- Indices de la tabla `treballador`
--
ALTER TABLE `treballador`
  ADD PRIMARY KEY (`id_treballador`),
  ADD UNIQUE KEY `nif` (`nif`),
  ADD KEY `id_usuari` (`id_usuari`);

--
-- Indices de la tabla `treballador_departament`
--
ALTER TABLE `treballador_departament`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_departament` (`id_departament`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuari`
--
ALTER TABLE `usuari`
  ADD PRIMARY KEY (`id_usuari`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuaris`
--
ALTER TABLE `usuaris`
  ADD PRIMARY KEY (`id_usuari`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `varietat`
--
ALTER TABLE `varietat`
  ADD PRIMARY KEY (`id_varietat`),
  ADD KEY `id_cultiu` (`id_cultiu`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `absencia`
--
ALTER TABLE `absencia`
  MODIFY `id_absencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `alerta`
--
ALTER TABLE `alerta`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `analisi_nutricional`
--
ALTER TABLE `analisi_nutricional`
  MODIFY `id_analisi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `aplicacio_tractament`
--
ALTER TABLE `aplicacio_tractament`
  MODIFY `id_aplicacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `assignacio_treballador_tasca`
--
ALTER TABLE `assignacio_treballador_tasca`
  MODIFY `id_assignacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `calendari_fitosanitari`
--
ALTER TABLE `calendari_fitosanitari`
  MODIFY `id_calendari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `categoria_professional`
--
ALTER TABLE `categoria_professional`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `certificacio_treballador`
--
ALTER TABLE `certificacio_treballador`
  MODIFY `id_certificacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `collita`
--
ALTER TABLE `collita`
  MODIFY `id_collita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `contracte`
--
ALTER TABLE `contracte`
  MODIFY `id_contracte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `cultiu`
--
ALTER TABLE `cultiu`
  MODIFY `id_cultiu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321335;

--
-- AUTO_INCREMENT de la tabla `departament`
--
ALTER TABLE `departament`
  MODIFY `id_departament` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `document_treballador`
--
ALTER TABLE `document_treballador`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estoc`
--
ALTER TABLE `estoc`
  MODIFY `id_estoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `fila_arbres`
--
ALTER TABLE `fila_arbres`
  MODIFY `id_fila` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `fitxatge`
--
ALTER TABLE `fitxatge`
  MODIFY `id_fitxatge` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `historial_cultiu`
--
ALTER TABLE `historial_cultiu`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `hores_treball`
--
ALTER TABLE `hores_treball`
  MODIFY `id_hora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inventari`
--
ALTER TABLE `inventari`
  MODIFY `id_inventari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lot`
--
ALTER TABLE `lot`
  MODIFY `id_lot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `monitoratge_plagues`
--
ALTER TABLE `monitoratge_plagues`
  MODIFY `id_monitoratge` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `parcel·la`
--
ALTER TABLE `parcel·la`
  MODIFY `id_parcela` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `producte`
--
ALTER TABLE `producte`
  MODIFY `id_producte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `qualitat_fruita`
--
ALTER TABLE `qualitat_fruita`
  MODIFY `id_qualitat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sector_cultiu`
--
ALTER TABLE `sector_cultiu`
  MODIFY `id_sector` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `sensor`
--
ALTER TABLE `sensor`
  MODIFY `id_sensor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tasca`
--
ALTER TABLE `tasca`
  MODIFY `id_tasca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tasques_gestio`
--
ALTER TABLE `tasques_gestio`
  MODIFY `id_tasca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipus_certificacio`
--
ALTER TABLE `tipus_certificacio`
  MODIFY `id_tipus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipus_contracte`
--
ALTER TABLE `tipus_contracte`
  MODIFY `id_tipus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tractamentsoficials`
--
ALTER TABLE `tractamentsoficials`
  MODIFY `id_tractament` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `treballador`
--
ALTER TABLE `treballador`
  MODIFY `id_treballador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `treballador_departament`
--
ALTER TABLE `treballador_departament`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuari`
--
ALTER TABLE `usuari`
  MODIFY `id_usuari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuaris`
--
ALTER TABLE `usuaris`
  MODIFY `id_usuari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `varietat`
--
ALTER TABLE `varietat`
  MODIFY `id_varietat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alerta`
--
ALTER TABLE `alerta`
  ADD CONSTRAINT `Alerta_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `sector_cultiu` (`id_sector`) ON DELETE SET NULL,
  ADD CONSTRAINT `Alerta_ibfk_2` FOREIGN KEY (`id_usuari_destinatari`) REFERENCES `usuari` (`id_usuari`) ON DELETE SET NULL;

--
-- Filtros para la tabla `collita`
--
ALTER TABLE `collita`
  ADD CONSTRAINT `collita_ibfk_parcela` FOREIGN KEY (`id_parcela`) REFERENCES `parcel·la` (`id_parcela`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventari`
--
ALTER TABLE `inventari`
  ADD CONSTRAINT `fk_inventari_producte` FOREIGN KEY (`producte_id`) REFERENCES `producte` (`id_producte`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lot`
--
ALTER TABLE `lot`
  ADD CONSTRAINT `lot_ibfk_collita` FOREIGN KEY (`id_collita`) REFERENCES `collita` (`id_collita`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_ibfk_parcela` FOREIGN KEY (`id_parcela`) REFERENCES `parcel·la` (`id_parcela`) ON DELETE CASCADE;

--
-- Filtros para la tabla `qualitat_fruita`
--
ALTER TABLE `qualitat_fruita`
  ADD CONSTRAINT `fk_qual_collita` FOREIGN KEY (`collita_id`) REFERENCES `collita` (`id_collita`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tractamentsoficials`
--
ALTER TABLE `tractamentsoficials`
  ADD CONSTRAINT `fk_tractament_parcela` FOREIGN KEY (`parcela_id`) REFERENCES `parcel·la` (`id_parcela`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tractament_producte` FOREIGN KEY (`producte_id`) REFERENCES `producte` (`id_producte`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
