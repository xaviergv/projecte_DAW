-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 17-04-2026 a las 09:35:15
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `Projecte`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Absencia`
--

CREATE TABLE `Absencia` (
  `id_absencia` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `tipus` varchar(50) NOT NULL DEFAULT 'Altres',
  `data_inici` date NOT NULL,
  `data_fi` date NOT NULL,
  `aprovada` tinyint(1) DEFAULT 0,
  `document_justificatiu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Absencia`
--

INSERT INTO `Absencia` (`id_absencia`, `id_treballador`, `tipus`, `data_inici`, `data_fi`, `aprovada`, `document_justificatiu`) VALUES
(1, 0, 'Baixa mèdica', '2025-11-04', '2025-11-26', 0, NULL),
(2, 7, 'Vacances', '2026-01-26', '2026-02-09', 1, NULL),
(7, 3, 'Baixa mèdica', '2026-02-05', '2026-02-21', 1, NULL),
(9, 26, 'Permís', '2026-03-06', '2026-03-20', 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Alerta`
--

CREATE TABLE `Alerta` (
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
-- Volcado de datos para la tabla `Alerta`
--

INSERT INTO `Alerta` (`id_alerta`, `id_sector`, `tipus_alerta`, `data_generada`, `nivell_urgencia`, `missatge`, `canal_notificacio`, `estat`, `id_usuari_destinatari`) VALUES
(26, NULL, 'Estoc baix', '2026-02-24 10:27:17', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'dsa\' té només 0.01 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Vista', NULL),
(27, NULL, 'Estoc baix', '2026-02-24 10:27:17', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'prova\' té només 1.00 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Vista', NULL),
(28, NULL, 'Estoc baix', '2026-03-03 09:24:36', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'aaa\' té només 0.00 unitats disponibles (mínim recomanat: 100.00). Compra més aviat!', 'Web', 'Vista', NULL),
(29, NULL, 'Estoc baix', '2026-03-10 09:15:10', 'Alt', 'ALERTA AUTOMÀTICA: El producte \'aaaa\' té només 0.00 unitats disponibles (mínim recomanat: 10.00). Compra més aviat!', 'Web', 'Vista', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Analisi_Nutricional`
--

CREATE TABLE `Analisi_Nutricional` (
  `id_analisi` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `tipus_analisi` enum('sol','aigua','foliar') NOT NULL,
  `data_analisi` date NOT NULL,
  `resultats` longtext DEFAULT NULL,
  `tendencies` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Analisi_Nutricional`
--

INSERT INTO `Analisi_Nutricional` (`id_analisi`, `id_sector`, `tipus_analisi`, `data_analisi`, `resultats`, `tendencies`) VALUES
(1, 0, 'aigua', '2025-11-12', NULL, 'dasd'),
(2, 3, 'aigua', '2026-04-01', '31  aad 2', 'das');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Aplicacio_Tractament`
--

CREATE TABLE `Aplicacio_Tractament` (
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
-- Volcado de datos para la tabla `Aplicacio_Tractament`
--

INSERT INTO `Aplicacio_Tractament` (`id_aplicacio`, `id_sector`, `id_fila`, `id_producte`, `id_usuari`, `data_hora`, `quantitat_aplicada`, `concentracio_nutrients`, `metode_aplicacio`, `condicions_ambientals`, `observacions`, `volum_caldo`, `dosi_calculada`) VALUES
(2, 3, 2, 2, NULL, '2026-04-08 10:30:00', 20.00, NULL, 'polvoritzacio', NULL, 'a', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Assignacio_Treballador_Tasca`
--

CREATE TABLE `Assignacio_Treballador_Tasca` (
  `id_assignacio` int(11) NOT NULL,
  `id_tasca` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `es_cap_equip` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Assignacio_Treballador_Tasca`
--

INSERT INTO `Assignacio_Treballador_Tasca` (`id_assignacio`, `id_tasca`, `id_treballador`, `es_cap_equip`) VALUES
(10, 7, 7, 1),
(11, 7, 1, 1),
(12, 7, 3, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Calendari_Fitosanitari`
--

CREATE TABLE `Calendari_Fitosanitari` (
  `id_calendari` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `data_planificada` date NOT NULL,
  `estat_fenologic` varchar(100) DEFAULT NULL,
  `plaga_malaltia` varchar(100) DEFAULT NULL,
  `id_producte_recomanat` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Calendari_Fitosanitari`
--

INSERT INTO `Calendari_Fitosanitari` (`id_calendari`, `id_sector`, `data_planificada`, `estat_fenologic`, `plaga_malaltia`, `id_producte_recomanat`, `notes`) VALUES
(2, 3, '2026-03-30', 'floració', 'mildiu', 2, 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Categoria_Professional`
--

CREATE TABLE `Categoria_Professional` (
  `id_categoria` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `descripcio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Categoria_Professional`
--

INSERT INTO `Categoria_Professional` (`id_categoria`, `nom`, `descripcio`) VALUES
(1, 'preo', 'sadas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Certificacio_Treballador`
--

CREATE TABLE `Certificacio_Treballador` (
  `id_certificacio` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `id_tipus_certificacio` int(11) NOT NULL,
  `data_obtencio` date NOT NULL,
  `data_caducitat` date DEFAULT NULL,
  `document_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Certificacio_Treballador`
--

INSERT INTO `Certificacio_Treballador` (`id_certificacio`, `id_treballador`, `id_tipus_certificacio`, `data_obtencio`, `data_caducitat`, `document_pdf`) VALUES
(1, 0, 0, '2025-11-28', '2025-12-04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Contracte`
--

CREATE TABLE `Contracte` (
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
-- Volcado de datos para la tabla `Contracte`
--

INSERT INTO `Contracte` (`id_contracte`, `id_treballador`, `id_tipus_contracte`, `data_inici`, `data_fi`, `categoria_professional`, `salari_brut_anual`, `document_pdf`) VALUES
(4, 7, NULL, NULL, NULL, 'Boss', 50000.00, NULL),
(5, 3, NULL, NULL, NULL, 'Administrador', 2000.00, NULL),
(8, 1, NULL, NULL, NULL, 'Administrador', 20000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Cultiu`
--

CREATE TABLE `Cultiu` (
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
-- Volcado de datos para la tabla `Cultiu`
--

INSERT INTO `Cultiu` (`id_cultiu`, `nom_comu`, `nom_cientific`, `hores_fred`, `necessitats_hidriques`, `resistencia_malalties`, `cicle_vegetatiu`, `pol·linitzacio`, `productivitat_mitjana`, `qualitats_fruit`) VALUES
(1, 'poma', 'pomacus', 433, '238', 'tetanus', '2 estacions', 'cada 4 setmanes', NULL, 'Bon aliment'),
(321315, 'prova', 'provacus', NULL, NULL, NULL, '2 estius', NULL, NULL, 'Perfecte'),
(321332, 'aa', 'aa', NULL, NULL, NULL, 'aa', NULL, NULL, NULL),
(321333, 'aaaaa', 'aaaa', NULL, NULL, NULL, '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Departament`
--

CREATE TABLE `Departament` (
  `id_departament` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `id_cap` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Departament`
--

INSERT INTO `Departament` (`id_departament`, `nom`, `id_cap`) VALUES
(1, 'magatzem', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Document_Treballador`
--

CREATE TABLE `Document_Treballador` (
  `id_document` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `tipus_document` varchar(100) NOT NULL,
  `nom_fitxer` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `data_pujada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Estoc`
--

CREATE TABLE `Estoc` (
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
-- Volcado de datos para la tabla `Estoc`
--

INSERT INTO `Estoc` (`id_estoc`, `id_producte`, `quantitat_disponible`, `unitat_mesura`, `data_compra`, `proveidor`, `numero_lot`, `data_caducitat`, `ubicacio_magatzem`, `preu`) VALUES
(1, 1, 0.01, 'L', '2025-11-10', 'ds', 'dsa', '2025-11-29', 'dsa', 0.01),
(2, 2, 1.00, 'kh', '2026-02-11', 'a', '2', '2026-03-07', 'a1', NULL),
(3, 1, 22.00, 'kg', NULL, '', '', NULL, '', NULL),
(4, 7, 111.00, '111', NULL, '', '', NULL, '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Fila_Arbres`
--

CREATE TABLE `Fila_Arbres` (
  `id_fila` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `numero_fila` int(11) NOT NULL,
  `coordenades_fila` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Fila_Arbres`
--

INSERT INTO `Fila_Arbres` (`id_fila`, `id_sector`, `numero_fila`, `coordenades_fila`, `notes`) VALUES
(2, 2, 3, '12.23256,32.1561', 'molt verd'),
(3, 3, 2, '21.23313, 22.3432', 'zona poc verda');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Fitxatge`
--

CREATE TABLE `Fitxatge` (
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
-- Volcado de datos para la tabla `Fitxatge`
--

INSERT INTO `Fitxatge` (`id_fitxatge`, `id_treballador`, `data_hora_entrada`, `data_hora_sortida`, `latitud`, `longitud`, `id_tasca`, `observacions`) VALUES
(1, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'ddadsa'),
(2, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'dda'),
(3, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'd'),
(4, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'ddadsa'),
(5, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'd'),
(6, 1, '2025-11-24 13:56:42', NULL, 41.62268570, 0.89135230, NULL, 'dda'),
(7, 24, '2026-04-02 08:15:00', NULL, 41.00000000, 1.00000000, 7, 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Historial_Cultiu`
--

CREATE TABLE `Historial_Cultiu` (
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
-- Estructura de tabla para la tabla `Monitoratge_Plagues`
--

CREATE TABLE `Monitoratge_Plagues` (
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
-- Volcado de datos para la tabla `Monitoratge_Plagues`
--

INSERT INTO `Monitoratge_Plagues` (`id_monitoratge`, `id_sector`, `id_fila`, `data_observacio`, `tipus_plaga`, `nivell_poblacio`, `tipus_trampa`, `geolocalitzacio`, `observacions`) VALUES
(3, 3, 2, '2026-04-02', 'mosca', 'Alt', 'raid', '', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Parcel·la`
--

CREATE TABLE `Parcel·la` (
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
-- Volcado de datos para la tabla `Parcel·la`
--

INSERT INTO `Parcel·la` (`id_parcela`, `nom`, `superficie`, `tipus_sol`, `textura`, `ph`, `materia_organica`, `pendent`, `orientacio`, `infraestructures`, `documentacio`, `coordenades`) VALUES
(8, 'hola', 21.00, NULL, 'ssa', NULL, NULL, NULL, NULL, NULL, NULL, '12.23256,32.1561'),
(10, 'prova', 12.00, NULL, 'sorrenc', NULL, NULL, NULL, NULL, NULL, NULL, '21.23313, 22.3432'),
(12, 'pomers', 12.00, NULL, 'sorrenca', NULL, NULL, NULL, NULL, NULL, NULL, '16.25415, 24.12412');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Producte`
--

CREATE TABLE `Producte` (
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
-- Volcado de datos para la tabla `Producte`
--

INSERT INTO `Producte` (`id_producte`, `nom_comercial`, `materia_activa`, `tipus`, `dosi_recomanada`, `termini_seguretat`, `classificacio_tox`, `restriccions`, `compatibilitat`, `fabricant`, `numero_registre`, `mode_accio`, `quantitat_minima`) VALUES
(1, 'dsa', 'awdsa', 'fertilitzant', '0.01', 1, 'das', 'dsa', 'integrada', 'dsa', 'das', 'ad', 10.00),
(2, 'prova', NULL, 'fitosanitari', NULL, NULL, NULL, NULL, 'integrada', NULL, NULL, NULL, 10.00),
(3, 'prova', NULL, 'fitosanitari', NULL, NULL, NULL, NULL, 'integrada', NULL, NULL, NULL, 10.00),
(5, 'a', NULL, 'Fertilitzant', '1', 0, NULL, NULL, '', NULL, '', NULL, 10.00),
(6, 'a', NULL, 'Fertilitzant', '1', 0, NULL, NULL, '', NULL, '', NULL, 10.00),
(7, 'aaa', NULL, 'Fertilitzant', 'aaa', 0, NULL, NULL, '', NULL, '', NULL, 100.00),
(8, 'aaaa', NULL, 'Insecticida', '222', 0, NULL, NULL, '', NULL, '', NULL, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sector_Cultiu`
--

CREATE TABLE `Sector_Cultiu` (
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
-- Volcado de datos para la tabla `Sector_Cultiu`
--

INSERT INTO `Sector_Cultiu` (`id_sector`, `id_parcela`, `id_varietat`, `data_plantacio`, `marc_plantacio_files`, `marc_plantacio_arbres`, `num_arbres`, `origen_material`, `sistema_formacio`, `inversio_inicial`, `previsio_produccio`, `coordenades_sector`) VALUES
(2, 8, NULL, '2025-10-08', '3x6', '6x7', 150, NULL, NULL, NULL, 13000.00, NULL),
(3, 10, NULL, '2025-10-16', '5x5', '5x5', 23, NULL, NULL, NULL, 2.00, NULL),
(4, 8, NULL, '2026-01-14', '2x2', '1x1', 12, NULL, NULL, NULL, 123.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sensor`
--

CREATE TABLE `Sensor` (
  `id_sensor` int(11) NOT NULL,
  `id_sector` int(11) NOT NULL,
  `tipus_sensor` varchar(100) NOT NULL DEFAULT '',
  `ubicacio` varchar(255) DEFAULT NULL,
  `data_instalacio` date DEFAULT NULL,
  `lectures` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Sensor`
--

INSERT INTO `Sensor` (`id_sensor`, `id_sector`, `tipus_sensor`, `ubicacio`, `data_instalacio`, `lectures`) VALUES
(2, 2, 'Temperatura ambiental', 'sector2', '2026-01-28', '12'),
(3, 2, 'Pluviòmetre', 'fila2', '2026-02-05', '1 ml/h'),
(5, 4, 'Temperatura sòl', 'aaa', '2026-02-26', 'aaa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tasca`
--

CREATE TABLE `Tasca` (
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
-- Volcado de datos para la tabla `Tasca`
--

INSERT INTO `Tasca` (`id_tasca`, `nom`, `descripcio`, `id_sector`, `id_parcela`, `data_inici_prevista`, `data_fi_prevista`, `hores_estimades`, `finalitzada`, `id_certificat_requerit`, `id_certificacio_requerida`, `instruccions`) VALUES
(6, 'plantar', '', 3, 10, NULL, NULL, 12.00, 0, 0, NULL, NULL),
(7, 'Plantacio', 'plantar mandarines', 2, 8, NULL, NULL, 123.00, 0, 0, NULL, NULL),
(8, 'plantar', 'plantar pomes', 2, 8, NULL, NULL, 123.00, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tipus_Certificacio`
--

CREATE TABLE `Tipus_Certificacio` (
  `id_tipus` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `periode_renovacio_mesos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Tipus_Certificacio`
--

INSERT INTO `Tipus_Certificacio` (`id_tipus`, `nom`, `periode_renovacio_mesos`) VALUES
(1, 'das', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tipus_Contracte`
--

CREATE TABLE `Tipus_Contracte` (
  `id_tipus` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Tipus_Contracte`
--

INSERT INTO `Tipus_Contracte` (`id_tipus`, `nom`) VALUES
(1, ''),
(2, 'indefinit'),
(3, 'formacio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Treballador`
--

CREATE TABLE `Treballador` (
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
-- Volcado de datos para la tabla `Treballador`
--

INSERT INTO `Treballador` (`id_treballador`, `nif`, `nom`, `cognoms`, `foto`, `data_naixement`, `nacionalitat`, `telefon`, `email`, `adreca`, `contacte_emergencia`, `telefon_emergencia`, `iban`, `num_ss`, `data_alta`, `data_baixa`, `actiu`, `id_usuari`) VALUES
(1, '12345678F', 'dsa', 'dsa', NULL, '2025-10-30', 'dsa', 'dsa', 'dsa@ads.dsad', 'dsa', 'dsa', 'das', 'dsadsa', 'dsadas', '2025-11-06', '2025-11-05', 0, 1),
(3, '32132142', 'prova', 'prova', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02', NULL, 1, NULL),
(7, '14523678', 'victor', 'more', NULL, NULL, NULL, '587153982', NULL, NULL, NULL, NULL, NULL, NULL, '1579-01-10', NULL, 1, NULL),
(24, '1', 'aa', 'aa', NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-24', NULL, 1, NULL),
(26, 'aaaaaa', 'aaaaaaa', 'aaaaa', NULL, NULL, NULL, 'aaaaaa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-10', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Treballador_Departament`
--

CREATE TABLE `Treballador_Departament` (
  `id` int(11) NOT NULL,
  `id_treballador` int(11) NOT NULL,
  `id_departament` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `es_cap` tinyint(1) DEFAULT 0,
  `data_inici` date NOT NULL,
  `data_fi` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Treballador_Departament`
--

INSERT INTO `Treballador_Departament` (`id`, `id_treballador`, `id_departament`, `id_categoria`, `es_cap`, `data_inici`, `data_fi`) VALUES
(1, 0, 0, 0, 1, '2025-11-04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Usuari`
--

CREATE TABLE `Usuari` (
  `id_usuari` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasenya_hash` varchar(255) NOT NULL,
  `rol` enum('admin','tecnic','operari','consulta') DEFAULT 'consulta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Usuari`
--

INSERT INTO `Usuari` (`id_usuari`, `nom`, `email`, `contrasenya_hash`, `rol`) VALUES
(1, 'dsa', 'dsa@dsa.dsa', '$2y$10$LhvNWPqMqvtG3Rkx3z.Ocunbri17vs1V3XPCHwlI0W19vKvs/Qesa', 'operari');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Varietat`
--

CREATE TABLE `Varietat` (
  `id_varietat` int(11) NOT NULL,
  `id_cultiu` int(11) NOT NULL,
  `nom_varietat` varchar(100) NOT NULL,
  `caracteristiques` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `Varietat`
--

INSERT INTO `Varietat` (`id_varietat`, `id_cultiu`, `nom_varietat`, `caracteristiques`, `foto`) VALUES
(3, 1, 'ga', 'a', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Absencia`
--
ALTER TABLE `Absencia`
  ADD PRIMARY KEY (`id_absencia`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `Alerta`
--
ALTER TABLE `Alerta`
  ADD PRIMARY KEY (`id_alerta`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_usuari_destinatari` (`id_usuari_destinatari`),
  ADD KEY `idx_alerta_tipus` (`tipus_alerta`);

--
-- Indices de la tabla `Analisi_Nutricional`
--
ALTER TABLE `Analisi_Nutricional`
  ADD PRIMARY KEY (`id_analisi`),
  ADD KEY `id_sector` (`id_sector`);

--
-- Indices de la tabla `Aplicacio_Tractament`
--
ALTER TABLE `Aplicacio_Tractament`
  ADD PRIMARY KEY (`id_aplicacio`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_fila` (`id_fila`),
  ADD KEY `id_producte` (`id_producte`),
  ADD KEY `id_usuari` (`id_usuari`),
  ADD KEY `idx_aplicacio_data` (`data_hora`);

--
-- Indices de la tabla `Assignacio_Treballador_Tasca`
--
ALTER TABLE `Assignacio_Treballador_Tasca`
  ADD PRIMARY KEY (`id_assignacio`),
  ADD KEY `id_tasca` (`id_tasca`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `Calendari_Fitosanitari`
--
ALTER TABLE `Calendari_Fitosanitari`
  ADD PRIMARY KEY (`id_calendari`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_producte_recomanat` (`id_producte_recomanat`);

--
-- Indices de la tabla `Categoria_Professional`
--
ALTER TABLE `Categoria_Professional`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `Certificacio_Treballador`
--
ALTER TABLE `Certificacio_Treballador`
  ADD PRIMARY KEY (`id_certificacio`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tipus_certificacio` (`id_tipus_certificacio`);

--
-- Indices de la tabla `Contracte`
--
ALTER TABLE `Contracte`
  ADD PRIMARY KEY (`id_contracte`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tipus_contracte` (`id_tipus_contracte`);

--
-- Indices de la tabla `Cultiu`
--
ALTER TABLE `Cultiu`
  ADD PRIMARY KEY (`id_cultiu`);

--
-- Indices de la tabla `Departament`
--
ALTER TABLE `Departament`
  ADD PRIMARY KEY (`id_departament`),
  ADD KEY `id_cap` (`id_cap`);

--
-- Indices de la tabla `Document_Treballador`
--
ALTER TABLE `Document_Treballador`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `id_treballador` (`id_treballador`);

--
-- Indices de la tabla `Estoc`
--
ALTER TABLE `Estoc`
  ADD PRIMARY KEY (`id_estoc`),
  ADD KEY `idx_estoc_producte` (`id_producte`);

--
-- Indices de la tabla `Fila_Arbres`
--
ALTER TABLE `Fila_Arbres`
  ADD PRIMARY KEY (`id_fila`),
  ADD UNIQUE KEY `uniq_fila_sector` (`id_sector`,`numero_fila`);

--
-- Indices de la tabla `Fitxatge`
--
ALTER TABLE `Fitxatge`
  ADD PRIMARY KEY (`id_fitxatge`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_tasca` (`id_tasca`);

--
-- Indices de la tabla `Historial_Cultiu`
--
ALTER TABLE `Historial_Cultiu`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_parcela` (`id_parcela`),
  ADD KEY `id_varietat` (`id_varietat`);

--
-- Indices de la tabla `Monitoratge_Plagues`
--
ALTER TABLE `Monitoratge_Plagues`
  ADD PRIMARY KEY (`id_monitoratge`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_fila` (`id_fila`);

--
-- Indices de la tabla `Parcel·la`
--
ALTER TABLE `Parcel·la`
  ADD PRIMARY KEY (`id_parcela`);

--
-- Indices de la tabla `Producte`
--
ALTER TABLE `Producte`
  ADD PRIMARY KEY (`id_producte`);

--
-- Indices de la tabla `Sector_Cultiu`
--
ALTER TABLE `Sector_Cultiu`
  ADD PRIMARY KEY (`id_sector`),
  ADD KEY `id_varietat` (`id_varietat`),
  ADD KEY `idx_sector_parcela` (`id_parcela`);

--
-- Indices de la tabla `Sensor`
--
ALTER TABLE `Sensor`
  ADD PRIMARY KEY (`id_sensor`),
  ADD KEY `id_sector` (`id_sector`);

--
-- Indices de la tabla `Tasca`
--
ALTER TABLE `Tasca`
  ADD PRIMARY KEY (`id_tasca`),
  ADD KEY `id_sector` (`id_sector`),
  ADD KEY `id_parcela` (`id_parcela`),
  ADD KEY `id_certificacio_requerida` (`id_certificacio_requerida`);

--
-- Indices de la tabla `Tipus_Certificacio`
--
ALTER TABLE `Tipus_Certificacio`
  ADD PRIMARY KEY (`id_tipus`);

--
-- Indices de la tabla `Tipus_Contracte`
--
ALTER TABLE `Tipus_Contracte`
  ADD PRIMARY KEY (`id_tipus`);

--
-- Indices de la tabla `Treballador`
--
ALTER TABLE `Treballador`
  ADD PRIMARY KEY (`id_treballador`),
  ADD UNIQUE KEY `nif` (`nif`),
  ADD KEY `id_usuari` (`id_usuari`);

--
-- Indices de la tabla `Treballador_Departament`
--
ALTER TABLE `Treballador_Departament`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_treballador` (`id_treballador`),
  ADD KEY `id_departament` (`id_departament`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `Usuari`
--
ALTER TABLE `Usuari`
  ADD PRIMARY KEY (`id_usuari`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `Varietat`
--
ALTER TABLE `Varietat`
  ADD PRIMARY KEY (`id_varietat`),
  ADD KEY `id_cultiu` (`id_cultiu`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Absencia`
--
ALTER TABLE `Absencia`
  MODIFY `id_absencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `Alerta`
--
ALTER TABLE `Alerta`
  MODIFY `id_alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `Analisi_Nutricional`
--
ALTER TABLE `Analisi_Nutricional`
  MODIFY `id_analisi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `Aplicacio_Tractament`
--
ALTER TABLE `Aplicacio_Tractament`
  MODIFY `id_aplicacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `Assignacio_Treballador_Tasca`
--
ALTER TABLE `Assignacio_Treballador_Tasca`
  MODIFY `id_assignacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `Calendari_Fitosanitari`
--
ALTER TABLE `Calendari_Fitosanitari`
  MODIFY `id_calendari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `Categoria_Professional`
--
ALTER TABLE `Categoria_Professional`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Certificacio_Treballador`
--
ALTER TABLE `Certificacio_Treballador`
  MODIFY `id_certificacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Contracte`
--
ALTER TABLE `Contracte`
  MODIFY `id_contracte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `Cultiu`
--
ALTER TABLE `Cultiu`
  MODIFY `id_cultiu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321334;

--
-- AUTO_INCREMENT de la tabla `Departament`
--
ALTER TABLE `Departament`
  MODIFY `id_departament` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Document_Treballador`
--
ALTER TABLE `Document_Treballador`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `Estoc`
--
ALTER TABLE `Estoc`
  MODIFY `id_estoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `Fila_Arbres`
--
ALTER TABLE `Fila_Arbres`
  MODIFY `id_fila` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `Fitxatge`
--
ALTER TABLE `Fitxatge`
  MODIFY `id_fitxatge` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `Historial_Cultiu`
--
ALTER TABLE `Historial_Cultiu`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Monitoratge_Plagues`
--
ALTER TABLE `Monitoratge_Plagues`
  MODIFY `id_monitoratge` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `Parcel·la`
--
ALTER TABLE `Parcel·la`
  MODIFY `id_parcela` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `Producte`
--
ALTER TABLE `Producte`
  MODIFY `id_producte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `Sector_Cultiu`
--
ALTER TABLE `Sector_Cultiu`
  MODIFY `id_sector` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `Sensor`
--
ALTER TABLE `Sensor`
  MODIFY `id_sensor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `Tasca`
--
ALTER TABLE `Tasca`
  MODIFY `id_tasca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `Tipus_Certificacio`
--
ALTER TABLE `Tipus_Certificacio`
  MODIFY `id_tipus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Tipus_Contracte`
--
ALTER TABLE `Tipus_Contracte`
  MODIFY `id_tipus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `Treballador`
--
ALTER TABLE `Treballador`
  MODIFY `id_treballador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `Treballador_Departament`
--
ALTER TABLE `Treballador_Departament`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Usuari`
--
ALTER TABLE `Usuari`
  MODIFY `id_usuari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `Varietat`
--
ALTER TABLE `Varietat`
  MODIFY `id_varietat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Absencia`
--
ALTER TABLE `Absencia`
  ADD CONSTRAINT `Absencia_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`);

--
-- Filtros para la tabla `Alerta`
--
ALTER TABLE `Alerta`
  ADD CONSTRAINT `Alerta_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE SET NULL,
  ADD CONSTRAINT `Alerta_ibfk_2` FOREIGN KEY (`id_usuari_destinatari`) REFERENCES `Usuari` (`id_usuari`) ON DELETE SET NULL;

--
-- Filtros para la tabla `Analisi_Nutricional`
--
ALTER TABLE `Analisi_Nutricional`
  ADD CONSTRAINT `Analisi_Nutricional_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE;

--
-- Filtros para la tabla `Aplicacio_Tractament`
--
ALTER TABLE `Aplicacio_Tractament`
  ADD CONSTRAINT `Aplicacio_Tractament_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE,
  ADD CONSTRAINT `Aplicacio_Tractament_ibfk_2` FOREIGN KEY (`id_fila`) REFERENCES `Fila_Arbres` (`id_fila`) ON DELETE SET NULL,
  ADD CONSTRAINT `Aplicacio_Tractament_ibfk_3` FOREIGN KEY (`id_producte`) REFERENCES `Producte` (`id_producte`),
  ADD CONSTRAINT `Aplicacio_Tractament_ibfk_4` FOREIGN KEY (`id_usuari`) REFERENCES `Usuari` (`id_usuari`);

--
-- Filtros para la tabla `Assignacio_Treballador_Tasca`
--
ALTER TABLE `Assignacio_Treballador_Tasca`
  ADD CONSTRAINT `Assignacio_Treballador_Tasca_ibfk_1` FOREIGN KEY (`id_tasca`) REFERENCES `Tasca` (`id_tasca`) ON DELETE CASCADE,
  ADD CONSTRAINT `Assignacio_Treballador_Tasca_ibfk_2` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`);

--
-- Filtros para la tabla `Calendari_Fitosanitari`
--
ALTER TABLE `Calendari_Fitosanitari`
  ADD CONSTRAINT `Calendari_Fitosanitari_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE,
  ADD CONSTRAINT `Calendari_Fitosanitari_ibfk_2` FOREIGN KEY (`id_producte_recomanat`) REFERENCES `Producte` (`id_producte`) ON DELETE SET NULL;

--
-- Filtros para la tabla `Certificacio_Treballador`
--
ALTER TABLE `Certificacio_Treballador`
  ADD CONSTRAINT `Certificacio_Treballador_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`) ON DELETE CASCADE,
  ADD CONSTRAINT `Certificacio_Treballador_ibfk_2` FOREIGN KEY (`id_tipus_certificacio`) REFERENCES `Tipus_Certificacio` (`id_tipus`);

--
-- Filtros para la tabla `Contracte`
--
ALTER TABLE `Contracte`
  ADD CONSTRAINT `Contracte_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`) ON DELETE CASCADE,
  ADD CONSTRAINT `Contracte_ibfk_2` FOREIGN KEY (`id_tipus_contracte`) REFERENCES `Tipus_Contracte` (`id_tipus`);

--
-- Filtros para la tabla `Departament`
--
ALTER TABLE `Departament`
  ADD CONSTRAINT `Departament_ibfk_1` FOREIGN KEY (`id_cap`) REFERENCES `Treballador` (`id_treballador`);

--
-- Filtros para la tabla `Document_Treballador`
--
ALTER TABLE `Document_Treballador`
  ADD CONSTRAINT `Document_Treballador_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`) ON DELETE CASCADE;

--
-- Filtros para la tabla `Estoc`
--
ALTER TABLE `Estoc`
  ADD CONSTRAINT `Estoc_ibfk_1` FOREIGN KEY (`id_producte`) REFERENCES `Producte` (`id_producte`);

--
-- Filtros para la tabla `Fila_Arbres`
--
ALTER TABLE `Fila_Arbres`
  ADD CONSTRAINT `Fila_Arbres_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE;

--
-- Filtros para la tabla `Fitxatge`
--
ALTER TABLE `Fitxatge`
  ADD CONSTRAINT `Fitxatge_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`),
  ADD CONSTRAINT `Fitxatge_ibfk_2` FOREIGN KEY (`id_tasca`) REFERENCES `Tasca` (`id_tasca`);

--
-- Filtros para la tabla `Historial_Cultiu`
--
ALTER TABLE `Historial_Cultiu`
  ADD CONSTRAINT `Historial_Cultiu_ibfk_1` FOREIGN KEY (`id_parcela`) REFERENCES `Parcel·la` (`id_parcela`) ON DELETE CASCADE,
  ADD CONSTRAINT `Historial_Cultiu_ibfk_2` FOREIGN KEY (`id_varietat`) REFERENCES `Varietat` (`id_varietat`);

--
-- Filtros para la tabla `Monitoratge_Plagues`
--
ALTER TABLE `Monitoratge_Plagues`
  ADD CONSTRAINT `Monitoratge_Plagues_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE,
  ADD CONSTRAINT `Monitoratge_Plagues_ibfk_2` FOREIGN KEY (`id_fila`) REFERENCES `Fila_Arbres` (`id_fila`) ON DELETE SET NULL;

--
-- Filtros para la tabla `Sector_Cultiu`
--
ALTER TABLE `Sector_Cultiu`
  ADD CONSTRAINT `Sector_Cultiu_ibfk_1` FOREIGN KEY (`id_parcela`) REFERENCES `Parcel·la` (`id_parcela`) ON DELETE CASCADE,
  ADD CONSTRAINT `Sector_Cultiu_ibfk_2` FOREIGN KEY (`id_varietat`) REFERENCES `Varietat` (`id_varietat`);

--
-- Filtros para la tabla `Sensor`
--
ALTER TABLE `Sensor`
  ADD CONSTRAINT `Sensor_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`) ON DELETE CASCADE;

--
-- Filtros para la tabla `Tasca`
--
ALTER TABLE `Tasca`
  ADD CONSTRAINT `Tasca_ibfk_1` FOREIGN KEY (`id_sector`) REFERENCES `Sector_Cultiu` (`id_sector`),
  ADD CONSTRAINT `Tasca_ibfk_2` FOREIGN KEY (`id_parcela`) REFERENCES `Parcel·la` (`id_parcela`),
  ADD CONSTRAINT `Tasca_ibfk_3` FOREIGN KEY (`id_certificacio_requerida`) REFERENCES `Tipus_Certificacio` (`id_tipus`);

--
-- Filtros para la tabla `Treballador`
--
ALTER TABLE `Treballador`
  ADD CONSTRAINT `Treballador_ibfk_1` FOREIGN KEY (`id_usuari`) REFERENCES `Usuari` (`id_usuari`) ON DELETE SET NULL;

--
-- Filtros para la tabla `Treballador_Departament`
--
ALTER TABLE `Treballador_Departament`
  ADD CONSTRAINT `Treballador_Departament_ibfk_1` FOREIGN KEY (`id_treballador`) REFERENCES `Treballador` (`id_treballador`) ON DELETE CASCADE,
  ADD CONSTRAINT `Treballador_Departament_ibfk_2` FOREIGN KEY (`id_departament`) REFERENCES `Departament` (`id_departament`),
  ADD CONSTRAINT `Treballador_Departament_ibfk_3` FOREIGN KEY (`id_categoria`) REFERENCES `Categoria_Professional` (`id_categoria`);

--
-- Filtros para la tabla `Varietat`
--
ALTER TABLE `Varietat`
  ADD CONSTRAINT `Varietat_ibfk_1` FOREIGN KEY (`id_cultiu`) REFERENCES `Cultiu` (`id_cultiu`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
