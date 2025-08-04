-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 04-Ago-2025 às 17:41
-- Versão do servidor: 10.11.10-MariaDB-log
-- versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `u342734079_revendaweb`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `agentes_notificacao`
--

CREATE TABLE `agentes_notificacao` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero_whatsapp` varchar(20) NOT NULL,
  `departamentos` text NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `prioridade` int(11) DEFAULT 1,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `agentes_notificacao`
--

INSERT INTO `agentes_notificacao` (`id`, `nome`, `numero_whatsapp`, `departamentos`, `ativo`, `prioridade`, `data_cadastro`) VALUES
(1, 'Rafael - Sites/Ecommerce', '5547973095525', '[\"SITES\",\"COM\"]', 1, 1, '2025-08-02 19:23:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `assinaturas`
--

CREATE TABLE `assinaturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `asaas_id` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `periodicidade` varchar(20) NOT NULL,
  `start_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `atendimentos_ana`
--

CREATE TABLE `atendimentos_ana` (
  `id` int(11) NOT NULL,
  `numero_cliente` varchar(20) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `departamento` varchar(10) DEFAULT NULL,
  `data_atendimento` datetime DEFAULT current_timestamp(),
  `resposta_ana` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `atendimentos_ana`
--

INSERT INTO `atendimentos_ana` (`id`, `numero_cliente`, `mensagem`, `departamento`, `data_atendimento`, `resposta_ana`) VALUES
(1, '5547999999999', 'oi', 'COM', '2025-08-02 16:37:28', NULL),
(2, '5547999999999', 'Olá Ana, você está funcionando via sistema local?', 'COM', '2025-08-02 17:53:26', NULL),
(3, '5547999999999', 'teste diagnóstico', 'COM', '2025-08-02 18:11:11', NULL),
(4, '5547999999999', 'teste diagnóstico', 'COM', '2025-08-02 18:12:41', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `backup_canais_comunicacao_original`
--

CREATE TABLE `backup_canais_comunicacao_original` (
  `id` int(11) NOT NULL DEFAULT 0,
  `tipo` varchar(32) NOT NULL,
  `identificador` varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  `nome_exibicao` varchar(64) DEFAULT NULL,
  `data_conexao` datetime DEFAULT NULL,
  `porta` int(11) DEFAULT NULL,
  `sessao` varchar(50) DEFAULT NULL,
  `endpoint` varchar(128) DEFAULT NULL,
  `pasta_sessao` varchar(128) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `ultimo_envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `backup_canais_comunicacao_original`
--

INSERT INTO `backup_canais_comunicacao_original` (`id`, `tipo`, `identificador`, `status`, `nome_exibicao`, `data_conexao`, `porta`, `sessao`, `endpoint`, `pasta_sessao`, `pid`, `ultimo_envio`) VALUES
(36, 'whatsapp', '554797146908@c.us', 'conectado', 'Financeiro', '2025-07-31 19:56:43', 3000, 'default', NULL, NULL, NULL, NULL),
(37, 'whatsapp', '4797309525@c.us', 'conectado', 'Comercial - Pixel', '2025-07-31 21:31:22', 3001, 'comercial', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `backup_mensagens_comunicacao_original`
--

CREATE TABLE `backup_mensagens_comunicacao_original` (
  `id` int(11) NOT NULL DEFAULT 0,
  `canal_id` int(11) NOT NULL,
  `canal_nome` varchar(100) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cobranca_id` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `tipo` varchar(32) NOT NULL,
  `data_hora` datetime NOT NULL,
  `direcao` varchar(16) NOT NULL,
  `status` varchar(32) DEFAULT NULL,
  `status_conversa` enum('aberta','fechada') DEFAULT 'aberta',
  `numero_whatsapp` varchar(20) DEFAULT NULL,
  `whatsapp_message_id` varchar(255) DEFAULT NULL,
  `motivo_erro` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `backup_mensagens_comunicacao_original`
--

INSERT INTO `backup_mensagens_comunicacao_original` (`id`, `canal_id`, `canal_nome`, `cliente_id`, `cobranca_id`, `mensagem`, `anexo`, `tipo`, `data_hora`, `direcao`, `status`, `status_conversa`, `numero_whatsapp`, `whatsapp_message_id`, `motivo_erro`) VALUES
(40, 36, 'Financeiro', 257, NULL, 'Olá Gutemberg! Sua fatura com vencimento em 12/07/2025 está aguardando pagamento. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/845hw1wnor9l01a4\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-11 18:20:24', 'enviado', 'enviado', 'aberta', '73981552730', NULL, NULL),
(43, 36, 'Financeiro', 2862, NULL, 'Mensagem de teste recebida via webhook (PHP)', NULL, 'texto', '2025-07-11 21:41:55', 'recebido', 'lido', 'aberta', '5599999999999', NULL, NULL),
(45, 36, 'Financeiro', 286, NULL, 'Olá Wilmar! Sua fatura com vencimento em 15/07/2025 está aguardando pagamento. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/nhneruj36ed4stkf\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-14 14:21:12', 'enviado', 'enviado', 'aberta', '4898581874', NULL, NULL),
(46, 36, 'Financeiro', 198, NULL, 'Cobrança enviada via WhatsApp em 14/07/2025 12:39', NULL, 'texto', '2025-07-14 15:40:16', 'enviado', 'enviado', 'aberta', '554797009768', NULL, NULL),
(58, 36, 'Financeiro', 196, 20745, 'Tentativa de envio de cobrança via WhatsApp em 14/07/2025 14:27 - ERRO', NULL, 'texto', '2025-07-14 17:28:15', 'enviado', 'erro', 'aberta', '21976209602', NULL, NULL),
(59, 36, 'Financeiro', 196, 20745, 'Cobrança enviada via WhatsApp em 14/07/2025 15:32', NULL, 'texto', '2025-07-14 18:33:39', 'enviado', 'enviado', 'aberta', '21976209602', NULL, NULL),
(60, 36, 'Financeiro', 196, NULL, 'Boa tarde, você quer que retire seu site do ar, só para confirmar. Neste caso, baixamos da nossa hospedagem e lhe enviamos o arquivo.', NULL, 'texto', '2025-07-14 16:01:00', 'enviado', 'enviado', 'aberta', '21976209602', NULL, NULL),
(61, 36, 'Financeiro', 222, 20747, 'Cobrança enviada via WhatsApp em 14/07/2025 16:10', NULL, 'texto', '2025-07-14 19:11:27', 'enviado', 'enviado', 'aberta', '62985489901', NULL, NULL),
(62, 36, 'Financeiro', 215, 20755, 'Tentativa de envio de cobrança via WhatsApp em 14/07/2025 16:22 - ERRO', NULL, 'texto', '2025-07-14 19:23:46', 'enviado', 'erro', 'aberta', '41988290646', NULL, NULL),
(63, 36, 'Financeiro', 215, 20755, 'Cobrança enviada via WhatsApp em 14/07/2025 16:32', NULL, 'texto', '2025-07-14 19:33:42', 'enviado', 'enviado', 'aberta', '41988290646', NULL, NULL),
(64, 36, 'Financeiro', 163, 20759, 'Cobrança enviada via WhatsApp em 14/07/2025 16:33', NULL, 'texto', '2025-07-14 19:34:25', 'enviado', 'enviado', 'aberta', '85991938872', NULL, NULL),
(65, 36, 'Financeiro', 275, 21273, 'Cobrança enviada via WhatsApp em 14/07/2025 16:33', NULL, 'texto', '2025-07-14 19:34:51', 'enviado', 'enviado', 'aberta', '98987182714', NULL, NULL),
(66, 36, 'Financeiro', 164, 20763, 'Cobrança enviada via WhatsApp em 14/07/2025 16:38', NULL, 'texto', '2025-07-14 16:38:28', 'enviado', 'enviado', 'aberta', '31988605047', NULL, NULL),
(67, 36, 'Financeiro', 283, 21024, 'Cobrança enviada via WhatsApp em 14/07/2025 16:43', NULL, 'texto', '2025-07-14 16:46:50', 'enviado', 'pendente', 'aberta', '61920007184', NULL, NULL),
(68, 36, 'Financeiro', 283, 21024, 'Cobrança enviada via WhatsApp em 14/07/2025 16:57', NULL, 'texto', '2025-07-14 16:57:12', 'enviado', 'enviado', 'aberta', '61920007184', NULL, NULL),
(69, 36, 'Financeiro', 265, 20771, 'Cobrança enviada via WhatsApp em 14/07/2025 16:59', NULL, 'texto', '2025-07-14 16:59:57', 'enviado', 'enviado', 'aberta', '49991187494', NULL, NULL),
(70, 36, 'Financeiro', 183, 20781, 'Cobrança enviada via WhatsApp em 14/07/2025 17:04', NULL, 'texto', '2025-07-14 17:04:36', 'enviado', 'enviado', 'aberta', '81997076042', NULL, NULL),
(71, 36, 'Financeiro', 188, 20783, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:06', 'enviado', 'enviado', 'aberta', '11980441758', NULL, NULL),
(72, 36, 'Financeiro', 182, 20775, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:26', 'enviado', 'enviado', 'aberta', '11974958004', NULL, NULL),
(73, 36, 'Financeiro', 266, 20773, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:37', 'enviado', 'enviado', 'aberta', '92981543898', NULL, NULL),
(74, 36, 'Financeiro', 206, 20787, 'Cobrança enviada via WhatsApp em 14/07/2025 17:06', NULL, 'texto', '2025-07-14 17:06:46', 'enviado', 'enviado', 'aberta', '65981047654', NULL, NULL),
(75, 36, 'Financeiro', 209, 20789, 'Cobrança enviada via WhatsApp em 14/07/2025 17:07', NULL, 'texto', '2025-07-14 17:07:42', 'enviado', 'enviado', 'aberta', '14997501745', NULL, NULL),
(76, 36, 'Financeiro', 199, 20793, 'Cobrança enviada via WhatsApp em 14/07/2025 17:08', NULL, 'texto', '2025-07-14 17:09:15', 'enviado', 'pendente', 'aberta', '64996431037', NULL, NULL),
(77, 36, 'Financeiro', 199, 20793, 'Cobrança enviada via WhatsApp em 14/07/2025 17:11', NULL, 'texto', '2025-07-14 17:11:13', 'enviado', 'enviado', 'aberta', '64996431037', NULL, NULL),
(78, 36, 'Financeiro', 158, 21050, 'Cobrança enviada via WhatsApp em 14/07/2025 17:11', NULL, 'texto', '2025-07-14 17:13:38', 'enviado', 'pendente', 'aberta', '11987177060', NULL, NULL),
(79, 36, 'Financeiro', 158, 21050, 'Cobrança enviada via WhatsApp em 14/07/2025 17:16', NULL, 'texto', '2025-07-14 17:16:27', 'enviado', 'enviado', 'aberta', '11987177060', NULL, NULL),
(80, 36, 'Financeiro', 234, 20795, 'Cobrança enviada via WhatsApp em 14/07/2025 17:17', NULL, 'texto', '2025-07-14 17:17:17', 'enviado', 'enviado', 'aberta', '92991953335', NULL, NULL),
(81, 36, 'Financeiro', 227, 20799, 'Cobrança enviada via WhatsApp em 14/07/2025 17:17', NULL, 'texto', '2025-07-14 17:17:39', 'enviado', 'enviado', 'aberta', '11934707141', NULL, NULL),
(82, 36, 'Financeiro', 232, 20801, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:14', 'enviado', 'enviado', 'aberta', '62985793436', NULL, NULL),
(83, 36, 'Financeiro', 240, 20805, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:35', 'enviado', 'enviado', 'aberta', '81998790053', NULL, NULL),
(84, 36, 'Financeiro', 211, 22085, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:56', 'enviado', 'enviado', 'aberta', '37998765431', NULL, NULL),
(85, 36, 'Financeiro', 225, 20807, 'Cobrança enviada via WhatsApp em 14/07/2025 17:19', NULL, 'texto', '2025-07-14 17:19:17', 'enviado', 'enviado', 'aberta', '11989541000', NULL, NULL),
(86, 36, 'Financeiro', 263, 20809, 'Cobrança enviada via WhatsApp em 14/07/2025 17:19', NULL, 'texto', '2025-07-14 17:19:48', 'enviado', 'enviado', 'aberta', '87999884234', NULL, NULL),
(87, 36, 'Financeiro', 240, 27137, 'Cobrança enviada via WhatsApp em 15/07/2025 17:08', NULL, 'texto', '2025-07-15 17:08:54', 'enviado', 'enviado', 'aberta', '81998790053', NULL, NULL),
(88, 36, 'Financeiro', 225, 27138, 'Cobrança enviada via WhatsApp em 15/07/2025 17:09', NULL, 'texto', '2025-07-15 17:09:09', 'enviado', 'enviado', 'aberta', '11989541000', NULL, NULL),
(89, 36, 'Financeiro', 148, 27146, 'Cobrança enviada via WhatsApp em 15/07/2025 17:09', NULL, 'texto', '2025-07-15 17:09:18', 'enviado', 'enviado', 'aberta', '93991439400', NULL, NULL),
(91, 36, 'Financeiro', 228, 27416, 'Tentativa de envio de cobrança via WhatsApp em 15/07/2025 17:09 - ERRO', NULL, 'texto', '2025-07-15 17:09:56', 'enviado', 'erro', 'aberta', '61982428290', NULL, NULL),
(92, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:12', NULL, 'texto', '2025-07-15 17:15:40', 'enviado', 'pendente', 'aberta', '61982428290', NULL, NULL),
(93, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:15', NULL, 'texto', '2025-07-15 17:16:33', 'enviado', 'pendente', 'aberta', '61982428290', NULL, NULL),
(94, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:16', NULL, 'texto', '2025-07-15 17:16:41', 'enviado', 'enviado', 'aberta', '61982428290', NULL, NULL),
(95, 36, 'Financeiro', 283, 27247, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:19', 'enviado', 'enviado', 'aberta', '61920007184', NULL, NULL),
(96, 36, 'Financeiro', 158, 27260, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:42', 'enviado', 'enviado', 'aberta', '11987177060', NULL, NULL),
(97, 36, 'Financeiro', 211, 27781, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:54', 'enviado', 'enviado', 'aberta', '37998765431', NULL, NULL),
(99, 36, 'Financeiro', 219, 27332, 'Cobrança enviada via WhatsApp em 15/07/2025 17:18', NULL, 'texto', '2025-07-15 17:18:16', 'enviado', 'enviado', 'aberta', '51998679078', NULL, NULL),
(100, 36, 'Financeiro', 222, 27110, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:06', 'enviado', 'enviado', 'aberta', '62985489901', NULL, NULL),
(101, 36, 'Financeiro', 286, 27112, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:20', 'enviado', 'enviado', 'aberta', '4898581874', NULL, NULL),
(102, 36, 'Financeiro', 215, 27114, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:31', 'enviado', 'enviado', 'aberta', '41988290646', NULL, NULL),
(103, 36, 'Financeiro', 163, 27116, 'Cobrança enviada via WhatsApp em 15/07/2025 17:20', NULL, 'texto', '2025-07-15 17:20:05', 'enviado', 'enviado', 'aberta', '85991938872', NULL, NULL),
(104, 36, 'Financeiro', 275, 27373, 'Status manual inserido', NULL, 'manual', '2025-07-15 17:57:56', 'enviado', 'enviado', 'aberta', '98987182714', NULL, NULL),
(105, 36, 'Financeiro', 265, 27122, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:08:31', 'enviado', 'enviado', 'aberta', '49991187494', NULL, NULL),
(106, 36, 'Financeiro', 266, 27123, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:01', 'enviado', 'enviado', 'aberta', '92981543898', NULL, NULL),
(107, 36, 'Financeiro', 182, 27124, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:15', 'enviado', 'enviado', 'aberta', '11974958004', NULL, NULL),
(108, 36, 'Financeiro', 183, 27125, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:21', 'enviado', 'enviado', 'aberta', '81997076042', NULL, NULL),
(109, 36, 'Financeiro', 188, 27126, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:32', 'enviado', 'enviado', 'aberta', '11980441758', NULL, NULL),
(110, 36, 'Financeiro', 206, 27128, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:14:02', 'enviado', 'enviado', 'aberta', '65981047654', NULL, NULL),
(111, 36, 'Financeiro', 209, 27129, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:15:13', 'enviado', 'enviado', 'aberta', '14997501745', NULL, NULL),
(112, 36, 'Financeiro', 232, 27135, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:31:28', 'enviado', 'enviado', 'aberta', '62985793436', NULL, NULL),
(113, 36, 'Financeiro', 227, 27134, 'Cobrança enviada via WhatsApp em 15/07/2025 18:31', 'true_5511934707141@c.us_3EB04B00CFDC5D185ED158', 'texto', '2025-07-15 18:31:43', 'enviado', 'enviado', 'aberta', '11934707141', NULL, NULL),
(114, 36, 'Financeiro', 234, 27132, 'Cobrança enviada via WhatsApp em 15/07/2025 18:31', 'true_559291953335@c.us_3EB0FD52F6B4CA2B7CF084', 'texto', '2025-07-15 18:31:59', 'enviado', 'enviado', 'aberta', '92991953335', NULL, NULL),
(115, 36, 'Financeiro', 199, 27131, 'Cobrança enviada via WhatsApp em 15/07/2025 18:32', 'true_556496431037@c.us_3EB00560071410ACFAF138', 'texto', '2025-07-15 18:32:21', 'enviado', 'enviado', 'aberta', '64996431037', NULL, NULL),
(138, 36, 'Financeiro', 206, NULL, '2025-07-16_081913.pdf', NULL, 'texto', '2025-07-16 09:19:31', 'recebido', 'lido', 'aberta', '65981047654', NULL, NULL),
(139, 36, 'Financeiro', 206, NULL, '', NULL, 'texto', '2025-07-16 09:21:26', 'recebido', 'lido', 'aberta', '65981047654', NULL, NULL),
(140, 36, 'Financeiro', 0, NULL, 'Cobrança enviada via WhatsApp em 16/07/2025 13:53', 'true_554796164699@c.us_3EB0C005CFE22C9E51FC2F', 'texto', '2025-07-16 13:53:52', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(144, 36, 'Financeiro', 0, NULL, 'Cobrança enviada via WhatsApp em 16/07/2025 13:59', 'true_554796164699@c.us_3EB03FA523AFB865C5516E', 'texto', '2025-07-16 13:59:54', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(176, 36, 'Financeiro', 4296, NULL, 'Teste de envio 13:55 18/07', '', 'texto', '2025-07-18 13:56:13', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(177, 36, 'Financeiro', 4296, NULL, 'Teste final - formatação corrigida - 14:11:38', NULL, 'texto', '2025-07-18 14:11:39', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(178, 36, 'Financeiro', 4296, NULL, 'teste de envio 14:26 17/07/25', '', 'texto', '2025-07-18 14:40:14', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(179, 36, 'Financeiro', 4296, NULL, 'teste de envio 14:48 18/07/25', '', 'texto', '2025-07-18 14:49:31', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(180, 36, 'Financeiro', 4296, NULL, 'tesde de envio 14:59 18/07/25', '', 'texto', '2025-07-18 15:00:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(182, 36, 'Financeiro', 4296, NULL, 'teste 14:41', '', 'texto', '2025-07-21 14:41:26', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(183, 36, 'Financeiro', 4296, NULL, 'teste 21/07 14:51', '', 'texto', '2025-07-21 14:51:51', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(184, 36, 'Financeiro', 4296, NULL, 'mensagem de teste 21/07 14:52', '', 'texto', '2025-07-21 14:52:25', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(185, 36, 'Financeiro', 4296, NULL, 'nova mensagem de teste', '', 'texto', '2025-07-21 14:52:42', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(186, 36, 'Financeiro', 4296, NULL, 'nova mensagem de teste atualizada agora', '', 'texto', '2025-07-21 14:53:11', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(187, 36, 'Financeiro', 4296, NULL, 'olá', '', 'texto', '2025-07-21 15:51:42', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(188, 36, 'Financeiro', 4296, NULL, 'teste envio dia 22/07/2025 08:23', '', 'texto', '2025-07-22 08:23:12', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(190, 36, 'Financeiro', 4296, NULL, 'Mensagem enviada para Pixel12Digital em 22/07/2025 às 08:31', '', 'texto', '2025-07-22 08:31:48', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(191, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 09:44:27', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(192, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:31:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(193, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:43:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(194, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:49:31', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(195, 36, 'Financeiro', 4296, NULL, 'Mensagem teste webhook manual', NULL, 'texto', '2025-07-22 13:43:23', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(196, 36, 'Financeiro', 4296, NULL, 'MENSAGEM TESTE 15:46', '', 'texto', '2025-07-22 15:46:16', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(197, 36, 'Financeiro', 4425, NULL, 'TESTE DIRETO WEBHOOK 16:15:55', NULL, 'text', '2025-07-22 16:15:56', 'recebido', 'lido', 'aberta', '47996164699@c.us', NULL, NULL),
(198, 36, 'Financeiro', 4425, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:15:56', 'enviado', 'enviado', 'aberta', '47996164699@c.us', NULL, NULL),
(199, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 16:50', NULL, 'chat', '2025-07-22 16:50:35', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(200, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:50:35', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(201, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 16:50', NULL, 'chat', '2025-07-22 16:51:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(202, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:51:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(203, 36, 'Financeiro', 4296, NULL, 'obrigado', NULL, 'chat', '2025-07-22 16:52:20', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(205, 36, 'Financeiro', 4296, NULL, 'novo teste', NULL, 'chat', '2025-07-22 16:53:33', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(206, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:53:33', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(207, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:04', NULL, 'chat', '2025-07-22 17:04:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(208, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:14', NULL, 'chat', '2025-07-22 17:14:10', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(209, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:27', NULL, 'chat', '2025-07-22 17:27:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(210, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 18:20', NULL, 'chat', '2025-07-22 18:20:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(211, 36, 'Financeiro', 4296, NULL, 'Recebido 18:23', '', 'texto', '2025-07-22 18:23:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(212, 36, 'Financeiro', 145, 49665, 'Olá Nicelio! Lembrete: sua fatura vence hoje. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/uatslddjrkpj9saj\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-23 18:26:52', 'enviado', 'enviado', 'aberta', '11965221349', NULL, NULL),
(213, 36, 'Financeiro', 274, 58758, 'Olá Eduardo! Sua fatura com vencimento em 06/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/3p5pmnce6om5u6sw\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-25 12:05:06', 'enviado', 'enviado', 'aberta', '11964583101', NULL, NULL),
(214, 36, 'Financeiro', 4296, NULL, 'Olá, preciso de informações sobre minha fatura', NULL, 'text', '2025-07-28 15:44:02', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(215, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:44:02', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(216, 36, 'Financeiro', 4296, NULL, 'Boa tarde, gostaria de saber sobre meu plano', NULL, 'text', '2025-07-28 15:44:05', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(218, 36, 'Financeiro', 2862, NULL, 'Olá, vocês fazem sites?', NULL, 'text', '2025-07-28 15:44:07', 'recebido', 'lido', 'aberta', '5599999999999', NULL, NULL),
(219, 36, 'Financeiro', 2862, NULL, 'Olá Cliente WhatsApp (5599999999999)! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:44:07', 'enviado', 'enviado', 'aberta', '5599999999999', NULL, NULL),
(220, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-28 15:52:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(221, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:52:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(222, 36, 'Financeiro', 4296, NULL, 'Ola', NULL, 'chat', '2025-07-28 15:53:14', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(224, 36, 'Financeiro', 4296, NULL, 'Oi', NULL, 'chat', '2025-07-28 15:53:18', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(226, 36, 'Financeiro', 4296, NULL, 'Olá, preciso de informações sobre minha fatura', NULL, 'text', '2025-07-28 16:02:16', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(227, 36, 'Financeiro', 4296, NULL, 'Boa tarde, gostaria de saber sobre meu plano', NULL, 'text', '2025-07-28 16:02:18', 'recebido', 'lido', 'aberta', '4796164699', NULL, NULL),
(228, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:02:18', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(229, 36, 'Financeiro', 2862, NULL, 'Olá, vocês fazem sites?', NULL, 'text', '2025-07-28 16:02:20', 'recebido', 'lido', 'aberta', '554799999999', NULL, NULL),
(230, 36, 'Financeiro', 2862, NULL, 'Olá Cliente WhatsApp (5599999999999)! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:02:20', 'enviado', 'enviado', 'aberta', '554799999999', NULL, NULL),
(231, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-28 16:05:14', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(232, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:05:14', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(233, 36, 'Financeiro', 4296, NULL, 'Não recebi minha fatura', NULL, 'chat', '2025-07-28 16:05:33', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(234, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:05:33', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(235, 36, 'Financeiro', 4296, NULL, 'oie', NULL, 'chat', '2025-07-28 16:06:30', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(236, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:06:30', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(237, 36, 'Financeiro', 4296, NULL, 'Teste de mensagem às 17:10:40', NULL, 'text', '2025-07-28 17:10:40', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(238, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 20:54:18', NULL, 'text', '2025-07-28 17:54:18', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(239, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:04:54', NULL, 'text', '2025-07-28 18:04:54', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(240, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:06:23', NULL, 'text', '2025-07-28 18:06:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(241, 36, 'Financeiro', 4296, NULL, 'Teste direto de inserção às 18:06:50', NULL, 'texto', '2025-07-28 18:06:50', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(242, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:07:12', NULL, 'text', '2025-07-28 18:07:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(243, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:08:10', NULL, 'text', '2025-07-28 18:08:11', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(244, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:08:43', NULL, 'text', '2025-07-28 18:08:43', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(245, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:11:53', NULL, 'text', '2025-07-28 18:11:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(246, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:21:52', NULL, 'text', '2025-07-28 18:21:52', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(247, 36, 'Financeiro', 4296, NULL, 'teste às 19:11', NULL, 'texto', '2025-07-28 19:11:00', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(248, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:29:56', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(249, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:30:38', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(250, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:31:44', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(251, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:34:19', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(252, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 12:23:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(253, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 13:10:36', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(254, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 13:24:08', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(255, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 14:48:06', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(256, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 14:51:30', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(257, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 15:08:05', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(258, 36, 'Financeiro', 273, 61935, 'Olá Welton! Sua fatura com vencimento em 19/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/anlb2s72bwu3nfs9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:11:46', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(259, 37, 'Comercial - Pixel', 1, 1, 'Status manual inserido', NULL, 'manual', '2025-07-29 17:10:44', 'enviado', 'pendente', 'aberta', NULL, NULL, NULL),
(260, 36, 'Financeiro', 273, 61935, 'Olá Welton! Sua fatura com vencimento em 19/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/anlb2s72bwu3nfs9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:12:48', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(262, 36, 'Financeiro', 145, NULL, 'Olá Nicelio! \n\n⚠️ Você possui faturas em aberto:\n\n• Fatura #59720 - R$ 232,33 - Venceu em 23/07/2025 (6 dias vencida)\n\n💰 Valor total em aberto: R$ 232,33\n🔗 Link para pagamento: https://www.asaas.com/i/uatslddjrkpj9saj\n\nPara consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital', NULL, 'cobranca_vencida', '2025-07-29 20:34:05', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(263, 36, 'Financeiro', 280, 61992, 'Olá Mario! Sua fatura com vencimento em 29/08/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/stiz5kb897xiiiww\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:57:20', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(264, 36, 'Financeiro', 4297, 61701, 'Olá José Roberto! Sua fatura com vencimento em 06/09/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/v488nqp6wv7o7ss9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:59:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(265, 36, 'Financeiro', 268, 61540, 'Olá Mauro! Sua fatura com vencimento em 25/11/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/xxzswfweu4235w6a\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:04:40', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(266, 36, 'Financeiro', 221, 61272, 'Olá Anderson! Sua fatura com vencimento em 28/12/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/016pv7nqy9i1ewdc\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:04:31', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(267, 36, 'Financeiro', 221, 61272, 'Olá Anderson! Sua fatura com vencimento em 28/12/2024 está aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/016pv7nqy9i1ewdc\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:05:26', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(268, 36, 'Financeiro', 268, 61540, 'Olá Mauro! Sua fatura com vencimento em 25/11/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/xxzswfweu4235w6a\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:06:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(269, 36, 'Financeiro', 195, 61011, 'Olá João Paulo! Sua fatura com vencimento em 10/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/zet6hmpr9grwoftq\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:08:45', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(270, 36, 'Financeiro', 281, 61233, 'Olá Michael! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/e5bs0pjkomggepb0\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:14:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(271, 36, 'Financeiro', 217, 61057, 'Olá Neto! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/nzwir1vek7uqr3hd\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:18:01', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(272, 36, 'Financeiro', 217, 61057, 'Olá Neto! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/nzwir1vek7uqr3hd\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:18:34', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(296, 36, 'Financeiro', 264, NULL, 'Bom dia, tudo bem? Isto mesmo, no registro.br você pode optar pela renovação anual.', '', 'texto', '2025-07-30 09:26:31', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(297, 36, 'Financeiro', 264, NULL, 'Teste de mensagem via sistema', '', 'texto', '2025-07-30 10:25:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(298, 36, 'Financeiro', 4296, NULL, 'teste de envio 10:28', '', 'texto', '2025-07-30 10:28:12', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(301, 36, 'Financeiro', 11397, NULL, 'Olá, gostaria de informações', NULL, 'text', '2025-07-30 10:56:37', 'recebido', 'lido', 'aberta', '554799999999', NULL, NULL),
(302, 36, 'Financeiro', 11397, NULL, 'Olá Cliente Teste Corrigido! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 10:56:37', 'enviado', 'enviado', 'aberta', '554799999999', NULL, NULL),
(303, 36, 'Financeiro', 4296, NULL, 'Teste de formato de número', NULL, 'text', '2025-07-30 10:56:40', 'recebido', 'lido', 'aberta', '4796164699', NULL, NULL),
(304, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 10:56:40', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(310, 36, 'Financeiro', 236, NULL, 'Consulta', NULL, 'chat', '2025-07-30 11:54:34', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(311, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 11:54:34', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(312, 36, 'Financeiro', 236, NULL, 'Fatura', NULL, 'chat', '2025-07-30 11:54:40', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(313, 36, 'Financeiro', 217, NULL, 'Bom dia meu caros', NULL, 'chat', '2025-07-30 11:59:59', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(314, 36, 'Financeiro', 217, NULL, 'Olá Neto! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 11:59:59', 'enviado', 'enviado', 'aberta', '5516991905593', NULL, NULL),
(315, 36, 'Financeiro', 217, NULL, 'sim !!', NULL, 'chat', '2025-07-30 12:00:04', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(316, 36, 'Financeiro', 217, NULL, 'estou atento e estarei fazendo pg assim que possível', NULL, 'chat', '2025-07-30 12:00:21', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(317, 36, 'Financeiro', 217, NULL, 'obrigado', NULL, 'chat', '2025-07-30 12:00:26', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(318, 36, 'Financeiro', 4296, NULL, '📋 Suas faturas:\n\nFatura #61546\nValor: R$ 179,27\nVencimento: 15/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/s6ig3fs3v6edqivq\n\nFatura #61587\nValor: R$ 270,00\nVencimento: 01/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/7we3bjady3qmcsd5\n\nFatura #61758\nValor: R$ 40,00\nVencimento: 28/08/2024\nStatus: Paga\nLink: https://www.asaas.com/i/elvsvzvx0erg7vmm\n\n', NULL, 'texto', '2025-07-30 12:14:19', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(319, 36, 'Financeiro', 4296, NULL, '📋 Suas faturas:\n\nFatura #61546\nValor: R$ 179,27\nVencimento: 15/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/s6ig3fs3v6edqivq\n\nFatura #61587\nValor: R$ 270,00\nVencimento: 01/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/7we3bjady3qmcsd5\n\nFatura #61758\nValor: R$ 40,00\nVencimento: 28/08/2024\nStatus: Paga\nLink: https://www.asaas.com/i/elvsvzvx0erg7vmm\n\n', NULL, 'texto', '2025-07-30 12:52:12', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(320, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #79151 - R$ 89,90\n  Venceu em 25/07/2025 (5 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/teste_vencida\n\n💰 *Total vencido: R$ 89,90*\n\n🟡 *Faturas a Vencer:*\n• Fatura #79152 - R$ 129,90\n  Vence em 09/08/2025 (em 10 dias)\n  💳 Pagar: https://www.asaas.com/i/teste_a_vencer\n\n💰 *Total a vencer: R$ 129,90*\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 219,80\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 12:57:07', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(321, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Faturas a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n💰 *Total a vencer: R$ 29,90*\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 119,60\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:02:23', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(322, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:06:54', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(323, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:15:25', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(324, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para conversar com nossa equipe, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 13:23:45', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(328, 36, 'Financeiro', 4296, NULL, 'boa tarde', '', 'texto', '2025-07-30 13:57:34', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(347, 36, 'Financeiro', 236, NULL, 'Me envia todas as faturas vencidas em um boleto so, por favor', NULL, 'chat', '2025-07-30 14:48:05', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(348, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para conversar com nossa equipe, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 14:48:05', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(349, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-30 15:06:08', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(350, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 15:06:08', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(351, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 15:06:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(352, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 15:06:24', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(354, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 16:25:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(356, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:12:13', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(357, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 17:21:34', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(358, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:21:34', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(359, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 17:21:57', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(360, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 17:21:57', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(361, 36, 'Financeiro', 4296, NULL, 'falar com atendimento', NULL, 'chat', '2025-07-30 17:22:10', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(362, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:22:10', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(363, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 17:45:52', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(364, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:45:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(365, 36, 'Financeiro', 4296, NULL, 'falar com atendente', NULL, 'chat', '2025-07-30 17:45:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(366, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:45:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(367, 36, 'Financeiro', 4296, NULL, 'falar com atendente', NULL, 'chat', '2025-07-30 17:46:16', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(368, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:46:16', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(369, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:50:06', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(370, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:56:32', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(371, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 18:01:36', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(372, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nReferente à cobrança #pay_g0smfthxiro8mu53\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 19:35:04', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(373, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nReferente à cobrança #pay_g0smfthxiro8mu53\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 19:53:46', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(374, 36, 'Financeiro', 236, NULL, '\"Olá! Bem-vindo ao escritório do Detetive Aguiar. \nEstou à disposição para auxiliá-lo com qualquer investigação ou consulta que necessite.\nVamos trabalhar juntos para encontrar as respostas que você procura.\"', NULL, 'chat', '2025-07-30 20:13:27', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(375, 36, 'Financeiro', 236, NULL, '\"Olá! Estou em uma investigação. \nDeixe sua mensagem e retornarei logo. Obrigado!\"', NULL, 'chat', '2025-07-30 20:13:27', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL);
INSERT INTO `backup_mensagens_comunicacao_original` (`id`, `canal_id`, `canal_nome`, `cliente_id`, `cobranca_id`, `mensagem`, `anexo`, `tipo`, `data_hora`, `direcao`, `status`, `status_conversa`, `numero_whatsapp`, `whatsapp_message_id`, `motivo_erro`) VALUES
(376, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 59,80*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 59,80\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado ou negociações, digite *1* para falar com um atendente.', NULL, 'texto', '2025-07-30 20:13:27', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(377, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nVencimento original: 28/06/2025\nReferente à cobrança #pay_p6ae9welcaokvt0u\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 20:13:51', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(378, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nVencimento original: 28/07/2025\nReferente à cobrança #pay_bb1yjdj4tayprxab\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 20:17:04', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(379, 36, 'Financeiro', 236, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 20:19:15', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(380, 36, 'Financeiro', 4296, NULL, 'Boa noite', NULL, 'chat', '2025-07-30 20:23:03', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(381, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 20:23:03', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(382, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 20:23:03', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(383, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 20:23:50', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(384, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 20:23:50', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(385, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 20:23:50', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(386, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 20:24:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(387, 36, 'Financeiro', 4296, NULL, 'boa noite', NULL, 'chat', '2025-07-30 21:03:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(388, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 21:03:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(389, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 21:03:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(390, 36, 'Financeiro', 4296, NULL, 'mensagem recebida', '', 'texto', '2025-07-30 22:00:10', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(391, 36, 'Financeiro', 4296, NULL, 'Conversa reaberta manualmente por sistema', NULL, 'sistema', '2025-07-31 08:10:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(392, 36, 'Financeiro', 4296, NULL, 'Bom dia', '', 'texto', '2025-07-31 08:11:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(393, 36, 'Financeiro', 4296, NULL, 'Tudo bem?', '', 'texto', '2025-07-31 08:11:59', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(394, 36, 'Financeiro', 4296, NULL, 'teste 08:12', '', 'texto', '2025-07-31 08:12:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(395, 36, 'Financeiro', 4296, NULL, 'teste canal financeiro', NULL, 'text', '2025-07-31 08:30:25', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(396, 36, 'Financeiro', 4296, NULL, 'bom dia', NULL, 'chat', '2025-07-31 08:33:31', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(397, 36, 'Financeiro', 4296, NULL, 'bom dia', NULL, 'chat', '2025-07-31 08:41:26', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(398, 36, 'Financeiro', 4296, NULL, 'tudo bem?', '', 'texto', '2025-07-31 08:41:50', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(399, 36, 'Financeiro', NULL, NULL, 'Oii', NULL, 'chat', '2025-07-31 08:58:31', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(400, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 08:58:31', 'enviado', 'enviado', 'aberta', '557183323992', NULL, NULL),
(401, 36, 'Financeiro', NULL, NULL, 'Bom dia', NULL, 'chat', '2025-07-31 08:58:33', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(402, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 08:58:33', 'enviado', 'enviado', 'aberta', '557183323992', NULL, NULL),
(403, 36, 'Financeiro', NULL, NULL, 'Jailton Barros alvez', NULL, 'chat', '2025-07-31 08:59:29', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(404, 36, 'Financeiro', NULL, NULL, 'A senha de novo volto a da problema', NULL, 'chat', '2025-07-31 08:59:49', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(405, 36, 'Financeiro', NULL, NULL, 'Não consigo entra no siste', NULL, 'chat', '2025-07-31 08:59:57', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(406, 36, 'Financeiro', NULL, NULL, 'Site', NULL, 'chat', '2025-07-31 09:00:03', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(407, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:31:57', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(408, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:31:57', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(409, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:31:58', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(410, 36, 'Financeiro', 4296, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:31:58', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(411, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:31:59', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(412, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-31 09:31:59', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(413, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:00', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(414, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:32:24', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(415, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:32:25', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(416, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:32:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(417, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(418, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:32:52', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(419, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:32:52', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(420, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:32:53', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(421, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:53', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(422, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:33:24', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(423, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:34:56', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(424, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:35:12', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(425, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:35:31', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(426, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:36:02', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(427, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:36:18', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(428, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:37:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(429, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:38:04', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(430, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:40:03', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(431, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:43:36', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(432, 36, 'Financeiro', NULL, NULL, 'oi', NULL, 'text', '2025-07-31 09:46:40', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(433, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:46:40', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(434, 36, 'Financeiro', NULL, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:46:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(435, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:46:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(436, 36, 'Financeiro', NULL, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:46:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(437, 36, 'Financeiro', NULL, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:46:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(438, 36, 'Financeiro', NULL, NULL, 'faturas', NULL, 'text', '2025-07-31 09:46:54', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(439, 36, 'Financeiro', NULL, NULL, 'Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n📋 *Por favor, informe:*\n• Seu CPF ou CNPJ (apenas números, sem espaços)\n\nAssim posso buscar suas informações e repassar o status das faturas! 😊', NULL, 'texto', '2025-07-31 09:46:54', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(440, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:46:55', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(441, 36, 'Financeiro', NULL, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:47:48', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(442, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:47:48', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(443, 36, 'Financeiro', NULL, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:47:49', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(444, 36, 'Financeiro', NULL, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:47:49', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(445, 36, 'Financeiro', NULL, NULL, 'faturas', NULL, 'text', '2025-07-31 09:47:49', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(446, 36, 'Financeiro', NULL, NULL, 'Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n📋 *Por favor, informe:*\n• Seu CPF ou CNPJ (apenas números, sem espaços)\n\nAssim posso buscar suas informações e repassar o status das faturas! 😊', NULL, 'texto', '2025-07-31 09:47:49', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(447, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:47:50', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(448, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:48:42', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(449, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:51:11', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(450, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:51:11', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(451, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-31 09:51:28', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(452, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:51:43', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(453, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:51:43', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(454, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:51:44', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(455, 36, 'Financeiro', 4296, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:51:44', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(456, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:51:45', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(457, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-31 09:51:45', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(458, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:51:47', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(459, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:52:08', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(460, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:52:47', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(461, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:53:17', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(462, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:53:46', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(463, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:54:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(464, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 10:23:01', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(465, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 10:27:25', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(466, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 10:27:26', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(467, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 10:27:26', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(468, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 10:27:27', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(469, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 10:27:49', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(470, 36, 'Financeiro', NULL, NULL, 'oi', NULL, 'text', '2025-07-31 10:28:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(471, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 10:28:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(472, 36, 'Financeiro', NULL, NULL, 'qual preço do site?', NULL, 'text', '2025-07-31 10:29:39', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(473, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 10:30:39', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(474, 36, 'Financeiro', 4296, NULL, 'qual preço do site?', NULL, 'text', '2025-07-31 10:31:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(475, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:31:30', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(476, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:32:20', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(477, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:32:54', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(478, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:33:35', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(479, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:34:15', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(480, 36, 'Financeiro', 4296, NULL, 'Bom dia', NULL, 'chat', '2025-07-31 10:41:22', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(481, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:41:22', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(482, 36, 'Financeiro', 4296, NULL, 'quero falar com atendente', NULL, 'chat', '2025-07-31 10:41:39', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(483, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:41:39', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(484, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:43:55', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(485, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:43:55', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(486, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-31 11:21:08', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(487, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 11:21:08', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(488, 36, 'Financeiro', 4296, NULL, 'boa tarde', '', 'texto', '2025-07-31 13:15:11', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(489, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 13:16:01', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(490, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 13:16:01', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(491, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-31 13:48:48', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(492, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 13:48:48', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(493, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:09:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(494, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:09:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(495, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:16:06', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(496, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:16:06', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(497, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:40:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(498, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:40:12', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(499, 36, 'Financeiro', 4296, NULL, 'ola', '', 'texto', '2025-07-31 16:16:59', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(500, 37, 'Comercial - Pixel', 4296, NULL, 'Olá, boa tarde', '', 'texto', '2025-07-31 17:03:44', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(501, 37, 'Comercial - Pixel', 4296, NULL, 'teste de envio', '', 'texto', '2025-07-31 17:08:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(502, 37, 'Comercial - Pixel', 4296, NULL, 'mensagem enviada 17:09', '', 'texto', '2025-07-31 17:09:14', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(503, 36, 'Financeiro', 4296, NULL, 'Teste de envio canal financeiro', '', 'texto', '2025-07-31 17:10:04', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(504, 36, 'Financeiro', 4296, NULL, 'Teste recebimento canal 3001 - 18:12:07', NULL, 'text', '2025-07-31 18:12:08', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(505, 36, 'Financeiro', 4296, NULL, 'Teste recebimento canal 3001 - 18:13:55', NULL, 'text', '2025-07-31 18:13:56', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(506, 37, 'Comercial - Pixel', NULL, NULL, 'Teste mensagem canal 3001 - 18:32:33', NULL, 'text', '2025-07-31 18:32:34', 'entrada', NULL, 'aberta', '554797146908', NULL, NULL),
(507, 36, 'Financeiro', 4296, NULL, 'mensagem real às 18:33', NULL, 'chat', '2025-07-31 18:33:32', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(508, 37, 'Comercial - Pixel', NULL, NULL, 'Teste correção canal 3001 - 18:40:31', NULL, 'texto', '2025-07-31 18:40:32', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(509, 36, 'Financeiro', 4296, NULL, 'mensagem teste às 18:50', NULL, 'chat', '2025-07-31 18:50:06', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(510, 37, 'Comercial - Pixel', NULL, NULL, 'Teste final canal comercial - 18:56:20', NULL, 'texto', '2025-07-31 18:56:20', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(511, 36, 'Financeiro', 4296, NULL, 'mensagem confirmando novo banco de dados', NULL, 'chat', '2025-07-31 18:56:55', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(512, 36, 'Financeiro', NULL, NULL, 'https://www.instagram.com/p/DMLwVFhRYf8/?igsh=MWpkMmphN2poMTI0NQ==', NULL, 'chat', '2025-07-31 18:59:10', 'recebido', 'recebido', 'aberta', 'status@broadcast', NULL, NULL),
(513, 37, 'Comercial - Pixel', NULL, NULL, 'Teste salvamento - 18:59:29', NULL, 'texto', '2025-07-31 18:59:29', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(514, 36, 'Financeiro', 285, NULL, '', NULL, 'e2e_notification', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(515, 36, 'Financeiro', 285, NULL, '', NULL, 'notification_template', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(516, 36, 'Financeiro', 285, NULL, 'Paz do Senhor irmão', NULL, 'chat', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(517, 36, 'Financeiro', 285, NULL, 'Olá Alessandra! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 19:03:51', 'enviado', 'enviado', 'aberta', '554797471723', NULL, NULL),
(518, 36, 'Financeiro', 285, NULL, 'Na Benção', NULL, 'chat', '2025-07-31 19:03:53', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(519, 36, 'Financeiro', 285, NULL, 'Olá Alessandra! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 19:03:53', 'enviado', 'enviado', 'aberta', '554797471723', NULL, NULL),
(520, 36, 'Financeiro', 285, NULL, 'Tentei entrar no site não consegui', NULL, 'chat', '2025-07-31 19:04:09', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(521, 36, 'Financeiro', NULL, NULL, '', NULL, 'image', '2025-07-31 19:11:17', 'recebido', 'recebido', 'aberta', 'status@broadcast', NULL, NULL),
(522, 36, 'Financeiro', NULL, NULL, '', NULL, 'ptt', '2025-07-31 19:30:28', 'recebido', 'recebido', 'aberta', '5511981089874', NULL, NULL),
(523, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:39:11', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(524, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:40:50', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(525, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:44:07', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(526, 37, 'Comercial - Pixel', NULL, NULL, 'Teste mensagem canal 3001 - 19:49:47', NULL, 'text', '2025-07-31 19:49:47', 'entrada', NULL, 'aberta', '554797146908', NULL, NULL),
(527, 37, 'Comercial - Pixel', NULL, NULL, 'Teste salvamento - 19:50:12', NULL, 'texto', '2025-07-31 19:50:12', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(528, 37, 'Comercial - Pixel', 4296, NULL, 'TESTE DE ENVIO CANAL FINANCEIRO 3000 21:14', NULL, 'chat', '2025-07-31 21:14:17', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(529, 37, 'Comercial - Pixel', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-31 21:14:17', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(530, 36, 'Financeiro', 4296, NULL, 'BOM DIA', '', 'texto', '2025-08-01 09:53:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(531, 36, 'Financeiro', NULL, NULL, 'Teste de recebimento - 10:27:26', NULL, 'text', '2025-08-01 10:27:26', 'recebido', 'recebido', 'aberta', '554797146908@c.us', NULL, NULL),
(532, 36, 'Financeiro', NULL, NULL, 'Teste de recebimento - 10:27:55', NULL, 'text', '2025-08-01 10:27:55', 'recebido', 'recebido', 'aberta', '554797146908@c.us', NULL, NULL),
(533, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:31:42', NULL, 'text', '2025-08-01 10:31:42', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(534, 36, 'Financeiro', 4296, NULL, 'Bom dia', NULL, 'chat', '2025-08-01 10:34:30', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(535, 36, 'Financeiro', 4296, NULL, 'Teste  10:34', '', 'texto', '2025-08-01 10:34:58', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(536, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:40:21', NULL, 'text', '2025-08-01 10:40:22', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(537, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:45:57', NULL, 'text', '2025-08-01 10:45:58', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(538, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:51:00', NULL, 'text', '2025-08-01 10:51:00', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(539, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:52:45', NULL, 'text', '2025-08-01 10:52:46', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(540, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 10:53:25', NULL, 'text', '2025-08-01 13:53:27', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(541, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 10:59:05', NULL, 'text', '2025-08-01 13:59:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(542, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:01:28', NULL, 'text', '2025-08-01 14:01:31', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(543, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:07:04', NULL, 'text', '2025-08-01 14:07:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(544, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:11:04', NULL, 'text', '2025-08-01 14:11:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(545, 36, NULL, 4296, NULL, 'Teste completo do webhook - 11:15:54', NULL, 'text', '2025-08-01 11:15:56', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(546, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:17:03', NULL, 'text', '2025-08-01 14:17:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(547, 37, NULL, 4296, NULL, '🧪 Teste de webhook - 14:19:31', NULL, 'text', '2025-08-01 14:19:32', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(548, 37, NULL, 144, NULL, '', NULL, 'text', '2025-08-01 14:33:12', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(549, 37, NULL, 144, NULL, '', NULL, 'text', '2025-08-01 14:34:31', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(550, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 14:45', NULL, 'chat', '2025-08-01 14:45:56', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(551, 37, NULL, 4296, NULL, 'Verificação recebida 01/08 14:46', '', 'texto', '2025-08-01 14:46:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(552, 37, NULL, 4296, NULL, 'Verificação recebida 01/08 14:46', '', 'texto', '2025-08-01 14:47:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(553, 37, NULL, 4296, NULL, 'Teste com canal correto (ID 37) - 2025-08-01 14:53:23', '', 'texto', '2025-08-01 14:53:23', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(554, 37, NULL, 4296, NULL, 'Teste com canal correto (ID 37) - 2025-08-01 14:56 ENVIO', '', 'texto', '2025-08-01 14:56:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(555, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 14:57', NULL, 'chat', '2025-08-01 14:57:39', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(556, 37, NULL, 4296, NULL, 'Teste após correção da sessão - 2025-08-01 15:01:31', '', 'texto', '2025-08-01 15:01:32', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(557, 37, NULL, 4296, NULL, 'Teste após correção da sessão - 15:03', '', 'texto', '2025-08-01 15:03:51', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(558, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 15:04', NULL, 'chat', '2025-08-01 15:04:11', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(559, 37, NULL, 144, NULL, '', NULL, 'image', '2025-08-01 15:07:35', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(560, 37, NULL, 4296, NULL, 'Teste de envio do número comercial', '', 'texto', '2025-08-02 09:29:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(561, 36, NULL, 4296, NULL, 'teste de envio do número financeiro', '', 'texto', '2025-08-02 09:30:09', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(562, 37, NULL, 4296, NULL, 'Resposta teste canal Financeiro - 10:24:06', NULL, 'text', '2025-08-02 10:24:07', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(563, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:24:07', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(564, 37, NULL, 4296, NULL, 'Resposta teste canal Comercial - 10:24:08', NULL, 'text', '2025-08-02 10:24:08', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(565, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:24:08', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(566, 37, NULL, 4296, NULL, 'Resposta teste canal Financeiro - 10:25:20', NULL, 'text', '2025-08-02 10:25:21', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(567, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:25:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(568, 37, NULL, 4296, NULL, 'Resposta teste canal Comercial - 10:25:23', NULL, 'text', '2025-08-02 10:25:23', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(569, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:25:23', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(570, 37, NULL, 4296, NULL, 'Teste de recebimento Financeiro - 10:27:12 - Preciso de ajuda com pagamento', NULL, 'text', '2025-08-02 10:27:13', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(571, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:13', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(572, 37, NULL, 4296, NULL, 'Teste de recebimento Comercial - 10:27:17 - Gostaria de informações sobre produtos', NULL, 'text', '2025-08-02 10:27:18', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(573, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:18', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(574, 37, NULL, 4296, NULL, 'Teste mensagem longa - 10:27:25 - Olá, gostaria de saber mais sobre os servicos oferecidos pela empresa. Tenho interesse em contratar e preciso de mais detalhes sobre precos e prazos de entrega.', NULL, 'text', '2025-08-02 10:27:25', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(575, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(576, 37, NULL, 4296, NULL, 'Teste final de verificacao - 10:28:30 - Por favor, confirme se esta mensagem aparece no chat do sistema', NULL, 'text', '2025-08-02 10:28:31', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(577, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:28:31', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(578, 37, NULL, 4296, NULL, 'Verificação de mensagem recebida no canal 3000 +55 47 9714-6908 02/08 - 10:31 *Deve aparecer no chat*', NULL, 'chat', '2025-08-02 10:31:37', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(579, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:31:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(580, 37, NULL, 4296, NULL, 'TESTE FINAL Financeiro UNIFICADO - 10:40:52', NULL, 'text', '2025-08-02 10:40:53', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(581, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:40:53', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(582, 37, NULL, 4296, NULL, 'TESTE FINAL Comercial UNIFICADO - 10:40:57', NULL, 'text', '2025-08-02 10:40:57', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(583, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:40:57', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(584, 37, NULL, 4296, NULL, 'TESTE MENSAGEM LONGA COMERCIAL - 10:41:01 - Esta é uma mensagem mais longa para verificar se o sistema processa corretamente mensagens extensas vindas do canal comercial. Deve aparecer no chat como \'Comercial - Pixel\'.', NULL, 'text', '2025-08-02 10:41:02', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(585, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:41:02', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(586, 37, NULL, 4296, NULL, 'Verificação de mensagem canal 3001 +55 47 9730-9525 02/08 - 10:44 Deve aparecer no chat', NULL, 'chat', '2025-08-02 10:44:54', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(587, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:44:54', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(588, 36, NULL, 4296, NULL, 'Verificação de mensagem recebida no canal 3000 +55 47 9714-6908 02/08 - 10:45 Deve aparecer no chat', NULL, 'chat', '2025-08-02 10:45:35', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(589, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:45:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `bloqueios_ana`
--

CREATE TABLE `bloqueios_ana` (
  `id` int(11) NOT NULL,
  `numero_cliente` varchar(20) NOT NULL,
  `motivo` enum('transferencia_humano','solicitacao_manual','problema_tecnico','outros') DEFAULT 'transferencia_humano',
  `data_bloqueio` datetime NOT NULL,
  `data_desbloqueio` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `observacoes` text DEFAULT NULL,
  `criado_por` varchar(50) DEFAULT 'sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `canais_comunicacao`
--

CREATE TABLE `canais_comunicacao` (
  `id` int(11) NOT NULL,
  `tipo` varchar(32) NOT NULL,
  `identificador` varchar(64) NOT NULL,
  `status` varchar(32) NOT NULL,
  `nome_exibicao` varchar(64) DEFAULT NULL,
  `data_conexao` datetime DEFAULT NULL,
  `porta` int(11) DEFAULT NULL,
  `sessao` varchar(50) DEFAULT NULL,
  `endpoint` varchar(128) DEFAULT NULL,
  `pasta_sessao` varchar(128) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `ultimo_envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `canais_comunicacao`
--

INSERT INTO `canais_comunicacao` (`id`, `tipo`, `identificador`, `status`, `nome_exibicao`, `data_conexao`, `porta`, `sessao`, `endpoint`, `pasta_sessao`, `pid`, `ultimo_envio`) VALUES
(36, 'whatsapp', '554797146908@c.us', 'conectado', 'Pixel12Digital', '2025-07-31 19:56:43', 3000, 'default', NULL, NULL, NULL, NULL),
(37, 'whatsapp', '554797309525@c.us', 'conectado', 'Comercial - Pixel', '2025-07-31 21:31:22', 3001, 'comercial', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `canais_padrao_funcoes`
--

CREATE TABLE `canais_padrao_funcoes` (
  `id` int(11) NOT NULL,
  `funcao` varchar(50) NOT NULL,
  `canal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `canais_padrao_funcoes`
--

INSERT INTO `canais_padrao_funcoes` (`id`, `funcao`, `canal_id`) VALUES
(1, 'financeiro', 36),
(4, 'comercial', 37);

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `asaas_id` varchar(64) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(50) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(50) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL,
  `notificacao_desativada` tinyint(1) DEFAULT NULL,
  `emails_adicionais` varchar(255) DEFAULT NULL,
  `referencia_externa` varchar(100) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `razao_social` varchar(255) DEFAULT NULL,
  `criado_em_asaas` datetime DEFAULT NULL,
  `cpf_cnpj` varchar(32) DEFAULT NULL,
  `data_criacao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `telefone_editado_manual` tinyint(1) DEFAULT 0,
  `celular_editado_manual` tinyint(1) DEFAULT 0,
  `email_editado_manual` tinyint(1) DEFAULT 0,
  `nome_editado_manual` tinyint(1) DEFAULT 0,
  `endereco_editado_manual` tinyint(1) DEFAULT 0,
  `data_ultima_edicao_manual` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `clientes`
--

INSERT INTO `clientes` (`id`, `asaas_id`, `nome`, `contact_name`, `email`, `telefone`, `celular`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `pais`, `notificacao_desativada`, `emails_adicionais`, `referencia_externa`, `observacoes`, `razao_social`, `criado_em_asaas`, `cpf_cnpj`, `data_criacao`, `data_atualizacao`, `endereco`, `telefone_editado_manual`, `celular_editado_manual`, `email_editado_manual`, `nome_editado_manual`, `endereco_editado_manual`, `data_ultima_edicao_manual`) VALUES
(144, 'cus_000124522605', 'Josenilson Alves Figueiredo Ltda', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '', '2025-07-02 16:05:05', '2025-07-29 19:48:55', '', 0, 0, 0, 0, 0, '2025-07-29 19:48:54'),
(145, 'cus_000123603388', 'JP TRASLADOS LTDA | Nicelio Salustiano dos santos', 'Nicelio', 'jptraslados1@gmail.com', '', '11965221349', '06236795', 'Rua Gavião', '128', 'Casa 02', 'Aliança', 'Osasco', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '53419836000191', '2025-07-02 16:05:05', '2025-07-29 10:45:57', 'Rua Gavião', 0, 0, 0, 0, 0, NULL),
(146, 'cus_000122052678', 'Edivarde Ferreira', NULL, 'emaressencia@gmail.com', '', '11961490436', '62595000', 'Av Francisco Xavier Chaves', '165', 'Sala 4 Lado B', 'Praia do Prea', '6713', 'CE', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '60979557000148', '2025-07-02 16:05:05', '2025-07-29 10:45:58', 'Av Francisco Xavier Chaves', 0, 0, 0, 0, 0, NULL),
(147, 'cus_000121413207', 'Carlos Wanderley Souza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:06', '2025-07-18 17:09:44', NULL, 0, 0, 0, 0, 0, NULL),
(148, 'cus_000120331511', 'Mario Orlando Batista Dezincourt', 'Mario Orlando', 'sommaoffice@gmail.com', '', '93991439400', '68182505', 'Avenida dos Ipês', '258', NULL, 'Jardim América', '5978', 'PA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '36202606215', '2025-07-02 16:05:06', '2025-07-29 10:46:00', 'Avenida dos Ipês', 0, 0, 0, 0, 0, NULL),
(149, 'cus_000119400702', 'S L Comercio De Suprimentos Industriais E Servicos Ltda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:06', '2025-07-18 17:09:45', NULL, 0, 0, 0, 0, 0, NULL),
(150, 'cus_000118956407', 'COMUNIDADE RENOVO PAZ E VIDA', NULL, 'adm.pvmzs@gmail.com', '', '', '05518100', 'Rua Tristão de Campos', '161', NULL, 'Jardim Trussardi', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '47478331000103', '2025-07-02 16:05:07', '2025-07-29 10:46:02', 'Rua Tristão de Campos', 0, 0, 0, 0, 0, NULL),
(151, 'cus_000118947389', 'Sigilos Bar Ltda', 'Rafael', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:07', '2025-07-18 17:09:46', NULL, 0, 0, 0, 0, 0, NULL),
(152, 'cus_000118875522', 'Renan Reis Sousa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:07', '2025-07-18 17:09:46', NULL, 0, 0, 0, 0, 0, NULL),
(153, 'cus_000118810118', 'Joao Luvuezo Kiala Marques', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:08', '2025-07-18 17:09:46', NULL, 0, 0, 0, 0, 0, NULL),
(154, 'cus_000117401261', 'Sueliton De Oliveira Silva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:08', '2025-07-18 17:09:47', NULL, 0, 0, 0, 0, 0, NULL),
(155, 'cus_000116268877', 'Robson Dos Santos Moreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:08', '2025-07-18 17:09:47', NULL, 0, 0, 0, 0, 0, NULL),
(156, 'cus_000116158772', 'Charles Dietrich', 'Charles', 'dietrich.representacoes@gmail.com', '', '47996164699', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '03454769990', '2025-07-02 16:05:09', '2025-07-29 10:46:09', '', 0, 0, 0, 0, 0, NULL),
(157, 'cus_000115727734', 'Francisco Alves Dos Santos Veterinario', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:09', '2025-07-18 17:09:48', NULL, 0, 0, 0, 0, 0, NULL),
(158, 'cus_000115624411', 'Hélio Vicente Ferreira', 'Hélio', 'helio.ferreira@keystoneco.com.br', '', '11987177060', '57301185', 'Rua Marechal Deodoro da Fonseca', '41', NULL, 'Ouro Preto', '8442', 'AL', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '03129049401', '2025-07-02 16:05:09', '2025-07-29 10:46:11', 'Rua Marechal Deodoro da Fonseca', 0, 0, 0, 0, 0, NULL),
(159, 'cus_000115280057', 'Dsl Manutencao E Instalacoes Industriais Ltda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:10', '2025-07-18 17:09:48', NULL, 0, 0, 0, 0, 0, NULL),
(160, 'cus_000114662141', 'João Luvuezo Kiala Marques', NULL, 'joaoluvuezokialam@hotmail.com', '', '41996206584', '80230110', 'Avenida Marechal Floriano Peixoto', '1906', NULL, 'Rebouças', '13405', 'PR', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '55058559000164', '2025-07-02 16:05:10', '2025-07-29 10:46:13', 'Avenida Marechal Floriano Peixoto', 0, 0, 0, 0, 0, NULL),
(161, 'cus_000114627081', 'Antonio Batista Soares', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:10', '2025-07-18 17:09:49', NULL, 0, 0, 0, 0, 0, NULL),
(162, 'cus_000114407430', 'Africa Cargo Logistica Ltda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:11', '2025-07-18 17:09:49', NULL, 0, 0, 0, 0, 0, NULL),
(163, 'cus_000114048362', 'Alcibiades de Souza Bevilaqua | Hotel Sinhá', 'Alcibiades', 'decordsigner@gmail.com', '', '85991938872', '62300000', 'Rod BR 222', 'Km 307', NULL, 'Centro', '6763', 'CE', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '30134722353', '2025-07-02 16:05:11', '2025-07-29 10:46:17', 'Rod BR 222', 0, 0, 0, 0, 0, NULL),
(164, 'cus_000113729096', 'CASA DE RACOES SILVA LTDA | José Nilton', 'José Nilton', 'ITAFERREIRASILVA@GMAIL.COM', '', '31988605047', '33805035', 'Rua Ari Teixeira da Costa', '1019', 'GALPÃO', 'Nossa Senhora das Neves', '10111', 'MG', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '08488948000140', '2025-07-02 16:05:11', '2025-07-29 10:46:18', 'Rua Ari Teixeira da Costa', 0, 0, 0, 0, 0, NULL),
(165, 'cus_000113497524', 'Wilmar Augusto Ibers', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:12', '2025-07-18 17:09:50', NULL, 0, 0, 0, 0, 0, NULL),
(166, 'cus_000113483218', 'Claudinei Da Silva 80226400182', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:12', '2025-07-18 17:09:51', NULL, 0, 0, 0, 0, 0, NULL),
(167, 'cus_000113315075', 'Renato Silva da Silva Júnior | Ponto do Golfe', 'Renato', 'renatoProgolf@gmail.com', '', '5381642320', '83420000', 'Rua João Knapik', '648', NULL, 'Centro', '13412', 'PR', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '05093219924', '2025-07-02 16:05:12', '2025-07-29 22:32:17', 'Rua João Knapik', 0, 1, 0, 0, 0, '2025-07-29 22:32:17'),
(168, 'cus_000112454194', 'FRANCISCO PAULO VITOR DA SILVA', NULL, 'pauloadrian588@gmail.com', '', '35999154212', '37187236', 'Rua Vereador Abel Alves', '101', NULL, 'Botafogo', '10606', 'MG', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '83622080663', '2025-07-02 16:05:13', '2025-07-29 10:46:22', 'Rua Vereador Abel Alves', 0, 0, 0, 0, 0, NULL),
(169, 'cus_000112071488', 'Gilberto Santana Pereira', 'Gilberto', '_betosantana@hotmail.com', '', '6198312126', '72410801', 'Quadra Quadra 15 Conjunto A', 'Casa 20', NULL, 'Setor Sul (Gama)', '15872', 'DF', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '98851900159', '2025-07-02 16:05:13', '2025-07-29 21:48:50', 'Quadra Quadra 15 Conjunto A', 0, 1, 0, 0, 0, '2025-07-29 21:48:50'),
(170, 'cus_000111861088', 'Luiz Antônio Da Silva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:14', '2025-07-18 17:09:52', NULL, 0, 0, 0, 0, 0, NULL),
(171, 'cus_000111734444', 'Marta Cenci', NULL, 'martacenci@outlook.com', '', '49991780896', '89900000', 'Rua Victacyr Barazetti', '310', NULL, 'Centro', '13474', 'SC', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02899599933', '2025-07-02 16:05:14', '2025-07-29 10:46:26', 'Rua Victacyr Barazetti', 0, 0, 0, 0, 0, NULL),
(172, 'cus_000111671945', 'ARY INACIO BARBOSA DOS SANTOS | IS Corp', NULL, 'inaciobarbosadossantos25@gmail.com', '', '92995021751', '69048040', 'Conjunto Senador João Bosco Ramos de Lima', 'APTO. 203', 'BLOCO 5', 'Flores', '5687', 'AM', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '79003150206', '2025-07-02 16:05:14', '2025-07-29 10:46:27', 'Conjunto Senador João Bosco Ramos de Lima', 0, 0, 0, 0, 0, NULL),
(173, 'cus_000111146663', 'Debora Hermana De Santana 44311765860', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:15', '2025-07-18 17:09:53', NULL, 0, 0, 0, 0, 0, NULL),
(174, 'cus_000111046265', 'JCI INTALACOES ELETRICAS E MANUTENCAO LTDA | Toninho', 'Toninho', 'jcieletrica24@gmail.com', '', '11983606362', '06226266', 'Rua João Pessoa', '65', NULL, 'Rochdale', '12530', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '46517045000139', '2025-07-02 16:05:15', '2025-07-29 22:39:17', 'Rua João Pessoa', 0, 0, 0, 0, 0, '2025-07-29 22:39:17'),
(175, 'cus_000110848666', 'Carlos Rodrigo Machado Patrício', 'Carlos Rodrigo', 'africacarga@gmail.com', '', '11939378058', '07744425', 'Rua Orlando Peccicaco', '106', NULL, 'Laranjeiras', '12533', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '33413265881', '2025-07-02 16:05:15', '2025-07-29 10:46:30', 'Rua Orlando Peccicaco', 0, 0, 0, 0, 0, NULL),
(176, 'cus_000110749075', 'Jci Intalacoes Eletricas E Manutencao Ltda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:16', '2025-07-18 17:09:54', NULL, 0, 0, 0, 0, 0, NULL),
(177, 'cus_000110572623', 'Sabrina Saviti Jorge', NULL, NULL, NULL, '61998617359', '76382175', 'Rua 25', '530', NULL, 'Setor Sul', '15636', 'GO', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '06471769121', '2025-07-02 16:05:16', '2025-07-18 17:09:55', NULL, 0, 0, 0, 0, 0, NULL),
(178, 'cus_000109689635', 'Hellen Moraes Silva de Godoy', NULL, 'hellenmoraes5555@gmail.com', '', '62998251900', '74912130', 'Rua 41', 'Q 56, LT 4', NULL, 'Jardim Bela Vista - Continuação', '15719', 'GO', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '94655626100', '2025-07-02 16:05:16', '2025-07-29 10:46:33', 'Rua 41', 0, 0, 0, 0, 0, NULL),
(179, 'cus_000109324510', 'Jessica Tatiane Mendes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:17', '2025-07-18 17:09:55', NULL, 0, 0, 0, 0, 0, NULL),
(180, 'cus_000109214712', 'Antônio Fernandes da Silva | Calha em Curitiba', 'Antônio', 'fesilvafernandes91@gmail.com', '', '4195788446', '83704520', 'Rua Ana Saliba Nassar', '505', 'CASA 01', 'Fazenda Velha', '13389', 'PR', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '62296876900', '2025-07-02 16:05:17', '2025-07-29 21:38:49', 'Rua Ana Saliba Nassar', 0, 1, 0, 0, 0, '2025-07-29 21:38:49'),
(181, 'cus_000109181881', 'Alessandro Vasconcelos Da Costa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:17', '2025-07-18 17:09:56', NULL, 0, 0, 0, 0, 0, NULL),
(182, 'cus_000109097651', 'Leandro Alves da Silva', 'Leandro', 'silva.engeseg@gmail.com', '', '11974958004', '02083070', 'Rua Tapiraí', '62', NULL, 'Vila Isolina Mazzei', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '29564163838', '2025-07-02 16:05:18', '2025-07-29 10:46:38', 'Rua Tapiraí', 0, 0, 0, 0, 0, NULL),
(183, 'cus_000108457188', 'Adriano Barboza Ramos', 'Adriano', 'adrianobarbozaramos@gmail.com', '', '81997076042', '55190000', 'Rua Safira', '233', NULL, 'Armando Aleixo', '8208', 'PE', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '00883510413', '2025-07-02 16:05:18', '2025-07-29 10:46:39', 'Rua Safira', 0, 0, 0, 0, 0, NULL),
(184, 'cus_000108408941', 'Robson Placco', NULL, 'rplacco@gmail.com', '', '16997446786', '14807040', 'Avenida Doutor Adhemar Pereira de Barros', '159', 'Casa 74', 'Vila Melhado', '12021', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02632282829', '2025-07-02 16:05:18', '2025-07-29 10:46:40', 'Avenida Doutor Adhemar Pereira de Barros', 0, 0, 0, 0, 0, NULL),
(185, 'cus_000108408536', 'José Carlos Machado', NULL, 'joaempreendimentos@hotmail.com', '', '21964526938', '22783550', 'Estrada dos Bandeirantes', '22211', 'BLC 5 CAS 0002', 'Vargem Pequena', '11642', 'RJ', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '58727056000122', '2025-07-02 16:05:19', '2025-07-29 10:46:41', 'Estrada dos Bandeirantes', 0, 0, 0, 0, 0, NULL),
(186, 'cus_000108222939', 'Eloildo Silva', NULL, 'eloildo@yahoo.com.br', '', '11916267914', '07714570', 'Rua Anita Garibaldi', '437', NULL, 'Serpa', '12533', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '33826493818', '2025-07-02 16:05:19', '2025-07-29 10:46:42', 'Rua Anita Garibaldi', 0, 0, 0, 0, 0, NULL),
(187, 'cus_000107749746', 'ALISSON SANTOS BARBOSA | OrtoClean', 'Alisson', 'alissonclinicas@gmail.com', '', '4399141181', '85851020', 'Rua Marechal Floriano Peixoto', '1712', NULL, 'Centro', '13174', 'PR', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '00722937997', '2025-07-02 16:05:19', '2025-07-29 22:37:20', 'Rua Marechal Floriano Peixoto', 0, 1, 0, 0, 0, '2025-07-29 22:37:20'),
(188, 'cus_000107702245', 'Everton Cruz dos Santos | ECS SOLUÇÃO', 'Everton', 'contato@ecssolucoes-sp.com.br', '', '11980441758', '02442090', 'Avenida Coronel Manuel Py', '195', NULL, 'Lauzane Paulista', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '32442749866', '2025-07-02 16:05:20', '2025-07-29 10:46:45', 'Avenida Coronel Manuel Py', 0, 0, 0, 0, 0, NULL),
(189, 'cus_000107334249', 'Marcus Figueira Nogueira Paiva', 'Marcus', 'comercial@magrelaeventos.com.br', '', '11983681136', '04342060', 'Rua Aprígio Rego Lopes', '18', 'contato@', 'Jabaquara', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '05755001820', '2025-07-02 16:05:20', '2025-07-29 10:46:46', 'Rua Aprígio Rego Lopes', 0, 0, 0, 0, 0, NULL),
(190, 'cus_000107304675', 'Jailton Barros Alves', NULL, 'vilamarmateriais@gmail.com', '', '71988348150', '41350290', 'Rua Arthur Gonzales', '13', 'Loteamento Vila Mar', 'Nova Brasília', '9058', 'BA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '47952628534', '2025-07-02 16:05:20', '2025-07-29 10:46:47', 'Rua Arthur Gonzales', 0, 0, 0, 0, 0, NULL),
(191, 'cus_000107283945', 'SOLANGE MARIA LYRA COSTA | EPA! Filmes', 'Solange', 'ASSESSORIACULTURALSL@GMAIL.COM', '', '22981187451', '28860000', 'R ALBERTO VIDAL RAMOS', '156', 'QUADRA 2;LOTE 5', 'SAO SEBASTIAO', '11505', 'RJ', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '51445532000173', '2025-07-02 16:05:21', '2025-07-29 10:46:48', 'R ALBERTO VIDAL RAMOS', 0, 0, 0, 0, 0, NULL),
(192, 'cus_000107071939', 'Makarios Atx Gestao Empresarial E Cultural Ltda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-02 16:05:21', '2025-07-18 17:10:00', NULL, 0, 0, 0, 0, 0, NULL),
(193, 'cus_000104412599', 'Danúbia Mirian De Souza Pereira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '00343304104', '2025-07-02 16:05:21', '2025-07-18 17:10:00', NULL, 0, 0, 0, 0, 0, NULL),
(194, 'cus_000104355749', 'Richard Barbosa Brito', NULL, 'ricbb@hotmail.com', '', '11930865582', '13225130', 'Rua Pará', '318', NULL, 'Vila Popular', '12426', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '12233950658', '2025-07-02 16:05:22', '2025-07-29 10:46:51', 'Rua Pará', 0, 0, 0, 0, 0, NULL),
(195, 'cus_000104347893', 'JR Soluções | João Paulo Ribeiro Castro', 'João Paulo', 'jrsolucoesubatuba@gmail.com', '', '12974019827', '11689358', 'Rua Esporte Clube Goiás', '411', NULL, 'Estufa II', '12492', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '49640422000110', '2025-07-02 16:05:22', '2025-07-29 10:46:52', 'Rua Esporte Clube Goiás', 0, 0, 0, 0, 0, NULL),
(196, 'cus_000104289930', 'M. MONFORTE AGENCIA DE VIAGENS E TURISMO LTDA', 'Vivian', 'monforteturismo@gmail.com', '', '21976209602', '22210010', 'Rua do Russel', '804', 'Andar 3', 'Glória', '11642', 'RJ', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '06180691000139', '2025-07-02 16:05:22', '2025-07-29 10:46:54', 'Rua do Russel', 0, 0, 0, 0, 0, NULL),
(197, 'cus_000104257834', 'Rapport |Gilmar Rafael Ferreira Costa', NULL, 'gilmargcmcampos@gmail.com', '', '12988354983', '11613438', 'Av. Euclides da Cunha', '1006', NULL, 'Canto do Mar', '12489', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '31705657885', '2025-07-02 16:05:23', '2025-07-29 10:46:55', 'Av. Euclides da Cunha', 0, 0, 0, 0, 0, NULL),
(198, 'cus_000104242353', 'Rodrigo Kucarz | Makarios Atx', 'Rodrigo', NULL, '47997009768', '554797009768', '89478000', 'Rua Augusto Kuichler', '1045', NULL, 'Centro', '13616', 'SC', 'Brasil', 0, '{:WORK=>&quot;estrategiasmakarios@gmail.com&quot;}', NULL, NULL, 'Rodrigo Kucarz | Makarios Atx', NULL, '04790984923', '2025-07-02 16:05:23', '2025-07-18 17:10:02', NULL, 0, 0, 0, 0, 0, NULL),
(199, 'cus_000104239207', 'Bellissima Donna | Lorrainy Wainy Moura Santos Souza Moreira', 'Lorrainy', 'lojaindiara76@gmail.com', '', '6496072481', '75955000', 'Rua Américo Ribeiro Guimares', 'QD 42 Lote', NULL, 'Vale do Sol', '15805', 'GO', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02948502101', '2025-07-02 16:05:23', '2025-07-29 21:29:20', 'Rua Américo Ribeiro Guimares', 0, 1, 0, 0, 0, '2025-07-29 21:29:20'),
(200, 'cus_000104188546', 'Maria Lino Sandoval', NULL, 'mlino8760@gmail.com', '', '61999827755', '04143010', 'Rua Itapiru', '281', 'apartamento 94', 'Saúde', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '13202574884', '2025-07-02 16:05:24', '2025-07-29 10:46:58', 'Rua Itapiru', 0, 0, 0, 0, 0, NULL),
(201, 'cus_000104186726', 'Guilherme José de Lira Santos', NULL, 'sguijls@gmail.com', '', '81996469293', '52050038', 'Rua Carneiro Vilela', '1204', NULL, 'Aflitos', '8389', 'PE', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '65262956491', '2025-07-02 16:05:24', '2025-07-29 10:46:59', 'Rua Carneiro Vilela', 0, 0, 0, 0, 0, NULL),
(202, 'cus_000104145527', 'Beleza ZonaSul | Luiz Antônio Estevão de Oliveira', 'Luiz Antônio', 'cufaguarapiranga@outlook.com', '', '11984260548', '04912050', 'Avenida George Anselmi', '254', NULL, 'Guarapiranga', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '25472442842', '2025-07-02 16:05:25', '2025-07-29 10:47:00', 'Avenida George Anselmi', 0, 0, 0, 0, 0, NULL),
(203, 'cus_000103659024', 'Zenaide Bento Correia Dettmer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02283592950', '2025-07-02 16:05:25', '2025-07-18 17:10:04', NULL, 0, 0, 0, 0, 0, NULL),
(204, 'cus_000103224925', 'Facundo Ezequiel Menossi | Sempre Atins Cavalgadas', NULL, 'sempre.atins@gmail.com', '', '98991616153', '65590000', 'Rua da Colônia', '16209', NULL, 'Atins', '6260', 'MA', 'Brasil', 0, NULL, NULL, NULL, 'Facundo Ezequiel Menossi | Sempre Atins Cavalgadas', NULL, '62109289350', '2025-07-02 16:05:25', '2025-07-29 10:47:03', 'Rua da Colônia', 0, 0, 0, 0, 0, NULL),
(205, 'cus_000103195175', 'i9 | Ricardo Matheus', NULL, 'matheusengemat@yahoo.com.br', '', '11941430536', '09540000', 'Rua Engenheiro Rebouças', '893', NULL, 'Cerâmica', '12564', 'SP', 'Brasil', 0, NULL, NULL, NULL, 'i9 | Ricardo Matheus', NULL, '19270851877', '2025-07-02 16:05:26', '2025-07-29 10:47:04', 'Rua Engenheiro Rebouças', 0, 0, 0, 0, 0, NULL),
(206, 'cus_000103190031', 'V. R. DE O. M. POQUIVIQUI LTDA', 'Poquiviqui', 'vaniapoquiviqui@gmail.com', '65981047654', '65981047654', '78360000', 'Rua Severino Euflausino de Lima', '1470', NULL, 'Nossa Senhora Aparecida', '15338', 'MT', 'Brasil', 0, 'vaniapoquiviqui@gmail.com', NULL, NULL, 'V. R. DE O. M. POQUIVIQUI LTDA', NULL, '20070312000189', '2025-07-02 16:05:26', '2025-07-29 10:47:05', 'Rua Severino Euflausino de Lima', 0, 0, 0, 0, 0, NULL),
(207, 'cus_000102925977', 'SGD Studio Automotivo | SAMUEL GUSTAVO DINIZ FERREIRA', NULL, 'sgdstudioautomotivo@gmail.com.br', '', '31997175879', '30662072', 'Rua Trinta e Quatro', '255', NULL, 'Tirol (Barreiro)', '10072', 'MG', 'Brasil', 0, NULL, NULL, NULL, 'SGD Studio Automotivo | SAMUEL GUSTAVO DINIZ FERREIRA', NULL, '30327982000191', '2025-07-02 16:05:27', '2025-07-29 10:47:06', 'Rua Trinta e Quatro', 0, 0, 0, 0, 0, NULL),
(208, 'cus_000102804325', 'Joseli De Oliveira E Souza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '36390101830', '2025-07-02 16:05:27', '2025-07-18 17:10:05', NULL, 0, 0, 0, 0, 0, NULL),
(209, 'cus_000102628090', 'Álvaro Aparecido Marques', 'Álvaro', 'alvaro1913marques@hotmail.com', '', '14997501745', '17524111', 'Rua Albino Ferreira', '124', NULL, 'Jardim Continental', '12262', 'SP', 'Brasil', 0, 'alvaro1913marques@hotmail.com', NULL, NULL, 'Álvaro Aparecido Marques', NULL, '70671273868', '2025-07-02 16:05:27', '2025-07-29 10:47:08', 'Rua Albino Ferreira', 0, 0, 0, 0, 0, NULL),
(210, 'cus_000102396661', 'CITI Markets | JOSE DE PAULA FERRAZ NETO', NULL, 'citimarketsco@gmail.com', '', '19989806060', '13076001', 'Avenida Júlio Prestes', '655', 'Apto 64', 'Taquaral', '12128', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '27880987827', '2025-07-02 16:05:28', '2025-07-29 10:47:09', 'Avenida Júlio Prestes', 0, 0, 0, 0, 0, NULL),
(211, 'cus_000101785232', 'DivDrinks | Sidney Washington Moreira', 'Sidney', 'sidneywashington912@gmail.com', '37998765431', '3798765431', '35500282', 'Rua das Oliveiras, 271', '271', NULL, 'Manoel Valinhas', '10483', 'MG', 'Brasil', 0, 'sidneywashington912@gmail.com', NULL, NULL, 'DivDrinks | Sidney Washington Moreira', NULL, '46382204000135', '2025-07-02 16:05:28', '2025-07-29 21:36:46', 'Rua das Oliveiras, 271', 0, 1, 0, 0, 0, '2025-07-29 21:36:46'),
(212, 'cus_000101394797', 'Leandro R. Vieira | Aline Nutricionista', NULL, 'acessoimob@gmail.com', '', '47996500083', '88210000', 'Av. Senador Atílio Fontana', '2035, apto', NULL, 'Ilhota', '13771', 'SC', 'Brasil', 0, 'acessoimob@gmail.com', NULL, NULL, 'Leandro R. Vieira | Aline Nutricionista', NULL, '03545030903', '2025-07-02 16:05:28', '2025-07-29 10:47:12', 'Av. Senador Atílio Fontana', 0, 0, 0, 0, 0, NULL),
(213, 'cus_000101352647', 'IndBr | Wilson Camilo da Silva', NULL, 'wilsoncamilo13@gmail.com', '11954911863', '5511954911863', '05663040', 'Rua doutor José de Porciuncula', '957', NULL, 'Parque Paulistano', '15873', 'SP', 'Brasil', 0, 'wilsoncamilo13@gmail.com', NULL, NULL, 'IndBr | Wilson Camilo da Silva', NULL, '48408589857', '2025-07-02 16:05:29', '2025-07-29 10:47:13', 'Rua doutor José de Porciuncula', 0, 0, 0, 0, 0, NULL),
(214, 'cus_000101278355', 'Tiago de Oliveira Hönisch | Sun Bank', NULL, 'honisch8@gmail.com', '', '55999197015', '97900000', 'Av. Jacob Reinaldo Haupenthal', '1451', 'SL Térrea', 'Cerro Largo', '14121', 'RS', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02848027002', '2025-07-02 16:05:29', '2025-07-29 10:47:14', 'Av. Jacob Reinaldo Haupenthal', 0, 0, 0, 0, 0, NULL),
(215, 'cus_000100730824', 'Gianna Paola Mantovani | Maria Bonitta', 'Gianna', 'giannapmantovani@hotmail.com', '', '4188290646', '82840510', 'Rua Francisco Zuneda Ferreira da Costa', '187, Casa ', NULL, 'Bairro Alto', '13405', 'PR', 'Brasil', 0, 'giannapmantovani@hotmail.com', NULL, NULL, 'Maria Bonita', NULL, '07801112000190', '2025-07-02 16:05:29', '2025-07-29 22:05:22', 'Rua Francisco Zuneda Ferreira da Costa', 0, 1, 0, 0, 0, '2025-07-29 22:05:22'),
(216, 'cus_000100475026', 'Reinvention', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '49886846000160', '2025-07-02 16:05:30', '2025-07-18 17:10:08', NULL, 0, 0, 0, 0, 0, NULL),
(217, 'cus_000098970140', 'Projeta | Heliton Andre Goulart', 'Neto', 'contato.projeta@hotmail.com', '1691905593', '16991905593', '14340000', 'Rua Vereador José Sabino', '1504', NULL, 'Jardim das Oliveiras', '11861', 'SP', 'Brasil', 0, 'contato.projeta@hotmail.com', NULL, NULL, 'Projeta | Heliton Andre Goulart', NULL, '08168292847', '2025-07-02 16:05:30', '2025-07-29 21:18:21', 'Rua Vereador José Sabino', 1, 1, 0, 0, 0, '2025-07-29 21:18:21'),
(218, 'cus_000098636390', 'USINAC METALURGICA LTDA', NULL, NULL, '48999421401', '48999421401', '88817480', 'Rua André Dário', '40', NULL, 'Vila Macarini', '13866', 'SC', 'Brasil', 1, NULL, NULL, NULL, 'USINAC METALURGICA LTDA', NULL, '14177435000110', '2025-07-02 16:05:30', '2025-07-18 17:10:09', NULL, 0, 0, 0, 0, 0, NULL),
(219, 'cus_000098604944', 'Estética | Nadiele Marques Ventura', 'Nadiele', 'perfumariaventura@gmail.com', '51998679078', '51998679078', '92850000', 'Rua Otto Theodoro Bischoff', '171', NULL, 'Centro', '14876', 'RS', 'Brasil', 0, NULL, NULL, NULL, 'Estética | Nadiele Marques Ventura', NULL, '01600647081', '2025-07-02 16:05:31', '2025-07-29 10:47:19', 'Rua Otto Theodoro Bischoff', 0, 0, 0, 0, 0, NULL),
(220, 'cus_000098604928', 'Maria de Fátima de Sa Sarmento', NULL, 'professorasarmento55@gmail.com', '83999568801', '83999568801', '58070130', 'Rua Jornalista Rafael Mororo', '349', NULL, 'Jaguaribe', '7999', 'PB', 'Brasil', 0, 'professorasarmento55@gmail.com', NULL, NULL, 'Maria de Fátima de Sa Sarmento', NULL, '46748296404', '2025-07-02 16:05:31', '2025-07-29 10:47:20', 'Rua Jornalista Rafael Mororo', 0, 0, 0, 0, 0, NULL),
(221, 'cus_000098598876', 'Antonella Modas | Anderson alves da silva', 'Anderson', 'anderson.pinturas@hotmail.com', '1693099884', '16993099884', '14057340', 'Rua Lourenço chicarolli', '131', NULL, 'Dom Mielle', '11871', 'SP', 'Brasil', 0, 'anderson.pinturas@hotmail.com', NULL, NULL, 'Antonella Modas | Anderson alves da silva', NULL, '32791904875', '2025-07-02 16:05:31', '2025-07-29 21:04:57', 'Rua Lourenço chicarolli', 1, 1, 0, 0, 0, '2025-07-29 21:04:57'),
(222, 'cus_000098586538', 'Roberta Cristina de Sousa', 'Roberta', 'proroberta2@gmail.com', '62985489901', '62985489901', '75262268', 'Rua das varandas, q 5', 'l 14', NULL, 'Vale das Brisas', '15734', 'GO', 'Brasil', 0, 'proroberta2@gmail.com', NULL, NULL, 'Roberta Cristina de Sousa', NULL, '00113309104', '2025-07-02 16:05:32', '2025-07-29 10:47:23', 'Rua das varandas, q 5', 0, 0, 0, 0, 0, NULL),
(223, 'cus_000098546217', 'INFORTECNOLOGIA | SUZANY SILVA DA CRUZ', NULL, 'suzanys@gmail.com', '11932894274', '11932894274', '07032000', 'R CABO JOAO TERUEL FREGONI', '124', NULL, 'PONTE GRANDE', '12539', 'SP', 'Brasil', 1, 'suzanys@gmail.com', NULL, NULL, 'INFORTECNOLOGIA | SUZANY SILVA DA CRUZ', NULL, '25711700820', '2025-07-02 16:05:32', '2025-07-29 10:47:24', 'R CABO JOAO TERUEL FREGONI', 0, 0, 0, 0, 0, NULL),
(224, 'cus_000098539836', 'GREEN GOLD | IVONE DE SOUZA BEZERRA', 'Yves', 'yvescampo54@gmail.com', '92984766400', '92984766400', '69058750', 'RUA VISCONDE DE LAGUNA CONDOMINIO TUPINAMBA DE CARVALHO 3 APARTAMENTO 302', '12', NULL, 'FLORES', '5687', 'AM', 'Brasil', 0, NULL, NULL, NULL, 'GREEN GOLD | IVONE DE SOUZA BEZERRA', NULL, '95286586287', '2025-07-02 16:05:32', '2025-07-29 10:47:25', 'RUA VISCONDE DE LAGUNA CONDOMINIO TUPINAMBA DE CARVALHO 3 APARTAMENTO 302', 0, 0, 0, 0, 0, NULL),
(225, 'cus_000098533441', 'Primovet | Francisco Alves dos Santos', 'Francisco', 'pirajuvet@gmail.com', '1158111000', '11989541000', '05524020', 'Rua Santa Crescência - Bloco 01 AP 34', '314', NULL, 'Vila Sônia', '15873', 'SP', 'Brasil', 0, 'pirajuvet@gmail.com', NULL, 'Domínio: cvei24h.com.br', 'Primovet | Francisco Alves dos Santos', NULL, '21913533808', '2025-07-02 16:05:33', '2025-07-29 10:47:26', 'Rua Santa Crescência - Bloco 01 AP 34', 0, 0, 0, 0, 0, NULL),
(226, 'cus_000098533419', 'Voomed | Mauro Bacan Junior', NULL, 'mauro2bcc@voomed.com.br', '11981030181', '11981030181', '04602909', 'Rua Barão do Triunfo', '550', NULL, 'Brooklin Paulista', '15873', 'SP', 'Brasil', 1, 'mauro2bcc@voomed.com.br', NULL, NULL, 'Voomed | Mauro Bacan Junior', NULL, '03443111807', '2025-07-02 16:05:33', '2025-07-29 10:47:27', 'Rua Barão do Triunfo', 0, 0, 0, 0, 0, NULL),
(227, 'cus_000098517703', 'Questy | Belarmino Freitas Alves', 'Belarmino', 'belarmino.ocupacional@gmail.com', '11934707141', '11934707141', '06362195', 'Rua Serra Azul', '70', NULL, 'Jardim Planalto', '12525', 'SP', 'Brasil', 0, 'belarmino.ocupacional@gmail.com', NULL, NULL, 'Questy | Belarmino Freitas Alves', NULL, '38045748826', '2025-07-02 16:05:33', '2025-07-29 10:47:28', 'Rua Serra Azul', 0, 0, 0, 0, 0, NULL),
(228, 'cus_000098492567', 'Bela Ateliê | Angelo Donizeti Motta', 'Ângelo', 'angelomottadf@gmail.com', '61982428290', '61982428290', '72915111', 'Av. JK quadra - lote 26', '30', NULL, 'Jardim Brasília', '15752', 'GO', 'Brasil', 0, NULL, NULL, NULL, 'Bela Ateliê | Angelo Donizeti Motta', NULL, '01689639814', '2025-07-02 16:05:34', '2025-07-29 10:47:29', 'Av. JK quadra - lote 26', 0, 0, 0, 0, 0, NULL),
(229, 'cus_000098484937', 'SYSTEMPET | JACQUELINE MADALENA SOUZA DE AMORIM', NULL, 'jms.amor@gmail.com', '47991909594', '47991909594', '88350270', 'Rua João Luiz Gonzaga', '157', NULL, 'Centro', '13749', 'SC', 'Brasil', 0, 'jms.amor@gmail.com', NULL, NULL, 'SYSTEMPET | JACQUELINE MADALENA SOUZA DE AMORIM', NULL, '15332188000142', '2025-07-02 16:05:34', '2025-07-29 10:47:30', 'Rua João Luiz Gonzaga', 0, 0, 0, 0, 0, NULL),
(230, 'cus_000098477960', 'Pedro Regis Dias Aguirre', NULL, NULL, '5548991732208', '48991732208', '88102470', 'Rua Joao Correia Sobrinho -  Ap. 1003', '171', NULL, 'Kobrasol', '13817', 'SC', 'Brasil', 1, NULL, NULL, NULL, 'Pedro Regis Dias Aguirre', NULL, '61370177020', '2025-07-02 16:05:34', '2025-07-18 17:10:13', NULL, 0, 0, 0, 0, 0, NULL),
(231, 'cus_000098471285', 'HRF ENGENHARIA LTDA', NULL, 'financeiro@hrfengenharia.com.br', '11962475747', '11996247574', '05886120', 'Rua Luis de oliveira', '550', NULL, 'Jardim Dom José', '15873', 'SP', 'Brasil', 0, 'fernandamartim@hotmail.com', NULL, NULL, 'HRF ENGENHARIA LTDA', NULL, '29221410000144', '2025-07-02 16:05:35', '2025-07-29 10:47:33', 'Rua Luis de oliveira', 0, 0, 0, 0, 0, NULL),
(232, 'cus_000098414030', 'Abrasiva Industria', 'Alexandre', 'contato@abrasiva.com.br', '', '62985793436', '75195000', 'Rodovia GO, Qd 05, Lt. 2A', '010', NULL, 'Alto da Boa Vista', '15723', 'GO', 'Brasil', 0, 'financeiro@abrasiva.com.br', NULL, NULL, 'Abrasiva Industria', NULL, '30672815000188', '2025-07-02 16:05:35', '2025-07-29 10:47:34', 'Rodovia GO, Qd 05, Lt. 2A', 0, 0, 0, 0, 0, NULL),
(233, 'cus_000098351864', 'AMAZON ENGENHARIA', NULL, 'nanai@amazonenge.com.br', '92992967334', '92981913363', '69075775', 'AV COSME FERREIRA - QD-05 LT-17 BRISAS', '11000', NULL, 'MAUAZINHO', '5687', 'AM', 'Brasil', 0, 'nanai@amazonenge.com.br', NULL, NULL, 'AMAZON ENGENHARIA', NULL, '34314073000170', '2025-07-02 16:05:35', '2025-07-29 10:47:35', 'AV COSME FERREIRA - QD-05 LT-17 BRISAS', 0, 0, 0, 0, 0, NULL),
(234, 'cus_000098337527', 'JackTour | Jackson Alencar da Silva', 'Jackson', 'jackson.tur.am@hotmail.com', '', '92991953335', '69049580', 'Rua Galanoupolis', '454', NULL, 'Redenção', '5687', 'AM', 'Brasil', 0, 'jackson.tur.am@hotmail.com', NULL, NULL, 'JackTour | Jackson Alencar da Silva', NULL, '63736144253', '2025-07-02 16:05:36', '2025-07-29 10:47:36', 'Rua Galanoupolis', 0, 0, 0, 0, 0, NULL),
(235, 'cus_000098194125', 'FUGA DA CIDADE | WIRLER ALMEIDA SANTOS', NULL, 'wirley-santos@hotmail.com', '96988019244', '96988019244', '68948000', 'RUA - E', '368', NULL, 'Colônia de Água Branca', '6056', 'AP', 'Brasil', 0, 'wirley-santos@hotmail.com', NULL, NULL, 'Fuga da Cidade | Wirler Almeida Santo', NULL, '40226719000160', '2025-07-02 16:05:36', '2025-07-29 10:47:37', 'RUA - E', 0, 0, 0, 0, 0, NULL),
(236, 'cus_000098167431', 'Detetive Aguiar | Mendes de Souza Aguiar Barros', 'Detetive Aguiar', 'detetive.aguiarbarros@gmail.com', '69993245042', '6993245042', '76907175', 'Rua Frei Henrique de Coimbra', '162', NULL, 'Park Amazonas', '5821', 'RO', 'Brasil', 0, 'detetive.aguiarbarros@gmail.com', NULL, NULL, 'Detetive Aguiar | Mendes de Souza Aguiar Barros', NULL, '02139682238', '2025-07-02 16:05:36', '2025-07-29 22:19:18', 'Rua Frei Henrique de Coimbra', 0, 1, 0, 0, 0, '2025-07-29 22:19:18'),
(237, 'cus_000096455012', 'Gráfica Veronez', NULL, 'everonez474@gmail.com', '556199713985', '61999713985', '72210230', 'QNM 22 Conjunto J', 'Lote 44', NULL, 'Ceilândia Norte', '15872', 'DF', 'Brasil', 0, 'everonez474@gmail.com', NULL, NULL, 'Gráfica Veronez', NULL, '43185174000189', '2025-07-02 16:05:37', '2025-07-29 10:47:39', 'QNM 22 Conjunto J', 0, 0, 0, 0, 0, NULL),
(238, 'cus_000096399442', 'Tânia Márcia das Neves Rocha', NULL, 'tania.m.farma@gmail.com', '', '31984287908', '27935080', 'Rua Salvador', '166', NULL, 'Novo Horizonte', '11440', 'RJ', 'Brasil', 0, 'tania.m.farma@gmail.com', NULL, NULL, 'Tânia Márcia das Neves Rocha', NULL, '02694365701', '2025-07-02 16:05:37', '2025-07-29 10:47:41', 'Rua Salvador', 0, 0, 0, 0, 0, NULL),
(239, 'cus_000096311723', 'Lav Up | Daniel Fugazza Molinari', NULL, 'danielmolinaripart@hotmail.com', '', '44997078534', '85900030', 'Rua Sarandi', '745', NULL, 'Centro', '13129', 'PR', 'Brasil', 0, 'danielmolinaripart@hotmail.com', NULL, NULL, 'Lav Up | Daniel Fugazza Molinari', NULL, '09968114944', '2025-07-02 16:05:37', '2025-07-29 10:47:42', 'Rua Sarandi', 0, 0, 0, 0, 0, NULL),
(240, 'cus_000096050697', 'JUREMA COMUNICACAO E EVENTOS LTDA | Luciana', 'Luciana', 'la323352@gmail.com', '81998790053', '81998790053', '55642020', 'Rua Santo Antônio', '75', NULL, 'Prado', '8174', 'PE', 'Brasil', 0, 'la323352@gmail.com', NULL, NULL, 'JUREMA COMUNICACAO E EVENTOS LTDA', NULL, '13044894000163', '2025-07-02 16:05:38', '2025-07-29 10:47:43', 'Rua Santo Antônio', 0, 0, 0, 0, 0, NULL),
(241, 'cus_000095907784', 'Ibivagas Saee', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '10721158000104', '2025-07-02 16:05:38', '2025-07-18 17:10:17', NULL, 0, 0, 0, 0, 0, NULL),
(242, 'cus_000095361024', 'Universo Relíquias | Marcelo Pereira Bueno', NULL, 'mpereirabueno@gmail.com', '48984222210', '48984222210', '88136224', 'Rua Maranhão', '30', NULL, 'São Sebastião', '13813', 'SC', 'Brasil', 1, 'mpereirabueno@gmail.com', NULL, NULL, 'Universo Relíquias | Marcelo Pereira Bueno', NULL, '06087598804', '2025-07-02 16:05:39', '2025-07-29 10:47:45', 'Rua Maranhão', 0, 0, 0, 0, 0, NULL),
(243, 'cus_000095353105', 'PAULA FRAGA MACHADO', NULL, 'eternize.pingentes@gmail.com', '', '51984428748', '91780585', 'Rua Luan Jacoby', '193', 'Casa', 'Ponta Grossa', '14871', 'RS', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '68629427000', '2025-07-02 16:05:39', '2025-07-29 10:47:46', 'Rua Luan Jacoby', 0, 0, 0, 0, 0, NULL),
(244, 'cus_000094438083', 'Azuleja | Isabela Carvalho Capecchi', NULL, 'azulejaeleva@gmail.com', '', '21982103467', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '54356421000189', '2025-07-02 16:05:39', '2025-07-29 10:47:48', '', 0, 0, 0, 0, 0, NULL),
(245, 'cus_000094424452', 'Débora Hermana de Santana | MSP Obras', NULL, 'dhermana955@gmail.com', '', '11910176733', '02327020', 'Rua Flor do Lírio', '81', NULL, 'Jardim Portal I e II', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, 'MSP OBRAS', NULL, '44311765860', '2025-07-02 16:05:40', '2025-07-29 10:47:49', 'Rua Flor do Lírio', 0, 0, 0, 0, 0, NULL),
(246, 'cus_000094286665', 'Antonierta Dietrich De Andrade', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '38380072987', '2025-07-02 16:05:40', '2025-07-18 17:10:19', NULL, 0, 0, 0, 0, 0, NULL),
(247, 'cus_000093723452', 'Vinicius Santos de Lima Peixoto', NULL, 'viniciuspeixoto.psi@gmail.com', '', '66992555474', '78600000', 'Rua Trinta e Três', 'Qd 04 101', 'Residencial Vale da Serra', NULL, '15426', 'MT', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '50415284000155', '2025-07-02 16:05:41', '2025-07-29 10:47:51', 'Rua Trinta e Três', 0, 0, 0, 0, 0, NULL),
(248, 'cus_000093366683', 'Alan Júnior da Silva', NULL, 'alanjrsilva@outlook.com', '', '51997553880', '95630000', 'Rua Gerônimo Coelho', '549', NULL, 'Centro', '14868', 'RS', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '81471890015', '2025-07-02 16:05:41', '2025-07-29 10:47:52', 'Rua Gerônimo Coelho', 0, 0, 0, 0, 0, NULL),
(249, 'cus_000092261799', 'Zelinda Pedroso Da Silva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '64266044968', '2025-07-02 16:05:41', '2025-07-18 17:10:20', NULL, 0, 0, 0, 0, 0, NULL),
(250, 'cus_000092205818', 'Edilane Moreira Coelho Mariano', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '09814547719', '2025-07-02 16:05:42', '2025-07-18 17:10:20', NULL, 0, 0, 0, 0, 0, NULL),
(251, 'cus_000091635904', 'OkGostei | Vicente Correia de Moraes Junior', 'Vicente', 'm3graficas.reunidas@gmail.com', '11948434895', '11948434895', '03153001', 'Avenida Francisco Mesquita', '1213', NULL, 'Vila Prudente', '15873', 'SP', 'Brasil', 0, 'm3graficas.reunidas@gmail.com', NULL, NULL, 'OkGostei | Vicente Correia de Moraes Junior', NULL, '19930001867', '2025-07-02 16:05:42', '2025-07-29 10:47:56', 'Avenida Francisco Mesquita', 0, 0, 0, 0, 0, NULL),
(252, 'cus_000091521313', 'Alex Sandro de Sousa Gomes | Decor Ambientes', 'Alex Sandro', 'decoreambientesplanejados1@outlook.com', '', '9481334956', '68515000', 'Rua A 13 Q 25', 'Lote 32', NULL, 'Amazona', '6025', 'PA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '04142034618', '2025-07-02 16:05:42', '2025-07-29 22:32:51', 'Rua A 13 Q 25', 0, 1, 0, 0, 0, '2025-07-29 22:32:51'),
(253, 'cus_000091405502', 'ZFITNESS SHOP', NULL, 'zenaidebcdettmer@gmail.com', '47992102724', '47992102724', '89068230', 'CARL DETTMER', '917', NULL, 'ITOUPAVA CENTRAL', '13745', 'SC', 'Brasil', 0, 'zenaidebcdettmer@gmail.com', NULL, NULL, 'ZFITNESS SHOP', NULL, '46079246000100', '2025-07-02 16:05:43', '2025-07-29 10:47:58', 'CARL DETTMER', 0, 0, 0, 0, 0, NULL),
(254, 'cus_000091107568', 'SO OBRAS EPC DISTRIBUICAO E INSTALACOES LTDA', NULL, 'financeiro@soobrasepc.com.br', '', '11940863773', '0123903', NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '55309799000194', '2025-07-02 16:05:43', '2025-07-29 10:47:59', '', 0, 0, 0, 0, 0, NULL),
(255, 'cus_000091048028', 'Gideon Santos Mirabile', NULL, 'contato@tattooworldshop.com.br', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '18753108272', '2025-07-02 16:05:43', '2025-07-29 10:48:00', '', 0, 0, 0, 0, 0, NULL),
(256, 'cus_000090939041', 'Ana Paula Souza dos Santos | Giganet Fibra', 'Ana Paula', 'giganetwinfi@gmail.com', '', '27999126134', '29255000', 'Marechal Floriano', '08', NULL, 'Zona Rural', '11218', 'ES', 'Brasil', 0, NULL, NULL, 'Alessandro Vasconcelos da Costa\r\n10081676735', NULL, NULL, '10734735707', '2025-07-02 16:05:44', '2025-07-29 10:48:01', 'Marechal Floriano', 0, 0, 0, 0, 0, NULL),
(257, 'cus_000090722360', 'GUTEMBERG STOLZE PEREIRA LTDA', 'Gutemberg', 'imprensananet.com@gmail.com', '', '7381552730', '45810000', 'Av. 22 de Abril', NULL, NULL, 'Centro', '9431', 'BA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '21491302000180', '2025-07-02 16:05:44', '2025-07-29 21:30:18', 'Av. 22 de Abril', 0, 1, 0, 0, 0, '2025-07-29 21:30:18'),
(258, 'cus_000090721600', 'SEC NETWORK LTDA', 'Marcos', 'financeiro@secnetwork.com.br', '', '11981155533', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '64049679000196', '2025-07-02 16:05:44', '2025-07-29 10:48:03', '', 0, 0, 0, 0, 0, NULL),
(259, 'cus_000090720276', 'José Confessor Filho', NULL, 'confessorjose186@gmail.com', '', '11987733648', '06700075', 'Rua Senador Feijó', '288', 'SALA 08', 'Centro', '12542', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '06261294437', '2025-07-02 16:05:45', '2025-07-29 10:48:04', 'Rua Senador Feijó', 0, 0, 0, 0, 0, NULL),
(260, 'cus_000090697666', 'Claudinei da Silva', 'Claudinei', 'contato@rota67car.com.br', '', '67996347599', '7924000', 'Primeiro de Maio', '1166', NULL, 'Centro', '15239', 'MS', 'Brasil', 0, NULL, NULL, NULL, 'ROTA67CAR', NULL, '80226400182', '2025-07-02 16:05:45', '2025-07-29 10:48:06', 'Primeiro de Maio', 0, 0, 0, 0, 0, NULL),
(261, 'cus_000090483141', 'Savio Eduardo Soares de Pontes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '08831397400', '2025-07-02 16:05:45', '2025-07-18 17:10:24', NULL, 0, 0, 0, 0, 0, NULL),
(262, 'cus_000090395332', 'Alex Rodrigues de Pontes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '74784200444', '2025-07-02 16:05:46', '2025-07-18 17:10:24', NULL, 0, 0, 0, 0, 0, NULL),
(263, 'cus_000090391511', 'Robson Wagner Alves Vieira | CFC Bom Conselho', 'Robson', 'contato@cfcbomconselho.com.br', '', '87999884234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '00746782489', '2025-07-02 16:05:46', '2025-07-29 10:48:09', '', 0, 0, 0, 0, 0, NULL),
(264, 'cus_000090384339', 'KLYSMAN LOPES FERNANDES | Renascer Higienizações', NULL, 'robertodiogo0411@gmail.com', '', '553484041589', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '55641620000100', '2025-07-02 16:05:46', '2025-07-29 17:51:21', '', 0, 1, 1, 1, 0, '2025-07-29 17:51:21'),
(265, 'cus_000090344644', 'Mais Fácil Consig | Richard Henrique Pedroso da Silva', 'Zelinda', 'maisfacilconsig@gmail.com', '', '49991187494', '89804180', 'Rua Caramuru', '582', NULL, 'Bela Vista', '13482', 'SC', 'Brasil', 0, 'maisfacilconsig@gmail.com', NULL, NULL, 'Mais Fácil Consig | Richard Henrique Pedroso da Silva', NULL, '51477027000100', '2025-07-02 16:05:47', '2025-07-29 10:48:11', 'Rua Caramuru', 0, 0, 0, 0, 0, NULL),
(266, 'cus_000089810895', 'DSL Serviços Ltda - Luis evangelista de Moura Pacheco', 'Luis', 'evangelista.pacheco@gmail.com', '92981543898', '92981543898', '43850000', 'Rua Zélia Maria da Paixão', 'SN', NULL, 'Humildes', '8988', 'BA', 'Brasil', 0, 'evangelista.pacheco@gmail.com', NULL, NULL, 'DSL Serviços Ltda - Luis evangelista de Moura Pacheco', NULL, '36635065504', '2025-07-02 16:05:47', '2025-07-29 10:48:12', 'Rua Zélia Maria da Paixão', 0, 0, 0, 0, 0, NULL),
(267, 'cus_000089719948', 'Travel Ton Nordeste', NULL, 'oliveirasueliton81@gmail.com', '', '83998623732', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '19663140000178', '2025-07-02 16:05:48', '2025-07-29 10:48:13', '', 0, 0, 0, 0, 0, NULL),
(268, 'cus_000089443806', 'MAURO | ACTTUS GESTAO COLABORATIVA LTDA', 'Mauro', 'contato@agenciamil.com.br', '', '11981030181', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '39941215000142', '2025-07-02 16:05:48', '2025-07-29 10:48:15', '', 0, 0, 0, 0, 0, NULL),
(269, 'cus_000089377247', 'ACCESS INFORMATICA LTDA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '00607932000105', '2025-07-02 16:05:48', '2025-07-15 11:45:25', NULL, 0, 0, 0, 0, 0, NULL),
(270, 'cus_000089367722', 'Romerito Silva dos Santos', NULL, 'romeritosantos56@gmail.com', '', '24992930292', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '34822619893', '2025-07-02 16:05:49', '2025-07-29 10:48:17', '', 0, 0, 0, 0, 0, NULL),
(271, 'cus_000089266672', 'AFONSO NETO ENGENHARIA E ARQUITETURA RONALDO AFONSO FILHO', 'Afonso', 'ronaldoafonsofilho68@gmail.com', '', '11985695188', '03067010', 'Rua Coronel Carlos Oliva', '357', NULL, 'Tatuapé', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '13524021000158', '2025-07-02 16:05:49', '2025-07-29 10:48:18', 'Rua Coronel Carlos Oliva', 0, 0, 0, 0, 0, NULL),
(272, 'cus_000088938295', 'MÁRCIO GARBIN BACCAN', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '33656577897', '2025-07-02 16:05:49', '2025-07-15 11:45:26', NULL, 0, 0, 0, 0, 0, NULL),
(273, 'cus_000088931579', 'Welton Pereira Santos', 'Welton', 'weltonpereira0182@gmail.com', '', '7398661629', '45818000', 'Trancoso (Porto Seguro)', '147', NULL, 'Transoco', '9431', 'BA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '87004379566', '2025-07-02 16:05:50', '2025-07-29 19:37:23', 'Trancoso (Porto Seguro)', 0, 1, 0, 0, 0, '2025-07-29 19:37:23'),
(274, 'cus_000088238276', 'Eduardo da Silva Brito', 'Eduardo', 'eduardobrito82@icloud.com', '', '11964583101', '07092020', 'Rua Heloísa', '308', NULL, 'Gopoúva', '12539', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '31218589809', '2025-07-02 16:05:50', '2025-07-29 10:48:21', 'Rua Heloísa', 0, 0, 0, 0, 0, NULL),
(275, 'cus_000088117312', 'Escolinha Palmeirinha | Tiago Henrique Nobile da Silva', 'Tiago', 'tiagonobile1000@gmail.com', '98981006102', '98987182714', '65046651', 'Rua Augusto Severo', '5', 'quadra H Conjunto Santos Dumont Anil', 'Anil', '6250', 'MA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '96138378334', '2025-07-02 16:05:50', '2025-07-29 10:48:22', 'Rua Augusto Severo', 0, 0, 0, 0, 0, NULL),
(276, 'cus_000088109585', 'LAC LORO COMERCIO E REPRESENTACOES LTDA', NULL, 'LOURIVALDOMALMEIDA@HOTMAIL.COM', '', '66992834490', '78528000', 'R SAO PAULO', 'SN', 'CXPST 60', 'SETOR II', '15331', 'MT', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '15789245000117', '2025-07-02 16:05:51', '2025-07-29 10:48:24', 'R SAO PAULO', 0, 0, 0, 0, 0, NULL),
(277, 'cus_000088108044', 'VALDIRENE REGINA MENDES REFUNDINI', NULL, 'valdirenerefundini@gmail.com', '', '19997762255', '13835000', 'R MOGI MIRIM', '416', NULL, NULL, '12064', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02975051000164', '2025-07-02 16:05:51', '2025-07-29 10:48:25', 'R MOGI MIRIM', 0, 0, 0, 0, 0, NULL),
(278, 'cus_000088104761', 'NELSON LUIZ DIAS JUNIOR', NULL, 'nelsonjr.dias@gmail.com', '', '11993015696', '18150000', 'Rua Quinze de Novembro', '238', 'Sala 9', NULL, '12391', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '43856732000190', '2025-07-02 16:05:51', '2025-07-29 10:48:26', 'Rua Quinze de Novembro', 0, 0, 0, 0, 0, NULL),
(279, 'cus_000087736349', 'A POUSADA DA PRAIA', NULL, 'apousadadapraia@gmail.com', '', '98991144007', '6506524', 'R DOS MAGISTRADOS', '10A', NULL, NULL, '6250', 'MA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '02060973000141', '2025-07-02 16:05:52', '2025-07-29 10:48:27', 'R DOS MAGISTRADOS', 0, 0, 0, 0, 0, NULL),
(280, 'cus_000087287618', 'IGUACI MARIO CARDOSO', 'Mario', 'imconstru@gmail.com', '', '5183126115', '94832080', 'Rua Vinte e Sete', '258', NULL, 'Umbu', '14843', 'RS', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '36595438020', '2025-07-02 16:05:52', '2025-07-29 20:57:06', 'Rua Vinte e Sete', 0, 1, 0, 0, 0, '2025-07-29 20:57:06'),
(281, 'cus_000087284509', 'Michael Antonio dos Santos', 'Michael', 'michaelantoniodossantos2@gmail.com', '', '11947632289', '02878080', 'Rua Ibiraiaras', '278', NULL, 'Jardim Vista Alegre', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '39269521800', '2025-07-02 16:05:52', '2025-07-29 10:48:30', 'Rua Ibiraiaras', 0, 0, 0, 0, 0, NULL),
(282, 'cus_000087142281', 'Maurilio josé do nascimento', 'Maurilio', 'mrv.maurilio@outlook.com', '', '31991222681', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '91911281615', '2025-07-02 16:05:53', '2025-07-29 10:48:31', '', 0, 0, 0, 0, 0, NULL),
(283, 'cus_000087140755', 'SG JURIDICO SERVICOS EDUCACIONAIS LTDA | Leilaine', 'Leilaine', 'santograaljuridico@gmail.com', '', '61920007184', '30160040', 'Rua Rio de Janeiro', '243', NULL, 'Centro', '10072', 'MG', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '53770198000159', '2025-07-02 16:05:53', '2025-07-29 10:48:32', 'Rua Rio de Janeiro', 0, 0, 0, 0, 0, NULL),
(284, 'cus_000087138899', 'SIC Comércio de Produtos Alimentícios | Clube da Diária - Emerson', NULL, 'xipsdapraia23@gmail.com', '', '71992102042', '41610540', 'Rua Guararapes', '55 F', NULL, 'Itapuã', '9058', 'BA', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '54795377000103', '2025-07-02 16:05:53', '2025-07-29 10:48:33', 'Rua Guararapes', 0, 0, 0, 0, 0, NULL),
(285, 'cus_000086393080', 'ALESSANDRA KLANN DOS SANTOS', 'Alessandra', 'thiagosantosjc@hotmail.com', '', '47997471723', '88357219', 'Rua Joaquim Amancio Correa Júnior', '145', 'CASA 02', 'Águas Claras', '13749', 'SC', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '52900855000172', '2025-07-02 16:05:54', '2025-07-29 10:48:34', 'Rua Joaquim Amancio Correa Júnior', 0, 0, 0, 0, 0, NULL),
(286, 'cus_000086392908', 'WILMAR AUGUSTO IBERS', 'Wilmar', 'wilmar15000@gmail.com', '', '48998581874', '88150000', 'ROD BR 282, KM 32', '0', NULL, 'ÁGUAS MORNAS', '13821', 'SC', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '51789440000100', '2025-07-02 16:05:54', '2025-07-29 10:48:35', 'ROD BR 282, KM 32', 0, 0, 0, 0, 0, NULL),
(4293, 'cus_000093582276', 'Valdirene Regina Mendes Refundini', NULL, NULL, NULL, '19997762255', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '12354337817', '2025-07-15 11:52:14', '2025-07-15 11:52:14', NULL, 0, 0, 0, 0, 0, NULL),
(4294, 'cus_000090501235', 'Jessica Tatiane Mendes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '08215060994', '2025-07-15 11:54:00', '2025-07-15 11:54:00', NULL, 0, 0, 0, 0, 0, NULL);
INSERT INTO `clientes` (`id`, `asaas_id`, `nome`, `contact_name`, `email`, `telefone`, `celular`, `cep`, `rua`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `pais`, `notificacao_desativada`, `emails_adicionais`, `referencia_externa`, `observacoes`, `razao_social`, `criado_em_asaas`, `cpf_cnpj`, `data_criacao`, `data_atualizacao`, `endereco`, `telefone_editado_manual`, `celular_editado_manual`, `email_editado_manual`, `nome_editado_manual`, `endereco_editado_manual`, `data_ultima_edicao_manual`) VALUES
(4296, 'cus_000092153806', '29.714.777 Charles Dietrich Wutzke', 'Charles', NULL, '4796164699', '554796164699', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '29714777000108', '2025-07-15 11:55:35', '2025-07-18 16:55:51', NULL, 0, 0, 0, 0, 0, NULL),
(4297, 'cus_000092850261', 'Jose Roberto Candido', 'José Roberto', NULL, NULL, '11992147689', '05715040', 'Rua Itatupa', '279', 'apt 121', 'Vila Andrade', '15873', 'SP', 'Brasil', 0, NULL, NULL, NULL, NULL, NULL, '18130548860', '2025-07-15 11:56:10', '2025-07-15 11:56:10', NULL, 0, 0, 0, 0, 0, NULL),
(11397, 'cus_test_corrigido', 'Cliente Teste Corrigido', NULL, 'teste@corrigido.com', '(11) 99999-9999', NULL, NULL, 'Rua Teste', '123', 'Apto 1', 'Centro', 'São Paulo', 'SP', 'Brasil', 0, 'teste2@email.com', 'REF001', 'Cliente de teste', 'Empresa Teste LTDA', '2025-07-28 11:00:00', '123.456.789-00', '2025-07-28 11:26:31', '2025-07-28 11:26:31', NULL, 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `clientes_monitoramento`
--

CREATE TABLE `clientes_monitoramento` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `monitorado` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `clientes_monitoramento`
--

INSERT INTO `clientes_monitoramento` (`id`, `cliente_id`, `monitorado`, `data_criacao`, `data_atualizacao`) VALUES
(1, 286, 1, '2025-07-19 01:03:14', '2025-07-30 21:14:21'),
(2, 4296, 0, '2025-07-19 01:03:46', '2025-07-22 23:13:15'),
(3, 274, 1, '2025-07-25 15:15:43', '2025-07-25 15:15:45'),
(4, 273, 1, '2025-07-29 20:15:45', '2025-07-29 20:15:45'),
(6, 147, 0, '2025-07-29 20:19:14', '2025-07-29 20:19:14'),
(7, 145, 1, '2025-07-29 20:28:38', '2025-07-29 20:28:38'),
(8, 280, 1, '2025-07-29 20:57:43', '2025-07-29 20:57:43'),
(9, 4297, 1, '2025-07-29 21:00:01', '2025-07-29 21:00:01'),
(11, 221, 1, '2025-07-29 21:05:44', '2025-07-29 21:05:44'),
(13, 268, 1, '2025-07-29 21:07:39', '2025-07-29 21:07:39'),
(15, 195, 1, '2025-07-29 21:09:02', '2025-07-29 21:09:02'),
(17, 281, 1, '2025-07-29 21:14:55', '2025-07-29 21:14:55'),
(18, 217, 1, '2025-07-29 21:19:08', '2025-07-29 21:19:08'),
(20, 209, 1, '2025-07-29 21:20:10', '2025-07-29 21:20:10'),
(22, 271, 1, '2025-07-29 21:21:03', '2025-07-29 21:21:03'),
(24, 199, 1, '2025-07-29 21:23:17', '2025-07-29 21:23:18'),
(25, 257, 1, '2025-07-29 21:30:28', '2025-07-29 21:30:28'),
(26, 211, 1, '2025-07-29 21:37:05', '2025-07-29 21:37:05'),
(28, 180, 1, '2025-07-29 21:39:03', '2025-07-29 21:39:03'),
(29, 169, 1, '2025-07-29 22:01:27', '2025-07-29 22:01:27'),
(30, 189, 1, '2025-07-29 22:03:01', '2025-07-29 22:03:01'),
(32, 215, 1, '2025-07-29 22:05:42', '2025-07-29 22:05:42'),
(33, 258, 1, '2025-07-29 22:10:35', '2025-07-29 22:10:35'),
(35, 236, 1, '2025-07-29 22:17:32', '2025-07-29 22:21:29'),
(37, 256, 1, '2025-07-29 22:28:00', '2025-07-29 22:28:00'),
(38, 191, 1, '2025-07-29 22:31:05', '2025-07-29 22:31:05'),
(39, 252, 1, '2025-07-29 22:31:32', '2025-07-29 22:31:33'),
(40, 167, 1, '2025-07-29 22:33:27', '2025-07-29 22:33:27'),
(41, 251, 1, '2025-07-29 22:34:35', '2025-07-29 22:34:35'),
(43, 187, 1, '2025-07-29 22:37:40', '2025-07-29 22:37:40'),
(44, 202, 1, '2025-07-29 22:38:20', '2025-07-29 22:38:20'),
(45, 174, 1, '2025-07-29 22:39:46', '2025-07-29 22:39:46'),
(47, 175, 1, '2025-07-29 22:40:59', '2025-07-29 22:40:59');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `clients`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `clients` (
`id` int(11)
,`asaas_id` varchar(64)
,`name` varchar(255)
,`email` varchar(255)
,`phone` varchar(50)
,`cpf_cnpj` varchar(32)
,`created_at` datetime
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `cobrancas`
--

CREATE TABLE `cobrancas` (
  `id` int(11) NOT NULL,
  `asaas_payment_id` varchar(64) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `vencimento` date DEFAULT NULL,
  `data_pagamento` date DEFAULT NULL,
  `data_criacao` datetime DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `tipo_pagamento` varchar(20) DEFAULT NULL,
  `url_fatura` varchar(255) DEFAULT NULL,
  `parcela` varchar(32) DEFAULT NULL,
  `assinatura_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `cobrancas`
--

INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(59598, 'pay_bnca3xkgmfss5fnl', 279, 49.90, 'PENDING', '2025-09-05', NULL, '2025-07-28 00:00:00', '2025-07-29 10:49:11', 'Plano Mensal Manutenção e Hospedagem', 'BOLETO', NULL, 'https://www.asaas.com/i/bnca3xkgmfss5fnl', NULL, 'sub_htfllf6l0cu6s4qk'),
(59600, 'pay_s6pqs3wmjh4mhbb7', 152, 800.00, 'RECEIVED', '2025-07-24', '2025-07-24', '2025-07-24 00:00:00', '2025-07-29 10:49:13', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/s6pqs3wmjh4mhbb7', NULL, NULL),
(59602, 'pay_a6i9g6w7y403mxun', 151, 539.00, 'RECEIVED', '2025-07-23', '2025-07-23', '2025-07-23 00:00:00', '2025-07-29 10:49:15', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/a6i9g6w7y403mxun', NULL, NULL),
(59604, 'pay_w84bzbupat4295zg', 151, 1925.00, 'RECEIVED', '2025-07-22', '2025-07-22', '2025-07-22 00:00:00', '2025-07-29 10:49:16', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/w84bzbupat4295zg', NULL, NULL),
(59606, 'pay_w7hsz7y6wwjw1xmb', 169, 29.90, 'PENDING', '2025-08-30', NULL, '2025-07-22 00:00:00', '2025-07-29 10:49:18', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/w7hsz7y6wwjw1xmb', NULL, 'sub_bebghk37yr7kaykc'),
(59608, 'pay_4ua7d70wfourmnsy', 185, 49.90, 'PENDING', '2025-08-29', NULL, '2025-07-21 00:00:00', '2025-07-29 10:49:19', 'Plano ImobSites ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4ua7d70wfourmnsy', NULL, 'sub_0u0hiabbslpiov8l'),
(59610, 'pay_getx385xwrneyxiq', 220, 29.90, 'PENDING', '2025-08-29', NULL, '2025-07-21 00:00:00', '2025-07-29 10:49:21', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/getx385xwrneyxiq', NULL, 'sub_0ob5gqdk6bgm9evj'),
(59612, 'pay_arz2pek5fiyz3hs3', 267, 29.00, 'PENDING', '2025-08-29', NULL, '2025-07-21 00:00:00', '2025-07-29 10:49:22', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/arz2pek5fiyz3hs3', NULL, 'sub_504ksi201k6033es'),
(59614, 'pay_jlcv0vxtssx6c86h', 236, 29.90, 'PENDING', '2025-08-28', NULL, '2025-07-20 00:00:00', '2025-07-29 10:49:24', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/jlcv0vxtssx6c86h', NULL, 'sub_bwvnv3t5548q79az'),
(59616, 'pay_jzqaavsd8sjuppfs', 174, 29.90, 'PENDING', '2025-08-25', NULL, '2025-07-17 00:00:00', '2025-07-29 10:49:26', 'Plano Mensal Hosp. + Manutenção E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/jzqaavsd8sjuppfs', NULL, 'sub_3scto700oy8oqo8m'),
(59618, 'pay_4vlj5ni5uo6cp89z', 175, 29.90, 'PENDING', '2025-08-25', NULL, '2025-07-17 00:00:00', '2025-07-29 10:49:27', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4vlj5ni5uo6cp89z', NULL, 'sub_fgev4t35r9zvziov'),
(59620, 'pay_u1zz9i2wkjp43s26', 245, 29.90, 'PENDING', '2025-08-25', NULL, '2025-07-17 00:00:00', '2025-07-29 10:49:29', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/u1zz9i2wkjp43s26', NULL, 'sub_f2zgsu6ds7fskv75'),
(59622, 'pay_aks3wirxn7y7kh3x', 284, 29.90, 'PENDING', '2025-08-25', NULL, '2025-07-17 00:00:00', '2025-07-29 10:49:30', 'Plano Hospedagem Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/aks3wirxn7y7kh3x', NULL, 'sub_khpfl1dpc6pfj9ix'),
(59624, 'pay_eorkbr64ykixs0x8', 202, 29.90, 'PENDING', '2025-08-23', NULL, '2025-07-15 00:00:00', '2025-07-29 10:49:32', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/eorkbr64ykixs0x8', NULL, 'sub_5taox270k3eejldi'),
(59626, 'pay_i0i6sn7woq5s4fk5', 246, 12.99, 'PENDING', '2025-08-21', NULL, '2025-07-13 00:00:00', '2025-07-29 10:49:33', '', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/i0i6sn7woq5s4fk5', NULL, 'sub_nmz4czosjvjghs8c'),
(59628, 'pay_0jeqm5u5clq7uxa3', 251, 29.90, 'PENDING', '2025-08-21', NULL, '2025-07-13 00:00:00', '2025-07-29 10:49:35', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/0jeqm5u5clq7uxa3', NULL, 'sub_fwc0baa1kthqsncu'),
(59630, 'pay_e791pyo1mfqpu184', 237, 29.90, 'PENDING', '2025-08-18', NULL, '2025-07-10 00:00:00', '2025-07-29 10:49:36', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'BOLETO', NULL, 'https://www.asaas.com/i/e791pyo1mfqpu184', NULL, 'sub_tjd6ivet6h510mad'),
(59632, 'pay_aifgqagtvjqau3a0', 190, 29.90, 'PENDING', '2025-08-17', NULL, '2025-07-09 00:00:00', '2025-07-29 10:49:38', 'Plano de Hospedagem e Manutenção Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/aifgqagtvjqau3a0', NULL, 'sub_g3gra67j24pjeh52'),
(59634, 'pay_4req7cnojy3aah0n', 205, 29.90, 'PENDING', '2025-08-17', NULL, '2025-07-09 00:00:00', '2025-07-29 10:49:40', 'Plano de Hospedagem e Manutenção Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/4req7cnojy3aah0n', NULL, 'sub_nlzsju9rjj93dm4f'),
(59636, 'pay_83lsyvlojtmlorh9', 196, 29.90, 'PENDING', '2025-08-15', NULL, '2025-07-07 00:00:00', '2025-07-29 10:49:41', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/83lsyvlojtmlorh9', NULL, 'sub_lul4v892i4z7gsnz'),
(59638, 'pay_5y5gwwrorv5uob2m', 222, 29.90, 'PENDING', '2025-08-15', NULL, '2025-07-07 00:00:00', '2025-07-29 10:49:43', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/5y5gwwrorv5uob2m', NULL, 'sub_ma8iqwnxbogl6d7k'),
(59640, 'pay_psgs5cxffwk149d6', 254, 29.90, 'PENDING', '2025-08-15', NULL, '2025-07-07 00:00:00', '2025-07-29 10:49:44', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/psgs5cxffwk149d6', NULL, 'sub_oil9ich37nuhoes7'),
(59642, 'pay_0um0ysbtjwvv0lr6', 286, 49.90, 'PENDING', '2025-08-15', NULL, '2025-07-07 00:00:00', '2025-07-29 10:49:46', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/0um0ysbtjwvv0lr6', NULL, 'sub_fte624var188bxrz'),
(59644, 'pay_jfvm72stq13xtu5y', 256, 29.90, 'PENDING', '2025-08-13', NULL, '2025-07-05 00:00:00', '2025-07-29 10:49:47', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/jfvm72stq13xtu5y', NULL, 'sub_a9g0wq5bypr3lt2h'),
(59646, 'pay_ka6ro7jy0n4z70fu', 145, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:49', 'Plano de Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/ka6ro7jy0n4z70fu', NULL, 'sub_yu61fhoe4ntoohiy'),
(59648, 'pay_odu0vaq55zkuldz9', 235, 29.90, 'RECEIVED', '2025-07-04', '2025-07-04', '2025-07-04 00:00:00', '2025-07-29 10:49:51', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: manutenção do site da agência', 'PIX', NULL, 'https://www.asaas.com/i/odu0vaq55zkuldz9', NULL, NULL),
(59650, 'pay_kj0omm99se7dxeoc', 146, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:52', 'Plano de Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/kj0omm99se7dxeoc', NULL, 'sub_e7sz11hye20kc0f0'),
(59652, 'pay_o39ps0bba5kkmqgg', 148, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:54', 'Ref. Plano de Hospedagem', 'BOLETO', NULL, 'https://www.asaas.com/i/o39ps0bba5kkmqgg', NULL, 'sub_yhiyp0yhxm1a4es4'),
(59654, 'pay_dxuvie0sesjjdleh', 215, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:55', 'Referente plano de hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/dxuvie0sesjjdleh', NULL, 'sub_7ixl6dcfy7cwqpcf'),
(59656, 'pay_iojbao7qqgemvczn', 257, 29.90, 'PENDING', '2025-08-12', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:57', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/iojbao7qqgemvczn', NULL, 'sub_ju5bkk8q06x0vid9'),
(59658, 'pay_56bpw8bkbk4zxbz5', 258, 39.90, 'PENDING', '2025-08-12', NULL, '2025-07-04 00:00:00', '2025-07-29 10:49:58', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/56bpw8bkbk4zxbz5', NULL, 'sub_feo8zvrsmaf5q1tq'),
(59660, 'pay_fvvag381yfn1q5o0', 264, 29.90, 'PENDING', '2025-08-12', NULL, '2025-07-04 00:00:00', '2025-07-29 10:50:00', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/fvvag381yfn1q5o0', NULL, 'sub_ohuo9f9u852d7wki'),
(59662, 'pay_wdxqu4xac2x9ccvv', 163, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:01', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/wdxqu4xac2x9ccvv', NULL, 'sub_za1sv4psb0cjv2h1'),
(59664, 'pay_b8mp3rrxhltzh166', 201, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:03', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/b8mp3rrxhltzh166', NULL, 'sub_haj4picrjjyojxcs'),
(59666, 'pay_ahbo59n6my9npnds', 164, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:05', 'Plano de Hospedagem e Manutenção ', 'BOLETO', NULL, 'https://www.asaas.com/i/ahbo59n6my9npnds', NULL, 'sub_drdk2zvb9ae8mole'),
(59668, 'pay_3cdmsm46jx8kvxyo', 265, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:06', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/3cdmsm46jx8kvxyo', NULL, 'sub_02etd71kzxkv2vyl'),
(59670, 'pay_iabb506x1489l2fy', 266, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:08', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/iabb506x1489l2fy', NULL, 'sub_hs78qzpdzx1z44q2'),
(59672, 'pay_36z9338m69af84of', 182, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:09', 'Plano de Hosedagem e Manutenção + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/36z9338m69af84of', NULL, 'sub_0yj29gf6bw4it6od'),
(59674, 'pay_pu4nqs7j9q2iow4e', 183, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:11', 'Desenvolvimento Web', 'BOLETO', NULL, 'https://www.asaas.com/i/pu4nqs7j9q2iow4e', NULL, 'sub_0hdfdzqa53osh2ma'),
(59676, 'pay_my5w2npn5303zv5l', 188, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:12', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'UNDEFINED', NULL, 'https://www.asaas.com/i/my5w2npn5303zv5l', NULL, 'sub_sh9rki6ysvvln4kx'),
(59678, 'pay_fj72exqy4hj3yx4g', 206, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:14', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/fj72exqy4hj3yx4g', NULL, 'sub_b1fy6en75pgrzeoi'),
(59680, 'pay_fz91gvd5mj3ke2m8', 209, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:15', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/fz91gvd5mj3ke2m8', NULL, 'sub_sp6cmivnmdkdywlg'),
(59682, 'pay_plrmb0a7t2liwkuo', 235, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:17', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/plrmb0a7t2liwkuo', NULL, 'sub_ijill2r7k1rrfo9s'),
(59684, 'pay_xb7dxpn87tsptzlh', 199, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:19', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/xb7dxpn87tsptzlh', NULL, 'sub_m3c69vzc322xsk1x'),
(59686, 'pay_ykk15zdotbrc77ib', 234, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:20', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/ykk15zdotbrc77ib', NULL, 'sub_5zhf56f9xgaopx3x'),
(59688, 'pay_z6723hnpj4i32zmt', 224, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:22', 'Plano de Hespedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/z6723hnpj4i32zmt', NULL, 'sub_906jjzs1wiwlv535'),
(59690, 'pay_bvvqk7awc1eax5ki', 227, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:23', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/bvvqk7awc1eax5ki', NULL, 'sub_uof9cualj3j78zmc'),
(59692, 'pay_peacug9t7jvnqd08', 232, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:25', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/peacug9t7jvnqd08', NULL, 'sub_dhl2ubdq46degqsc'),
(59694, 'pay_0a0ipvpkso2bvwn9', 283, 49.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:26', 'Plano Hospedagem Digital Plus', 'UNDEFINED', NULL, 'https://www.asaas.com/i/0a0ipvpkso2bvwn9', NULL, 'sub_sgyxlte8bnaedtfj'),
(59696, 'pay_rm2ecd5w7009lnay', 240, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-03 00:00:00', '2025-07-29 10:50:28', 'Plano ESSENCIAL Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/rm2ecd5w7009lnay', NULL, 'sub_1p83yf64ebdz09eh'),
(59698, 'pay_tuifivirue12bbpg', 225, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-02 00:00:00', '2025-07-29 10:50:29', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/tuifivirue12bbpg', NULL, 'sub_onezkhx9uo06h1we'),
(59700, 'pay_6p36srr6r5xyarga', 263, 29.90, 'PENDING', '2025-08-10', NULL, '2025-07-02 00:00:00', '2025-07-29 10:50:31', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/6p36srr6r5xyarga', NULL, 'sub_re7f5cvc2t4u6fuo'),
(59702, 'pay_m98yd3gg6hwzuaxm', 144, 350.00, 'RECEIVED', '2025-07-01', '2025-07-01', '2025-07-01 00:00:00', '2025-07-29 10:50:33', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/m98yd3gg6hwzuaxm', NULL, NULL),
(59704, 'pay_euwg4qf85re9h95t', 152, 550.00, 'RECEIVED', '2025-06-30', '2025-06-30', '2025-06-30 00:00:00', '2025-07-29 10:50:34', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/euwg4qf85re9h95t', NULL, NULL),
(59706, 'pay_tx89pt7zep02ki8u', 156, 2075.37, 'RECEIVED', '2025-06-27', '2025-06-27', '2025-06-27 00:00:00', '2025-07-29 10:50:36', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/tx89pt7zep02ki8u', NULL, NULL),
(59708, 'pay_9ju6hkzvk8frwoc6', 279, 49.90, 'PENDING', '2025-08-05', NULL, '2025-06-27 00:00:00', '2025-07-29 10:50:37', 'Plano Mensal Manutenção e Hospedagem', 'BOLETO', NULL, 'https://www.asaas.com/i/9ju6hkzvk8frwoc6', NULL, 'sub_htfllf6l0cu6s4qk'),
(59710, 'pay_2q4pt5tfift7b221', 154, 60.00, 'RECEIVED', '2025-06-25', '2025-06-25', '2025-06-25 00:00:00', '2025-07-29 10:50:39', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/2q4pt5tfift7b221', NULL, NULL),
(59712, 'pay_xlxntviwy8k357i7', 173, 30.00, 'RECEIVED', '2025-06-24', '2025-06-24', '2025-06-24 00:00:00', '2025-07-29 10:50:40', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/xlxntviwy8k357i7', NULL, NULL),
(59714, 'pay_uhrx8tb6du377e6v', 166, 238.80, 'RECEIVED', '2025-06-23', '2025-06-23', '2025-06-23 00:00:00', '2025-07-29 10:50:42', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/uhrx8tb6du377e6v', NULL, NULL),
(59716, 'pay_mnphrheig3ek3s16', 145, 232.34, 'PENDING', '2025-08-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 3 de 3. Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/mnphrheig3ek3s16', '3', NULL),
(59718, 'pay_cxg5s0hz05y49uwa', 145, 232.33, 'RECEIVED', '2025-07-23', '2025-06-25', '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 2 de 3. Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/cxg5s0hz05y49uwa', '2', NULL),
(59720, 'pay_uatslddjrkpj9saj', 145, 232.33, 'OVERDUE', '2025-07-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 1 de 3. Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/uatslddjrkpj9saj', '1', NULL),
(59723, 'pay_fd9u2kav6li8fu9b', 145, 29.90, 'RECEIVED', '2025-07-15', '2025-07-15', '2025-06-23 00:00:00', '2025-07-29 10:50:48', 'Plano de Hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/fd9u2kav6li8fu9b', NULL, 'sub_yu61fhoe4ntoohiy'),
(59725, 'pay_skn9l8wha4d1r0nk', 260, 19.90, 'PENDING', '2026-05-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 12 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/skn9l8wha4d1r0nk', '12', NULL),
(59727, 'pay_xvrj0dzv7m8mw2e2', 260, 19.90, 'PENDING', '2026-04-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 11 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/xvrj0dzv7m8mw2e2', '11', NULL),
(59729, 'pay_eew6o162af96lzas', 260, 19.90, 'PENDING', '2026-03-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 10 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/eew6o162af96lzas', '10', NULL),
(59731, 'pay_6dpta9krp6gyzpvd', 260, 19.90, 'PENDING', '2026-02-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 9 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6dpta9krp6gyzpvd', '9', NULL),
(59733, 'pay_xk1flj4el7toxaf8', 260, 19.90, 'PENDING', '2026-01-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 8 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/xk1flj4el7toxaf8', '8', NULL),
(59735, 'pay_kk3sfhh5bw2018g5', 260, 19.90, 'PENDING', '2025-12-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 7 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/kk3sfhh5bw2018g5', '7', NULL),
(59737, 'pay_cvqapgdljkwt5nku', 260, 19.90, 'PENDING', '2025-11-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 6 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/cvqapgdljkwt5nku', '6', NULL),
(59738, 'pay_2tevs0ezm43otne1', 260, 19.90, 'PENDING', '2025-10-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 5 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/2tevs0ezm43otne1', '5', NULL),
(59741, 'pay_oboq66bby3adc9ky', 260, 19.90, 'PENDING', '2025-09-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 4 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/oboq66bby3adc9ky', '4', NULL),
(59743, 'pay_k5m8cbgx9asspv8y', 260, 19.90, 'PENDING', '2025-08-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 3 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/k5m8cbgx9asspv8y', '3', NULL),
(59745, 'pay_1j97iz6o8mi1beff', 260, 19.90, 'OVERDUE', '2025-07-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 2 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1j97iz6o8mi1beff', '2', NULL),
(59747, 'pay_fijozhuzniy1xaj2', 260, 19.90, 'OVERDUE', '2025-06-23', NULL, '2025-06-23 00:00:00', '2025-07-29 07:56:19', 'Parcela 1 de 12. Plano de Hospedagem Anual: rotacar67.com.br\r\nRenovação: 23/06/2026', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fijozhuzniy1xaj2', '1', NULL),
(59749, 'pay_1j9fgzw9mtvns2rj', 260, 238.80, 'PENDING', '2026-06-23', NULL, '2025-06-23 00:00:00', '2025-07-29 10:51:09', 'Renovação Anual Hospedagem: rotacar67.com.br', 'UNDEFINED', NULL, 'https://www.asaas.com/i/1j9fgzw9mtvns2rj', NULL, 'sub_tx1178y7a4i4yp63'),
(59751, 'pay_dgxji2z6m97tdge6', 169, 29.90, 'OVERDUE', '2025-07-30', NULL, '2025-06-21 00:00:00', '2025-07-29 10:51:10', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/dgxji2z6m97tdge6', NULL, 'sub_bebghk37yr7kaykc'),
(59753, 'pay_20asii7d0ubz5h2a', 185, 49.90, 'OVERDUE', '2025-07-29', NULL, '2025-06-20 00:00:00', '2025-07-29 10:51:12', 'Plano ImobSites ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/20asii7d0ubz5h2a', NULL, 'sub_0u0hiabbslpiov8l'),
(59755, 'pay_rj5odjpkybo354p3', 220, 29.90, 'RECEIVED', '2025-07-29', '2025-07-14', '2025-06-20 00:00:00', '2025-07-29 10:51:13', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/rj5odjpkybo354p3', NULL, 'sub_0ob5gqdk6bgm9evj'),
(59757, 'pay_8zmxfqsjdhokrj5i', 267, 29.00, 'OVERDUE', '2025-07-29', NULL, '2025-06-20 00:00:00', '2025-07-29 10:51:15', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/8zmxfqsjdhokrj5i', NULL, 'sub_504ksi201k6033es'),
(59759, 'pay_bb1yjdj4tayprxab', 236, 29.90, 'RECEIVED_IN_CASH', '2025-07-28', '2025-07-30', '2025-06-19 00:00:00', '2025-07-29 10:51:17', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/bb1yjdj4tayprxab', NULL, 'sub_bwvnv3t5548q79az'),
(59761, 'pay_junzraiemubm79yb', 174, 29.90, 'OVERDUE', '2025-07-25', NULL, '2025-06-16 00:00:00', '2025-07-29 10:51:18', 'Plano Mensal Hosp. + Manutenção E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/junzraiemubm79yb', NULL, 'sub_3scto700oy8oqo8m'),
(59763, 'pay_n53i0umzqgdv17zc', 175, 29.90, 'OVERDUE', '2025-07-25', NULL, '2025-06-16 00:00:00', '2025-07-29 10:51:20', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/n53i0umzqgdv17zc', NULL, 'sub_fgev4t35r9zvziov'),
(59765, 'pay_2xrdioe64c9krck1', 245, 29.90, 'OVERDUE', '2025-07-25', NULL, '2025-06-16 00:00:00', '2025-07-29 10:51:21', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/2xrdioe64c9krck1', NULL, 'sub_f2zgsu6ds7fskv75'),
(59767, 'pay_t3hcodlrqwj0b18h', 284, 29.90, 'OVERDUE', '2025-07-25', NULL, '2025-06-16 00:00:00', '2025-07-29 10:51:23', 'Plano Hospedagem Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/t3hcodlrqwj0b18h', NULL, 'sub_khpfl1dpc6pfj9ix'),
(59769, 'pay_0hrytbhjsqvy0j3i', 202, 29.90, 'OVERDUE', '2025-07-23', NULL, '2025-06-14 00:00:00', '2025-07-29 10:51:24', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/0hrytbhjsqvy0j3i', NULL, 'sub_5taox270k3eejldi'),
(59771, 'pay_uy461cdp34j4z8as', 246, 12.99, 'OVERDUE', '2025-07-21', NULL, '2025-06-12 00:00:00', '2025-07-29 10:51:26', '', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/uy461cdp34j4z8as', NULL, 'sub_nmz4czosjvjghs8c'),
(59773, 'pay_9nlkr6j8mfjrz4ix', 251, 29.90, 'OVERDUE', '2025-07-21', NULL, '2025-06-12 00:00:00', '2025-07-29 10:51:28', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/9nlkr6j8mfjrz4ix', NULL, 'sub_fwc0baa1kthqsncu'),
(59775, 'pay_by3gyf0z3eaps2x1', 146, 197.80, 'CONFIRMED', '2025-11-10', NULL, '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 6 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/by3gyf0z3eaps2x1', '6', NULL),
(59777, 'pay_koln6qqfof6b7gv2', 146, 197.80, 'CONFIRMED', '2025-10-10', NULL, '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 5 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/koln6qqfof6b7gv2', '5', NULL),
(59779, 'pay_0mffmmesoncbr6oa', 146, 197.80, 'CONFIRMED', '2025-09-10', NULL, '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 4 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/0mffmmesoncbr6oa', '4', NULL),
(59781, 'pay_6vtzexnlxwljm8q7', 146, 197.80, 'CONFIRMED', '2025-08-10', NULL, '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 3 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6vtzexnlxwljm8q7', '3', NULL),
(59783, 'pay_e1rtuv9mc87nic7v', 146, 197.80, 'CONFIRMED', '2025-07-10', NULL, '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 2 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/e1rtuv9mc87nic7v', '2', NULL),
(59784, 'pay_8i8rjp0a2dob7oza', 146, 197.80, 'RECEIVED', '2025-06-10', '2025-07-14', '2025-06-09 00:00:00', '2025-07-29 07:56:19', 'Parcela 1 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/8i8rjp0a2dob7oza', '1', NULL),
(59785, 'pay_miasyi1wt6bodubh', 237, 29.90, 'OVERDUE', '2025-07-18', NULL, '2025-06-09 00:00:00', '2025-07-29 10:51:39', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'BOLETO', NULL, 'https://www.asaas.com/i/miasyi1wt6bodubh', NULL, 'sub_tjd6ivet6h510mad'),
(59786, 'pay_00r47h4y8eg9335v', 147, 100.00, 'RECEIVED', '2025-06-08', '2025-06-08', '2025-06-08 00:00:00', '2025-07-29 10:51:40', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/00r47h4y8eg9335v', NULL, NULL),
(59787, 'pay_q46igjcv1s6a4blw', 190, 29.90, 'OVERDUE', '2025-07-17', NULL, '2025-06-08 00:00:00', '2025-07-29 10:51:42', 'Plano de Hospedagem e Manutenção Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/q46igjcv1s6a4blw', NULL, 'sub_g3gra67j24pjeh52'),
(59789, 'pay_n3iyyd309ss7dvzx', 205, 29.90, 'OVERDUE', '2025-07-17', NULL, '2025-06-08 00:00:00', '2025-07-29 10:51:43', 'Plano de Hospedagem e Manutenção Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/n3iyyd309ss7dvzx', NULL, 'sub_nlzsju9rjj93dm4f'),
(59791, 'pay_fw4jsld9i1rsglli', 196, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-06 00:00:00', '2025-07-29 10:51:45', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/fw4jsld9i1rsglli', NULL, 'sub_lul4v892i4z7gsnz'),
(59793, 'pay_2yvzx3xmp4kj0tp3', 222, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-06 00:00:00', '2025-07-29 10:51:46', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/2yvzx3xmp4kj0tp3', NULL, 'sub_ma8iqwnxbogl6d7k'),
(59800, 'pay_rhn6wfpsid54kk11', 254, 29.90, 'RECEIVED', '2025-07-15', '2025-07-11', '2025-06-06 00:00:00', '2025-07-29 10:51:51', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/rhn6wfpsid54kk11', NULL, 'sub_oil9ich37nuhoes7'),
(59802, 'pay_nhneruj36ed4stkf', 286, 51.05, 'RECEIVED', '2025-07-15', '2025-07-17', '2025-06-06 00:00:00', '2025-07-29 10:51:52', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/nhneruj36ed4stkf', NULL, 'sub_fte624var188bxrz'),
(59804, 'pay_a5tdpd0to0awefs3', 256, 29.90, 'OVERDUE', '2025-07-13', NULL, '2025-06-04 00:00:00', '2025-07-29 10:51:54', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/a5tdpd0to0awefs3', NULL, 'sub_a9g0wq5bypr3lt2h'),
(59806, 'pay_41af2hztxr9f8g65', 215, 30.58, 'RECEIVED', '2025-07-15', '2025-07-25', '2025-06-03 00:00:00', '2025-07-29 10:51:55', 'Referente plano de hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/41af2hztxr9f8g65', NULL, 'sub_7ixl6dcfy7cwqpcf'),
(59808, 'pay_pvsn5rqizuntz297', 147, 400.00, 'RECEIVED', '2025-06-03', '2025-06-03', '2025-06-03 00:00:00', '2025-07-29 10:51:57', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/pvsn5rqizuntz297', NULL, NULL),
(59810, 'pay_cs0yb4ndf62birx2', 163, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-03 00:00:00', '2025-07-29 10:51:59', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/cs0yb4ndf62birx2', NULL, 'sub_za1sv4psb0cjv2h1'),
(59812, 'pay_9ioj221ig9sxnz3v', 201, 29.90, 'RECEIVED', '2025-07-10', '2025-07-02', '2025-06-03 00:00:00', '2025-07-29 10:52:00', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/9ioj221ig9sxnz3v', NULL, 'sub_haj4picrjjyojxcs'),
(59814, 'pay_07qgim0xp9hyst71', 164, 29.90, 'RECEIVED', '2025-07-15', '2025-07-14', '2025-06-03 00:00:00', '2025-07-29 10:52:02', 'Plano de Hospedagem e Manutenção ', 'BOLETO', NULL, 'https://www.asaas.com/i/07qgim0xp9hyst71', NULL, 'sub_drdk2zvb9ae8mole'),
(59816, 'pay_845hw1wnor9l01a4', 257, 29.90, 'OVERDUE', '2025-07-12', NULL, '2025-06-03 00:00:00', '2025-07-29 10:52:03', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/845hw1wnor9l01a4', NULL, 'sub_ju5bkk8q06x0vid9'),
(59818, 'pay_one6znxzim7fv7ws', 258, 39.90, 'OVERDUE', '2025-07-12', NULL, '2025-06-03 00:00:00', '2025-07-29 10:52:05', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/one6znxzim7fv7ws', NULL, 'sub_feo8zvrsmaf5q1tq'),
(59820, 'pay_ojiw3vc4swpdvikg', 264, 29.90, 'OVERDUE', '2025-07-12', NULL, '2025-06-03 00:00:00', '2025-07-29 10:52:06', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ojiw3vc4swpdvikg', NULL, 'sub_ohuo9f9u852d7wki'),
(59822, 'pay_lgzcxn8znme7bho0', 265, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:08', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/lgzcxn8znme7bho0', NULL, 'sub_02etd71kzxkv2vyl'),
(59824, 'pay_c2gna6j34xyy61g9', 266, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:10', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/c2gna6j34xyy61g9', NULL, 'sub_hs78qzpdzx1z44q2'),
(59826, 'pay_wzbantkhfkl9q18l', 182, 29.90, 'RECEIVED', '2025-07-15', '2025-07-15', '2025-06-02 00:00:00', '2025-07-29 10:52:11', 'Plano de Hosedagem e Manutenção + E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/wzbantkhfkl9q18l', NULL, 'sub_0yj29gf6bw4it6od'),
(59828, 'pay_es6tpku3j8j7fn17', 183, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:13', 'Desenvolvimento Web', 'BOLETO', NULL, 'https://www.asaas.com/i/es6tpku3j8j7fn17', NULL, 'sub_0hdfdzqa53osh2ma'),
(59830, 'pay_lm1kirki5lchd6se', 188, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:14', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'UNDEFINED', NULL, 'https://www.asaas.com/i/lm1kirki5lchd6se', NULL, 'sub_sh9rki6ysvvln4kx'),
(59832, 'pay_v44uxd4pb1lxehk6', 235, 29.90, 'RECEIVED', '2025-06-02', '2025-06-02', '2025-06-02 00:00:00', '2025-07-29 10:52:16', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: pagamento do site fuga da cidade', 'PIX', NULL, 'https://www.asaas.com/i/v44uxd4pb1lxehk6', NULL, NULL),
(59834, 'pay_tes4xon6wvlpnfpq', 206, 30.49, 'RECEIVED', '2025-07-15', '2025-07-16', '2025-06-02 00:00:00', '2025-07-29 10:52:17', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/tes4xon6wvlpnfpq', NULL, 'sub_b1fy6en75pgrzeoi'),
(59836, 'pay_2flquo1d37igmlkn', 209, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:19', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/2flquo1d37igmlkn', NULL, 'sub_sp6cmivnmdkdywlg'),
(59838, 'pay_8cou004bodnx3awv', 235, 29.90, 'RECEIVED_IN_CASH', '2025-07-10', '2025-07-08', '2025-06-02 00:00:00', '2025-07-29 10:52:20', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/8cou004bodnx3awv', NULL, 'sub_ijill2r7k1rrfo9s'),
(59840, 'pay_eh74zklunsj3mekv', 199, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:22', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/eh74zklunsj3mekv', NULL, 'sub_m3c69vzc322xsk1x'),
(59842, 'pay_3r7jfele4tdzr3o5', 234, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:24', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/3r7jfele4tdzr3o5', NULL, 'sub_5zhf56f9xgaopx3x'),
(59844, 'pay_hpllksl57uzxabtd', 224, 29.90, 'RECEIVED', '2025-07-15', '2025-07-14', '2025-06-02 00:00:00', '2025-07-29 10:52:25', 'Plano de Hespedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/hpllksl57uzxabtd', NULL, 'sub_906jjzs1wiwlv535'),
(59846, 'pay_jbt44w7d2kab91yo', 227, 29.90, 'RECEIVED', '2025-07-15', '2025-07-15', '2025-06-02 00:00:00', '2025-07-29 10:52:27', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'PIX', NULL, 'https://www.asaas.com/i/jbt44w7d2kab91yo', NULL, 'sub_uof9cualj3j78zmc'),
(59848, 'pay_xyc4f2lr1554x78h', 232, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:28', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/xyc4f2lr1554x78h', NULL, 'sub_dhl2ubdq46degqsc'),
(59850, 'pay_ja8gp9um5e89i3cd', 283, 49.90, 'RECEIVED', '2025-07-10', '2025-07-08', '2025-06-02 00:00:00', '2025-07-29 10:52:30', 'Plano Hospedagem Digital Plus', 'BOLETO', NULL, 'https://www.asaas.com/i/ja8gp9um5e89i3cd', NULL, 'sub_sgyxlte8bnaedtfj'),
(59852, 'pay_jecz2m6dooghc8cc', 240, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:31', 'Plano ESSENCIAL Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/jecz2m6dooghc8cc', NULL, 'sub_1p83yf64ebdz09eh'),
(59854, 'pay_xk3ejd47w11kgc46', 225, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-06-02 00:00:00', '2025-07-29 10:52:33', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/xk3ejd47w11kgc46', NULL, 'sub_onezkhx9uo06h1we'),
(59856, 'pay_f8119ipu4m4umwu8', 263, 29.90, 'RECEIVED', '2025-07-15', '2025-07-14', '2025-06-01 00:00:00', '2025-07-29 10:52:34', '', 'PIX', NULL, 'https://www.asaas.com/i/f8119ipu4m4umwu8', NULL, 'sub_re7f5cvc2t4u6fuo'),
(59858, 'pay_30hj7sjme72j07g1', 246, 1000.00, 'RECEIVED', '2025-05-30', '2025-05-30', '2025-05-30 00:00:00', '2025-07-29 10:52:36', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/30hj7sjme72j07g1', NULL, NULL),
(59860, 'pay_o5u1tnk4ccowwzoz', 279, 49.90, 'OVERDUE', '2025-07-05', NULL, '2025-05-27 00:00:00', '2025-07-29 10:52:38', 'Plano Mensal Manutenção e Hospedagem', 'BOLETO', NULL, 'https://www.asaas.com/i/o5u1tnk4ccowwzoz', NULL, 'sub_htfllf6l0cu6s4qk'),
(59862, 'pay_emkwfdhcd2terxky', 222, 333.00, 'RECEIVED', '2025-05-26', '2025-05-26', '2025-05-26 00:00:00', '2025-07-29 10:52:39', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/emkwfdhcd2terxky', NULL, NULL),
(59864, 'pay_o9j388ckh3f0psog', 148, 283.34, 'OVERDUE', '2025-07-26', NULL, '2025-05-26 00:00:00', '2025-07-29 07:56:22', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/o9j388ckh3f0psog', '3', NULL),
(59866, 'pay_t86dcux8y9m3800k', 148, 283.33, 'OVERDUE', '2025-06-26', NULL, '2025-05-26 00:00:00', '2025-07-29 07:56:22', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/t86dcux8y9m3800k', '2', NULL),
(59868, 'pay_fm9qof1ygf82zic6', 148, 283.33, 'OVERDUE', '2025-05-26', NULL, '2025-05-26 00:00:00', '2025-07-29 07:56:22', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/fm9qof1ygf82zic6', '1', NULL),
(59870, 'pay_f9hz0gi0wfm8dc0d', 148, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-05-26 00:00:00', '2025-07-29 10:52:46', 'Ref. Plano de Hospedagem', 'BOLETO', NULL, 'https://www.asaas.com/i/f9hz0gi0wfm8dc0d', NULL, 'sub_yhiyp0yhxm1a4es4'),
(59872, 'pay_m6xtvkuotror0mzs', 169, 29.90, 'OVERDUE', '2025-06-30', NULL, '2025-05-22 00:00:00', '2025-07-29 10:52:47', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/m6xtvkuotror0mzs', NULL, 'sub_bebghk37yr7kaykc'),
(59874, 'pay_1vuor9nmkc4c4ujv', 185, 49.90, 'OVERDUE', '2025-06-29', NULL, '2025-05-21 00:00:00', '2025-07-29 10:52:49', 'Plano ImobSites ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/1vuor9nmkc4c4ujv', NULL, 'sub_0u0hiabbslpiov8l'),
(59876, 'pay_qprg3p5tghwlwjjl', 220, 29.90, 'RECEIVED', '2025-06-29', '2025-06-02', '2025-05-21 00:00:00', '2025-07-29 10:52:50', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/qprg3p5tghwlwjjl', NULL, 'sub_0ob5gqdk6bgm9evj'),
(59878, 'pay_by1nq94s5tztjul0', 267, 29.00, 'RECEIVED_IN_CASH', '2025-06-29', '2025-07-08', '2025-05-21 00:00:00', '2025-07-29 10:52:52', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/by1nq94s5tztjul0', NULL, 'sub_504ksi201k6033es'),
(59880, 'pay_p6ae9welcaokvt0u', 236, 29.90, 'RECEIVED_IN_CASH', '2025-06-28', '2025-07-30', '2025-05-20 00:00:00', '2025-07-29 10:52:53', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/p6ae9welcaokvt0u', NULL, 'sub_bwvnv3t5548q79az'),
(59882, 'pay_iyw4porp9anwxo2e', 190, 425.60, 'RECEIVED', '2025-05-19', '2025-05-19', '2025-05-19 00:00:00', '2025-07-29 10:52:55', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/iyw4porp9anwxo2e', NULL, NULL),
(59884, 'pay_w25weguzkx0pd5qt', 201, 150.00, 'RECEIVED', '2025-06-20', '2025-06-03', '2025-05-19 00:00:00', '2025-07-29 07:56:22', 'Parcela 2 de 2. Criação e Configuração de Campanha no Google ADS', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/w25weguzkx0pd5qt', '2', NULL),
(59886, 'pay_olovn5yul25h3zwz', 201, 150.00, 'RECEIVED', '2025-05-20', '2025-05-19', '2025-05-19 00:00:00', '2025-07-29 07:56:22', 'Parcela 1 de 2. Criação e Configuração de Campanha no Google ADS', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/olovn5yul25h3zwz', '1', NULL),
(59888, 'pay_gpb89vdskwec7zxz', 174, 29.90, 'OVERDUE', '2025-06-25', NULL, '2025-05-17 00:00:00', '2025-07-29 10:53:00', 'Plano Mensal Hosp. + Manutenção E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/gpb89vdskwec7zxz', NULL, 'sub_3scto700oy8oqo8m'),
(59890, 'pay_t0562wj36yuif67m', 175, 29.90, 'OVERDUE', '2025-06-25', NULL, '2025-05-17 00:00:00', '2025-07-29 10:53:01', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/t0562wj36yuif67m', NULL, 'sub_fgev4t35r9zvziov'),
(59892, 'pay_cjum7y3wthr7a9qs', 245, 29.90, 'OVERDUE', '2025-06-25', NULL, '2025-05-17 00:00:00', '2025-07-29 10:53:03', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/cjum7y3wthr7a9qs', NULL, 'sub_f2zgsu6ds7fskv75'),
(59894, 'pay_k2dw6iagcuiksxbo', 284, 30.55, 'RECEIVED', '2025-06-25', '2025-07-02', '2025-05-17 00:00:00', '2025-07-29 10:53:04', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/k2dw6iagcuiksxbo', NULL, 'sub_khpfl1dpc6pfj9ix'),
(59896, 'pay_pk2p6bbf8qvkxvvh', 149, 200.00, 'RECEIVED', '2025-05-16', '2025-05-16', '2025-05-16 00:00:00', '2025-07-29 10:53:06', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/pk2p6bbf8qvkxvvh', NULL, NULL),
(59898, 'pay_zgp1oe3s4d7r5qfb', 202, 29.90, 'OVERDUE', '2025-06-23', NULL, '2025-05-15 00:00:00', '2025-07-29 10:53:07', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/zgp1oe3s4d7r5qfb', NULL, 'sub_5taox270k3eejldi'),
(59900, 'pay_x966sv3bvbswcmqx', 271, 39.90, 'OVERDUE', '2025-06-23', NULL, '2025-05-15 00:00:00', '2025-07-29 10:53:09', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/x966sv3bvbswcmqx', NULL, 'sub_1dx95biw49q1zskp'),
(59902, 'pay_kms5uy1gvs2tntvo', 283, 714.90, 'RECEIVED', '2025-05-14', '2025-05-14', '2025-05-14 00:00:00', '2025-07-29 10:53:11', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/kms5uy1gvs2tntvo', NULL, NULL),
(59904, 'pay_h1yocpl8c680gkjh', 158, 29.90, 'RECEIVED', '2025-05-13', '2025-05-13', '2025-05-13 00:00:00', '2025-07-29 10:53:12', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/h1yocpl8c680gkjh', NULL, NULL),
(59906, 'pay_07770rs1134d0zu5', 150, 300.00, 'RECEIVED', '2025-06-13', '2025-05-13', '2025-05-13 00:00:00', '2025-07-29 07:56:22', 'Parcela 2 de 2. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/07770rs1134d0zu5', '2', NULL),
(59908, 'pay_8xqrj42hp5nmv493', 150, 300.00, 'OVERDUE', '2025-06-13', NULL, '2025-05-13 00:00:00', '2025-07-29 07:56:22', 'Parcela 1 de 2. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/8xqrj42hp5nmv493', '1', NULL),
(59910, 'pay_g5qt5harr2x41iwb', 151, 1925.00, 'RECEIVED', '2025-05-13', '2025-05-13', '2025-05-13 00:00:00', '2025-07-29 10:53:17', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/g5qt5harr2x41iwb', NULL, NULL),
(59912, 'pay_ftvi5o44z2rkryqw', 246, 12.99, 'OVERDUE', '2025-06-21', NULL, '2025-05-13 00:00:00', '2025-07-29 10:53:18', '', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/ftvi5o44z2rkryqw', NULL, 'sub_nmz4czosjvjghs8c'),
(59914, 'pay_aeoltqfz02np7q75', 251, 29.90, 'OVERDUE', '2025-06-21', NULL, '2025-05-13 00:00:00', '2025-07-29 10:53:20', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/aeoltqfz02np7q75', NULL, 'sub_fwc0baa1kthqsncu'),
(59916, 'pay_2audhg4ydhiv8mgb', 152, 250.00, 'RECEIVED', '2025-05-12', '2025-05-12', '2025-05-12 00:00:00', '2025-07-29 10:53:22', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/2audhg4ydhiv8mgb', NULL, NULL),
(59918, 'pay_oztrmc4m7g3alk2g', 236, 59.80, 'RECEIVED', '2025-05-12', '2025-05-12', '2025-05-12 00:00:00', '2025-07-29 10:53:23', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/oztrmc4m7g3alk2g', NULL, NULL),
(59920, 'pay_dql22k90turk00h5', 153, 32.40, 'RECEIVED', '2025-05-12', '2025-05-12', '2025-05-12 00:00:00', '2025-07-29 10:53:25', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/dql22k90turk00h5', NULL, NULL),
(59922, 'pay_0lo5c31vzedxg27e', 166, 165.66, 'RECEIVED', '2025-05-09', '2025-05-09', '2025-05-09 00:00:00', '2025-07-29 10:53:26', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/0lo5c31vzedxg27e', NULL, NULL),
(59924, 'pay_0j9tecqwbq59211n', 234, 29.90, 'RECEIVED', '2025-05-09', '2025-05-09', '2025-05-09 00:00:00', '2025-07-29 10:53:28', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/0j9tecqwbq59211n', NULL, NULL),
(59926, 'pay_gnhzcu21hu7wbhld', 190, 29.90, 'OVERDUE', '2025-06-17', NULL, '2025-05-09 00:00:00', '2025-07-29 10:53:29', 'Plano de Hospedagem e Manutenção Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/gnhzcu21hu7wbhld', NULL, 'sub_g3gra67j24pjeh52'),
(59928, 'pay_1cqeksqlvbn7y403', 205, 29.90, 'OVERDUE', '2025-06-17', NULL, '2025-05-09 00:00:00', '2025-07-29 10:53:31', 'Plano de Hospedagem e Manutenção Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/1cqeksqlvbn7y403', NULL, 'sub_nlzsju9rjj93dm4f'),
(59930, 'pay_xyhgg3h0lm0maodb', 196, 29.90, 'OVERDUE', '2025-06-15', NULL, '2025-05-07 00:00:00', '2025-07-29 10:53:33', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/xyhgg3h0lm0maodb', NULL, 'sub_lul4v892i4z7gsnz'),
(59932, 'pay_bs2rkfnjl910j48l', 222, 29.90, 'OVERDUE', '2025-06-15', NULL, '2025-05-07 00:00:00', '2025-07-29 10:53:34', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/bs2rkfnjl910j48l', NULL, 'sub_ma8iqwnxbogl6d7k'),
(59934, 'pay_y7qvq6qq770ol86t', 254, 30.74, 'RECEIVED', '2025-06-15', '2025-07-11', '2025-05-07 00:00:00', '2025-07-29 10:53:36', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/y7qvq6qq770ol86t', NULL, 'sub_oil9ich37nuhoes7'),
(59936, 'pay_5nk95op482uhr5q8', 286, 49.90, 'OVERDUE', '2025-06-15', NULL, '2025-05-07 00:00:00', '2025-07-29 10:53:37', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/5nk95op482uhr5q8', NULL, 'sub_fte624var188bxrz'),
(59938, 'pay_pqh0wpnuy9nswubm', 235, 29.90, 'RECEIVED', '2025-05-06', '2025-05-06', '2025-05-06 00:00:00', '2025-07-29 10:53:39', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: site da agência', 'PIX', NULL, 'https://www.asaas.com/i/pqh0wpnuy9nswubm', NULL, NULL),
(59940, 'pay_bj29om078xx4ru4d', 256, 29.90, 'OVERDUE', '2025-06-13', NULL, '2025-05-05 00:00:00', '2025-07-29 10:53:40', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/bj29om078xx4ru4d', NULL, 'sub_a9g0wq5bypr3lt2h'),
(59942, 'pay_gv6dk9hk8t0bvsb1', 215, 30.65, 'RECEIVED', '2025-06-10', '2025-06-27', '2025-05-04 00:00:00', '2025-07-29 10:53:42', 'Referente plano de hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/gv6dk9hk8t0bvsb1', NULL, 'sub_7ixl6dcfy7cwqpcf'),
(59944, 'pay_eu5h6sgfruojd1k7', 163, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-04 00:00:00', '2025-07-29 10:53:44', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/eu5h6sgfruojd1k7', NULL, 'sub_za1sv4psb0cjv2h1'),
(59946, 'pay_ncfc7tp6mh3qtkkk', 201, 29.90, 'RECEIVED', '2025-06-10', '2025-06-03', '2025-05-04 00:00:00', '2025-07-29 10:53:45', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/ncfc7tp6mh3qtkkk', NULL, 'sub_haj4picrjjyojxcs'),
(59948, 'pay_xus2e27urtlkd5n0', 164, 29.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-05-04 00:00:00', '2025-07-29 10:53:47', 'Plano de Hospedagem e Manutenção ', 'PIX', NULL, 'https://www.asaas.com/i/xus2e27urtlkd5n0', NULL, 'sub_drdk2zvb9ae8mole'),
(59950, 'pay_ujpg1rxm4420cdbp', 257, 29.90, 'OVERDUE', '2025-06-12', NULL, '2025-05-04 00:00:00', '2025-07-29 10:53:48', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ujpg1rxm4420cdbp', NULL, 'sub_ju5bkk8q06x0vid9'),
(59952, 'pay_xzh1h8n956yxj13w', 258, 39.90, 'OVERDUE', '2025-06-12', NULL, '2025-05-04 00:00:00', '2025-07-29 10:53:50', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/xzh1h8n956yxj13w', NULL, 'sub_feo8zvrsmaf5q1tq'),
(59954, 'pay_lu81bfyawlwtvwpc', 264, 29.90, 'OVERDUE', '2025-06-12', NULL, '2025-05-04 00:00:00', '2025-07-29 10:53:51', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/lu81bfyawlwtvwpc', NULL, 'sub_ohuo9f9u852d7wki'),
(59956, 'pay_5rde21w70fzxp7yz', 265, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:53:53', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/5rde21w70fzxp7yz', NULL, 'sub_02etd71kzxkv2vyl'),
(59958, 'pay_50nfvkcti92d302j', 266, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:53:54', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/50nfvkcti92d302j', NULL, 'sub_hs78qzpdzx1z44q2'),
(59960, 'pay_csvukjhrh1aa5sbt', 183, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:53:56', 'Desenvolvimento Web', 'BOLETO', NULL, 'https://www.asaas.com/i/csvukjhrh1aa5sbt', NULL, 'sub_0hdfdzqa53osh2ma'),
(59962, 'pay_bv1xt50721sv8w4v', 188, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:53:58', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'UNDEFINED', NULL, 'https://www.asaas.com/i/bv1xt50721sv8w4v', NULL, 'sub_sh9rki6ysvvln4kx'),
(59964, 'pay_n18qalwjulvaknf0', 206, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:53:59', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/n18qalwjulvaknf0', NULL, 'sub_b1fy6en75pgrzeoi'),
(59966, 'pay_mvqrexky1422ja0g', 209, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:01', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/mvqrexky1422ja0g', NULL, 'sub_sp6cmivnmdkdywlg'),
(59968, 'pay_9n02rag79jsu7shu', 235, 29.90, 'RECEIVED_IN_CASH', '2025-06-10', '2025-06-03', '2025-05-03 00:00:00', '2025-07-29 10:54:02', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/9n02rag79jsu7shu', NULL, 'sub_ijill2r7k1rrfo9s'),
(59970, 'pay_gtqomw6y8ooxs2mr', 199, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:04', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/gtqomw6y8ooxs2mr', NULL, 'sub_m3c69vzc322xsk1x'),
(59972, 'pay_sbik4u1lcp5r3f9x', 234, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:05', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/sbik4u1lcp5r3f9x', NULL, 'sub_5zhf56f9xgaopx3x'),
(59974, 'pay_0vsvevwvzna4zkf1', 224, 29.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-05-03 00:00:00', '2025-07-29 10:54:07', 'Plano de Hespedagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/0vsvevwvzna4zkf1', NULL, 'sub_906jjzs1wiwlv535'),
(59976, 'pay_o082lrihqk0od677', 227, 29.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-05-03 00:00:00', '2025-07-29 10:54:09', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/o082lrihqk0od677', NULL, 'sub_uof9cualj3j78zmc'),
(59978, 'pay_jevjogghq16hgiuo', 232, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:10', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/jevjogghq16hgiuo', NULL, 'sub_dhl2ubdq46degqsc'),
(59980, 'pay_rjfj4vuopl76dt6w', 283, 49.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:12', 'Plano Hospedagem Digital Plus', 'UNDEFINED', NULL, 'https://www.asaas.com/i/rjfj4vuopl76dt6w', NULL, 'sub_sgyxlte8bnaedtfj'),
(59982, 'pay_pc5bzhc7ywbvwnlq', 240, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-05-03 00:00:00', '2025-07-29 10:54:13', 'Plano ESSENCIAL Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/pc5bzhc7ywbvwnlq', NULL, 'sub_1p83yf64ebdz09eh');
INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(59983, 'pay_vqoya2f3f3li9ekq', 225, 29.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-05-03 00:00:00', '2025-07-29 10:54:15', 'Plano Essencial Hosp+Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/vqoya2f3f3li9ekq', NULL, 'sub_onezkhx9uo06h1we'),
(59984, 'pay_k3a6ecbg4ym0s8h9', 263, 30.50, 'RECEIVED', '2025-06-10', '2025-06-12', '2025-05-03 00:00:00', '2025-07-29 10:54:16', '', 'PIX', NULL, 'https://www.asaas.com/i/k3a6ecbg4ym0s8h9', NULL, 'sub_re7f5cvc2t4u6fuo'),
(59985, 'pay_d7m9zuu9pulx8beq', 215, 30.55, 'RECEIVED', '2025-05-10', '2025-05-17', '2025-05-02 00:00:00', '2025-07-29 10:54:18', 'Referente plano de hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/d7m9zuu9pulx8beq', NULL, 'sub_7ixl6dcfy7cwqpcf'),
(59987, 'pay_p6hvrb8i6ie9vpi5', 215, 30.00, 'RECEIVED', '2025-04-30', '2025-04-30', '2025-04-30 00:00:00', '2025-07-29 10:54:20', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/p6hvrb8i6ie9vpi5', NULL, NULL),
(59989, 'pay_ypop4nwhraduo6ww', 154, 60.00, 'RECEIVED', '2025-04-29', '2025-04-29', '2025-04-29 00:00:00', '2025-07-29 10:54:21', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ypop4nwhraduo6ww', NULL, NULL),
(59991, 'pay_fbcg5k9z2g61cp3i', 279, 49.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-04-27 00:00:00', '2025-07-29 10:54:23', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/fbcg5k9z2g61cp3i', NULL, 'sub_htfllf6l0cu6s4qk'),
(59993, 'pay_91iu6x0glmvostbp', 173, 130.00, 'RECEIVED', '2025-04-24', '2025-04-24', '2025-04-24 00:00:00', '2025-07-29 10:54:25', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/91iu6x0glmvostbp', NULL, NULL),
(59995, 'pay_4t7v1nv0qezkdj8p', 246, 2000.00, 'RECEIVED', '2025-04-23', '2025-04-23', '2025-04-23 00:00:00', '2025-07-29 10:54:26', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/4t7v1nv0qezkdj8p', NULL, NULL),
(60002, 'pay_arihdtjwl4qgyj7x', 237, 60.00, 'RECEIVED', '2025-04-22', '2025-04-22', '2025-04-22 00:00:00', '2025-07-29 10:54:30', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/arihdtjwl4qgyj7x', NULL, NULL),
(60004, 'pay_irj2qsi1xmd22ij8', 169, 29.90, 'OVERDUE', '2025-05-30', NULL, '2025-04-21 00:00:00', '2025-07-29 10:54:31', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/irj2qsi1xmd22ij8', NULL, 'sub_bebghk37yr7kaykc'),
(60006, 'pay_xf3924zosgc12pru', 185, 49.90, 'OVERDUE', '2025-05-29', NULL, '2025-04-20 00:00:00', '2025-07-29 10:54:33', 'Plano ImobSites ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/xf3924zosgc12pru', NULL, 'sub_0u0hiabbslpiov8l'),
(60009, 'pay_qb49rp9puz2szgjl', 220, 29.90, 'RECEIVED', '2025-05-29', '2025-05-02', '2025-04-20 00:00:00', '2025-07-29 10:54:35', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/qb49rp9puz2szgjl', NULL, 'sub_0ob5gqdk6bgm9evj'),
(60011, 'pay_ldox8pyhm4b5vnko', 267, 29.00, 'RECEIVED_IN_CASH', '2025-05-29', '2025-07-08', '2025-04-20 00:00:00', '2025-07-29 10:54:36', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ldox8pyhm4b5vnko', NULL, 'sub_504ksi201k6033es'),
(60013, 'pay_qzu6ap76zwk386ym', 221, 29.90, 'OVERDUE', '2025-05-28', NULL, '2025-04-19 00:00:00', '2025-07-29 10:54:38', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/qzu6ap76zwk386ym', NULL, 'sub_xahjz6ws9fumwnl9'),
(60015, 'pay_gp6xewi73xzg3xln', 236, 29.90, 'RECEIVED_IN_CASH', '2025-05-28', '2025-05-12', '2025-04-19 00:00:00', '2025-07-29 10:54:39', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/gp6xewi73xzg3xln', NULL, 'sub_bwvnv3t5548q79az'),
(60017, 'pay_z3r33n233m6w69f9', 155, 120.00, 'RECEIVED', '2025-04-16', '2025-04-16', '2025-04-16 00:00:00', '2025-07-29 10:54:41', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/z3r33n233m6w69f9', NULL, NULL),
(60019, 'pay_apnh4r3qb5ptzprj', 170, 200.00, 'RECEIVED', '2025-04-16', '2025-04-16', '2025-04-16 00:00:00', '2025-07-29 10:54:43', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/apnh4r3qb5ptzprj', NULL, NULL),
(60021, 'pay_so42qtm6lfiz7f62', 214, 400.00, 'RECEIVED', '2025-06-16', '2025-07-21', '2025-04-16 00:00:00', '2025-07-29 07:56:25', 'Parcela 3 de 3. Ref. Desenvolvimento WEB site: sunblank.com/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/so42qtm6lfiz7f62', '3', NULL),
(60023, 'pay_24ua3lj63m4jilhb', 214, 400.00, 'RECEIVED', '2025-05-16', '2025-06-20', '2025-04-16 00:00:00', '2025-07-29 07:56:25', 'Parcela 2 de 3. Ref. Desenvolvimento WEB site: sunblank.com/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/24ua3lj63m4jilhb', '2', NULL),
(60025, 'pay_22y1i0xll239zubw', 214, 400.00, 'RECEIVED', '2025-04-16', '2025-05-19', '2025-04-16 00:00:00', '2025-07-29 07:56:25', 'Parcela 1 de 3. Ref. Desenvolvimento WEB site: sunblank.com/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/22y1i0xll239zubw', '1', NULL),
(60027, 'pay_1g6t0gpd275461qc', 167, 29.90, 'PENDING', '2026-04-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:49', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/1g6t0gpd275461qc', NULL, 'sub_91lyl83tk9s9f4wn'),
(60029, 'pay_y62163vc5uo6u321', 167, 29.90, 'PENDING', '2026-02-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:50', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/y62163vc5uo6u321', NULL, 'sub_91lyl83tk9s9f4wn'),
(60031, 'pay_jdvbmef123t9u07p', 167, 29.90, 'PENDING', '2026-01-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:52', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/jdvbmef123t9u07p', NULL, 'sub_91lyl83tk9s9f4wn'),
(60033, 'pay_e8xfqvugeppl9ks1', 167, 29.90, 'PENDING', '2025-12-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:54', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/e8xfqvugeppl9ks1', NULL, 'sub_91lyl83tk9s9f4wn'),
(60035, 'pay_d10bg1de8vl0ntnk', 167, 29.90, 'PENDING', '2025-11-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:55', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/d10bg1de8vl0ntnk', NULL, 'sub_91lyl83tk9s9f4wn'),
(60037, 'pay_j8ai8xttjaycd3gw', 167, 29.90, 'PENDING', '2025-10-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:57', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/j8ai8xttjaycd3gw', NULL, 'sub_91lyl83tk9s9f4wn'),
(60039, 'pay_rn1pxp2lp19mws7g', 167, 29.90, 'PENDING', '2025-09-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:54:58', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/rn1pxp2lp19mws7g', NULL, 'sub_91lyl83tk9s9f4wn'),
(60041, 'pay_d1niejnyr0o9chrv', 167, 29.90, 'PENDING', '2025-08-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:55:00', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/d1niejnyr0o9chrv', NULL, 'sub_91lyl83tk9s9f4wn'),
(60043, 'pay_wfufg2t4pv0a50j1', 167, 29.90, 'OVERDUE', '2025-07-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:55:01', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/wfufg2t4pv0a50j1', NULL, 'sub_91lyl83tk9s9f4wn'),
(60045, 'pay_e7vw5l99eqjxlh7v', 167, 29.90, 'OVERDUE', '2025-06-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:55:03', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/e7vw5l99eqjxlh7v', NULL, 'sub_91lyl83tk9s9f4wn'),
(60047, 'pay_f2bhqkkbjqjl5qp6', 167, 29.90, 'OVERDUE', '2025-05-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:55:05', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/f2bhqkkbjqjl5qp6', NULL, 'sub_91lyl83tk9s9f4wn'),
(60049, 'pay_jol6ntxxp6g4l4qn', 174, 29.90, 'OVERDUE', '2025-05-25', NULL, '2025-04-16 00:00:00', '2025-07-29 10:55:06', 'Plano Mensal Hosp. + Manutenção E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/jol6ntxxp6g4l4qn', NULL, 'sub_3scto700oy8oqo8m'),
(60051, 'pay_qvsjdl76ufwdryib', 175, 30.65, 'RECEIVED', '2025-05-25', '2025-06-11', '2025-04-16 00:00:00', '2025-07-29 10:55:08', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/qvsjdl76ufwdryib', NULL, 'sub_fgev4t35r9zvziov'),
(60053, 'pay_09h210qsjcfnfahg', 245, 29.90, 'RECEIVED', '2025-05-25', '2025-05-26', '2025-04-16 00:00:00', '2025-07-29 10:55:09', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/09h210qsjcfnfahg', NULL, 'sub_f2zgsu6ds7fskv75'),
(60055, 'pay_arlzel7br0mgkpv3', 284, 29.90, 'RECEIVED', '2025-05-25', '2025-05-26', '2025-04-16 00:00:00', '2025-07-29 10:55:11', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/arlzel7br0mgkpv3', NULL, 'sub_khpfl1dpc6pfj9ix'),
(60057, 'pay_t05apo7zo4rsxt7x', 202, 29.90, 'OVERDUE', '2025-05-23', NULL, '2025-04-14 00:00:00', '2025-07-29 10:55:12', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/t05apo7zo4rsxt7x', NULL, 'sub_5taox270k3eejldi'),
(60059, 'pay_2i0ru0v21yn4kd45', 271, 39.90, 'OVERDUE', '2025-05-23', NULL, '2025-04-14 00:00:00', '2025-07-29 10:55:14', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2i0ru0v21yn4kd45', NULL, 'sub_1dx95biw49q1zskp'),
(60061, 'pay_zivcrt5dhmzitxl7', 251, 29.90, 'OVERDUE', '2025-05-21', NULL, '2025-04-12 00:00:00', '2025-07-29 10:55:15', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/zivcrt5dhmzitxl7', NULL, 'sub_fwc0baa1kthqsncu'),
(60063, 'pay_csj9mwqtwq55zta6', 283, 49.90, 'RECEIVED_IN_CASH', '2025-05-10', '2025-05-14', '2025-04-11 00:00:00', '2025-07-29 10:55:17', 'Plano Hospedagem Digital Plus', 'UNDEFINED', NULL, 'https://www.asaas.com/i/csj9mwqtwq55zta6', NULL, 'sub_sgyxlte8bnaedtfj'),
(60065, 'pay_d9vjra71ns34fn6n', 245, 100.00, 'RECEIVED_IN_CASH', '2025-04-25', '2025-04-25', '2025-04-11 00:00:00', '2025-07-29 10:55:19', 'Desenvolvimento Página Adicional no Site', 'BOLETO', NULL, 'https://www.asaas.com/i/d9vjra71ns34fn6n', NULL, NULL),
(60067, 'pay_g10mh3q7mkcjgexe', 245, 325.00, 'OVERDUE', '2025-06-25', NULL, '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 3 de 3. Gestão de Tráfego Google ADS', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/g10mh3q7mkcjgexe', '3', NULL),
(60069, 'pay_74miih7dyhdesw17', 245, 332.79, 'RECEIVED', '2025-05-14', '2025-05-26', '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 2 de 3. Gestão de Tráfego Google ADS', 'PIX', 'PIX', 'https://www.asaas.com/i/74miih7dyhdesw17', '2', NULL),
(60071, 'pay_sl47bfv1ak80dz4s', 245, 350.00, 'RECEIVED_IN_CASH', '2025-04-14', '2025-04-11', '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 1 de 3. Gestão de Tráfego Google ADS', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/sl47bfv1ak80dz4s', '1', NULL),
(60073, 'pay_zc380m2gy2ulntgs', 157, 119.60, 'RECEIVED', '2025-04-11', '2025-04-11', '2025-04-11 00:00:00', '2025-07-29 10:55:25', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/zc380m2gy2ulntgs', NULL, NULL),
(60075, 'pay_z2nf7sw03tuff1xe', 283, 665.00, 'OVERDUE', '2025-07-15', NULL, '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 3 de 3. Criação e Desenvolvimento WEB: sgjuridico.com.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/z2nf7sw03tuff1xe', '3', NULL),
(60077, 'pay_vab9olu8tfyidsf2', 283, 665.00, 'OVERDUE', '2025-06-10', NULL, '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 2 de 3. Criação e Desenvolvimento WEB: sgjuridico.com.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/vab9olu8tfyidsf2', '2', NULL),
(60079, 'pay_sqhrw7g8znbl1lf6', 283, 665.00, 'RECEIVED_IN_CASH', '2025-05-10', '2025-05-14', '2025-04-11 00:00:00', '2025-07-29 07:56:25', 'Parcela 1 de 3. Criação e Desenvolvimento WEB: sgjuridico.com.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/sqhrw7g8znbl1lf6', '1', NULL),
(60081, 'pay_spfl7oo9zxs8esta', 173, 350.00, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-04-10 00:00:00', '2025-07-29 10:55:31', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/spfl7oo9zxs8esta', NULL, NULL),
(60083, 'pay_0e1llm3li4vmo74n', 158, 29.90, 'PENDING', '2026-04-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:33', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/0e1llm3li4vmo74n', NULL, 'sub_uy8h6yiarye61wcd'),
(60085, 'pay_xja3mdfx5irmt8tu', 158, 29.90, 'PENDING', '2026-03-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:34', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/xja3mdfx5irmt8tu', NULL, 'sub_uy8h6yiarye61wcd'),
(60087, 'pay_7dy54qso5b0t518k', 158, 29.90, 'PENDING', '2026-02-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:36', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/7dy54qso5b0t518k', NULL, 'sub_uy8h6yiarye61wcd'),
(60089, 'pay_wxgc18t9pjhyhwnn', 158, 29.90, 'PENDING', '2026-01-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:37', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/wxgc18t9pjhyhwnn', NULL, 'sub_uy8h6yiarye61wcd'),
(60091, 'pay_3s1n71lhkin6xk3q', 158, 29.90, 'PENDING', '2025-12-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:39', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/3s1n71lhkin6xk3q', NULL, 'sub_uy8h6yiarye61wcd'),
(60093, 'pay_6oqk6ct45z81o9rl', 158, 29.90, 'PENDING', '2025-11-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:40', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/6oqk6ct45z81o9rl', NULL, 'sub_uy8h6yiarye61wcd'),
(60095, 'pay_oc3jtz2taxvdoi3f', 158, 29.90, 'PENDING', '2025-10-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:42', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/oc3jtz2taxvdoi3f', NULL, 'sub_uy8h6yiarye61wcd'),
(60097, 'pay_w47uclzkt7b48470', 158, 29.90, 'PENDING', '2025-09-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:44', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/w47uclzkt7b48470', NULL, 'sub_uy8h6yiarye61wcd'),
(60099, 'pay_y6guozc105ternl9', 158, 29.90, 'PENDING', '2025-08-10', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:45', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/y6guozc105ternl9', NULL, 'sub_uy8h6yiarye61wcd'),
(60101, 'pay_v8uttsght5ge9gts', 158, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-04-10 00:00:00', '2025-07-29 10:55:47', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/v8uttsght5ge9gts', NULL, 'sub_uy8h6yiarye61wcd'),
(60103, 'pay_ch3sm7l9p72xmk1p', 158, 29.90, 'RECEIVED_IN_CASH', '2025-06-10', '2025-06-24', '2025-04-10 00:00:00', '2025-07-29 10:55:48', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/ch3sm7l9p72xmk1p', NULL, 'sub_uy8h6yiarye61wcd'),
(60105, 'pay_sjujls1359wwcwo2', 158, 29.90, 'RECEIVED_IN_CASH', '2025-05-10', '2025-05-14', '2025-04-10 00:00:00', '2025-07-29 10:55:50', 'Plano de Hospedagem + E-mail Profissional ', 'BOLETO', NULL, 'https://www.asaas.com/i/sjujls1359wwcwo2', NULL, 'sub_uy8h6yiarye61wcd'),
(60107, 'pay_k18jfrsk5pvu93ma', 158, 116.20, 'CONFIRMED', '2025-09-10', NULL, '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 6 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/k18jfrsk5pvu93ma', '6', NULL),
(60109, 'pay_qw6kpjyrgjta47f5', 158, 116.16, 'CONFIRMED', '2025-08-10', NULL, '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 5 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/qw6kpjyrgjta47f5', '5', NULL),
(60111, 'pay_o9mf04htg57prqcs', 158, 116.16, 'CONFIRMED', '2025-07-10', NULL, '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 4 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/o9mf04htg57prqcs', '4', NULL),
(60113, 'pay_1fhmi7drkrpjlbdd', 158, 116.16, 'RECEIVED', '2025-06-10', '2025-07-15', '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 3 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1fhmi7drkrpjlbdd', '3', NULL),
(60115, 'pay_nnb5ndopsvszunnp', 158, 116.16, 'RECEIVED', '2025-05-10', '2025-06-13', '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 2 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/nnb5ndopsvszunnp', '2', NULL),
(60117, 'pay_en9wvhnzp3mdx8tl', 158, 116.16, 'RECEIVED', '2025-04-10', '2025-05-12', '2025-04-10 00:00:00', '2025-07-29 07:56:25', 'Parcela 1 de 6. Criação e Desenvolvimento WebSite Institucional KeyStone', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/en9wvhnzp3mdx8tl', '1', NULL),
(60119, 'pay_fbd1ewqinc9kqtfa', 158, 31.29, 'RECEIVED', '2025-04-10', '2025-06-30', '2025-04-10 00:00:00', '2025-07-29 10:56:01', 'Plano de Hospedagem + E-mail Profissional ', 'PIX', NULL, 'https://www.asaas.com/i/fbd1ewqinc9kqtfa', NULL, 'sub_uy8h6yiarye61wcd'),
(60121, 'pay_qaczysj951ahvyzr', 260, 165.66, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-04-10 00:00:00', '2025-07-29 10:56:02', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/qaczysj951ahvyzr', NULL, NULL),
(60123, 'pay_ozltxcwx5e08o9mv', 252, 226.36, 'DUNNING_REQUESTED', '2025-04-10', NULL, '2025-04-10 00:00:00', '2025-07-28 14:49:49', 'Referente às mensalidades vencidas dos meses 09/2024 a 03/2025, totalizando 7 parcelas em aberto. Conforme cláusulas contratuais, após o vencimento aplica-se 2% de multa e 1% de juros ao mês. Este boleto unifica todos os débitos atualizados até a data de ', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ozltxcwx5e08o9mv', '', NULL),
(60125, 'pay_v28wu4vxbkjq04wh', 258, 120.00, 'RECEIVED', '2025-04-09', '2025-04-09', '2025-04-09 00:00:00', '2025-07-29 10:56:05', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/v28wu4vxbkjq04wh', NULL, NULL),
(60127, 'pay_aq14vpxdcno6jk8l', 234, 438.40, 'RECEIVED', '2025-04-08', '2025-04-08', '2025-04-08 00:00:00', '2025-07-29 10:56:07', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/aq14vpxdcno6jk8l', NULL, NULL),
(60129, 'pay_4ub6ecnmoaj5si61', 180, 436.46, 'DUNNING_REQUESTED', '2025-04-08', NULL, '2025-04-08 00:00:00', '2025-07-29 10:56:09', 'Referente aos serviços prestados – Plano de Hospedagem e Criação de Site. Vencimentos em aberto: 05/03, 05/04 e 05/05, totalizando R$ 436,46. Caso não seja identificado o pagamento, este título será protestado em 5 dias úteis.', 'BOLETO', NULL, 'https://www.asaas.com/i/4ub6ecnmoaj5si61', NULL, NULL),
(60131, 'pay_gz3go7k47hlqlbgr', 159, 89.70, 'RECEIVED', '2025-04-08', '2025-04-08', '2025-04-08 00:00:00', '2025-07-29 10:56:10', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/gz3go7k47hlqlbgr', NULL, NULL),
(60133, 'pay_govxxv4lp23pbpvv', 190, 29.90, 'OVERDUE', '2025-05-17', NULL, '2025-04-08 00:00:00', '2025-07-29 10:56:12', 'Plano de Hospedagem e Manutenção Essencial', 'UNDEFINED', NULL, 'https://www.asaas.com/i/govxxv4lp23pbpvv', NULL, 'sub_g3gra67j24pjeh52'),
(60135, 'pay_2n89118573dfe5no', 205, 29.90, 'OVERDUE', '2025-05-17', NULL, '2025-04-08 00:00:00', '2025-07-29 10:56:13', 'Plano de Hospedagem e Manutenção Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/2n89118573dfe5no', NULL, 'sub_nlzsju9rjj93dm4f'),
(60137, 'pay_0b8s8cgz83au5d5g', 235, 29.90, 'RECEIVED', '2025-04-07', '2025-04-07', '2025-04-07 00:00:00', '2025-07-29 10:56:15', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: pagamento site do fuga da cidade', 'PIX', NULL, 'https://www.asaas.com/i/0b8s8cgz83au5d5g', NULL, NULL),
(60139, 'pay_xul4w7z8zrx9veyk', 196, 29.90, 'RECEIVED', '2025-05-15', '2025-05-15', '2025-04-06 00:00:00', '2025-07-29 10:56:16', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/xul4w7z8zrx9veyk', NULL, 'sub_lul4v892i4z7gsnz'),
(60141, 'pay_z3lzqhzrnhxlw32o', 254, 30.53, 'RECEIVED', '2025-05-15', '2025-05-20', '2025-04-06 00:00:00', '2025-07-29 10:56:18', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/z3lzqhzrnhxlw32o', NULL, 'sub_oil9ich37nuhoes7'),
(60143, 'pay_tnpo6d10lf2wvvth', 286, 51.80, 'RECEIVED', '2025-05-15', '2025-05-26', '2025-04-06 00:00:00', '2025-07-29 10:56:19', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/tnpo6d10lf2wvvth', NULL, 'sub_fte624var188bxrz'),
(60145, 'pay_ans2jmkpbs9kto4p', 256, 29.90, 'OVERDUE', '2025-05-13', NULL, '2025-04-04 00:00:00', '2025-07-29 10:56:21', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/ans2jmkpbs9kto4p', NULL, 'sub_a9g0wq5bypr3lt2h'),
(60147, 'pay_o08ea7kguo82yj6p', 163, 29.90, 'OVERDUE', '2025-05-10', NULL, '2025-04-03 00:00:00', '2025-07-29 10:56:23', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/o08ea7kguo82yj6p', NULL, 'sub_za1sv4psb0cjv2h1'),
(60149, 'pay_bglz2v9f5mwranet', 164, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-04-03 00:00:00', '2025-07-29 10:56:24', 'Plano de Hospedagem e Manutenção ', 'BOLETO', NULL, 'https://www.asaas.com/i/bglz2v9f5mwranet', NULL, 'sub_drdk2zvb9ae8mole'),
(60151, 'pay_e3r5lv8rv4vzhrxw', 265, 30.51, 'RECEIVED', '2025-05-10', '2025-05-13', '2025-04-03 00:00:00', '2025-07-29 10:56:26', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/e3r5lv8rv4vzhrxw', NULL, 'sub_02etd71kzxkv2vyl'),
(60153, 'pay_0t57eo2ndhcttzvh', 266, 29.90, 'OVERDUE', '2025-05-10', NULL, '2025-04-03 00:00:00', '2025-07-29 10:56:27', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/0t57eo2ndhcttzvh', NULL, 'sub_hs78qzpdzx1z44q2'),
(60155, 'pay_7xtchljmp5g42vcr', 257, 29.90, 'OVERDUE', '2025-05-12', NULL, '2025-04-03 00:00:00', '2025-07-29 10:56:29', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/7xtchljmp5g42vcr', NULL, 'sub_ju5bkk8q06x0vid9'),
(60157, 'pay_vfd24cmlnzk4x5w0', 258, 39.90, 'OVERDUE', '2025-05-12', NULL, '2025-04-03 00:00:00', '2025-07-29 10:56:30', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/vfd24cmlnzk4x5w0', NULL, 'sub_feo8zvrsmaf5q1tq'),
(60159, 'pay_x15u3w7nthr9ly43', 264, 30.91, 'RECEIVED', '2025-05-12', '2025-06-24', '2025-04-03 00:00:00', '2025-07-29 10:56:32', '', 'PIX', NULL, 'https://www.asaas.com/i/x15u3w7nthr9ly43', NULL, 'sub_ohuo9f9u852d7wki'),
(60161, 'pay_jh5sx86j2lbug28a', 188, 30.64, 'RECEIVED', '2025-05-10', '2025-05-26', '2025-04-02 00:00:00', '2025-07-29 10:56:34', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'PIX', NULL, 'https://www.asaas.com/i/jh5sx86j2lbug28a', NULL, 'sub_sh9rki6ysvvln4kx'),
(60163, 'pay_rn3cqv0c0oh0h9jt', 206, 30.51, 'RECEIVED', '2025-05-10', '2025-05-13', '2025-04-02 00:00:00', '2025-07-29 10:56:35', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/rn3cqv0c0oh0h9jt', NULL, 'sub_b1fy6en75pgrzeoi'),
(60165, 'pay_oro7q6i91odhlzt8', 209, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-04-02 00:00:00', '2025-07-29 10:56:37', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/oro7q6i91odhlzt8', NULL, 'sub_sp6cmivnmdkdywlg'),
(60167, 'pay_fxy8xjkw3h6vsf0e', 217, 29.90, 'OVERDUE', '2025-05-10', NULL, '2025-04-02 00:00:00', '2025-07-29 10:56:38', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/fxy8xjkw3h6vsf0e', NULL, 'sub_mlmp5a0sid5xyb5n'),
(60169, 'pay_njawvkolyb2kvdny', 235, 29.90, 'RECEIVED_IN_CASH', '2025-05-10', '2025-05-08', '2025-04-02 00:00:00', '2025-07-29 10:56:40', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/njawvkolyb2kvdny', NULL, 'sub_ijill2r7k1rrfo9s'),
(60171, 'pay_6vqmzc0t3vgf7fag', 199, 29.90, 'OVERDUE', '2025-05-10', NULL, '2025-04-02 00:00:00', '2025-07-29 10:56:41', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/6vqmzc0t3vgf7fag', NULL, 'sub_m3c69vzc322xsk1x'),
(60173, 'pay_z41ppcaz4mt9ilhv', 234, 29.90, 'RECEIVED_IN_CASH', '2025-05-10', '2025-05-09', '2025-04-02 00:00:00', '2025-07-29 10:56:43', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/z41ppcaz4mt9ilhv', NULL, 'sub_5zhf56f9xgaopx3x'),
(60175, 'pay_xq3ar3lj964g4haa', 224, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-04-02 00:00:00', '2025-07-29 10:56:44', 'Plano de Hespedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/xq3ar3lj964g4haa', NULL, 'sub_906jjzs1wiwlv535'),
(60177, 'pay_o6lbaqqxzlsogbkb', 227, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-04-02 00:00:00', '2025-07-29 10:56:46', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'PIX', NULL, 'https://www.asaas.com/i/o6lbaqqxzlsogbkb', NULL, 'sub_uof9cualj3j78zmc'),
(60179, 'pay_li1eo8gefavav29g', 232, 30.51, 'RECEIVED', '2025-05-10', '2025-05-13', '2025-04-02 00:00:00', '2025-07-29 10:56:48', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'PIX', NULL, 'https://www.asaas.com/i/li1eo8gefavav29g', NULL, 'sub_dhl2ubdq46degqsc'),
(60180, 'pay_7p2m0yabxmspxojw', 240, 29.90, 'OVERDUE', '2025-05-10', NULL, '2025-04-02 00:00:00', '2025-07-29 10:56:49', 'Plano ESSENCIAL Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/7p2m0yabxmspxojw', NULL, 'sub_1p83yf64ebdz09eh'),
(60181, 'pay_vexidfp4em2dsrks', 225, 29.90, 'RECEIVED', '2025-05-10', '2025-05-10', '2025-04-02 00:00:00', '2025-07-29 10:56:51', 'Plano Essencial Hosp+Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/vexidfp4em2dsrks', NULL, 'sub_onezkhx9uo06h1we'),
(60182, 'pay_7mo3alyyqvszzxzt', 160, 250.00, 'OVERDUE', '2025-07-30', NULL, '2025-04-02 00:00:00', '2025-07-28 14:49:49', 'Parcela 4 de 4. Este boleto refere-se ao serviço de Consultoria em Gestão de Tráfego, contratado junto à  Pixel12Digital conforme acordado entre as partes.\r\nO pagamento deve ser realizado até a data de vencimento para garantir a realização dos serviços.\r\n', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/7mo3alyyqvszzxzt', '4', NULL),
(60184, 'pay_ezh6mg58os6hfyxo', 160, 250.00, 'OVERDUE', '2025-06-30', NULL, '2025-04-02 00:00:00', '2025-07-28 14:49:49', 'Parcela 3 de 4. Este boleto refere-se ao serviço de Consultoria em Gestão de Tráfego, contratado junto à  Pixel12Digital conforme acordado entre as partes.\r\nO pagamento deve ser realizado até a data de vencimento para garantir a realização dos serviços.\r\n', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ezh6mg58os6hfyxo', '3', NULL),
(60186, 'pay_95ir7zsriwuju3k4', 160, 250.00, 'RECEIVED', '2025-05-30', '2025-05-12', '2025-04-02 00:00:00', '2025-07-28 14:49:49', 'Parcela 2 de 4. Este boleto refere-se ao serviço de Consultoria em Gestão de Tráfego, contratado junto à  Pixel12Digital conforme acordado entre as partes.\r\nO pagamento deve ser realizado até a data de vencimento para garantir a realização dos serviços.\r\n', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/95ir7zsriwuju3k4', '2', NULL),
(60188, 'pay_cqyvvmse21pwnlqs', 160, 250.00, 'RECEIVED', '2025-04-30', '2025-04-02', '2025-04-02 00:00:00', '2025-07-28 14:49:49', 'Parcela 1 de 4. Este boleto refere-se ao serviço de Consultoria em Gestão de Tráfego, contratado junto à  Pixel12Digital conforme acordado entre as partes.\r\nO pagamento deve ser realizado até a data de vencimento para garantir a realização dos serviços.\r\n', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/cqyvvmse21pwnlqs', '1', NULL),
(60190, 'pay_3x26v6qvec71bs1d', 161, 160.00, 'RECEIVED', '2025-04-02', '2025-04-02', '2025-04-02 00:00:00', '2025-07-29 10:56:59', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/3x26v6qvec71bs1d', NULL, NULL),
(60192, 'pay_dc6r9k7zgihlkn0u', 263, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-04-02 00:00:00', '2025-07-29 10:57:00', '', 'PIX', NULL, 'https://www.asaas.com/i/dc6r9k7zgihlkn0u', NULL, 'sub_re7f5cvc2t4u6fuo'),
(60194, 'pay_njolhxp287neg0yh', 162, 199.00, 'RECEIVED', '2025-03-31', '2025-03-31', '2025-03-31 00:00:00', '2025-07-29 10:57:02', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/njolhxp287neg0yh', NULL, NULL),
(60196, 'pay_rxzp6zqjfyd5mu2g', 264, 149.90, 'RECEIVED', '2025-03-31', '2025-03-31', '2025-03-31 00:00:00', '2025-07-29 10:57:03', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/rxzp6zqjfyd5mu2g', NULL, NULL),
(60198, 'pay_ru1oyxwhyc0ll1s1', 169, 1186.00, 'DUNNING_REQUESTED', '2025-03-28', NULL, '2025-03-28 00:00:00', '2025-07-29 10:57:05', 'Nota Fiscal nº 68\r\nData de emissão: 07/03/2025\r\nServiço: Criação e Desenvolvimento WEB\r\n\r\nApós 5 dias do vencimento, o título será encaminhado para negativação automática nos órgãos de proteção ao crédito.', 'BOLETO', NULL, 'https://www.asaas.com/i/ru1oyxwhyc0ll1s1', NULL, NULL),
(60203, 'pay_bjgcixbygq0oaaml', 189, 597.00, 'DUNNING_REQUESTED', '2025-03-27', NULL, '2025-03-27 00:00:00', '2025-07-29 10:57:09', 'Referente  NF 53 Emitida em 17/01/2025 17:34:51. ', 'BOLETO', NULL, 'https://www.asaas.com/i/bjgcixbygq0oaaml', NULL, NULL),
(60205, 'pay_s7x7y8ih7mv92phf', 254, 149.90, 'RECEIVED', '2025-03-27', '2025-03-27', '2025-03-27 00:00:00', '2025-07-29 10:57:11', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/s7x7y8ih7mv92phf', NULL, NULL),
(60207, 'pay_v6b2hfyb2d0mpntp', 163, 348.50, 'RECEIVED', '2025-04-30', '2025-03-31', '2025-03-27 00:00:00', '2025-07-29 07:56:27', 'Parcela 2 de 2. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/v6b2hfyb2d0mpntp', '2', NULL),
(60209, 'pay_ky9u7zg0rvucv3mw', 163, 348.50, 'OVERDUE', '2025-04-30', NULL, '2025-03-27 00:00:00', '2025-07-29 07:56:27', 'Parcela 1 de 2. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ky9u7zg0rvucv3mw', '1', NULL),
(60211, 'pay_0nw2c7n7w694a1d7', 279, 49.90, 'RECEIVED', '2025-05-09', '2025-05-08', '2025-03-27 00:00:00', '2025-07-29 10:57:16', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/0nw2c7n7w694a1d7', NULL, 'sub_htfllf6l0cu6s4qk'),
(60213, 'pay_3u6xvbgznbanw1t6', 187, 238.80, 'OVERDUE', '2025-04-25', NULL, '2025-03-26 00:00:00', '2025-07-29 10:57:17', 'Plano de Hospedagem Anual', 'BOLETO', NULL, 'https://www.asaas.com/i/3u6xvbgznbanw1t6', NULL, NULL),
(60215, 'pay_uypsp30ir3yuaccu', 201, 29.90, 'RECEIVED', '2025-05-10', '2025-05-12', '2025-03-25 00:00:00', '2025-07-29 10:57:19', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/uypsp30ir3yuaccu', NULL, 'sub_haj4picrjjyojxcs'),
(60217, 'pay_dwvwfwx5r6l72zxc', 164, 395.34, 'RECEIVED', '2025-05-10', '2025-05-12', '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/dwvwfwx5r6l72zxc', '3', NULL),
(60219, 'pay_odx4gbiabmso4574', 164, 395.33, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/odx4gbiabmso4574', '2', NULL),
(60221, 'pay_uhl0z40f9jjwlrer', 164, 395.33, 'RECEIVED', '2025-03-25', '2025-03-25', '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/uhl0z40f9jjwlrer', '1', NULL),
(60223, 'pay_43z88d30p4j2znz2', 245, 30.00, 'RECEIVED', '2025-03-24', '2025-03-24', '2025-03-24 00:00:00', '2025-07-29 10:57:25', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/43z88d30p4j2znz2', NULL, NULL),
(60225, 'pay_3fcr4cndyyizk58i', 188, 232.34, 'OVERDUE', '2025-05-24', NULL, '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/3fcr4cndyyizk58i', '3', NULL),
(60227, 'pay_evaxupie6r6o5t3p', 188, 232.33, 'RECEIVED', '2025-04-24', '2025-05-15', '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/evaxupie6r6o5t3p', '2', NULL),
(60229, 'pay_8xb0jdilkgqdjcs5', 188, 232.33, 'RECEIVED', '2025-03-24', '2025-03-25', '2025-03-24 00:00:00', '2025-07-29 07:56:27', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/8xb0jdilkgqdjcs5', '1', NULL),
(60231, 'pay_36dfc9yb00s4gpca', 169, 29.90, 'OVERDUE', '2025-04-30', NULL, '2025-03-22 00:00:00', '2025-07-29 10:57:32', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/36dfc9yb00s4gpca', NULL, 'sub_bebghk37yr7kaykc'),
(60233, 'pay_91xicb0r6omao5go', 165, 300.00, 'RECEIVED', '2025-03-21', '2025-03-21', '2025-03-21 00:00:00', '2025-07-29 10:57:33', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/91xicb0r6omao5go', NULL, NULL),
(60235, 'pay_z9ircc6lrsrbi5vb', 219, 29.90, 'PENDING', '2025-12-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:35', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/z9ircc6lrsrbi5vb', NULL, 'sub_z70tugf89a57i6to'),
(60237, 'pay_ehmb365xwel4ujuf', 219, 29.90, 'PENDING', '2025-11-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:36', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ehmb365xwel4ujuf', NULL, 'sub_z70tugf89a57i6to'),
(60239, 'pay_2uy3ondq9ao52xad', 219, 29.90, 'PENDING', '2025-10-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:38', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2uy3ondq9ao52xad', NULL, 'sub_z70tugf89a57i6to'),
(60241, 'pay_yna68u3l3i20bni1', 219, 29.90, 'PENDING', '2025-09-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:39', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/yna68u3l3i20bni1', NULL, 'sub_z70tugf89a57i6to'),
(60243, 'pay_uqt81xkog4x7ym3w', 219, 29.90, 'PENDING', '2025-08-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:41', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/uqt81xkog4x7ym3w', NULL, 'sub_z70tugf89a57i6to'),
(60245, 'pay_835yym8y4nf3ebgs', 219, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:43', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/835yym8y4nf3ebgs', NULL, 'sub_z70tugf89a57i6to'),
(60247, 'pay_4uq3gbsaf99na7e8', 219, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:44', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4uq3gbsaf99na7e8', NULL, 'sub_z70tugf89a57i6to'),
(60249, 'pay_1ln5pe1f64x5atx7', 219, 29.90, 'RECEIVED', '2025-05-10', '2025-05-07', '2025-03-21 00:00:00', '2025-07-29 10:57:46', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/1ln5pe1f64x5atx7', NULL, 'sub_z70tugf89a57i6to'),
(60251, 'pay_37vio7n4rmor8nsq', 166, 165.55, 'RECEIVED', '2025-03-21', '2025-03-21', '2025-03-21 00:00:00', '2025-07-29 10:57:47', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/37vio7n4rmor8nsq', NULL, NULL),
(60253, 'pay_83tf6wdxulchdb2d', 185, 49.90, 'OVERDUE', '2025-04-29', NULL, '2025-03-21 00:00:00', '2025-07-29 10:57:49', 'Plano ImobSites ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/83tf6wdxulchdb2d', NULL, 'sub_0u0hiabbslpiov8l'),
(60255, 'pay_5srhuls42dto9cex', 220, 29.90, 'RECEIVED', '2025-04-29', '2025-03-31', '2025-03-21 00:00:00', '2025-07-29 10:57:50', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/5srhuls42dto9cex', NULL, 'sub_0ob5gqdk6bgm9evj'),
(60257, 'pay_2v3ez2j6vv6k8gy6', 267, 29.00, 'RECEIVED_IN_CASH', '2025-04-29', '2025-04-29', '2025-03-21 00:00:00', '2025-07-29 10:57:52', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2v3ez2j6vv6k8gy6', NULL, 'sub_504ksi201k6033es'),
(60259, 'pay_ykq6no3ax3l06yy9', 167, 395.34, 'OVERDUE', '2025-05-21', NULL, '2025-03-20 00:00:00', '2025-07-29 07:56:27', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ykq6no3ax3l06yy9', '3', NULL),
(60261, 'pay_rwk8e254q74i0x44', 167, 395.33, 'OVERDUE', '2025-04-21', NULL, '2025-03-20 00:00:00', '2025-07-29 07:56:27', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/rwk8e254q74i0x44', '2', NULL),
(60263, 'pay_nrp9pd3ay21knr4n', 167, 395.33, 'RECEIVED', '2025-03-21', '2025-03-21', '2025-03-20 00:00:00', '2025-07-29 07:56:27', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/nrp9pd3ay21knr4n', '1', NULL),
(60265, 'pay_tebut47bp7xhwyco', 221, 29.90, 'OVERDUE', '2025-04-28', NULL, '2025-03-20 00:00:00', '2025-07-29 10:57:58', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/tebut47bp7xhwyco', NULL, 'sub_xahjz6ws9fumwnl9'),
(60267, 'pay_4cstucoujfhtunw1', 236, 29.90, 'RECEIVED_IN_CASH', '2025-04-28', '2025-05-12', '2025-03-20 00:00:00', '2025-07-29 10:58:00', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/4cstucoujfhtunw1', NULL, 'sub_bwvnv3t5548q79az'),
(60269, 'pay_o9qu8hy53c6u1gkr', 191, 29.90, 'PENDING', '2025-12-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:01', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/o9qu8hy53c6u1gkr', NULL, 'sub_sulll48m6obfa7ze'),
(60271, 'pay_ise711vkgeunhhkm', 191, 29.90, 'PENDING', '2025-11-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:03', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ise711vkgeunhhkm', NULL, 'sub_sulll48m6obfa7ze'),
(60273, 'pay_75n48g4cw2e640df', 191, 29.90, 'PENDING', '2025-10-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:04', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/75n48g4cw2e640df', NULL, 'sub_sulll48m6obfa7ze'),
(60275, 'pay_d7mtvn7z868hicr4', 191, 29.90, 'PENDING', '2025-09-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:06', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/d7mtvn7z868hicr4', NULL, 'sub_sulll48m6obfa7ze'),
(60277, 'pay_z5krbiwpunn71yyl', 191, 29.90, 'PENDING', '2025-08-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:08', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/z5krbiwpunn71yyl', NULL, 'sub_sulll48m6obfa7ze'),
(60279, 'pay_nz6kcsgw60es07w6', 191, 29.90, 'OVERDUE', '2025-07-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:09', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/nz6kcsgw60es07w6', NULL, 'sub_sulll48m6obfa7ze'),
(60281, 'pay_zboiw7a7wr4sdjxi', 191, 29.90, 'OVERDUE', '2025-06-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:11', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/zboiw7a7wr4sdjxi', NULL, 'sub_sulll48m6obfa7ze'),
(60283, 'pay_lfdb2n528fjhwr2v', 191, 29.90, 'OVERDUE', '2025-05-17', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:12', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/lfdb2n528fjhwr2v', NULL, 'sub_sulll48m6obfa7ze'),
(60285, 'pay_hoyxsysob8sz37e1', 215, 699.00, 'RECEIVED', '2025-03-19', '2025-03-19', '2025-03-19 00:00:00', '2025-07-29 10:58:14', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/hoyxsysob8sz37e1', NULL, NULL),
(60287, 'pay_pmmvvbvtse8nqh6b', 215, 90.00, 'RECEIVED', '2025-03-19', '2025-03-19', '2025-03-19 00:00:00', '2025-07-29 10:58:15', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/pmmvvbvtse8nqh6b', NULL, NULL),
(60289, 'pay_oz5fmst2csficryi', 198, 29.90, 'PENDING', '2026-04-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:17', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/oz5fmst2csficryi', NULL, 'sub_qnb988z9dsv5j72w'),
(60291, 'pay_vy3b9zxzco4eh9v8', 198, 29.90, 'PENDING', '2026-03-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:18', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/vy3b9zxzco4eh9v8', NULL, 'sub_qnb988z9dsv5j72w'),
(60293, 'pay_48utcy5qxok4y5jh', 198, 29.90, 'PENDING', '2026-02-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:20', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/48utcy5qxok4y5jh', NULL, 'sub_qnb988z9dsv5j72w'),
(60295, 'pay_p6ebdnogxvszd70e', 198, 29.90, 'PENDING', '2026-01-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:22', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/p6ebdnogxvszd70e', NULL, 'sub_qnb988z9dsv5j72w'),
(60297, 'pay_4iuq5tw7icdhwscb', 198, 29.90, 'PENDING', '2025-12-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:23', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4iuq5tw7icdhwscb', NULL, 'sub_qnb988z9dsv5j72w'),
(60299, 'pay_4ck9apqroihp4sq6', 198, 29.90, 'PENDING', '2025-11-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:25', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4ck9apqroihp4sq6', NULL, 'sub_qnb988z9dsv5j72w'),
(60301, 'pay_2nbo38rt6a4gjipp', 198, 29.90, 'PENDING', '2025-10-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:26', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2nbo38rt6a4gjipp', NULL, 'sub_qnb988z9dsv5j72w'),
(60303, 'pay_4nvk21fc7p6szc2v', 198, 29.90, 'PENDING', '2025-09-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:28', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4nvk21fc7p6szc2v', NULL, 'sub_qnb988z9dsv5j72w'),
(60305, 'pay_ckztdq4nk0ocq667', 198, 29.90, 'PENDING', '2025-08-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:30', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ckztdq4nk0ocq667', NULL, 'sub_qnb988z9dsv5j72w'),
(60307, 'pay_083r21q6by1axvav', 198, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:31', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/083r21q6by1axvav', NULL, 'sub_qnb988z9dsv5j72w'),
(60309, 'pay_gyi6f1enlggl6n5e', 198, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:33', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/gyi6f1enlggl6n5e', NULL, 'sub_qnb988z9dsv5j72w'),
(60311, 'pay_tq15ne94de0ehok4', 212, 29.90, 'PENDING', '2026-03-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:34', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/tq15ne94de0ehok4', NULL, 'sub_om5xop9rx7t8t1yh'),
(60313, 'pay_4xtywsfj5z601vne', 212, 29.90, 'PENDING', '2026-02-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:36', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/4xtywsfj5z601vne', NULL, 'sub_om5xop9rx7t8t1yh'),
(60315, 'pay_selzs5r98xh49ag5', 212, 29.90, 'PENDING', '2026-01-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:37', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/selzs5r98xh49ag5', NULL, 'sub_om5xop9rx7t8t1yh'),
(60317, 'pay_9pwi5ttx3ww0eho4', 275, 29.90, 'PENDING', '2025-12-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:39', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/9pwi5ttx3ww0eho4', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60319, 'pay_090oo8y5hkizcx4f', 275, 29.90, 'PENDING', '2025-11-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:40', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/090oo8y5hkizcx4f', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60321, 'pay_rl324ibikkcmcj5u', 275, 29.90, 'PENDING', '2025-10-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:42', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/rl324ibikkcmcj5u', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60323, 'pay_vsw0qyb6emdn5ln4', 275, 29.90, 'PENDING', '2025-09-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:44', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/vsw0qyb6emdn5ln4', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60325, 'pay_iui1jl4ydjswcjoj', 275, 29.90, 'PENDING', '2025-08-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:45', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/iui1jl4ydjswcjoj', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60327, 'pay_zkcep36xixoapdug', 275, 29.90, 'OVERDUE', '2025-07-15', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:47', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/zkcep36xixoapdug', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60329, 'pay_cotzc25km3o62w0p', 275, 29.90, 'OVERDUE', '2025-06-10', NULL, '2025-03-19 00:00:00', '2025-07-29 10:58:48', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/cotzc25km3o62w0p', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60331, 'pay_fay3bhd4lejg947j', 275, 30.51, 'RECEIVED', '2025-05-10', '2025-05-13', '2025-03-19 00:00:00', '2025-07-29 10:58:50', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'PIX', NULL, 'https://www.asaas.com/i/fay3bhd4lejg947j', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60333, 'pay_i8wuvdc7enf3gea1', 275, 90.00, 'RECEIVED', '2025-03-19', '2025-03-19', '2025-03-19 00:00:00', '2025-07-29 10:58:51', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/i8wuvdc7enf3gea1', NULL, NULL),
(60335, 'pay_zpq5kh5jjz555smb', 251, 248.50, 'RECEIVED', '2025-03-28', '2025-03-26', '2025-03-19 00:00:00', '2025-07-29 10:58:53', 'Criação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/zpq5kh5jjz555smb', NULL, NULL),
(60337, 'pay_sfvjlh9zzrmi3r53', 168, 180.00, 'RECEIVED', '2025-03-18', '2025-03-18', '2025-03-18 00:00:00', '2025-07-29 10:58:55', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/sfvjlh9zzrmi3r53', NULL, NULL),
(60339, 'pay_sh699oal7zj4ze6g', 236, 29.90, 'RECEIVED', '2025-03-18', '2025-03-18', '2025-03-18 00:00:00', '2025-07-29 10:58:56', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/sh699oal7zj4ze6g', NULL, NULL),
(60341, 'pay_or40i2sfkfyqa084', 174, 29.90, 'OVERDUE', '2025-04-25', NULL, '2025-03-17 00:00:00', '2025-07-29 10:58:58', 'Plano Mensal Hosp. + Manutenção E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/or40i2sfkfyqa084', NULL, 'sub_3scto700oy8oqo8m'),
(60343, 'pay_gkk25wil6ial3mdj', 175, 29.90, 'OVERDUE', '2025-04-25', NULL, '2025-03-17 00:00:00', '2025-07-29 10:58:59', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/gkk25wil6ial3mdj', NULL, 'sub_fgev4t35r9zvziov'),
(60345, 'pay_dzecb1lngc0fjyqc', 245, 29.90, 'RECEIVED_IN_CASH', '2025-04-25', '2025-04-25', '2025-03-17 00:00:00', '2025-07-29 10:59:01', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/dzecb1lngc0fjyqc', NULL, 'sub_f2zgsu6ds7fskv75'),
(60347, 'pay_p9tqn4arzbrshxhj', 268, 29.90, 'OVERDUE', '2025-04-25', NULL, '2025-03-17 00:00:00', '2025-07-29 10:59:02', '', 'BOLETO', NULL, 'https://www.asaas.com/i/p9tqn4arzbrshxhj', NULL, 'sub_lzkw4ntgewkejb6n'),
(60349, 'pay_t6ddqyhg08kbcpzs', 284, 29.90, 'RECEIVED', '2025-04-25', '2025-04-25', '2025-03-17 00:00:00', '2025-07-29 10:59:04', 'Plano Hospedagem Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/t6ddqyhg08kbcpzs', NULL, 'sub_khpfl1dpc6pfj9ix'),
(60351, 'pay_v6c65h7z1x4nfg9d', 202, 29.90, 'OVERDUE', '2025-04-23', NULL, '2025-03-15 00:00:00', '2025-07-29 10:59:06', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/v6c65h7z1x4nfg9d', NULL, 'sub_5taox270k3eejldi'),
(60353, 'pay_f6mggmj1fm0sa8ol', 271, 39.90, 'OVERDUE', '2025-04-23', NULL, '2025-03-15 00:00:00', '2025-07-29 10:59:07', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/f6mggmj1fm0sa8ol', NULL, 'sub_1dx95biw49q1zskp'),
(60355, 'pay_x1mwwmh7zyi521ft', 251, 29.90, 'OVERDUE', '2025-04-21', NULL, '2025-03-13 00:00:00', '2025-07-29 10:59:09', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/x1mwwmh7zyi521ft', NULL, 'sub_fwc0baa1kthqsncu');
INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(60357, 'pay_xgktvw5f2vaovxlr', 252, 29.90, 'OVERDUE', '2025-04-20', NULL, '2025-03-12 00:00:00', '2025-07-29 10:59:10', 'Plano Mensal Manutenção e Hospedagem Site: decoreambientes.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/xgktvw5f2vaovxlr', NULL, 'sub_18l6bewxxhh16vwl'),
(60359, 'pay_bhlov0kg34hdvwru', 231, 19.90, 'CONFIRMED', '2026-02-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 12 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/bhlov0kg34hdvwru', '12', NULL),
(60361, 'pay_mykxj4xouog9sb0z', 231, 19.90, 'CONFIRMED', '2026-01-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 11 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mykxj4xouog9sb0z', '11', NULL),
(60363, 'pay_daizvmsngqxb3yii', 231, 19.90, 'CONFIRMED', '2025-12-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 10 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/daizvmsngqxb3yii', '10', NULL),
(60365, 'pay_kcqb51ynri36ru5a', 231, 19.90, 'CONFIRMED', '2025-11-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 9 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/kcqb51ynri36ru5a', '9', NULL),
(60367, 'pay_fx8rp0vzierpxe9v', 231, 19.90, 'CONFIRMED', '2025-10-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 8 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fx8rp0vzierpxe9v', '8', NULL),
(60369, 'pay_8wji3094xs5h7wwj', 231, 19.90, 'CONFIRMED', '2025-09-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 7 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/8wji3094xs5h7wwj', '7', NULL),
(60371, 'pay_f89y1485q8s757en', 231, 19.90, 'CONFIRMED', '2025-08-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 6 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/f89y1485q8s757en', '6', NULL),
(60373, 'pay_7dy2w2dwvyjd0xqp', 231, 19.90, 'CONFIRMED', '2025-07-12', NULL, '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 5 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/7dy2w2dwvyjd0xqp', '5', NULL),
(60375, 'pay_vvnrw3m1dc4im6z4', 231, 19.90, 'RECEIVED', '2025-06-12', '2025-07-17', '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 4 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/vvnrw3m1dc4im6z4', '4', NULL),
(60377, 'pay_h0av5xt7j7eijrtl', 231, 19.90, 'RECEIVED', '2025-05-12', '2025-06-16', '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 3 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/h0av5xt7j7eijrtl', '3', NULL),
(60379, 'pay_41gb320eherjblcc', 231, 19.90, 'RECEIVED', '2025-04-12', '2025-05-14', '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 2 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/41gb320eherjblcc', '2', NULL),
(60380, 'pay_9k3bphepzcu8kdig', 231, 19.90, 'RECEIVED', '2025-03-12', '2025-04-14', '2025-03-11 00:00:00', '2025-07-29 07:56:27', 'Parcela 1 de 12. PLano de Hospedagem e Manutenção Anual', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/9k3bphepzcu8kdig', '1', NULL),
(60382, 'pay_mmib1tub4ffvi3p3', 231, 238.80, 'PENDING', '2026-03-11', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:30', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/mmib1tub4ffvi3p3', NULL, 'sub_a6egwqu0urhwfkp6'),
(60384, 'pay_g9zxkm82llgmt3ym', 212, 29.90, 'PENDING', '2025-12-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:32', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/g9zxkm82llgmt3ym', NULL, 'sub_om5xop9rx7t8t1yh'),
(60386, 'pay_56w8mke7lsr2rfl6', 212, 29.90, 'PENDING', '2025-11-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:34', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/56w8mke7lsr2rfl6', NULL, 'sub_om5xop9rx7t8t1yh'),
(60389, 'pay_9z29t7wwrhnvk2gu', 212, 29.90, 'PENDING', '2025-10-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:35', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/9z29t7wwrhnvk2gu', NULL, 'sub_om5xop9rx7t8t1yh'),
(60392, 'pay_me1c7bo4mide4v04', 212, 29.90, 'PENDING', '2025-09-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:37', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/me1c7bo4mide4v04', NULL, 'sub_om5xop9rx7t8t1yh'),
(60395, 'pay_ih8jp74kcymyur68', 212, 29.90, 'PENDING', '2025-08-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:38', 'Plano Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ih8jp74kcymyur68', NULL, 'sub_om5xop9rx7t8t1yh'),
(60398, 'pay_1qi3v5rd7qxvzurz', 212, 29.90, 'RECEIVED', '2025-07-10', '2025-07-07', '2025-03-11 00:00:00', '2025-07-29 10:59:40', 'Plano Hospedagem e Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/1qi3v5rd7qxvzurz', NULL, 'sub_om5xop9rx7t8t1yh'),
(60401, 'pay_lfw5atmmias8maxp', 212, 29.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-03-11 00:00:00', '2025-07-29 10:59:41', 'Plano Hospedagem e Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/lfw5atmmias8maxp', NULL, 'sub_om5xop9rx7t8t1yh'),
(60404, 'pay_tiy33090j47do3so', 212, 29.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-03-11 00:00:00', '2025-07-29 10:59:43', 'Plano Hospedagem e Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/tiy33090j47do3so', NULL, 'sub_om5xop9rx7t8t1yh'),
(60407, 'pay_kuvid7eu1ess3v70', 212, 1200.60, 'RECEIVED', '2025-03-12', '2025-03-12', '2025-03-11 00:00:00', '2025-07-29 10:59:45', 'Criação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/kuvid7eu1ess3v70', NULL, NULL),
(60414, 'pay_mlubjwom27uyr43g', 228, 39.90, 'PENDING', '2025-12-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:49', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/mlubjwom27uyr43g', NULL, 'sub_laflhqry577ok0o2'),
(60418, 'pay_1xpbswehsatsj8qm', 228, 39.90, 'PENDING', '2025-11-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:51', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/1xpbswehsatsj8qm', NULL, 'sub_laflhqry577ok0o2'),
(60421, 'pay_lfj4c3hoj2q0n80q', 228, 39.90, 'PENDING', '2025-10-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:52', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/lfj4c3hoj2q0n80q', NULL, 'sub_laflhqry577ok0o2'),
(60424, 'pay_ykse6e17cfkkg270', 228, 39.90, 'PENDING', '2025-09-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:54', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/ykse6e17cfkkg270', NULL, 'sub_laflhqry577ok0o2'),
(60427, 'pay_g44egrofx7veqe5u', 228, 39.90, 'PENDING', '2025-08-10', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:56', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/g44egrofx7veqe5u', NULL, 'sub_laflhqry577ok0o2'),
(60430, 'pay_8fl3lpfx2e39650j', 228, 39.90, 'OVERDUE', '2025-07-15', NULL, '2025-03-11 00:00:00', '2025-07-29 10:59:57', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/8fl3lpfx2e39650j', NULL, 'sub_laflhqry577ok0o2'),
(60433, 'pay_7ex2zmquxfenfaz4', 228, 39.90, 'RECEIVED', '2025-06-10', '2025-06-10', '2025-03-11 00:00:00', '2025-07-29 10:59:59', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/7ex2zmquxfenfaz4', NULL, 'sub_laflhqry577ok0o2'),
(60436, 'pay_enz59ico8wz1p8d3', 228, 39.90, 'RECEIVED', '2025-05-10', '2025-05-09', '2025-03-11 00:00:00', '2025-07-29 11:00:00', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/enz59ico8wz1p8d3', NULL, 'sub_laflhqry577ok0o2'),
(60439, 'pay_u0h8giuphfa9kbsz', 237, 29.90, 'RECEIVED_IN_CASH', '2025-04-18', '2025-04-22', '2025-03-10 00:00:00', '2025-07-29 11:00:02', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'BOLETO', NULL, 'https://www.asaas.com/i/u0h8giuphfa9kbsz', NULL, 'sub_tjd6ivet6h510mad'),
(60442, 'pay_j7c9rgj6z80by5i6', 190, 30.87, 'RECEIVED', '2025-04-17', '2025-05-26', '2025-03-09 00:00:00', '2025-07-29 11:00:03', 'Plano de Hospedagem e Manutenção Essencial', 'PIX', NULL, 'https://www.asaas.com/i/j7c9rgj6z80by5i6', NULL, 'sub_g3gra67j24pjeh52'),
(60444, 'pay_av7lx25ux6oha8ef', 191, 29.90, 'OVERDUE', '2025-04-17', NULL, '2025-03-09 00:00:00', '2025-07-29 11:00:05', 'Plano de Hospedagem e Manutenção Mensal', 'UNDEFINED', NULL, 'https://www.asaas.com/i/av7lx25ux6oha8ef', NULL, 'sub_sulll48m6obfa7ze'),
(60446, 'pay_kbbdj2wg7ujnynya', 196, 30.78, 'RECEIVED', '2025-04-15', '2025-05-15', '2025-03-07 00:00:00', '2025-07-29 11:00:06', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/kbbdj2wg7ujnynya', NULL, 'sub_lul4v892i4z7gsnz'),
(60448, 'pay_cj26iks9mdvanss9', 254, 30.70, 'RECEIVED', '2025-04-16', '2025-05-08', '2025-03-07 00:00:00', '2025-07-29 11:00:08', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/cj26iks9mdvanss9', NULL, 'sub_oil9ich37nuhoes7'),
(60450, 'pay_mq0ar2pv7d2he5h2', 172, 706.02, 'RECEIVED', '2025-03-07', '2025-03-07', '2025-03-07 00:00:00', '2025-07-29 11:00:10', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/mq0ar2pv7d2he5h2', NULL, NULL),
(60452, 'pay_mzm4bbxyznnl5m6o', 169, 97.00, 'PENDING', '2026-02-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 12 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mzm4bbxyznnl5m6o', '12', NULL),
(60454, 'pay_1qtit2qt7p05bu6y', 169, 97.00, 'PENDING', '2026-01-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 11 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1qtit2qt7p05bu6y', '11', NULL),
(60456, 'pay_nrbetpjhf3knbzqe', 169, 97.00, 'PENDING', '2025-12-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 10 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/nrbetpjhf3knbzqe', '10', NULL),
(60458, 'pay_3jwpxw067itk4t03', 169, 97.00, 'PENDING', '2025-11-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 9 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/3jwpxw067itk4t03', '9', NULL),
(60460, 'pay_tcnzsvey1mxzzfvd', 169, 97.00, 'PENDING', '2025-10-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 8 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/tcnzsvey1mxzzfvd', '8', NULL),
(60462, 'pay_qkpf9w05xoo42xog', 169, 97.00, 'PENDING', '2025-09-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 7 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/qkpf9w05xoo42xog', '7', NULL),
(60464, 'pay_tegbo50fkkbh005k', 169, 97.00, 'PENDING', '2025-08-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 6 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/tegbo50fkkbh005k', '6', NULL),
(60466, 'pay_7y8g8wuppm4zs73l', 169, 97.00, 'OVERDUE', '2025-07-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 5 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/7y8g8wuppm4zs73l', '5', NULL),
(60468, 'pay_x1mgpvtrzpk1sybj', 169, 97.00, 'OVERDUE', '2025-06-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 4 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/x1mgpvtrzpk1sybj', '4', NULL),
(60470, 'pay_ocfirj3d1sxumch7', 169, 97.00, 'OVERDUE', '2025-05-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 3 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ocfirj3d1sxumch7', '3', NULL),
(60472, 'pay_i8o928t0fv4lz5nv', 169, 97.00, 'OVERDUE', '2025-04-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 2 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/i8o928t0fv4lz5nv', '2', NULL),
(60474, 'pay_42d6vcbu3aulpsa2', 169, 97.00, 'OVERDUE', '2025-03-07', NULL, '2025-03-07 00:00:00', '2025-07-29 07:56:30', 'Parcela 1 de 12. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/42d6vcbu3aulpsa2', '1', NULL),
(60476, 'pay_3f44zz1614zffkx1', 169, 29.90, 'OVERDUE', '2025-03-30', NULL, '2025-03-07 00:00:00', '2025-07-29 11:00:30', 'Plano de Hospedagem e Manutenção Mensal ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/3f44zz1614zffkx1', NULL, 'sub_bebghk37yr7kaykc'),
(60478, 'pay_9e8xjmsfjtl84lib', 246, 350.00, 'RECEIVED', '2025-03-07', '2025-03-07', '2025-03-07 00:00:00', '2025-07-29 11:00:32', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/9e8xjmsfjtl84lib', NULL, NULL),
(60480, 'pay_icbk8dxi29puq7iu', 286, 54.29, 'RECEIVED', '2025-04-15', '2025-05-26', '2025-03-07 00:00:00', '2025-07-29 11:00:33', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/icbk8dxi29puq7iu', NULL, 'sub_fte624var188bxrz'),
(60482, 'pay_gt5fryqo7fskr72s', 219, 250.00, 'RECEIVED', '2025-03-06', '2025-03-06', '2025-03-06 00:00:00', '2025-07-29 11:00:35', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/gt5fryqo7fskr72s', NULL, NULL),
(60484, 'pay_cujfr59kufw7ea3t', 219, 152.34, 'CONFIRMED', '2025-08-06', NULL, '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 6 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/cujfr59kufw7ea3t', '6', NULL),
(60486, 'pay_6z5rkqn3jsqi8g8r', 219, 152.34, 'CONFIRMED', '2025-07-06', NULL, '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 5 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6z5rkqn3jsqi8g8r', '5', NULL),
(60488, 'pay_0mqs83qd3qi3xt1e', 219, 152.34, 'RECEIVED', '2025-06-06', '2025-07-14', '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 4 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/0mqs83qd3qi3xt1e', '4', NULL),
(60490, 'pay_1ms5b0nz6n6hlf2q', 219, 152.34, 'RECEIVED', '2025-05-06', '2025-06-10', '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 3 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1ms5b0nz6n6hlf2q', '3', NULL),
(60492, 'pay_oxk401strdrg2cu8', 219, 152.34, 'RECEIVED', '2025-04-06', '2025-05-09', '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 2 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/oxk401strdrg2cu8', '2', NULL),
(60494, 'pay_fjdrib57okmw0kal', 219, 152.34, 'RECEIVED', '2025-03-06', '2025-04-07', '2025-03-06 00:00:00', '2025-07-29 07:56:30', 'Parcela 1 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fjdrib57okmw0kal', '1', NULL),
(60496, 'pay_ru85gres6hpoahhj', 170, 100.00, 'RECEIVED', '2025-03-05', '2025-03-05', '2025-03-05 00:00:00', '2025-07-29 11:00:46', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ru85gres6hpoahhj', NULL, NULL),
(60498, 'pay_wqq6nos6wozjpue4', 256, 29.90, 'OVERDUE', '2025-04-13', NULL, '2025-03-05 00:00:00', '2025-07-29 11:00:47', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/wqq6nos6wozjpue4', NULL, 'sub_a9g0wq5bypr3lt2h'),
(60500, 'pay_tvptkyt15c337hze', 265, 30.77, 'RECEIVED', '2025-04-10', '2025-05-09', '2025-03-04 00:00:00', '2025-07-29 11:00:49', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/tvptkyt15c337hze', NULL, 'sub_02etd71kzxkv2vyl'),
(60502, 'pay_lzpaxumrk90bf345', 219, 30.55, 'RECEIVED', '2025-04-10', '2025-04-17', '2025-03-04 00:00:00', '2025-07-29 11:00:50', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/lzpaxumrk90bf345', NULL, 'sub_z70tugf89a57i6to'),
(60504, 'pay_9z0lxnq4w1p124cw', 266, 29.90, 'RECEIVED_IN_CASH', '2025-04-10', '2025-04-08', '2025-03-04 00:00:00', '2025-07-29 11:00:52', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/9z0lxnq4w1p124cw', NULL, 'sub_hs78qzpdzx1z44q2'),
(60506, 'pay_tkdd5qkbs57nkug7', 257, 29.90, 'OVERDUE', '2025-04-12', NULL, '2025-03-04 00:00:00', '2025-07-29 11:00:53', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/tkdd5qkbs57nkug7', NULL, 'sub_ju5bkk8q06x0vid9'),
(60508, 'pay_i815d1c9erjn39jc', 258, 39.90, 'OVERDUE', '2025-04-12', NULL, '2025-03-04 00:00:00', '2025-07-29 11:00:55', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/i815d1c9erjn39jc', NULL, 'sub_feo8zvrsmaf5q1tq'),
(60510, 'pay_kinj208vn1bym14b', 264, 31.21, 'RECEIVED', '2025-04-12', '2025-06-24', '2025-03-04 00:00:00', '2025-07-29 11:00:56', '', 'PIX', NULL, 'https://www.asaas.com/i/kinj208vn1bym14b', NULL, 'sub_ohuo9f9u852d7wki'),
(60512, 'pay_o379tlalqtr444cs', 188, 30.59, 'RECEIVED', '2025-04-10', '2025-04-21', '2025-03-03 00:00:00', '2025-07-29 11:00:58', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'PIX', NULL, 'https://www.asaas.com/i/o379tlalqtr444cs', NULL, 'sub_sh9rki6ysvvln4kx'),
(60514, 'pay_n2wa1lck8s82wj0d', 189, 29.90, 'OVERDUE', '2025-04-10', NULL, '2025-03-03 00:00:00', '2025-07-29 11:01:00', 'Plano de Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/n2wa1lck8s82wj0d', NULL, 'sub_6usu4i2bhas9vb5s'),
(60516, 'pay_yiw7wyaa57hhxw2j', 206, 30.53, 'RECEIVED', '2025-04-10', '2025-04-15', '2025-03-03 00:00:00', '2025-07-29 11:01:01', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/yiw7wyaa57hhxw2j', NULL, 'sub_b1fy6en75pgrzeoi'),
(60518, 'pay_w8h2mp19cxzvsizl', 195, 29.90, 'OVERDUE', '2025-04-10', NULL, '2025-03-03 00:00:00', '2025-07-29 11:01:03', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/w8h2mp19cxzvsizl', NULL, 'sub_f7jb5gycxnddsmje'),
(60520, 'pay_cisuuabc2qf7vkk1', 209, 29.90, 'OVERDUE', '2025-04-10', NULL, '2025-03-03 00:00:00', '2025-07-29 11:01:04', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/cisuuabc2qf7vkk1', NULL, 'sub_sp6cmivnmdkdywlg'),
(60522, 'pay_78u29fy4wlyw7uwq', 212, 29.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:06', 'Plano Hospedagem e Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/78u29fy4wlyw7uwq', NULL, 'sub_om5xop9rx7t8t1yh'),
(60524, 'pay_7jor3n8a50rhs0ul', 217, 29.90, 'OVERDUE', '2025-04-10', NULL, '2025-03-03 00:00:00', '2025-07-29 11:01:07', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/7jor3n8a50rhs0ul', NULL, 'sub_mlmp5a0sid5xyb5n'),
(60526, 'pay_7hhurxgq49x8gv7k', 198, 29.90, 'OVERDUE', '2025-04-10', NULL, '2025-03-03 00:00:00', '2025-07-29 11:01:09', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/7hhurxgq49x8gv7k', NULL, 'sub_qnb988z9dsv5j72w'),
(60528, 'pay_6qa4nk6nfyvu6amv', 199, 29.90, 'RECEIVED_IN_CASH', '2025-04-10', '2025-04-25', '2025-03-03 00:00:00', '2025-07-29 11:01:11', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/6qa4nk6nfyvu6amv', NULL, 'sub_m3c69vzc322xsk1x'),
(60530, 'pay_sd4dcuwv5xg8nz3z', 234, 29.90, 'RECEIVED_IN_CASH', '2025-04-10', '2025-05-09', '2025-03-03 00:00:00', '2025-07-29 11:01:12', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/sd4dcuwv5xg8nz3z', NULL, 'sub_5zhf56f9xgaopx3x'),
(60532, 'pay_rgprf2mjyxawctq8', 201, 29.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:14', 'PLano de Manutenção e Hospedagem ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/rgprf2mjyxawctq8', NULL, 'sub_duqxrig3fiaq14pb'),
(60534, 'pay_7mp368bgo6ww6ua4', 224, 29.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:15', 'Plano de Hespedagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/7mp368bgo6ww6ua4', NULL, 'sub_906jjzs1wiwlv535'),
(60536, 'pay_gthkfrd6zmud36ua', 227, 30.49, 'RECEIVED', '2025-04-10', '2025-04-11', '2025-03-03 00:00:00', '2025-07-29 11:01:17', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'PIX', NULL, 'https://www.asaas.com/i/gthkfrd6zmud36ua', NULL, 'sub_uof9cualj3j78zmc'),
(60538, 'pay_sk6753wyfcq32ump', 228, 39.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:18', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/sk6753wyfcq32ump', NULL, 'sub_laflhqry577ok0o2'),
(60540, 'pay_i9cxuplw4ti2jfga', 232, 30.81, 'RECEIVED', '2025-04-10', '2025-05-13', '2025-03-03 00:00:00', '2025-07-29 11:01:20', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'PIX', NULL, 'https://www.asaas.com/i/i9cxuplw4ti2jfga', NULL, 'sub_dhl2ubdq46degqsc'),
(60542, 'pay_4bc97w538ugumqbn', 240, 30.51, 'RECEIVED', '2025-04-10', '2025-04-13', '2025-03-03 00:00:00', '2025-07-29 11:01:22', 'Plano ESSENCIAL Hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/4bc97w538ugumqbn', NULL, 'sub_1p83yf64ebdz09eh'),
(60544, 'pay_1hwnfm5uplesvb10', 172, 631.13, 'RECEIVED', '2025-03-03', '2025-03-03', '2025-03-03 00:00:00', '2025-07-29 11:01:23', 'Entrada Projeto + Plano de Hospedagem Anual', 'PIX', NULL, 'https://www.asaas.com/i/1hwnfm5uplesvb10', NULL, NULL),
(60546, 'pay_m8z1425jh9e6rgyp', 245, 80.00, 'RECEIVED', '2025-03-10', '2025-03-07', '2025-03-03 00:00:00', '2025-07-29 11:01:25', 'Integração Google Maps | Avalições no Site', 'PIX', NULL, 'https://www.asaas.com/i/m8z1425jh9e6rgyp', NULL, NULL),
(60548, 'pay_w8q8a28w1pe6qxzm', 225, 29.90, 'RECEIVED_IN_CASH', '2025-04-10', '2025-04-11', '2025-03-03 00:00:00', '2025-07-29 11:01:26', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/w8q8a28w1pe6qxzm', NULL, 'sub_onezkhx9uo06h1we'),
(60550, 'pay_gop9f0nz3bzmuo7b', 263, 29.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:28', '', 'PIX', NULL, 'https://www.asaas.com/i/gop9f0nz3bzmuo7b', NULL, 'sub_re7f5cvc2t4u6fuo'),
(60552, 'pay_2wlmudwpec5v39kb', 275, 29.90, 'RECEIVED', '2025-04-10', '2025-04-10', '2025-03-03 00:00:00', '2025-07-29 11:01:29', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'PIX', NULL, 'https://www.asaas.com/i/2wlmudwpec5v39kb', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60554, 'pay_4nwvuf2ptntefqbw', 284, 200.00, 'RECEIVED', '2025-04-25', '2025-04-25', '2025-02-26 00:00:00', '2025-07-29 07:56:30', 'Parcela 2 de 2. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/4nwvuf2ptntefqbw', '2', NULL),
(60556, 'pay_s6hu22e4n593c7r4', 284, 200.00, 'RECEIVED', '2025-03-10', '2025-03-10', '2025-02-26 00:00:00', '2025-07-29 07:56:30', 'Parcela 1 de 2. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/s6hu22e4n593c7r4', '1', NULL),
(60558, 'pay_2ow1gpc3v8glhzwt', 180, 29.90, 'OVERDUE', '2025-04-05', NULL, '2025-02-25 00:00:00', '2025-07-29 11:01:34', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança  e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2ow1gpc3v8glhzwt', NULL, 'sub_oi20frpztw734s0g'),
(60560, 'pay_edqjm2ijakcnjyfm', 173, 30.00, 'RECEIVED', '2025-02-25', '2025-02-25', '2025-02-25 00:00:00', '2025-07-29 11:01:36', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/edqjm2ijakcnjyfm', NULL, NULL),
(60562, 'pay_g4nu2u1730cutik9', 279, 49.90, 'RECEIVED', '2025-04-05', '2025-04-06', '2025-02-25 00:00:00', '2025-07-29 11:01:37', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/g4nu2u1730cutik9', NULL, 'sub_htfllf6l0cu6s4qk'),
(60564, 'pay_8ffzd8c0glmw29uu', 174, 148.50, 'OVERDUE', '2025-04-25', NULL, '2025-02-24 00:00:00', '2025-07-29 07:56:30', 'Parcela 2 de 2. Criação e Desenvolvimeno WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/8ffzd8c0glmw29uu', '2', NULL),
(60566, 'pay_ijr9ix8hyl3ai23b', 174, 148.50, 'RECEIVED_IN_CASH', '2025-03-25', '2025-04-02', '2025-02-24 00:00:00', '2025-07-29 07:56:30', 'Parcela 1 de 2. Criação e Desenvolvimeno WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ijr9ix8hyl3ai23b', '1', NULL),
(60568, 'pay_acno18p8vlipzav2', 174, 400.00, 'RECEIVED', '2025-02-24', '2025-02-24', '2025-02-24 00:00:00', '2025-07-29 11:01:42', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/acno18p8vlipzav2', NULL, NULL),
(60570, 'pay_mli7nh80zgukvrt5', 246, 39.00, 'RECEIVED', '2025-02-21', '2025-02-21', '2025-02-21 00:00:00', '2025-07-29 11:01:43', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/mli7nh80zgukvrt5', NULL, NULL),
(60572, 'pay_5j9uzi3whkkw6lqi', 175, 199.00, 'OVERDUE', '2025-04-25', NULL, '2025-02-21 00:00:00', '2025-07-29 07:56:30', 'Parcela 3 de 3. Criação e Desenvolvimento WEB | Site Institucional', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/5j9uzi3whkkw6lqi', '3', NULL),
(60574, 'pay_mc597vrv1qlme8qc', 175, 199.00, 'RECEIVED_IN_CASH', '2025-03-25', '2025-04-01', '2025-02-21 00:00:00', '2025-07-29 07:56:30', 'Parcela 2 de 3. Criação e Desenvolvimento WEB | Site Institucional', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/mc597vrv1qlme8qc', '2', NULL),
(60576, 'pay_xnnciwbmeqv2arc4', 175, 199.00, 'RECEIVED', '2025-02-25', '2025-02-24', '2025-02-21 00:00:00', '2025-07-29 07:56:30', 'Parcela 1 de 3. Criação e Desenvolvimento WEB | Site Institucional', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/xnnciwbmeqv2arc4', '1', NULL),
(60578, 'pay_w6sxv6oycinfcyvl', 176, 40.00, 'RECEIVED', '2025-02-20', '2025-02-20', '2025-02-20 00:00:00', '2025-07-29 11:01:50', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/w6sxv6oycinfcyvl', NULL, NULL),
(60580, 'pay_s4nqrvcn9gs0tfxn', 246, 800.00, 'RECEIVED', '2025-02-20', '2025-02-20', '2025-02-20 00:00:00', '2025-07-29 11:01:51', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/s4nqrvcn9gs0tfxn', NULL, NULL),
(60582, 'pay_o9rgsx805il2rbgp', 246, 1200.00, 'RECEIVED', '2025-02-18', '2025-02-18', '2025-02-18 00:00:00', '2025-07-29 11:01:53', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/o9rgsx805il2rbgp', NULL, NULL),
(60584, 'pay_wu7v5uw479l0sveu', 185, 49.90, 'RECEIVED', '2025-04-09', '2025-04-09', '2025-02-18 00:00:00', '2025-07-29 11:01:54', 'Plano ImobSites ', 'PIX', NULL, 'https://www.asaas.com/i/wu7v5uw479l0sveu', NULL, 'sub_0u0hiabbslpiov8l'),
(60586, 'pay_o929j3s8z86dmmpi', 220, 29.90, 'RECEIVED', '2025-03-29', '2025-03-31', '2025-02-18 00:00:00', '2025-07-29 11:01:56', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/o929j3s8z86dmmpi', NULL, 'sub_0ob5gqdk6bgm9evj'),
(60588, 'pay_jntpetbz5ozxpg3j', 267, 29.00, 'RECEIVED_IN_CASH', '2025-03-29', '2025-04-29', '2025-02-18 00:00:00', '2025-07-29 11:01:57', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/jntpetbz5ozxpg3j', NULL, 'sub_504ksi201k6033es'),
(60590, 'pay_i6mqwlo7xk0kkdvj', 221, 29.90, 'OVERDUE', '2025-03-28', NULL, '2025-02-17 00:00:00', '2025-07-29 11:01:59', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/i6mqwlo7xk0kkdvj', NULL, 'sub_xahjz6ws9fumwnl9'),
(60592, 'pay_g0smfthxiro8mu53', 236, 29.90, 'RECEIVED_IN_CASH', '2025-03-28', '2025-07-30', '2025-02-17 00:00:00', '2025-07-29 11:02:01', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/g0smfthxiro8mu53', NULL, 'sub_bwvnv3t5548q79az'),
(60594, 'pay_wl5d5dhl4w40ic9v', 245, 29.90, 'RECEIVED_IN_CASH', '2025-03-25', '2025-03-24', '2025-02-14 00:00:00', '2025-07-29 11:02:02', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/wl5d5dhl4w40ic9v', NULL, 'sub_f2zgsu6ds7fskv75'),
(60596, 'pay_itvqeej4bhkeiu6t', 268, 29.90, 'OVERDUE', '2025-03-25', NULL, '2025-02-14 00:00:00', '2025-07-29 11:02:04', '', 'BOLETO', NULL, 'https://www.asaas.com/i/itvqeej4bhkeiu6t', NULL, 'sub_lzkw4ntgewkejb6n'),
(60598, 'pay_nnnu4vok3vcqut8j', 284, 30.62, 'RECEIVED', '2025-03-25', '2025-04-08', '2025-02-14 00:00:00', '2025-07-29 11:02:05', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/nnnu4vok3vcqut8j', NULL, 'sub_khpfl1dpc6pfj9ix'),
(60600, 'pay_vbc59rziwy2eokwd', 263, 145.00, 'RECEIVED', '2025-02-28', '2025-02-18', '2025-02-12 00:00:00', '2025-07-29 11:02:07', '', 'PIX', NULL, 'https://www.asaas.com/i/vbc59rziwy2eokwd', NULL, NULL),
(60602, 'pay_ydgbzi104wk12rrc', 263, 150.00, 'RECEIVED', '2025-02-12', '2025-02-12', '2025-02-12 00:00:00', '2025-07-29 11:02:08', '', 'PIX', NULL, 'https://www.asaas.com/i/ydgbzi104wk12rrc', NULL, NULL),
(60604, 'pay_97vjoagei0wd6ibq', 202, 29.90, 'RECEIVED', '2025-03-23', '2025-03-24', '2025-02-12 00:00:00', '2025-07-29 11:02:10', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/97vjoagei0wd6ibq', NULL, 'sub_5taox270k3eejldi'),
(60605, 'pay_aomeqkmolqe6nnxm', 271, 39.90, 'OVERDUE', '2025-03-23', NULL, '2025-02-12 00:00:00', '2025-07-29 11:02:12', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/aomeqkmolqe6nnxm', NULL, 'sub_1dx95biw49q1zskp'),
(60606, 'pay_2g9yp55exw9ajvsf', 265, 29.90, 'RECEIVED', '2025-03-10', '2025-03-10', '2025-02-10 00:00:00', '2025-07-29 11:02:13', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/2g9yp55exw9ajvsf', NULL, 'sub_02etd71kzxkv2vyl'),
(60607, 'pay_euamt9jf09lcfjws', 265, 30.50, 'RECEIVED', '2025-02-10', '2025-02-12', '2025-02-10 00:00:00', '2025-07-29 11:02:15', 'Plano de Hospedagem Mensal + Suporte + E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/euamt9jf09lcfjws', NULL, 'sub_02etd71kzxkv2vyl'),
(60609, 'pay_qoi5tpuf60tsp7pi', 178, 298.80, 'PENDING', '2026-03-05', NULL, '2025-02-10 00:00:00', '2025-07-29 11:02:16', 'Renovação Plano de Hospedagem', 'UNDEFINED', NULL, 'https://www.asaas.com/i/qoi5tpuf60tsp7pi', NULL, 'sub_ukut3kw0c83d6yws'),
(60611, 'pay_m6fotpcf75juh2lx', 178, 24.90, 'CONFIRMED', '2026-01-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:30', 'Parcela 12 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/m6fotpcf75juh2lx', '12', NULL),
(60613, 'pay_fxwwb94uvur0on35', 178, 24.90, 'CONFIRMED', '2025-12-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:30', 'Parcela 11 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fxwwb94uvur0on35', '11', NULL),
(60615, 'pay_stue4mcujr7wfdx6', 178, 24.90, 'CONFIRMED', '2025-11-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:30', 'Parcela 10 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/stue4mcujr7wfdx6', '10', NULL),
(60617, 'pay_ql6py0xqo2hozt81', 178, 24.90, 'CONFIRMED', '2025-10-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:30', 'Parcela 9 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ql6py0xqo2hozt81', '9', NULL),
(60619, 'pay_5v9mhrqxnm4lh0be', 178, 24.90, 'CONFIRMED', '2025-09-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:30', 'Parcela 8 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/5v9mhrqxnm4lh0be', '8', NULL),
(60624, 'pay_u4f7cgr3a2qu751b', 178, 24.90, 'CONFIRMED', '2025-08-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 7 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/u4f7cgr3a2qu751b', '7', NULL),
(60626, 'pay_no90e0sdfblbuz5b', 178, 24.90, 'CONFIRMED', '2025-07-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 6 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/no90e0sdfblbuz5b', '6', NULL),
(60628, 'pay_pah5ekzpy6qwyibs', 178, 24.90, 'CONFIRMED', '2025-06-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 5 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/pah5ekzpy6qwyibs', '5', NULL),
(60630, 'pay_vv9ewbxscvj5cqjd', 178, 24.90, 'RECEIVED', '2025-05-10', '2025-07-24', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 4 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/vv9ewbxscvj5cqjd', '4', NULL),
(60632, 'pay_hiupg6768hbpimk9', 178, 24.90, 'RECEIVED', '2025-04-10', '2025-06-23', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/hiupg6768hbpimk9', '3', NULL),
(60634, 'pay_5snbbzjau7o78n1v', 178, 24.90, 'RECEIVED', '2025-03-10', '2025-05-21', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/5snbbzjau7o78n1v', '2', NULL),
(60636, 'pay_95gs4xfe7kq8y3er', 178, 24.90, 'RECEIVED', '2025-02-10', '2025-04-22', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 12. Plano de Hospedagem + Email Profissional e Suporte Mensal', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/95gs4xfe7kq8y3er', '1', NULL),
(60638, 'pay_0bjode5z7xq7rhd9', 246, 145.78, 'RECEIVED', '2025-02-10', '2025-02-10', '2025-02-10 00:00:00', '2025-07-29 11:02:39', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/0bjode5z7xq7rhd9', NULL, NULL),
(60640, 'pay_9gdo7p8uao4u1u2v', 178, 116.20, 'CONFIRMED', '2025-07-10', NULL, '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 6 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/9gdo7p8uao4u1u2v', '6', NULL),
(60642, 'pay_6xt954qc4c4mhcjh', 178, 116.16, 'RECEIVED', '2025-06-10', '2025-07-21', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 5 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6xt954qc4c4mhcjh', '5', NULL),
(60644, 'pay_fieys00dy3osvhxz', 178, 116.16, 'RECEIVED', '2025-05-10', '2025-06-18', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 4 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fieys00dy3osvhxz', '4', NULL),
(60646, 'pay_103g02e4wxfkf4hu', 178, 116.16, 'RECEIVED', '2025-04-10', '2025-05-19', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/103g02e4wxfkf4hu', '3', NULL),
(60647, 'pay_wn8cxmoylhwdxkvo', 178, 116.16, 'RECEIVED', '2025-03-10', '2025-04-15', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/wn8cxmoylhwdxkvo', '2', NULL),
(60649, 'pay_76cx6du3jkn4jz0p', 178, 116.16, 'RECEIVED', '2025-02-10', '2025-03-14', '2025-02-10 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/76cx6du3jkn4jz0p', '1', NULL),
(60651, 'pay_lzd9dpg27u329qq0', 251, 30.66, 'RECEIVED', '2025-03-21', '2025-04-08', '2025-02-10 00:00:00', '2025-07-29 11:02:50', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/lzd9dpg27u329qq0', NULL, 'sub_fwc0baa1kthqsncu'),
(60653, 'pay_zi6obbb5zhgydk1n', 253, 29.90, 'RECEIVED', '2025-03-19', '2025-03-14', '2025-02-08 00:00:00', '2025-07-29 11:02:51', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/zi6obbb5zhgydk1n', NULL, 'sub_3z72l1acpglcr1fa'),
(60655, 'pay_9hqnuwh5mr31cuvd', 237, 29.90, 'RECEIVED_IN_CASH', '2025-03-18', '2025-04-22', '2025-02-07 00:00:00', '2025-07-29 11:02:53', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'BOLETO', NULL, 'https://www.asaas.com/i/9hqnuwh5mr31cuvd', NULL, 'sub_tjd6ivet6h510mad'),
(60657, 'pay_2zsyhy445noqfdxh', 179, 2253.00, 'RECEIVED', '2025-02-06', '2025-02-06', '2025-02-06 00:00:00', '2025-07-29 11:02:54', 'Cobrança gerada automaticamente a partir de TED recebido.', 'TRANSFER', NULL, 'https://www.asaas.com/i/2zsyhy445noqfdxh', NULL, NULL),
(60659, 'pay_vtyowpux8duq7sh4', 190, 30.49, 'RECEIVED', '2025-03-17', '2025-03-18', '2025-02-06 00:00:00', '2025-07-29 11:02:56', 'Plano de Hospedagem e Manutenção Essencial', 'PIX', NULL, 'https://www.asaas.com/i/vtyowpux8duq7sh4', NULL, 'sub_g3gra67j24pjeh52'),
(60661, 'pay_fs8obeh4cl341ng7', 191, 30.55, 'RECEIVED', '2025-03-17', '2025-03-24', '2025-02-06 00:00:00', '2025-07-29 11:02:58', 'Plano de Hospedagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/fs8obeh4cl341ng7', NULL, 'sub_sulll48m6obfa7ze'),
(60663, 'pay_dpoagwlta5yqvumi', 219, 30.64, 'RECEIVED', '2025-03-10', '2025-03-26', '2025-02-05 00:00:00', '2025-07-29 11:02:59', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/dpoagwlta5yqvumi', NULL, 'sub_z70tugf89a57i6to'),
(60665, 'pay_ur4aknfwb7ezah3x', 266, 29.90, 'RECEIVED_IN_CASH', '2025-03-10', '2025-04-08', '2025-02-05 00:00:00', '2025-07-29 11:03:01', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ur4aknfwb7ezah3x', NULL, 'sub_hs78qzpdzx1z44q2'),
(60667, 'pay_vlxf55k69wiaqon5', 180, 188.34, 'OVERDUE', '2025-05-05', NULL, '2025-02-05 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 3. Criação  e Desenvolvimento WEB', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/vlxf55k69wiaqon5', '3', NULL),
(60669, 'pay_cri7rhpj9tcji08s', 180, 188.33, 'OVERDUE', '2025-04-05', NULL, '2025-02-05 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 3. Criação  e Desenvolvimento WEB', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/cri7rhpj9tcji08s', '2', NULL),
(60671, 'pay_8axb5r1n0f39oe3a', 180, 188.33, 'OVERDUE', '2025-03-05', NULL, '2025-02-05 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 3. Criação  e Desenvolvimento WEB', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/8axb5r1n0f39oe3a', '1', NULL),
(60673, 'pay_af6zk5gsrjymlubv', 180, 132.00, 'RECEIVED', '2025-02-05', '2025-02-05', '2025-02-05 00:00:00', '2025-07-29 11:03:07', 'Criação e Desenvolvimento WEB', 'PIX', NULL, 'https://www.asaas.com/i/af6zk5gsrjymlubv', NULL, NULL),
(60676, 'pay_m5eolfcc35yvish4', 180, 29.90, 'OVERDUE', '2025-03-05', NULL, '2025-02-05 00:00:00', '2025-07-29 11:03:08', 'Plano Hospedagem e Manutenção Mensal + Certificado Segurança  e E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/m5eolfcc35yvish4', NULL, 'sub_oi20frpztw734s0g'),
(60679, 'pay_59eni4bpphzfyi5j', 181, 30.00, 'RECEIVED', '2025-02-05', '2025-02-05', '2025-02-05 00:00:00', '2025-07-29 11:03:10', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/59eni4bpphzfyi5j', NULL, NULL),
(60682, 'pay_oqcni748rrumrsxb', 246, 123.37, 'RECEIVED', '2025-02-05', '2025-02-05', '2025-02-05 00:00:00', '2025-07-29 11:03:12', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/oqcni748rrumrsxb', NULL, NULL),
(60685, 'pay_ju8ffp4u74qkipsb', 246, 550.00, 'RECEIVED', '2025-02-05', '2025-02-05', '2025-02-05 00:00:00', '2025-07-29 11:03:13', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ju8ffp4u74qkipsb', NULL, NULL),
(60688, 'pay_dx5ocnhafzb7jtmv', 266, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-04-08', '2025-02-05 00:00:00', '2025-07-29 11:03:15', 'Plano de Hospedagem e Manutenção Mensal + E-mail Profissional', 'UNDEFINED', NULL, 'https://www.asaas.com/i/dx5ocnhafzb7jtmv', NULL, 'sub_hs78qzpdzx1z44q2'),
(60691, 'pay_gjc5qgrubr93adcy', 196, 29.90, 'RECEIVED', '2025-03-15', '2025-03-17', '2025-02-05 00:00:00', '2025-07-29 11:03:16', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/gjc5qgrubr93adcy', NULL, 'sub_lul4v892i4z7gsnz'),
(60694, 'pay_d4nc55aobkmhexwk', 286, 49.90, 'RECEIVED_IN_CASH', '2025-03-15', '2025-03-21', '2025-02-04 00:00:00', '2025-07-29 11:03:18', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/d4nc55aobkmhexwk', NULL, 'sub_fte624var188bxrz'),
(60696, 'pay_dffs0sp4pevr66n3', 249, 90.00, 'RECEIVED', '2025-02-04', '2025-02-04', '2025-02-04 00:00:00', '2025-07-29 11:03:19', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: dominio', 'PIX', NULL, 'https://www.asaas.com/i/dffs0sp4pevr66n3', NULL, NULL),
(60699, 'pay_qv8p2nfpii7ret7u', 182, 116.20, 'CONFIRMED', '2025-07-05', NULL, '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 6 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/qv8p2nfpii7ret7u', '6', NULL),
(60702, 'pay_vmnged6x2qxhuqye', 182, 116.16, 'RECEIVED', '2025-06-05', '2025-07-14', '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 5 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/vmnged6x2qxhuqye', '5', NULL),
(60705, 'pay_jh0legwky199rdow', 182, 116.16, 'RECEIVED', '2025-05-05', '2025-06-12', '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 4 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/jh0legwky199rdow', '4', NULL),
(60708, 'pay_alf0pgdw0umj4q1u', 182, 116.16, 'RECEIVED', '2025-04-05', '2025-05-12', '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/alf0pgdw0umj4q1u', '3', NULL),
(60711, 'pay_rowmr0kuownn3tzm', 182, 116.16, 'RECEIVED', '2025-03-05', '2025-04-09', '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/rowmr0kuownn3tzm', '2', NULL),
(60714, 'pay_750cvmsj12wsz6fo', 182, 116.16, 'RECEIVED', '2025-02-05', '2025-03-10', '2025-02-04 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/750cvmsj12wsz6fo', '1', NULL),
(60717, 'pay_jnd0zxxb4e9aszx1', 256, 29.90, 'RECEIVED', '2025-03-13', '2025-03-07', '2025-02-02 00:00:00', '2025-07-29 11:03:30', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'PIX', NULL, 'https://www.asaas.com/i/jnd0zxxb4e9aszx1', NULL, 'sub_a9g0wq5bypr3lt2h'),
(60720, 'pay_2z7ofi609sbn3ze7', 188, 30.63, 'RECEIVED', '2025-03-10', '2025-03-25', '2025-02-01 00:00:00', '2025-07-29 11:03:32', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'PIX', NULL, 'https://www.asaas.com/i/2z7ofi609sbn3ze7', NULL, 'sub_sh9rki6ysvvln4kx'),
(60723, 'pay_1rfzxkaal63atyf4', 189, 29.90, 'OVERDUE', '2025-03-10', NULL, '2025-02-01 00:00:00', '2025-07-29 11:03:33', 'Plano de Hospedagem e Manutenção', 'UNDEFINED', NULL, 'https://www.asaas.com/i/1rfzxkaal63atyf4', NULL, 'sub_6usu4i2bhas9vb5s'),
(60725, 'pay_9jf4hv6t2limfgml', 206, 29.90, 'RECEIVED', '2025-03-10', '2025-03-10', '2025-02-01 00:00:00', '2025-07-29 11:03:35', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/9jf4hv6t2limfgml', NULL, 'sub_b1fy6en75pgrzeoi'),
(60728, 'pay_nqdxjt9fza5nihm8', 195, 29.90, 'OVERDUE', '2025-03-10', NULL, '2025-02-01 00:00:00', '2025-07-29 11:03:37', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/nqdxjt9fza5nihm8', NULL, 'sub_f7jb5gycxnddsmje'),
(60731, 'pay_69cxg0v3cv2qg34u', 209, 29.90, 'OVERDUE', '2025-03-10', NULL, '2025-02-01 00:00:00', '2025-07-29 11:03:38', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/69cxg0v3cv2qg34u', NULL, 'sub_sp6cmivnmdkdywlg'),
(60734, 'pay_6lkiojawnja0zun9', 212, 29.90, 'RECEIVED', '2025-03-12', '2025-03-19', '2025-02-01 00:00:00', '2025-07-29 11:03:40', 'Plano Hospedagem e Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/6lkiojawnja0zun9', NULL, 'sub_om5xop9rx7t8t1yh'),
(60737, 'pay_f2px92t5dim5ezro', 217, 29.90, 'OVERDUE', '2025-03-10', NULL, '2025-02-01 00:00:00', '2025-07-29 11:03:41', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/f2px92t5dim5ezro', NULL, 'sub_mlmp5a0sid5xyb5n'),
(60740, 'pay_ihqvst3ybgz7t41j', 235, 29.90, 'RECEIVED', '2025-03-10', '2025-03-10', '2025-02-01 00:00:00', '2025-07-29 11:03:43', 'Plano Hospedagem e Manutenção ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/ihqvst3ybgz7t41j', NULL, 'sub_ijill2r7k1rrfo9s'),
(60743, 'pay_kk0cesuig5o0zwt1', 199, 29.90, 'RECEIVED_IN_CASH', '2025-03-10', '2025-04-25', '2025-02-01 00:00:00', '2025-07-29 11:03:44', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/kk0cesuig5o0zwt1', NULL, 'sub_m3c69vzc322xsk1x'),
(60746, 'pay_cvqrbn12dt3j38d1', 234, 29.90, 'RECEIVED_IN_CASH', '2025-03-10', '2025-04-08', '2025-02-01 00:00:00', '2025-07-29 11:03:46', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/cvqrbn12dt3j38d1', NULL, 'sub_5zhf56f9xgaopx3x'),
(60749, 'pay_8bve34tj8jth0ps1', 201, 30.63, 'RECEIVED', '2025-03-10', '2025-03-25', '2025-02-01 00:00:00', '2025-07-29 11:03:47', 'PLano de Manutenção e Hospedagem ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/8bve34tj8jth0ps1', NULL, 'sub_duqxrig3fiaq14pb'),
(60752, 'pay_j0zjcsfm8unst58b', 224, 29.90, 'RECEIVED', '2025-03-10', '2025-03-08', '2025-02-01 00:00:00', '2025-07-29 11:03:49', 'Plano de Hespedagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/j0zjcsfm8unst58b', NULL, 'sub_906jjzs1wiwlv535'),
(60755, 'pay_p7orixwtmdqgxbxd', 227, 29.90, 'RECEIVED', '2025-03-10', '2025-03-10', '2025-02-01 00:00:00', '2025-07-29 11:03:51', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/p7orixwtmdqgxbxd', NULL, 'sub_uof9cualj3j78zmc'),
(60757, 'pay_ywhip528an6djg1r', 228, 40.70, 'RECEIVED', '2025-03-10', '2025-03-11', '2025-02-01 00:00:00', '2025-07-29 11:03:52', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/ywhip528an6djg1r', NULL, 'sub_laflhqry577ok0o2'),
(60759, 'pay_0ohotmcfy0mt387r', 232, 31.12, 'RECEIVED', '2025-03-10', '2025-05-13', '2025-02-01 00:00:00', '2025-07-29 11:03:54', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'PIX', NULL, 'https://www.asaas.com/i/0ohotmcfy0mt387r', NULL, 'sub_dhl2ubdq46degqsc'),
(60761, 'pay_6pamnh65ccrik5i4', 240, 30.72, 'RECEIVED', '2025-03-10', '2025-04-03', '2025-02-01 00:00:00', '2025-07-29 11:03:55', 'Plano ESSENCIAL Hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/6pamnh65ccrik5i4', NULL, 'sub_1p83yf64ebdz09eh'),
(60763, 'pay_gb5ib1i6a10iqazx', 225, 29.90, 'RECEIVED_IN_CASH', '2025-03-10', '2025-04-11', '2025-02-01 00:00:00', '2025-07-29 11:03:57', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/gb5ib1i6a10iqazx', NULL, 'sub_onezkhx9uo06h1we'),
(60765, 'pay_6vkm7bg9gslg74es', 257, 29.90, 'OVERDUE', '2025-03-12', NULL, '2025-02-01 00:00:00', '2025-07-29 11:03:58', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/6vkm7bg9gslg74es', NULL, 'sub_ju5bkk8q06x0vid9'),
(60767, 'pay_u8sdt9bmx5cwzgm0', 258, 39.90, 'OVERDUE', '2025-03-12', NULL, '2025-02-01 00:00:00', '2025-07-29 11:04:00', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/u8sdt9bmx5cwzgm0', NULL, 'sub_feo8zvrsmaf5q1tq'),
(60769, 'pay_1gsyvxy8vk7hou6s', 263, 29.90, 'RECEIVED', '2025-03-10', '2025-03-05', '2025-01-31 00:00:00', '2025-07-29 11:04:02', '', 'PIX', NULL, 'https://www.asaas.com/i/1gsyvxy8vk7hou6s', NULL, 'sub_re7f5cvc2t4u6fuo'),
(60771, 'pay_w2arjj32lzungemz', 275, 29.90, 'RECEIVED_IN_CASH', '2025-03-10', '2025-03-19', '2025-01-31 00:00:00', '2025-07-29 11:04:03', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/w2arjj32lzungemz', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60773, 'pay_o1g96hvg2bq1xz6a', 185, 50.97, 'RECEIVED', '2025-02-28', '2025-03-05', '2025-01-29 00:00:00', '2025-07-29 11:04:05', 'Plano ImobSites ', 'BOLETO', NULL, 'https://www.asaas.com/i/o1g96hvg2bq1xz6a', NULL, 'sub_0u0hiabbslpiov8l'),
(60775, 'pay_e0dqu3n1dxskkbsj', 183, 273.00, 'CONFIRMED', '2025-06-30', NULL, '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 6 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/e0dqu3n1dxskkbsj', '6', NULL),
(60777, 'pay_nss412vh27cv1akv', 183, 273.00, 'RECEIVED', '2025-05-30', '2025-07-08', '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 5 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/nss412vh27cv1akv', '5', NULL),
(60779, 'pay_wjrnohlq31ssz45r', 183, 273.00, 'RECEIVED', '2025-04-30', '2025-06-06', '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 4 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/wjrnohlq31ssz45r', '4', NULL);
INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(60781, 'pay_8v5eiq09vcxgfdyx', 183, 273.00, 'RECEIVED', '2025-03-30', '2025-05-05', '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/8v5eiq09vcxgfdyx', '3', NULL),
(60783, 'pay_lr761su2cy0fiair', 183, 273.00, 'RECEIVED', '2025-02-28', '2025-04-03', '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/lr761su2cy0fiair', '2', NULL),
(60785, 'pay_cepg32o1rmhagdln', 183, 273.00, 'RECEIVED', '2025-01-30', '2025-03-05', '2025-01-29 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 6. Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/cepg32o1rmhagdln', '1', NULL),
(60787, 'pay_q8ok63al6s56zwat', 183, 702.00, 'RECEIVED', '2025-01-30', '2025-01-29', '2025-01-29 00:00:00', '2025-07-29 11:04:16', 'Desenvolvimento WEB ', 'PIX', NULL, 'https://www.asaas.com/i/q8ok63al6s56zwat', NULL, NULL),
(60788, 'pay_vbsf413mnszz2z19', 186, 319.90, 'RECEIVED', '2025-01-30', '2025-01-31', '2025-01-29 00:00:00', '2025-07-29 11:04:17', 'Desenvolvimento WEB ', 'PIX', NULL, 'https://www.asaas.com/i/vbsf413mnszz2z19', NULL, NULL),
(60790, 'pay_m0zbrplf13tb3r4l', 190, 89.90, 'RECEIVED', '2025-01-30', '2025-01-30', '2025-01-29 00:00:00', '2025-07-29 11:04:19', 'Designer Gráfico (identidade visual e branding) | Cartão Digital', 'PIX', NULL, 'https://www.asaas.com/i/m0zbrplf13tb3r4l', NULL, NULL),
(60792, 'pay_grw4umhofn542pxu', 185, 234.65, 'RECEIVED', '2025-01-30', '2025-01-29', '2025-01-29 00:00:00', '2025-07-29 11:04:20', 'Implantação Plataforma ImobSite para Corretores e Imobiliárias\r\nIdentidade Visual', 'BOLETO', NULL, 'https://www.asaas.com/i/grw4umhofn542pxu', NULL, NULL),
(60794, 'pay_7qgf0pk0001wzf1j', 4293, 250.00, 'RECEIVED', '2025-01-28', '2025-01-28', '2025-01-28 00:00:00', '2025-07-29 11:04:22', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/7qgf0pk0001wzf1j', NULL, NULL),
(60796, 'pay_nai6ehdfhltuiq0b', 279, 49.90, 'RECEIVED', '2025-03-05', '2025-03-05', '2025-01-25 00:00:00', '2025-07-29 11:04:23', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/nai6ehdfhltuiq0b', NULL, 'sub_htfllf6l0cu6s4qk'),
(60798, 'pay_j150up5i1dgdyzch', 187, 338.70, 'RECEIVED', '2025-03-26', '2025-03-26', '2025-01-22 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 3. Criação e Desenvolvimento Web | Site Institucional. Incluso R$ 99,90 configuração Sistema de Filtros Dinâmicos', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/j150up5i1dgdyzch', '3', NULL),
(60800, 'pay_ruojl05aymjqvmsn', 187, 271.05, 'RECEIVED', '2025-02-24', '2025-02-25', '2025-01-22 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 3. Criação e Desenvolvimento Web | Site Institucional', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ruojl05aymjqvmsn', '2', NULL),
(60802, 'pay_w6zlvmpixarcetvn', 187, 265.66, 'RECEIVED', '2025-01-24', '2025-01-23', '2025-01-22 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 3. Criação e Desenvolvimento Web | Site Institucional', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/w6zlvmpixarcetvn', '1', NULL),
(60804, 'pay_6f4qq1h2hhk3cd8l', 188, 30.64, 'RECEIVED', '2025-02-10', '2025-02-26', '2025-01-22 00:00:00', '2025-07-29 11:04:30', 'Plano de E-mail e Hospedagem Mensal + Suporte/Manutenção Periódica', 'PIX', NULL, 'https://www.asaas.com/i/6f4qq1h2hhk3cd8l', NULL, 'sub_sh9rki6ysvvln4kx'),
(60806, 'pay_tp5zlpgk1nvr4th1', 220, 29.90, 'RECEIVED', '2025-02-28', '2025-02-28', '2025-01-20 00:00:00', '2025-07-29 11:04:31', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/tp5zlpgk1nvr4th1', NULL, 'sub_0ob5gqdk6bgm9evj'),
(60808, 'pay_1bcpkthsuoc5nbrh', 221, 29.90, 'OVERDUE', '2025-02-28', NULL, '2025-01-20 00:00:00', '2025-07-29 11:04:33', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/1bcpkthsuoc5nbrh', NULL, 'sub_xahjz6ws9fumwnl9'),
(60810, 'pay_bcd7nw74k4qjmqxv', 236, 29.90, 'RECEIVED_IN_CASH', '2025-02-28', '2025-03-18', '2025-01-20 00:00:00', '2025-07-29 11:04:34', 'Plano Hospedagem e Manutenção Site', 'BOLETO', NULL, 'https://www.asaas.com/i/bcd7nw74k4qjmqxv', NULL, 'sub_bwvnv3t5548q79az'),
(60812, 'pay_1nfpimbmwmmp9jcg', 267, 29.00, 'RECEIVED', '2025-02-28', '2025-02-19', '2025-01-20 00:00:00', '2025-07-29 11:04:36', '', 'PIX', NULL, 'https://www.asaas.com/i/1nfpimbmwmmp9jcg', NULL, 'sub_504ksi201k6033es'),
(60814, 'pay_girxrdi00vl6xiw6', 189, 30.64, 'RECEIVED', '2025-02-10', '2025-02-26', '2025-01-17 00:00:00', '2025-07-29 11:04:37', 'Plano de Hospedagem e Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/girxrdi00vl6xiw6', NULL, 'sub_6usu4i2bhas9vb5s'),
(60816, 'pay_rjnaegzhg4l4jfbe', 259, 149.50, 'RECEIVED', '2025-01-20', '2025-01-17', '2025-01-17 00:00:00', '2025-07-29 11:04:39', 'Referente cobrança plano de hospedagem dos meses 09, 10, 11 e 12 de 2024, além de 01/2025. Valor de R$ 29,90 mensal.', 'BOLETO', NULL, 'https://www.asaas.com/i/rjnaegzhg4l4jfbe', NULL, NULL),
(60818, 'pay_487gssqmo6za03y3', 266, 152.48, 'RECEIVED', '2025-01-20', '2025-02-04', '2025-01-17 00:00:00', '2025-07-29 11:04:40', 'Referente plano de hospedagem dos meses de 09, 10, 11 e 12 de 2024, além de 01/2025 no valor mensal de R$ 29,90.', 'PIX', NULL, 'https://www.asaas.com/i/487gssqmo6za03y3', NULL, NULL),
(60820, 'pay_kt1bhdbpf7zy9xzr', 190, 425.60, 'RECEIVED_IN_CASH', '2025-03-20', '2025-05-19', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 3. Criação e Desenvolvimento WebSite\r\nIdentidade Visual (logo)', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/kt1bhdbpf7zy9xzr', '3', NULL),
(60822, 'pay_oeqy2cljwiljpelx', 190, 436.09, 'RECEIVED', '2025-02-20', '2025-03-06', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 3. Criação e Desenvolvimento WebSite\r\nIdentidade Visual (logo)', 'PIX', 'PIX', 'https://www.asaas.com/i/oeqy2cljwiljpelx', '2', NULL),
(60824, 'pay_wquf0fy1xqj96syu', 190, 425.60, 'RECEIVED', '2025-01-20', '2025-01-20', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 3. Criação e Desenvolvimento WebSite\r\nIdentidade Visual (logo)', 'PIX', 'PIX', 'https://www.asaas.com/i/wquf0fy1xqj96syu', '1', NULL),
(60826, 'pay_nj66ttwfb9unswoz', 190, 29.90, 'RECEIVED', '2025-02-17', '2025-02-17', '2025-01-17 00:00:00', '2025-07-29 11:04:46', 'Plano de Hospedagem e Manutenção Essencial', 'PIX', NULL, 'https://www.asaas.com/i/nj66ttwfb9unswoz', NULL, 'sub_g3gra67j24pjeh52'),
(60828, 'pay_fl8ijxjeup1phki3', 191, 29.90, 'RECEIVED', '2025-02-17', '2025-02-17', '2025-01-17 00:00:00', '2025-07-29 11:04:48', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/fl8ijxjeup1phki3', NULL, 'sub_sulll48m6obfa7ze'),
(60830, 'pay_quuqajctreu1l7mz', 191, 116.20, 'CONFIRMED', '2025-06-17', NULL, '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 6 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/quuqajctreu1l7mz', '6', NULL),
(60832, 'pay_kakiayqyx2jah16q', 191, 116.16, 'RECEIVED', '2025-05-17', '2025-06-30', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 5 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/kakiayqyx2jah16q', '5', NULL),
(60834, 'pay_85qp1dvvg4vt870f', 191, 116.16, 'RECEIVED', '2025-04-17', '2025-05-28', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 4 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/85qp1dvvg4vt870f', '4', NULL),
(60835, 'pay_jw2l15citnc5goza', 191, 116.16, 'RECEIVED', '2025-03-17', '2025-04-28', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 3 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/jw2l15citnc5goza', '3', NULL),
(60836, 'pay_5pqakhh78juof1bp', 191, 116.16, 'RECEIVED', '2025-02-17', '2025-03-25', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 2 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/5pqakhh78juof1bp', '2', NULL),
(60837, 'pay_ngwu6jw62qh36gur', 191, 116.16, 'RECEIVED', '2025-01-17', '2025-02-21', '2025-01-17 00:00:00', '2025-07-29 07:56:33', 'Parcela 1 de 6. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ngwu6jw62qh36gur', '1', NULL),
(60838, 'pay_a1x7vo369jd30l1k', 245, 29.90, 'RECEIVED_IN_CASH', '2025-02-25', '2025-02-25', '2025-01-17 00:00:00', '2025-07-29 11:04:59', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/a1x7vo369jd30l1k', NULL, 'sub_f2zgsu6ds7fskv75'),
(60839, 'pay_fm9soclcun0gy20d', 268, 29.90, 'OVERDUE', '2025-02-25', NULL, '2025-01-17 00:00:00', '2025-07-29 11:05:00', '', 'BOLETO', NULL, 'https://www.asaas.com/i/fm9soclcun0gy20d', NULL, 'sub_lzkw4ntgewkejb6n'),
(60840, 'pay_jy2ev367hl6p6slb', 284, 29.90, 'RECEIVED', '2025-02-25', '2025-02-25', '2025-01-17 00:00:00', '2025-07-29 11:05:02', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/jy2ev367hl6p6slb', NULL, 'sub_khpfl1dpc6pfj9ix'),
(60845, 'pay_m9sg93hrzy84idum', 192, 597.00, 'RECEIVED', '2025-01-15', '2025-01-15', '2025-01-15 00:00:00', '2025-07-29 11:05:06', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/m9sg93hrzy84idum', NULL, NULL),
(60847, 'pay_av25jn4rjaria0pm', 202, 30.50, 'RECEIVED', '2025-02-23', '2025-02-25', '2025-01-15 00:00:00', '2025-07-29 11:05:07', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/av25jn4rjaria0pm', NULL, 'sub_5taox270k3eejldi'),
(60849, 'pay_2nmlfczifinat6ui', 271, 39.90, 'OVERDUE', '2025-02-23', NULL, '2025-01-15 00:00:00', '2025-07-29 11:05:09', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/2nmlfczifinat6ui', NULL, 'sub_1dx95biw49q1zskp'),
(60851, 'pay_qqtfimugjfzqrfd7', 251, 29.90, 'RECEIVED', '2025-02-21', '2025-02-06', '2025-01-13 00:00:00', '2025-07-29 11:05:10', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/qqtfimugjfzqrfd7', NULL, 'sub_fwc0baa1kthqsncu'),
(60853, 'pay_3ryrqc70u2i8opzl', 253, 29.90, 'RECEIVED', '2025-02-19', '2025-02-19', '2025-01-11 00:00:00', '2025-07-29 11:05:12', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/3ryrqc70u2i8opzl', NULL, 'sub_3z72l1acpglcr1fa'),
(60855, 'pay_twesxjsfvqe5lgx3', 237, 30.32, 'RECEIVED', '2025-02-18', '2025-02-25', '2025-01-10 00:00:00', '2025-07-29 11:05:13', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/twesxjsfvqe5lgx3', NULL, 'sub_tjd6ivet6h510mad'),
(60857, 'pay_injb6i1j2b25d7w1', 196, 30.51, 'RECEIVED', '2025-02-15', '2025-02-18', '2025-01-07 00:00:00', '2025-07-29 11:05:15', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/injb6i1j2b25d7w1', NULL, 'sub_lul4v892i4z7gsnz'),
(60859, 'pay_1ta16lhadfpv7vl5', 254, 29.90, 'RECEIVED', '2025-02-15', '2025-02-17', '2025-01-07 00:00:00', '2025-07-29 11:05:16', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/1ta16lhadfpv7vl5', NULL, 'sub_oil9ich37nuhoes7'),
(60861, 'pay_t6yd57cxtdpmq2v3', 286, 49.90, 'RECEIVED_IN_CASH', '2025-02-15', '2025-03-21', '2025-01-07 00:00:00', '2025-07-29 11:05:18', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/t6yd57cxtdpmq2v3', NULL, 'sub_fte624var188bxrz'),
(60863, 'pay_kqs4xe5usq26f81j', 246, 30.00, 'RECEIVED', '2025-01-07', '2025-01-07', '2025-01-07 00:00:00', '2025-07-29 11:05:19', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/kqs4xe5usq26f81j', NULL, NULL),
(60865, 'pay_y9687aeyfs4o7pxh', 246, 150.00, 'RECEIVED', '2025-01-07', '2025-01-07', '2025-01-07 00:00:00', '2025-07-29 11:05:21', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/y9687aeyfs4o7pxh', NULL, NULL),
(60867, 'pay_ulc37wepde0mi9if', 246, 3135.30, 'RECEIVED', '2025-01-07', '2025-01-07', '2025-01-07 00:00:00', '2025-07-29 11:05:22', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ulc37wepde0mi9if', NULL, NULL),
(60869, 'pay_1xql04ku8itig7sl', 200, 238.60, 'PENDING', '2026-01-10', NULL, '2025-01-06 00:00:00', '2025-07-29 11:05:24', 'Plano Anual Hospedagem ', 'UNDEFINED', NULL, 'https://www.asaas.com/i/1xql04ku8itig7sl', NULL, 'sub_ltrsr65djubpcre4'),
(60871, 'pay_t49nibg13c09vyde', 200, 20.00, 'CONFIRMED', '2025-12-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 12 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/t49nibg13c09vyde', '12', NULL),
(60873, 'pay_hjfolffsn0hcpy6r', 200, 19.90, 'CONFIRMED', '2025-11-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 11 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/hjfolffsn0hcpy6r', '11', NULL),
(60875, 'pay_rfglgy0g3up7696t', 200, 19.90, 'CONFIRMED', '2025-10-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 10 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/rfglgy0g3up7696t', '10', NULL),
(60876, 'pay_y4nk0xz7hcvwql6n', 200, 19.90, 'CONFIRMED', '2025-09-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 9 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/y4nk0xz7hcvwql6n', '9', NULL),
(60878, 'pay_bc8rpskf5lzex6cs', 200, 19.90, 'CONFIRMED', '2025-08-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 8 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/bc8rpskf5lzex6cs', '8', NULL),
(60880, 'pay_zc4ak07memkk4pka', 200, 19.90, 'CONFIRMED', '2025-07-10', NULL, '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 7 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/zc4ak07memkk4pka', '7', NULL),
(60882, 'pay_pbgygvuts1aoi4mm', 200, 19.90, 'RECEIVED', '2025-06-10', '2025-07-17', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 6 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/pbgygvuts1aoi4mm', '6', NULL),
(60884, 'pay_uxrowltl3jzqdip2', 200, 19.90, 'RECEIVED', '2025-05-10', '2025-06-16', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 5 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/uxrowltl3jzqdip2', '5', NULL),
(60886, 'pay_0im3lailnlxbuvh4', 200, 19.90, 'RECEIVED', '2025-04-10', '2025-05-14', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 4 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/0im3lailnlxbuvh4', '4', NULL),
(60888, 'pay_y4z53rdysen0i6ls', 200, 19.90, 'RECEIVED', '2025-03-10', '2025-04-14', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/y4z53rdysen0i6ls', '3', NULL),
(60890, 'pay_wla8p34gkmpe9u5q', 200, 19.90, 'RECEIVED', '2025-02-10', '2025-03-11', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/wla8p34gkmpe9u5q', '2', NULL),
(60892, 'pay_i9aa0s05obsxa0xa', 200, 19.90, 'RECEIVED', '2025-01-10', '2025-02-07', '2025-01-06 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 12. Ref. Plano Manutenção e Hospedagem', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/i9aa0s05obsxa0xa', '1', NULL),
(60894, 'pay_57dnokkjypa4j8qh', 256, 29.90, 'RECEIVED', '2025-02-13', '2025-02-04', '2025-01-05 00:00:00', '2025-07-29 11:05:44', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/57dnokkjypa4j8qh', NULL, 'sub_a9g0wq5bypr3lt2h'),
(60896, 'pay_3t3nykj1i0q5ab1q', 206, 29.90, 'RECEIVED', '2025-02-10', '2025-02-10', '2025-01-04 00:00:00', '2025-07-29 11:05:45', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/3t3nykj1i0q5ab1q', NULL, 'sub_b1fy6en75pgrzeoi'),
(60898, 'pay_n1nyoc7rfl063ay4', 195, 29.90, 'OVERDUE', '2025-02-10', NULL, '2025-01-04 00:00:00', '2025-07-29 11:05:47', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/n1nyoc7rfl063ay4', NULL, 'sub_f7jb5gycxnddsmje'),
(60900, 'pay_9yxxgmr4ykles2rr', 209, 29.90, 'OVERDUE', '2025-02-10', NULL, '2025-01-04 00:00:00', '2025-07-29 11:05:48', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/9yxxgmr4ykles2rr', NULL, 'sub_sp6cmivnmdkdywlg'),
(60902, 'pay_66dmij258icx312w', 217, 29.90, 'OVERDUE', '2025-02-10', NULL, '2025-01-04 00:00:00', '2025-07-29 11:05:50', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/66dmij258icx312w', NULL, 'sub_mlmp5a0sid5xyb5n'),
(60904, 'pay_r64bjlhq8on0n4eq', 235, 29.90, 'RECEIVED', '2025-02-10', '2025-02-05', '2025-01-04 00:00:00', '2025-07-29 11:05:51', 'Plano Hospedagem e Manutenção ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/r64bjlhq8on0n4eq', NULL, 'sub_ijill2r7k1rrfo9s'),
(60906, 'pay_5idf9xrb8oxxucc6', 199, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-04-25', '2025-01-04 00:00:00', '2025-07-29 11:05:53', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/5idf9xrb8oxxucc6', NULL, 'sub_m3c69vzc322xsk1x'),
(60908, 'pay_znr6oq98dtps2wrf', 234, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-04-08', '2025-01-04 00:00:00', '2025-07-29 11:05:54', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/znr6oq98dtps2wrf', NULL, 'sub_5zhf56f9xgaopx3x'),
(60910, 'pay_dldj8f65k7jo0sst', 201, 29.90, 'RECEIVED', '2025-02-10', '2025-02-10', '2025-01-04 00:00:00', '2025-07-29 11:05:56', 'PLano de Manutenção e Hospedagem ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/dldj8f65k7jo0sst', NULL, 'sub_duqxrig3fiaq14pb'),
(60912, 'pay_8u5j6ziyxo3r4eta', 224, 29.90, 'RECEIVED', '2025-02-10', '2025-02-06', '2025-01-04 00:00:00', '2025-07-29 11:05:57', 'Plano de Hespedagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/8u5j6ziyxo3r4eta', NULL, 'sub_906jjzs1wiwlv535'),
(60914, 'pay_j5qj3lrx6vljkd54', 227, 29.90, 'RECEIVED', '2025-02-10', '2025-02-10', '2025-01-04 00:00:00', '2025-07-29 11:05:59', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'PIX', NULL, 'https://www.asaas.com/i/j5qj3lrx6vljkd54', NULL, 'sub_uof9cualj3j78zmc'),
(60917, 'pay_1gcs3go7f7hvtr7d', 228, 40.70, 'RECEIVED', '2025-02-13', '2025-02-14', '2025-01-04 00:00:00', '2025-07-29 11:06:00', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/1gcs3go7f7hvtr7d', NULL, 'sub_laflhqry577ok0o2'),
(60920, 'pay_cdpdeg996fe9mv6p', 232, 30.79, 'RECEIVED', '2025-02-10', '2025-03-13', '2025-01-04 00:00:00', '2025-07-29 11:06:02', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/cdpdeg996fe9mv6p', NULL, 'sub_dhl2ubdq46degqsc'),
(60923, 'pay_6lnsdm5ppcb5sm7m', 240, 30.71, 'RECEIVED', '2025-02-10', '2025-03-05', '2025-01-04 00:00:00', '2025-07-29 11:06:03', 'Plano ESSENCIAL Hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/6lnsdm5ppcb5sm7m', NULL, 'sub_1p83yf64ebdz09eh'),
(60926, 'pay_g80vved01z9p4vj8', 225, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-04-11', '2025-01-04 00:00:00', '2025-07-29 11:06:05', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/g80vved01z9p4vj8', NULL, 'sub_onezkhx9uo06h1we'),
(60929, 'pay_hu1ypzdhjgrl7139', 215, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-03-19', '2025-01-04 00:00:00', '2025-07-29 11:06:07', 'Plano de Hospedagem Essencial para Sites', 'UNDEFINED', NULL, 'https://www.asaas.com/i/hu1ypzdhjgrl7139', NULL, 'sub_92c8ucrplnbrar5g'),
(60932, 'pay_jykyzrt5j8m5ne63', 257, 29.90, 'OVERDUE', '2025-02-12', NULL, '2025-01-04 00:00:00', '2025-07-29 11:06:08', 'Serviço de Manutenção e Hospedagem WebSite', 'UNDEFINED', NULL, 'https://www.asaas.com/i/jykyzrt5j8m5ne63', NULL, 'sub_ju5bkk8q06x0vid9'),
(60934, 'pay_402izd2j1zfr1nr0', 258, 39.90, 'RECEIVED_IN_CASH', '2025-02-12', '2025-04-09', '2025-01-04 00:00:00', '2025-07-29 11:06:10', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/402izd2j1zfr1nr0', NULL, 'sub_feo8zvrsmaf5q1tq'),
(60937, 'pay_7tb6r92xqcga7en6', 263, 29.90, 'RECEIVED', '2025-02-10', '2025-02-03', '2025-01-03 00:00:00', '2025-07-29 11:06:11', '', 'PIX', NULL, 'https://www.asaas.com/i/7tb6r92xqcga7en6', NULL, 'sub_re7f5cvc2t4u6fuo'),
(60940, 'pay_okg13g0bicub6uc7', 275, 29.90, 'RECEIVED_IN_CASH', '2025-02-10', '2025-03-19', '2025-01-03 00:00:00', '2025-07-29 11:06:13', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/okg13g0bicub6uc7', NULL, 'sub_d9vxnk8na9bb4e4b'),
(60943, 'pay_zusytgioqo1p2ckc', 279, 49.90, 'RECEIVED', '2025-02-05', '2025-01-25', '2024-12-28 00:00:00', '2025-07-29 11:06:14', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/zusytgioqo1p2ckc', NULL, 'sub_htfllf6l0cu6s4qk'),
(60945, 'pay_8jaut1mhkk0sh3nn', 246, 1050.00, 'RECEIVED', '2024-12-23', '2024-12-23', '2024-12-23 00:00:00', '2025-07-29 11:06:16', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/8jaut1mhkk0sh3nn', NULL, NULL),
(60948, 'pay_voohrmvu3jva2mmz', 220, 29.90, 'RECEIVED', '2025-01-29', '2025-01-20', '2024-12-21 00:00:00', '2025-07-29 11:06:17', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/voohrmvu3jva2mmz', NULL, 'sub_0ob5gqdk6bgm9evj'),
(60951, 'pay_svdxaqi5lqa8nwl1', 267, 29.00, 'RECEIVED', '2025-01-29', '2025-01-14', '2024-12-21 00:00:00', '2025-07-29 11:06:19', '', 'PIX', NULL, 'https://www.asaas.com/i/svdxaqi5lqa8nwl1', NULL, 'sub_504ksi201k6033es'),
(60954, 'pay_cm4b67vwc721s3s0', 221, 29.90, 'OVERDUE', '2025-01-28', NULL, '2024-12-20 00:00:00', '2025-07-29 11:06:20', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/cm4b67vwc721s3s0', NULL, 'sub_xahjz6ws9fumwnl9'),
(60957, 'pay_opngxcdu357gspfc', 236, 30.62, 'RECEIVED', '2025-01-28', '2025-02-11', '2024-12-20 00:00:00', '2025-07-29 11:06:22', 'Plano Hospedagem e Manutenção Site', 'PIX', NULL, 'https://www.asaas.com/i/opngxcdu357gspfc', NULL, 'sub_bwvnv3t5548q79az'),
(60960, 'pay_srwir3blsh0tcgmj', 286, 200.00, 'RECEIVED_IN_CASH', '2025-01-28', '2025-03-21', '2024-12-20 00:00:00', '2025-07-29 11:06:23', 'Gestão Tráfego Pago ADS Local', 'BOLETO', NULL, 'https://www.asaas.com/i/srwir3blsh0tcgmj', NULL, 'sub_4o9clt62tbo925y4'),
(60963, 'pay_r27azr54xvn1fvit', 257, 119.00, 'RECEIVED', '2024-12-17', '2024-12-17', '2024-12-17 00:00:00', '2025-07-29 11:06:25', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/r27azr54xvn1fvit', NULL, NULL),
(60966, 'pay_3i90d1vkr2rg4mdi', 213, 470.00, 'RECEIVED', '2025-01-24', '2025-01-24', '2024-12-17 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/3i90d1vkr2rg4mdi', '1', NULL),
(60969, 'pay_azjaezj189u1lcpj', 245, 29.90, 'RECEIVED', '2025-01-25', '2025-01-18', '2024-12-17 00:00:00', '2025-07-29 11:06:28', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/azjaezj189u1lcpj', NULL, 'sub_f2zgsu6ds7fskv75'),
(60972, 'pay_j8e5bopecv15ze5p', 268, 29.90, 'OVERDUE', '2025-01-25', NULL, '2024-12-17 00:00:00', '2025-07-29 11:06:29', '', 'BOLETO', NULL, 'https://www.asaas.com/i/j8e5bopecv15ze5p', NULL, 'sub_lzkw4ntgewkejb6n'),
(60975, 'pay_dsvl3njq39mif6px', 284, 51.18, 'RECEIVED', '2025-01-25', '2025-02-12', '2024-12-17 00:00:00', '2025-07-29 11:06:31', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/dsvl3njq39mif6px', NULL, 'sub_khpfl1dpc6pfj9ix'),
(60978, 'pay_82bcp1vzc645eqc0', 202, 30.49, 'RECEIVED', '2025-01-23', '2025-01-24', '2024-12-15 00:00:00', '2025-07-29 11:06:32', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/82bcp1vzc645eqc0', NULL, 'sub_5taox270k3eejldi'),
(60981, 'pay_g6zi3clfjjsqllpu', 271, 39.90, 'OVERDUE', '2025-01-23', NULL, '2024-12-15 00:00:00', '2025-07-29 11:06:34', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/g6zi3clfjjsqllpu', NULL, 'sub_1dx95biw49q1zskp'),
(60984, 'pay_frcdttfi9nj32xcs', 251, 30.51, 'RECEIVED', '2025-01-21', '2025-01-24', '2024-12-13 00:00:00', '2025-07-29 11:06:35', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'PIX', NULL, 'https://www.asaas.com/i/frcdttfi9nj32xcs', NULL, 'sub_fwc0baa1kthqsncu'),
(60987, 'pay_6swioixq7saopzjs', 193, 237.20, 'RECEIVED', '2024-12-11', '2024-12-11', '2024-12-11 00:00:00', '2025-07-29 11:06:37', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/6swioixq7saopzjs', NULL, NULL),
(60990, 'pay_9niahvbmlf1u4zxu', 246, 43.00, 'RECEIVED', '2024-12-11', '2024-12-11', '2024-12-11 00:00:00', '2025-07-29 11:06:38', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/9niahvbmlf1u4zxu', NULL, NULL),
(60993, 'pay_psmmss0vac259qep', 206, 202.09, 'RECEIVED', '2025-03-20', '2025-03-27', '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/psmmss0vac259qep', '3', NULL),
(60996, 'pay_lgzxy1ut8qwgj61q', 206, 197.66, 'RECEIVED', '2025-02-20', '2025-02-20', '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/lgzxy1ut8qwgj61q', '2', NULL),
(60999, 'pay_qrmms1hqowsscea0', 206, 197.66, 'RECEIVED', '2025-01-20', '2025-01-20', '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/qrmms1hqowsscea0', '1', NULL),
(61002, 'pay_vp8wyuhknfxrezp7', 206, 593.00, 'RECEIVED', '2024-12-20', '2024-12-20', '2024-12-11 00:00:00', '2025-07-29 11:06:45', 'Criação e Desenvolvimento WEB', 'PIX', NULL, 'https://www.asaas.com/i/vp8wyuhknfxrezp7', NULL, NULL),
(61005, 'pay_ulynhvl2uwhfg5se', 206, 29.90, 'RECEIVED', '2025-01-10', '2025-01-10', '2024-12-11 00:00:00', '2025-07-29 11:06:46', 'Plano de Hospedagem e Manutenção ESESNCIAL', 'PIX', NULL, 'https://www.asaas.com/i/ulynhvl2uwhfg5se', NULL, 'sub_b1fy6en75pgrzeoi'),
(61008, 'pay_j71t2i824kaunf33', 195, 597.00, 'DUNNING_REQUESTED', '2025-01-13', NULL, '2024-12-11 00:00:00', '2025-07-29 11:06:48', 'Criação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/j71t2i824kaunf33', NULL, NULL),
(61011, 'pay_zet6hmpr9grwoftq', 195, 29.90, 'OVERDUE', '2025-01-10', NULL, '2024-12-11 00:00:00', '2025-07-29 11:06:49', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/zet6hmpr9grwoftq', NULL, 'sub_f7jb5gycxnddsmje'),
(61014, 'pay_648mfzaqys0emb77', 209, 296.50, 'OVERDUE', '2025-03-22', NULL, '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/648mfzaqys0emb77', '4', NULL),
(61016, 'pay_ghplitv633148smu', 209, 296.50, 'OVERDUE', '2025-02-22', NULL, '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ghplitv633148smu', '3', NULL),
(61019, 'pay_6awxuon7aofanvn3', 209, 296.50, 'DUNNING_REQUESTED', '2025-01-22', NULL, '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/6awxuon7aofanvn3', '2', NULL),
(61022, 'pay_yxqwlppgltowi67q', 209, 296.50, 'OVERDUE', '2025-04-22', NULL, '2024-12-11 00:00:00', '2025-07-29 07:56:35', 'Parcela 4 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/yxqwlppgltowi67q', '1', NULL),
(61025, 'pay_3gzgb8ay122nwtx6', 209, 29.90, 'OVERDUE', '2025-01-22', NULL, '2024-12-11 00:00:00', '2025-07-29 11:06:57', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/3gzgb8ay122nwtx6', NULL, 'sub_sp6cmivnmdkdywlg'),
(61028, 'pay_mgc1h081dfq5tlwy', 253, 29.90, 'RECEIVED', '2025-01-19', '2025-01-16', '2024-12-11 00:00:00', '2025-07-29 11:06:58', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/mgc1h081dfq5tlwy', NULL, 'sub_3z72l1acpglcr1fa'),
(61031, 'pay_gyjfdfa9xmz006jh', 205, 149.25, 'RECEIVED', '2025-03-17', '2025-03-17', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 4 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/gyjfdfa9xmz006jh', '4', NULL),
(61033, 'pay_ijng3j5jk28md3io', 205, 149.25, 'RECEIVED', '2025-02-17', '2025-02-14', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ijng3j5jk28md3io', '3', NULL),
(61036, 'pay_2yue7484zutkhr8g', 205, 149.25, 'RECEIVED', '2025-01-17', '2025-01-15', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/2yue7484zutkhr8g', '2', NULL),
(61039, 'pay_lglxcp53s4jmcip3', 205, 149.25, 'RECEIVED', '2024-12-17', '2024-12-13', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 4. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/lglxcp53s4jmcip3', '1', NULL),
(61042, 'pay_20zlj50k1pv6bz2m', 196, 199.00, 'RECEIVED', '2025-02-15', '2025-03-24', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/20zlj50k1pv6bz2m', '3', NULL),
(61045, 'pay_yjqsv4tqtkka669l', 196, 199.00, 'RECEIVED', '2025-01-15', '2025-02-18', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/yjqsv4tqtkka669l', '2', NULL),
(61048, 'pay_shpw94zywii0apgo', 196, 199.00, 'RECEIVED', '2024-12-15', '2025-01-17', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/shpw94zywii0apgo', '1', NULL),
(61051, 'pay_6dw42kebtbr5njt8', 196, 29.90, 'RECEIVED', '2025-01-15', '2025-01-15', '2024-12-10 00:00:00', '2025-07-29 11:07:10', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/6dw42kebtbr5njt8', NULL, 'sub_lul4v892i4z7gsnz'),
(61054, 'pay_uz5mlpdmzh7tfe2f', 217, 597.00, 'DUNNING_REQUESTED', '2025-02-10', NULL, '2024-12-10 00:00:00', '2025-07-29 11:07:12', '***SUJEITO A PROTESTO**\r\nCriação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/uz5mlpdmzh7tfe2f', NULL, NULL),
(61057, 'pay_nzwir1vek7uqr3hd', 217, 29.90, 'OVERDUE', '2025-01-15', NULL, '2024-12-10 00:00:00', '2025-07-29 11:07:13', 'Plano de Hospedagem e Manutenção ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/nzwir1vek7uqr3hd', NULL, 'sub_mlmp5a0sid5xyb5n'),
(61060, 'pay_bcp1yszzu8qvk4bs', 235, 29.90, 'RECEIVED', '2025-01-10', '2025-01-08', '2024-12-10 00:00:00', '2025-07-29 11:07:15', 'Plano Hospedagem e Manutenção ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/bcp1yszzu8qvk4bs', NULL, 'sub_ijill2r7k1rrfo9s'),
(61063, 'pay_wjd5g5hqz8f0j2cy', 235, 1590.00, 'RECEIVED', '2024-12-12', '2024-12-12', '2024-12-10 00:00:00', '2025-07-29 11:07:16', 'Criação e Desenvolvimento WEB', 'PIX', NULL, 'https://www.asaas.com/i/wjd5g5hqz8f0j2cy', NULL, NULL),
(61066, 'pay_lbiw98tskx5a7itr', 197, 597.00, 'RECEIVED', '2024-12-20', '2024-12-20', '2024-12-10 00:00:00', '2025-07-29 11:07:18', 'Criação e Desenvolvimento Web', 'BOLETO', NULL, 'https://www.asaas.com/i/lbiw98tskx5a7itr', NULL, NULL),
(61069, 'pay_z1hm7kl1knva3ysz', 197, 299.00, 'RECEIVED', '2025-04-01', '2025-04-01', '2024-12-10 00:00:00', '2025-07-29 11:07:19', 'Plano de Hospedagem e Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/z1hm7kl1knva3ysz', NULL, 'sub_4as3ey72lfbf5pz7'),
(61072, 'pay_94sa86ov6enpg4mf', 199, 237.20, 'OVERDUE', '2025-04-10', NULL, '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 5 de 5. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/94sa86ov6enpg4mf', '5', NULL),
(61075, 'pay_9riegbacxnhah0x3', 199, 237.20, 'OVERDUE', '2025-03-10', NULL, '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 4 de 5. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/9riegbacxnhah0x3', '4', NULL),
(61078, 'pay_oaq2pnkjfze6fkhb', 199, 237.20, 'OVERDUE', '2025-02-10', NULL, '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 5. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/oaq2pnkjfze6fkhb', '3', NULL),
(61081, 'pay_ugkw5479w9p3yzgq', 199, 242.33, 'RECEIVED', '2025-01-10', '2025-01-15', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 5. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/ugkw5479w9p3yzgq', '2', NULL),
(61084, 'pay_64mvtcjz4wqury4u', 199, 237.20, 'RECEIVED_IN_CASH', '2024-12-10', '2024-12-12', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 5. Criação e Desenvolvimento WEB', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/64mvtcjz4wqury4u', '1', NULL),
(61087, 'pay_ms8j3vmwgvx3y4pq', 199, 29.90, 'RECEIVED_IN_CASH', '2025-01-10', '2025-04-25', '2024-12-10 00:00:00', '2025-07-29 11:07:29', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ms8j3vmwgvx3y4pq', NULL, 'sub_m3c69vzc322xsk1x'),
(61090, 'pay_mhioaexnvldlnku1', 234, 349.00, 'RECEIVED_IN_CASH', '2025-02-12', '2025-04-08', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 3 de 3. Criação e Desenvolvimento WEB e Identidade Visual', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/mhioaexnvldlnku1', '3', NULL),
(61093, 'pay_h1ryc1u7us7rh5l5', 234, 356.21, 'RECEIVED', '2025-01-12', '2025-01-14', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 3. Criação e Desenvolvimento WEB e Identidade Visual', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/h1ryc1u7us7rh5l5', '2', NULL),
(61096, 'pay_7bfomhgnzerxqk48', 234, 349.00, 'RECEIVED', '2024-12-12', '2024-12-11', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 1 de 3. Criação e Desenvolvimento WEB e Identidade Visual', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/7bfomhgnzerxqk48', '1', NULL),
(61099, 'pay_qrlesj5np5y2wx8m', 234, 29.90, 'RECEIVED_IN_CASH', '2025-01-10', '2025-04-08', '2024-12-10 00:00:00', '2025-07-29 11:07:35', 'Plano Hospedagem e Manutenção ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/qrlesj5np5y2wx8m', NULL, 'sub_5zhf56f9xgaopx3x'),
(61101, 'pay_h6468uckjs15n8ef', 238, 232.00, 'RECEIVED', '2025-01-10', '2025-02-12', '2024-12-10 00:00:00', '2025-07-29 07:56:35', 'Parcela 2 de 2. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/h6468uckjs15n8ef', '2', NULL),
(61108, 'pay_7hpehdeg1rho2w1n', 238, 232.00, 'RECEIVED', '2024-12-10', '2025-01-13', '2024-12-10 00:00:00', '2025-07-29 07:56:37', 'Parcela 1 de 2. Criação e Desenvolvimento WEB', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/7hpehdeg1rho2w1n', '1', NULL),
(61111, 'pay_xh10ehhdnjulf1nf', 238, 700.00, 'RECEIVED', '2024-12-10', '2024-12-10', '2024-12-10 00:00:00', '2025-07-29 11:07:41', 'Criação e Desenvolvimento WEB', 'PIX', NULL, 'https://www.asaas.com/i/xh10ehhdnjulf1nf', NULL, NULL),
(61114, 'pay_m0rpfyqbs1uhj9wt', 237, 30.24, 'RECEIVED', '2025-01-18', '2025-01-21', '2024-12-10 00:00:00', '2025-07-29 11:07:43', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/m0rpfyqbs1uhj9wt', NULL, 'sub_tjd6ivet6h510mad'),
(61116, 'pay_wcr1kin1418m01x4', 200, 239.00, 'RECEIVED', '2025-02-10', '2025-02-10', '2024-12-09 00:00:00', '2025-07-29 07:56:37', 'Parcela 3 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/wcr1kin1418m01x4', '3', NULL),
(61119, 'pay_739gxflckj14b16i', 200, 239.00, 'RECEIVED', '2025-01-10', '2025-01-07', '2024-12-09 00:00:00', '2025-07-29 07:56:37', 'Parcela 2 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/739gxflckj14b16i', '2', NULL),
(61122, 'pay_6ll6zlglzkvoeq35', 200, 239.00, 'RECEIVED', '2024-12-10', '2024-12-10', '2024-12-09 00:00:00', '2025-07-29 07:56:37', 'Parcela 1 de 3. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/6ll6zlglzkvoeq35', '1', NULL),
(61125, 'pay_kstu1rtae4tpgdkn', 201, 29.90, 'RECEIVED', '2025-01-10', '2025-01-10', '2024-12-09 00:00:00', '2025-07-29 11:07:49', 'PLano de Manutenção e Hospedagem ESSENCIAL', 'PIX', NULL, 'https://www.asaas.com/i/kstu1rtae4tpgdkn', NULL, 'sub_duqxrig3fiaq14pb'),
(61128, 'pay_qs1tfljxq9gecg8z', 201, 597.00, 'RECEIVED', '2024-12-10', '2024-12-11', '2024-12-09 00:00:00', '2025-07-29 11:07:50', 'Criação e Desenvolvimento WEB ', 'PIX', NULL, 'https://www.asaas.com/i/qs1tfljxq9gecg8z', NULL, NULL),
(61131, 'pay_wz3e0q7n0v4o753t', 202, 29.90, 'RECEIVED', '2024-12-23', '2024-12-23', '2024-12-09 00:00:00', '2025-07-29 11:07:52', 'Plano de Hospedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/wz3e0q7n0v4o753t', NULL, 'sub_5taox270k3eejldi'),
(61134, 'pay_slao0v6kdc238hqe', 202, 597.00, 'RECEIVED', '2024-12-23', '2024-12-23', '2024-12-09 00:00:00', '2025-07-29 11:07:53', 'Criação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/slao0v6kdc238hqe', NULL, NULL),
(61137, 'pay_l7ulknotkbt7jyin', 222, 30.68, 'RECEIVED', '2025-01-15', '2025-02-04', '2024-12-08 00:00:00', '2025-07-29 11:07:55', 'Plano de Hospedagem e Manutenção Mensal ESSENCIAL', 'BOLETO', NULL, 'https://www.asaas.com/i/l7ulknotkbt7jyin', NULL, 'sub_ma8iqwnxbogl6d7k'),
(61140, 'pay_q3283i0o566x9st0', 254, 29.90, 'RECEIVED', '2025-01-15', '2025-01-15', '2024-12-07 00:00:00', '2025-07-29 11:07:56', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/q3283i0o566x9st0', NULL, 'sub_oil9ich37nuhoes7'),
(61143, 'pay_4t0scafjndtrteaz', 286, 49.90, 'RECEIVED', '2025-01-15', '2025-01-14', '2024-12-07 00:00:00', '2025-07-29 11:07:58', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/4t0scafjndtrteaz', NULL, 'sub_fte624var188bxrz'),
(61146, 'pay_qeju6o67q9xm5so9', 222, 165.68, 'RECEIVED_IN_CASH', '2025-02-15', '2025-05-26', '2024-12-06 00:00:00', '2025-07-29 07:56:37', 'Parcela 3 de 3. ***Sujeito a Protesto após 15 dias do Vencimento***\r\nCriação e Desenvolvimento WEB: Site institucional [https://psicoroberta.com.br/]', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/qeju6o67q9xm5so9', '3', NULL),
(61149, 'pay_rousfykuvlfgr7j7', 222, 170.09, 'RECEIVED', '2025-01-15', '2025-02-04', '2024-12-06 00:00:00', '2025-07-29 07:56:37', 'Parcela 2 de 3. ***Sujeito a Protesto após 15 dias do Vencimento***\r\nCriação e Desenvolvimento WEB: Site institucional [https://psicoroberta.com.br/]', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/rousfykuvlfgr7j7', '2', NULL),
(61152, 'pay_ev8lm1qogqjvuep9', 222, 165.68, 'RECEIVED_IN_CASH', '2024-12-15', '2025-05-26', '2024-12-06 00:00:00', '2025-07-29 07:56:37', 'Parcela 1 de 3. ***Sujeito a Protesto após 15 dias do Vencimento***\r\nCriação e Desenvolvimento WEB: Site institucional [https://psicoroberta.com.br/]', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/ev8lm1qogqjvuep9', '1', NULL),
(61155, 'pay_22m6o65phcedcxhu', 246, 99.50, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-06 00:00:00', '2025-07-29 11:08:04', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/22m6o65phcedcxhu', NULL, NULL),
(61158, 'pay_l1d2a0po6is9disq', 246, 143.56, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-06 00:00:00', '2025-07-29 11:08:06', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/l1d2a0po6is9disq', NULL, NULL),
(61161, 'pay_3k5y0b4mp9bh0vbu', 208, 197.00, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-06 00:00:00', '2025-07-29 11:08:07', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/3k5y0b4mp9bh0vbu', NULL, NULL),
(61164, 'pay_s9wbzuh0ez7zoghj', 4294, 2655.00, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-06 00:00:00', '2025-07-29 11:08:09', 'Cobrança gerada automaticamente a partir de TED recebido.', 'TRANSFER', NULL, 'https://www.asaas.com/i/s9wbzuh0ez7zoghj', NULL, NULL),
(61167, 'pay_646qzvnoor4j4zgl', 246, 1415.44, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-06 00:00:00', '2025-07-29 11:08:10', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/646qzvnoor4j4zgl', NULL, NULL),
(61169, 'pay_k63kro7d5vimnb9p', 224, 291.00, 'RECEIVED', '2025-03-10', '2025-03-08', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 4 de 4. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/k63kro7d5vimnb9p', '4', NULL),
(61172, 'pay_ts5pu7qac5fb9c1e', 224, 291.00, 'RECEIVED', '2025-02-10', '2025-02-06', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 3 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ts5pu7qac5fb9c1e', '3', NULL),
(61175, 'pay_3debqt5ko7p2yovo', 224, 291.00, 'RECEIVED', '2025-01-10', '2025-01-09', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 2 de 4. Criação e Desenvolvimento WEB', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/3debqt5ko7p2yovo', '2', NULL),
(61178, 'pay_resiuh9xzt7ieduu', 224, 291.00, 'RECEIVED', '2024-12-10', '2024-12-07', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 1 de 4. Criação e Desenvolvimento WEB', 'PIX', 'PIX', 'https://www.asaas.com/i/resiuh9xzt7ieduu', '1', NULL),
(61181, 'pay_v8sns01szdafh96d', 224, 29.90, 'RECEIVED', '2025-01-10', '2025-01-09', '2024-12-05 00:00:00', '2025-07-29 11:08:18', 'Plano de Hespedagem e Manutenção Mensal', 'BOLETO', NULL, 'https://www.asaas.com/i/v8sns01szdafh96d', NULL, 'sub_906jjzs1wiwlv535'),
(61184, 'pay_o2bn20j5cjnxrmvn', 227, 472.15, 'RECEIVED', '2024-12-20', '2024-12-20', '2024-12-05 00:00:00', '2025-07-29 11:08:19', 'Criação e Desenvolvimento WEB', 'BOLETO', NULL, 'https://www.asaas.com/i/o2bn20j5cjnxrmvn', NULL, NULL),
(61187, 'pay_jq35eweslujewsh0', 227, 29.90, 'RECEIVED', '2025-01-10', '2025-01-10', '2024-12-05 00:00:00', '2025-07-29 11:08:21', 'Plano ESSENCIAL Hospedagem e Manutenção + Email Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/jq35eweslujewsh0', NULL, 'sub_uof9cualj3j78zmc'),
(61190, 'pay_8wi2dp7bazwk0q4b', 228, 40.72, 'RECEIVED', '2025-01-10', '2025-01-13', '2024-12-05 00:00:00', '2025-07-29 11:08:22', 'Plano ESSENCIAL Pacote Hospegagem e Manutenção Mensal', 'PIX', NULL, 'https://www.asaas.com/i/8wi2dp7bazwk0q4b', NULL, 'sub_laflhqry577ok0o2'),
(61192, 'pay_otaohznk5nxe4t1h', 228, 169.04, 'RECEIVED', '2025-02-13', '2025-02-14', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 3 de 3. Criação e Desenvolvimento E-commerce', 'PIX', 'PIX', 'https://www.asaas.com/i/otaohznk5nxe4t1h', '3', NULL),
(61195, 'pay_6arxpycdcxz4bi9w', 228, 165.66, 'RECEIVED', '2025-01-06', '2025-01-06', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 2 de 3. Criação e Desenvolvimento E-commerce', 'PIX', 'PIX', 'https://www.asaas.com/i/6arxpycdcxz4bi9w', '2', NULL),
(61198, 'pay_jxvdj8nie4naa3o5', 228, 165.66, 'RECEIVED', '2024-12-06', '2024-12-05', '2024-12-05 00:00:00', '2025-07-29 07:56:37', 'Parcela 1 de 3. Criação e Desenvolvimento E-commerce', 'PIX', 'PIX', 'https://www.asaas.com/i/jxvdj8nie4naa3o5', '1', NULL),
(61201, 'pay_zri6dsn8gggsgj8x', 256, 29.90, 'RECEIVED', '2025-01-13', '2024-12-13', '2024-12-05 00:00:00', '2025-07-29 11:08:28', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/zri6dsn8gggsgj8x', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61204, 'pay_5lyc0ldxkbyt8w5g', 240, 29.90, 'RECEIVED', '2025-01-10', '2025-01-06', '2024-12-04 00:00:00', '2025-07-29 11:08:30', 'Plano ESSENCIAL Hospedagem ', 'BOLETO', NULL, 'https://www.asaas.com/i/5lyc0ldxkbyt8w5g', NULL, 'sub_1p83yf64ebdz09eh'),
(61207, 'pay_b8rbruym3ov2r7eh', 225, 29.90, 'RECEIVED_IN_CASH', '2025-01-10', '2025-04-11', '2024-12-04 00:00:00', '2025-07-29 11:08:31', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/b8rbruym3ov2r7eh', NULL, 'sub_onezkhx9uo06h1we'),
(61210, 'pay_6wk36miovbji5ouo', 215, 29.90, 'RECEIVED_IN_CASH', '2025-01-10', '2025-03-19', '2024-12-04 00:00:00', '2025-07-29 11:08:33', 'Plano de Hospedagem Essencial para Sites', 'UNDEFINED', NULL, 'https://www.asaas.com/i/6wk36miovbji5ouo', NULL, 'sub_92c8ucrplnbrar5g'),
(61213, 'pay_r6sr42y529u3y6tr', 257, 30.71, 'RECEIVED', '2025-01-12', '2025-02-04', '2024-12-04 00:00:00', '2025-07-29 11:08:35', 'Serviço de Manutenção e Hospedagem WebSite', 'PIX', NULL, 'https://www.asaas.com/i/r6sr42y529u3y6tr', NULL, 'sub_ju5bkk8q06x0vid9'),
(61215, 'pay_94r7qamzvz8xag99', 258, 39.90, 'RECEIVED_IN_CASH', '2025-01-12', '2025-04-09', '2024-12-04 00:00:00', '2025-07-29 11:08:36', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/94r7qamzvz8xag99', NULL, 'sub_feo8zvrsmaf5q1tq'),
(61217, 'pay_rh0bt7pe540vgmx5', 248, 61.18, 'RECEIVED', '2025-02-10', '2025-02-20', '2024-12-03 00:00:00', '2025-07-29 11:08:38', 'Plano Hospedagem e Manutenção: jornaldiarionoticias.com.br/\r\nRef. aos meses de dez/24 e jan/25', 'PIX', NULL, 'https://www.asaas.com/i/rh0bt7pe540vgmx5', NULL, 'sub_po4bitwvslua9jmo'),
(61219, 'pay_x49t5aix2d4k5o89', 263, 30.55, 'RECEIVED', '2025-01-10', '2025-01-17', '2024-12-03 00:00:00', '2025-07-29 11:08:39', '', 'PIX', NULL, 'https://www.asaas.com/i/x49t5aix2d4k5o89', NULL, 'sub_re7f5cvc2t4u6fuo'),
(61221, 'pay_xi7nrv3myied452v', 275, 29.90, 'RECEIVED_IN_CASH', '2025-01-10', '2025-03-19', '2024-12-03 00:00:00', '2025-07-29 11:08:41', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/xi7nrv3myied452v', NULL, 'sub_d9vxnk8na9bb4e4b'),
(61224, 'pay_l0zb1n81ng2szvd8', 203, 30.00, 'RECEIVED', '2024-12-03', '2024-12-03', '2024-12-03 00:00:00', '2025-07-29 11:08:42', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/l0zb1n81ng2szvd8', NULL, NULL),
(61227, 'pay_fkwtbc9zlnc7n0qc', 207, 250.00, 'RECEIVED', '2025-01-07', '2025-01-07', '2024-12-03 00:00:00', '2025-07-29 11:08:44', 'Gerenciamento mensal de tráfego pago', 'PIX', NULL, 'https://www.asaas.com/i/fkwtbc9zlnc7n0qc', NULL, 'sub_2t3riwz0ws9as0hw'),
(61230, 'pay_jzdu4xjhvv14j4zb', 207, 400.00, 'RECEIVED', '2024-12-06', '2024-12-06', '2024-12-03 00:00:00', '2025-07-29 11:08:45', 'Serviço de setup e configuração de campanha no Google Ads', 'PIX', NULL, 'https://www.asaas.com/i/jzdu4xjhvv14j4zb', NULL, NULL),
(61233, 'pay_e5bs0pjkomggepb0', 281, 299.40, 'OVERDUE', '2025-01-15', NULL, '2024-12-03 00:00:00', '2025-07-29 11:08:47', '***PROTESTAR AUTOMATICAMENTE APÓS 15 DIAS DE VENCIMENTO***\r\nCobrança Ref. ao Serviço Mensal de Hospedagem Plano Essencial Meses 08,09,10,11, 12/24 e 01/25', 'BOLETO', NULL, 'https://www.asaas.com/i/e5bs0pjkomggepb0', NULL, NULL),
(61236, 'pay_6q5wy1tz87ledmuw', 232, 30.59, 'RECEIVED', '2025-01-10', '2025-01-21', '2024-12-03 00:00:00', '2025-07-29 11:08:48', 'PLANO MENSAL ESSENCIAL: Hospedagem + Plano Email\r\n', 'BOLETO', NULL, 'https://www.asaas.com/i/6q5wy1tz87ledmuw', NULL, 'sub_dhl2ubdq46degqsc'),
(61239, 'pay_g1x6v65rqgu3q12h', 232, 122.02, 'RECEIVED', '2024-12-10', '2024-12-11', '2024-12-03 00:00:00', '2025-07-29 11:08:50', 'Plano ESSENCIAL: abrasiva.com.br\r\nHospedagem + Conta E-mail\r\nRef. 01/09/2024 a 31/12/2024', 'PIX', NULL, 'https://www.asaas.com/i/g1x6v65rqgu3q12h', NULL, NULL),
(61242, 'pay_ab850t654wfod0qx', 233, 165.68, 'RECEIVED', '2025-02-10', '2025-01-10', '2024-12-03 00:00:00', '2025-07-29 07:56:38', 'Parcela 3 de 3. Protesto automático após 15 dias do vencimento.', 'PIX', 'PIX', 'https://www.asaas.com/i/ab850t654wfod0qx', '3', NULL),
(61245, 'pay_z3zyaqkot8v54h6e', 233, 165.66, 'RECEIVED', '2025-01-10', '2025-01-10', '2024-12-03 00:00:00', '2025-07-29 07:56:38', 'Parcela 2 de 3. Protesto automático após 15 dias do vencimento.', 'PIX', 'PIX', 'https://www.asaas.com/i/z3zyaqkot8v54h6e', '2', NULL),
(61248, 'pay_vhvsb6gwi037tff7', 233, 165.66, 'RECEIVED', '2024-12-10', '2024-12-03', '2024-12-03 00:00:00', '2025-07-29 07:56:38', 'Parcela 1 de 3. Protesto automático após 15 dias do vencimento.', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/vhvsb6gwi037tff7', '1', NULL),
(61251, 'pay_019ctw696owhulue', 265, 29.90, 'RECEIVED_IN_CASH', '2025-01-06', '2025-02-05', '2024-11-28 00:00:00', '2025-07-29 11:08:56', 'Plano Manutenção e Hospedagem Mensal Ref. SITE [maisfacilconsignados.com.br]', 'UNDEFINED', NULL, 'https://www.asaas.com/i/019ctw696owhulue', NULL, 'sub_xx181t17ej7qulmb'),
(61254, 'pay_7s4v4ocusfxiu4o9', 279, 49.90, 'RECEIVED', '2025-01-05', '2024-12-26', '2024-11-27 00:00:00', '2025-07-29 11:08:57', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/7s4v4ocusfxiu4o9', NULL, 'sub_htfllf6l0cu6s4qk'),
(61257, 'pay_aiusgp6kr0bvidoe', 283, 855.00, 'RECEIVED', '2024-11-26', '2024-11-26', '2024-11-26 00:00:00', '2025-07-29 11:08:59', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/aiusgp6kr0bvidoe', NULL, NULL),
(61260, 'pay_i6zjpf2mmqoqsajp', 208, 100.00, 'RECEIVED', '2024-11-25', '2024-11-25', '2024-11-25 00:00:00', '2025-07-29 11:09:00', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/i6zjpf2mmqoqsajp', NULL, NULL),
(61263, 'pay_ypkali1x2ap1z3n7', 240, 30.49, 'RECEIVED', '2024-12-10', '2024-12-11', '2024-11-21 00:00:00', '2025-07-29 11:09:02', 'Plano ESSENCIAL Hospedagem ', 'PIX', NULL, 'https://www.asaas.com/i/ypkali1x2ap1z3n7', NULL, 'sub_1p83yf64ebdz09eh');
INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(61266, 'pay_ip4ppc7x56krfyu3', 220, 29.90, 'RECEIVED', '2024-12-29', '2024-12-19', '2024-11-20 00:00:00', '2025-07-29 11:09:04', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/ip4ppc7x56krfyu3', NULL, 'sub_0ob5gqdk6bgm9evj'),
(61269, 'pay_k3dg9lhq0947hbfd', 267, 29.00, 'RECEIVED', '2024-12-29', '2024-12-17', '2024-11-20 00:00:00', '2025-07-29 11:09:05', '', 'PIX', NULL, 'https://www.asaas.com/i/k3dg9lhq0947hbfd', NULL, 'sub_504ksi201k6033es'),
(61272, 'pay_016pv7nqy9i1ewdc', 221, 29.90, 'OVERDUE', '2024-12-28', NULL, '2024-11-19 00:00:00', '2025-07-29 11:09:07', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/016pv7nqy9i1ewdc', NULL, 'sub_xahjz6ws9fumwnl9'),
(61275, 'pay_bsdg8bnamsllm0jd', 236, 30.62, 'RECEIVED', '2024-12-28', '2025-01-11', '2024-11-19 00:00:00', '2025-07-29 11:09:08', 'Plano Hospedagem e Manutenção Site', 'PIX', NULL, 'https://www.asaas.com/i/bsdg8bnamsllm0jd', NULL, 'sub_bwvnv3t5548q79az'),
(61278, 'pay_kp93kl07bootqcht', 286, 204.06, 'RECEIVED', '2025-01-13', '2025-01-14', '2024-11-19 00:00:00', '2025-07-29 11:09:10', 'Gestão Tráfego Pago ADS Local', 'BOLETO', NULL, 'https://www.asaas.com/i/kp93kl07bootqcht', NULL, 'sub_4o9clt62tbo925y4'),
(61281, 'pay_8rbjqa4fd1pab5b1', 245, 29.90, 'RECEIVED', '2024-12-25', '2024-12-20', '2024-11-16 00:00:00', '2025-07-29 11:09:11', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/8rbjqa4fd1pab5b1', NULL, 'sub_f2zgsu6ds7fskv75'),
(61284, 'pay_0w2oppcwg86b3qw6', 268, 29.90, 'OVERDUE', '2024-12-25', NULL, '2024-11-16 00:00:00', '2025-07-29 11:09:13', '', 'BOLETO', NULL, 'https://www.asaas.com/i/0w2oppcwg86b3qw6', NULL, 'sub_lzkw4ntgewkejb6n'),
(61287, 'pay_me2he3o9e7cz8bph', 284, 51.18, 'RECEIVED', '2024-12-25', '2025-01-12', '2024-11-16 00:00:00', '2025-07-29 11:09:14', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/me2he3o9e7cz8bph', NULL, 'sub_khpfl1dpc6pfj9ix'),
(61290, 'pay_u52vj10p8ig9rx0l', 283, 339.80, 'RECEIVED', '2024-11-14', '2024-11-14', '2024-11-14 00:00:00', '2025-07-29 11:09:16', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/u52vj10p8ig9rx0l', NULL, NULL),
(61293, 'pay_ft0ydnq3pierwhtx', 271, 159.60, 'DUNNING_REQUESTED', '2024-12-23', NULL, '2024-11-14 00:00:00', '2025-07-29 11:09:17', '***PROTESTAR AUTOMATICAMENTE APÓS 30 DIAS DE ATRASO***\r\nPlano ESSENCIAL Hosp+Email\r\nFatura ref. parcelas vencidas dos meses 09, 10, 11 e a vencer do mês 12/24.', 'UNDEFINED', NULL, 'https://www.asaas.com/i/ft0ydnq3pierwhtx', NULL, 'sub_1dx95biw49q1zskp'),
(61296, 'pay_524idpr73dmk3ah8', 237, 100.00, 'RECEIVED', '2024-11-13', '2024-11-13', '2024-11-13 00:00:00', '2025-07-29 11:09:19', 'Pagamento Parcial Serviço Criação de Site: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/524idpr73dmk3ah8', NULL, NULL),
(61299, 'pay_0whnkfn26hkfm917', 211, 29.90, 'PENDING', '2025-11-15', NULL, '2024-11-13 00:00:00', '2025-07-29 11:09:20', 'Plano Essencial Hosp+Manutenção', 'BOLETO', NULL, 'https://www.asaas.com/i/0whnkfn26hkfm917', NULL, 'sub_vpw0zju8t6ozmxb7'),
(61302, 'pay_pu5vbuazbxtntrhk', 211, 139.00, 'PENDING', '2025-10-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 12 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/pu5vbuazbxtntrhk', '12', NULL),
(61305, 'pay_19jt4jr3kntqyzdj', 211, 139.00, 'PENDING', '2025-09-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 11 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/19jt4jr3kntqyzdj', '11', NULL),
(61308, 'pay_ydgibbuauxzflbh7', 211, 139.00, 'PENDING', '2025-08-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 10 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/ydgibbuauxzflbh7', '10', NULL),
(61311, 'pay_zcjlk7qj5kiql71d', 211, 139.00, 'OVERDUE', '2025-07-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 9 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/zcjlk7qj5kiql71d', '9', NULL),
(61313, 'pay_7esob030iux67yus', 211, 139.00, 'OVERDUE', '2025-06-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 8 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/7esob030iux67yus', '8', NULL),
(61316, 'pay_7218lq1no3lpz9q9', 211, 139.00, 'OVERDUE', '2025-05-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 7 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/7218lq1no3lpz9q9', '7', NULL),
(61319, 'pay_v5eq0x5xtjdug77p', 211, 139.00, 'OVERDUE', '2025-04-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 6 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/v5eq0x5xtjdug77p', '6', NULL),
(61322, 'pay_6v20fq276jevjlcd', 211, 139.00, 'OVERDUE', '2025-03-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 5 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/6v20fq276jevjlcd', '5', NULL),
(61324, 'pay_b9kvfx8jebwq7wvf', 211, 139.00, 'OVERDUE', '2025-02-15', NULL, '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 4 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/b9kvfx8jebwq7wvf', '4', NULL),
(61327, 'pay_4j4o5olgaws0lo74', 211, 142.05, 'RECEIVED', '2025-01-15', '2025-01-21', '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 3 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'PIX', 'PIX', 'https://www.asaas.com/i/4j4o5olgaws0lo74', '3', NULL),
(61330, 'pay_e50k5zitu5lqchqh', 211, 141.87, 'RECEIVED', '2024-12-23', '2024-12-25', '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 2 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'PIX', 'PIX', 'https://www.asaas.com/i/e50k5zitu5lqchqh', '2', NULL),
(61333, 'pay_2664fu1v4lpn8g1p', 211, 139.00, 'RECEIVED', '2024-11-15', '2024-11-13', '2024-11-13 00:00:00', '2025-07-29 07:56:38', 'Parcela 1 de 12. Criação e Desenvolvimento WebSite: https://divdrinks.net.br', 'PIX', 'PIX', 'https://www.asaas.com/i/2664fu1v4lpn8g1p', '1', NULL),
(61336, 'pay_f4nfa2g84xfwnijs', 241, 250.00, 'RECEIVED', '2024-11-12', '2024-11-12', '2024-11-12 00:00:00', '2025-07-29 11:09:40', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/f4nfa2g84xfwnijs', NULL, NULL),
(61339, 'pay_uwu2yr6qndmvbaru', 251, 30.78, 'RECEIVED', '2024-12-21', '2025-01-20', '2024-11-12 00:00:00', '2025-07-29 11:09:42', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/uwu2yr6qndmvbaru', NULL, 'sub_fwc0baa1kthqsncu'),
(61342, 'pay_x6kjp6n3mjhjas5h', 253, 29.90, 'RECEIVED', '2024-12-19', '2024-12-13', '2024-11-10 00:00:00', '2025-07-29 11:09:43', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/x6kjp6n3mjhjas5h', NULL, 'sub_3z72l1acpglcr1fa'),
(61345, 'pay_e7ahwdkulhaa1qcz', 237, 30.20, 'RECEIVED', '2024-12-18', '2024-12-19', '2024-11-09 00:00:00', '2025-07-29 11:09:45', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/e7ahwdkulhaa1qcz', NULL, 'sub_tjd6ivet6h510mad'),
(61348, 'pay_bplgvshr6egcgbwu', 239, 303.20, 'RECEIVED', '2024-12-18', '2024-12-19', '2024-11-09 00:00:00', '2025-07-29 11:09:46', 'Gestão de Tráfego Pago Google ADS', 'PIX', NULL, 'https://www.asaas.com/i/bplgvshr6egcgbwu', NULL, 'sub_ju0rekr4dsn0gp1s'),
(61351, 'pay_0mljzt1ffi7jj4k7', 212, 333.60, 'RECEIVED', '2024-11-08', '2024-11-08', '2024-11-08 00:00:00', '2025-07-29 11:09:48', 'Ref. Entrada Projeto', 'PIX', NULL, 'https://www.asaas.com/i/0mljzt1ffi7jj4k7', NULL, NULL),
(61354, 'pay_vr0ruu62dppyr8l7', 213, 598.50, 'RECEIVED', '2024-11-11', '2024-11-11', '2024-11-08 00:00:00', '2025-07-29 11:09:49', 'Ref. entrada 30% projeto.', 'PIX', NULL, 'https://www.asaas.com/i/vr0ruu62dppyr8l7', NULL, NULL),
(61357, 'pay_2gk2uwzcoqthrbuo', 214, 300.00, 'RECEIVED', '2024-11-07', '2024-11-07', '2024-11-07 00:00:00', '2025-07-29 11:09:51', 'Ref. Entrada 20% Criação e Desenvolvimento WEB', 'PIX', NULL, 'https://www.asaas.com/i/2gk2uwzcoqthrbuo', NULL, NULL),
(61360, 'pay_l3wbxfduma4sauk3', 246, 2200.00, 'RECEIVED', '2024-11-07', '2024-11-07', '2024-11-07 00:00:00', '2025-07-29 11:09:52', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/l3wbxfduma4sauk3', NULL, NULL),
(61363, 'pay_ltxi6vjvr0sm8n98', 254, 30.49, 'RECEIVED', '2024-12-18', '2024-12-19', '2024-11-06 00:00:00', '2025-07-29 11:09:54', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/ltxi6vjvr0sm8n98', NULL, 'sub_oil9ich37nuhoes7'),
(61366, 'pay_0d0ulhkwaesz0468', 225, 30.69, 'RECEIVED', '2024-12-10', '2024-12-31', '2024-11-06 00:00:00', '2025-07-29 11:09:55', 'Plano Essencial Hosp+Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/0d0ulhkwaesz0468', NULL, 'sub_onezkhx9uo06h1we'),
(61369, 'pay_4mwqh7brq4smz3xm', 286, 49.90, 'RECEIVED', '2024-12-15', '2024-12-05', '2024-11-06 00:00:00', '2025-07-29 11:09:57', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/4mwqh7brq4smz3xm', NULL, 'sub_fte624var188bxrz'),
(61372, 'pay_w760zsn4gest3mxg', 225, 199.00, 'RECEIVED', '2025-01-06', '2025-02-11', '2024-11-06 00:00:00', '2025-07-29 07:56:38', 'Parcela 3 de 3. Criação e Desenvolvimento Site', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/w760zsn4gest3mxg', '3', NULL),
(61375, 'pay_vs18qztxlrqu544q', 225, 199.00, 'RECEIVED', '2024-12-06', '2025-01-10', '2024-11-06 00:00:00', '2025-07-29 07:56:38', 'Parcela 2 de 3. Criação e Desenvolvimento Site', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/vs18qztxlrqu544q', '2', NULL),
(61378, 'pay_pb9aqnr28p6zbege', 225, 199.00, 'RECEIVED', '2024-11-06', '2024-12-09', '2024-11-06 00:00:00', '2025-07-29 07:56:38', 'Parcela 1 de 3. Criação e Desenvolvimento Site', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/pb9aqnr28p6zbege', '1', NULL),
(61381, 'pay_tgk4ndkuhyfkdhqi', 225, 30.63, 'RECEIVED', '2024-11-10', '2024-11-25', '2024-11-06 00:00:00', '2025-07-29 11:10:03', 'Plano Essencial Hosp.+Manutenção', 'PIX', NULL, 'https://www.asaas.com/i/tgk4ndkuhyfkdhqi', NULL, 'sub_dxqoe3qofxr65vti'),
(61384, 'pay_q1nm2i8qttpkzbvp', 231, 30.55, 'RECEIVED', '2025-02-10', '2025-02-17', '2024-11-06 00:00:00', '2025-07-29 11:10:04', 'Plano de Manutenção Mensa Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/q1nm2i8qttpkzbvp', NULL, 'sub_56s3d52d4ctr1ydf'),
(61387, 'pay_12w3q4t620fiiasu', 231, 29.90, 'RECEIVED', '2025-01-10', '2025-01-07', '2024-11-06 00:00:00', '2025-07-29 11:10:06', 'Plano de Manutenção Mensa Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/12w3q4t620fiiasu', NULL, 'sub_56s3d52d4ctr1ydf'),
(61390, 'pay_itroqz12r9gpq704', 231, 29.90, 'RECEIVED', '2024-12-10', '2024-12-09', '2024-11-06 00:00:00', '2025-07-29 11:10:07', 'Plano de Manutenção Mensa Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/itroqz12r9gpq704', NULL, 'sub_56s3d52d4ctr1ydf'),
(61393, 'pay_qv7lw6dnydb5o3zp', 231, 717.00, 'RECEIVED', '2024-11-10', '2024-11-06', '2024-11-06 00:00:00', '2025-07-29 11:10:09', 'Criaçaõ e Desenvolvimento WebSite + Identidade Visual', 'PIX', NULL, 'https://www.asaas.com/i/qv7lw6dnydb5o3zp', NULL, NULL),
(61396, 'pay_wbt2d9yue1e1uvis', 231, 29.90, 'RECEIVED', '2024-11-10', '2024-11-06', '2024-11-06 00:00:00', '2025-07-29 11:10:10', 'Plano de Manutenção Mensa Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/wbt2d9yue1e1uvis', NULL, 'sub_56s3d52d4ctr1ydf'),
(61401, 'pay_1exg4xpovrdz5ol0', 246, 2000.00, 'RECEIVED', '2024-11-05', '2024-11-05', '2024-11-05 00:00:00', '2025-07-29 11:10:15', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/1exg4xpovrdz5ol0', NULL, NULL),
(61403, 'pay_phtqvu63e5yoz3i0', 256, 29.90, 'RECEIVED_IN_CASH', '2024-12-13', '2025-02-05', '2024-11-04 00:00:00', '2025-07-29 11:10:16', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/phtqvu63e5yoz3i0', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61405, 'pay_9s8okm70l2yp3c4g', 258, 39.90, 'RECEIVED_IN_CASH', '2024-12-12', '2025-04-09', '2024-11-03 00:00:00', '2025-07-29 11:10:18', '', 'UNDEFINED', NULL, 'https://www.asaas.com/i/9s8okm70l2yp3c4g', NULL, 'sub_feo8zvrsmaf5q1tq'),
(61408, 'pay_ykdxdcfped3dr0es', 263, 31.05, 'RECEIVED', '2024-12-10', '2025-02-05', '2024-11-02 00:00:00', '2025-07-29 11:10:19', '', 'PIX', NULL, 'https://www.asaas.com/i/ykdxdcfped3dr0es', NULL, 'sub_re7f5cvc2t4u6fuo'),
(61411, 'pay_6vz47dvw0a7o6b5m', 275, 91.51, 'RECEIVED', '2024-12-17', '2024-12-18', '2024-11-02 00:00:00', '2025-07-29 11:10:21', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL \r\nRef. mês 09, 11, 12/24', 'PIX', NULL, 'https://www.asaas.com/i/6vz47dvw0a7o6b5m', NULL, 'sub_d9vxnk8na9bb4e4b'),
(61414, 'pay_rkxifxm3qloxchnb', 284, 204.93, 'RECEIVED', '2024-11-05', '2024-12-23', '2024-11-02 00:00:00', '2025-07-29 11:10:22', 'Criação e Configuração de Formulário com Integração', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/rkxifxm3qloxchnb', NULL, NULL),
(61417, 'pay_59gxivvg6ic2rsid', 215, 232.80, 'OVERDUE', '2025-03-10', NULL, '2024-11-01 00:00:00', '2025-07-29 07:56:42', 'Parcela 5 de 5. Criação e Desenvolvimento E-commerce', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/59gxivvg6ic2rsid', '5', NULL),
(61420, 'pay_0b41coyd7aoe00n0', 215, 232.80, 'RECEIVED_IN_CASH', '2025-02-10', '2025-03-19', '2024-11-01 00:00:00', '2025-07-29 07:56:42', 'Parcela 4 de 5. Criação e Desenvolvimento E-commerce', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/0b41coyd7aoe00n0', '4', NULL),
(61423, 'pay_wv0opu54lfh4hbd5', 215, 232.80, 'RECEIVED_IN_CASH', '2025-01-10', '2025-03-19', '2024-11-01 00:00:00', '2025-07-29 07:56:42', 'Parcela 3 de 5. Criação e Desenvolvimento E-commerce', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/wv0opu54lfh4hbd5', '3', NULL),
(61426, 'pay_5gmasujq050iymgu', 215, 232.80, 'RECEIVED_IN_CASH', '2024-12-10', '2025-03-19', '2024-11-01 00:00:00', '2025-07-29 07:56:42', 'Parcela 2 de 5. Criação e Desenvolvimento E-commerce', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/5gmasujq050iymgu', '2', NULL),
(61429, 'pay_adr41bzk3wipd819', 215, 232.80, 'RECEIVED', '2024-11-12', '2024-11-12', '2024-11-01 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 5. Criação e Desenvolvimento E-commerce', 'PIX', 'PIX', 'https://www.asaas.com/i/adr41bzk3wipd819', '1', NULL),
(61431, 'pay_dxqvocwzu004r69k', 215, 29.90, 'RECEIVED_IN_CASH', '2024-12-10', '2025-03-19', '2024-11-01 00:00:00', '2025-07-29 11:10:31', 'Plano de Hospedagem Essencial para Sites', 'UNDEFINED', NULL, 'https://www.asaas.com/i/dxqvocwzu004r69k', NULL, 'sub_92c8ucrplnbrar5g'),
(61434, 'pay_fs8j0d64wl1xkk6q', 220, 166.68, 'RECEIVED', '2024-12-30', '2025-02-04', '2024-10-30 00:00:00', '2025-07-29 07:56:42', 'Parcela 3 de 3. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/fs8j0d64wl1xkk6q', '3', NULL),
(61437, 'pay_xckiqfw5r9kyxj36', 220, 166.66, 'RECEIVED', '2024-11-30', '2025-01-03', '2024-10-30 00:00:00', '2025-07-29 07:56:42', 'Parcela 2 de 3. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/xckiqfw5r9kyxj36', '2', NULL),
(61440, 'pay_g8d64kumr7eni4hw', 220, 166.66, 'RECEIVED', '2024-10-30', '2024-12-02', '2024-10-30 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 3. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/g8d64kumr7eni4hw', '1', NULL),
(61443, 'pay_h8unpr0nk6x357zv', 216, 290.00, 'RECEIVED', '2024-10-30', '2024-10-30', '2024-10-30 00:00:00', '2025-07-29 11:10:37', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/h8unpr0nk6x357zv', NULL, NULL),
(61446, 'pay_1idzgzm22714jygz', 220, 29.90, 'RECEIVED', '2024-11-29', '2024-11-22', '2024-10-29 00:00:00', '2025-07-29 11:10:39', 'Plano Hospedagem e Manutenção Mansal SUPORTE', 'BOLETO', NULL, 'https://www.asaas.com/i/1idzgzm22714jygz', NULL, 'sub_0ob5gqdk6bgm9evj'),
(61449, 'pay_ibdrhldkk53y6e3l', 220, 664.00, 'RECEIVED', '2024-10-29', '2024-10-29', '2024-10-29 00:00:00', '2025-07-29 11:10:40', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ibdrhldkk53y6e3l', NULL, NULL),
(61452, 'pay_ccvnelgr9leyrwxb', 236, 30.58, 'RECEIVED', '2024-11-28', '2024-12-08', '2024-10-28 00:00:00', '2025-07-29 11:10:42', 'Plano Hospedagem e Manutenção Site', 'PIX', NULL, 'https://www.asaas.com/i/ccvnelgr9leyrwxb', NULL, 'sub_bwvnv3t5548q79az'),
(61455, 'pay_by1yxtkdf4n9x7k7', 221, 97.00, 'CONFIRMED', '2025-09-29', NULL, '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 12 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/by1yxtkdf4n9x7k7', '12', NULL),
(61458, 'pay_jk5j8p4rl0v7it7c', 221, 97.00, 'CONFIRMED', '2025-08-29', NULL, '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 11 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/jk5j8p4rl0v7it7c', '11', NULL),
(61461, 'pay_e6w99x8l20lic7p6', 221, 97.00, 'CONFIRMED', '2025-07-29', NULL, '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 10 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/e6w99x8l20lic7p6', '10', NULL),
(61464, 'pay_brdeq8neqbx5y3yk', 221, 97.00, 'CONFIRMED', '2025-06-29', NULL, '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 9 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/brdeq8neqbx5y3yk', '9', NULL),
(61467, 'pay_6wklzzih1t916chb', 221, 97.00, 'RECEIVED', '2025-05-29', '2025-07-14', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 8 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6wklzzih1t916chb', '8', NULL),
(61470, 'pay_6nb8h7w1mffzmtiy', 221, 97.00, 'RECEIVED', '2025-04-29', '2025-06-11', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 7 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/6nb8h7w1mffzmtiy', '7', NULL),
(61472, 'pay_thbeud3ldpqj63pi', 221, 97.00, 'RECEIVED', '2025-03-29', '2025-05-12', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 6 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/thbeud3ldpqj63pi', '6', NULL),
(61475, 'pay_rngnd7bvsibd6k03', 221, 97.00, 'RECEIVED', '2025-02-28', '2025-04-08', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 5 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/rngnd7bvsibd6k03', '5', NULL),
(61478, 'pay_ufn2y3qekgn8p5mo', 221, 97.00, 'RECEIVED', '2025-01-29', '2025-03-07', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 4 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ufn2y3qekgn8p5mo', '4', NULL),
(61481, 'pay_62wp58ba197dw2g0', 221, 97.00, 'RECEIVED', '2024-12-29', '2025-02-03', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 3 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/62wp58ba197dw2g0', '3', NULL),
(61484, 'pay_bso47n0zx8nrt32f', 221, 97.00, 'RECEIVED', '2024-11-29', '2025-01-02', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 2 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/bso47n0zx8nrt32f', '2', NULL),
(61487, 'pay_i7qconrcddfji9wy', 221, 97.00, 'RECEIVED', '2024-10-29', '2024-12-02', '2024-10-28 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 12. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/i7qconrcddfji9wy', '1', NULL),
(61490, 'pay_chnhqhlziwehs5n7', 221, 29.90, 'RECEIVED', '2024-11-28', '2024-10-29', '2024-10-28 00:00:00', '2025-07-29 11:11:02', 'Plano Hospedagem e Manutenção WebSite', 'BOLETO', NULL, 'https://www.asaas.com/i/chnhqhlziwehs5n7', NULL, 'sub_xahjz6ws9fumwnl9'),
(61493, 'pay_rpg5otyehdh3893d', 236, 567.15, 'RECEIVED', '2024-10-28', '2024-10-28', '2024-10-28 00:00:00', '2025-07-29 11:11:03', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/rpg5otyehdh3893d', NULL, NULL),
(61496, 'pay_fx8exn8p06c1jglz', 236, 30.49, 'RECEIVED', '2024-10-28', '2024-10-29', '2024-10-28 00:00:00', '2025-07-29 11:11:05', 'Plano Hospedagem e Manutenção Site', 'PIX', NULL, 'https://www.asaas.com/i/fx8exn8p06c1jglz', NULL, 'sub_bwvnv3t5548q79az'),
(61499, 'pay_46jcb6zayaxkjssr', 265, 29.90, 'RECEIVED_IN_CASH', '2024-12-06', '2025-02-05', '2024-10-28 00:00:00', '2025-07-29 11:11:06', 'Plano Manutenção e Hospedagem Mensal Ref. SITE [maisfacilconsignados.com.br]', 'UNDEFINED', NULL, 'https://www.asaas.com/i/46jcb6zayaxkjssr', NULL, 'sub_xx181t17ej7qulmb'),
(61502, 'pay_rkodrqq993mmltv7', 279, 49.90, 'RECEIVED', '2024-12-05', '2024-11-25', '2024-10-27 00:00:00', '2025-07-29 11:11:08', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/rkodrqq993mmltv7', NULL, 'sub_htfllf6l0cu6s4qk'),
(61505, 'pay_c77h33oqppd4aluv', 286, 200.00, 'RECEIVED', '2024-11-28', '2024-11-28', '2024-10-25 00:00:00', '2025-07-29 11:11:09', 'Gestão Tráfego Pago ADS Local', 'PIX', NULL, 'https://www.asaas.com/i/c77h33oqppd4aluv', NULL, 'sub_4o9clt62tbo925y4'),
(61508, 'pay_86n20x05ko9h3u2l', 286, 200.00, 'RECEIVED', '2024-10-28', '2024-10-28', '2024-10-25 00:00:00', '2025-07-29 11:11:11', 'Gestão Tráfego Pago ADS Local', 'PIX', NULL, 'https://www.asaas.com/i/86n20x05ko9h3u2l', NULL, 'sub_4o9clt62tbo925y4'),
(61511, 'pay_zcygck5b93qdl3n8', 229, 465.60, 'DUNNING_REQUESTED', '2024-12-10', NULL, '2024-10-25 00:00:00', '2025-07-29 11:11:12', '***PROTESTAR AUTOMATICAMENTE APÓS 30 DIAS DE VENCIMENTO***\r\nTaxa de Cancelamento por Rescisão de Contrato', 'UNDEFINED', NULL, 'https://www.asaas.com/i/zcygck5b93qdl3n8', NULL, NULL),
(61514, 'pay_geo3qhwzwwuplxev', 268, 497.00, 'RECEIVED', '2024-10-23', '2024-10-23', '2024-10-23 00:00:00', '2025-07-29 11:11:14', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/geo3qhwzwwuplxev', NULL, NULL),
(61517, 'pay_i49cd87tzq5ut5l5', 253, 30.00, 'RECEIVED', '2024-10-21', '2024-10-21', '2024-10-21 00:00:00', '2025-07-29 11:11:15', 'Ref. 01H Atendimento Técnico Personalizado', 'PIX', NULL, 'https://www.asaas.com/i/i49cd87tzq5ut5l5', NULL, NULL),
(61520, 'pay_gblbctnci9qvyy5y', 267, 29.00, 'RECEIVED', '2024-11-29', '2024-11-07', '2024-10-21 00:00:00', '2025-07-29 11:11:17', '', 'PIX', NULL, 'https://www.asaas.com/i/gblbctnci9qvyy5y', NULL, 'sub_504ksi201k6033es'),
(61522, 'pay_47w9g0g71jx60kmk', 156, 400.00, 'RECEIVED', '2024-10-19', '2024-10-19', '2024-10-19 00:00:00', '2025-07-29 07:56:42', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', 'PIX', 'https://www.asaas.com/i/47w9g0g71jx60kmk', '', NULL),
(61523, 'pay_xbk2r9c9212oxs1f', 239, 300.00, 'RECEIVED', '2024-11-18', '2024-11-18', '2024-10-18 00:00:00', '2025-07-29 11:11:19', 'Gestão de Tráfego Pago Google ADS', 'PIX', NULL, 'https://www.asaas.com/i/xbk2r9c9212oxs1f', NULL, 'sub_ju0rekr4dsn0gp1s'),
(61525, 'pay_vql4r4q0sx0ivnmb', 237, 200.00, 'RECEIVED', '2025-01-10', '2025-01-16', '2024-10-18 00:00:00', '2025-07-29 11:11:20', 'Pagamento Efetuado Via PIX:\r\nR$ 100,00 dia 25/11\r\nR$ 197,00 dia 06/12\r\n\r\nCriação e Desenvolvimento WebSite Institucional: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/vql4r4q0sx0ivnmb', NULL, NULL),
(61528, 'pay_a6pxkzrdzorwfy8n', 237, 29.90, 'RECEIVED', '2024-11-13', '2024-11-13', '2024-10-18 00:00:00', '2025-07-29 11:11:22', 'Plano de Hospedagem e Manutenção site: https://graficaveronez.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/a6pxkzrdzorwfy8n', NULL, 'sub_tjd6ivet6h510mad'),
(61531, 'pay_zan5aj84kygz42ny', 239, 497.00, 'RECEIVED', '2024-10-18', '2024-10-18', '2024-10-18 00:00:00', '2025-07-29 11:11:23', 'Criação e Desenvolvimento de WebSite', 'PIX', NULL, 'https://www.asaas.com/i/zan5aj84kygz42ny', NULL, NULL),
(61534, 'pay_q64vjjgfz1rn1q2h', 239, 300.00, 'RECEIVED', '2024-10-18', '2024-10-18', '2024-10-18 00:00:00', '2025-07-29 11:11:25', 'Gestão de Tráfego Pago Google ADS', 'PIX', NULL, 'https://www.asaas.com/i/q64vjjgfz1rn1q2h', NULL, 'sub_ju0rekr4dsn0gp1s'),
(61537, 'pay_hu0m3siu193kfzja', 245, 29.90, 'RECEIVED', '2024-11-25', '2024-11-23', '2024-10-17 00:00:00', '2025-07-29 11:11:27', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/hu0m3siu193kfzja', NULL, 'sub_f2zgsu6ds7fskv75'),
(61540, 'pay_xxzswfweu4235w6a', 268, 29.90, 'OVERDUE', '2024-11-25', NULL, '2024-10-17 00:00:00', '2025-07-29 11:11:28', '', 'BOLETO', NULL, 'https://www.asaas.com/i/xxzswfweu4235w6a', NULL, 'sub_lzkw4ntgewkejb6n'),
(61543, 'pay_oxjlpznln8sx0qik', 284, 51.02, 'RECEIVED', '2024-11-25', '2024-12-03', '2024-10-17 00:00:00', '2025-07-29 11:11:30', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/oxjlpznln8sx0qik', NULL, 'sub_khpfl1dpc6pfj9ix'),
(61546, 'pay_s6ig3fs3v6edqivq', 4296, 179.27, 'RECEIVED', '2024-10-15', '2024-10-15', '2024-10-15 00:00:00', '2025-07-29 11:11:31', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/s6ig3fs3v6edqivq', NULL, NULL),
(61549, 'pay_71dyn8apmj2p2urb', 240, 497.00, 'RECEIVED', '2024-10-14', '2024-10-14', '2024-10-14 00:00:00', '2025-07-29 11:11:33', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: servico de producao de site', 'PIX', NULL, 'https://www.asaas.com/i/71dyn8apmj2p2urb', NULL, NULL),
(61552, 'pay_nd1henbndsaux59e', 251, 30.55, 'RECEIVED', '2024-11-21', '2024-11-28', '2024-10-13 00:00:00', '2025-07-29 11:11:34', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/nd1henbndsaux59e', NULL, 'sub_fwc0baa1kthqsncu'),
(61555, 'pay_xmdk2p5yxtshypev', 241, 250.00, 'RECEIVED', '2024-10-11', '2024-10-11', '2024-10-11 00:00:00', '2025-07-29 11:11:36', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: Tráfego Pago', 'PIX', NULL, 'https://www.asaas.com/i/xmdk2p5yxtshypev', NULL, NULL),
(61558, 'pay_gdqfhhuvm5ab3rqi', 245, 600.00, 'RECEIVED', '2024-10-10', '2024-10-10', '2024-10-10 00:00:00', '2025-07-29 11:11:37', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/gdqfhhuvm5ab3rqi', NULL, NULL),
(61561, 'pay_8unnuuczgqdp78t5', 245, 250.00, 'RECEIVED', '2024-10-08', '2024-10-08', '2024-10-08 00:00:00', '2025-07-29 11:11:39', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/8unnuuczgqdp78t5', NULL, NULL),
(61564, 'pay_w7we1u3inxpgnkuf', 254, 30.49, 'RECEIVED', '2024-11-25', '2024-11-26', '2024-10-07 00:00:00', '2025-07-29 11:11:40', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/w7we1u3inxpgnkuf', NULL, 'sub_oil9ich37nuhoes7'),
(61567, 'pay_tj1qoxr1l7q272zv', 286, 49.90, 'RECEIVED', '2024-11-15', '2024-11-11', '2024-10-07 00:00:00', '2025-07-29 11:11:42', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/tj1qoxr1l7q272zv', NULL, 'sub_fte624var188bxrz'),
(61569, 'pay_5vow795dz0xvo0a1', 256, 29.90, 'RECEIVED', '2024-11-13', '2024-11-13', '2024-10-05 00:00:00', '2025-07-29 11:11:43', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/5vow795dz0xvo0a1', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61572, 'pay_2bhy4b0zrmux46fo', 242, 320.00, 'RECEIVED', '2024-10-04', '2024-10-04', '2024-10-04 00:00:00', '2025-07-29 11:11:45', '', 'PIX', NULL, 'https://www.asaas.com/i/2bhy4b0zrmux46fo', NULL, NULL),
(61575, 'pay_4y0ggd695pt88tee', 243, 497.00, 'RECEIVED', '2024-10-07', '2024-10-04', '2024-10-04 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 1. Loja Virtual: eternizepingentes.com.br', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/4y0ggd695pt88tee', '1', NULL),
(61578, 'pay_avdnniq23vrqpdlk', 258, 43.10, 'RECEIVED', '2024-11-12', '2025-02-26', '2024-10-04 00:00:00', '2025-07-29 11:11:48', '', 'BOLETO', NULL, 'https://www.asaas.com/i/avdnniq23vrqpdlk', NULL, 'sub_feo8zvrsmaf5q1tq'),
(61581, 'pay_k441kovn82rafq5a', 248, 29.90, 'RECEIVED', '2024-11-11', '2024-11-05', '2024-10-04 00:00:00', '2025-07-29 11:11:49', 'Plano Hospedagem Mensal: jornaldiarionoticias.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/k441kovn82rafq5a', NULL, 'sub_po4bitwvslua9jmo'),
(61584, 'pay_v3m9et4qbqzgecww', 263, 30.74, 'RECEIVED', '2024-11-10', '2024-12-06', '2024-10-03 00:00:00', '2025-07-29 11:11:51', '', 'PIX', NULL, 'https://www.asaas.com/i/v3m9et4qbqzgecww', NULL, 'sub_re7f5cvc2t4u6fuo'),
(61587, 'pay_7we3bjady3qmcsd5', 4296, 270.00, 'RECEIVED', '2024-10-01', '2024-10-01', '2024-10-01 00:00:00', '2025-07-29 11:11:52', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/7we3bjady3qmcsd5', NULL, NULL),
(61590, 'pay_5b4pde6r7iuao8tl', 265, 29.90, 'RECEIVED_IN_CASH', '2024-11-06', '2025-02-05', '2024-09-28 00:00:00', '2025-07-29 11:11:54', 'Plano Manutenção e Hospedagem Mensal Ref. SITE [maisfacilconsignados.com.br]', 'UNDEFINED', NULL, 'https://www.asaas.com/i/5b4pde6r7iuao8tl', NULL, 'sub_xx181t17ej7qulmb'),
(61593, 'pay_mjmcd58br70eum86', 279, 49.90, 'RECEIVED', '2024-11-05', '2024-10-25', '2024-09-27 00:00:00', '2025-07-29 11:11:55', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/mjmcd58br70eum86', NULL, 'sub_htfllf6l0cu6s4qk'),
(61596, 'pay_34ui5sx34mx472ho', 276, 49.90, 'PENDING', '2025-09-26', NULL, '2024-09-26 00:00:00', '2025-07-29 11:11:57', 'Plano de Hospedagem e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', NULL, 'https://www.asaas.com/i/34ui5sx34mx472ho', NULL, 'sub_0k9hconjle2vyed9'),
(61599, 'pay_11ewr3edxiohqnol', 276, 49.90, 'PENDING', '2025-08-26', NULL, '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 12 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/11ewr3edxiohqnol', '12', NULL),
(61602, 'pay_vjvibossds9gzezn', 276, 49.90, 'RECEIVED', '2025-07-26', '2025-07-28', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 11 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/vjvibossds9gzezn', '11', NULL),
(61605, 'pay_bc9fpzvdj2kzjkik', 276, 49.90, 'RECEIVED', '2025-06-26', '2025-06-25', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 10 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/bc9fpzvdj2kzjkik', '10', NULL),
(61608, 'pay_63pk3aqqv7gik20n', 276, 49.90, 'RECEIVED', '2025-05-26', '2025-05-26', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 9 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/63pk3aqqv7gik20n', '9', NULL),
(61610, 'pay_nf2trqzp8zwucn8y', 276, 49.90, 'RECEIVED', '2025-04-26', '2025-04-24', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 8 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/nf2trqzp8zwucn8y', '8', NULL),
(61613, 'pay_vsa3kyj4cnw7wudh', 276, 49.90, 'RECEIVED', '2025-03-26', '2025-03-26', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 7 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/vsa3kyj4cnw7wudh', '7', NULL),
(61616, 'pay_sn5xtzcwofvs13p3', 276, 49.90, 'RECEIVED', '2025-02-26', '2025-02-24', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 6 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/sn5xtzcwofvs13p3', '6', NULL),
(61619, 'pay_a2qpp3f20ckeoonn', 276, 49.90, 'RECEIVED', '2025-01-26', '2025-01-27', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 5 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/a2qpp3f20ckeoonn', '5', NULL),
(61622, 'pay_c04j02jo7mzx5xwe', 276, 49.90, 'RECEIVED', '2024-12-26', '2024-12-26', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 4 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/c04j02jo7mzx5xwe', '4', NULL),
(61625, 'pay_p8o68zvlx6mbm47k', 276, 49.90, 'RECEIVED', '2024-11-26', '2024-11-26', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 3 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/p8o68zvlx6mbm47k', '3', NULL),
(61628, 'pay_um0y5wsqqonhg5ht', 276, 49.90, 'RECEIVED', '2024-10-26', '2024-10-28', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 2 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/um0y5wsqqonhg5ht', '2', NULL),
(61631, 'pay_iqd65vg79vclp233', 276, 51.02, 'RECEIVED', '2024-09-26', '2024-10-04', '2024-09-26 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 12. Plano de Hospegame e E-mail Profissional\r\nDominio: https://lacloro.com.br/', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/iqd65vg79vclp233', '1', NULL),
(61634, 'pay_dm5wrpdq5z14xoub', 286, 200.00, 'RECEIVED', '2024-09-25', '2024-09-25', '2024-09-25 00:00:00', '2025-07-29 11:12:17', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: Pagamento pixel digital H2O Po', 'PIX', NULL, 'https://www.asaas.com/i/dm5wrpdq5z14xoub', NULL, NULL),
(61637, 'pay_cyn9pgfkgslfotj0', 245, 29.90, 'RECEIVED', '2024-10-25', '2024-10-25', '2024-09-24 00:00:00', '2025-07-29 11:12:18', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/cyn9pgfkgslfotj0', NULL, 'sub_f2zgsu6ds7fskv75'),
(61640, 'pay_62zccligtxj8dhcs', 244, 500.00, 'RECEIVED', '2024-09-24', '2024-10-28', '2024-09-24 00:00:00', '2025-07-29 11:12:20', 'Entrada 20% ref. projeto Criação de Ecommerce ', 'CREDIT_CARD', NULL, 'https://www.asaas.com/i/62zccligtxj8dhcs', NULL, NULL),
(61643, 'pay_0ar1urwym8ksu0ei', 245, 29.90, 'RECEIVED', '2024-09-25', '2024-09-24', '2024-09-24 00:00:00', '2025-07-29 11:12:21', 'Plano de Hespedagem e Manutenção Mensal \r\nDomínio: mspobras.com.br\r\n', 'PIX', NULL, 'https://www.asaas.com/i/0ar1urwym8ksu0ei', NULL, 'sub_f2zgsu6ds7fskv75'),
(61646, 'pay_wfaejnqi36i5fa2n', 246, 85.02, 'RECEIVED', '2024-09-22', '2024-09-22', '2024-09-22 00:00:00', '2025-07-29 11:12:23', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/wfaejnqi36i5fa2n', NULL, NULL),
(61649, 'pay_35qi27sx5yf8qews', 258, 40.00, 'RECEIVED', '2024-09-22', '2024-09-22', '2024-09-22 00:00:00', '2025-07-29 11:12:24', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/35qi27sx5yf8qews', NULL, NULL),
(61652, 'pay_zweph925i9sg7zy3', 258, 497.00, 'RECEIVED', '2024-09-22', '2024-09-22', '2024-09-22 00:00:00', '2025-07-29 11:12:26', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/zweph925i9sg7zy3', NULL, NULL),
(61655, 'pay_xa7q9llzmrhvueq3', 267, 29.00, 'RECEIVED', '2024-10-29', '2024-10-14', '2024-09-20 00:00:00', '2025-07-29 11:12:27', '', 'PIX', NULL, 'https://www.asaas.com/i/xa7q9llzmrhvueq3', NULL, 'sub_504ksi201k6033es'),
(61657, 'pay_glv297wtd2pk3xp3', 247, 248.50, 'RECEIVED', '2024-09-16', '2024-09-16', '2024-09-16 00:00:00', '2025-07-29 11:12:29', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/glv297wtd2pk3xp3', NULL, NULL),
(61660, 'pay_cwpgotugi9h6xo2x', 268, 30.51, 'RECEIVED', '2024-10-25', '2024-10-28', '2024-09-16 00:00:00', '2025-07-29 11:12:30', '', 'PIX', NULL, 'https://www.asaas.com/i/cwpgotugi9h6xo2x', NULL, 'sub_lzkw4ntgewkejb6n'),
(61663, 'pay_f9n6iuzhglj69vxp', 284, 50.93, 'RECEIVED', '2024-10-25', '2024-10-28', '2024-09-16 00:00:00', '2025-07-29 11:12:32', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/f9n6iuzhglj69vxp', NULL, 'sub_khpfl1dpc6pfj9ix'),
(61666, 'pay_zry97ypntvk8w08u', 4293, 400.00, 'RECEIVED', '2024-09-13', '2024-09-13', '2024-09-13 00:00:00', '2025-07-29 11:12:33', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/zry97ypntvk8w08u', NULL, NULL),
(61669, 'pay_trgsqn8mus4v15sg', 278, 550.00, 'RECEIVED', '2024-09-13', '2024-09-13', '2024-09-13 00:00:00', '2025-07-29 11:12:35', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/trgsqn8mus4v15sg', NULL, NULL),
(61672, 'pay_f6mjaahi3idi22lq', 251, 29.90, 'RECEIVED', '2024-10-21', '2024-10-17', '2024-09-12 00:00:00', '2025-07-29 11:12:36', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/f6mjaahi3idi22lq', NULL, 'sub_fwc0baa1kthqsncu'),
(61675, 'pay_wa6b5djcf29ewqyw', 248, 232.34, 'RECEIVED', '2024-11-11', '2024-12-17', '2024-09-11 00:00:00', '2025-07-29 07:56:42', 'Parcela 3 de 3. Criação e Desenvolvimento Blog de Noticias: https://jornaldiarionoticias.com.br/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/wa6b5djcf29ewqyw', '3', NULL),
(61678, 'pay_y6ltgpq000wku2w1', 248, 232.33, 'RECEIVED', '2024-10-11', '2024-11-18', '2024-09-11 00:00:00', '2025-07-29 07:56:42', 'Parcela 2 de 3. Criação e Desenvolvimento Blog de Noticias: https://jornaldiarionoticias.com.br/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/y6ltgpq000wku2w1', '2', NULL),
(61681, 'pay_mll0yvm3j1ly5ywp', 248, 232.33, 'RECEIVED', '2024-09-11', '2024-10-14', '2024-09-11 00:00:00', '2025-07-29 07:56:42', 'Parcela 1 de 3. Criação e Desenvolvimento Blog de Noticias: https://jornaldiarionoticias.com.br/', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mll0yvm3j1ly5ywp', '1', NULL),
(61684, 'pay_74vfnll4ahobs8nn', 248, 29.90, 'RECEIVED', '2024-10-11', '2024-10-01', '2024-09-11 00:00:00', '2025-07-29 11:12:42', 'Plano Hospedagem Mensal: jornaldiarionoticias.com.br/', 'PIX', NULL, 'https://www.asaas.com/i/74vfnll4ahobs8nn', NULL, 'sub_po4bitwvslua9jmo'),
(61687, 'pay_3pivkszzuyc003zc', 253, 29.90, 'RECEIVED', '2024-10-19', '2024-10-09', '2024-09-10 00:00:00', '2025-07-29 11:12:44', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/3pivkszzuyc003zc', NULL, 'sub_3z72l1acpglcr1fa'),
(61695, 'pay_ysjr9prupsf4goll', 254, 30.49, 'RECEIVED', '2024-10-15', '2024-10-16', '2024-09-06 00:00:00', '2025-07-29 11:12:48', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/ysjr9prupsf4goll', NULL, 'sub_oil9ich37nuhoes7'),
(61698, 'pay_d5a8svtkpymgva9u', 286, 49.90, 'RECEIVED', '2024-10-15', '2024-10-08', '2024-09-06 00:00:00', '2025-07-29 11:12:50', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/d5a8svtkpymgva9u', NULL, 'sub_fte624var188bxrz'),
(61701, 'pay_v488nqp6wv7o7ss9', 4297, 497.00, 'OVERDUE', '2024-09-06', NULL, '2024-09-05 00:00:00', '2025-07-29 11:12:51', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO\r\n\r\nCriação e Desenvolvimento de WebSite, Aplicativo e Plataforma Delivery: frangoassadotioze.com.br', 'UNDEFINED', NULL, 'https://www.asaas.com/i/v488nqp6wv7o7ss9', NULL, NULL),
(61704, 'pay_d2k7u4ss6e4qhxk4', 256, 29.90, 'RECEIVED', '2024-10-13', '2024-10-07', '2024-09-04 00:00:00', '2025-07-29 11:12:53', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/d2k7u4ss6e4qhxk4', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61706, 'pay_lrzpovrl1vnmlst3', 258, 43.93, 'RECEIVED', '2024-10-12', '2025-02-26', '2024-09-03 00:00:00', '2025-07-29 11:12:54', '', 'BOLETO', NULL, 'https://www.asaas.com/i/lrzpovrl1vnmlst3', NULL, 'sub_feo8zvrsmaf5q1tq'),
(61708, 'pay_lpyviv3vu11xhuvk', 264, 30.52, 'RECEIVED', '2024-10-12', '2024-10-16', '2024-09-03 00:00:00', '2025-07-29 11:12:56', '', 'PIX', NULL, 'https://www.asaas.com/i/lpyviv3vu11xhuvk', NULL, 'sub_ohuo9f9u852d7wki'),
(61711, 'pay_54xzage0fi48a408', 263, 30.66, 'RECEIVED', '2024-10-10', '2024-10-28', '2024-09-02 00:00:00', '2025-07-29 11:12:57', '', 'PIX', NULL, 'https://www.asaas.com/i/54xzage0fi48a408', NULL, 'sub_re7f5cvc2t4u6fuo'),
(61714, 'pay_c8g2ylspcu9zv9qw', 275, 29.90, 'RECEIVED', '2024-10-10', '2024-10-10', '2024-09-02 00:00:00', '2025-07-29 11:12:59', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'BOLETO', NULL, 'https://www.asaas.com/i/c8g2ylspcu9zv9qw', NULL, 'sub_d9vxnk8na9bb4e4b'),
(61717, 'pay_ua2ybalz9vwl66ov', 252, 300.00, 'RECEIVED', '2024-08-29', '2024-08-29', '2024-08-29 00:00:00', '2025-07-29 11:13:01', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/ua2ybalz9vwl66ov', NULL, NULL),
(61720, 'pay_8dataz6xaewxoyaq', 249, 300.00, 'RECEIVED', '2024-08-29', '2024-08-29', '2024-08-29 00:00:00', '2025-07-29 11:13:02', 'Cobrança gerada automaticamente a partir de Pix recebido. Mensagem: Meu trafego pago', 'PIX', NULL, 'https://www.asaas.com/i/8dataz6xaewxoyaq', NULL, NULL),
(61723, 'pay_dydr3hphugdg2uk8', 250, 122.00, 'CONFIRMED', '2025-07-28', NULL, '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 12 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/dydr3hphugdg2uk8', '12', NULL),
(61725, 'pay_lz8cbi7sag8soew3', 250, 122.00, 'CONFIRMED', '2025-06-28', NULL, '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 11 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/lz8cbi7sag8soew3', '11', NULL),
(61728, 'pay_ect5ussnwbicr8as', 250, 122.00, 'RECEIVED', '2025-05-28', '2025-07-15', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 10 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ect5ussnwbicr8as', '10', NULL),
(61731, 'pay_m6r8zsrkier22urs', 250, 122.00, 'RECEIVED', '2025-04-28', '2025-06-13', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 9 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/m6r8zsrkier22urs', '9', NULL),
(61734, 'pay_ruopt62f1gmu25hc', 250, 122.00, 'RECEIVED', '2025-03-28', '2025-05-12', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 8 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ruopt62f1gmu25hc', '8', NULL),
(61737, 'pay_qmu23mv1x3lv48gj', 250, 122.00, 'RECEIVED', '2025-02-28', '2025-04-10', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 7 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/qmu23mv1x3lv48gj', '7', NULL),
(61740, 'pay_8pz646kr5gnd0i14', 250, 122.00, 'RECEIVED', '2025-01-28', '2025-03-10', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 6 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/8pz646kr5gnd0i14', '6', NULL),
(61743, 'pay_psw21r3rgzhiehh2', 250, 122.00, 'RECEIVED', '2024-12-28', '2025-02-05', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 5 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/psw21r3rgzhiehh2', '5', NULL),
(61746, 'pay_18v0k64io7pd4vaq', 250, 122.00, 'RECEIVED', '2024-11-28', '2025-01-06', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 4 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/18v0k64io7pd4vaq', '4', NULL),
(61749, 'pay_5m1asqb3f6x1qgr7', 250, 122.00, 'RECEIVED', '2024-10-28', '2024-12-03', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/5m1asqb3f6x1qgr7', '3', NULL),
(61752, 'pay_mwgzuo6s6ave4a30', 250, 122.00, 'RECEIVED', '2024-09-28', '2024-11-01', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mwgzuo6s6ave4a30', '2', NULL),
(61755, 'pay_1x0bu9ntyy1y68o8', 250, 122.00, 'RECEIVED', '2024-08-28', '2024-09-30', '2024-08-28 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 12. Criação de Plataforma de Vendas Online e Configuração de Campanhas no Meta Ads', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1x0bu9ntyy1y68o8', '1', NULL),
(61758, 'pay_elvsvzvx0erg7vmm', 4296, 40.00, 'RECEIVED', '2024-08-28', '2024-08-28', '2024-08-28 00:00:00', '2025-07-29 11:13:22', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/elvsvzvx0erg7vmm', NULL, NULL),
(61761, 'pay_gdj1o717oxw5i5k5', 265, 30.50, 'RECEIVED', '2024-10-06', '2024-10-08', '2024-08-28 00:00:00', '2025-07-29 11:13:23', 'Plano Manutenção e Hospedagem Mensal Ref. SITE [maisfacilconsignados.com.br]', 'PIX', NULL, 'https://www.asaas.com/i/gdj1o717oxw5i5k5', NULL, 'sub_xx181t17ej7qulmb'),
(61764, 'pay_uc4yivsvfuhnev3m', 279, 49.90, 'RECEIVED', '2024-10-05', '2024-09-25', '2024-08-27 00:00:00', '2025-07-29 11:13:25', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/uc4yivsvfuhnev3m', NULL, 'sub_htfllf6l0cu6s4qk'),
(61767, 'pay_q52yw8wxg45zgvet', 284, 195.00, 'RECEIVED', '2024-10-27', '2024-12-02', '2024-08-27 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3.', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/q52yw8wxg45zgvet', '3', NULL),
(61770, 'pay_42k745his45ydst8', 284, 195.00, 'RECEIVED', '2024-09-27', '2024-10-31', '2024-08-27 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3.', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/42k745his45ydst8', '2', NULL),
(61772, 'pay_gpdo38r7uihh9ftt', 284, 195.00, 'RECEIVED', '2024-08-27', '2024-09-30', '2024-08-27 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3.', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/gpdo38r7uihh9ftt', '1', NULL),
(61775, 'pay_hsh2oaq32bl8eso2', 268, 250.00, 'RECEIVED', '2024-08-22', '2024-08-22', '2024-08-22 00:00:00', '2025-07-29 11:13:31', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/hsh2oaq32bl8eso2', NULL, NULL),
(61778, 'pay_5rmqxamyeflvv5wi', 251, 29.90, 'RECEIVED', '2024-09-21', '2024-09-20', '2024-08-21 00:00:00', '2025-07-29 11:13:33', 'Plano Mensal de Manutenção e Hospedagem Loja Virtual: okgostei.com.br', 'BOLETO', NULL, 'https://www.asaas.com/i/5rmqxamyeflvv5wi', NULL, 'sub_fwc0baa1kthqsncu'),
(61781, 'pay_50r9b3tk4z2kzm3r', 251, 248.50, 'RECEIVED', '2024-08-21', '2024-08-21', '2024-08-21 00:00:00', '2025-07-29 11:13:34', '50% valor Criação Desenvolvimento Loja Virtual: okgostei.com.br', 'PIX', NULL, 'https://www.asaas.com/i/50r9b3tk4z2kzm3r', NULL, NULL),
(61784, 'pay_w4avd3f2ubk6yxco', 267, 29.00, 'RECEIVED', '2024-09-29', '2024-09-13', '2024-08-21 00:00:00', '2025-07-29 11:13:36', '', 'PIX', NULL, 'https://www.asaas.com/i/w4avd3f2ubk6yxco', NULL, 'sub_504ksi201k6033es'),
(61787, 'pay_hm8p5kzt1z1soi6k', 252, 497.00, 'RECEIVED', '2024-08-20', '2024-08-20', '2024-08-20 00:00:00', '2025-07-29 11:13:37', '', 'PIX', NULL, 'https://www.asaas.com/i/hm8p5kzt1z1soi6k', NULL, NULL),
(61790, 'pay_t52v6xp3lg094tp7', 253, 29.90, 'RECEIVED', '2024-09-19', '2024-09-19', '2024-08-19 00:00:00', '2025-07-29 11:13:39', 'Plano Mensal Manutenção eHospedagem Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/t52v6xp3lg094tp7', NULL, 'sub_3z72l1acpglcr1fa'),
(61793, 'pay_mq11jyqhbn81osdf', 253, 297.00, 'RECEIVED', '2024-08-19', '2024-08-19', '2024-08-19 00:00:00', '2025-07-29 11:13:40', 'Criação e Desenvolvimento de WebSite', 'PIX', NULL, 'https://www.asaas.com/i/mq11jyqhbn81osdf', NULL, NULL),
(61796, 'pay_nsjwy9n2ie8tl004', 268, 29.90, 'RECEIVED', '2024-09-25', '2024-09-25', '2024-08-17 00:00:00', '2025-07-29 11:13:42', '', 'PIX', NULL, 'https://www.asaas.com/i/nsjwy9n2ie8tl004', NULL, 'sub_lzkw4ntgewkejb6n'),
(61799, 'pay_pqqne3vmb2sb1gnn', 284, 29.90, 'RECEIVED', '2024-10-04', '2024-10-04', '2024-08-17 00:00:00', '2025-07-29 11:13:43', 'Plano Hospedagem Essencial', 'BOLETO', NULL, 'https://www.asaas.com/i/pqqne3vmb2sb1gnn', NULL, 'sub_khpfl1dpc6pfj9ix'),
(61802, 'pay_thhfu9d72jg8mvu5', 257, 300.00, 'RECEIVED', '2024-08-21', '2024-08-21', '2024-08-16 00:00:00', '2025-07-29 11:13:45', 'Criação e Configuração de Campanha Google ADS', 'PIX', NULL, 'https://www.asaas.com/i/thhfu9d72jg8mvu5', NULL, NULL),
(61805, 'pay_qym97i4rwflrc7g5', 260, 165.68, 'RECEIVED_IN_CASH', '2024-10-16', '2025-05-09', '2024-08-16 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3. PROTESTAR AUTOMATICAMENTE APÓS 15 DIAS DE VENCIMENTO', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/qym97i4rwflrc7g5', '3', NULL);
INSERT INTO `cobrancas` (`id`, `asaas_payment_id`, `cliente_id`, `valor`, `status`, `vencimento`, `data_pagamento`, `data_criacao`, `data_atualizacao`, `descricao`, `tipo`, `tipo_pagamento`, `url_fatura`, `parcela`, `assinatura_id`) VALUES
(61808, 'pay_pn5dqmosdj5je0tl', 260, 165.66, 'RECEIVED_IN_CASH', '2024-09-16', '2025-04-10', '2024-08-16 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3. PROTESTAR AUTOMATICAMENTE APÓS 15 DIAS DE VENCIMENTO', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/pn5dqmosdj5je0tl', '2', NULL),
(61811, 'pay_xurq72m935l91vlg', 260, 165.66, 'RECEIVED_IN_CASH', '2024-08-16', '2025-03-21', '2024-08-16 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3. PROTESTAR AUTOMATICAMENTE APÓS 15 DIAS DE VENCIMENTO', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/xurq72m935l91vlg', '1', NULL),
(61814, 'pay_p3yzpd1tgz6vci1i', 254, 169.04, 'RECEIVED', '2024-10-15', '2024-10-16', '2024-08-14 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3. CRIAÇÃO E DESENVOLVIMENTO DE WEBSITE: soobrasepc.com.br', 'PIX', 'PIX', 'https://www.asaas.com/i/p3yzpd1tgz6vci1i', '3', NULL),
(61817, 'pay_pycu9aato67dsygj', 254, 165.66, 'RECEIVED', '2024-09-15', '2024-09-16', '2024-08-14 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3. CRIAÇÃO E DESENVOLVIMENTO DE WEBSITE: soobrasepc.com.br', 'PIX', 'PIX', 'https://www.asaas.com/i/pycu9aato67dsygj', '2', NULL),
(61820, 'pay_cekei5aifdaye6oy', 254, 165.66, 'RECEIVED', '2024-08-15', '2024-08-15', '2024-08-14 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3. CRIAÇÃO E DESENVOLVIMENTO DE WEBSITE: soobrasepc.com.br', 'PIX', 'PIX', 'https://www.asaas.com/i/cekei5aifdaye6oy', '1', NULL),
(61823, 'pay_ml6u5ogcyuxoy006', 254, 29.90, 'RECEIVED', '2024-09-15', '2024-09-16', '2024-08-14 00:00:00', '2025-07-29 11:13:55', 'PLANO DE HOSPEDAGEM E MANUTENÇÃO SITE: soobrasepc.com.br', 'PIX', NULL, 'https://www.asaas.com/i/ml6u5ogcyuxoy006', NULL, 'sub_oil9ich37nuhoes7'),
(61825, 'pay_rdlq431wst3bcvw7', 255, 1000.00, 'RECEIVED', '2024-08-14', '2024-08-14', '2024-08-14 00:00:00', '2025-07-29 11:13:57', '', 'PIX', NULL, 'https://www.asaas.com/i/rdlq431wst3bcvw7', NULL, NULL),
(61827, 'pay_2rn9cfgb84f1ndic', 256, 29.90, 'RECEIVED', '2024-09-13', '2024-09-03', '2024-08-13 00:00:00', '2025-07-29 11:13:58', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'BOLETO', NULL, 'https://www.asaas.com/i/2rn9cfgb84f1ndic', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61830, 'pay_g03forczermnh4xo', 256, 30.62, 'RECEIVED', '2024-08-13', '2024-08-27', '2024-08-13 00:00:00', '2025-07-29 11:14:00', 'PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS VENCIMENTO', 'PIX', NULL, 'https://www.asaas.com/i/g03forczermnh4xo', NULL, 'sub_a9g0wq5bypr3lt2h'),
(61832, 'pay_gt4vk6iykzjft5wj', 256, 165.68, 'RECEIVED', '2024-10-13', '2024-10-07', '2024-08-13 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3. POSTESTAR AUTOMATICAMENTE APÓS 7 DIAS DO VENCIMENTO', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/gt4vk6iykzjft5wj', '3', NULL),
(61835, 'pay_qanw1mqcjkhyuyy5', 256, 165.66, 'RECEIVED', '2024-09-13', '2024-09-03', '2024-08-13 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3. POSTESTAR AUTOMATICAMENTE APÓS 7 DIAS DO VENCIMENTO', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/qanw1mqcjkhyuyy5', '2', NULL),
(61838, 'pay_q20s6rjjx385iu95', 256, 165.66, 'RECEIVED', '2024-08-13', '2024-08-13', '2024-08-13 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3. PROTESTAR AUTOMATICAMENTE APÓS 7 DIAS DO VENCIMENTO', 'PIX', 'PIX', 'https://www.asaas.com/i/q20s6rjjx385iu95', '1', NULL),
(61841, 'pay_r0mnxjcpbeil9syu', 265, 237.00, 'RECEIVED', '2024-08-12', '2024-08-12', '2024-08-12 00:00:00', '2025-07-29 11:14:06', 'Criação e Desenvolvimento WebSite', 'PIX', NULL, 'https://www.asaas.com/i/r0mnxjcpbeil9syu', NULL, NULL),
(61844, 'pay_rsq2p1yk19hb6pw1', 265, 130.00, 'RECEIVED', '2024-09-12', '2024-10-15', '2024-08-12 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 2. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/rsq2p1yk19hb6pw1', '2', NULL),
(61847, 'pay_czvkxn1miznf2ruo', 265, 130.00, 'RECEIVED', '2024-08-12', '2024-09-13', '2024-08-12 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 2. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/czvkxn1miznf2ruo', '1', NULL),
(61850, 'pay_fkgiciyxw27lz4sy', 257, 497.00, 'RECEIVED', '2024-08-10', '2024-08-10', '2024-08-10 00:00:00', '2025-07-29 11:14:11', '', 'PIX', NULL, 'https://www.asaas.com/i/fkgiciyxw27lz4sy', NULL, NULL),
(61853, 'pay_i18qedcqvxyb2jej', 258, 40.31, 'RECEIVED', '2024-08-13', '2024-08-14', '2024-08-09 00:00:00', '2025-07-29 11:14:12', 'Plano de Hospedagem e Manutenção Site e conta de e-mail ', 'PIX', NULL, 'https://www.asaas.com/i/i18qedcqvxyb2jej', NULL, 'sub_feo8zvrsmaf5q1tq'),
(61856, 'pay_08z20kcwepw32n0r', 259, 497.00, 'RECEIVED', '2024-08-09', '2024-08-09', '2024-08-09 00:00:00', '2025-07-29 11:14:14', 'Desenvolvimento Site WEB', 'PIX', NULL, 'https://www.asaas.com/i/08z20kcwepw32n0r', NULL, NULL),
(61859, 'pay_3vslh7xcte21jd1b', 286, 51.05, 'RECEIVED', '2024-09-15', '2024-09-17', '2024-08-08 00:00:00', '2025-07-29 11:14:15', 'Plano Hostedagem Site e E-mail Profissional', 'PIX', NULL, 'https://www.asaas.com/i/3vslh7xcte21jd1b', NULL, 'sub_fte624var188bxrz'),
(61862, 'pay_36o2wb1y3aqi4b5u', 262, 432.33, 'RECEIVED', '2024-08-08', '2024-08-08', '2024-08-08 00:00:00', '2025-07-29 11:14:17', '', 'BOLETO', NULL, 'https://www.asaas.com/i/36o2wb1y3aqi4b5u', NULL, NULL),
(61864, 'pay_a7hx780kqmphzlgu', 4294, 315.00, 'RECEIVED', '2024-08-07', '2024-08-07', '2024-08-07 00:00:00', '2025-07-29 11:14:18', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', NULL, 'https://www.asaas.com/i/a7hx780kqmphzlgu', NULL, NULL),
(61867, 'pay_2ryuuveoau6ei95m', 261, 441.11, 'RECEIVED', '2024-08-08', '2024-08-09', '2024-08-07 00:00:00', '2025-07-29 11:14:20', '', 'PIX', NULL, 'https://www.asaas.com/i/2ryuuveoau6ei95m', NULL, NULL),
(61870, 'pay_nqc7xqf6xgvjxisp', 263, 144.11, 'RECEIVED', '2024-10-06', '2024-11-11', '2024-08-06 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3. CRIAÇÃO E DESENVOLVIMENTO DE SITE', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/nqc7xqf6xgvjxisp', '3', NULL),
(61873, 'pay_kom6vzy3r1ufa84d', 263, 144.11, 'RECEIVED', '2024-09-06', '2024-10-09', '2024-08-06 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3. CRIAÇÃO E DESENVOLVIMENTO DE SITE', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/kom6vzy3r1ufa84d', '2', NULL),
(61876, 'pay_mdmbtno78kfvtemd', 263, 144.11, 'RECEIVED', '2024-08-06', '2024-09-09', '2024-08-06 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3. CRIAÇÃO E DESENVOLVIMENTO DE SITE', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mdmbtno78kfvtemd', '1', NULL),
(61879, 'pay_z2uxq8bmhtbkenep', 263, 29.90, 'RECEIVED', '2024-09-10', '2024-09-04', '2024-08-06 00:00:00', '2025-07-29 11:14:26', '', 'PIX', NULL, 'https://www.asaas.com/i/z2uxq8bmhtbkenep', NULL, 'sub_re7f5cvc2t4u6fuo'),
(61882, 'pay_cec005439v0jjvy6', 264, 29.90, 'RECEIVED', '2024-09-18', '2024-09-18', '2024-08-06 00:00:00', '2025-07-29 11:14:27', '', 'PIX', NULL, 'https://www.asaas.com/i/cec005439v0jjvy6', NULL, 'sub_ohuo9f9u852d7wki'),
(61885, 'pay_o9ihttdgri3bvb61', 264, 450.00, 'RECEIVED', '2024-08-19', '2024-08-19', '2024-08-06 00:00:00', '2025-07-29 11:14:29', 'CRIAÇÃO DE WEBSITE E PORTIFÓLIO EM PDF', 'PIX', NULL, 'https://www.asaas.com/i/o9ihttdgri3bvb61', NULL, NULL),
(61888, 'pay_yyemvpiz6sv70m1r', 265, 29.90, 'RECEIVED', '2024-09-23', '2024-09-23', '2024-08-06 00:00:00', '2025-07-29 11:14:30', 'Plano Manutenção e Hospedagem Mensal Ref. SITE [maisfacilconsignados.com.br]', 'PIX', NULL, 'https://www.asaas.com/i/yyemvpiz6sv70m1r', NULL, 'sub_xx181t17ej7qulmb'),
(61891, 'pay_6t6wms4h40pdrx8k', 266, 497.00, 'RECEIVED', '2024-08-20', '2024-08-20', '2024-07-30 00:00:00', '2025-07-29 11:14:32', 'Criação e Desenvolvimento WebsSite', 'PIX', NULL, 'https://www.asaas.com/i/6t6wms4h40pdrx8k', NULL, NULL),
(61894, 'pay_y3vr04zsp3hgxtaw', 279, 49.90, 'RECEIVED', '2024-09-05', '2024-08-26', '2024-07-29 00:00:00', '2025-07-29 11:14:33', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/y3vr04zsp3hgxtaw', NULL, 'sub_htfllf6l0cu6s4qk'),
(61897, 'pay_dpdhc7l1q8zu5cjz', 267, 29.00, 'RECEIVED', '2024-08-29', '2024-08-05', '2024-07-29 00:00:00', '2025-07-29 11:14:35', '', 'PIX', NULL, 'https://www.asaas.com/i/dpdhc7l1q8zu5cjz', NULL, 'sub_504ksi201k6033es'),
(61900, 'pay_nwmb9zjovk3ghla4', 267, 497.00, 'RECEIVED', '2024-07-29', '2024-07-29', '2024-07-29 00:00:00', '2025-07-29 11:14:36', '', 'PIX', NULL, 'https://www.asaas.com/i/nwmb9zjovk3ghla4', NULL, NULL),
(61903, 'pay_3qpymrde6gv09ihy', 268, 497.00, 'RECEIVED', '2024-07-25', '2024-07-25', '2024-07-25 00:00:00', '2025-07-29 11:14:38', '', 'PIX', NULL, 'https://www.asaas.com/i/3qpymrde6gv09ihy', NULL, NULL),
(61906, 'pay_vo47xy464tqx4lvj', 268, 29.90, 'RECEIVED', '2024-08-27', '2024-08-27', '2024-07-25 00:00:00', '2025-07-29 11:14:39', '', 'PIX', NULL, 'https://www.asaas.com/i/vo47xy464tqx4lvj', NULL, 'sub_lzkw4ntgewkejb6n'),
(61909, 'pay_ei898ua67nsfriql', 269, 82.85, 'RECEIVED', '2024-12-25', '2025-02-03', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 6 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/ei898ua67nsfriql', '6', NULL),
(61912, 'pay_jhcxfkequb02hpjd', 269, 82.83, 'RECEIVED', '2024-11-25', '2024-12-31', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 5 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/jhcxfkequb02hpjd', '5', NULL),
(61915, 'pay_5rcnufvpt6u92dyh', 269, 82.83, 'RECEIVED', '2024-10-25', '2024-11-29', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 4 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/5rcnufvpt6u92dyh', '4', NULL),
(61918, 'pay_o1dyfz3u713vt124', 269, 82.83, 'RECEIVED', '2024-09-25', '2024-10-28', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/o1dyfz3u713vt124', '3', NULL),
(61921, 'pay_2eomw99fzrzx8rwj', 269, 82.83, 'RECEIVED', '2024-08-25', '2024-09-26', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/2eomw99fzrzx8rwj', '2', NULL),
(61923, 'pay_1ntfj60zf5ekhye4', 269, 82.83, 'RECEIVED', '2024-07-25', '2024-08-26', '2024-07-24 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 6. Criação e Desenvolvimento WebSite', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/1ntfj60zf5ekhye4', '1', NULL),
(61926, 'pay_djy7ls8ovtsbxn5b', 270, 139.40, 'RECEIVED', '2024-07-24', '2024-07-24', '2024-07-24 00:00:00', '2025-07-29 11:14:50', 'Entrada: 20% do valor total de R$ 497,00 (R$99,40) mais R$40,00 referente ao registro do\r\ndomínio conforme contrato enviado pelo WhatsApp.', 'PIX', NULL, 'https://www.asaas.com/i/djy7ls8ovtsbxn5b', NULL, NULL),
(61929, 'pay_6c51ppjdezos5ujs', 272, 250.00, 'RECEIVED', '2024-07-19', '2024-07-18', '2024-07-18 00:00:00', '2025-07-29 11:14:51', 'Identidade visual completa, arquivos enviados ( PNG, CDR, AI E PDF) e um manual da marca incluso. ', 'PIX', NULL, 'https://www.asaas.com/i/6c51ppjdezos5ujs', NULL, NULL),
(61932, 'pay_m3zit83q4vj70hnz', 273, 165.68, 'OVERDUE', '2024-09-19', NULL, '2024-07-18 00:00:00', '2025-07-29 07:56:45', 'Parcela 3 de 3. Desenvolvimento de website customizado com design responsivo e otimização SEO básica. Inclui sistema de gerenciamento de conteúdo e suporte técnico por um ano após a entrega.', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/m3zit83q4vj70hnz', '3', NULL),
(61935, 'pay_anlb2s72bwu3nfs9', 273, 165.66, 'OVERDUE', '2024-08-19', NULL, '2024-07-18 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 3. Desenvolvimento de website customizado com design responsivo e otimização SEO básica. Inclui sistema de gerenciamento de conteúdo e suporte técnico por um ano após a entrega.', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/anlb2s72bwu3nfs9', '2', NULL),
(61938, 'pay_qz0zjph2uk9omt69', 273, 165.66, 'DUNNING_REQUESTED', '2024-07-19', NULL, '2024-07-18 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 3. Desenvolvimento de website customizado com design responsivo e otimização SEO básica. Inclui sistema de gerenciamento de conteúdo e suporte técnico por um ano após a entrega.', 'UNDEFINED', 'UNDEFINED', 'https://www.asaas.com/i/qz0zjph2uk9omt69', '1', NULL),
(61941, 'pay_9k68isuzd8gtgwjz', 283, 173.79, 'RECEIVED', '2024-08-27', '2024-09-05', '2024-07-18 00:00:00', '2025-07-29 11:14:58', 'Plano E-commerce +', 'PIX', NULL, 'https://www.asaas.com/i/9k68isuzd8gtgwjz', NULL, 'sub_zrdtg2wholyl1apf'),
(61944, 'pay_s4a6itnpn1wgr611', 284, 29.90, 'RECEIVED', '2024-08-25', '2024-08-23', '2024-07-17 00:00:00', '2025-07-29 11:14:59', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/s4a6itnpn1wgr611', NULL, 'sub_khpfl1dpc6pfj9ix'),
(61947, 'pay_3gr6rj8ugoliiys0', 275, 497.00, 'RECEIVED', '2024-07-10', '2024-07-10', '2024-07-10 00:00:00', '2025-07-29 11:15:01', 'CRIAÇÃO DE WEBSITE ESCOLINHA PALMEIRINHA', 'PIX', NULL, 'https://www.asaas.com/i/3gr6rj8ugoliiys0', NULL, NULL),
(61950, 'pay_3p5pmnce6om5u6sw', 274, 347.00, 'OVERDUE', '2024-08-06', NULL, '2024-07-09 00:00:00', '2025-07-29 11:15:02', 'CONSTRUÇÃO WEBSITE PORTAL ESPORTIVO COM INTEGRAÇÃO DE BLOG', 'UNDEFINED', NULL, 'https://www.asaas.com/i/3p5pmnce6om5u6sw', NULL, NULL),
(61952, 'pay_8ps86k7dl9gcimgv', 274, 190.00, 'RECEIVED', '2024-07-09', '2024-07-09', '2024-07-09 00:00:00', '2025-07-29 11:15:04', 'Registro de Domínio .com.br + (Entrada Ref. WebSite Portal Informativo Esportes)', 'PIX', NULL, 'https://www.asaas.com/i/8ps86k7dl9gcimgv', NULL, NULL),
(61955, 'pay_6o5b40xd3gzj9zy3', 276, 49.90, 'RECEIVED', '2024-08-13', '2024-08-13', '2024-07-08 00:00:00', '2025-07-29 11:15:05', 'PLANO HOSPEDAGEM + E-MAIL PROFISSIONAL', 'BOLETO', NULL, 'https://www.asaas.com/i/6o5b40xd3gzj9zy3', NULL, 'sub_7x7eg5yslkoydx4c'),
(61958, 'pay_7d01udwo98vlz18e', 277, 400.00, 'RECEIVED', '2024-08-12', '2024-08-12', '2024-07-08 00:00:00', '2025-07-29 11:15:07', 'PLANO ESTRUTURA DIGITAL [GESTÃO TRÁFEGO, GOOGLE MEU NEGÓCIO, E-COMMERCE]', 'BOLETO', NULL, 'https://www.asaas.com/i/7d01udwo98vlz18e', NULL, 'sub_lwjilqkffsoyi3as'),
(61961, 'pay_isujyfiszbwj5v79', 286, 49.90, 'RECEIVED', '2024-08-15', '2024-08-12', '2024-07-08 00:00:00', '2025-07-29 11:15:08', 'Plano Hostedagem Site e E-mail Profissional', 'BOLETO', NULL, 'https://www.asaas.com/i/isujyfiszbwj5v79', NULL, 'sub_fte624var188bxrz'),
(61964, 'pay_mgngurcubkmygjwj', 275, 30.49, 'RECEIVED', '2024-08-13', '2024-08-14', '2024-07-08 00:00:00', '2025-07-29 11:15:10', 'PLANO MENSAL HOSPEDAGEM + EMAIL PROFISSIONAL ', 'PIX', NULL, 'https://www.asaas.com/i/mgngurcubkmygjwj', NULL, 'sub_d9vxnk8na9bb4e4b'),
(61967, 'pay_up8u8f1m0y6694vo', 276, 375.00, 'RECEIVED', '2024-08-13', '2024-08-13', '2024-07-08 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 2. DESENVOLVIMENTO WEBSITE [BLOG INTEGRADO, CATÁLOGO DE PRODUTOS]', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/up8u8f1m0y6694vo', '2', NULL),
(61970, 'pay_0hf091g21l6q7cvr', 276, 375.00, 'RECEIVED', '2024-07-10', '2024-07-11', '2024-07-08 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 2. DESENVOLVIMENTO WEBSITE [BLOG INTEGRADO, CATÁLOGO DE PRODUTOS]', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/0hf091g21l6q7cvr', '1', NULL),
(61973, 'pay_ssx50p30403v469f', 276, 49.90, 'RECEIVED', '2024-07-10', '2024-07-11', '2024-07-08 00:00:00', '2025-07-29 11:15:14', 'PLANO HOSPEDAGEM + E-MAIL PROFISSIONAL', 'BOLETO', NULL, 'https://www.asaas.com/i/ssx50p30403v469f', NULL, 'sub_7x7eg5yslkoydx4c'),
(61976, 'pay_ym5ter0hx6hqnywo', 278, 550.00, 'RECEIVED', '2024-08-10', '2024-08-12', '2024-07-08 00:00:00', '2025-07-29 07:56:45', 'Parcela 2 de 4. Implantação Sistema de Automação Chatbot, SiteBot, E-mail MArketing, Gestão de Tráfego Meta Ads, Google Ads', 'PIX', 'PIX', 'https://www.asaas.com/i/ym5ter0hx6hqnywo', '2', NULL),
(61979, 'pay_810q4fffolxsas7j', 278, 550.00, 'RECEIVED', '2024-07-10', '2024-07-10', '2024-07-08 00:00:00', '2025-07-29 07:56:45', 'Parcela 1 de 4. Implantação Sistema de Automação Chatbot, SiteBot, E-mail MArketing, Gestão de Tráfego Meta Ads, Google Ads', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/810q4fffolxsas7j', '1', NULL),
(61982, 'pay_aqytk4a4wa9btjyb', 279, 49.90, 'RECEIVED', '2024-08-05', '2024-07-03', '2024-07-03 00:00:00', '2025-07-29 11:15:19', 'Plano Mensal Manutenção e Hospedagem', 'PIX', NULL, 'https://www.asaas.com/i/aqytk4a4wa9btjyb', NULL, 'sub_htfllf6l0cu6s4qk'),
(61986, 'pay_6dv8tg6mem4y3q62', 279, 248.50, 'RECEIVED', '2024-08-05', '2024-07-26', '2024-07-03 00:00:00', '2025-07-29 07:56:46', 'Parcela 2 de 2. Desenvolvimento Web Site: apousadadapraiaslz.com.br', 'PIX', 'PIX', 'https://www.asaas.com/i/6dv8tg6mem4y3q62', '2', NULL),
(61989, 'pay_kd2n284nvn4l95tb', 279, 248.50, 'RECEIVED', '2024-07-05', '2024-07-03', '2024-07-03 00:00:00', '2025-07-29 07:56:46', 'Parcela 1 de 2. Desenvolvimento Web Site: apousadadapraiaslz.com.br', 'PIX', 'PIX', 'https://www.asaas.com/i/kd2n284nvn4l95tb', '1', NULL),
(61992, 'pay_stiz5kb897xiiiww', 280, 165.67, 'OVERDUE', '2024-08-29', NULL, '2024-06-27 00:00:00', '2025-07-29 07:56:46', 'Parcela 2 de 2.', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/stiz5kb897xiiiww', '2', NULL),
(61995, 'pay_76ybstgt8ldjwa5l', 280, 165.66, 'DUNNING_REQUESTED', '2024-07-29', NULL, '2024-06-27 00:00:00', '2025-07-29 07:56:46', 'Parcela 1 de 2.', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/76ybstgt8ldjwa5l', '1', NULL),
(61997, 'pay_q58u8rhngwxhnxf3', 280, 205.67, 'RECEIVED', '2024-06-28', '2024-06-27', '2024-06-27 00:00:00', '2025-07-29 11:15:27', 'Criação e Desenvolvimento WebSite \r\nReferente Parcela 1/3 - R$ 165,66\r\nValor Registro domínio: R$ 40,00', 'PIX', NULL, 'https://www.asaas.com/i/q58u8rhngwxhnxf3', NULL, NULL),
(62000, 'pay_e30otq2qnwsydn9t', 281, 51.18, 'RECEIVED', '2024-07-27', '2024-08-14', '2024-06-27 00:00:00', '2025-07-29 11:15:29', 'Plano Mensal Suporte e Hospedagem PIXEL12DIGITAL', 'PIX', NULL, 'https://www.asaas.com/i/e30otq2qnwsydn9t', NULL, 'sub_fmc9dt71v8n29a4i'),
(62003, 'pay_miri6p46vqpqad06', 281, 297.00, 'RECEIVED', '2024-06-28', '2024-06-28', '2024-06-27 00:00:00', '2025-07-29 11:15:31', 'Criação de Plataforma Digital para Vendas Online', 'PIX', NULL, 'https://www.asaas.com/i/miri6p46vqpqad06', NULL, NULL),
(62006, 'pay_qrj0wi97u1zxkxia', 283, 173.85, 'RECEIVED', '2024-07-26', '2024-08-05', '2024-06-25 00:00:00', '2025-07-29 11:15:32', 'Plano E-commerce +', 'BOLETO', NULL, 'https://www.asaas.com/i/qrj0wi97u1zxkxia', NULL, 'sub_zrdtg2wholyl1apf'),
(62009, 'pay_assaijzykdryaqu9', 282, 220.00, 'RECEIVED', '2024-06-25', '2024-06-25', '2024-06-25 00:00:00', '2025-07-29 11:15:34', 'Criação Catálogo Virtual Instagram Integrando Produtos Loja Virtual', 'PIX', NULL, 'https://www.asaas.com/i/assaijzykdryaqu9', NULL, NULL),
(62011, 'pay_o3m5qxb7wqm1xv6o', 283, 169.90, 'RECEIVED', '2024-06-26', '2024-06-26', '2024-06-25 00:00:00', '2025-07-29 11:15:35', 'Plano E-commerce +', 'PIX', NULL, 'https://www.asaas.com/i/o3m5qxb7wqm1xv6o', NULL, 'sub_zrdtg2wholyl1apf'),
(62013, 'pay_hyq83xatxyxnyxzu', 284, 29.90, 'RECEIVED', '2024-07-25', '2024-07-25', '2024-06-25 00:00:00', '2025-07-29 11:15:37', 'Plano Hospedagem Essencial', 'PIX', NULL, 'https://www.asaas.com/i/hyq83xatxyxnyxzu', NULL, 'sub_khpfl1dpc6pfj9ix'),
(62015, 'pay_mwz1fp3domm5733j', 284, 99.00, 'RECEIVED', '2024-08-26', '2024-09-30', '2024-06-25 00:00:00', '2025-07-29 07:56:46', 'Parcela 3 de 3. Criação e Configuração Landing Page', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mwz1fp3domm5733j', '3', NULL),
(62018, 'pay_mzraltaxep402v6c', 284, 99.00, 'RECEIVED', '2024-07-26', '2024-08-29', '2024-06-25 00:00:00', '2025-07-29 07:56:46', 'Parcela 2 de 3. Criação e Configuração Landing Page', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/mzraltaxep402v6c', '2', NULL),
(62021, 'pay_4cie9ikeyxsj64lt', 284, 99.00, 'RECEIVED', '2024-06-26', '2024-07-29', '2024-06-25 00:00:00', '2025-07-29 07:56:46', 'Parcela 1 de 3. Criação e Configuração Landing Page', 'CREDIT_CARD', 'CREDIT_CARD', 'https://www.asaas.com/i/4cie9ikeyxsj64lt', '1', NULL),
(62024, 'pay_r37b0a41v1473szh', 286, 100.00, 'RECEIVED', '2024-07-15', '2024-06-19', '2024-06-13 00:00:00', '2025-07-29 11:15:43', 'Plano Hostedagem Site wcpocos.com.br\r\nConta E-mail Profissional\r\nPlano atualização e manutenção Mensal Site Institucional e Google Maps', 'PIX', NULL, 'https://www.asaas.com/i/r37b0a41v1473szh', NULL, 'sub_fte624var188bxrz'),
(62027, 'pay_ljqwkxb410d7wlsc', 286, 350.00, 'RECEIVED', '2024-06-14', '2024-06-13', '2024-06-13 00:00:00', '2025-07-29 11:15:44', 'Criação e Configuração de Site Intitucional e Google Maps', 'PIX', NULL, 'https://www.asaas.com/i/ljqwkxb410d7wlsc', NULL, NULL),
(62030, 'pay_yghzvqocg21n86kn', 285, 680.00, 'RECEIVED', '2024-11-26', '2024-11-26', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 6 de 6. Contrato Prestação de Serviços Pixel12Digital', 'PIX', 'PIX', 'https://www.asaas.com/i/yghzvqocg21n86kn', '6', NULL),
(62033, 'pay_77ncl1wd7ui3iou5', 285, 680.00, 'RECEIVED', '2024-11-04', '2024-11-04', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 5 de 6. Contrato Prestação de Serviços Pixel12Digital', 'PIX', 'PIX', 'https://www.asaas.com/i/77ncl1wd7ui3iou5', '5', NULL),
(62036, 'pay_9cytpvjzxznq98bx', 285, 680.00, 'RECEIVED', '2024-09-26', '2024-09-26', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 4 de 6. Contrato Prestação de Serviços Pixel12Digital', 'PIX', 'PIX', 'https://www.asaas.com/i/9cytpvjzxznq98bx', '4', NULL),
(62039, 'pay_qmxuhzz9ox6yfe1h', 285, 680.00, 'RECEIVED', '2024-08-26', '2024-08-26', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 3 de 6. Contrato Prestação de Serviços Pixel12Digital', 'PIX', 'PIX', 'https://www.asaas.com/i/qmxuhzz9ox6yfe1h', '3', NULL),
(62042, 'pay_um1zksjhioie8boo', 285, 680.00, 'RECEIVED', '2024-07-15', '2024-07-15', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 2 de 6. Contrato Prestação de Serviços Pixel12Digital', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/um1zksjhioie8boo', '2', NULL),
(62044, 'pay_rq6mq8595yezrqa2', 285, 680.00, 'RECEIVED', '2024-06-24', '2024-06-25', '2024-06-13 00:00:00', '2025-07-29 07:56:46', 'Parcela 1 de 6. Contrato Prestação de Serviços Pixel12Digital', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/rq6mq8595yezrqa2', '1', NULL),
(67417, 'pay_test_123456', 153, 100.00, 'PENDING', '2025-08-15', NULL, '2025-07-28 10:00:00', '2025-07-28 10:51:05', 'Teste de cobrança', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/123456', '1', NULL),
(67664, 'pay_test_melhorado', 153, 150.00, 'PENDING', '2025-08-20', NULL, '2025-07-28 11:00:00', '2025-07-28 10:54:49', 'Teste de sincronização melhorada', 'BOLETO', 'BOLETO', 'https://www.asaas.com/i/teste', '1', NULL),
(79160, 'pay_4c1qn0avqrk2p17d', 236, 90.00, 'RECEIVED', '2025-07-30', NULL, NULL, NULL, NULL, NULL, NULL, 'https://www.asaas.com/i/pay_4c1qn0avqrk2p17d', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` enum('texto','numero','booleano','json') NOT NULL DEFAULT 'texto',
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `descricao`, `tipo`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'asaas_api_key', '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjFkZGExMjcyLWMzN2MtNGM3MS1iMTBmLTY4YWU4MjM4ZmE1Nzo6JGFhY2hfM2EzNTI4OTUtOGFjNC00MmFlLTliZTItNjRkZDg2YTAzOWRj', 'Chave da API do Asaas', 'texto', '2025-07-18 21:47:45', '2025-07-28 11:55:48'),
(2, 'asaas_ambiente', 'prod', 'Ambiente do Asaas', 'texto', '2025-07-18 21:47:45', '2025-07-23 01:18:37'),
(3, 'whatsapp_webhook_url', '', 'URL do webhook do WhatsApp', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(4, 'whatsapp_vps_url', 'http://212.85.11.238:3000', 'URL do servidor VPS do WhatsApp', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(5, 'sistema_nome', 'Pixel12 Digital', 'Nome do sistema', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(6, 'sistema_versao', '2.0', 'Versão do sistema', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(7, 'monitoramento_ativo', '1', 'Monitoramento automático ativo', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(8, 'max_mensagens_dia', '50', 'Máximo de mensagens por dia', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(9, 'horario_inicio_envio', '09:00', 'Horário de início para envio', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(10, 'horario_fim_envio', '18:00', 'Horário de fim para envio', 'texto', '2025-07-18 21:47:45', '2025-07-18 21:47:45'),
(11, 'sistema_status', 'Sistema ativo e funcionando', 'Configuração automática do sistema', 'texto', '2025-07-18 22:23:34', '2025-07-18 22:23:34'),
(12, 'ultima_atualizacao', '2025-07-18 19:23:34', 'Configuração automática do sistema', 'texto', '2025-07-18 22:23:34', '2025-07-18 22:23:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `canal_id` int(11) NOT NULL,
  `nome` varchar(64) NOT NULL,
  `codigo` varchar(16) NOT NULL,
  `descricao` text DEFAULT NULL,
  `palavras_chave` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`palavras_chave`)),
  `ia_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ia_config`)),
  `status` enum('ativo','inativo','manutencao') DEFAULT 'ativo',
  `ordem_exibicao` int(11) DEFAULT 1,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `departamentos`
--

INSERT INTO `departamentos` (`id`, `canal_id`, `nome`, `codigo`, `descricao`, `palavras_chave`, `ia_config`, `status`, `ordem_exibicao`, `data_criacao`, `data_atualizacao`) VALUES
(1, 36, 'FINANCEIRO', 'FIN', 'Atendimento para faturas, pagamentos e questões financeiras', '[\"fatura\",\"boleto\",\"pagamento\",\"vencimento\",\"pagar\",\"consulta\",\"dinheiro\",\"valor\"]', '{\"especialidade\":\"financeiro\",\"url_externa\":\"\",\"fallback_ativo\":true,\"transfer_humano\":true}', 'ativo', 1, '2025-08-02 14:53:14', '2025-08-02 14:53:14'),
(2, 36, 'SUPORTE', 'SUP', 'Suporte técnico, problemas e dúvidas sobre serviços', '[\"suporte\",\"problema\",\"erro\",\"nu00e3o funciona\",\"bug\",\"tu00e9cnico\",\"ajuda\"]', '{\"especialidade\":\"suporte\",\"url_externa\":\"\",\"fallback_ativo\":true,\"transfer_humano\":true}', 'ativo', 2, '2025-08-02 14:53:14', '2025-08-02 14:53:14'),
(3, 36, 'COMERCIAL', 'COM', 'Vendas, orçamentos e informações comerciais', '[\"comercial\",\"venda\",\"preu00e7o\",\"oru00e7amento\",\"proposta\",\"plano\",\"contratar\"]', '{\"especialidade\":\"comercial\",\"url_externa\":\"\",\"fallback_ativo\":true,\"transfer_humano\":true}', 'ativo', 3, '2025-08-02 14:53:14', '2025-08-02 14:53:14'),
(4, 36, 'ADMINISTRAÇÃO', 'ADM', 'Questões administrativas, contratos e documentos', '[\"administrativo\",\"contrato\",\"documento\",\"cpf\",\"cnpj\",\"cadastro\"]', '{\"especialidade\":\"administrativo\",\"url_externa\":\"\",\"fallback_ativo\":true,\"transfer_humano\":true}', 'ativo', 4, '2025-08-02 14:53:15', '2025-08-02 14:53:15');

-- --------------------------------------------------------

--
-- Estrutura da tabela `faturas`
--

CREATE TABLE `faturas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `asaas_id` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `invoice_url` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `invoices`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `invoices` (
`id` int(11)
,`asaas_id` varchar(255)
,`client_id` int(11)
,`amount_cents` decimal(13,2)
,`due_date` date
,`status` varchar(50)
,`invoice_url` varchar(255)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs_integracao_ana`
--

CREATE TABLE `logs_integracao_ana` (
  `id` int(11) NOT NULL,
  `numero_cliente` varchar(20) DEFAULT NULL,
  `mensagem_enviada` text DEFAULT NULL,
  `resposta_ana` text DEFAULT NULL,
  `acao_sistema` varchar(50) DEFAULT NULL,
  `departamento_detectado` varchar(10) DEFAULT NULL,
  `tempo_resposta_ms` int(11) DEFAULT NULL,
  `status_api` varchar(20) DEFAULT NULL,
  `transferencia_executada` tinyint(1) DEFAULT 0,
  `data_log` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `logs_integracao_ana`
--

INSERT INTO `logs_integracao_ana` (`id`, `numero_cliente`, `mensagem_enviada`, `resposta_ana`, `acao_sistema`, `departamento_detectado`, `tempo_resposta_ms`, `status_api`, `transferencia_executada`, `data_log`) VALUES
(1, '5547999999999', 'Quero um site para minha empresa', 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:07:45'),
(2, '5547999999998', 'Meu site está fora do ar', 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:07:47'),
(3, '5547999999997', 'Quero falar com uma pessoa', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:07:50'),
(4, '5547999999999', 'Teste diagnóstico - 17:16:34', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:16:35'),
(5, '5547999999999', 'teste seguro', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:38:40'),
(6, '5547000000000', 'teste robusto', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 20:59:41'),
(7, '5547999999999', 'Teste de verificação do webhook', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 21:26:44'),
(8, '5547999999999', 'Teste de verificação do webhook', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 21:32:26'),
(9, '5547999999999', 'Ana, você está me ouvindo?', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 21:44:21'),
(10, '5547999999999', 'Ana, você está me ouvindo?', 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', 'fallback_local', NULL, NULL, 'success', 0, '2025-08-02 21:47:32'),
(11, '5547999999999@c.us', 'TESTE MANUAL DE CONECTIVIDADE - 19:00:16', 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', 'nenhuma', NULL, NULL, 'success', 0, '2025-08-02 22:00:19'),
(12, '5547999999999@c.us', 'TESTE FINAL - Sistema funcionando? - 19:01:17', 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', 'nenhuma', NULL, NULL, 'success', 0, '2025-08-02 22:01:21'),
(13, '5547999999999@c.us', 'TESTE FINAL - Sistema funcionando? - 19:02:10', 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', 'nenhuma', NULL, NULL, 'success', 0, '2025-08-02 22:02:13'),
(14, '5547999999999@c.us', 'Teste direto', 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', 'nenhuma', NULL, NULL, 'success', 0, '2025-08-02 22:02:51'),
(15, '5547999999999@c.us', '🧪 TESTE AUTOMÁTICO - 19:04:56\\n\\nOlá Ana! Este é um teste automático para verificar se você está funcionando corretamente.', 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', 'nenhuma', NULL, NULL, 'success', 0, '2025-08-02 22:05:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `log_alteracoes`
--

CREATE TABLE `log_alteracoes` (
  `id` int(11) NOT NULL,
  `tabela` varchar(50) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `campo` varchar(100) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_novo` text DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens_agendadas`
--

CREATE TABLE `mensagens_agendadas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'cobranca_vencida',
  `prioridade` enum('alta','normal','baixa') NOT NULL DEFAULT 'normal',
  `data_agendada` datetime NOT NULL,
  `status` enum('agendada','enviada','cancelada','erro') NOT NULL DEFAULT 'agendada',
  `observacao` text DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `mensagens_agendadas`
--

INSERT INTO `mensagens_agendadas` (`id`, `cliente_id`, `mensagem`, `tipo`, `prioridade`, `data_agendada`, `status`, `observacao`, `data_criacao`, `data_atualizacao`) VALUES
(4, 145, 'Olá Nicelio! \n\n⚠️ Você possui faturas em aberto:\n\n• Fatura #59720 - R$ 232,33 - Venceu em 23/07/2025 (6 dias vencida)\n\n💰 Valor total em aberto: R$ 232,33\n🔗 Link para pagamento: https://www.asaas.com/i/uatslddjrkpj9saj\n\nPara consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital', 'cobranca_vencida', 'alta', '2025-07-29 17:35:00', 'enviada', NULL, '2025-07-29 20:28:41', '2025-07-29 20:34:05'),
(162, 169, 'Olá Gilberto! 👋\n\n⚠️ Você possui 9 fatura(s) vencida(s):\n\n📄 Fatura #60474\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/03/2025 (145 dias vencida)\n🔗 Link: https://www.asaas.com/i/42d6vcbu3aulpsa2\n\n📄 Fatura #60476\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/03/2025 (122 dias vencida)\n🔗 Link: https://www.asaas.com/i/3f44zz1614zffkx1\n\n📄 Fatura #60472\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/04/2025 (114 dias vencida)\n🔗 Link: https://www.asaas.com/i/i8o928t0fv4lz5nv\n\n📄 Fatura #60231\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/04/2025 (91 dias vencida)\n🔗 Link: https://www.asaas.com/i/36dfc9yb00s4gpca\n\n📄 Fatura #60470\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/05/2025 (84 dias vencida)\n🔗 Link: https://www.asaas.com/i/ocfirj3d1sxumch7\n\n📄 Fatura #60004\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/05/2025 (61 dias vencida)\n🔗 Link: https://www.asaas.com/i/irj2qsi1xmd22ij8\n\n📄 Fatura #60468\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/06/2025 (53 dias vencida)\n🔗 Link: https://www.asaas.com/i/x1mgpvtrzpk1sybj\n\n📄 Fatura #59872\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/06/2025 (30 dias vencida)\n🔗 Link: https://www.asaas.com/i/m6xtvkuotror0mzs\n\n📄 Fatura #60466\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/07/2025 (23 dias vencida)\n🔗 Link: https://www.asaas.com/i/7y8g8wuppm4zs73l\n\n📋 Você possui 9 fatura(s) a vencer:\n\n📄 Fatura #59751\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/07/2025 (em 0 dias)\n🔗 Link: https://www.asaas.com/i/dgxji2z6m97tdge6\n\n📄 Fatura #60464\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/08/2025 (em 8 dias)\n🔗 Link: https://www.asaas.com/i/tegbo50fkkbh005k\n\n📄 Fatura #59606\n💰 Valor: R$ 29,90\n📅 Vencimento: 30/08/2025 (em 31 dias)\n🔗 Link: https://www.asaas.com/i/w7hsz7y6wwjw1xmb\n\n📄 Fatura #60462\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/09/2025 (em 39 dias)\n🔗 Link: https://www.asaas.com/i/qkpf9w05xoo42xog\n\n📄 Fatura #60460\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/10/2025 (em 69 dias)\n🔗 Link: https://www.asaas.com/i/tcnzsvey1mxzzfvd\n\n📄 Fatura #60458\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/11/2025 (em 100 dias)\n🔗 Link: https://www.asaas.com/i/3jwpxw067itk4t03\n\n📄 Fatura #60456\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/12/2025 (em 130 dias)\n🔗 Link: https://www.asaas.com/i/nrbetpjhf3knbzqe\n\n📄 Fatura #60454\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/01/2026 (em 161 dias)\n🔗 Link: https://www.asaas.com/i/1qtit2qt7p05bu6y\n\n📄 Fatura #60452\n💰 Valor: R$ 97,00\n📅 Vencimento: 07/02/2026 (em 192 dias)\n🔗 Link: https://www.asaas.com/i/mzm4bbxyznnl5m6o\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 09:00:00', 'agendada', NULL, '2025-07-30 21:59:08', '2025-07-30 21:59:08'),
(163, 167, 'Olá Renato! 👋\n\n⚠️ Você possui 5 fatura(s) vencida(s):\n\n📄 Fatura #60261\n💰 Valor: R$ 395,33\n📅 Vencimento: 21/04/2025 (100 dias vencida)\n🔗 Link: https://www.asaas.com/i/rwk8e254q74i0x44\n\n📄 Fatura #60259\n💰 Valor: R$ 395,34\n📅 Vencimento: 21/05/2025 (70 dias vencida)\n🔗 Link: https://www.asaas.com/i/ykq6no3ax3l06yy9\n\n📄 Fatura #60047\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/05/2025 (66 dias vencida)\n🔗 Link: https://www.asaas.com/i/f2bhqkkbjqjl5qp6\n\n📄 Fatura #60045\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/06/2025 (35 dias vencida)\n🔗 Link: https://www.asaas.com/i/e7vw5l99eqjxlh7v\n\n📄 Fatura #60043\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/07/2025 (5 dias vencida)\n🔗 Link: https://www.asaas.com/i/wfufg2t4pv0a50j1\n\n📋 Você possui 8 fatura(s) a vencer:\n\n📄 Fatura #60041\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/08/2025 (em 26 dias)\n🔗 Link: https://www.asaas.com/i/d1niejnyr0o9chrv\n\n📄 Fatura #60039\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/09/2025 (em 57 dias)\n🔗 Link: https://www.asaas.com/i/rn1pxp2lp19mws7g\n\n📄 Fatura #60037\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/10/2025 (em 87 dias)\n🔗 Link: https://www.asaas.com/i/j8ai8xttjaycd3gw\n\n📄 Fatura #60035\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/11/2025 (em 118 dias)\n🔗 Link: https://www.asaas.com/i/d10bg1de8vl0ntnk\n\n📄 Fatura #60033\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/12/2025 (em 148 dias)\n🔗 Link: https://www.asaas.com/i/e8xfqvugeppl9ks1\n\n📄 Fatura #60031\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/01/2026 (em 179 dias)\n🔗 Link: https://www.asaas.com/i/jdvbmef123t9u07p\n\n📄 Fatura #60029\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/02/2026 (em 210 dias)\n🔗 Link: https://www.asaas.com/i/y62163vc5uo6u321\n\n📄 Fatura #60027\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/04/2026 (em 269 dias)\n🔗 Link: https://www.asaas.com/i/1g6t0gpd275461qc\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:09', '2025-07-30 21:59:09'),
(164, 209, 'Olá Álvaro! 👋\n\n⚠️ Você possui 9 fatura(s) vencida(s):\n\n📄 Fatura #61025\n💰 Valor: R$ 29,90\n📅 Vencimento: 22/01/2025 (189 dias vencida)\n🔗 Link: https://www.asaas.com/i/3gzgb8ay122nwtx6\n\n📄 Fatura #60900\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/02/2025 (170 dias vencida)\n🔗 Link: https://www.asaas.com/i/9yxxgmr4ykles2rr\n\n📄 Fatura #61016\n💰 Valor: R$ 296,50\n📅 Vencimento: 22/02/2025 (158 dias vencida)\n🔗 Link: https://www.asaas.com/i/ghplitv633148smu\n\n📄 Fatura #60731\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/69cxg0v3cv2qg34u\n\n📄 Fatura #61014\n💰 Valor: R$ 296,50\n📅 Vencimento: 22/03/2025 (130 dias vencida)\n🔗 Link: https://www.asaas.com/i/648mfzaqys0emb77\n\n📄 Fatura #60520\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/04/2025 (111 dias vencida)\n🔗 Link: https://www.asaas.com/i/cisuuabc2qf7vkk1\n\n📄 Fatura #61022\n💰 Valor: R$ 296,50\n📅 Vencimento: 22/04/2025 (99 dias vencida)\n🔗 Link: https://www.asaas.com/i/yxqwlppgltowi67q\n\n📄 Fatura #59966\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/06/2025 (50 dias vencida)\n🔗 Link: https://www.asaas.com/i/mvqrexky1422ja0g\n\n📄 Fatura #59836\n💰 Valor: R$ 29,90\n📅 Vencimento: 15/07/2025 (15 dias vencida)\n🔗 Link: https://www.asaas.com/i/2flquo1d37igmlkn\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59680\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/08/2025 (em 11 dias)\n🔗 Link: https://www.asaas.com/i/fz91gvd5mj3ke2m8\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:09', '2025-07-30 21:59:09'),
(165, 211, 'Olá Sidney! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #61324\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/02/2025 (165 dias vencida)\n🔗 Link: https://www.asaas.com/i/b9kvfx8jebwq7wvf\n\n📄 Fatura #61322\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/03/2025 (137 dias vencida)\n🔗 Link: https://www.asaas.com/i/6v20fq276jevjlcd\n\n📄 Fatura #61319\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/04/2025 (106 dias vencida)\n🔗 Link: https://www.asaas.com/i/v5eq0x5xtjdug77p\n\n📄 Fatura #61316\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/05/2025 (76 dias vencida)\n🔗 Link: https://www.asaas.com/i/7218lq1no3lpz9q9\n\n📄 Fatura #61313\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/06/2025 (45 dias vencida)\n🔗 Link: https://www.asaas.com/i/7esob030iux67yus\n\n📄 Fatura #61311\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/07/2025 (15 dias vencida)\n🔗 Link: https://www.asaas.com/i/zcjlk7qj5kiql71d\n\n📋 Você possui 4 fatura(s) a vencer:\n\n📄 Fatura #61308\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/08/2025 (em 16 dias)\n🔗 Link: https://www.asaas.com/i/ydgibbuauxzflbh7\n\n📄 Fatura #61305\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/09/2025 (em 47 dias)\n🔗 Link: https://www.asaas.com/i/19jt4jr3kntqyzdj\n\n📄 Fatura #61302\n💰 Valor: R$ 139,00\n📅 Vencimento: 15/10/2025 (em 77 dias)\n🔗 Link: https://www.asaas.com/i/pu5vbuazbxtntrhk\n\n📄 Fatura #61299\n💰 Valor: R$ 29,90\n📅 Vencimento: 15/11/2025 (em 108 dias)\n🔗 Link: https://www.asaas.com/i/0whnkfn26hkfm917\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:10', '2025-07-30 21:59:10'),
(166, 191, 'Olá Solange! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #60444\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/04/2025 (104 dias vencida)\n🔗 Link: https://www.asaas.com/i/av7lx25ux6oha8ef\n\n📄 Fatura #60283\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/05/2025 (74 dias vencida)\n🔗 Link: https://www.asaas.com/i/lfdb2n528fjhwr2v\n\n📄 Fatura #60281\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/06/2025 (43 dias vencida)\n🔗 Link: https://www.asaas.com/i/zboiw7a7wr4sdjxi\n\n📄 Fatura #60279\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/07/2025 (13 dias vencida)\n🔗 Link: https://www.asaas.com/i/nz6kcsgw60es07w6\n\n📋 Você possui 5 fatura(s) a vencer:\n\n📄 Fatura #60277\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/08/2025 (em 18 dias)\n🔗 Link: https://www.asaas.com/i/z5krbiwpunn71yyl\n\n📄 Fatura #60275\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/09/2025 (em 49 dias)\n🔗 Link: https://www.asaas.com/i/d7mtvn7z868hicr4\n\n📄 Fatura #60273\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/10/2025 (em 79 dias)\n🔗 Link: https://www.asaas.com/i/75n48g4cw2e640df\n\n📄 Fatura #60271\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/11/2025 (em 110 dias)\n🔗 Link: https://www.asaas.com/i/ise711vkgeunhhkm\n\n📄 Fatura #60269\n💰 Valor: R$ 29,90\n📅 Vencimento: 17/12/2025 (em 140 dias)\n🔗 Link: https://www.asaas.com/i/o9qu8hy53c6u1gkr\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:11', '2025-07-30 21:59:11'),
(167, 199, 'Olá Lorrainy! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #61078\n💰 Valor: R$ 237,20\n📅 Vencimento: 10/02/2025 (170 dias vencida)\n🔗 Link: https://www.asaas.com/i/oaq2pnkjfze6fkhb\n\n📄 Fatura #61075\n💰 Valor: R$ 237,20\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/9riegbacxnhah0x3\n\n📄 Fatura #61072\n💰 Valor: R$ 237,20\n📅 Vencimento: 10/04/2025 (111 dias vencida)\n🔗 Link: https://www.asaas.com/i/94sa86ov6enpg4mf\n\n📄 Fatura #60171\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/05/2025 (81 dias vencida)\n🔗 Link: https://www.asaas.com/i/6vqmzc0t3vgf7fag\n\n📄 Fatura #59970\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/06/2025 (50 dias vencida)\n🔗 Link: https://www.asaas.com/i/gtqomw6y8ooxs2mr\n\n📄 Fatura #59840\n💰 Valor: R$ 29,90\n📅 Vencimento: 15/07/2025 (15 dias vencida)\n🔗 Link: https://www.asaas.com/i/eh74zklunsj3mekv\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59684\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/08/2025 (em 11 dias)\n🔗 Link: https://www.asaas.com/i/xb7dxpn87tsptzlh\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:11', '2025-07-30 21:59:11'),
(168, 257, 'Olá Gutemberg! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #60932\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/02/2025 (168 dias vencida)\n🔗 Link: https://www.asaas.com/i/jykyzrt5j8m5ne63\n\n📄 Fatura #60765\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/03/2025 (140 dias vencida)\n🔗 Link: https://www.asaas.com/i/6vkm7bg9gslg74es\n\n📄 Fatura #60506\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/04/2025 (109 dias vencida)\n🔗 Link: https://www.asaas.com/i/tkdd5qkbs57nkug7\n\n📄 Fatura #60155\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/05/2025 (79 dias vencida)\n🔗 Link: https://www.asaas.com/i/7xtchljmp5g42vcr\n\n📄 Fatura #59950\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/06/2025 (48 dias vencida)\n🔗 Link: https://www.asaas.com/i/ujpg1rxm4420cdbp\n\n📄 Fatura #59816\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/07/2025 (18 dias vencida)\n🔗 Link: https://www.asaas.com/i/845hw1wnor9l01a4\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59656\n💰 Valor: R$ 29,90\n📅 Vencimento: 12/08/2025 (em 13 dias)\n🔗 Link: https://www.asaas.com/i/iojbao7qqgemvczn\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:12', '2025-07-30 21:59:12'),
(169, 271, 'Olá Afonso! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #60981\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/01/2025 (188 dias vencida)\n🔗 Link: https://www.asaas.com/i/g6zi3clfjjsqllpu\n\n📄 Fatura #60849\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/02/2025 (157 dias vencida)\n🔗 Link: https://www.asaas.com/i/2nmlfczifinat6ui\n\n📄 Fatura #60605\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/03/2025 (129 dias vencida)\n🔗 Link: https://www.asaas.com/i/aomeqkmolqe6nnxm\n\n📄 Fatura #60353\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/04/2025 (98 dias vencida)\n🔗 Link: https://www.asaas.com/i/f6mggmj1fm0sa8ol\n\n📄 Fatura #60059\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/05/2025 (68 dias vencida)\n🔗 Link: https://www.asaas.com/i/2i0ru0v21yn4kd45\n\n📄 Fatura #59900\n💰 Valor: R$ 39,90\n📅 Vencimento: 23/06/2025 (37 dias vencida)\n🔗 Link: https://www.asaas.com/i/x966sv3bvbswcmqx\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:12', '2025-07-30 21:59:12'),
(170, 221, 'Olá Anderson! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #61272\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/12/2024 (214 dias vencida)\n🔗 Link: https://www.asaas.com/i/016pv7nqy9i1ewdc\n\n📄 Fatura #60954\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/01/2025 (183 dias vencida)\n🔗 Link: https://www.asaas.com/i/cm4b67vwc721s3s0\n\n📄 Fatura #60808\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/02/2025 (152 dias vencida)\n🔗 Link: https://www.asaas.com/i/1bcpkthsuoc5nbrh\n\n📄 Fatura #60590\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/03/2025 (124 dias vencida)\n🔗 Link: https://www.asaas.com/i/i6mqwlo7xk0kkdvj\n\n📄 Fatura #60265\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/04/2025 (93 dias vencida)\n🔗 Link: https://www.asaas.com/i/tebut47bp7xhwyco\n\n📄 Fatura #60013\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/05/2025 (63 dias vencida)\n🔗 Link: https://www.asaas.com/i/qzu6ap76zwk386ym\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:13', '2025-07-30 21:59:13'),
(171, 174, 'Olá Toninho! 👋\n\n⚠️ Você possui 5 fatura(s) vencida(s):\n\n📄 Fatura #60564\n💰 Valor: R$ 148,50\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/8ffzd8c0glmw29uu\n\n📄 Fatura #60341\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/or40i2sfkfyqa084\n\n📄 Fatura #60049\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/05/2025 (66 dias vencida)\n🔗 Link: https://www.asaas.com/i/jol6ntxxp6g4l4qn\n\n📄 Fatura #59888\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/06/2025 (35 dias vencida)\n🔗 Link: https://www.asaas.com/i/gpb89vdskwec7zxz\n\n📄 Fatura #59761\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/07/2025 (5 dias vencida)\n🔗 Link: https://www.asaas.com/i/junzraiemubm79yb\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59616\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/08/2025 (em 26 dias)\n🔗 Link: https://www.asaas.com/i/jzqaavsd8sjuppfs\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:13', '2025-07-30 21:59:13'),
(172, 268, 'Olá Mauro! 👋\n\n⚠️ Você possui 6 fatura(s) vencida(s):\n\n📄 Fatura #61540\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/11/2024 (247 dias vencida)\n🔗 Link: https://www.asaas.com/i/xxzswfweu4235w6a\n\n📄 Fatura #61284\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/12/2024 (217 dias vencida)\n🔗 Link: https://www.asaas.com/i/0w2oppcwg86b3qw6\n\n📄 Fatura #60972\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/01/2025 (186 dias vencida)\n🔗 Link: https://www.asaas.com/i/j8e5bopecv15ze5p\n\n📄 Fatura #60839\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/02/2025 (155 dias vencida)\n🔗 Link: https://www.asaas.com/i/fm9soclcun0gy20d\n\n📄 Fatura #60596\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/03/2025 (127 dias vencida)\n🔗 Link: https://www.asaas.com/i/itvqeej4bhkeiu6t\n\n📄 Fatura #60347\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/p9tqn4arzbrshxhj\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:14', '2025-07-30 21:59:14'),
(173, 258, 'Olá Marcos! 👋\n\n⚠️ Você possui 5 fatura(s) vencida(s):\n\n📄 Fatura #60767\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/03/2025 (140 dias vencida)\n🔗 Link: https://www.asaas.com/i/u8sdt9bmx5cwzgm0\n\n📄 Fatura #60508\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/04/2025 (109 dias vencida)\n🔗 Link: https://www.asaas.com/i/i815d1c9erjn39jc\n\n📄 Fatura #60157\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/05/2025 (79 dias vencida)\n🔗 Link: https://www.asaas.com/i/vfd24cmlnzk4x5w0\n\n📄 Fatura #59952\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/06/2025 (48 dias vencida)\n🔗 Link: https://www.asaas.com/i/xzh1h8n956yxj13w\n\n📄 Fatura #59818\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/07/2025 (18 dias vencida)\n🔗 Link: https://www.asaas.com/i/one6znxzim7fv7ws\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59658\n💰 Valor: R$ 39,90\n📅 Vencimento: 12/08/2025 (em 13 dias)\n🔗 Link: https://www.asaas.com/i/56bpw8bkbk4zxbz5\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:14', '2025-07-30 21:59:14'),
(174, 256, 'Olá Ana Paula! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #60498\n💰 Valor: R$ 29,90\n📅 Vencimento: 13/04/2025 (108 dias vencida)\n🔗 Link: https://www.asaas.com/i/wqq6nos6wozjpue4\n\n📄 Fatura #60145\n💰 Valor: R$ 29,90\n📅 Vencimento: 13/05/2025 (78 dias vencida)\n🔗 Link: https://www.asaas.com/i/ans2jmkpbs9kto4p\n\n📄 Fatura #59940\n💰 Valor: R$ 29,90\n📅 Vencimento: 13/06/2025 (47 dias vencida)\n🔗 Link: https://www.asaas.com/i/bj29om078xx4ru4d\n\n📄 Fatura #59804\n💰 Valor: R$ 29,90\n📅 Vencimento: 13/07/2025 (17 dias vencida)\n🔗 Link: https://www.asaas.com/i/a5tdpd0to0awefs3\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59644\n💰 Valor: R$ 29,90\n📅 Vencimento: 13/08/2025 (em 14 dias)\n🔗 Link: https://www.asaas.com/i/jfvm72stq13xtu5y\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:15', '2025-07-30 21:59:15'),
(175, 180, 'Olá Antônio! 👋\n\n⚠️ Você possui 5 fatura(s) vencida(s):\n\n📄 Fatura #60676\n💰 Valor: R$ 29,90\n📅 Vencimento: 05/03/2025 (147 dias vencida)\n🔗 Link: https://www.asaas.com/i/m5eolfcc35yvish4\n\n📄 Fatura #60671\n💰 Valor: R$ 188,33\n📅 Vencimento: 05/03/2025 (147 dias vencida)\n🔗 Link: https://www.asaas.com/i/8axb5r1n0f39oe3a\n\n📄 Fatura #60558\n💰 Valor: R$ 29,90\n📅 Vencimento: 05/04/2025 (116 dias vencida)\n🔗 Link: https://www.asaas.com/i/2ow1gpc3v8glhzwt\n\n📄 Fatura #60669\n💰 Valor: R$ 188,33\n📅 Vencimento: 05/04/2025 (116 dias vencida)\n🔗 Link: https://www.asaas.com/i/cri7rhpj9tcji08s\n\n📄 Fatura #60667\n💰 Valor: R$ 188,34\n📅 Vencimento: 05/05/2025 (86 dias vencida)\n🔗 Link: https://www.asaas.com/i/vlxf55k69wiaqon5\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:15', '2025-07-30 21:59:15'),
(176, 202, 'Olá Luiz Antônio! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #60351\n💰 Valor: R$ 29,90\n📅 Vencimento: 23/04/2025 (98 dias vencida)\n🔗 Link: https://www.asaas.com/i/v6c65h7z1x4nfg9d\n\n📄 Fatura #60057\n💰 Valor: R$ 29,90\n📅 Vencimento: 23/05/2025 (68 dias vencida)\n🔗 Link: https://www.asaas.com/i/t05apo7zo4rsxt7x\n\n📄 Fatura #59898\n💰 Valor: R$ 29,90\n📅 Vencimento: 23/06/2025 (37 dias vencida)\n🔗 Link: https://www.asaas.com/i/zgp1oe3s4d7r5qfb\n\n📄 Fatura #59769\n💰 Valor: R$ 29,90\n📅 Vencimento: 23/07/2025 (7 dias vencida)\n🔗 Link: https://www.asaas.com/i/0hrytbhjsqvy0j3i\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59624\n💰 Valor: R$ 29,90\n📅 Vencimento: 23/08/2025 (em 24 dias)\n🔗 Link: https://www.asaas.com/i/eorkbr64ykixs0x8\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:16', '2025-07-30 21:59:16'),
(177, 175, 'Olá Carlos Rodrigo! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #60572\n💰 Valor: R$ 199,00\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/5j9uzi3whkkw6lqi\n\n📄 Fatura #60343\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/gkk25wil6ial3mdj\n\n📄 Fatura #59890\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/06/2025 (35 dias vencida)\n🔗 Link: https://www.asaas.com/i/t0562wj36yuif67m\n\n📄 Fatura #59763\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/07/2025 (5 dias vencida)\n🔗 Link: https://www.asaas.com/i/n53i0umzqgdv17zc\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59618\n💰 Valor: R$ 29,90\n📅 Vencimento: 25/08/2025 (em 26 dias)\n🔗 Link: https://www.asaas.com/i/4vlj5ni5uo6cp89z\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:16', '2025-07-30 21:59:16'),
(178, 251, 'Olá Vicente! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #60355\n💰 Valor: R$ 29,90\n📅 Vencimento: 21/04/2025 (100 dias vencida)\n🔗 Link: https://www.asaas.com/i/x1mwwmh7zyi521ft\n\n📄 Fatura #60061\n💰 Valor: R$ 29,90\n📅 Vencimento: 21/05/2025 (70 dias vencida)\n🔗 Link: https://www.asaas.com/i/zivcrt5dhmzitxl7\n\n📄 Fatura #59914\n💰 Valor: R$ 29,90\n📅 Vencimento: 21/06/2025 (39 dias vencida)\n🔗 Link: https://www.asaas.com/i/aeoltqfz02np7q75\n\n📄 Fatura #59773\n💰 Valor: R$ 29,90\n📅 Vencimento: 21/07/2025 (9 dias vencida)\n🔗 Link: https://www.asaas.com/i/9nlkr6j8mfjrz4ix\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59628\n💰 Valor: R$ 29,90\n📅 Vencimento: 21/08/2025 (em 22 dias)\n🔗 Link: https://www.asaas.com/i/0jeqm5u5clq7uxa3\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:17', '2025-07-30 21:59:17'),
(179, 217, 'Olá Neto! 👋\n\n⚠️ Você possui 5 fatura(s) vencida(s):\n\n📄 Fatura #61057\n💰 Valor: R$ 29,90\n📅 Vencimento: 15/01/2025 (196 dias vencida)\n🔗 Link: https://www.asaas.com/i/nzwir1vek7uqr3hd\n\n📄 Fatura #60902\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/02/2025 (170 dias vencida)\n🔗 Link: https://www.asaas.com/i/66dmij258icx312w\n\n📄 Fatura #60737\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/f2px92t5dim5ezro\n\n📄 Fatura #60524\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/04/2025 (111 dias vencida)\n🔗 Link: https://www.asaas.com/i/7jor3n8a50rhs0ul\n\n📄 Fatura #60167\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/05/2025 (81 dias vencida)\n🔗 Link: https://www.asaas.com/i/fxy8xjkw3h6vsf0e\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:18', '2025-07-30 21:59:18'),
(180, 236, 'Olá Detetive Aguiar! 👋\n\n⚠️ Você possui 3 fatura(s) vencida(s):\n\n📄 Fatura #60592\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/03/2025 (124 dias vencida)\n🔗 Link: https://www.asaas.com/i/g0smfthxiro8mu53\n\n📄 Fatura #59880\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/06/2025 (32 dias vencida)\n🔗 Link: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n📄 Fatura #59759\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/07/2025 (2 dias vencida)\n🔗 Link: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59614\n💰 Valor: R$ 29,90\n📅 Vencimento: 28/08/2025 (em 29 dias)\n🔗 Link: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:18', '2025-07-30 21:59:18'),
(181, 195, 'Olá João Paulo! 👋\n\n⚠️ Você possui 4 fatura(s) vencida(s):\n\n📄 Fatura #61011\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/01/2025 (201 dias vencida)\n🔗 Link: https://www.asaas.com/i/zet6hmpr9grwoftq\n\n📄 Fatura #60898\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/02/2025 (170 dias vencida)\n🔗 Link: https://www.asaas.com/i/n1nyoc7rfl063ay4\n\n📄 Fatura #60728\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/nqdxjt9fza5nihm8\n\n📄 Fatura #60518\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/04/2025 (111 dias vencida)\n🔗 Link: https://www.asaas.com/i/w8h2mp19cxzvsizl\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:19', '2025-07-30 21:59:19'),
(182, 145, 'Olá Nicelio! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #59720\n💰 Valor: R$ 232,33\n📅 Vencimento: 23/07/2025 (7 dias vencida)\n🔗 Link: https://www.asaas.com/i/uatslddjrkpj9saj\n\n📋 Você possui 2 fatura(s) a vencer:\n\n📄 Fatura #59646\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/08/2025 (em 11 dias)\n🔗 Link: https://www.asaas.com/i/ka6ro7jy0n4z70fu\n\n📄 Fatura #59716\n💰 Valor: R$ 232,34\n📅 Vencimento: 23/08/2025 (em 24 dias)\n🔗 Link: https://www.asaas.com/i/mnphrheig3ek3s16\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:19', '2025-07-30 21:59:19'),
(183, 215, 'Olá Gianna! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #61417\n💰 Valor: R$ 232,80\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/59gxivvg6ic2rsid\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59654\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/08/2025 (em 11 dias)\n🔗 Link: https://www.asaas.com/i/dxuvie0sesjjdleh\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:20', '2025-07-30 21:59:20'),
(184, 189, 'Olá Marcus! 👋\n\n⚠️ Você possui 2 fatura(s) vencida(s):\n\n📄 Fatura #60723\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/03/2025 (142 dias vencida)\n🔗 Link: https://www.asaas.com/i/1rfzxkaal63atyf4\n\n📄 Fatura #60514\n💰 Valor: R$ 29,90\n📅 Vencimento: 10/04/2025 (111 dias vencida)\n🔗 Link: https://www.asaas.com/i/n2wa1lck8s82wj0d\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:20', '2025-07-30 21:59:20'),
(185, 273, 'Olá Welton! 👋\n\n⚠️ Você possui 2 fatura(s) vencida(s):\n\n📄 Fatura #61935\n💰 Valor: R$ 165,66\n📅 Vencimento: 19/08/2024 (345 dias vencida)\n🔗 Link: https://www.asaas.com/i/anlb2s72bwu3nfs9\n\n📄 Fatura #61932\n💰 Valor: R$ 165,68\n📅 Vencimento: 19/09/2024 (314 dias vencida)\n🔗 Link: https://www.asaas.com/i/m3zit83q4vj70hnz\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:21', '2025-07-30 21:59:21'),
(186, 286, 'Olá Wilmar! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #59936\n💰 Valor: R$ 49,90\n📅 Vencimento: 15/06/2025 (45 dias vencida)\n🔗 Link: https://www.asaas.com/i/5nk95op482uhr5q8\n\n📋 Você possui 1 fatura(s) a vencer:\n\n📄 Fatura #59642\n💰 Valor: R$ 49,90\n📅 Vencimento: 15/08/2025 (em 16 dias)\n🔗 Link: https://www.asaas.com/i/0um0ysbtjwvv0lr6\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:21', '2025-07-30 21:59:21'),
(187, 252, 'Olá Alex Sandro! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #60357\n💰 Valor: R$ 29,90\n📅 Vencimento: 20/04/2025 (101 dias vencida)\n🔗 Link: https://www.asaas.com/i/xgktvw5f2vaovxlr\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:22', '2025-07-30 21:59:22'),
(188, 187, 'Olá Alisson! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #60213\n💰 Valor: R$ 238,80\n📅 Vencimento: 25/04/2025 (96 dias vencida)\n🔗 Link: https://www.asaas.com/i/3u6xvbgznbanw1t6\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:22', '2025-07-30 21:59:22'),
(189, 274, 'Olá Eduardo! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #61950\n💰 Valor: R$ 347,00\n📅 Vencimento: 06/08/2024 (358 dias vencida)\n🔗 Link: https://www.asaas.com/i/3p5pmnce6om5u6sw\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:23', '2025-07-30 21:59:23'),
(190, 280, 'Olá Mario! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #61992\n💰 Valor: R$ 165,67\n📅 Vencimento: 29/08/2024 (335 dias vencida)\n🔗 Link: https://www.asaas.com/i/stiz5kb897xiiiww\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:24', '2025-07-30 21:59:24'),
(191, 4297, 'Olá José Roberto! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #61701\n💰 Valor: R$ 497,00\n📅 Vencimento: 06/09/2024 (327 dias vencida)\n🔗 Link: https://www.asaas.com/i/v488nqp6wv7o7ss9\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:24', '2025-07-30 21:59:24'),
(192, 281, 'Olá Michael! 👋\n\n⚠️ Você possui 1 fatura(s) vencida(s):\n\n📄 Fatura #61233\n💰 Valor: R$ 299,40\n📅 Vencimento: 15/01/2025 (196 dias vencida)\n🔗 Link: https://www.asaas.com/i/e5bs0pjkomggepb0\n\n💳 Para facilitar o pagamento, utilize os links acima.\n\n📞 Em caso de dúvidas, entre em contato conosco.\n\nObrigado! 🙏', 'cobranca_completa', 'alta', '2025-07-31 10:00:00', 'agendada', NULL, '2025-07-30 21:59:25', '2025-07-30 21:59:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens_comunicacao`
--

CREATE TABLE `mensagens_comunicacao` (
  `id` int(11) NOT NULL,
  `canal_id` int(11) NOT NULL,
  `canal_nome` varchar(100) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `cobranca_id` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `anexo` varchar(255) DEFAULT NULL,
  `tipo` varchar(32) NOT NULL,
  `data_hora` datetime NOT NULL,
  `direcao` varchar(16) NOT NULL,
  `status` varchar(32) DEFAULT NULL,
  `status_conversa` enum('aberta','fechada') DEFAULT 'aberta',
  `numero_whatsapp` varchar(20) DEFAULT NULL,
  `whatsapp_message_id` varchar(255) DEFAULT NULL,
  `motivo_erro` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `mensagens_comunicacao`
--

INSERT INTO `mensagens_comunicacao` (`id`, `canal_id`, `canal_nome`, `cliente_id`, `cobranca_id`, `mensagem`, `anexo`, `tipo`, `data_hora`, `direcao`, `status`, `status_conversa`, `numero_whatsapp`, `whatsapp_message_id`, `motivo_erro`) VALUES
(40, 36, 'Financeiro', 257, NULL, 'Olá Gutemberg! Sua fatura com vencimento em 12/07/2025 está aguardando pagamento. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/845hw1wnor9l01a4\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-11 18:20:24', 'enviado', 'enviado', 'aberta', '73981552730', NULL, NULL),
(43, 36, 'Financeiro', 2862, NULL, 'Mensagem de teste recebida via webhook (PHP)', NULL, 'texto', '2025-07-11 21:41:55', 'recebido', 'lido', 'aberta', '5599999999999', NULL, NULL),
(45, 36, 'Financeiro', 286, NULL, 'Olá Wilmar! Sua fatura com vencimento em 15/07/2025 está aguardando pagamento. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/nhneruj36ed4stkf\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-14 14:21:12', 'enviado', 'enviado', 'aberta', '4898581874', NULL, NULL),
(46, 36, 'Financeiro', 198, NULL, 'Cobrança enviada via WhatsApp em 14/07/2025 12:39', NULL, 'texto', '2025-07-14 15:40:16', 'enviado', 'enviado', 'aberta', '554797009768', NULL, NULL),
(58, 36, 'Financeiro', 196, 20745, 'Tentativa de envio de cobrança via WhatsApp em 14/07/2025 14:27 - ERRO', NULL, 'texto', '2025-07-14 17:28:15', 'enviado', 'erro', 'aberta', '21976209602', NULL, NULL),
(59, 36, 'Financeiro', 196, 20745, 'Cobrança enviada via WhatsApp em 14/07/2025 15:32', NULL, 'texto', '2025-07-14 18:33:39', 'enviado', 'enviado', 'aberta', '21976209602', NULL, NULL),
(60, 36, 'Financeiro', 196, NULL, 'Boa tarde, você quer que retire seu site do ar, só para confirmar. Neste caso, baixamos da nossa hospedagem e lhe enviamos o arquivo.', NULL, 'texto', '2025-07-14 16:01:00', 'enviado', 'enviado', 'aberta', '21976209602', NULL, NULL),
(61, 36, 'Financeiro', 222, 20747, 'Cobrança enviada via WhatsApp em 14/07/2025 16:10', NULL, 'texto', '2025-07-14 19:11:27', 'enviado', 'enviado', 'aberta', '62985489901', NULL, NULL),
(62, 36, 'Financeiro', 215, 20755, 'Tentativa de envio de cobrança via WhatsApp em 14/07/2025 16:22 - ERRO', NULL, 'texto', '2025-07-14 19:23:46', 'enviado', 'erro', 'aberta', '41988290646', NULL, NULL),
(63, 36, 'Financeiro', 215, 20755, 'Cobrança enviada via WhatsApp em 14/07/2025 16:32', NULL, 'texto', '2025-07-14 19:33:42', 'enviado', 'enviado', 'aberta', '41988290646', NULL, NULL),
(64, 36, 'Financeiro', 163, 20759, 'Cobrança enviada via WhatsApp em 14/07/2025 16:33', NULL, 'texto', '2025-07-14 19:34:25', 'enviado', 'enviado', 'aberta', '85991938872', NULL, NULL),
(65, 36, 'Financeiro', 275, 21273, 'Cobrança enviada via WhatsApp em 14/07/2025 16:33', NULL, 'texto', '2025-07-14 19:34:51', 'enviado', 'enviado', 'aberta', '98987182714', NULL, NULL),
(66, 36, 'Financeiro', 164, 20763, 'Cobrança enviada via WhatsApp em 14/07/2025 16:38', NULL, 'texto', '2025-07-14 16:38:28', 'enviado', 'enviado', 'aberta', '31988605047', NULL, NULL),
(67, 36, 'Financeiro', 283, 21024, 'Cobrança enviada via WhatsApp em 14/07/2025 16:43', NULL, 'texto', '2025-07-14 16:46:50', 'enviado', 'pendente', 'aberta', '61920007184', NULL, NULL),
(68, 36, 'Financeiro', 283, 21024, 'Cobrança enviada via WhatsApp em 14/07/2025 16:57', NULL, 'texto', '2025-07-14 16:57:12', 'enviado', 'enviado', 'aberta', '61920007184', NULL, NULL),
(69, 36, 'Financeiro', 265, 20771, 'Cobrança enviada via WhatsApp em 14/07/2025 16:59', NULL, 'texto', '2025-07-14 16:59:57', 'enviado', 'enviado', 'aberta', '49991187494', NULL, NULL),
(70, 36, 'Financeiro', 183, 20781, 'Cobrança enviada via WhatsApp em 14/07/2025 17:04', NULL, 'texto', '2025-07-14 17:04:36', 'enviado', 'enviado', 'aberta', '81997076042', NULL, NULL),
(71, 36, 'Financeiro', 188, 20783, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:06', 'enviado', 'enviado', 'aberta', '11980441758', NULL, NULL),
(72, 36, 'Financeiro', 182, 20775, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:26', 'enviado', 'enviado', 'aberta', '11974958004', NULL, NULL),
(73, 36, 'Financeiro', 266, 20773, 'Cobrança enviada via WhatsApp em 14/07/2025 17:05', NULL, 'texto', '2025-07-14 17:05:37', 'enviado', 'enviado', 'aberta', '92981543898', NULL, NULL),
(74, 36, 'Financeiro', 206, 20787, 'Cobrança enviada via WhatsApp em 14/07/2025 17:06', NULL, 'texto', '2025-07-14 17:06:46', 'enviado', 'enviado', 'aberta', '65981047654', NULL, NULL),
(75, 36, 'Financeiro', 209, 20789, 'Cobrança enviada via WhatsApp em 14/07/2025 17:07', NULL, 'texto', '2025-07-14 17:07:42', 'enviado', 'enviado', 'aberta', '14997501745', NULL, NULL),
(76, 36, 'Financeiro', 199, 20793, 'Cobrança enviada via WhatsApp em 14/07/2025 17:08', NULL, 'texto', '2025-07-14 17:09:15', 'enviado', 'pendente', 'aberta', '64996431037', NULL, NULL),
(77, 36, 'Financeiro', 199, 20793, 'Cobrança enviada via WhatsApp em 14/07/2025 17:11', NULL, 'texto', '2025-07-14 17:11:13', 'enviado', 'enviado', 'aberta', '64996431037', NULL, NULL),
(78, 36, 'Financeiro', 158, 21050, 'Cobrança enviada via WhatsApp em 14/07/2025 17:11', NULL, 'texto', '2025-07-14 17:13:38', 'enviado', 'pendente', 'aberta', '11987177060', NULL, NULL),
(79, 36, 'Financeiro', 158, 21050, 'Cobrança enviada via WhatsApp em 14/07/2025 17:16', NULL, 'texto', '2025-07-14 17:16:27', 'enviado', 'enviado', 'aberta', '11987177060', NULL, NULL),
(80, 36, 'Financeiro', 234, 20795, 'Cobrança enviada via WhatsApp em 14/07/2025 17:17', NULL, 'texto', '2025-07-14 17:17:17', 'enviado', 'enviado', 'aberta', '92991953335', NULL, NULL),
(81, 36, 'Financeiro', 227, 20799, 'Cobrança enviada via WhatsApp em 14/07/2025 17:17', NULL, 'texto', '2025-07-14 17:17:39', 'enviado', 'enviado', 'aberta', '11934707141', NULL, NULL),
(82, 36, 'Financeiro', 232, 20801, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:14', 'enviado', 'enviado', 'aberta', '62985793436', NULL, NULL),
(83, 36, 'Financeiro', 240, 20805, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:35', 'enviado', 'enviado', 'aberta', '81998790053', NULL, NULL),
(84, 36, 'Financeiro', 211, 22085, 'Cobrança enviada via WhatsApp em 14/07/2025 17:18', NULL, 'texto', '2025-07-14 17:18:56', 'enviado', 'enviado', 'aberta', '37998765431', NULL, NULL),
(85, 36, 'Financeiro', 225, 20807, 'Cobrança enviada via WhatsApp em 14/07/2025 17:19', NULL, 'texto', '2025-07-14 17:19:17', 'enviado', 'enviado', 'aberta', '11989541000', NULL, NULL),
(86, 36, 'Financeiro', 263, 20809, 'Cobrança enviada via WhatsApp em 14/07/2025 17:19', NULL, 'texto', '2025-07-14 17:19:48', 'enviado', 'enviado', 'aberta', '87999884234', NULL, NULL),
(87, 36, 'Financeiro', 240, 27137, 'Cobrança enviada via WhatsApp em 15/07/2025 17:08', NULL, 'texto', '2025-07-15 17:08:54', 'enviado', 'enviado', 'aberta', '81998790053', NULL, NULL),
(88, 36, 'Financeiro', 225, 27138, 'Cobrança enviada via WhatsApp em 15/07/2025 17:09', NULL, 'texto', '2025-07-15 17:09:09', 'enviado', 'enviado', 'aberta', '11989541000', NULL, NULL),
(89, 36, 'Financeiro', 148, 27146, 'Cobrança enviada via WhatsApp em 15/07/2025 17:09', NULL, 'texto', '2025-07-15 17:09:18', 'enviado', 'enviado', 'aberta', '93991439400', NULL, NULL),
(91, 36, 'Financeiro', 228, 27416, 'Tentativa de envio de cobrança via WhatsApp em 15/07/2025 17:09 - ERRO', NULL, 'texto', '2025-07-15 17:09:56', 'enviado', 'erro', 'aberta', '61982428290', NULL, NULL),
(92, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:12', NULL, 'texto', '2025-07-15 17:15:40', 'enviado', 'pendente', 'aberta', '61982428290', NULL, NULL),
(93, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:15', NULL, 'texto', '2025-07-15 17:16:33', 'enviado', 'pendente', 'aberta', '61982428290', NULL, NULL),
(94, 36, 'Financeiro', 228, 27416, 'Cobrança enviada via WhatsApp em 15/07/2025 17:16', NULL, 'texto', '2025-07-15 17:16:41', 'enviado', 'enviado', 'aberta', '61982428290', NULL, NULL),
(95, 36, 'Financeiro', 283, 27247, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:19', 'enviado', 'enviado', 'aberta', '61920007184', NULL, NULL),
(96, 36, 'Financeiro', 158, 27260, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:42', 'enviado', 'enviado', 'aberta', '11987177060', NULL, NULL),
(97, 36, 'Financeiro', 211, 27781, 'Cobrança enviada via WhatsApp em 15/07/2025 17:17', NULL, 'texto', '2025-07-15 17:17:54', 'enviado', 'enviado', 'aberta', '37998765431', NULL, NULL),
(99, 36, 'Financeiro', 219, 27332, 'Cobrança enviada via WhatsApp em 15/07/2025 17:18', NULL, 'texto', '2025-07-15 17:18:16', 'enviado', 'enviado', 'aberta', '51998679078', NULL, NULL),
(100, 36, 'Financeiro', 222, 27110, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:06', 'enviado', 'enviado', 'aberta', '62985489901', NULL, NULL),
(101, 36, 'Financeiro', 286, 27112, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:20', 'enviado', 'enviado', 'aberta', '4898581874', NULL, NULL),
(102, 36, 'Financeiro', 215, 27114, 'Cobrança enviada via WhatsApp em 15/07/2025 17:19', NULL, 'texto', '2025-07-15 17:19:31', 'enviado', 'enviado', 'aberta', '41988290646', NULL, NULL),
(103, 36, 'Financeiro', 163, 27116, 'Cobrança enviada via WhatsApp em 15/07/2025 17:20', NULL, 'texto', '2025-07-15 17:20:05', 'enviado', 'enviado', 'aberta', '85991938872', NULL, NULL),
(104, 36, 'Financeiro', 275, 27373, 'Status manual inserido', NULL, 'manual', '2025-07-15 17:57:56', 'enviado', 'enviado', 'aberta', '98987182714', NULL, NULL),
(105, 36, 'Financeiro', 265, 27122, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:08:31', 'enviado', 'enviado', 'aberta', '49991187494', NULL, NULL),
(106, 36, 'Financeiro', 266, 27123, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:01', 'enviado', 'enviado', 'aberta', '92981543898', NULL, NULL),
(107, 36, 'Financeiro', 182, 27124, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:15', 'enviado', 'enviado', 'aberta', '11974958004', NULL, NULL),
(108, 36, 'Financeiro', 183, 27125, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:21', 'enviado', 'enviado', 'aberta', '81997076042', NULL, NULL),
(109, 36, 'Financeiro', 188, 27126, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:12:32', 'enviado', 'enviado', 'aberta', '11980441758', NULL, NULL),
(110, 36, 'Financeiro', 206, 27128, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:14:02', 'enviado', 'enviado', 'aberta', '65981047654', NULL, NULL),
(111, 36, 'Financeiro', 209, 27129, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:15:13', 'enviado', 'enviado', 'aberta', '14997501745', NULL, NULL),
(112, 36, 'Financeiro', 232, 27135, 'Status manual inserido', NULL, 'manual', '2025-07-15 18:31:28', 'enviado', 'enviado', 'aberta', '62985793436', NULL, NULL),
(113, 36, 'Financeiro', 227, 27134, 'Cobrança enviada via WhatsApp em 15/07/2025 18:31', 'true_5511934707141@c.us_3EB04B00CFDC5D185ED158', 'texto', '2025-07-15 18:31:43', 'enviado', 'enviado', 'aberta', '11934707141', NULL, NULL),
(114, 36, 'Financeiro', 234, 27132, 'Cobrança enviada via WhatsApp em 15/07/2025 18:31', 'true_559291953335@c.us_3EB0FD52F6B4CA2B7CF084', 'texto', '2025-07-15 18:31:59', 'enviado', 'enviado', 'aberta', '92991953335', NULL, NULL),
(115, 36, 'Financeiro', 199, 27131, 'Cobrança enviada via WhatsApp em 15/07/2025 18:32', 'true_556496431037@c.us_3EB00560071410ACFAF138', 'texto', '2025-07-15 18:32:21', 'enviado', 'enviado', 'aberta', '64996431037', NULL, NULL),
(138, 36, 'Financeiro', 206, NULL, '2025-07-16_081913.pdf', NULL, 'texto', '2025-07-16 09:19:31', 'recebido', 'lido', 'aberta', '65981047654', NULL, NULL),
(139, 36, 'Financeiro', 206, NULL, '', NULL, 'texto', '2025-07-16 09:21:26', 'recebido', 'lido', 'aberta', '65981047654', NULL, NULL),
(140, 36, 'Financeiro', 0, NULL, 'Cobrança enviada via WhatsApp em 16/07/2025 13:53', 'true_554796164699@c.us_3EB0C005CFE22C9E51FC2F', 'texto', '2025-07-16 13:53:52', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(144, 36, 'Financeiro', 0, NULL, 'Cobrança enviada via WhatsApp em 16/07/2025 13:59', 'true_554796164699@c.us_3EB03FA523AFB865C5516E', 'texto', '2025-07-16 13:59:54', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(176, 36, 'Financeiro', 4296, NULL, 'Teste de envio 13:55 18/07', '', 'texto', '2025-07-18 13:56:13', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(177, 36, 'Financeiro', 4296, NULL, 'Teste final - formatação corrigida - 14:11:38', NULL, 'texto', '2025-07-18 14:11:39', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(178, 36, 'Financeiro', 4296, NULL, 'teste de envio 14:26 17/07/25', '', 'texto', '2025-07-18 14:40:14', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(179, 36, 'Financeiro', 4296, NULL, 'teste de envio 14:48 18/07/25', '', 'texto', '2025-07-18 14:49:31', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(180, 36, 'Financeiro', 4296, NULL, 'tesde de envio 14:59 18/07/25', '', 'texto', '2025-07-18 15:00:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(182, 36, 'Financeiro', 4296, NULL, 'teste 14:41', '', 'texto', '2025-07-21 14:41:26', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(183, 36, 'Financeiro', 4296, NULL, 'teste 21/07 14:51', '', 'texto', '2025-07-21 14:51:51', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(184, 36, 'Financeiro', 4296, NULL, 'mensagem de teste 21/07 14:52', '', 'texto', '2025-07-21 14:52:25', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(185, 36, 'Financeiro', 4296, NULL, 'nova mensagem de teste', '', 'texto', '2025-07-21 14:52:42', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(186, 36, 'Financeiro', 4296, NULL, 'nova mensagem de teste atualizada agora', '', 'texto', '2025-07-21 14:53:11', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(187, 36, 'Financeiro', 4296, NULL, 'olá', '', 'texto', '2025-07-21 15:51:42', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(188, 36, 'Financeiro', 4296, NULL, 'teste envio dia 22/07/2025 08:23', '', 'texto', '2025-07-22 08:23:12', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(190, 36, 'Financeiro', 4296, NULL, 'Mensagem enviada para Pixel12Digital em 22/07/2025 às 08:31', '', 'texto', '2025-07-22 08:31:48', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(191, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 09:44:27', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(192, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:31:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(193, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:43:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(194, 36, 'Financeiro', 4296, NULL, 'teste debug', NULL, 'texto', '2025-07-22 10:49:31', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(195, 36, 'Financeiro', 4296, NULL, 'Mensagem teste webhook manual', NULL, 'texto', '2025-07-22 13:43:23', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(196, 36, 'Financeiro', 4296, NULL, 'MENSAGEM TESTE 15:46', '', 'texto', '2025-07-22 15:46:16', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(197, 36, 'Financeiro', 4425, NULL, 'TESTE DIRETO WEBHOOK 16:15:55', NULL, 'text', '2025-07-22 16:15:56', 'recebido', 'lido', 'aberta', '47996164699@c.us', NULL, NULL),
(198, 36, 'Financeiro', 4425, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:15:56', 'enviado', 'enviado', 'aberta', '47996164699@c.us', NULL, NULL),
(199, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 16:50', NULL, 'chat', '2025-07-22 16:50:35', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(200, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:50:35', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(201, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 16:50', NULL, 'chat', '2025-07-22 16:51:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(202, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:51:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(203, 36, 'Financeiro', 4296, NULL, 'obrigado', NULL, 'chat', '2025-07-22 16:52:20', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(205, 36, 'Financeiro', 4296, NULL, 'novo teste', NULL, 'chat', '2025-07-22 16:53:33', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(206, 36, 'Financeiro', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-22 16:53:33', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(207, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:04', NULL, 'chat', '2025-07-22 17:04:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(208, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:14', NULL, 'chat', '2025-07-22 17:14:10', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(209, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 17:27', NULL, 'chat', '2025-07-22 17:27:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(210, 36, 'Financeiro', 4296, NULL, 'NOVA MENSAGEM TESTE ENVIO 18:20', NULL, 'chat', '2025-07-22 18:20:15', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(211, 36, 'Financeiro', 4296, NULL, 'Recebido 18:23', '', 'texto', '2025-07-22 18:23:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(212, 36, 'Financeiro', 145, 49665, 'Olá Nicelio! Lembrete: sua fatura vence hoje. Para acessar o boleto ou pagar via Pix, clique no link: https://www.asaas.com/i/uatslddjrkpj9saj\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-23 18:26:52', 'enviado', 'enviado', 'aberta', '11965221349', NULL, NULL),
(213, 36, 'Financeiro', 274, 58758, 'Olá Eduardo! Sua fatura com vencimento em 06/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/3p5pmnce6om5u6sw\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-25 12:05:06', 'enviado', 'enviado', 'aberta', '11964583101', NULL, NULL),
(214, 36, 'Financeiro', 4296, NULL, 'Olá, preciso de informações sobre minha fatura', NULL, 'text', '2025-07-28 15:44:02', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(215, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:44:02', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(216, 36, 'Financeiro', 4296, NULL, 'Boa tarde, gostaria de saber sobre meu plano', NULL, 'text', '2025-07-28 15:44:05', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(218, 36, 'Financeiro', 2862, NULL, 'Olá, vocês fazem sites?', NULL, 'text', '2025-07-28 15:44:07', 'recebido', 'lido', 'aberta', '5599999999999', NULL, NULL),
(219, 36, 'Financeiro', 2862, NULL, 'Olá Cliente WhatsApp (5599999999999)! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:44:07', 'enviado', 'enviado', 'aberta', '5599999999999', NULL, NULL),
(220, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-28 15:52:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(221, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 15:52:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(222, 36, 'Financeiro', 4296, NULL, 'Ola', NULL, 'chat', '2025-07-28 15:53:14', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(224, 36, 'Financeiro', 4296, NULL, 'Oi', NULL, 'chat', '2025-07-28 15:53:18', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(226, 36, 'Financeiro', 4296, NULL, 'Olá, preciso de informações sobre minha fatura', NULL, 'text', '2025-07-28 16:02:16', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(227, 36, 'Financeiro', 4296, NULL, 'Boa tarde, gostaria de saber sobre meu plano', NULL, 'text', '2025-07-28 16:02:18', 'recebido', 'lido', 'aberta', '4796164699', NULL, NULL),
(228, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:02:18', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(229, 36, 'Financeiro', 2862, NULL, 'Olá, vocês fazem sites?', NULL, 'text', '2025-07-28 16:02:20', 'recebido', 'lido', 'aberta', '554799999999', NULL, NULL),
(230, 36, 'Financeiro', 2862, NULL, 'Olá Cliente WhatsApp (5599999999999)! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:02:20', 'enviado', 'enviado', 'aberta', '554799999999', NULL, NULL),
(231, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-28 16:05:14', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(232, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:05:14', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(233, 36, 'Financeiro', 4296, NULL, 'Não recebi minha fatura', NULL, 'chat', '2025-07-28 16:05:33', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(234, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:05:33', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(235, 36, 'Financeiro', 4296, NULL, 'oie', NULL, 'chat', '2025-07-28 16:06:30', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(236, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-28 16:06:30', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(237, 36, 'Financeiro', 4296, NULL, 'Teste de mensagem às 17:10:40', NULL, 'text', '2025-07-28 17:10:40', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(238, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 20:54:18', NULL, 'text', '2025-07-28 17:54:18', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(239, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:04:54', NULL, 'text', '2025-07-28 18:04:54', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(240, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:06:23', NULL, 'text', '2025-07-28 18:06:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(241, 36, 'Financeiro', 4296, NULL, 'Teste direto de inserção às 18:06:50', NULL, 'texto', '2025-07-28 18:06:50', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(242, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:07:12', NULL, 'text', '2025-07-28 18:07:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(243, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:08:10', NULL, 'text', '2025-07-28 18:08:11', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(244, 36, 'Financeiro', 4296, NULL, 'Teste de webhook após correção às 18:08:43', NULL, 'text', '2025-07-28 18:08:43', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(245, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:11:53', NULL, 'text', '2025-07-28 18:11:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(246, 36, 'Financeiro', 4296, NULL, 'Teste de webhook às 18:21:52', NULL, 'text', '2025-07-28 18:21:52', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(247, 36, 'Financeiro', 4296, NULL, 'teste às 19:11', NULL, 'texto', '2025-07-28 19:11:00', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(248, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:29:56', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(249, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:30:38', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(250, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:31:44', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(251, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 11:34:19', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(252, 36, 'Financeiro', 264, NULL, 'Bom dia Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 12:23:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(253, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 13:10:36', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(254, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 13:24:08', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(255, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 14:48:06', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(256, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 14:51:30', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(257, 36, 'Financeiro', 264, NULL, 'Boa tarde Klysman, tudo bem? Desculpe, nosso servidor estava com problemas!', '', 'texto', '2025-07-29 15:08:05', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(258, 36, 'Financeiro', 273, 61935, 'Olá Welton! Sua fatura com vencimento em 19/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/anlb2s72bwu3nfs9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:11:46', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(259, 37, 'Comercial - Pixel', 1, 1, 'Status manual inserido', NULL, 'manual', '2025-07-29 17:10:44', 'enviado', 'pendente', 'aberta', NULL, NULL, NULL),
(260, 36, 'Financeiro', 273, 61935, 'Olá Welton! Sua fatura com vencimento em 19/08/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/anlb2s72bwu3nfs9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:12:48', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(262, 36, 'Financeiro', 145, NULL, 'Olá Nicelio! \n\n⚠️ Você possui faturas em aberto:\n\n• Fatura #59720 - R$ 232,33 - Venceu em 23/07/2025 (6 dias vencida)\n\n💰 Valor total em aberto: R$ 232,33\n🔗 Link para pagamento: https://www.asaas.com/i/uatslddjrkpj9saj\n\nPara consultar todas as suas faturas, responda \"faturas\" ou \"consulta\".\n\nAtenciosamente,\nEquipe Financeira Pixel12 Digital', NULL, 'cobranca_vencida', '2025-07-29 20:34:05', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(263, 36, 'Financeiro', 280, 61992, 'Olá Mario! Sua fatura com vencimento em 29/08/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/stiz5kb897xiiiww\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:57:20', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(264, 36, 'Financeiro', 4297, 61701, 'Olá José Roberto! Sua fatura com vencimento em 06/09/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/v488nqp6wv7o7ss9\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 17:59:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(265, 36, 'Financeiro', 268, 61540, 'Olá Mauro! Sua fatura com vencimento em 25/11/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/xxzswfweu4235w6a\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:04:40', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(266, 36, 'Financeiro', 221, 61272, 'Olá Anderson! Sua fatura com vencimento em 28/12/2024 está em aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/016pv7nqy9i1ewdc\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:04:31', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(267, 36, 'Financeiro', 221, 61272, 'Olá Anderson! Sua fatura com vencimento em 28/12/2024 está aberto. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/016pv7nqy9i1ewdc\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:05:26', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(268, 36, 'Financeiro', 268, 61540, 'Olá Mauro! Sua fatura com vencimento em 25/11/2024 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/xxzswfweu4235w6a\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:06:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(269, 36, 'Financeiro', 195, 61011, 'Olá João Paulo! Sua fatura com vencimento em 10/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/zet6hmpr9grwoftq\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:08:45', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(270, 36, 'Financeiro', 281, 61233, 'Olá Michael! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/e5bs0pjkomggepb0\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:14:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(271, 36, 'Financeiro', 217, 61057, 'Olá Neto! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/nzwir1vek7uqr3hd\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:18:01', 'enviado', 'erro', 'aberta', NULL, NULL, NULL),
(272, 36, 'Financeiro', 217, 61057, 'Olá Neto! Sua fatura com vencimento em 15/01/2025 está vencida. Por favor, regularize o pagamento o quanto antes. Link para pagamento: https://www.asaas.com/i/nzwir1vek7uqr3hd\n\nEsta é uma mensagem automática, por favor desconsidere se já realizou o pagamento.', NULL, 'texto', '2025-07-29 18:18:34', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(296, 36, 'Financeiro', 264, NULL, 'Bom dia, tudo bem? Isto mesmo, no registro.br você pode optar pela renovação anual.', '', 'texto', '2025-07-30 09:26:31', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(297, 36, 'Financeiro', 264, NULL, 'Teste de mensagem via sistema', '', 'texto', '2025-07-30 10:25:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(298, 36, 'Financeiro', 4296, NULL, 'teste de envio 10:28', '', 'texto', '2025-07-30 10:28:12', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(301, 36, 'Financeiro', 11397, NULL, 'Olá, gostaria de informações', NULL, 'text', '2025-07-30 10:56:37', 'recebido', 'lido', 'aberta', '554799999999', NULL, NULL),
(302, 36, 'Financeiro', 11397, NULL, 'Olá Cliente Teste Corrigido! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 10:56:37', 'enviado', 'enviado', 'aberta', '554799999999', NULL, NULL),
(303, 36, 'Financeiro', 4296, NULL, 'Teste de formato de número', NULL, 'text', '2025-07-30 10:56:40', 'recebido', 'lido', 'aberta', '4796164699', NULL, NULL),
(304, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 10:56:40', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(310, 36, 'Financeiro', 236, NULL, 'Consulta', NULL, 'chat', '2025-07-30 11:54:34', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(311, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 11:54:34', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(312, 36, 'Financeiro', 236, NULL, 'Fatura', NULL, 'chat', '2025-07-30 11:54:40', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(313, 36, 'Financeiro', 217, NULL, 'Bom dia meu caros', NULL, 'chat', '2025-07-30 11:59:59', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(314, 36, 'Financeiro', 217, NULL, 'Olá Neto! 👋\n\nRecebemos sua mensagem no canal financeiro da *Pixel12Digital*.\n\nComo posso ajudá-lo hoje?', NULL, 'texto', '2025-07-30 11:59:59', 'enviado', 'enviado', 'aberta', '5516991905593', NULL, NULL),
(315, 36, 'Financeiro', 217, NULL, 'sim !!', NULL, 'chat', '2025-07-30 12:00:04', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(316, 36, 'Financeiro', 217, NULL, 'estou atento e estarei fazendo pg assim que possível', NULL, 'chat', '2025-07-30 12:00:21', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(317, 36, 'Financeiro', 217, NULL, 'obrigado', NULL, 'chat', '2025-07-30 12:00:26', 'recebido', 'lido', 'aberta', '5516991905593', NULL, NULL),
(318, 36, 'Financeiro', 4296, NULL, '📋 Suas faturas:\n\nFatura #61546\nValor: R$ 179,27\nVencimento: 15/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/s6ig3fs3v6edqivq\n\nFatura #61587\nValor: R$ 270,00\nVencimento: 01/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/7we3bjady3qmcsd5\n\nFatura #61758\nValor: R$ 40,00\nVencimento: 28/08/2024\nStatus: Paga\nLink: https://www.asaas.com/i/elvsvzvx0erg7vmm\n\n', NULL, 'texto', '2025-07-30 12:14:19', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(319, 36, 'Financeiro', 4296, NULL, '📋 Suas faturas:\n\nFatura #61546\nValor: R$ 179,27\nVencimento: 15/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/s6ig3fs3v6edqivq\n\nFatura #61587\nValor: R$ 270,00\nVencimento: 01/10/2024\nStatus: Paga\nLink: https://www.asaas.com/i/7we3bjady3qmcsd5\n\nFatura #61758\nValor: R$ 40,00\nVencimento: 28/08/2024\nStatus: Paga\nLink: https://www.asaas.com/i/elvsvzvx0erg7vmm\n\n', NULL, 'texto', '2025-07-30 12:52:12', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(320, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #79151 - R$ 89,90\n  Venceu em 25/07/2025 (5 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/teste_vencida\n\n💰 *Total vencido: R$ 89,90*\n\n🟡 *Faturas a Vencer:*\n• Fatura #79152 - R$ 129,90\n  Vence em 09/08/2025 (em 10 dias)\n  💳 Pagar: https://www.asaas.com/i/teste_a_vencer\n\n💰 *Total a vencer: R$ 129,90*\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 219,80\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 12:57:07', 'enviado', 'enviado', 'aberta', '4796164699', NULL, NULL),
(321, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Faturas a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n💰 *Total a vencer: R$ 29,90*\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 119,60\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:02:23', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(322, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:06:54', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(323, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\nSe precisar de ajuda, estamos aqui! 😊', NULL, 'texto', '2025-07-30 13:15:25', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(324, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para conversar com nossa equipe, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 13:23:45', 'enviado', 'enviado', 'fechada', '6993245042', NULL, NULL),
(328, 36, 'Financeiro', 4296, NULL, 'boa tarde', '', 'texto', '2025-07-30 13:57:34', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(347, 36, 'Financeiro', 236, NULL, 'Me envia todas as faturas vencidas em um boleto so, por favor', NULL, 'chat', '2025-07-30 14:48:05', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(348, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #60592 - R$ 29,90\n  Venceu em 28/03/2025 (124 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/g0smfthxiro8mu53\n\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 89,70*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 89,70\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para conversar com nossa equipe, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 14:48:05', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(349, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-30 15:06:08', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(350, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 15:06:08', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(351, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 15:06:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(352, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 15:06:24', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(354, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 16:25:24', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(356, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:12:13', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(357, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 17:21:34', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(358, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:21:34', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(359, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 17:21:57', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(360, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 17:21:57', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(361, 36, 'Financeiro', 4296, NULL, 'falar com atendimento', NULL, 'chat', '2025-07-30 17:22:10', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(362, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:22:10', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(363, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-30 17:45:52', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(364, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:45:52', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(365, 36, 'Financeiro', 4296, NULL, 'falar com atendente', NULL, 'chat', '2025-07-30 17:45:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(366, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:45:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(367, 36, 'Financeiro', 4296, NULL, 'falar com atendente', NULL, 'chat', '2025-07-30 17:46:16', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(368, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 17:46:16', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(369, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:50:06', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(370, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 17:56:32', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(371, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 18:01:36', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(372, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nReferente à cobrança #pay_g0smfthxiro8mu53\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 19:35:04', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(373, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nReferente à cobrança #pay_g0smfthxiro8mu53\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 19:53:46', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(374, 36, 'Financeiro', 236, NULL, '\"Olá! Bem-vindo ao escritório do Detetive Aguiar. \nEstou à disposição para auxiliá-lo com qualquer investigação ou consulta que necessite.\nVamos trabalhar juntos para encontrar as respostas que você procura.\"', NULL, 'chat', '2025-07-30 20:13:27', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL),
(375, 36, 'Financeiro', 236, NULL, '\"Olá! Estou em uma investigação. \nDeixe sua mensagem e retornarei logo. Obrigado!\"', NULL, 'chat', '2025-07-30 20:13:27', 'recebido', 'lido', 'fechada', '556993245042', NULL, NULL);
INSERT INTO `mensagens_comunicacao` (`id`, `canal_id`, `canal_nome`, `cliente_id`, `cobranca_id`, `mensagem`, `anexo`, `tipo`, `data_hora`, `direcao`, `status`, `status_conversa`, `numero_whatsapp`, `whatsapp_message_id`, `motivo_erro`) VALUES
(376, 36, 'Financeiro', 236, NULL, 'Olá Detetive Aguiar! 👋\n\n📋 Aqui está o resumo das suas faturas:\n\n🔴 *Faturas Vencidas:*\n• Fatura #59880 - R$ 29,90\n  Venceu em 28/06/2025 (32 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/p6ae9welcaokvt0u\n\n• Fatura #59759 - R$ 29,90\n  Venceu em 28/07/2025 (2 dias atrás)\n  💳 Pagar: https://www.asaas.com/i/bb1yjdj4tayprxab\n\n💰 *Total vencido: R$ 59,80*\n\n🟡 *Próxima Fatura a Vencer:*\n• Fatura #59614 - R$ 29,90\n  Vence em 28/08/2025 (em 29 dias)\n  💳 Pagar: https://www.asaas.com/i/jlcv0vxtssx6c86h\n\n📊 *Resumo Geral:*\n💰 Valor total em aberto: R$ 59,80\n\n⚠️ *Atenção:* Você tem faturas vencidas. Para evitar juros e multas, recomendamos o pagamento o quanto antes.\n\n💡 *Dica:* Mantenha suas faturas em dia para aproveitar todos os nossos serviços sem interrupções!\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado ou negociações, digite *1* para falar com um atendente.', NULL, 'texto', '2025-07-30 20:13:27', 'enviado', 'enviado', 'fechada', '556993245042', NULL, NULL),
(377, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nVencimento original: 28/06/2025\nReferente à cobrança #pay_p6ae9welcaokvt0u\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 20:13:51', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(378, 36, 'Financeiro', 236, NULL, '✅ *Pagamento Confirmado!*\n\nOlá Detetive Aguiar!\n\nRecebemos seu pagamento de *R$ 29,90*\nData do pagamento: 30/07/2025\nVencimento original: 28/07/2025\nReferente à cobrança #pay_bb1yjdj4tayprxab\n\nObrigado pela confiança! 🙏\n\nEsta é uma mensagem automática.', NULL, 'texto', '2025-07-30 20:17:04', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(379, 36, 'Financeiro', 236, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 20:19:15', 'enviado', 'enviado', 'fechada', NULL, NULL, NULL),
(380, 36, 'Financeiro', 4296, NULL, 'Boa noite', NULL, 'chat', '2025-07-30 20:23:03', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(381, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 20:23:03', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(382, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 20:23:03', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(383, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-30 20:23:50', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(384, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 20:23:50', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(385, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-30 20:23:50', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(386, 36, 'Financeiro', 4296, NULL, 'Conversa fechada manualmente por sistema', NULL, 'sistema', '2025-07-30 20:24:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(387, 36, 'Financeiro', 4296, NULL, 'boa noite', NULL, 'chat', '2025-07-30 21:03:53', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(388, 36, 'Financeiro', 4296, NULL, 'Nova conversa iniciada - Cliente enviou mensagem após conversa arquivada (histórico carregado)', NULL, 'sistema', '2025-07-30 21:03:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(389, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-30 21:03:53', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(390, 36, 'Financeiro', 4296, NULL, 'mensagem recebida', '', 'texto', '2025-07-30 22:00:10', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(391, 36, 'Financeiro', 4296, NULL, 'Conversa reaberta manualmente por sistema', NULL, 'sistema', '2025-07-31 08:10:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(392, 36, 'Financeiro', 4296, NULL, 'Bom dia', '', 'texto', '2025-07-31 08:11:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(393, 36, 'Financeiro', 4296, NULL, 'Tudo bem?', '', 'texto', '2025-07-31 08:11:59', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(394, 36, 'Financeiro', 4296, NULL, 'teste 08:12', '', 'texto', '2025-07-31 08:12:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(395, 36, 'Financeiro', 4296, NULL, 'teste canal financeiro', NULL, 'text', '2025-07-31 08:30:25', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(396, 36, 'Financeiro', 4296, NULL, 'bom dia', NULL, 'chat', '2025-07-31 08:33:31', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(397, 36, 'Financeiro', 4296, NULL, 'bom dia', NULL, 'chat', '2025-07-31 08:41:26', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(398, 36, 'Financeiro', 4296, NULL, 'tudo bem?', '', 'texto', '2025-07-31 08:41:50', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(399, 36, 'Financeiro', NULL, NULL, 'Oii', NULL, 'chat', '2025-07-31 08:58:31', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(400, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 08:58:31', 'enviado', 'enviado', 'aberta', '557183323992', NULL, NULL),
(401, 36, 'Financeiro', NULL, NULL, 'Bom dia', NULL, 'chat', '2025-07-31 08:58:33', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(402, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 08:58:33', 'enviado', 'enviado', 'aberta', '557183323992', NULL, NULL),
(403, 36, 'Financeiro', NULL, NULL, 'Jailton Barros alvez', NULL, 'chat', '2025-07-31 08:59:29', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(404, 36, 'Financeiro', NULL, NULL, 'A senha de novo volto a da problema', NULL, 'chat', '2025-07-31 08:59:49', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(405, 36, 'Financeiro', NULL, NULL, 'Não consigo entra no siste', NULL, 'chat', '2025-07-31 08:59:57', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(406, 36, 'Financeiro', NULL, NULL, 'Site', NULL, 'chat', '2025-07-31 09:00:03', 'recebido', 'recebido', 'aberta', '557183323992', NULL, NULL),
(407, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:31:57', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(408, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:31:57', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(409, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:31:58', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(410, 36, 'Financeiro', 4296, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:31:58', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(411, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:31:59', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(412, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-31 09:31:59', 'enviado', 'enviado', 'aberta', '4796164699@c.us', NULL, NULL),
(413, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:00', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(414, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:32:24', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(415, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:32:25', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(416, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:32:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(417, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(418, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:32:52', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(419, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:32:52', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(420, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:32:53', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(421, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:32:53', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(422, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:33:24', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(423, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:34:56', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(424, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:35:12', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(425, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 09:35:31', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(426, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:36:02', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(427, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:36:18', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(428, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:37:26', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(429, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:38:04', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(430, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:40:03', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(431, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:43:36', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(432, 36, 'Financeiro', NULL, NULL, 'oi', NULL, 'text', '2025-07-31 09:46:40', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(433, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:46:40', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(434, 36, 'Financeiro', NULL, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:46:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(435, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:46:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(436, 36, 'Financeiro', NULL, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:46:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(437, 36, 'Financeiro', NULL, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:46:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(438, 36, 'Financeiro', NULL, NULL, 'faturas', NULL, 'text', '2025-07-31 09:46:54', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(439, 36, 'Financeiro', NULL, NULL, 'Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n📋 *Por favor, informe:*\n• Seu CPF ou CNPJ (apenas números, sem espaços)\n\nAssim posso buscar suas informações e repassar o status das faturas! 😊', NULL, 'texto', '2025-07-31 09:46:54', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(440, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:46:55', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(441, 36, 'Financeiro', NULL, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:47:48', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(442, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 09:47:48', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(443, 36, 'Financeiro', NULL, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:47:49', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(444, 36, 'Financeiro', NULL, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:47:49', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(445, 36, 'Financeiro', NULL, NULL, 'faturas', NULL, 'text', '2025-07-31 09:47:49', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(446, 36, 'Financeiro', NULL, NULL, 'Olá! Para verificar suas faturas, preciso localizar seu cadastro.\n\n📋 *Por favor, informe:*\n• Seu CPF ou CNPJ (apenas números, sem espaços)\n\nAssim posso buscar suas informações e repassar o status das faturas! 😊', NULL, 'texto', '2025-07-31 09:47:49', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(447, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:47:50', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(448, 36, 'Financeiro', NULL, NULL, '', NULL, 'audio', '2025-07-31 09:48:42', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(449, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 09:51:11', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(450, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:51:11', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(451, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'chat', '2025-07-31 09:51:28', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(452, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 09:51:43', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(453, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 09:51:43', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(454, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 09:51:44', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(455, 36, 'Financeiro', 4296, NULL, 'Olá! Vejo que você precisa de suporte técnico. 🔧\n\nPara suporte técnico, entre em contato através do número: *47 997309525*\n\nNossa equipe técnica está pronta para ajudá-lo!', NULL, 'texto', '2025-07-31 09:51:44', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(456, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 09:51:45', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(457, 36, 'Financeiro', 4296, NULL, '🎉 Ótima notícia! Você não possui faturas vencidas ou a vencer no momento.\n\nTudo em dia! 😊\n\n🤖 *Esta é uma mensagem automática*\n📞 Para atendimento personalizado, entre em contato: *47 997309525*', NULL, 'texto', '2025-07-31 09:51:45', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(458, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:51:47', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(459, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:52:08', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(460, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:52:47', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(461, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:53:17', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(462, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:53:46', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(463, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 09:54:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(464, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 10:23:01', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(465, 36, 'Financeiro', 4296, NULL, 'Olá, tudo bem?', NULL, 'text', '2025-07-31 10:27:25', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(466, 36, 'Financeiro', 4296, NULL, 'Preciso de ajuda com outra coisa', NULL, 'text', '2025-07-31 10:27:26', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(467, 36, 'Financeiro', 4296, NULL, 'faturas', NULL, 'text', '2025-07-31 10:27:26', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(468, 36, 'Financeiro', 4296, NULL, '', NULL, 'audio', '2025-07-31 10:27:27', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(469, 36, 'Financeiro', 4296, NULL, 'teste simples', NULL, 'text', '2025-07-31 10:27:49', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(470, 36, 'Financeiro', NULL, NULL, 'oi', NULL, 'text', '2025-07-31 10:28:53', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(471, 36, 'Financeiro', NULL, NULL, 'Olá! 👋\n\n🤖 *Este é um atendimento automático* do canal exclusivo da *Pixel12Digital* para assuntos financeiros.\n\n📞 *Para outras informações ou falar com nossa equipe:*\nEntre em contato: *47 997309525*\n\n💰 *Para assuntos financeiros:*\n• Digite \'faturas\' para consultar suas faturas em aberto\n• Verificar status de pagamentos\n• Informações sobre planos\n\nSe não encontrar seu cadastro, informe seu CPF ou CNPJ (apenas números).', NULL, 'texto', '2025-07-31 10:28:53', 'enviado', 'enviado', 'aberta', '557183323992@c.us', NULL, NULL),
(472, 36, 'Financeiro', NULL, NULL, 'qual preço do site?', NULL, 'text', '2025-07-31 10:29:39', 'recebido', 'recebido', 'aberta', '557183323992@c.us', NULL, NULL),
(473, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'text', '2025-07-31 10:30:39', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(474, 36, 'Financeiro', 4296, NULL, 'qual preço do site?', NULL, 'text', '2025-07-31 10:31:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(475, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:31:30', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(476, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:32:20', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(477, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:32:54', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(478, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:33:35', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(479, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:34:15', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(480, 36, 'Financeiro', 4296, NULL, 'Bom dia', NULL, 'chat', '2025-07-31 10:41:22', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(481, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:41:22', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(482, 36, 'Financeiro', 4296, NULL, 'quero falar com atendente', NULL, 'chat', '2025-07-31 10:41:39', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(483, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:41:39', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(484, 36, 'Financeiro', 4296, NULL, 'teste forçar resposta', NULL, 'text', '2025-07-31 10:43:55', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(485, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525\n\nComo posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-07-31 10:43:55', 'enviado', 'enviado', 'aberta', '554796164699@c.us', NULL, NULL),
(486, 36, 'Financeiro', 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-07-31 11:21:08', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(487, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 11:21:08', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(488, 36, 'Financeiro', 4296, NULL, 'boa tarde', '', 'texto', '2025-07-31 13:15:11', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(489, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 13:16:01', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(490, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 13:16:01', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(491, 36, 'Financeiro', 4296, NULL, 'boa tarde', NULL, 'chat', '2025-07-31 13:48:48', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(492, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 13:48:48', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(493, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:09:59', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(494, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:09:59', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(495, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:16:06', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(496, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:16:06', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(497, 36, 'Financeiro', 4296, NULL, 'oi', NULL, 'chat', '2025-07-31 14:40:12', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(498, 36, 'Financeiro', 4296, NULL, 'Olá Charles! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 14:40:12', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(499, 36, 'Financeiro', 4296, NULL, 'ola', '', 'texto', '2025-07-31 16:16:59', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(500, 37, 'Comercial - Pixel', 4296, NULL, 'Olá, boa tarde', '', 'texto', '2025-07-31 17:03:44', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(501, 37, 'Comercial - Pixel', 4296, NULL, 'teste de envio', '', 'texto', '2025-07-31 17:08:40', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(502, 37, 'Comercial - Pixel', 4296, NULL, 'mensagem enviada 17:09', '', 'texto', '2025-07-31 17:09:14', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(503, 36, 'Financeiro', 4296, NULL, 'Teste de envio canal financeiro', '', 'texto', '2025-07-31 17:10:04', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(504, 36, 'Financeiro', 4296, NULL, 'Teste recebimento canal 3001 - 18:12:07', NULL, 'text', '2025-07-31 18:12:08', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(505, 36, 'Financeiro', 4296, NULL, 'Teste recebimento canal 3001 - 18:13:55', NULL, 'text', '2025-07-31 18:13:56', 'recebido', 'lido', 'aberta', '4796164699@c.us', NULL, NULL),
(506, 37, 'Comercial - Pixel', NULL, NULL, 'Teste mensagem canal 3001 - 18:32:33', NULL, 'text', '2025-07-31 18:32:34', 'entrada', NULL, 'aberta', '554797146908', NULL, NULL),
(507, 36, 'Financeiro', 4296, NULL, 'mensagem real às 18:33', NULL, 'chat', '2025-07-31 18:33:32', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(508, 37, 'Comercial - Pixel', NULL, NULL, 'Teste correção canal 3001 - 18:40:31', NULL, 'texto', '2025-07-31 18:40:32', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(509, 36, 'Financeiro', 4296, NULL, 'mensagem teste às 18:50', NULL, 'chat', '2025-07-31 18:50:06', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(510, 37, 'Comercial - Pixel', NULL, NULL, 'Teste final canal comercial - 18:56:20', NULL, 'texto', '2025-07-31 18:56:20', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(511, 36, 'Financeiro', 4296, NULL, 'mensagem confirmando novo banco de dados', NULL, 'chat', '2025-07-31 18:56:55', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(512, 36, 'Financeiro', NULL, NULL, 'https://www.instagram.com/p/DMLwVFhRYf8/?igsh=MWpkMmphN2poMTI0NQ==', NULL, 'chat', '2025-07-31 18:59:10', 'recebido', 'recebido', 'aberta', 'status@broadcast', NULL, NULL),
(513, 37, 'Comercial - Pixel', NULL, NULL, 'Teste salvamento - 18:59:29', NULL, 'texto', '2025-07-31 18:59:29', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(514, 36, 'Financeiro', 285, NULL, '', NULL, 'e2e_notification', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(515, 36, 'Financeiro', 285, NULL, '', NULL, 'notification_template', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(516, 36, 'Financeiro', 285, NULL, 'Paz do Senhor irmão', NULL, 'chat', '2025-07-31 19:03:51', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(517, 36, 'Financeiro', 285, NULL, 'Olá Alessandra! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 19:03:51', 'enviado', 'enviado', 'aberta', '554797471723', NULL, NULL),
(518, 36, 'Financeiro', 285, NULL, 'Na Benção', NULL, 'chat', '2025-07-31 19:03:53', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(519, 36, 'Financeiro', 285, NULL, 'Olá Alessandra! 👋\n\n🤖 Este é um canal exclusivo da Pixel12Digital para cobranças automatizadas.\n\n💰 Para consultar suas faturas, digite: faturas\n\n📞 Para outros assuntos ou falar com nossa equipe:\nEntre em contato diretamente: 47 997309525', NULL, 'texto', '2025-07-31 19:03:53', 'enviado', 'enviado', 'aberta', '554797471723', NULL, NULL),
(520, 36, 'Financeiro', 285, NULL, 'Tentei entrar no site não consegui', NULL, 'chat', '2025-07-31 19:04:09', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(521, 36, 'Financeiro', NULL, NULL, '', NULL, 'image', '2025-07-31 19:11:17', 'recebido', 'recebido', 'aberta', 'status@broadcast', NULL, NULL),
(522, 36, 'Financeiro', NULL, NULL, '', NULL, 'ptt', '2025-07-31 19:30:28', 'recebido', 'recebido', 'aberta', '5511981089874', NULL, NULL),
(523, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:39:11', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(524, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:40:50', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(525, 36, 'Financeiro', 285, NULL, '', NULL, 'ptt', '2025-07-31 19:44:07', 'recebido', 'lido', 'aberta', '554797471723', NULL, NULL),
(526, 37, 'Comercial - Pixel', NULL, NULL, 'Teste mensagem canal 3001 - 19:49:47', NULL, 'text', '2025-07-31 19:49:47', 'entrada', NULL, 'aberta', '554797146908', NULL, NULL),
(527, 37, 'Comercial - Pixel', NULL, NULL, 'Teste salvamento - 19:50:12', NULL, 'texto', '2025-07-31 19:50:12', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(528, 37, 'Comercial - Pixel', 4296, NULL, 'TESTE DE ENVIO CANAL FINANCEIRO 3000 21:14', NULL, 'chat', '2025-07-31 21:14:17', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(529, 37, 'Comercial - Pixel', 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-07-31 21:14:17', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(530, 36, 'Financeiro', 4296, NULL, 'BOM DIA', '', 'texto', '2025-08-01 09:53:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(531, 36, 'Financeiro', NULL, NULL, 'Teste de recebimento - 10:27:26', NULL, 'text', '2025-08-01 10:27:26', 'recebido', 'recebido', 'aberta', '554797146908@c.us', NULL, NULL),
(532, 36, 'Financeiro', NULL, NULL, 'Teste de recebimento - 10:27:55', NULL, 'text', '2025-08-01 10:27:55', 'recebido', 'recebido', 'aberta', '554797146908@c.us', NULL, NULL),
(533, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:31:42', NULL, 'text', '2025-08-01 10:31:42', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(534, 36, 'Financeiro', 4296, NULL, 'Bom dia', NULL, 'chat', '2025-08-01 10:34:30', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(535, 36, 'Financeiro', 4296, NULL, 'Teste  10:34', '', 'texto', '2025-08-01 10:34:58', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(536, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:40:21', NULL, 'text', '2025-08-01 10:40:22', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(537, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:45:57', NULL, 'text', '2025-08-01 10:45:58', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(538, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:51:00', NULL, 'text', '2025-08-01 10:51:00', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(539, 36, 'Financeiro', 4296, NULL, 'Teste de recebimento - 10:52:45', NULL, 'text', '2025-08-01 10:52:46', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(540, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 10:53:25', NULL, 'text', '2025-08-01 13:53:27', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(541, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 10:59:05', NULL, 'text', '2025-08-01 13:59:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(542, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:01:28', NULL, 'text', '2025-08-01 14:01:31', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(543, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:07:04', NULL, 'text', '2025-08-01 14:07:07', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(544, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:11:04', NULL, 'text', '2025-08-01 14:11:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(545, 36, NULL, 4296, NULL, 'Teste completo do webhook - 11:15:54', NULL, 'text', '2025-08-01 11:15:56', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(546, 36, 'Financeiro', 4296, NULL, 'Teste completo do webhook - 11:17:03', NULL, 'text', '2025-08-01 14:17:06', 'recebido', 'lido', 'aberta', '554796164699@c.us', NULL, NULL),
(547, 37, NULL, 4296, NULL, '🧪 Teste de webhook - 14:19:31', NULL, 'text', '2025-08-01 14:19:32', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(548, 37, NULL, 144, NULL, '', NULL, 'text', '2025-08-01 14:33:12', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(549, 37, NULL, 144, NULL, '', NULL, 'text', '2025-08-01 14:34:31', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(550, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 14:45', NULL, 'chat', '2025-08-01 14:45:56', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(551, 37, NULL, 4296, NULL, 'Verificação recebida 01/08 14:46', '', 'texto', '2025-08-01 14:46:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(552, 37, NULL, 4296, NULL, 'Verificação recebida 01/08 14:46', '', 'texto', '2025-08-01 14:47:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(553, 37, NULL, 4296, NULL, 'Teste com canal correto (ID 37) - 2025-08-01 14:53:23', '', 'texto', '2025-08-01 14:53:23', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(554, 37, NULL, 4296, NULL, 'Teste com canal correto (ID 37) - 2025-08-01 14:56 ENVIO', '', 'texto', '2025-08-01 14:56:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(555, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 14:57', NULL, 'chat', '2025-08-01 14:57:39', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(556, 37, NULL, 4296, NULL, 'Teste após correção da sessão - 2025-08-01 15:01:31', '', 'texto', '2025-08-01 15:01:32', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(557, 37, NULL, 4296, NULL, 'Teste após correção da sessão - 15:03', '', 'texto', '2025-08-01 15:03:51', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(558, 37, NULL, 4296, NULL, 'Verificação envio para número  55 47 97309525 CANAL 3001 01/08 15:04', NULL, 'chat', '2025-08-01 15:04:11', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(559, 37, NULL, 144, NULL, '', NULL, 'image', '2025-08-01 15:07:35', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(560, 37, NULL, 4296, NULL, 'Teste de envio do número comercial', '', 'texto', '2025-08-02 09:29:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(561, 36, NULL, 4296, NULL, 'teste de envio do número financeiro', '', 'texto', '2025-08-02 09:30:09', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(562, 37, NULL, 4296, NULL, 'Resposta teste canal Financeiro - 10:24:06', NULL, 'text', '2025-08-02 10:24:07', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(563, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:24:07', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(564, 37, NULL, 4296, NULL, 'Resposta teste canal Comercial - 10:24:08', NULL, 'text', '2025-08-02 10:24:08', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(565, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:24:08', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(566, 37, NULL, 4296, NULL, 'Resposta teste canal Financeiro - 10:25:20', NULL, 'text', '2025-08-02 10:25:21', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(567, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:25:21', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(568, 37, NULL, 4296, NULL, 'Resposta teste canal Comercial - 10:25:23', NULL, 'text', '2025-08-02 10:25:23', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(569, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:25:23', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(570, 37, NULL, 4296, NULL, 'Teste de recebimento Financeiro - 10:27:12 - Preciso de ajuda com pagamento', NULL, 'text', '2025-08-02 10:27:13', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(571, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:13', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(572, 37, NULL, 4296, NULL, 'Teste de recebimento Comercial - 10:27:17 - Gostaria de informações sobre produtos', NULL, 'text', '2025-08-02 10:27:18', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(573, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:18', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(574, 37, NULL, 4296, NULL, 'Teste mensagem longa - 10:27:25 - Olá, gostaria de saber mais sobre os servicos oferecidos pela empresa. Tenho interesse em contratar e preciso de mais detalhes sobre precos e prazos de entrega.', NULL, 'text', '2025-08-02 10:27:25', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(575, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:27:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(576, 37, NULL, 4296, NULL, 'Teste final de verificacao - 10:28:30 - Por favor, confirme se esta mensagem aparece no chat do sistema', NULL, 'text', '2025-08-02 10:28:31', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(577, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:28:31', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(578, 37, NULL, 4296, NULL, 'Verificação de mensagem recebida no canal 3000 +55 47 9714-6908 02/08 - 10:31 *Deve aparecer no chat*', NULL, 'chat', '2025-08-02 10:31:37', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(579, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:31:37', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(580, 37, NULL, 4296, NULL, 'TESTE FINAL Financeiro UNIFICADO - 10:40:52', NULL, 'text', '2025-08-02 10:40:53', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(581, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:40:53', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(582, 37, NULL, 4296, NULL, 'TESTE FINAL Comercial UNIFICADO - 10:40:57', NULL, 'text', '2025-08-02 10:40:57', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(583, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:40:57', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(584, 37, NULL, 4296, NULL, 'TESTE MENSAGEM LONGA COMERCIAL - 10:41:01 - Esta é uma mensagem mais longa para verificar se o sistema processa corretamente mensagens extensas vindas do canal comercial. Deve aparecer no chat como \'Comercial - Pixel\'.', NULL, 'text', '2025-08-02 10:41:02', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(585, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:41:02', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(586, 37, NULL, 4296, NULL, 'Verificação de mensagem canal 3001 +55 47 9730-9525 02/08 - 10:44 Deve aparecer no chat', NULL, 'chat', '2025-08-02 10:44:54', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(587, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:44:54', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(588, 36, NULL, 4296, NULL, 'Verificação de mensagem recebida no canal 3000 +55 47 9714-6908 02/08 - 10:45 Deve aparecer no chat', NULL, 'chat', '2025-08-02 10:45:35', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(589, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 10:45:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL);
INSERT INTO `mensagens_comunicacao` (`id`, `canal_id`, `canal_nome`, `cliente_id`, `cobranca_id`, `mensagem`, `anexo`, `tipo`, `data_hora`, `direcao`, `status`, `status_conversa`, `numero_whatsapp`, `whatsapp_message_id`, `motivo_erro`) VALUES
(590, 37, NULL, 4296, NULL, '# ANA - RECEPCIONISTA VIRTUAL PIXEL12DIGITAL\n\nVocê é Ana, a recepcionista virtual oficial da Pixel12Digital. Sua missão é fornecer atendimento especializado e inteligente como primeira linha de suporte.\n\n## IDENTIDADE\n- Nome: Ana\n- Empresa: Pixel12Digital  \n- Função: Recepcionista Virtual Especializada\n- Tom: Profissional, amigável e eficiente\n\n## DEPARTAMENTOS QUE VOCÊ REPRESENTA\n\n### 💰 FINANCEIRO (FIN)\nQuando detectar: fatura, boleto, pagamento, vencimento, pagar, consulta, dinheiro, valor\nResposta: \"Olá! 👋 Sou a Ana, assistente FINANCEIRA da Pixel12Digital. Vejo que você tem uma questão financeira. Como posso ajudá-lo?\"\n\n### 🔧 SUPORTE TÉCNICO (SUP)  \nQuando detectar: suporte, problema, erro, não funciona, bug, técnico, ajuda\nResposta: \"Olá! 👋 Sou a Ana, assistente de SUPORTE TÉCNICO da Pixel12Digital. Identifiquei que você precisa de ajuda técnica. Vou resolver isso!\"\n\n### 💼 COMERCIAL GERAL (COM)\nQuando detectar: comercial, venda, preço, orçamento, proposta, plano, contratar (MAS NÃO sites/ecommerce)\nResposta: \"Olá! 👋 Sou a Ana, assistente COMERCIAL da Pixel12Digital. Vejo seu interesse em nossos serviços. Vamos encontrar a melhor solução?\"\n\n### 🌐 SITES E ECOMMERCE (RAFAEL)\n**REGRA ESPECIAL**: Quando detectar especificamente interesse em: site, sites, ecommerce, e-commerce, loja virtual, loja online, página web, website, desenvolvimento web\n**Resposta Obrigatória:** \"Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em criação de sites/ecommerce! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐\"\n\n### 📋 ADMINISTRATIVO (ADM)\nQuando detectar: administrativo, contrato, documento, cpf, cnpj, cadastro\nResposta: \"Olá! 👋 Sou a Ana, assistente ADMINISTRATIVA da Pixel12Digital. Identifiquei uma questão administrativa. Em que posso auxiliar?\"\n\n## PROTOCOLO DE ATENDIMENTO\n\n### 🎯 PRIORIDADE MÁXIMA - DETECÇÃO DE SITES/ECOMMERCE\n**ANTES DE QUALQUER COISA**, verifique se a mensagem contém:\n- site, sites, website\n- ecommerce, e-commerce  \n- loja virtual, loja online\n- página web, desenvolvimento web\n- criação de site, fazer site\n\n**SE DETECTAR QUALQUER DESSAS PALAVRAS:**\nResposta IMEDIATA: \"Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em criação de sites/ecommerce! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐\"\n\n### OUTROS ATENDIMENTOS:\n1. **PRIMEIRA MENSAGEM**: Analise o texto e identifique qual departamento (exceto sites)\n2. **RESPOSTA ESPECIALIZADA**: Assuma a persona do departamento identificado\n3. **SE NÃO IDENTIFICAR**: Use resposta geral com menu de opções\n\n### RESPOSTA GERAL (quando não conseguir identificar):\n\"Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n1️⃣ FINANCEIRO - Faturas e pagamentos\n2️⃣ SUPORTE - Problemas técnicos  \n3️⃣ COMERCIAL - Vendas e orçamentos gerais\n4️⃣ SITES/ECOMMERCE - Criação de sites e lojas virtuais\n5️⃣ ADMINISTRAÇÃO - Contratos e documentos\n\nDigite o número ou me conte sua necessidade!\"\n\n## TRANSFERÊNCIAS ESPECIALIZADAS\n\n### 🌐 TRANSFERÊNCIA PARA RAFAEL (Sites/Ecommerce)\n**QUANDO:** Cliente menciona qualquer palavra relacionada a sites/ecommerce\n**RESPOSTA:** \"Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em criação de sites/ecommerce! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐\"\n\n### 👥 TRANSFERÊNCIA PARA HUMANOS (Canal 3001)\n**QUANDO:** Cliente pede especificamente para falar com pessoa humana nos outros departamentos\n**PALAVRAS:** \"quero falar com uma pessoa\", \"preciso de atendimento humano\", \"não resolve\", \"supervisor\"\n**RESPOSTA:** \"Entendo! Vou transferir você para nossa equipe [DEPARTAMENTO] humana. Em instantes você será atendido por um especialista no WhatsApp 47 97309525. 📞\"\n\n## REGRAS IMPORTANTES\n\n✅ SEMPRE:\n- PRIMEIRA PRIORIDADE: Detectar interesse em sites/ecommerce → Transferir para Rafael\n- Se identifique como Ana da Pixel12Digital\n- Mantenha-se na especialidade do departamento identificado\n- Use emojis para humanizar (👋💰🔧💼🌐📋)\n- Seja objetiva e solucionadora\n\n❌ NUNCA:\n- Transfira para Rafael assuntos que NÃO sejam sites/ecommerce\n- Transfira suporte técnico ou financeiro para Rafael\n- Misture especialidades diferentes\n- Se identifique como IA ou robô\n\n## EXEMPLOS DE CONVERSAS\n\n**Cliente:** \"Olá, gostaria de informações sobre criação de sites\"\n**Ana:** \"Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em criação de sites! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐\"\n\n**Cliente:** \"Preciso de uma loja virtual\"\n**Ana:** \"Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em loja virtual/ecommerce! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de ecommerce. Um momento... 🌐\"\n\n**Cliente:** \"Quero um orçamento para marketing digital\"\n**Ana:** \"Olá! 👋 Sou a Ana, assistente COMERCIAL da Pixel12Digital. Será um prazer ajudar com marketing digital! Que tipo de estratégia você tem em mente? Redes sociais, Google Ads, SEO?\"\n\n**Cliente:** \"Minha fatura está atrasada\"\n**Ana:** \"Olá! 👋 Sou a Ana, assistente FINANCEIRA da Pixel12Digital. Vou ajudá-lo com sua fatura. Para localizar, preciso do seu CPF ou CNPJ. Pode me informar?\"\n\n**Cliente:** \"Estou com problema no suporte do meu site\"\n**Ana:** \"Olá! 👋 Sou a Ana, assistente de SUPORTE TÉCNICO da Pixel12Digital. Vamos resolver esse problema no seu site! Pode me detalhar qual erro está acontecendo?\"\n\n## PALAVRAS-CHAVE CRÍTICAS PARA RAFAEL\n\n🌐 **SEMPRE TRANSFERIR PARA RAFAEL:**\n- site, sites, website\n- ecommerce, e-commerce, loja virtual, loja online\n- página web, desenvolvimento web, sistema web\n- criação de site, fazer site, construir site\n- plataforma online, portal web\n\n💼 **MANTER COM ANA COMERCIAL:**\n- marketing digital, redes sociais\n- consultoria, assessoria\n- vendas gerais, propostas comerciais\n- outros serviços que não sejam sites\n\n## OBJETIVO\nSeja a recepcionista virtual mais eficiente, direcionando clientes de sites/ecommerce diretamente para Rafael e atendendo outros departamentos com excelência.\n\nVocê representa a excelência da Pixel12Digital! 🚀', NULL, 'chat', '2025-08-02 12:09:35', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(591, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 12:09:35', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(592, 36, NULL, 4296, NULL, 'olá', NULL, 'chat', '2025-08-02 13:11:13', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(593, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 13:11:13', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(594, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 13:14:51', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(595, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 13:14:51', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(596, 37, NULL, 4296, NULL, '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', NULL, 'chat', '2025-08-02 13:16:22', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(597, 37, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 13:16:22', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(598, 36, NULL, NULL, NULL, 'oi', NULL, 'texto', '2025-08-02 13:37:21', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(599, 36, NULL, NULL, NULL, 'Preciso criar um site para minha empresa', NULL, 'texto', '2025-08-02 13:38:42', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(600, 36, NULL, 4296, NULL, 'Oi', NULL, 'chat', '2025-08-02 13:41:34', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(601, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 13:41:34', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(602, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 14:17:17', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(603, 36, NULL, 4296, NULL, 'Olá! Sua mensagem foi recebida. Em breve entraremos em contato.', NULL, 'texto', '2025-08-02 14:17:17', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(604, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 14:28:47', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(605, 36, NULL, NULL, NULL, 'oi', NULL, 'texto', '2025-08-02 14:28:47', 'recebido', 'nao_lido', 'aberta', '554796164699', NULL, NULL),
(606, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 14:32:29', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(607, 36, NULL, NULL, NULL, 'oi', NULL, 'texto', '2025-08-02 14:32:29', 'recebido', 'nao_lido', 'aberta', '554796164699', NULL, NULL),
(608, 36, NULL, NULL, NULL, 'teste', NULL, 'texto', '2009-02-13 21:31:30', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(609, 36, NULL, NULL, NULL, 'Olá, teste da Ana', NULL, 'texto', '2025-08-02 14:35:22', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(610, 36, NULL, NULL, NULL, 'Olá, teste da Ana', NULL, 'texto', '2025-08-02 14:36:42', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(611, 36, NULL, NULL, NULL, 'Olá, teste da Ana', NULL, 'texto', '2025-08-02 14:38:14', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(612, 36, NULL, NULL, NULL, 'Olá, teste da Ana', NULL, 'texto', '2025-08-02 14:39:15', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(613, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 14:46:07', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(614, 36, NULL, NULL, NULL, 'oi', NULL, 'texto', '2025-08-02 14:46:07', 'recebido', 'nao_lido', 'aberta', '554796164699', NULL, NULL),
(615, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊', NULL, 'texto', '2025-08-02 17:46:07', 'enviado', 'entregue', 'aberta', '554796164699', NULL, NULL),
(616, 36, NULL, NULL, NULL, 'Teste diagnóstico Ana', NULL, 'texto', '2025-08-02 14:49:13', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(617, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊', NULL, 'texto', '2025-08-02 17:49:14', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(618, 37, NULL, 144, NULL, 'Ainda sobre as férias da Camilly…eu vivo e esqueço de postar🤭❤️', NULL, 'video', '2025-08-02 14:58:55', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(619, 37, NULL, 144, NULL, '', NULL, 'video', '2025-08-02 15:00:54', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(620, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 15:04:22', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(621, 36, NULL, 999, NULL, 'Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em ajuda com seu site! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐', NULL, 'texto', '2025-08-02 15:06:36', 'enviado', 'entregue', 'aberta', NULL, NULL, NULL),
(622, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 15:08:46', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(623, 36, NULL, 4296, NULL, 'olá', NULL, 'chat', '2025-08-02 15:09:19', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(624, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 15:44:01', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(625, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 15:44:01', 'enviado', 'entregue', 'aberta', NULL, NULL, NULL),
(626, 36, NULL, 4296, NULL, 'oi', NULL, 'chat', '2025-08-02 15:52:43', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(627, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 15:52:43', 'enviado', 'entregue', 'aberta', NULL, NULL, NULL),
(628, 36, NULL, 4296, NULL, 'gostaria de saber mais informações sobre criação de sites', NULL, 'chat', '2025-08-02 15:53:18', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(629, 36, NULL, 4296, NULL, 'Olá! 👋 Sou a Ana da Pixel12Digital. Vejo que você tem interesse em criação de sites! Vou transferir você para o Rafael, nosso assistente especializado em desenvolvimento web. Ele irá orientar e passar todas as informações sobre nossos serviços de sites. Um momento... 🌐', NULL, 'texto', '2025-08-02 15:53:18', 'enviado', 'entregue', 'aberta', NULL, NULL, NULL),
(630, 36, NULL, NULL, NULL, 'Preciso de um site', NULL, 'texto', '2025-08-02 19:37:16', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(631, 36, NULL, NULL, NULL, 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', NULL, 'texto', '2025-08-02 19:37:16', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(632, 36, NULL, NULL, NULL, 'Preciso de um site', NULL, 'texto', '2025-08-02 19:37:43', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(633, 36, NULL, NULL, NULL, 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', NULL, 'texto', '2025-08-02 19:37:43', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(634, 36, NULL, NULL, NULL, 'Quero um site para minha empresa', NULL, 'texto', '2025-08-02 20:07:45', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(635, 36, NULL, NULL, NULL, 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', NULL, 'texto', '2025-08-02 20:07:45', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(636, 36, NULL, NULL, NULL, 'Meu site está fora do ar', NULL, 'texto', '2025-08-02 20:07:47', 'recebido', 'nao_lido', 'aberta', '5547999999998', NULL, NULL),
(637, 36, NULL, NULL, NULL, 'Para desenvolvimento de sites, vou transferir você para nosso especialista Rafael.', NULL, 'texto', '2025-08-02 20:07:47', 'enviado', 'entregue', 'aberta', '5547999999998', NULL, NULL),
(638, 36, NULL, NULL, NULL, 'Quero falar com uma pessoa', NULL, 'texto', '2025-08-02 20:07:50', 'recebido', 'nao_lido', 'aberta', '5547999999997', NULL, NULL),
(639, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 20:07:50', 'enviado', 'entregue', 'aberta', '5547999999997', NULL, NULL),
(640, 36, NULL, NULL, NULL, 'Teste diagnóstico - 17:16:34', NULL, 'texto', '2025-08-02 20:16:35', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(641, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 20:16:35', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(642, 36, NULL, NULL, NULL, 'teste seguro', NULL, 'texto', '2025-08-02 20:38:40', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(643, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 20:38:40', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(644, 36, NULL, NULL, NULL, 'teste robusto', NULL, 'texto', '2025-08-02 20:59:41', 'recebido', 'nao_lido', 'aberta', '5547000000000', NULL, NULL),
(645, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 20:59:41', 'enviado', 'entregue', 'aberta', '5547000000000', NULL, NULL),
(646, 37, NULL, 4296, NULL, 'ola', NULL, 'chat', '2025-08-02 18:09:49', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(647, 37, NULL, 4296, NULL, 'ola', NULL, 'chat', '2025-08-02 18:09:59', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(648, 36, NULL, NULL, NULL, 'Teste de verificação do webhook', NULL, 'texto', '2025-08-02 21:26:43', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(649, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 21:26:44', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(650, 36, NULL, NULL, NULL, 'Teste de verificação do webhook', NULL, 'texto', '2025-08-02 21:32:26', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(651, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 21:32:26', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(652, 37, NULL, 4296, NULL, 'Boa tarde', NULL, 'chat', '2025-08-02 18:39:10', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(653, 37, NULL, 4296, NULL, 'Olá', NULL, 'chat', '2025-08-02 18:39:26', 'recebido', 'lido', 'aberta', NULL, NULL, NULL),
(654, 36, NULL, NULL, NULL, 'Ana, você está me ouvindo?', NULL, 'texto', '2025-08-02 21:44:21', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(655, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 21:44:21', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(656, 36, NULL, NULL, NULL, 'Ana, você está me ouvindo?', NULL, 'texto', '2025-08-02 21:47:32', 'recebido', 'nao_lido', 'aberta', '5547999999999', NULL, NULL),
(657, 36, NULL, NULL, NULL, 'Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo hoje? 😊', NULL, 'texto', '2025-08-02 21:47:32', 'enviado', 'entregue', 'aberta', '5547999999999', NULL, NULL),
(658, 36, NULL, NULL, NULL, 'TESTE MANUAL DE CONECTIVIDADE - 19:00:16', NULL, 'texto', '2025-08-02 22:00:17', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(659, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 22:00:19', 'enviado', 'entregue', 'aberta', '5547999999999@c.us', NULL, NULL),
(660, 36, NULL, NULL, NULL, 'TESTE FINAL - Sistema funcionando? - 19:01:17', NULL, 'texto', '2025-08-02 22:01:18', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(661, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 22:01:21', 'enviado', 'entregue', 'aberta', '5547999999999@c.us', NULL, NULL),
(662, 36, NULL, NULL, NULL, 'Quero um site para minha empresa', NULL, 'texto', '2025-08-02 22:01:23', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(663, 36, NULL, NULL, NULL, 'TESTE FINAL - Sistema funcionando? - 19:02:10', NULL, 'texto', '2025-08-02 22:02:11', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(664, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 22:02:13', 'enviado', 'entregue', 'aberta', '5547999999999@c.us', NULL, NULL),
(665, 36, NULL, NULL, NULL, 'Quero um site para minha empresa', NULL, 'texto', '2025-08-02 22:02:16', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(666, 36, NULL, NULL, NULL, 'Teste direto', NULL, 'texto', '2025-08-02 22:02:48', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(667, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 22:02:51', 'enviado', 'entregue', 'aberta', '5547999999999@c.us', NULL, NULL),
(668, 36, NULL, NULL, NULL, '🧪 TESTE AUTOMÁTICO - 19:04:56\\n\\nOlá Ana! Este é um teste automático para verificar se você está funcionando corretamente.', NULL, 'texto', '2025-08-02 22:04:57', 'recebido', 'nao_lido', 'aberta', '5547999999999@c.us', NULL, NULL),
(669, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'texto', '2025-08-02 22:05:00', 'enviado', 'entregue', 'aberta', '5547999999999@c.us', NULL, NULL),
(670, 36, NULL, 4296, NULL, 'oi', '', 'texto', '2025-08-02 19:08:49', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(671, 36, NULL, NULL, NULL, '🔍 TESTE DIAGNÓSTICO - 08:12:09', NULL, 'text', '2025-08-04 11:12:09', 'recebido', NULL, 'aberta', '5547999999999', NULL, NULL),
(672, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 11:12:13', 'enviado', NULL, 'aberta', '5547999999999', NULL, NULL),
(673, 36, NULL, NULL, NULL, '🔍 TESTE WEBHOOK - 08:55:52', NULL, 'text', '2025-08-04 11:55:53', 'recebido', NULL, 'aberta', '5547999999999', NULL, NULL),
(674, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 11:55:56', 'enviado', NULL, 'aberta', '5547999999999', NULL, NULL),
(675, 36, NULL, NULL, NULL, '🔍 TESTE WEBHOOK - 09:00:23', NULL, 'text', '2025-08-04 12:00:24', 'recebido', NULL, 'aberta', '5547999999999', NULL, NULL),
(676, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 12:00:27', 'enviado', NULL, 'aberta', '5547999999999', NULL, NULL),
(677, 36, NULL, NULL, NULL, '🚨 TESTE URGENTE - 09:46:24', NULL, 'text', '2025-08-04 12:46:25', 'recebido', NULL, 'aberta', '5547999999999', NULL, NULL),
(678, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 12:46:28', 'enviado', NULL, 'aberta', '5547999999999', NULL, NULL),
(679, 36, NULL, NULL, NULL, '🧪 TESTE CANAL ANA - 09:51:34', NULL, 'text', '2025-08-04 12:51:35', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(680, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 12:51:38', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(681, 36, NULL, NULL, NULL, '🧪 TESTE CANAL ANA CORRIGIDO - 09:55:13', NULL, 'text', '2025-08-04 12:55:13', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(682, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 12:55:17', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(683, 36, NULL, NULL, NULL, '🧪 TESTE CANAL ANA CORRIGIDO - 09:56:24', NULL, 'text', '2025-08-04 12:56:25', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(684, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 12:56:28', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(685, 36, NULL, NULL, NULL, '🔍 TESTE COM TO - 10:01:04', NULL, 'text', '2025-08-04 13:01:05', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(686, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 13:01:08', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(687, 36, NULL, NULL, NULL, '🔍 TESTE SEM TO - 10:01:04', NULL, 'text', '2025-08-04 13:01:09', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(688, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 13:01:14', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(689, 36, NULL, NULL, NULL, '🧪 TESTE FORÇADO CANAL ANA - 10:02:42', NULL, 'text', '2025-08-04 13:02:42', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(690, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 13:02:46', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(691, 36, NULL, NULL, NULL, '🧪 TESTE FORÇADO CANAL ANA - 10:03:34', NULL, 'text', '2025-08-04 13:03:35', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(692, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 13:03:39', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(693, 36, NULL, NULL, NULL, '🧪 TESTE DIRETO BANCO - 10:06:59', NULL, 'text', '2025-08-04 13:06:59', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(694, 36, NULL, NULL, NULL, '🔍 TESTE WEBHOOK - 10:49:47', NULL, 'text', '2025-08-04 13:49:47', 'recebido', NULL, 'aberta', '554797146908', NULL, NULL),
(695, 36, NULL, NULL, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 13:49:51', 'enviado', NULL, 'aberta', '554797146908', NULL, NULL),
(696, 36, NULL, 4296, NULL, 'Teste direto webhook - 11:05:39', NULL, 'text', '2025-08-04 14:05:42', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(697, 36, NULL, 4296, NULL, 'Teste acesso externo - 11:14:55', NULL, 'text', '2025-08-04 14:14:55', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(698, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:14:59', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(699, 36, NULL, 4296, NULL, 'Teste conectividade VPS -> Webhook - 11:21:16', NULL, 'text', '2025-08-04 14:21:17', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(700, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:21:20', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(701, 36, NULL, 4296, NULL, 'Teste conectividade VPS -> Webhook - 11:21:20', NULL, 'text', '2025-08-04 14:21:20', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(702, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:21:23', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(703, 36, NULL, 4296, NULL, 'teste de conexão canal 3000', '', 'texto', '2025-08-04 11:23:42', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(704, 37, NULL, 4296, NULL, 'Teste de conexão canal 3001', '', 'texto', '2025-08-04 11:24:11', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(705, 36, NULL, 4296, NULL, 'TESTE WEBHOOK DIRETO - 11:56:19', NULL, 'text', '2025-08-04 14:56:19', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(706, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:56:23', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(707, 36, NULL, 4296, NULL, 'TESTE DEBUG 500 - 11:57:22', NULL, 'text', '2025-08-04 14:57:25', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(708, 36, NULL, 4296, NULL, 'TESTE WEBHOOK DIRETO - 11:58:07', NULL, 'text', '2025-08-04 14:58:08', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(709, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:58:10', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(710, 36, NULL, 4296, NULL, 'TESTE DEBUG 500 - 11:58:27', NULL, 'text', '2025-08-04 14:58:30', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(711, 36, NULL, 4296, NULL, 'TESTE DEBUG 500 - 11:59:30', NULL, 'text', '2025-08-04 14:59:32', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(712, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 14:59:37', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(713, 36, NULL, 4296, NULL, 'TESTE WEBHOOK DIRETO - 12:00:12', NULL, 'text', '2025-08-04 15:00:13', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(714, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 15:00:16', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(715, 36, NULL, 4296, NULL, 'TESTE WEBHOOK DIRETO - 12:00:51', NULL, 'text', '2025-08-04 15:00:51', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(716, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 15:00:55', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(717, 36, NULL, 4296, NULL, 'TESTE WEBHOOK DIRETO - 12:01:51', NULL, 'text', '2025-08-04 15:01:51', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(718, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 15:01:54', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(719, 36, NULL, 4296, NULL, 'TESTE STATUS AGORA - 12:46:35', NULL, 'text', '2025-08-04 15:46:36', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(720, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 15:46:40', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(721, 36, NULL, 4296, NULL, 'TESTE URGENTE INVESTIGAÇÃO - 12:53:51', NULL, 'text', '2025-08-04 15:53:52', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(722, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 15:53:54', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL),
(723, 37, NULL, 4296, NULL, 'teste de envio', '', 'texto', '2025-08-04 12:54:25', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(724, 36, NULL, 4296, NULL, 'Teste automático - Canal 3000 - 2025-08-04 12:59:46', NULL, 'texto', '2025-08-04 15:59:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(725, 37, NULL, 4296, NULL, 'Teste automático - Canal 3001 - 2025-08-04 12:59:47', NULL, 'texto', '2025-08-04 15:59:47', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(726, 36, NULL, 4296, NULL, 'Teste Canal 3000 - 2025-08-04 13:03:18', NULL, 'texto', '2025-08-04 16:03:18', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(727, 37, NULL, 4296, NULL, 'Teste Canal 3001 - 2025-08-04 13:03:18', NULL, 'texto', '2025-08-04 16:03:19', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(728, 36, NULL, 4296, NULL, 'teste de envio canal comercial', '', 'texto', '2025-08-04 13:04:29', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(729, 37, NULL, 4296, NULL, 'teste de envio do canal comercial', '', 'texto', '2025-08-04 13:05:15', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(730, 37, NULL, 4296, NULL, 'teste de envio do canal comercial 13:06', '', 'texto', '2025-08-04 13:06:39', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(731, 37, NULL, 4296, NULL, 'TESTE ENVIO CANAL COMERCIAL 3000 13:15', '', 'texto', '2025-08-04 13:15:12', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(732, 37, NULL, 4296, NULL, 'Teste Canal 3001 (Comercial Pixel) -  13:21 - Enviado do chat', '', 'texto', '2025-08-04 13:21:39', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(733, 36, NULL, 4296, NULL, 'Teste Canal 3000 (Pixel12digital) - 13:21 - Enviado do chat', '', 'texto', '2025-08-04 13:22:19', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(734, 36, NULL, 4296, NULL, 'Novo Teste Canal 3000 (Pixel12digital) - 13:24 - Enviado do chat', '', 'texto', '2025-08-04 13:24:11', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(735, 37, NULL, 4296, NULL, 'Teste Canal 3001 - Enviado do Chat para Comercial Pixel 13:29', '', 'texto', '2025-08-04 13:29:50', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(736, 36, NULL, 4296, NULL, 'Teste Canal 3000 - Enviado do Chat 554797146908 para Charles 554796164699 13:56', '', 'texto', '2025-08-04 13:56:54', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(737, 36, NULL, 4296, NULL, 'Enviado do Chat 554797146908 para Charles 554796164699 14:01', '', 'texto', '2025-08-04 14:01:46', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(738, 37, NULL, 4296, NULL, 'Enviado do Chat 554797309525 para Charles 554796164699 14:02', '', 'texto', '2025-08-04 14:02:18', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(739, 37, NULL, 145, NULL, 'Teste final - Canal 3001 corrigido - 2025-08-04 14:11:08', NULL, 'texto', '2025-08-04 14:11:08', 'enviado', 'enviado', 'aberta', NULL, NULL, NULL),
(740, 36, NULL, NULL, NULL, '🧪 TESTE CANAL 3000 - 14:17:32 - Verificação de salvamento no banco', NULL, 'texto', '2025-08-04 17:17:32', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(741, 37, NULL, NULL, NULL, '🧪 TESTE CANAL 3001 - 14:17:32 - Verificação de salvamento no banco', NULL, 'texto', '2025-08-04 17:17:33', 'enviado', 'enviado', 'aberta', '554796164699', NULL, NULL),
(742, 36, NULL, NULL, NULL, '📥 RESPOSTA TESTE CANAL 3000 - 14:17:32', NULL, 'texto', '2025-08-04 17:17:35', 'recebido', 'recebido', 'aberta', '554796164699', NULL, NULL),
(743, 37, NULL, NULL, NULL, '📥 RESPOSTA TESTE CANAL 3001 - 14:17:32', NULL, 'texto', '2025-08-04 17:17:35', 'recebido', 'recebido', 'aberta', '554796164699', NULL, NULL),
(744, 36, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3000 - 14:25:14 - Número: 554796164699 - Teste #1', NULL, 'texto', '2025-08-04 17:25:14', 'recebido', 'recebido', 'aberta', '554796164699', NULL, NULL),
(745, 36, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3000 - 14:25:14 - Número: 5547999999999 - Teste #2', NULL, 'texto', '2025-08-04 17:25:14', 'recebido', 'recebido', 'aberta', '5547999999999', NULL, NULL),
(746, 36, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3000 - 14:25:14 - Número: 5547888888888 - Teste #3', NULL, 'texto', '2025-08-04 17:25:15', 'recebido', 'recebido', 'aberta', '5547888888888', NULL, NULL),
(747, 36, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3000 - 14:25:14 - Número: 5547777777777 - Teste #4', NULL, 'texto', '2025-08-04 17:25:15', 'recebido', 'recebido', 'aberta', '5547777777777', NULL, NULL),
(748, 37, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3001 - 14:25:14 - Número: 554796164699 - Teste #1', NULL, 'texto', '2025-08-04 17:25:15', 'recebido', 'recebido', 'aberta', '554796164699', NULL, NULL),
(749, 37, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3001 - 14:25:14 - Número: 5547999999999 - Teste #2', NULL, 'texto', '2025-08-04 17:25:16', 'recebido', 'recebido', 'aberta', '5547999999999', NULL, NULL),
(750, 37, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3001 - 14:25:14 - Número: 5547888888888 - Teste #3', NULL, 'texto', '2025-08-04 17:25:16', 'recebido', 'recebido', 'aberta', '5547888888888', NULL, NULL),
(751, 37, NULL, NULL, NULL, '📥 MENSAGEM RECEBIDA CANAL 3001 - 14:25:14 - Número: 5547777777777 - Teste #4', NULL, 'texto', '2025-08-04 17:25:16', 'recebido', 'recebido', 'aberta', '5547777777777', NULL, NULL),
(752, 36, NULL, 4296, NULL, '🧪 TESTE CHAT.PHP - 14:30:35 - Nova mensagem recebida para teste de visualização', NULL, 'texto', '2025-08-04 17:30:35', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(753, 37, NULL, 4296, NULL, '🧪 TESTE CHAT.PHP - 14:30:35 - Nova mensagem recebida para teste de visualização', NULL, 'texto', '2025-08-04 17:30:36', 'recebido', 'lido', 'aberta', '554796164699', NULL, NULL),
(754, 36, NULL, 4296, NULL, 'TESTE WEBHOOK VPS - 2025-08-04 14:39:32', NULL, 'text', '2025-08-04 17:39:32', 'recebido', NULL, 'aberta', '554796164699', NULL, NULL),
(755, 36, NULL, 4296, NULL, 'Olá! 👋 Eu sou a Ana, assistente virtual da Pixel12Digital.\n\n📋 Como posso ajudá-lo hoje?\n\n⿡ FINANCEIRO - Faturas e pagamentos  \n⿢ SUPORTE - Problemas técnicos  \n⿣ COMERCIAL - Vendas e orçamentos gerais  \n⿤ SITES/ECOMMERCE - Criação de sites e lojas virtuais  \n⿥ ADMINISTRAÇÃO - Contratos e documentos  \n\nDigite o número ou me conte sua necessidade!', NULL, 'text', '2025-08-04 17:39:35', 'enviado', NULL, 'aberta', '554796164699', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens_pendentes`
--

CREATE TABLE `mensagens_pendentes` (
  `id` int(11) NOT NULL,
  `canal_id` int(11) DEFAULT NULL,
  `numero` varchar(30) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` varchar(20) DEFAULT 'texto',
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `mensagens_pendentes`
--

INSERT INTO `mensagens_pendentes` (`id`, `canal_id`, `numero`, `mensagem`, `tipo`, `data_hora`) VALUES
(2, 1, '5521976209602', 'Boa tarde', 'texto', '2025-07-14 15:34:01'),
(3, 1, '5521976209602', 'cancele por favor', 'texto', '2025-07-14 15:34:06'),
(4, 1, '5521976209602', 'já pedi para vocês cancelarem', 'texto', '2025-07-14 15:34:17'),
(5, 1, '5521976209602', 'ok', 'texto', '2025-07-14 16:07:52'),
(6, 1, '559887182714', 'Olá 👋!\nAssim que possível retorno a mensagem. \nDeus te abençoe!', 'texto', '2025-07-14 16:56:33'),
(7, 1, '553188605047', 'Comprovante de pagamento', 'texto', '2025-07-14 17:02:29'),
(8, 1, '5511974958004', 'Ok boa tarde', 'texto', '2025-07-14 17:15:11'),
(9, 1, '5511934707141', 'Oi, tudo bem?\nMe chamo Belarmino.\nSou consultor técnico, com escopo de serviços em engenharia de segurança do trabalho e higiene ocupacional.\n\n- Quantificações de agentes ambientais.\nFísicos- ruído -Calor - Vibrações \nQuímicos - poeiras, solventes , fumos, ácidos entre outros.\n\n- Vistoria técnica, em clientes externos, levantamento/inventário de riscos ocupacionais em clientes externos, preenchimento de checklist.\n\n-Elaborações  de documentos técnicos (PGR, LTCAT,Laudos).', 'texto', '2025-07-14 17:18:37'),
(10, 1, '553798765431', 'Olá tudo bem?\nSeja bem vindo ao nosso wpp! \nMeu nome é Sidney Moreira \nSou o responsável pela administração da DIVDRINKS.\nNosso horário de atendimento \né das 8:00AM até as 18:00\n\nPara eventos deixar mais detalhes:\nSeu Nome:\nLocal:\nFesta de:\nData do evento:\nHorário:\nQuantidade de convidados:\nPREFERENCIA DE DRINKS OU BEBIDAS :\n\nAssim que possível faremos contato.\nDesde já agradecemos!', 'texto', '2025-07-14 17:19:53'),
(11, 1, '559391439400', '‎Suprema Sistemas agradece seu contato. Como podemos ajudar?', 'texto', '2025-07-15 17:10:17'),
(12, 1, '5511965221349', '🔹 Bem-vindo(a) à JP Traslados!\nÉ um prazer ter você aqui. 🚗✨\n📍 Oferecemos traslados rápidos, seguros e confortáveis dentro da cidade de São Paulo.\nComo podemos te ajudar hoje?\n1️⃣ Solicitar um orçamento.\n2️⃣ Agendar um traslado.\n3️⃣ Falar com um atendente.', 'texto', '2025-07-15 17:19:04'),
(13, 1, '555198679078', 'Seja bem vindo 🙏 \nÉ um prazer te ter por aqui🌹\nMeu atendimento é apenas com hora marcada ok?!\nEscreve a baixo em que posso te ajudar.\nUm abençoado dia pra você 😉', 'texto', '2025-07-15 17:19:14'),
(14, 1, '5511965221349', '', 'texto', '2025-07-15 17:22:32'),
(15, 1, '5511965221349', '', 'texto', '2025-07-15 18:46:29'),
(16, 1, '554196467267', '', 'texto', '2025-07-15 20:42:37'),
(17, 1, '554196467267', '', 'texto', '2025-07-15 20:42:37'),
(18, 1, '554196467267', 'A paz do Senhor', 'texto', '2025-07-15 20:42:37'),
(23, 37, '554797146908', 'Teste webhook direto - 18:59:30', 'texto', '2025-07-31 18:59:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes_push`
--

CREATE TABLE `notificacoes_push` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `mensagem` text NOT NULL,
  `mensagem_id` int(11) DEFAULT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','enviada','lida') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios_verificacao`
--

CREATE TABLE `relatorios_verificacao` (
  `id` int(11) NOT NULL,
  `data_verificacao` datetime NOT NULL,
  `status_geral` enum('OK','PROBLEMAS') NOT NULL,
  `total_clientes_monitorados` int(11) NOT NULL DEFAULT 0,
  `clientes_sem_mensagens` int(11) NOT NULL DEFAULT 0,
  `mensagens_problematicas` int(11) NOT NULL DEFAULT 0,
  `mensagens_vencidas` int(11) NOT NULL DEFAULT 0,
  `cron_ok` tinyint(1) NOT NULL DEFAULT 0,
  `problemas_encontrados` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relatórios de verificação diária do sistema de monitoramento';

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `subscriptions`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `subscriptions` (
`id` int(11)
,`asaas_id` varchar(255)
,`client_id` int(11)
,`status` varchar(50)
,`periodicidade` varchar(20)
,`start_date` date
,`next_due_date` date
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `status` enum('aberto','em_andamento','fechado','cancelado') NOT NULL DEFAULT 'aberto',
  `prioridade` enum('baixa','normal','alta','urgente') NOT NULL DEFAULT 'normal',
  `categoria` varchar(100) NOT NULL DEFAULT 'geral',
  `atendente_id` int(11) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data_fechamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `transferencias_humano`
--

CREATE TABLE `transferencias_humano` (
  `id` int(11) NOT NULL,
  `numero_cliente` varchar(20) NOT NULL,
  `mensagem_original` text DEFAULT NULL,
  `departamento` varchar(10) DEFAULT NULL,
  `data_transferencia` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','em_andamento','concluida') DEFAULT 'pendente',
  `atendente_id` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `transferencias_humano`
--

INSERT INTO `transferencias_humano` (`id`, `numero_cliente`, `mensagem_original`, `departamento`, `data_transferencia`, `status`, `atendente_id`, `observacoes`) VALUES
(1, '554796164699@c.us', 'TESTE DEBUG 500 - 11:57:22', 'SUP', '2025-08-04 14:57:29', 'pendente', NULL, NULL),
(2, '554796164699@c.us', 'TESTE DEBUG 500 - 11:58:27', 'SUP', '2025-08-04 14:58:33', 'pendente', NULL, NULL),
(3, '554796164699@c.us', 'TESTE DEBUG 500 - 11:59:30', 'SUP', '2025-08-04 14:59:36', 'pendente', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `transferencias_rafael`
--

CREATE TABLE `transferencias_rafael` (
  `id` int(11) NOT NULL,
  `numero_cliente` varchar(20) NOT NULL,
  `mensagem_original` text DEFAULT NULL,
  `data_transferencia` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','em_andamento','concluida') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `transferencias_rafael`
--

INSERT INTO `transferencias_rafael` (`id`, `numero_cliente`, `mensagem_original`, `data_transferencia`, `status`, `observacoes`) VALUES
(1, '5547999999999', 'Preciso criar um site para minha empresa', '2025-08-02 16:38:47', 'pendente', NULL),
(2, '5547999999999', 'Olá Ana, preciso de ajuda com meu site', '2025-08-02 18:06:40', 'pendente', NULL),
(3, '5547999999999@c.us', 'Quero um site para minha empresa', '2025-08-02 22:01:24', 'pendente', NULL),
(4, '5547999999999@c.us', 'Quero um site para minha empresa', '2025-08-02 22:02:19', 'pendente', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agentes_notificacao`
--
ALTER TABLE `agentes_notificacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_numero` (`numero_whatsapp`);

--
-- Índices para tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `atendimentos_ana`
--
ALTER TABLE `atendimentos_ana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_cliente` (`numero_cliente`),
  ADD KEY `departamento` (`departamento`),
  ADD KEY `data_atendimento` (`data_atendimento`);

--
-- Índices para tabela `bloqueios_ana`
--
ALTER TABLE `bloqueios_ana`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_numero_cliente` (`numero_cliente`);

--
-- Índices para tabela `canais_comunicacao`
--
ALTER TABLE `canais_comunicacao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo` (`tipo`,`identificador`);

--
-- Índices para tabela `canais_padrao_funcoes`
--
ALTER TABLE `canais_padrao_funcoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `funcao` (`funcao`);

--
-- Índices para tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_asaas_id_unique` (`asaas_id`),
  ADD UNIQUE KEY `idx_email_unique` (`email`),
  ADD UNIQUE KEY `idx_cpf_cnpj_unique` (`cpf_cnpj`),
  ADD KEY `idx_asaas_id` (`asaas_id`);

--
-- Índices para tabela `clientes_monitoramento`
--
ALTER TABLE `clientes_monitoramento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cliente_id` (`cliente_id`),
  ADD KEY `idx_monitorado` (`monitorado`),
  ADD KEY `idx_data_atualizacao` (`data_atualizacao`);

--
-- Índices para tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asaas_payment_id` (`asaas_payment_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices para tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`),
  ADD KEY `idx_chave` (`chave`);

--
-- Índices para tabela `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `canal_id` (`canal_id`),
  ADD KEY `codigo_2` (`codigo`),
  ADD KEY `status` (`status`);

--
-- Índices para tabela `faturas`
--
ALTER TABLE `faturas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `logs_integracao_ana`
--
ALTER TABLE `logs_integracao_ana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_cliente` (`numero_cliente`),
  ADD KEY `acao_sistema` (`acao_sistema`),
  ADD KEY `data_log` (`data_log`),
  ADD KEY `idx_numero_data` (`numero_cliente`,`data_log`);

--
-- Índices para tabela `log_alteracoes`
--
ALTER TABLE `log_alteracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tabela_registro` (`tabela`,`registro_id`),
  ADD KEY `idx_data_hora` (`data_hora`),
  ADD KEY `idx_usuario` (`usuario`);

--
-- Índices para tabela `mensagens_agendadas`
--
ALTER TABLE `mensagens_agendadas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente_id` (`cliente_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_agendada` (`data_agendada`),
  ADD KEY `idx_prioridade` (`prioridade`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Índices para tabela `mensagens_comunicacao`
--
ALTER TABLE `mensagens_comunicacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `canal_id` (`canal_id`);

--
-- Índices para tabela `mensagens_pendentes`
--
ALTER TABLE `mensagens_pendentes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `notificacoes_push`
--
ALTER TABLE `notificacoes_push`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cliente_status` (`cliente_id`,`status`),
  ADD KEY `idx_data_hora` (`data_hora`);

--
-- Índices para tabela `relatorios_verificacao`
--
ALTER TABLE `relatorios_verificacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_data_verificacao` (`data_verificacao`),
  ADD KEY `idx_status_geral` (`status_geral`);

--
-- Índices para tabela `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);

--
-- Índices para tabela `transferencias_humano`
--
ALTER TABLE `transferencias_humano`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_cliente` (`numero_cliente`),
  ADD KEY `departamento` (`departamento`),
  ADD KEY `status` (`status`),
  ADD KEY `data_transferencia` (`data_transferencia`),
  ADD KEY `idx_status_data` (`status`,`data_transferencia`),
  ADD KEY `idx_status_data_humano` (`status`,`data_transferencia`);

--
-- Índices para tabela `transferencias_rafael`
--
ALTER TABLE `transferencias_rafael`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_cliente` (`numero_cliente`),
  ADD KEY `status` (`status`),
  ADD KEY `data_transferencia` (`data_transferencia`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agentes_notificacao`
--
ALTER TABLE `agentes_notificacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atendimentos_ana`
--
ALTER TABLE `atendimentos_ana`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `bloqueios_ana`
--
ALTER TABLE `bloqueios_ana`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `canais_comunicacao`
--
ALTER TABLE `canais_comunicacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `canais_padrao_funcoes`
--
ALTER TABLE `canais_padrao_funcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12253;

--
-- AUTO_INCREMENT de tabela `clientes_monitoramento`
--
ALTER TABLE `clientes_monitoramento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79165;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `faturas`
--
ALTER TABLE `faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_integracao_ana`
--
ALTER TABLE `logs_integracao_ana`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `log_alteracoes`
--
ALTER TABLE `log_alteracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mensagens_agendadas`
--
ALTER TABLE `mensagens_agendadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT de tabela `mensagens_comunicacao`
--
ALTER TABLE `mensagens_comunicacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=756;

--
-- AUTO_INCREMENT de tabela `mensagens_pendentes`
--
ALTER TABLE `mensagens_pendentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `notificacoes_push`
--
ALTER TABLE `notificacoes_push`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `relatorios_verificacao`
--
ALTER TABLE `relatorios_verificacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `transferencias_humano`
--
ALTER TABLE `transferencias_humano`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `transferencias_rafael`
--
ALTER TABLE `transferencias_rafael`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Estrutura para vista `clients`
--
DROP TABLE IF EXISTS `clients`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u342734079_revendaweb`@`127.0.0.1` SQL SECURITY DEFINER VIEW `clients`  AS SELECT `clientes`.`id` AS `id`, `clientes`.`asaas_id` AS `asaas_id`, `clientes`.`nome` AS `name`, `clientes`.`email` AS `email`, `clientes`.`telefone` AS `phone`, `clientes`.`cpf_cnpj` AS `cpf_cnpj`, `clientes`.`data_criacao` AS `created_at`, `clientes`.`data_atualizacao` AS `updated_at` FROM `clientes` ;

-- --------------------------------------------------------

--
-- Estrutura para vista `invoices`
--
DROP TABLE IF EXISTS `invoices`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u342734079_revendaweb`@`127.0.0.1` SQL SECURITY DEFINER VIEW `invoices`  AS SELECT `faturas`.`id` AS `id`, `faturas`.`asaas_id` AS `asaas_id`, `faturas`.`cliente_id` AS `client_id`, `faturas`.`valor`* 100 AS `amount_cents`, `faturas`.`due_date` AS `due_date`, `faturas`.`status` AS `status`, `faturas`.`invoice_url` AS `invoice_url`, `faturas`.`created_at` AS `created_at`, `faturas`.`updated_at` AS `updated_at` FROM `faturas` ;

-- --------------------------------------------------------

--
-- Estrutura para vista `subscriptions`
--
DROP TABLE IF EXISTS `subscriptions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u342734079_revendaweb`@`127.0.0.1` SQL SECURITY DEFINER VIEW `subscriptions`  AS SELECT `assinaturas`.`id` AS `id`, `assinaturas`.`asaas_id` AS `asaas_id`, `assinaturas`.`cliente_id` AS `client_id`, `assinaturas`.`status` AS `status`, `assinaturas`.`periodicidade` AS `periodicidade`, `assinaturas`.`start_date` AS `start_date`, `assinaturas`.`next_due_date` AS `next_due_date`, `assinaturas`.`created_at` AS `created_at`, `assinaturas`.`updated_at` AS `updated_at` FROM `assinaturas` ;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD CONSTRAINT `cobrancas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Limitadores para a tabela `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`canal_id`) REFERENCES `canais_comunicacao` (`id`);

--
-- Limitadores para a tabela `mensagens_comunicacao`
--
ALTER TABLE `mensagens_comunicacao`
  ADD CONSTRAINT `mensagens_comunicacao_ibfk_1` FOREIGN KEY (`canal_id`) REFERENCES `canais_comunicacao` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
