-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 13/03/2026 às 22:13
-- Versão do servidor: 8.0.45-36
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `anto4524_resto_saas`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_03_08_000002_add_timestamps_to_pedidos_table', 2),
(6, '2026_03_09_000001_add_troco_to_pagamentos_table', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_assinaturas`
--

CREATE TABLE `tb_assinaturas` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `plano_id` bigint DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_assinaturas`
--

INSERT INTO `tb_assinaturas` (`id`, `empresa_id`, `plano_id`, `data_inicio`, `data_fim`, `status`, `created_at`, `updated_at`) VALUES
(3, 4, 1, '2026-03-05', '2026-07-05', 'trial', '2026-03-05 07:06:12', '2026-03-11 03:20:08'),
(4, 5, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 21:39:45', '2026-03-10 21:39:45'),
(5, 6, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:02:23', '2026-03-10 22:02:23'),
(6, 7, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:17:03', '2026-03-10 22:17:03'),
(7, 8, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:24:28', '2026-03-10 22:24:28'),
(8, 9, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:29:31', '2026-03-10 22:29:31'),
(9, 10, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:39:51', '2026-03-10 22:39:51'),
(10, 11, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:41:56', '2026-03-10 22:41:56'),
(11, 12, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:44:29', '2026-03-10 22:44:29'),
(12, 13, 1, '2026-03-10', '2026-06-10', 'trial', '2026-03-10 22:52:36', '2026-03-10 22:52:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_caixas`
--

CREATE TABLE `tb_caixas` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `usuario_id` bigint DEFAULT NULL,
  `aberto_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fechado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `saldo_inicial` decimal(10,2) DEFAULT NULL,
  `saldo_final` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_caixa_movimentos`
--

CREATE TABLE `tb_caixa_movimentos` (
  `id` bigint NOT NULL,
  `caixa_id` bigint DEFAULT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_categorias`
--

CREATE TABLE `tb_categorias` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_general_ci NOT NULL,
  `ativo` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_categorias`
--

INSERT INTO `tb_categorias` (`id`, `empresa_id`, `nome`, `descricao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 4, 'Bebidas', 'Bebidas diversas', 1, '2026-03-08 13:56:10', '2026-03-08 12:34:46'),
(2, 4, 'Lanches', 'Lanches diversos', 1, '2026-03-08 15:57:34', '2026-03-08 15:57:34'),
(3, 4, 'Espetinhos', 'Espetinhos diversos de carne, frango e legumes', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(4, 4, 'Porções', 'Porções para compartilhar', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(5, 4, 'Bebidas Quentes', 'Café, chá e outras bebidas quentes', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(6, 4, 'Sucos Naturais', 'Sucos feitos na hora', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(7, 4, 'Refrigerantes', 'Refrigerantes em lata e 600ml', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(8, 4, 'Cervejas', 'Cervejas long neck e garrafa', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(9, 4, 'Drinks', 'Drinks e coquetéis', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(10, 4, 'Sobremesas', 'Doces e sobremesas', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(11, 4, 'Caldo', 'Caldos diversos', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(12, 4, 'Salgados', 'Salgados fritos e assados', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(13, 4, 'Combo Especial', 'Combos promocionais', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13'),
(14, 4, 'Adicionais', 'Complementos e adicionais', 1, '2026-03-10 03:55:13', '2026-03-10 03:55:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_clientes`
--

CREATE TABLE `tb_clientes` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_clientes`
--

INSERT INTO `tb_clientes` (`id`, `empresa_id`, `nome`, `telefone`, `email`, `created_at`, `updated_at`) VALUES
(1, 4, 'Antonio Ronaldo de Paula Nascimento', '85987761553', 'ronaldodepaulasurf@gmail.com', '2026-03-12 12:19:03', '2026-03-12 12:19:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_cliente_enderecos`
--

CREATE TABLE `tb_cliente_enderecos` (
  `id` bigint NOT NULL,
  `cliente_id` bigint DEFAULT NULL,
  `endereco` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referencia` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_comandas`
--

CREATE TABLE `tb_comandas` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `numero` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aberta','fechada') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_comandas`
--

INSERT INTO `tb_comandas` (`id`, `empresa_id`, `numero`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, '1', 'aberta', '2026-03-08 16:54:13', '2026-03-08 16:54:13'),
(3, 4, 'C002', 'aberta', '2026-03-08 17:16:26', '2026-03-08 17:16:44'),
(4, 4, 'C001', 'aberta', '2026-03-08 19:25:14', '2026-03-08 19:25:14'),
(5, 4, 'C001', 'aberta', '2026-03-08 22:01:16', '2026-03-08 22:01:16'),
(6, 4, 'C004', 'aberta', '2026-03-08 22:02:22', '2026-03-08 22:02:22'),
(7, 4, 'C001', 'aberta', '2026-03-08 22:10:03', '2026-03-08 22:10:03'),
(8, 4, 'C002', 'aberta', '2026-03-08 22:12:38', '2026-03-08 22:12:38'),
(9, 4, 'C004', 'aberta', '2026-03-08 22:12:41', '2026-03-08 22:12:41'),
(10, 4, 'C001', 'aberta', '2026-03-08 22:12:47', '2026-03-08 22:12:47'),
(11, 4, 'C003', 'aberta', '2026-03-08 22:12:51', '2026-03-08 22:12:51'),
(12, 4, 'C002', 'aberta', '2026-03-08 22:12:52', '2026-03-08 22:12:52'),
(13, 4, 'C001', 'aberta', '2026-03-08 22:12:54', '2026-03-08 22:12:54'),
(14, 4, 'C001', 'aberta', '2026-03-08 22:23:36', '2026-03-08 22:23:36'),
(15, 4, 'C001', 'aberta', '2026-03-08 22:26:32', '2026-03-08 22:26:32'),
(16, 4, 'C001', 'aberta', '2026-03-08 22:26:36', '2026-03-08 22:26:36'),
(17, 4, 'C001', 'aberta', '2026-03-08 22:26:43', '2026-03-08 22:26:43'),
(18, 4, 'C002', 'aberta', '2026-03-08 22:44:25', '2026-03-08 22:44:25'),
(19, 4, 'C002', 'aberta', '2026-03-08 22:49:34', '2026-03-08 22:49:34'),
(20, 4, 'C001', 'aberta', '2026-03-08 22:49:48', '2026-03-08 22:49:48'),
(21, 4, 'C002', 'aberta', '2026-03-08 22:50:52', '2026-03-08 22:50:52'),
(22, 4, 'C001', 'aberta', '2026-03-08 22:50:55', '2026-03-08 22:50:55'),
(23, 4, 'C001', 'aberta', '2026-03-09 04:01:11', '2026-03-09 04:01:11'),
(24, 4, 'C001', 'aberta', '2026-03-09 05:07:57', '2026-03-09 05:07:57'),
(25, 4, 'C004', 'aberta', '2026-03-09 05:17:11', '2026-03-09 05:17:11'),
(26, 4, 'C004', 'aberta', '2026-03-09 05:28:29', '2026-03-09 05:28:29'),
(27, 4, 'C004', 'aberta', '2026-03-09 05:28:58', '2026-03-09 05:28:58'),
(28, 4, 'C004', 'aberta', '2026-03-09 05:29:02', '2026-03-09 05:29:02'),
(29, 4, 'C004', 'aberta', '2026-03-09 05:29:05', '2026-03-09 05:29:05'),
(30, 4, 'C001', 'aberta', '2026-03-09 05:30:32', '2026-03-09 05:30:32'),
(31, 4, 'C004', 'aberta', '2026-03-09 05:43:16', '2026-03-09 05:43:16'),
(32, 4, 'C001', 'aberta', '2026-03-09 05:43:27', '2026-03-09 05:43:27'),
(33, 4, 'C001', 'aberta', '2026-03-09 05:52:13', '2026-03-09 05:52:13'),
(34, 4, 'C001', 'aberta', '2026-03-09 05:55:39', '2026-03-09 05:55:39'),
(35, 4, 'C001', 'aberta', '2026-03-09 06:20:14', '2026-03-09 06:20:14'),
(36, 4, 'C001', 'aberta', '2026-03-09 06:21:13', '2026-03-09 06:21:13'),
(37, 4, 'C004', 'aberta', '2026-03-09 07:18:12', '2026-03-09 07:18:12'),
(38, 4, 'C001', 'aberta', '2026-03-09 07:22:17', '2026-03-09 07:22:17'),
(39, 4, 'C001', 'aberta', '2026-03-09 07:41:06', '2026-03-09 07:41:06'),
(40, 4, 'C001', 'aberta', '2026-03-09 15:12:27', '2026-03-09 15:12:27'),
(41, 4, 'C004', 'aberta', '2026-03-09 15:16:22', '2026-03-09 15:16:22'),
(42, 4, 'C001', 'aberta', '2026-03-09 15:51:59', '2026-03-09 15:51:59'),
(43, 4, 'C002', 'aberta', '2026-03-09 15:52:51', '2026-03-09 15:52:51'),
(44, 4, 'C003', 'aberta', '2026-03-09 16:00:24', '2026-03-09 16:00:24'),
(48, 4, 'C004', 'aberta', '2026-03-09 16:11:36', '2026-03-09 16:11:36'),
(49, 4, 'C004', 'aberta', '2026-03-09 16:13:51', '2026-03-09 16:13:51'),
(50, 4, '1', 'aberta', '2026-03-09 16:26:25', '2026-03-09 16:26:25'),
(51, 4, '2', 'aberta', '2026-03-09 16:26:48', '2026-03-09 16:26:48'),
(52, 4, 'C003', 'aberta', '2026-03-09 16:29:19', '2026-03-09 16:29:19'),
(53, 4, 'C004', 'aberta', '2026-03-09 16:39:00', '2026-03-09 16:39:00'),
(54, 4, 'C001', 'aberta', '2026-03-10 04:05:54', '2026-03-10 04:05:54'),
(55, 4, 'C001', 'aberta', '2026-03-11 06:45:58', '2026-03-11 06:45:58'),
(56, 4, 'C002', 'aberta', '2026-03-11 14:57:22', '2026-03-11 14:57:22'),
(57, 4, 'C001', 'aberta', '2026-03-12 01:12:05', '2026-03-12 01:12:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_configuracoes`
--

CREATE TABLE `tb_configuracoes` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `chave` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_confirmacoes_email`
--

CREATE TABLE `tb_confirmacoes_email` (
  `id` bigint UNSIGNED NOT NULL,
  `empresa_id` bigint UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL,
  `token` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_em` datetime NOT NULL,
  `confirmado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_confirmacoes_email`
--

INSERT INTO `tb_confirmacoes_email` (`id`, `empresa_id`, `usuario_id`, `token`, `expira_em`, `confirmado_em`, `created_at`, `updated_at`) VALUES
(1, 4, 2, 'WE0jDPbrh7CH5cOAG7s2LgDVCW8KCijpLrDOjyd2gNnOr0EyWDfdG9Uz7V57pVCY', '2026-03-07 04:06:12', NULL, '2026-03-05 07:06:12', '2026-03-05 07:06:12'),
(2, 5, 5, '55z6zFbkbYMm51QtzVYTa3feTWs6ZFB94EZ2PMC81hBw28dhEd6XewqBQVjUG8Rs', '2026-03-12 18:39:46', NULL, '2026-03-10 21:39:46', '2026-03-10 21:39:46'),
(3, 6, 6, 'HOjQF9YZlfHH2ki5NFTKcw2aJL9csJbGHX0qI97Bad3Sz35n2GmvFIfDCkwmc4vB', '2026-03-12 19:02:23', NULL, '2026-03-10 22:02:23', '2026-03-10 22:02:23'),
(4, 7, 7, 'B32kvrQh3KQEjaLWOqsFpxFiQgXLMHeCIU6dDOD4Op2v6t2f3ZUEoCjH7FErU0I0', '2026-03-12 19:17:04', NULL, '2026-03-10 22:17:04', '2026-03-10 22:17:04'),
(5, 8, 8, 'HpE2mZexFVn9cR3TcMvBsZCr5XmTMqlmPscHUE5K6dtpYHjcaYzcQT2SMj34KJNv', '2026-03-12 19:24:28', NULL, '2026-03-10 22:24:28', '2026-03-10 22:24:28'),
(6, 9, 9, 'jbyqnnP8Fe2VDaksdKmpK7Z7QGlYLXhdU5jaTbzV2UicJBcJ6jwLEsjVFLLvTAcA', '2026-03-12 19:29:31', NULL, '2026-03-10 22:29:31', '2026-03-10 22:29:31'),
(7, 10, 10, '8DPbnBCddSuqEb4AzYUSQiZq53B2HdCWp02joctwgUWDsBCUhMKIaFhewMrD2UHB', '2026-03-12 19:39:51', NULL, '2026-03-10 22:39:51', '2026-03-10 22:39:51'),
(8, 11, 11, 'GMff8IuY8vasMExMDg6jDepKHatarohBp95lCUjDbbk5d1CHk5F0nanYlj7TZTcJ', '2026-03-12 19:41:56', NULL, '2026-03-10 22:41:56', '2026-03-10 22:41:56'),
(9, 12, 12, 'Wo5nqA45nFDkhQpCbFtenmDSMmxZbK7aAeM0A9Wuwnjf5TYzdSqOOylrHAVOKPkP', '2026-03-12 19:44:29', NULL, '2026-03-10 22:44:29', '2026-03-10 22:44:29'),
(10, 13, 13, 'CWbYaWJU0NhehkgtqhmRJzTwYB6Q6ZJrIxvTM8MJnJsvhrwNovsmGy9cH8eM4j58', '2026-03-12 19:52:36', NULL, '2026-03-10 22:52:36', '2026-03-10 22:52:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_cozinha_estacoes`
--

CREATE TABLE `tb_cozinha_estacoes` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_cozinha_estacoes`
--

INSERT INTO `tb_cozinha_estacoes` (`id`, `empresa_id`, `nome`, `created_at`, `updated_at`) VALUES
(1, 4, 'Cozinha Geral', '2026-03-09 03:59:41', '2026-03-09 03:59:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_cozinha_itens`
--

CREATE TABLE `tb_cozinha_itens` (
  `id` bigint NOT NULL,
  `pedido_item_id` bigint DEFAULT NULL,
  `estacao_id` bigint DEFAULT NULL,
  `status` enum('pendente','recebido','preparo','pronto') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_cozinha_itens`
--

INSERT INTO `tb_cozinha_itens` (`id`, `pedido_item_id`, `estacao_id`, `status`, `created_at`, `updated_at`) VALUES
(7, 45, 1, 'recebido', '2026-03-09 05:53:22', '2026-03-09 05:53:22'),
(8, 46, 1, 'recebido', '2026-03-09 05:53:22', '2026-03-09 05:53:22'),
(9, 47, 1, 'recebido', '2026-03-09 05:53:22', '2026-03-09 05:53:22'),
(10, 52, 1, 'recebido', '2026-03-09 06:22:25', '2026-03-09 06:22:25'),
(11, 53, 1, 'recebido', '2026-03-09 06:22:25', '2026-03-09 06:22:25'),
(12, 54, 1, 'recebido', '2026-03-09 06:22:25', '2026-03-09 06:22:25'),
(13, 58, 1, 'recebido', '2026-03-09 07:18:54', '2026-03-09 07:18:54'),
(14, 59, 1, 'recebido', '2026-03-09 07:18:54', '2026-03-09 07:18:54'),
(15, 60, 1, 'recebido', '2026-03-09 07:18:54', '2026-03-09 07:18:54'),
(16, 65, 1, 'recebido', '2026-03-09 15:13:32', '2026-03-09 15:13:32'),
(17, 66, 1, 'recebido', '2026-03-09 15:13:32', '2026-03-09 15:13:32'),
(18, 67, 1, 'recebido', '2026-03-09 15:13:32', '2026-03-09 15:13:32'),
(19, 68, 1, 'recebido', '2026-03-09 15:17:10', '2026-03-09 15:17:10'),
(20, 69, 1, 'recebido', '2026-03-09 15:17:10', '2026-03-09 15:17:10'),
(21, 70, 1, 'recebido', '2026-03-09 15:17:10', '2026-03-09 15:17:10'),
(22, 84, 1, 'recebido', '2026-03-09 16:40:08', '2026-03-09 16:40:08'),
(23, 85, 1, 'recebido', '2026-03-09 16:40:09', '2026-03-09 16:40:09'),
(24, 86, 1, 'recebido', '2026-03-09 16:40:09', '2026-03-09 16:40:09'),
(25, 87, 1, 'recebido', '2026-03-09 19:22:50', '2026-03-09 19:22:50'),
(26, 88, 1, 'recebido', '2026-03-09 19:22:51', '2026-03-09 19:22:51'),
(27, 89, 1, 'recebido', '2026-03-09 19:32:21', '2026-03-09 19:32:21'),
(28, 90, 1, 'recebido', '2026-03-09 19:32:22', '2026-03-09 19:32:22'),
(29, 91, 1, 'recebido', '2026-03-09 19:32:22', '2026-03-09 19:32:22'),
(30, 92, 1, 'recebido', '2026-03-09 20:15:50', '2026-03-09 20:15:50'),
(31, 93, 1, 'recebido', '2026-03-09 21:00:46', '2026-03-09 21:00:46'),
(32, 94, 1, 'recebido', '2026-03-09 22:04:59', '2026-03-09 22:04:59'),
(33, 95, 1, 'recebido', '2026-03-09 22:04:59', '2026-03-09 22:04:59'),
(34, 96, 1, 'recebido', '2026-03-09 22:35:52', '2026-03-09 22:35:52'),
(35, 97, 1, 'recebido', '2026-03-09 22:35:53', '2026-03-09 22:35:53'),
(36, 98, 1, 'recebido', '2026-03-09 22:35:53', '2026-03-09 22:35:53'),
(37, 99, 1, 'recebido', '2026-03-10 03:46:02', '2026-03-10 03:46:02'),
(38, 100, 1, 'recebido', '2026-03-10 03:46:02', '2026-03-10 03:46:02'),
(39, 101, 1, 'recebido', '2026-03-10 04:00:14', '2026-03-10 04:00:14'),
(40, 102, 1, 'recebido', '2026-03-10 04:00:14', '2026-03-10 04:00:14'),
(41, 103, 1, 'recebido', '2026-03-10 04:00:14', '2026-03-10 04:00:14'),
(42, 104, 1, 'recebido', '2026-03-10 04:03:40', '2026-03-10 04:03:40'),
(43, 105, 1, 'recebido', '2026-03-10 04:06:11', '2026-03-10 04:06:11'),
(44, 106, 1, 'recebido', '2026-03-10 04:06:11', '2026-03-10 04:06:11'),
(45, 107, 1, 'recebido', '2026-03-10 05:03:56', '2026-03-10 05:03:56'),
(46, 108, 1, 'recebido', '2026-03-10 05:03:56', '2026-03-10 05:03:56'),
(47, 109, 1, 'recebido', '2026-03-10 05:03:56', '2026-03-10 05:03:56'),
(48, 110, 1, 'recebido', '2026-03-10 05:50:27', '2026-03-10 05:50:27'),
(49, 111, 1, 'recebido', '2026-03-10 05:50:27', '2026-03-10 05:50:27'),
(50, 112, 1, 'recebido', '2026-03-10 06:00:06', '2026-03-10 06:00:06'),
(51, 113, 1, 'recebido', '2026-03-10 06:00:06', '2026-03-10 06:00:06'),
(52, 114, 1, 'recebido', '2026-03-10 06:05:50', '2026-03-10 06:05:50'),
(53, 115, 1, 'recebido', '2026-03-10 06:27:50', '2026-03-10 06:27:50'),
(54, 116, 1, 'recebido', '2026-03-10 06:27:58', '2026-03-10 06:27:58'),
(55, 117, 1, 'recebido', '2026-03-10 06:30:30', '2026-03-10 06:30:30'),
(56, 118, 1, 'recebido', '2026-03-10 06:30:30', '2026-03-10 06:30:30'),
(57, 119, 1, 'recebido', '2026-03-10 06:32:01', '2026-03-10 06:32:01'),
(58, 120, 1, 'recebido', '2026-03-10 06:39:25', '2026-03-10 06:39:25'),
(59, 121, 1, 'recebido', '2026-03-10 06:39:25', '2026-03-10 06:39:25'),
(60, 122, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(61, 123, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(62, 124, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(63, 125, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(64, 126, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(65, 127, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(66, 128, 1, 'recebido', '2026-03-10 07:04:14', '2026-03-10 07:04:14'),
(67, 129, 1, 'recebido', '2026-03-10 07:09:19', '2026-03-10 07:09:19'),
(68, 130, 1, 'recebido', '2026-03-10 07:16:27', '2026-03-10 07:16:27'),
(69, 131, 1, 'recebido', '2026-03-10 07:16:27', '2026-03-10 07:16:27'),
(70, 132, 1, 'recebido', '2026-03-10 07:16:27', '2026-03-10 07:16:27'),
(71, 133, 1, 'recebido', '2026-03-10 07:16:27', '2026-03-10 07:16:27'),
(72, 134, 1, 'recebido', '2026-03-10 07:16:27', '2026-03-10 07:16:27'),
(73, 135, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(74, 136, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(75, 137, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(76, 138, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(77, 139, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(78, 140, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(79, 141, 1, 'recebido', '2026-03-10 12:06:11', '2026-03-10 12:06:11'),
(80, 142, 1, 'recebido', '2026-03-10 12:07:02', '2026-03-10 12:07:02'),
(81, 143, 1, 'recebido', '2026-03-10 12:09:39', '2026-03-10 12:09:39'),
(82, 144, 1, 'recebido', '2026-03-10 12:09:39', '2026-03-10 12:09:39'),
(83, 155, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(84, 156, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(85, 157, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(86, 158, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(87, 159, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(88, 160, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(89, 161, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(90, 162, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(91, 163, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(92, 164, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(93, 165, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(94, 166, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(95, 167, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(96, 168, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(97, 169, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(98, 170, 1, 'recebido', '2026-03-10 12:28:43', '2026-03-10 12:28:43'),
(99, 171, 1, 'recebido', '2026-03-10 13:57:16', '2026-03-10 13:57:16'),
(100, 172, 1, 'recebido', '2026-03-10 13:57:16', '2026-03-10 13:57:16'),
(101, 173, 1, 'recebido', '2026-03-10 13:57:16', '2026-03-10 13:57:16'),
(102, 174, 1, 'recebido', '2026-03-10 13:57:16', '2026-03-10 13:57:16'),
(103, 145, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(104, 146, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(105, 147, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(106, 148, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(107, 149, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(108, 150, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(109, 151, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(110, 152, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(111, 153, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(112, 154, 1, 'recebido', '2026-03-10 14:56:00', '2026-03-10 14:56:00'),
(113, 175, 1, 'recebido', '2026-03-10 14:59:44', '2026-03-10 14:59:44'),
(114, 176, 1, 'recebido', '2026-03-10 14:59:44', '2026-03-10 14:59:44'),
(115, 177, 1, 'recebido', '2026-03-10 14:59:44', '2026-03-10 14:59:44'),
(116, 178, 1, 'recebido', '2026-03-10 15:47:12', '2026-03-10 15:47:12'),
(117, 179, 1, 'recebido', '2026-03-10 15:47:12', '2026-03-10 15:47:12'),
(118, 185, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(119, 186, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(120, 187, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(121, 188, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(122, 189, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(123, 190, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(124, 193, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(125, 194, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(126, 195, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(127, 196, 1, 'recebido', '2026-03-10 16:51:03', '2026-03-10 16:51:03'),
(128, 180, 1, 'recebido', '2026-03-10 17:12:36', '2026-03-10 17:12:36'),
(129, 181, 1, 'recebido', '2026-03-10 17:12:36', '2026-03-10 17:12:36'),
(130, 182, 1, 'recebido', '2026-03-10 17:12:36', '2026-03-10 17:12:36'),
(131, 183, 1, 'recebido', '2026-03-10 17:12:36', '2026-03-10 17:12:36'),
(132, 184, 1, 'recebido', '2026-03-10 17:12:36', '2026-03-10 17:12:36'),
(133, 197, 1, 'recebido', '2026-03-10 17:13:34', '2026-03-10 17:13:34'),
(134, 198, 1, 'recebido', '2026-03-10 17:13:34', '2026-03-10 17:13:34'),
(135, 199, 1, 'recebido', '2026-03-10 17:13:34', '2026-03-10 17:13:34'),
(136, 200, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(137, 201, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(138, 202, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(139, 203, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(140, 204, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(141, 205, 1, 'recebido', '2026-03-11 02:30:21', '2026-03-11 02:30:21'),
(142, 206, 1, 'recebido', '2026-03-11 02:36:57', '2026-03-11 02:36:57'),
(143, 207, 1, 'recebido', '2026-03-11 02:36:57', '2026-03-11 02:36:57'),
(144, 208, 1, 'recebido', '2026-03-11 02:36:57', '2026-03-11 02:36:57'),
(145, 209, 1, 'recebido', '2026-03-11 02:36:57', '2026-03-11 02:36:57'),
(146, 210, 1, 'recebido', '2026-03-11 04:19:25', '2026-03-11 04:19:25'),
(147, 211, 1, 'recebido', '2026-03-11 04:19:25', '2026-03-11 04:19:25'),
(148, 212, 1, 'recebido', '2026-03-11 04:19:25', '2026-03-11 04:19:25'),
(149, 213, 1, 'recebido', '2026-03-11 04:19:25', '2026-03-11 04:19:25'),
(150, 214, 1, 'recebido', '2026-03-11 06:46:32', '2026-03-11 06:46:32'),
(151, 215, 1, 'recebido', '2026-03-11 06:46:32', '2026-03-11 06:46:32'),
(152, 216, 1, 'recebido', '2026-03-11 13:01:32', '2026-03-11 13:01:32'),
(153, 217, 1, 'recebido', '2026-03-11 13:01:32', '2026-03-11 13:01:32'),
(154, 218, 1, 'recebido', '2026-03-11 13:01:32', '2026-03-11 13:01:32'),
(155, 219, 1, 'recebido', '2026-03-11 14:58:33', '2026-03-11 14:58:33'),
(156, 220, 1, 'recebido', '2026-03-11 14:58:33', '2026-03-11 14:58:33'),
(157, 221, 1, 'recebido', '2026-03-11 14:58:33', '2026-03-11 14:58:33'),
(158, 222, 1, 'recebido', '2026-03-11 14:58:33', '2026-03-11 14:58:33'),
(159, 223, 1, 'recebido', '2026-03-12 01:12:40', '2026-03-12 01:12:40'),
(160, 224, 1, 'recebido', '2026-03-12 01:12:40', '2026-03-12 01:12:40'),
(161, 225, 1, 'recebido', '2026-03-12 23:12:41', '2026-03-12 23:12:41'),
(162, 226, 1, 'recebido', '2026-03-12 23:12:41', '2026-03-12 23:12:41'),
(163, 227, 1, 'recebido', '2026-03-12 23:12:41', '2026-03-12 23:12:41'),
(164, 228, 1, 'recebido', '2026-03-13 02:05:05', '2026-03-13 02:05:05'),
(165, 229, 1, 'recebido', '2026-03-13 02:05:05', '2026-03-13 02:05:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_empresas`
--

CREATE TABLE `tb_empresas` (
  `id` bigint NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_fantasia` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cnpj` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `endereco` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plano_id` bigint DEFAULT NULL,
  `status` enum('ativo','suspenso') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_empresas`
--

INSERT INTO `tb_empresas` (`id`, `nome`, `nome_fantasia`, `cnpj`, `telefone`, `email`, `endereco`, `cidade`, `estado`, `plano_id`, `status`, `criado_em`, `created_at`, `updated_at`) VALUES
(4, 'Restaurante Espetinho', 'Restaurante Espetinho', '03720882001200', '85992199146', 'ronaldodepaulasurf@yahoo.com.br', 'rua jose severino', 'Eusebio', 'Ceará', 1, 'ativo', '2026-03-05 04:06:12', '2026-03-05 07:06:12', '2026-03-05 04:14:02'),
(5, 'Pizzaria Estrela do Nordeste', 'Pizzaria Estrela do Nordeste', '03720882000149', '8531312821', 'admin@p.com', 'Av Cicero de Sá, 250', 'Eusebio', 'CE', 1, 'ativo', '2026-03-10 18:39:45', '2026-03-10 21:39:45', '2026-03-10 19:05:44'),
(6, 'Pastel da Praça', 'Pastel da Praça', '03720882000134', '8531313233', 'ronaldodepaulasurf@gmail.com', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:02:23', '2026-03-10 22:02:23', '2026-03-10 19:26:14'),
(7, 'Ze Esfiha', 'Ze Esfiha', '03720882000135', '(85) 98776-1553', 'ronaldodepaulasurf@yahoo.com.br', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:17:03', '2026-03-10 22:17:03', '2026-03-10 19:26:18'),
(8, 'Sabor da Tapioca', 'Sabor da Tapioca', '03720882000136', '(85) 98776-1553', 'ronaldodepaulasurf3@gmail.com', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:24:28', '2026-03-10 22:24:28', '2026-03-10 19:30:52'),
(9, 'Super Coxinha', 'Super Coxinha', '03720882000137', '(85) 98776-1553', 'ronaldodepaulasurf4@gmail.com', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:29:31', '2026-03-10 22:29:31', '2026-03-10 22:29:31'),
(10, 'Lanches Gerais', 'Lanches Gerais', '03720882000138', '(85) 98776-1553', 'ronaldodepaulasurf@yahoo.com.br', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:39:51', '2026-03-10 22:39:51', '2026-03-10 22:39:51'),
(11, 'Açaiteria Sabor da Praça', 'Açaiteria Sabor da Praça', '03720882000140', '(85) 98776-1553', 'ronaldodepaulasurf@yahoo.com.br', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:41:56', '2026-03-10 22:41:56', '2026-03-10 22:41:56'),
(12, 'Sorvetão Brasil', 'Sorvetão Brasil', '03720882000141', '(85) 98776-1553', 'ronaldodepaulasurf@yahoo.com.br', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:44:29', '2026-03-10 22:44:29', '2026-03-10 22:44:29'),
(13, 'Ronaldo de Paula', 'Ronaldo de Paula', '03720882000143', '(85) 98776-1553', 'ronaldodepaulasurf@yahoo.com.br', '250 Rua José Severino', 'Eusébio', 'Ceará', 1, 'ativo', '2026-03-10 19:52:36', '2026-03-10 22:52:36', '2026-03-10 22:52:36');

--
-- Acionadores `tb_empresas`
--
DELIMITER $$
CREATE TRIGGER `after_empresa_insert` AFTER INSERT ON `tb_empresas` FOR EACH ROW BEGIN
    -- Inserir na tabela tb_usuario_perfis para o usuário associado à empresa
    INSERT INTO tb_usuario_perfis (usuario_id, perfil_id)
    SELECT u.id, 2
    FROM tb_usuarios u
    WHERE u.empresa_id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_entregadores`
--

CREATE TABLE `tb_entregadores` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_entregas`
--

CREATE TABLE `tb_entregas` (
  `id` bigint NOT NULL,
  `pedido_id` bigint DEFAULT NULL,
  `entregador_id` bigint DEFAULT NULL,
  `taxa` decimal(10,2) DEFAULT NULL,
  `status` enum('pendente','saiu','entregue') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_estoque`
--

CREATE TABLE `tb_estoque` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `produto_id` bigint DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_estoque_movimentos`
--

CREATE TABLE `tb_estoque_movimentos` (
  `id` bigint NOT NULL,
  `produto_id` bigint DEFAULT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_formas_pagamento`
--

CREATE TABLE `tb_formas_pagamento` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_formas_pagamento`
--

INSERT INTO `tb_formas_pagamento` (`id`, `empresa_id`, `nome`, `created_at`, `updated_at`) VALUES
(1, 4, 'Cartão Credito', '2026-03-08 18:35:34', '2026-03-08 18:35:34'),
(2, 4, 'Cartão Debito', '2026-03-08 18:35:34', '2026-03-08 18:35:34'),
(3, 4, 'Pix', '2026-03-08 18:35:43', '2026-03-08 18:35:43'),
(4, 4, 'Dinheiro', '2026-03-08 18:35:54', '2026-03-08 18:35:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_mesas`
--

CREATE TABLE `tb_mesas` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `numero` int DEFAULT NULL,
  `status` enum('livre','ocupada') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_mesas`
--

INSERT INTO `tb_mesas` (`id`, `empresa_id`, `numero`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'ocupada', '2026-03-08 16:48:33', '2026-03-12 23:12:05'),
(2, 4, 2, 'livre', '2026-03-08 16:48:50', '2026-03-10 20:33:37'),
(3, 4, 3, 'ocupada', '2026-03-08 16:48:55', '2026-03-13 02:04:48'),
(4, 4, 4, 'livre', '2026-03-08 16:48:59', '2026-03-10 20:32:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_notificacoes`
--

CREATE TABLE `tb_notificacoes` (
  `id` bigint NOT NULL,
  `empresa_id` bigint NOT NULL,
  `pedido_id` bigint DEFAULT NULL,
  `mesa_id` bigint DEFAULT NULL,
  `comanda_id` bigint DEFAULT NULL,
  `cliente_id` bigint DEFAULT NULL,
  `usuario_id` bigint DEFAULT NULL,
  `estacao_id` bigint DEFAULT NULL,
  `destino` enum('cozinha','operacao','mesa','comanda') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operacao',
  `tipo` enum('pedido_status','item_status','mensagem') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pedido_status',
  `status` enum('pendente','enviada','lida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `prioridade` enum('baixa','normal','alta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `titulo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci,
  `payload` json DEFAULT NULL,
  `enviada_em` timestamp NULL DEFAULT NULL,
  `lida_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tb_notificacoes`
--

INSERT INTO `tb_notificacoes` (`id`, `empresa_id`, `pedido_id`, `mesa_id`, `comanda_id`, `cliente_id`, `usuario_id`, `estacao_id`, `destino`, `tipo`, `status`, `prioridade`, `titulo`, `mensagem`, `payload`, `enviada_em`, `lida_em`, `created_at`, `updated_at`) VALUES
(1, 4, 56, 1, NULL, NULL, 4, 1, 'operacao', 'pedido_status', 'lida', 'alta', 'Pedido concluído na cozinha', 'Pronto para ser retirado e servido na mesa.', NULL, '2026-03-09 18:02:57', '2026-03-09 22:01:25', '2026-03-09 18:02:57', '2026-03-09 22:01:25'),
(2, 4, 54, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 54 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-09 22:28:32', '2026-03-09 22:28:12', '2026-03-09 22:28:32'),
(3, 4, 57, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 57 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-09 22:41:02', '2026-03-09 22:36:20', '2026-03-09 22:41:02'),
(4, 4, 57, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 57 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-09 22:45:00', '2026-03-09 22:43:51', '2026-03-09 22:45:00'),
(5, 4, 57, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 57 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-09 22:58:59', '2026-03-09 22:58:31', '2026-03-09 22:58:59'),
(6, 4, 57, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 57 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-09 23:00:30', '2026-03-09 23:00:05', '2026-03-09 23:00:30'),
(7, 4, 58, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 58 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 03:47:43', '2026-03-10 03:47:38', '2026-03-10 03:47:43'),
(8, 4, 58, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 58 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 03:54:09', '2026-03-10 03:53:59', '2026-03-10 03:54:09'),
(9, 4, 58, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 58 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 03:57:55', '2026-03-10 03:54:31', '2026-03-10 03:57:55'),
(10, 4, 58, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 58 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 03:58:33', '2026-03-10 03:58:08', '2026-03-10 03:58:33'),
(11, 4, 59, 2, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 59 pronto para entrega na mesa 2', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 04:00:54', '2026-03-10 04:00:37', '2026-03-10 04:00:54'),
(12, 4, 60, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 60 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 04:42:12', '2026-03-10 04:05:19', '2026-03-10 04:42:12'),
(13, 4, 62, 2, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 62 pronto para entrega na mesa 2', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 05:06:24', '2026-03-10 05:05:44', '2026-03-10 05:06:24'),
(14, 4, 61, NULL, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 61 pronto', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 05:06:24', '2026-03-10 05:06:06', '2026-03-10 05:06:24'),
(15, 4, 63, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 63 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 05:52:50', '2026-03-10 05:50:56', '2026-03-10 05:52:50'),
(16, 4, 64, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 64 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 06:28:35', '2026-03-10 06:28:19', '2026-03-10 06:28:35'),
(17, 4, 65, 2, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 65 pronto para entrega na mesa 2', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 06:28:35', '2026-03-10 06:28:23', '2026-03-10 06:28:35'),
(18, 4, 66, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 66 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 06:28:35', '2026-03-10 06:28:27', '2026-03-10 06:28:35'),
(19, 4, 67, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 67 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 06:30:55', '2026-03-10 06:30:40', '2026-03-10 06:30:55'),
(20, 4, 68, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 68 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 06:32:32', '2026-03-10 06:32:13', '2026-03-10 06:32:32'),
(21, 4, 69, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 69 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 07:04:07', '2026-03-10 06:45:13', '2026-03-10 07:04:07'),
(22, 4, 68, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 68 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 07:04:07', '2026-03-10 06:45:18', '2026-03-10 07:04:07'),
(23, 4, 70, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 70 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 07:10:36', '2026-03-10 07:10:13', '2026-03-10 07:10:36'),
(24, 4, 71, 2, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 71 pronto para entrega na mesa 2', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 07:24:17', '2026-03-10 07:23:50', '2026-03-10 07:24:17'),
(25, 4, 72, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 72 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 12:08:16', '2026-03-10 12:07:58', '2026-03-10 12:08:16'),
(26, 4, 72, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 72 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 12:17:23', '2026-03-10 12:10:01', '2026-03-10 12:17:23'),
(27, 4, 76, 3, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 76 pronto para entrega na mesa 3', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 13:58:20', '2026-03-10 13:57:58', '2026-03-10 13:58:20'),
(28, 4, 74, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 74 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 14:57:52', '2026-03-10 14:56:30', '2026-03-10 14:57:52'),
(29, 4, 73, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 73 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 14:57:52', '2026-03-10 14:57:35', '2026-03-10 14:57:52'),
(30, 4, 79, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 79 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 15:00:32', '2026-03-10 15:00:11', '2026-03-10 15:00:32'),
(31, 4, 78, 2, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 78 pronto para entrega na mesa 2', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 15:57:28', '2026-03-10 15:47:30', '2026-03-10 15:57:28'),
(32, 4, 79, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 79 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 17:12:08', '2026-03-10 17:11:18', '2026-03-10 17:12:08'),
(33, 4, 77, 3, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 77 pronto para entrega na mesa 3', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 17:13:38', '2026-03-10 17:12:54', '2026-03-10 17:13:38'),
(34, 4, 75, 4, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 75 pronto para entrega na mesa 4', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-10 17:14:26', '2026-03-10 17:13:59', '2026-03-10 17:14:26'),
(35, 4, 81, 3, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 81 pronto para entrega na mesa 3', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 02:37:49', '2026-03-11 02:37:33', '2026-03-11 02:37:49'),
(36, 4, 80, 1, NULL, NULL, 15, NULL, 'operacao', 'pedido_status', 'pendente', 'normal', 'Pedido pronto', 'Pedido 80 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, NULL, '2026-03-11 02:38:07', '2026-03-11 02:38:07'),
(37, 4, 82, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 82 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 06:11:17', '2026-03-11 04:35:12', '2026-03-11 06:11:17'),
(38, 4, 81, 3, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 81 pronto para entrega na mesa 3', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 06:11:17', '2026-03-11 04:36:00', '2026-03-11 06:11:17'),
(39, 4, 83, NULL, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 83 pronto', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 13:02:20', '2026-03-11 06:47:52', '2026-03-11 13:02:20'),
(40, 4, 84, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 84 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 13:07:19', '2026-03-11 13:07:08', '2026-03-11 13:07:19'),
(41, 4, 84, 1, NULL, NULL, 4, NULL, 'operacao', 'pedido_status', 'lida', 'normal', 'Pedido pronto', 'Pedido 84 pronto para entrega na mesa 1', '{\"status_novo\": \"pronto\", \"status_anterior\": \"preparo\"}', NULL, '2026-03-11 14:56:59', '2026-03-11 13:08:10', '2026-03-11 14:56:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pagamentos`
--

CREATE TABLE `tb_pagamentos` (
  `id` bigint NOT NULL,
  `pedido_id` bigint DEFAULT NULL,
  `forma_pagamento_id` bigint DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `troco` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pagamentos`
--

INSERT INTO `tb_pagamentos` (`id`, `pedido_id`, `forma_pagamento_id`, `valor`, `troco`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 2.00, 0.00, '2026-03-08 22:02:07', '2026-03-08 22:02:07'),
(2, 4, 3, 5.00, 0.00, '2026-03-08 22:02:08', '2026-03-08 22:02:08'),
(3, 4, 4, 20.00, 0.00, '2026-03-08 22:02:08', '2026-03-08 22:02:08'),
(4, 12, 1, 35.40, 0.00, '2026-03-08 22:14:02', '2026-03-08 22:14:02'),
(5, 21, 1, 20.00, 0.00, '2026-03-08 23:02:19', '2026-03-08 23:02:19'),
(6, 21, 4, 50.00, 0.00, '2026-03-08 23:02:20', '2026-03-08 23:02:20'),
(7, 35, 4, 150.00, 0.00, '2026-03-09 06:34:40', '2026-03-09 06:34:40'),
(8, 34, 4, 150.00, 10.90, '2026-03-09 06:42:41', '2026-03-09 06:42:41'),
(9, 36, 2, 50.00, 0.00, '2026-03-09 07:22:03', '2026-03-09 07:22:03'),
(10, 36, 4, 50.00, 15.70, '2026-03-09 07:22:03', '2026-03-09 07:22:03'),
(11, 37, 3, 6.00, 0.00, '2026-03-09 07:22:58', '2026-03-09 07:22:58'),
(12, 33, 3, 35.50, 0.00, '2026-03-09 07:48:00', '2026-03-09 07:48:00'),
(13, 32, 4, 100.00, 1.90, '2026-03-09 07:53:36', '2026-03-09 07:53:36'),
(14, 32, 4, 100.00, 1.90, '2026-03-09 07:57:46', '2026-03-09 07:57:46'),
(15, 38, 4, 150.00, 32.10, '2026-03-09 08:05:52', '2026-03-09 08:05:52'),
(16, 39, 3, 50.00, 0.00, '2026-03-09 15:15:47', '2026-03-09 15:15:47'),
(17, 39, 4, 20.00, 1.40, '2026-03-09 15:15:47', '2026-03-09 15:15:47'),
(18, 40, 2, 50.00, 0.00, '2026-03-09 15:18:24', '2026-03-09 15:18:24'),
(19, 40, 4, 20.00, 9.20, '2026-03-09 15:18:24', '2026-03-09 15:18:24'),
(20, 41, 2, 43.30, 0.00, '2026-03-09 16:08:58', '2026-03-09 16:08:58'),
(21, 42, 2, 53.00, 0.00, '2026-03-09 16:10:08', '2026-03-09 16:10:08'),
(22, 43, 2, 59.00, 0.00, '2026-03-09 16:11:15', '2026-03-09 16:11:15'),
(23, 44, 3, 15.60, 0.00, '2026-03-09 16:12:52', '2026-03-09 16:12:52'),
(24, 48, 2, 6.00, 0.00, '2026-03-09 16:14:34', '2026-03-09 16:14:34'),
(25, 49, 2, 13.80, 0.00, '2026-03-09 16:30:24', '2026-03-09 16:30:24'),
(26, 50, 2, 7.80, 0.00, '2026-03-09 16:31:12', '2026-03-09 16:31:12'),
(27, 50, 2, 7.80, 0.00, '2026-03-09 16:33:14', '2026-03-09 16:33:14'),
(28, 51, 1, 37.30, 0.00, '2026-03-09 16:45:51', '2026-03-09 16:45:51'),
(29, 52, 1, 13.80, 0.00, '2026-03-09 19:29:53', '2026-03-09 19:29:53'),
(30, 53, 3, 37.30, 0.00, '2026-03-09 20:14:12', '2026-03-09 20:14:12'),
(31, 56, 2, 6.00, 0.00, '2026-03-09 22:02:57', '2026-03-09 22:02:57'),
(32, 55, 4, 6.00, 0.00, '2026-03-09 22:03:43', '2026-03-09 22:03:43'),
(33, 54, 4, 27.60, 0.00, '2026-03-09 22:30:01', '2026-03-09 22:30:01'),
(34, 57, 1, 37.30, 0.00, '2026-03-09 23:01:02', '2026-03-09 23:01:02'),
(35, 58, 2, 13.80, 0.00, '2026-03-10 03:59:03', '2026-03-10 03:59:03'),
(36, 59, 3, 60.80, 0.00, '2026-03-10 04:01:43', '2026-03-10 04:01:43'),
(37, 59, 4, 20.00, 20.00, '2026-03-10 04:01:43', '2026-03-10 04:01:43'),
(38, 60, 1, 47.00, 0.00, '2026-03-10 05:07:47', '2026-03-10 05:07:47'),
(39, 62, 3, 37.30, 0.00, '2026-03-10 05:08:02', '2026-03-10 05:08:02'),
(40, 61, 3, 27.60, 0.00, '2026-03-10 05:09:57', '2026-03-10 05:09:57'),
(41, 63, 3, 13.80, 0.00, '2026-03-10 05:51:32', '2026-03-10 05:51:32'),
(42, 63, 4, 13.80, 0.00, '2026-03-10 05:53:38', '2026-03-10 05:53:38'),
(43, 66, 2, 47.00, 0.00, '2026-03-10 06:29:00', '2026-03-10 06:29:00'),
(44, 65, 3, 47.00, 0.00, '2026-03-10 06:29:17', '2026-03-10 06:29:17'),
(45, 64, 3, 37.30, 0.00, '2026-03-10 06:30:07', '2026-03-10 06:30:07'),
(46, 67, 4, 13.80, 0.00, '2026-03-10 06:31:07', '2026-03-10 06:31:07'),
(47, 68, 2, 23.50, 0.00, '2026-03-10 06:44:35', '2026-03-10 06:44:35'),
(48, 69, 1, 109.60, 0.00, '2026-03-10 06:45:47', '2026-03-10 06:45:47'),
(49, 68, 2, 23.50, 0.00, '2026-03-10 06:46:05', '2026-03-10 06:46:05'),
(50, 70, 2, 43.00, 0.00, '2026-03-10 07:11:18', '2026-03-10 07:11:18'),
(51, 71, 1, 50.00, 0.00, '2026-03-10 07:24:59', '2026-03-10 07:24:59'),
(52, 71, 4, 50.00, 28.00, '2026-03-10 07:24:59', '2026-03-10 07:24:59'),
(53, 72, 2, 50.00, 0.00, '2026-03-10 12:23:04', '2026-03-10 12:23:04'),
(54, 72, 3, 20.00, 0.00, '2026-03-10 12:23:04', '2026-03-10 12:23:04'),
(55, 72, 4, 100.00, 13.20, '2026-03-10 12:23:04', '2026-03-10 12:23:04'),
(56, 76, 3, 52.00, 0.00, '2026-03-10 13:58:56', '2026-03-10 13:58:56'),
(57, 74, 1, 243.00, 0.00, '2026-03-10 14:57:06', '2026-03-10 14:57:06'),
(58, 74, 1, 243.00, 0.00, '2026-03-10 14:57:10', '2026-03-10 14:57:10'),
(59, 73, 2, 162.30, 0.00, '2026-03-10 14:58:33', '2026-03-10 14:58:33'),
(60, 75, 3, 47.00, 0.00, '2026-03-10 20:31:27', '2026-03-10 20:31:27'),
(61, 79, 4, 306.00, 0.00, '2026-03-10 20:32:26', '2026-03-10 20:32:26'),
(62, 77, 3, 88.10, 0.00, '2026-03-10 20:32:42', '2026-03-10 20:32:42'),
(63, 78, 4, 58.00, 0.00, '2026-03-10 20:33:37', '2026-03-10 20:33:37'),
(64, 82, 2, 111.00, 0.00, '2026-03-11 04:36:53', '2026-03-11 04:36:53'),
(65, 81, 1, 27.00, 0.00, '2026-03-11 04:37:09', '2026-03-11 04:37:09'),
(66, 80, 2, 136.00, 0.00, '2026-03-11 04:40:39', '2026-03-11 04:40:39'),
(67, 84, 1, 50.00, 0.00, '2026-03-11 13:14:23', '2026-03-11 13:14:23'),
(68, 84, 4, 50.00, 21.00, '2026-03-11 13:14:24', '2026-03-11 13:14:24'),
(69, 83, 2, 25.00, 0.00, '2026-03-12 01:14:29', '2026-03-12 01:14:29'),
(70, 83, 4, 100.00, 45.00, '2026-03-12 01:14:30', '2026-03-12 01:14:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedidos`
--

CREATE TABLE `tb_pedidos` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `usuario_id` bigint DEFAULT NULL,
  `mesa_id` bigint DEFAULT NULL,
  `comanda_id` bigint DEFAULT NULL,
  `cliente_id` bigint DEFAULT NULL,
  `tipo` enum('balcao','mesa','comanda','delivery','auto') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aberto','preparo','pronto','entregue','fechado') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pedidos`
--

INSERT INTO `tb_pedidos` (`id`, `empresa_id`, `usuario_id`, `mesa_id`, `comanda_id`, `cliente_id`, `tipo`, `status`, `total`, `criado_em`, `created_at`, `update_at`, `updated_at`) VALUES
(32, 4, 4, 1, 33, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 02:52:13', '2026-03-09 05:52:13', '2026-03-09 04:53:36', '2026-03-09 07:53:36'),
(33, 4, 4, 1, 34, NULL, 'mesa', 'fechado', 35.50, '2026-03-09 02:55:40', '2026-03-09 05:55:40', '2026-03-09 04:48:00', '2026-03-09 07:48:00'),
(34, 4, 4, 1, 35, NULL, 'mesa', 'fechado', 68.60, '2026-03-09 03:20:14', '2026-03-09 06:20:14', '2026-03-09 03:42:41', '2026-03-09 06:42:41'),
(35, 4, 4, 1, 36, NULL, 'mesa', 'fechado', 51.10, '2026-03-09 03:21:14', '2026-03-09 06:21:14', '2026-03-09 03:34:40', '2026-03-09 06:34:40'),
(36, 4, 4, 4, 37, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 04:18:12', '2026-03-09 07:18:12', '2026-03-09 04:22:03', '2026-03-09 07:22:03'),
(37, 4, 4, 1, 38, NULL, 'mesa', 'fechado', 6.00, '2026-03-09 04:22:17', '2026-03-09 07:22:17', '2026-03-09 04:22:58', '2026-03-09 07:22:58'),
(38, 4, 4, 1, 39, NULL, 'mesa', 'fechado', 51.10, '2026-03-09 04:41:06', '2026-03-09 07:41:06', '2026-03-09 05:05:52', '2026-03-09 08:05:52'),
(39, 4, 4, 1, 40, NULL, 'mesa', 'fechado', 45.10, '2026-03-09 12:12:27', '2026-03-09 15:12:27', '2026-03-09 12:15:47', '2026-03-09 15:15:47'),
(40, 4, 4, 4, 41, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 12:16:22', '2026-03-09 15:16:22', '2026-03-09 12:18:25', '2026-03-09 15:18:25'),
(41, 4, 4, 1, 42, NULL, 'mesa', 'fechado', 43.30, '2026-03-09 12:52:01', '2026-03-09 15:52:01', '2026-03-09 13:09:00', '2026-03-09 16:09:00'),
(42, 4, 4, 2, 43, NULL, 'mesa', 'fechado', 53.00, '2026-03-09 12:52:53', '2026-03-09 15:52:53', '2026-03-09 13:10:09', '2026-03-09 16:10:09'),
(43, 4, 4, 3, 44, NULL, 'mesa', 'fechado', 35.50, '2026-03-09 13:00:26', '2026-03-09 16:00:25', '2026-03-09 13:11:16', '2026-03-09 16:11:16'),
(44, 4, 4, 4, 45, NULL, 'mesa', 'fechado', 7.80, '2026-03-09 13:06:29', '2026-03-09 16:06:29', '2026-03-09 13:12:54', '2026-03-09 16:12:54'),
(48, 4, 4, 4, 49, NULL, 'mesa', 'fechado', 6.00, '2026-03-09 13:13:54', '2026-03-09 16:13:54', '2026-03-09 13:14:35', '2026-03-09 16:14:35'),
(49, 4, 4, NULL, 52, NULL, 'mesa', 'fechado', 13.80, '2026-03-09 13:29:22', '2026-03-09 16:29:22', '2026-03-09 13:30:26', '2026-03-09 16:30:26'),
(50, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 7.80, '2026-03-09 13:30:35', '2026-03-09 16:30:35', '2026-03-09 13:31:13', '2026-03-09 16:31:13'),
(51, 4, 4, NULL, 53, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 13:39:05', '2026-03-09 16:39:05', '2026-03-09 13:45:53', '2026-03-09 16:45:52'),
(52, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 13.80, '2026-03-09 16:22:11', '2026-03-09 19:22:11', '2026-03-09 16:29:54', '2026-03-09 19:29:54'),
(53, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 16:31:15', '2026-03-09 19:31:14', '2026-03-09 17:14:13', '2026-03-09 20:14:13'),
(54, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 13.80, '2026-03-09 17:15:24', '2026-03-09 20:15:24', '2026-03-09 19:30:02', '2026-03-09 22:30:02'),
(55, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 6.00, '2026-03-09 17:15:24', '2026-03-09 20:15:24', '2026-03-09 19:03:48', '2026-03-09 22:03:48'),
(56, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 6.00, '2026-03-09 17:15:29', '2026-03-09 20:15:29', '2026-03-09 19:02:59', '2026-03-09 22:02:59'),
(57, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 37.30, '2026-03-09 19:35:15', '2026-03-09 22:35:15', '2026-03-09 20:01:02', '2026-03-09 23:01:02'),
(58, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 13.80, '2026-03-10 00:45:33', '2026-03-10 03:45:33', '2026-03-10 00:59:03', '2026-03-10 03:59:03'),
(59, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 37.30, '2026-03-10 00:59:10', '2026-03-10 03:59:10', '2026-03-10 01:01:43', '2026-03-10 04:01:43'),
(60, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 23.50, '2026-03-10 01:03:27', '2026-03-10 04:03:27', '2026-03-10 02:07:48', '2026-03-10 05:07:48'),
(61, 4, 4, NULL, 54, NULL, 'mesa', 'fechado', 19.80, '2026-03-10 01:05:54', '2026-03-10 04:05:54', '2026-03-10 02:09:58', '2026-03-10 05:09:58'),
(62, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 37.30, '2026-03-10 02:03:30', '2026-03-10 05:03:30', '2026-03-10 02:08:02', '2026-03-10 05:08:02'),
(63, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 13.80, '2026-03-10 02:36:01', '2026-03-10 05:36:01', '2026-03-10 02:51:32', '2026-03-10 05:51:32'),
(64, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 37.30, '2026-03-10 02:59:38', '2026-03-10 05:59:38', '2026-03-10 03:30:07', '2026-03-10 06:30:07'),
(65, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 23.50, '2026-03-10 03:06:07', '2026-03-10 06:06:07', '2026-03-10 03:29:18', '2026-03-10 06:29:18'),
(66, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 23.50, '2026-03-10 03:27:00', '2026-03-10 06:27:00', '2026-03-10 03:29:00', '2026-03-10 06:29:00'),
(67, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 13.80, '2026-03-10 03:29:25', '2026-03-10 06:29:25', '2026-03-10 03:31:08', '2026-03-10 06:31:08'),
(68, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 23.50, '2026-03-10 03:31:17', '2026-03-10 06:31:17', '2026-03-10 03:46:05', '2026-03-10 06:46:05'),
(69, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 39.10, '2026-03-10 03:32:54', '2026-03-10 06:32:54', '2026-03-10 03:45:47', '2026-03-10 06:45:47'),
(70, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 43.00, '2026-03-10 03:59:30', '2026-03-10 06:59:30', '2026-03-10 04:11:18', '2026-03-10 07:11:18'),
(71, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 72.00, '2026-03-10 04:11:23', '2026-03-10 07:11:23', '2026-03-10 04:24:59', '2026-03-10 07:24:59'),
(72, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 156.80, '2026-03-10 09:03:59', '2026-03-10 12:03:59', '2026-03-10 09:23:04', '2026-03-10 12:23:04'),
(73, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 162.30, '2026-03-10 09:23:25', '2026-03-10 12:23:25', '2026-03-10 11:58:33', '2026-03-10 14:58:33'),
(74, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 308.00, '2026-03-10 09:26:30', '2026-03-10 12:26:30', '2026-03-10 11:57:10', '2026-03-10 14:57:10'),
(75, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 47.00, '2026-03-10 09:28:10', '2026-03-10 12:28:10', '2026-03-10 17:31:27', '2026-03-10 20:31:27'),
(76, 4, 4, 3, NULL, NULL, 'mesa', 'fechado', 52.00, '2026-03-10 10:56:42', '2026-03-10 13:56:42', '2026-03-10 10:58:56', '2026-03-10 13:58:56'),
(77, 4, 4, 3, NULL, NULL, 'mesa', 'fechado', 88.10, '2026-03-10 10:56:42', '2026-03-10 13:56:42', '2026-03-10 17:32:42', '2026-03-10 20:32:42'),
(78, 4, 4, 2, NULL, NULL, 'mesa', 'fechado', 58.00, '2026-03-10 11:42:29', '2026-03-10 14:42:29', '2026-03-10 17:33:37', '2026-03-10 20:33:37'),
(79, 4, 4, 4, NULL, NULL, 'mesa', 'fechado', 306.00, '2026-03-10 11:57:20', '2026-03-10 14:57:20', '2026-03-10 17:32:26', '2026-03-10 20:32:26'),
(80, 4, 15, 1, NULL, NULL, 'mesa', 'fechado', 136.00, '2026-03-10 23:29:25', '2026-03-11 02:29:25', '2026-03-11 01:40:39', '2026-03-11 04:40:39'),
(81, 4, 4, 3, NULL, NULL, 'mesa', 'fechado', 27.00, '2026-03-10 23:36:10', '2026-03-11 02:36:10', '2026-03-11 01:37:10', '2026-03-11 04:37:10'),
(82, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 97.00, '2026-03-11 01:18:52', '2026-03-11 04:18:52', '2026-03-11 01:36:53', '2026-03-11 04:36:53'),
(83, 4, 4, NULL, 55, NULL, 'mesa', 'fechado', 65.00, '2026-03-11 03:45:59', '2026-03-11 06:45:59', '2026-03-11 22:14:30', '2026-03-12 01:14:30'),
(84, 4, 4, 1, NULL, NULL, 'mesa', 'fechado', 57.00, '2026-03-11 10:00:28', '2026-03-11 13:00:28', '2026-03-11 10:14:24', '2026-03-11 13:14:24'),
(85, 4, 4, NULL, 56, NULL, 'mesa', 'preparo', 17.50, '2026-03-11 11:57:23', '2026-03-11 14:57:23', '2026-03-11 11:58:33', '2026-03-11 14:58:33'),
(86, 4, 4, NULL, 57, NULL, 'mesa', 'preparo', 37.00, '2026-03-11 22:12:05', '2026-03-12 01:12:05', '2026-03-11 22:12:40', '2026-03-12 01:12:40'),
(87, 4, 2, NULL, NULL, 1, 'delivery', 'aberto', 0.00, '2026-03-12 09:19:41', '2026-03-12 12:19:41', '2026-03-12 09:19:41', '2026-03-12 12:19:41'),
(88, 4, 4, 1, NULL, NULL, 'mesa', 'preparo', 60.00, '2026-03-12 20:12:05', '2026-03-12 23:12:05', '2026-03-12 20:12:41', '2026-03-12 23:12:41'),
(89, 4, 4, 3, NULL, NULL, 'mesa', 'preparo', 37.00, '2026-03-12 23:04:48', '2026-03-13 02:04:48', '2026-03-12 23:05:05', '2026-03-13 02:05:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedido_item_opcoes`
--

CREATE TABLE `tb_pedido_item_opcoes` (
  `id` bigint NOT NULL,
  `pedido_item_id` bigint DEFAULT NULL,
  `opcao_item_id` bigint DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedido_itens`
--

CREATE TABLE `tb_pedido_itens` (
  `id` bigint NOT NULL,
  `pedido_id` bigint DEFAULT NULL,
  `produto_id` bigint DEFAULT NULL,
  `quantidade` int DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `observacao` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pedido_itens`
--

INSERT INTO `tb_pedido_itens` (`id`, `pedido_id`, `produto_id`, `quantidade`, `preco`, `observacao`, `created_at`, `updated_at`) VALUES
(45, 32, 1, 2, 6.00, NULL, '2026-03-09 05:52:19', '2026-03-09 07:46:31'),
(46, 32, 2, 2, 7.80, NULL, '2026-03-09 05:52:22', '2026-03-09 07:46:38'),
(47, 32, 3, 3, 23.50, NULL, '2026-03-09 05:52:26', '2026-03-09 07:46:42'),
(48, 34, 1, 1, 6.00, NULL, '2026-03-09 06:21:00', '2026-03-09 06:21:00'),
(49, 34, 2, 2, 7.80, NULL, '2026-03-09 06:21:01', '2026-03-09 06:21:02'),
(50, 34, 3, 4, 23.50, NULL, '2026-03-09 06:21:05', '2026-03-09 06:42:20'),
(51, 34, 3, 1, 23.50, NULL, '2026-03-09 06:21:05', '2026-03-09 06:21:05'),
(52, 35, 1, 3, 6.00, NULL, '2026-03-09 06:21:26', '2026-03-09 06:22:02'),
(53, 35, 2, 2, 7.80, NULL, '2026-03-09 06:21:29', '2026-03-09 06:21:31'),
(54, 35, 3, 3, 23.50, NULL, '2026-03-09 06:21:35', '2026-03-09 06:21:59'),
(55, 33, 1, 1, 6.00, NULL, '2026-03-09 06:23:06', '2026-03-09 06:23:06'),
(56, 33, 1, 1, 6.00, NULL, '2026-03-09 06:23:07', '2026-03-09 06:23:07'),
(57, 33, 3, 1, 23.50, NULL, '2026-03-09 06:23:12', '2026-03-09 06:23:12'),
(58, 36, 1, 1, 6.00, NULL, '2026-03-09 07:18:24', '2026-03-09 07:18:24'),
(59, 36, 2, 1, 7.80, NULL, '2026-03-09 07:18:27', '2026-03-09 07:18:27'),
(60, 36, 3, 3, 23.50, NULL, '2026-03-09 07:18:32', '2026-03-09 07:18:35'),
(61, 37, 1, 1, 6.00, NULL, '2026-03-09 07:22:44', '2026-03-09 07:22:44'),
(62, 38, 1, 4, 6.00, NULL, '2026-03-09 07:50:19', '2026-03-09 07:58:30'),
(63, 38, 2, 3, 7.80, NULL, '2026-03-09 07:50:24', '2026-03-09 07:58:31'),
(64, 38, 3, 3, 23.50, NULL, '2026-03-09 07:50:30', '2026-03-09 07:58:36'),
(65, 39, 1, 1, 6.00, NULL, '2026-03-09 15:12:32', '2026-03-09 15:12:32'),
(66, 39, 2, 2, 7.80, NULL, '2026-03-09 15:12:35', '2026-03-09 15:12:38'),
(67, 39, 3, 2, 23.50, NULL, '2026-03-09 15:12:42', '2026-03-09 15:12:44'),
(68, 40, 1, 1, 6.00, NULL, '2026-03-09 15:16:32', '2026-03-09 15:16:32'),
(69, 40, 2, 1, 7.80, NULL, '2026-03-09 15:16:34', '2026-03-09 15:16:34'),
(70, 40, 3, 2, 23.50, NULL, '2026-03-09 15:16:38', '2026-03-09 15:16:57'),
(71, 41, 1, 2, 6.00, NULL, '2026-03-09 15:52:09', '2026-03-09 15:52:24'),
(72, 41, 2, 1, 7.80, NULL, '2026-03-09 15:52:16', '2026-03-09 15:52:16'),
(73, 41, 3, 1, 23.50, NULL, '2026-03-09 15:52:32', '2026-03-09 15:52:32'),
(74, 42, 3, 2, 23.50, NULL, '2026-03-09 15:53:05', '2026-03-09 15:53:10'),
(75, 42, 1, 1, 6.00, NULL, '2026-03-09 16:00:05', '2026-03-09 16:00:05'),
(76, 43, 1, 2, 6.00, NULL, '2026-03-09 16:00:37', '2026-03-09 16:00:42'),
(78, 43, 3, 2, 23.50, NULL, '2026-03-09 16:01:14', '2026-03-09 16:01:20'),
(79, 44, 2, 2, 7.80, NULL, '2026-03-09 16:06:39', '2026-03-09 16:06:46'),
(80, 48, 1, 1, 6.00, NULL, '2026-03-09 16:14:08', '2026-03-09 16:14:08'),
(81, 49, 1, 1, 6.00, NULL, '2026-03-09 16:29:34', '2026-03-09 16:29:34'),
(82, 49, 2, 1, 7.80, NULL, '2026-03-09 16:29:36', '2026-03-09 16:29:36'),
(83, 50, 2, 1, 7.80, NULL, '2026-03-09 16:30:48', '2026-03-09 16:30:48'),
(84, 51, 1, 1, 6.00, NULL, '2026-03-09 16:39:15', '2026-03-09 16:39:15'),
(85, 51, 2, 1, 7.80, NULL, '2026-03-09 16:39:21', '2026-03-09 16:39:21'),
(86, 51, 3, 1, 23.50, NULL, '2026-03-09 16:39:38', '2026-03-09 16:39:38'),
(87, 52, 1, 1, 6.00, NULL, '2026-03-09 19:22:24', '2026-03-09 19:22:24'),
(88, 52, 2, 1, 7.80, NULL, '2026-03-09 19:22:31', '2026-03-09 19:22:31'),
(89, 53, 1, 1, 6.00, NULL, '2026-03-09 19:31:23', '2026-03-09 19:31:23'),
(90, 53, 2, 1, 7.80, NULL, '2026-03-09 19:31:26', '2026-03-09 19:31:26'),
(91, 53, 3, 1, 23.50, NULL, '2026-03-09 19:31:35', '2026-03-09 19:31:35'),
(92, 56, 1, 1, 6.00, NULL, '2026-03-09 20:15:34', '2026-03-09 20:15:34'),
(93, 55, 1, 1, 6.00, NULL, '2026-03-09 21:00:31', '2026-03-09 21:00:31'),
(94, 54, 1, 2, 6.00, NULL, '2026-03-09 22:04:20', '2026-03-09 22:04:39'),
(95, 54, 2, 2, 7.80, NULL, '2026-03-09 22:04:28', '2026-03-09 22:04:44'),
(96, 57, 1, 1, 6.00, NULL, '2026-03-09 22:35:24', '2026-03-09 22:35:24'),
(97, 57, 2, 1, 7.80, NULL, '2026-03-09 22:35:28', '2026-03-09 22:35:28'),
(98, 57, 3, 1, 23.50, NULL, '2026-03-09 22:35:41', '2026-03-09 22:35:41'),
(99, 58, 1, 1, 6.00, NULL, '2026-03-10 03:45:46', '2026-03-10 03:45:46'),
(100, 58, 2, 1, 7.80, NULL, '2026-03-10 03:45:50', '2026-03-10 03:45:50'),
(101, 59, 1, 1, 6.00, NULL, '2026-03-10 03:59:15', '2026-03-10 03:59:15'),
(102, 59, 2, 1, 7.80, NULL, '2026-03-10 03:59:16', '2026-03-10 03:59:16'),
(103, 59, 3, 2, 23.50, NULL, '2026-03-10 03:59:22', '2026-03-10 03:59:32'),
(104, 60, 3, 2, 23.50, NULL, '2026-03-10 04:03:31', '2026-03-10 04:03:32'),
(105, 61, 1, 2, 6.00, NULL, '2026-03-10 04:05:57', '2026-03-10 04:05:58'),
(106, 61, 2, 2, 7.80, NULL, '2026-03-10 04:06:00', '2026-03-10 04:06:01'),
(107, 62, 1, 1, 6.00, NULL, '2026-03-10 05:03:34', '2026-03-10 05:03:34'),
(108, 62, 2, 1, 7.80, NULL, '2026-03-10 05:03:35', '2026-03-10 05:03:35'),
(109, 62, 3, 1, 23.50, NULL, '2026-03-10 05:03:39', '2026-03-10 05:03:39'),
(110, 63, 1, 1, 6.00, NULL, '2026-03-10 05:36:06', '2026-03-10 05:36:06'),
(111, 63, 2, 1, 7.80, NULL, '2026-03-10 05:36:06', '2026-03-10 05:36:06'),
(112, 64, 1, 1, 6.00, NULL, '2026-03-10 05:59:43', '2026-03-10 05:59:43'),
(113, 64, 2, 1, 7.80, NULL, '2026-03-10 05:59:44', '2026-03-10 05:59:44'),
(114, 64, 3, 1, 23.50, NULL, '2026-03-10 06:05:33', '2026-03-10 06:05:33'),
(115, 65, 3, 2, 23.50, NULL, '2026-03-10 06:06:12', '2026-03-10 06:06:12'),
(116, 66, 3, 2, 23.50, NULL, '2026-03-10 06:27:25', '2026-03-10 06:27:26'),
(117, 67, 1, 1, 6.00, NULL, '2026-03-10 06:30:20', '2026-03-10 06:30:20'),
(118, 67, 2, 1, 7.80, NULL, '2026-03-10 06:30:21', '2026-03-10 06:30:21'),
(119, 68, 3, 1, 23.50, NULL, '2026-03-10 06:31:21', '2026-03-10 06:31:21'),
(120, 69, 2, 2, 7.80, NULL, '2026-03-10 06:36:16', '2026-03-10 06:36:20'),
(121, 69, 3, 4, 23.50, NULL, '2026-03-10 06:38:58', '2026-03-10 06:43:58'),
(122, 70, 87, 1, 6.00, NULL, '2026-03-10 07:03:07', '2026-03-10 07:03:07'),
(123, 70, 85, 1, 6.00, NULL, '2026-03-10 07:03:12', '2026-03-10 07:03:12'),
(124, 70, 102, 1, 2.50, NULL, '2026-03-10 07:03:18', '2026-03-10 07:03:18'),
(125, 70, 106, 1, 5.00, NULL, '2026-03-10 07:03:19', '2026-03-10 07:03:19'),
(126, 70, 104, 1, 3.00, NULL, '2026-03-10 07:03:22', '2026-03-10 07:03:22'),
(127, 70, 54, 1, 10.00, NULL, '2026-03-10 07:03:29', '2026-03-10 07:03:29'),
(128, 70, 53, 1, 8.00, NULL, '2026-03-10 07:03:31', '2026-03-10 07:03:31'),
(129, 70, 105, 1, 2.50, NULL, '2026-03-10 07:08:59', '2026-03-10 07:08:59'),
(130, 71, 13, 1, 25.00, NULL, '2026-03-10 07:16:10', '2026-03-10 07:16:10'),
(131, 71, 4, 1, 12.00, NULL, '2026-03-10 07:16:11', '2026-03-10 07:16:11'),
(132, 71, 6, 1, 15.00, NULL, '2026-03-10 07:16:13', '2026-03-10 07:16:13'),
(133, 71, 5, 1, 10.00, NULL, '2026-03-10 07:16:15', '2026-03-10 07:16:15'),
(134, 71, 7, 1, 10.00, NULL, '2026-03-10 07:16:17', '2026-03-10 07:16:17'),
(135, 72, 1, 1, 6.00, NULL, '2026-03-10 12:04:03', '2026-03-10 12:04:03'),
(136, 72, 2, 1, 7.80, NULL, '2026-03-10 12:04:04', '2026-03-10 12:04:04'),
(137, 72, 13, 2, 25.00, NULL, '2026-03-10 12:04:07', '2026-03-10 12:04:08'),
(138, 72, 6, 1, 15.00, NULL, '2026-03-10 12:04:09', '2026-03-10 12:04:09'),
(139, 72, 5, 1, 10.00, NULL, '2026-03-10 12:04:10', '2026-03-10 12:04:10'),
(140, 72, 12, 2, 8.00, NULL, '2026-03-10 12:04:11', '2026-03-10 12:06:45'),
(141, 72, 12, 1, 8.00, NULL, '2026-03-10 12:04:12', '2026-03-10 12:04:12'),
(142, 72, 7, 1, 10.00, NULL, '2026-03-10 12:06:43', '2026-03-10 12:06:43'),
(143, 72, 22, 1, 14.00, NULL, '2026-03-10 12:08:57', '2026-03-10 12:08:57'),
(144, 72, 23, 1, 20.00, NULL, '2026-03-10 12:09:05', '2026-03-10 12:09:05'),
(145, 73, 1, 1, 6.00, NULL, '2026-03-10 12:23:30', '2026-03-10 12:23:30'),
(146, 73, 2, 1, 7.80, NULL, '2026-03-10 12:23:30', '2026-03-10 12:23:30'),
(147, 73, 3, 1, 23.50, NULL, '2026-03-10 12:23:36', '2026-03-10 12:23:36'),
(148, 73, 13, 1, 25.00, NULL, '2026-03-10 12:23:39', '2026-03-10 12:23:39'),
(149, 73, 13, 1, 25.00, NULL, '2026-03-10 12:23:40', '2026-03-10 12:23:40'),
(150, 73, 11, 1, 22.00, NULL, '2026-03-10 12:23:43', '2026-03-10 12:23:43'),
(151, 73, 11, 1, 22.00, NULL, '2026-03-10 12:23:43', '2026-03-10 12:23:43'),
(152, 73, 10, 1, 16.00, NULL, '2026-03-10 12:23:45', '2026-03-10 12:23:45'),
(153, 73, 33, 1, 7.00, NULL, '2026-03-10 12:24:04', '2026-03-10 12:24:04'),
(154, 73, 37, 1, 8.00, NULL, '2026-03-10 12:24:06', '2026-03-10 12:24:06'),
(155, 74, 40, 1, 6.00, NULL, '2026-03-10 12:27:05', '2026-03-10 12:27:05'),
(156, 74, 41, 1, 8.00, NULL, '2026-03-10 12:27:08', '2026-03-10 12:27:08'),
(157, 74, 51, 1, 6.00, NULL, '2026-03-10 12:27:11', '2026-03-10 12:27:11'),
(158, 74, 58, 1, 9.00, NULL, '2026-03-10 12:27:13', '2026-03-10 12:27:13'),
(159, 74, 65, 1, 16.00, NULL, '2026-03-10 12:27:16', '2026-03-10 12:27:16'),
(160, 74, 65, 1, 16.00, NULL, '2026-03-10 12:27:16', '2026-03-10 12:27:16'),
(161, 74, 76, 1, 12.00, NULL, '2026-03-10 12:27:22', '2026-03-10 12:27:22'),
(162, 74, 81, 1, 12.00, NULL, '2026-03-10 12:27:23', '2026-03-10 12:27:23'),
(163, 74, 93, 1, 8.00, NULL, '2026-03-10 12:27:27', '2026-03-10 12:27:27'),
(164, 74, 90, 1, 7.00, NULL, '2026-03-10 12:27:27', '2026-03-10 12:27:27'),
(165, 74, 86, 1, 6.00, NULL, '2026-03-10 12:27:43', '2026-03-10 12:27:43'),
(166, 74, 85, 1, 6.00, NULL, '2026-03-10 12:27:47', '2026-03-10 12:27:47'),
(167, 74, 109, 2, 4.00, NULL, '2026-03-10 12:27:53', '2026-03-10 12:27:55'),
(168, 74, 103, 1, 3.00, NULL, '2026-03-10 12:27:55', '2026-03-10 12:27:55'),
(169, 74, 95, 1, 120.00, NULL, '2026-03-10 12:28:05', '2026-03-10 12:28:05'),
(170, 74, 94, 1, 65.00, NULL, '2026-03-10 12:28:06', '2026-03-10 12:28:06'),
(171, 76, 4, 1, 12.00, NULL, '2026-03-10 13:56:47', '2026-03-10 13:56:47'),
(172, 76, 5, 1, 10.00, NULL, '2026-03-10 13:56:50', '2026-03-10 13:56:50'),
(173, 76, 9, 1, 18.00, NULL, '2026-03-10 13:56:55', '2026-03-10 13:56:55'),
(174, 76, 8, 1, 12.00, NULL, '2026-03-10 13:56:58', '2026-03-10 13:56:58'),
(175, 79, 17, 4, 20.00, NULL, '2026-03-10 14:58:59', '2026-03-10 16:31:49'),
(176, 79, 18, 3, 22.00, NULL, '2026-03-10 14:59:03', '2026-03-10 16:31:51'),
(177, 79, 1, 1, 6.00, NULL, '2026-03-10 14:59:12', '2026-03-10 14:59:12'),
(178, 78, 17, 1, 20.00, NULL, '2026-03-10 15:47:03', '2026-03-10 15:47:03'),
(179, 78, 18, 1, 22.00, NULL, '2026-03-10 15:47:05', '2026-03-10 15:47:05'),
(180, 77, 1, 2, 6.00, NULL, '2026-03-10 16:00:43', '2026-03-10 16:00:47'),
(181, 77, 2, 2, 7.80, NULL, '2026-03-10 16:00:44', '2026-03-10 16:00:45'),
(182, 77, 3, 1, 23.50, NULL, '2026-03-10 16:00:49', '2026-03-10 16:00:49'),
(183, 77, 13, 1, 25.00, NULL, '2026-03-10 16:00:52', '2026-03-10 16:00:52'),
(184, 77, 4, 1, 12.00, NULL, '2026-03-10 16:00:53', '2026-03-10 16:00:53'),
(185, 79, 13, 1, 25.00, NULL, '2026-03-10 16:36:30', '2026-03-10 16:36:30'),
(186, 79, 4, 1, 12.00, NULL, '2026-03-10 16:36:31', '2026-03-10 16:36:31'),
(187, 79, 6, 1, 15.00, NULL, '2026-03-10 16:36:34', '2026-03-10 16:36:34'),
(188, 79, 5, 1, 10.00, NULL, '2026-03-10 16:36:36', '2026-03-10 16:36:36'),
(189, 79, 11, 1, 22.00, NULL, '2026-03-10 16:36:43', '2026-03-10 16:36:43'),
(190, 79, 10, 1, 16.00, NULL, '2026-03-10 16:36:46', '2026-03-10 16:36:46'),
(191, 78, 35, 1, 8.00, NULL, '2026-03-10 16:37:12', '2026-03-10 16:37:12'),
(192, 78, 32, 1, 8.00, NULL, '2026-03-10 16:37:13', '2026-03-10 16:37:13'),
(193, 79, 19, 1, 25.00, NULL, '2026-03-10 16:43:39', '2026-03-10 16:43:39'),
(194, 79, 23, 1, 20.00, NULL, '2026-03-10 16:43:42', '2026-03-10 16:43:42'),
(195, 79, 47, 1, 5.00, NULL, '2026-03-10 16:43:54', '2026-03-10 16:43:54'),
(196, 79, 48, 1, 4.00, NULL, '2026-03-10 16:45:38', '2026-03-10 16:45:38'),
(197, 75, 13, 1, 25.00, NULL, '2026-03-10 17:13:19', '2026-03-10 17:13:19'),
(198, 75, 4, 1, 12.00, NULL, '2026-03-10 17:13:22', '2026-03-10 17:13:22'),
(199, 75, 5, 1, 10.00, NULL, '2026-03-10 17:13:25', '2026-03-10 17:13:25'),
(200, 80, 17, 1, 20.00, NULL, '2026-03-11 02:29:33', '2026-03-11 02:29:33'),
(201, 80, 14, 1, 18.00, NULL, '2026-03-11 02:29:35', '2026-03-11 02:29:35'),
(202, 80, 18, 1, 22.00, NULL, '2026-03-11 02:29:38', '2026-03-11 02:29:38'),
(203, 80, 21, 1, 35.00, NULL, '2026-03-11 02:29:39', '2026-03-11 02:29:39'),
(204, 80, 19, 1, 25.00, NULL, '2026-03-11 02:29:41', '2026-03-11 02:29:41'),
(205, 80, 15, 1, 16.00, NULL, '2026-03-11 02:29:43', '2026-03-11 02:29:43'),
(206, 81, 35, 1, 8.00, NULL, '2026-03-11 02:36:17', '2026-03-11 02:36:17'),
(207, 81, 32, 1, 8.00, NULL, '2026-03-11 02:36:22', '2026-03-11 02:36:22'),
(208, 81, 84, 1, 6.00, NULL, '2026-03-11 02:36:34', '2026-03-11 02:36:34'),
(209, 81, 92, 1, 5.00, NULL, '2026-03-11 02:36:37', '2026-03-11 02:36:37'),
(210, 82, 17, 1, 20.00, NULL, '2026-03-11 04:19:03', '2026-03-11 04:19:03'),
(211, 82, 21, 1, 35.00, NULL, '2026-03-11 04:19:05', '2026-03-11 04:19:05'),
(212, 82, 20, 1, 28.00, NULL, '2026-03-11 04:19:08', '2026-03-11 04:19:08'),
(213, 82, 22, 2, 14.00, NULL, '2026-03-11 04:19:10', '2026-03-11 04:19:13'),
(214, 83, 13, 2, 25.00, NULL, '2026-03-11 06:46:02', '2026-03-11 06:46:03'),
(215, 83, 6, 2, 15.00, NULL, '2026-03-11 06:46:04', '2026-03-11 06:46:05'),
(216, 84, 13, 1, 25.00, NULL, '2026-03-11 13:00:46', '2026-03-11 13:00:46'),
(217, 84, 5, 1, 10.00, NULL, '2026-03-11 13:00:50', '2026-03-11 13:00:50'),
(218, 84, 11, 2, 22.00, NULL, '2026-03-11 13:00:53', '2026-03-11 13:00:56'),
(219, 85, 103, 1, 3.00, NULL, '2026-03-11 14:57:43', '2026-03-11 14:57:43'),
(220, 85, 102, 1, 2.50, NULL, '2026-03-11 14:57:49', '2026-03-11 14:57:49'),
(221, 85, 108, 1, 2.00, NULL, '2026-03-11 14:57:51', '2026-03-11 14:57:51'),
(222, 85, 7, 2, 10.00, NULL, '2026-03-11 14:57:56', '2026-03-11 14:57:58'),
(223, 86, 13, 1, 25.00, NULL, '2026-03-12 01:12:20', '2026-03-12 01:12:20'),
(224, 86, 4, 1, 12.00, NULL, '2026-03-12 01:12:23', '2026-03-12 01:12:23'),
(225, 88, 17, 1, 20.00, NULL, '2026-03-12 23:12:20', '2026-03-12 23:12:20'),
(226, 88, 14, 1, 18.00, NULL, '2026-03-12 23:12:22', '2026-03-12 23:12:22'),
(227, 88, 18, 1, 22.00, NULL, '2026-03-12 23:12:25', '2026-03-12 23:12:25'),
(228, 89, 13, 1, 25.00, NULL, '2026-03-13 02:04:56', '2026-03-13 02:04:56'),
(229, 89, 4, 1, 12.00, NULL, '2026-03-13 02:04:57', '2026-03-13 02:04:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_perfil_permissoes`
--

CREATE TABLE `tb_perfil_permissoes` (
  `id` bigint NOT NULL,
  `perfil_id` bigint DEFAULT NULL,
  `permissao_id` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_perfil_permissoes`
--

INSERT INTO `tb_perfil_permissoes` (`id`, `perfil_id`, `permissao_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(16, 2, 2),
(17, 2, 3),
(18, 2, 4),
(19, 2, 5),
(20, 2, 6),
(21, 3, 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_perfis`
--

CREATE TABLE `tb_perfis` (
  `id` bigint NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_perfis`
--

INSERT INTO `tb_perfis` (`id`, `nome`, `descricao`) VALUES
(1, 'admin_master', 'Administrador global do sistema SaaS'),
(2, 'admin', 'Administrador da empresa'),
(3, 'user', 'Usuario colaborador'),
(4, 'Atendente', 'Atendentes da Operação'),
(5, 'cozinha', 'Atendentes da Cozinha');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_permissoes`
--

CREATE TABLE `tb_permissoes` (
  `id` bigint NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_permissoes`
--

INSERT INTO `tb_permissoes` (`id`, `nome`, `descricao`) VALUES
(1, 'gerenciar_empresas', 'Criar e gerenciar empresas'),
(2, 'gerenciar_usuarios', 'Gerenciar usuarios'),
(3, 'gerenciar_produtos', 'Cadastrar produtos'),
(4, 'gerenciar_pedidos', 'Gerenciar pedidos'),
(5, 'gerenciar_caixa', 'Controle de caixa'),
(6, 'visualizar_relatorios', 'Ver relatorios'),
(7, 'gerenciar_empresas', 'Criar e gerenciar empresas'),
(8, 'gerenciar_usuarios', 'Gerenciar usuarios'),
(9, 'gerenciar_produtos', 'Cadastrar produtos'),
(10, 'gerenciar_pedidos', 'Gerenciar pedidos'),
(11, 'gerenciar_caixa', 'Controle de caixa'),
(12, 'visualizar_relatorios', 'Ver relatorios');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_planos`
--

CREATE TABLE `tb_planos` (
  `id` bigint NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `limite_usuarios` int DEFAULT NULL,
  `limite_produtos` int DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_planos`
--

INSERT INTO `tb_planos` (`id`, `nome`, `limite_usuarios`, `limite_produtos`, `valor`, `ativo`) VALUES
(1, 'TRIAL_PLAN_ID', 1000, 1000, 0.00, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_produtos`
--

CREATE TABLE `tb_produtos` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `categoria_id` bigint DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_general_ci,
  `preco` decimal(10,2) DEFAULT NULL,
  `custo` decimal(10,2) DEFAULT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_produtos`
--

INSERT INTO `tb_produtos` (`id`, `empresa_id`, `categoria_id`, `nome`, `descricao`, `preco`, `custo`, `codigo_barras`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Refrigerante', 'Refrigerante Lata 350 ml', 6.00, 3.50, '12345678912345', 1, '2026-03-08 14:10:31', '2026-03-10 03:34:07'),
(2, 4, 1, 'Suco de uva 500ml', 'Suco de uva 500ml', 7.80, 5.00, '1221221212', 1, '2026-03-08 16:15:04', '2026-03-08 16:15:04'),
(3, 4, 2, 'Cobo (Sanduiche + Suco)', 'Cobo (Sanduiche + Suco)', 23.50, 15.80, '121221', 1, '2026-03-08 22:20:14', '2026-03-08 22:20:14'),
(4, 4, 3, 'Espetinho de Carne', 'Espetinho de carne bovina (100g)', 12.00, 7.00, '100001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(5, 4, 3, 'Espetinho de Frango', 'Espetinho de frango temperado (100g)', 10.00, 5.50, '100002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(6, 4, 3, 'Espetinho de Coração', 'Espetinho de coração de frango (6 unidades)', 15.00, 8.00, '100003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(7, 4, 3, 'Espetinho de Linguiça', 'Espetinho de linguiça toscana', 10.00, 5.00, '100004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(8, 4, 3, 'Espetinho de Queijo Coalho', 'Espetinho de queijo coalho grelhado', 12.00, 6.00, '100005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(9, 4, 3, 'Espetinho de Medalhão', 'Medalhão de mignon com bacon', 18.00, 10.00, '100006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(10, 4, 3, 'Espetinho Misto', 'Carne, frango e linguiça', 16.00, 9.00, '100007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(11, 4, 3, 'Espetinho de Picanha', 'Picanha em cubos (120g)', 22.00, 14.00, '100008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(12, 4, 3, 'Espetinho de Legumes', 'Legumes grelhados no espeto', 8.00, 3.50, '100009', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(13, 4, 3, 'Espetinho de Camarão', 'Camarões grelhados (4 unidades)', 25.00, 16.00, '100010', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(14, 4, 4, 'Batata Frita', 'Porção de batata frita (300g)', 18.00, 6.00, '200001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(15, 4, 4, 'Mandioca Frita', 'Porção de mandioca frita (300g)', 16.00, 5.00, '200002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(16, 4, 4, 'Polenta Frita', 'Porção de polenta frita (300g)', 16.00, 4.50, '200003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(17, 4, 4, 'Anéis de Cebola', 'Porção de anéis de cebola empanados', 20.00, 8.00, '200004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(18, 4, 4, 'Calabresa Acebolada', 'Calabresa frita com cebola', 22.00, 10.00, '200005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(19, 4, 4, 'Frango a Passarinho', 'Frango a passarinho temperado', 25.00, 12.00, '200006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(20, 4, 4, 'Isca de Carne', 'Iscas de carne aceboladas', 28.00, 15.00, '200007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(21, 4, 4, 'Camarão Empanado', 'Camarões empanados (10 unidades)', 35.00, 22.00, '200008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(22, 4, 4, 'Pastel de Carne', 'Pasteis de carne (4 unidades)', 14.00, 6.00, '200009', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(23, 4, 4, 'Queijo Coalho Grelhado', 'Queijo coalho grelhado (4 unidades)', 20.00, 9.00, '200010', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(24, 4, 5, 'Café Expresso', 'Café expresso (50ml)', 4.50, 1.50, '300001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(25, 4, 5, 'Café com Leite', 'Café com leite (200ml)', 6.00, 2.50, '300002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(26, 4, 5, 'Cappuccino', 'Cappuccino cremoso (200ml)', 8.00, 3.50, '300003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(27, 4, 5, 'Chocolate Quente', 'Chocolate quente cremoso (250ml)', 9.00, 4.00, '300004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(28, 4, 5, 'Chá Matte', 'Chá mate quente (300ml)', 5.00, 1.80, '300005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(29, 4, 5, 'Chá de Camomila', 'Chá de camomila (300ml)', 5.00, 1.50, '300006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(30, 4, 5, 'Chá de Hortelã', 'Chá de hortelã (300ml)', 5.00, 1.50, '300007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(31, 4, 5, 'Pinga Pura', 'Dose de pinga (50ml)', 3.00, 1.00, '300008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(32, 4, 6, 'Suco de Laranja', 'Suco de laranja natural (400ml)', 8.00, 4.00, '400001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(33, 4, 6, 'Suco de Limão', 'Suco de limão com açúcar (400ml)', 7.00, 3.00, '400002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(34, 4, 6, 'Suco de Maracujá', 'Suco de maracujá (400ml)', 8.00, 3.50, '400003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(35, 4, 6, 'Suco de Abacaxi', 'Suco de abacaxi com hortelã (400ml)', 8.00, 4.00, '400004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(36, 4, 6, 'Suco de Morango', 'Suco de morango (400ml)', 10.00, 5.50, '400005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(37, 4, 6, 'Suco de Manga', 'Suco de manga (400ml)', 8.00, 4.00, '400006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(38, 4, 6, 'Suco de Melancia', 'Suco de melancia (400ml)', 7.00, 3.00, '400007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(39, 4, 6, 'Suco Verde', 'Suco detox de couve, limão e laranja (400ml)', 10.00, 5.00, '400008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(40, 4, 7, 'Coca-Cola Lata', 'Coca-Cola lata 350ml', 6.00, 3.50, '500001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(41, 4, 7, 'Coca-Cola 600ml', 'Coca-Cola 600ml', 8.00, 4.50, '500002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(42, 4, 7, 'Guaraná Antarctica Lata', 'Guaraná Antarctica lata 350ml', 5.50, 3.00, '500003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(43, 4, 7, 'Guaraná Antarctica 600ml', 'Guaraná Antarctica 600ml', 7.50, 4.00, '500004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(44, 4, 7, 'Fanta Laranja Lata', 'Fanta Laranja lata 350ml', 5.50, 3.00, '500005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(45, 4, 7, 'Fanta Uva Lata', 'Fanta Uva lata 350ml', 5.50, 3.00, '500006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(46, 4, 7, 'Sprite Lata', 'Sprite lata 350ml', 5.50, 3.00, '500007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(47, 4, 7, 'Pepsi Lata', 'Pepsi lata 350ml', 5.00, 2.80, '500008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(48, 4, 7, 'Água com Gás', 'Água com gás 500ml', 4.00, 2.00, '500009', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(49, 4, 7, 'Água sem Gás', 'Água mineral sem gás 500ml', 3.50, 1.50, '500010', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(50, 4, 8, 'Skol Lata', 'Cerveja Skol lata 350ml', 6.00, 3.50, '600001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(51, 4, 8, 'Brahma Lata', 'Cerveja Brahma lata 350ml', 6.00, 3.50, '600002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(52, 4, 8, 'Antarctica Lata', 'Cerveja Antarctica lata 350ml', 6.00, 3.50, '600003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(53, 4, 8, 'Heineken Lata', 'Cerveja Heineken lata 350ml', 8.00, 5.00, '600004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(54, 4, 8, 'Heineken Garrafa', 'Cerveja Heineken long neck 330ml', 10.00, 6.00, '600005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(55, 4, 8, 'Original Long Neck', 'Cerveja Original long neck 355ml', 9.00, 5.50, '600006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(56, 4, 8, 'Stella Artois', 'Cerveja Stella Artois long neck 330ml', 10.00, 6.00, '600007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(57, 4, 8, 'Corona', 'Cerveja Corona long neck 330ml', 12.00, 7.00, '600008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(58, 4, 8, 'Budweiser', 'Cerveja Budweiser long neck 330ml', 9.00, 5.50, '600009', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(59, 4, 8, 'Amstel Lata', 'Cerveja Amstel lata 350ml', 6.00, 3.50, '600010', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(60, 4, 9, 'Caipirinha de Limão', 'Caipirinha de limão com cachaça', 15.00, 6.00, '700001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(61, 4, 9, 'Caipiroska de Morango', 'Caipiroska de morango com vodka', 18.00, 8.00, '700002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(62, 4, 9, 'Batida de Coco', 'Batida de coco com leite condensado', 16.00, 7.00, '700003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(63, 4, 9, 'Gin Tônica', 'Gin com água tônica e limão', 22.00, 12.00, '700004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(64, 4, 9, 'Mojito', 'Mojito com hortelã e limão', 20.00, 10.00, '700005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(65, 4, 9, 'Caipirinha de Maracujá', 'Caipirinha de maracujá com cachaça', 16.00, 7.00, '700006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(66, 4, 9, 'Sex on the Beach', 'Vodka, suco de laranja e groselha', 22.00, 11.00, '700007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(67, 4, 9, 'Cuba Libre', 'Rum, Coca-Cola e limão', 18.00, 8.00, '700008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(68, 4, 10, 'Pudim de Leite', 'Pudim de leite condensado', 12.00, 5.00, '800001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(69, 4, 10, 'Mousse de Maracujá', 'Mousse de maracujá', 10.00, 4.00, '800002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(70, 4, 10, 'Mousse de Chocolate', 'Mousse de chocolate', 10.00, 4.00, '800003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(71, 4, 10, 'Brownie com Sorvete', 'Brownie de chocolate com sorvete', 18.00, 8.00, '800004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(72, 4, 10, 'Sorvete 2 bolas', 'Sorvete de 2 bolas (sabores variados)', 10.00, 4.00, '800005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(73, 4, 10, 'Petit Gâteau', 'Petit gâteau com sorvete', 22.00, 10.00, '800006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(74, 4, 10, 'Banana Split', 'Banana split com sorvete e calda', 16.00, 7.00, '800007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(75, 4, 10, 'Torta de Limão', 'Fatia de torta de limão', 12.00, 5.00, '800008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(76, 4, 11, 'Caldo de Feijão', 'Caldo de feijão com bacon (300ml)', 12.00, 5.00, '900001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(77, 4, 11, 'Caldo de Mandioca', 'Caldo de mandioca com carne (300ml)', 14.00, 6.00, '900002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(78, 4, 11, 'Caldo Verde', 'Caldo verde com couve e calabresa (300ml)', 14.00, 6.00, '900003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(79, 4, 11, 'Caldo de Cana', 'Caldo de cana puro (400ml)', 6.00, 2.00, '900004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(80, 4, 11, 'Caldo de Mandioca com Costela', 'Caldo de mandioca com costela (400ml)', 18.00, 9.00, '900005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(81, 4, 11, 'Caldo de Feijão Carioca', 'Caldo de feijão carioca (300ml)', 12.00, 5.00, '900006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(82, 4, 11, 'Caldo de Abóbora', 'Caldo de abóbora com gengibre (300ml)', 13.00, 5.50, '900007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(83, 4, 11, 'Caldo de Palmito', 'Caldo de palmito (300ml)', 16.00, 7.00, '900008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(84, 4, 12, 'Coxinha', 'Coxinha de frango (unidade)', 6.00, 2.50, '110001', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(85, 4, 12, 'Risoles de Carne', 'Risoles de carne (unidade)', 6.00, 2.50, '110002', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(86, 4, 12, 'Quibe', 'Quibe frito (unidade)', 6.00, 2.50, '110003', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(87, 4, 12, 'Pastel de Carne', 'Pastel de carne (unidade)', 6.00, 2.50, '110004', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(88, 4, 12, 'Pastel de Queijo', 'Pastel de queijo (unidade)', 6.00, 2.50, '110005', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(89, 4, 12, 'Pastel de Pizza', 'Pastel de pizza (unidade)', 7.00, 3.00, '110006', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(90, 4, 12, 'Empada de Frango', 'Empada de frango (unidade)', 7.00, 3.00, '110007', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(91, 4, 12, 'Bolinho de Bacalhau', 'Bolinho de bacalhau (unidade)', 8.00, 3.50, '110008', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(92, 4, 12, 'Esfirra de Carne', 'Esfirra de carne (unidade)', 5.00, 2.00, '110009', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(93, 4, 12, 'Croissant de Frango', 'Croissant de frango (unidade)', 8.00, 3.50, '110010', 1, '2026-03-10 03:58:51', '2026-03-10 03:58:51'),
(94, 4, 13, 'Combo 5 Espetinhos', '5 espetinhos variados + porção de batata', 65.00, 35.00, '120001', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(95, 4, 13, 'Combo 10 Espetinhos', '10 espetinhos variados + porção de batata + refrigerante 2L', 120.00, 65.00, '120002', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(96, 4, 13, 'Combo Família', '15 espetinhos variados + 2 porções + refrigerante 2L', 180.00, 95.00, '120003', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(97, 4, 13, 'Combo Casal', '5 espetinhos + porção de calabresa + 2 cervejas', 70.00, 38.00, '120004', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(98, 4, 13, 'Combo Drink', '2 espetinhos + drink + porção pequena', 45.00, 22.00, '120005', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(99, 4, 13, 'Combo Espetinho + Cerveja', '2 espetinhos + 2 cervejas', 35.00, 18.00, '120006', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(100, 4, 13, 'Combo Executivo', '3 espetinhos + porção de mandioca + refrigerante lata', 42.00, 21.00, '120007', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(101, 4, 13, 'Combo Kids', '2 espetinhos de frango + suco natural', 25.00, 12.00, '120008', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(102, 4, 14, 'Molho Especial', 'Molho da casa (50ml)', 2.50, 1.00, '130001', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(103, 4, 14, 'Farofa', 'Porção de farofa temperada (100g)', 3.00, 1.00, '130002', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(104, 4, 14, 'Vinagrete', 'Porção de vinagrete (100g)', 3.00, 1.20, '130003', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(105, 4, 14, 'Maionese Temperada', 'Maionese temperada (50ml)', 2.50, 1.00, '130004', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(106, 4, 14, 'Pão de Alho', 'Pão de alho (unidade)', 5.00, 2.00, '130005', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(107, 4, 14, 'Molho de Pimenta', 'Molho de pimenta artesanal (30ml)', 2.00, 0.80, '130006', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(108, 4, 14, 'Queijo Ralado', 'Porção de queijo ralado (50g)', 2.00, 0.80, '130007', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52'),
(109, 4, 14, 'Bacon Extra', 'Porção de bacon frito (50g)', 4.00, 1.80, '130008', 1, '2026-03-10 03:58:52', '2026-03-10 03:58:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_produto_imagens`
--

CREATE TABLE `tb_produto_imagens` (
  `id` bigint NOT NULL,
  `produto_id` bigint DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_produto_opcao_itens`
--

CREATE TABLE `tb_produto_opcao_itens` (
  `id` bigint NOT NULL,
  `opcao_id` bigint DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `preco_adicional` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_produto_opcoes`
--

CREATE TABLE `tb_produto_opcoes` (
  `id` bigint NOT NULL,
  `produto_id` bigint DEFAULT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_usuarios`
--

CREATE TABLE `tb_usuarios` (
  `id` bigint NOT NULL,
  `empresa_id` bigint DEFAULT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_usuarios`
--

INSERT INTO `tb_usuarios` (`id`, `empresa_id`, `nome`, `email`, `senha`, `ativo`, `criado_em`, `created_at`, `updated_at`) VALUES
(2, 4, 'Ronaldo de Paula', 'ronaldodepaulasurf@yahoo.com.br', '$2y$12$.DIMfjCWML0NIEFBWJ4kjuuMB9AElu1W97eWEeYzk9DhFGtXuy/h.', 1, '2026-03-05 04:06:12', '2026-03-05 07:06:12', '2026-03-05 07:06:12'),
(3, NULL, 'Administrador Master', 'admin@clickresto.com', '$2y$12$.DIMfjCWML0NIEFBWJ4kjuuMB9AElu1W97eWEeYzk9DhFGtXuy/h.', 1, '2026-03-06 01:29:45', '2026-03-06 01:29:45', '2026-03-07 03:28:52'),
(4, 4, 'Atendente', 'atendente@admin.com', '$2y$12$.DIMfjCWML0NIEFBWJ4kjuuMB9AElu1W97eWEeYzk9DhFGtXuy/h.', 1, '2026-03-08 06:18:49', '2026-03-08 06:18:49', '2026-03-08 06:25:39'),
(5, 5, 'Administrador Sistemas', 'admin@p.com', '$2y$12$rIc5zrETPeUQ7K7AJGbf3.E5GmNtjhtRCsVu0WN4Fa/WQzUOyzFOy', 1, '2026-03-10 18:39:46', '2026-03-10 21:39:46', '2026-03-10 21:39:46'),
(6, 6, 'Administrador Pastel', 'admin@pastel.com', '$2y$12$CnT7X3KLWZ89lkj1bkhPm.0s0qoc5jUzSBJZ4dXRE25Cmd/Y/g/YC', 1, '2026-03-10 19:02:23', '2026-03-10 22:02:23', '2026-03-10 22:02:23'),
(7, 7, 'Admin Ze Esfiha', 'admin@ze.com', '$2y$12$ezXlewjgymik3hJNFyTOJOwk94Evzom7UEmW/rxNZL2JsVuAK4gEW', 1, '2026-03-10 19:17:04', '2026-03-10 22:17:04', '2026-03-10 22:17:04'),
(8, 8, 'Admin Tapioca', 'admin@tapioca.com', '$2y$12$XFj955BMKT6A8YDlHDCSVutubWTZ.SWnMivYf1mXYDqHl74NglrZa', 1, '2026-03-10 19:24:28', '2026-03-10 22:24:28', '2026-03-10 22:24:28'),
(9, 9, 'Admin Super Coxinha', 'admin@coxinha.com', '$2y$12$x2ueJzia7mSDxBHo/uWx3u2GkppGam9FPvBDi81BJQGloatE7M8g.', 1, '2026-03-10 19:29:31', '2026-03-10 22:29:31', '2026-03-10 22:29:31'),
(10, 10, 'Admin Lanches Gerais', 'admin@lanches.com', '$2y$12$QzdzVla3hzukA50.W5SJAOcDyaG5ZFOV1zrfvfuUYM3xxXL/kJ9we', 1, '2026-03-10 19:39:51', '2026-03-10 22:39:51', '2026-03-10 22:39:51'),
(11, 11, 'Açaiteria Sabor da Praça', 'admin@acai.com', '$2y$12$UW.11RLPBysEQA.WayZm5u7mD.F1XbK1L1pj/f7sG3YUDxs5uO/6S', 1, '2026-03-10 19:41:56', '2026-03-10 22:41:56', '2026-03-10 22:41:56'),
(12, 12, 'Sorvetão Brasil', 'admin@sorvetao.com', '$2y$12$le1POkXjPMXC.u2lpnjHxOB1rWT9yArnJYSH/KNvQnEbXfbcYwH0W', 1, '2026-03-10 19:44:29', '2026-03-10 22:44:29', '2026-03-10 22:44:29'),
(13, 13, 'Antonio Ronaldo de Paula Nascimento', 'ronaldo@admin.com', '$2y$12$b34NeKmwOEQgUIU7NVpw7OQVbQ6xsjkuANhxnIrLd7Kqk0lFGHvKW', 1, '2026-03-10 19:52:36', '2026-03-10 22:52:36', '2026-03-10 22:52:36'),
(14, 4, 'Chefe de Cozinha', 'chefe@admin.com', '$2y$12$blvpAsqnFDzetsZ0smZINOwZaLCUrQ4KsDQ.wVeuoUxA2H0E20C5S', 1, '2026-03-10 21:12:51', '2026-03-11 00:12:51', '2026-03-11 00:12:51'),
(15, 4, 'Atendente 2', 'atendente2@admin.com', '$2y$12$fNi4XBP6ki9kswbkV5LH7.GckhWP/.hCdYIhZdPC8kuMnkfBHwnT2', 1, '2026-03-10 23:28:17', '2026-03-11 02:28:17', '2026-03-11 02:28:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_usuario_perfis`
--

CREATE TABLE `tb_usuario_perfis` (
  `id` bigint NOT NULL,
  `usuario_id` bigint DEFAULT NULL,
  `perfil_id` bigint DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_usuario_perfis`
--

INSERT INTO `tb_usuario_perfis` (`id`, `usuario_id`, `perfil_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(2, 2, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(3, 4, 3, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(5, 5, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(6, 6, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(7, 7, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(8, 6, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(9, 8, 2, '2026-03-10 22:39:19', '2026-03-10 22:39:19'),
(10, 14, 5, '2026-03-11 01:39:36', '2026-03-11 01:39:36'),
(11, 15, 4, '2026-03-11 02:28:32', '2026-03-11 02:28:32'),
(13, 15, 3, '2026-03-12 12:13:59', '2026-03-12 12:13:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Índices de tabela `tb_assinaturas`
--
ALTER TABLE `tb_assinaturas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_caixas`
--
ALTER TABLE `tb_caixas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_caixa_movimentos`
--
ALTER TABLE `tb_caixa_movimentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_categorias`
--
ALTER TABLE `tb_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_cliente_enderecos`
--
ALTER TABLE `tb_cliente_enderecos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_comandas`
--
ALTER TABLE `tb_comandas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_configuracoes`
--
ALTER TABLE `tb_configuracoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_confirmacoes_email`
--
ALTER TABLE `tb_confirmacoes_email`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_cozinha_estacoes`
--
ALTER TABLE `tb_cozinha_estacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_cozinha_itens`
--
ALTER TABLE `tb_cozinha_itens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_empresas`
--
ALTER TABLE `tb_empresas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_entregadores`
--
ALTER TABLE `tb_entregadores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_entregas`
--
ALTER TABLE `tb_entregas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_estoque_movimentos`
--
ALTER TABLE `tb_estoque_movimentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_formas_pagamento`
--
ALTER TABLE `tb_formas_pagamento`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_mesas`
--
ALTER TABLE `tb_mesas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_notificacoes`
--
ALTER TABLE `tb_notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_empresa_destino_status` (`empresa_id`,`destino`,`status`),
  ADD KEY `idx_notif_empresa_usuario` (`empresa_id`,`usuario_id`),
  ADD KEY `idx_notif_empresa_estacao` (`empresa_id`,`estacao_id`),
  ADD KEY `idx_notif_empresa_pedido` (`empresa_id`,`pedido_id`),
  ADD KEY `idx_notif_empresa_mesa` (`empresa_id`,`mesa_id`),
  ADD KEY `idx_notif_empresa_comanda` (`empresa_id`,`comanda_id`),
  ADD KEY `fk_notif_pedido` (`pedido_id`),
  ADD KEY `fk_notif_mesa` (`mesa_id`),
  ADD KEY `fk_notif_comanda` (`comanda_id`),
  ADD KEY `fk_notif_cliente` (`cliente_id`),
  ADD KEY `fk_notif_usuario` (`usuario_id`),
  ADD KEY `fk_notif_estacao` (`estacao_id`);

--
-- Índices de tabela `tb_pagamentos`
--
ALTER TABLE `tb_pagamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_pedido_item_opcoes`
--
ALTER TABLE `tb_pedido_item_opcoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_pedido_itens`
--
ALTER TABLE `tb_pedido_itens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_perfil_permissoes`
--
ALTER TABLE `tb_perfil_permissoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_perfis`
--
ALTER TABLE `tb_perfis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_permissoes`
--
ALTER TABLE `tb_permissoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_planos`
--
ALTER TABLE `tb_planos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_produtos`
--
ALTER TABLE `tb_produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_produto_imagens`
--
ALTER TABLE `tb_produto_imagens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_produto_opcao_itens`
--
ALTER TABLE `tb_produto_opcao_itens`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_produto_opcoes`
--
ALTER TABLE `tb_produto_opcoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `tb_usuario_perfis`
--
ALTER TABLE `tb_usuario_perfis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_assinaturas`
--
ALTER TABLE `tb_assinaturas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `tb_caixas`
--
ALTER TABLE `tb_caixas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_caixa_movimentos`
--
ALTER TABLE `tb_caixa_movimentos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_categorias`
--
ALTER TABLE `tb_categorias`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `tb_clientes`
--
ALTER TABLE `tb_clientes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tb_cliente_enderecos`
--
ALTER TABLE `tb_cliente_enderecos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_comandas`
--
ALTER TABLE `tb_comandas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de tabela `tb_configuracoes`
--
ALTER TABLE `tb_configuracoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_confirmacoes_email`
--
ALTER TABLE `tb_confirmacoes_email`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `tb_cozinha_estacoes`
--
ALTER TABLE `tb_cozinha_estacoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tb_cozinha_itens`
--
ALTER TABLE `tb_cozinha_itens`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT de tabela `tb_empresas`
--
ALTER TABLE `tb_empresas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `tb_entregadores`
--
ALTER TABLE `tb_entregadores`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_entregas`
--
ALTER TABLE `tb_entregas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_estoque_movimentos`
--
ALTER TABLE `tb_estoque_movimentos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_formas_pagamento`
--
ALTER TABLE `tb_formas_pagamento`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_mesas`
--
ALTER TABLE `tb_mesas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tb_notificacoes`
--
ALTER TABLE `tb_notificacoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `tb_pagamentos`
--
ALTER TABLE `tb_pagamentos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de tabela `tb_pedido_item_opcoes`
--
ALTER TABLE `tb_pedido_item_opcoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_pedido_itens`
--
ALTER TABLE `tb_pedido_itens`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT de tabela `tb_perfil_permissoes`
--
ALTER TABLE `tb_perfil_permissoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `tb_perfis`
--
ALTER TABLE `tb_perfis`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_permissoes`
--
ALTER TABLE `tb_permissoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `tb_planos`
--
ALTER TABLE `tb_planos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tb_produtos`
--
ALTER TABLE `tb_produtos`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT de tabela `tb_produto_imagens`
--
ALTER TABLE `tb_produto_imagens`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_produto_opcao_itens`
--
ALTER TABLE `tb_produto_opcao_itens`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_produto_opcoes`
--
ALTER TABLE `tb_produto_opcoes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `tb_usuario_perfis`
--
ALTER TABLE `tb_usuario_perfis`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_notificacoes`
--
ALTER TABLE `tb_notificacoes`
  ADD CONSTRAINT `fk_notif_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_comanda` FOREIGN KEY (`comanda_id`) REFERENCES `tb_comandas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `tb_empresas` (`id`),
  ADD CONSTRAINT `fk_notif_estacao` FOREIGN KEY (`estacao_id`) REFERENCES `tb_cozinha_estacoes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_mesa` FOREIGN KEY (`mesa_id`) REFERENCES `tb_mesas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `tb_pedidos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `tb_usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
