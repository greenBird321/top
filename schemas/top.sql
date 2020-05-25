-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2017-05-08 03:14:51
-- 服务器版本： 5.7.9
-- PHP Version: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phalcon_top`
--

-- --------------------------------------------------------

--
-- 表的结构 `apps`
--

CREATE TABLE `apps` (
  `id` int(11) UNSIGNED NOT NULL,
  `app_id` varchar(32) DEFAULT '',
  `version` varchar(32) DEFAULT '',
  `name` varchar(64) DEFAULT '',
  `en_name` varchar(64) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `domain` varchar(255) DEFAULT '',
  `icon` varchar(255) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='内网应用';

--
-- 转存表中的数据 `apps`
--

INSERT INTO `apps` (`id`, `app_id`, `version`, `name`, `en_name`, `status`, `domain`, `icon`, `create_time`) VALUES
(1, 'top', 'zh_CN', 'TOP账号授权中心', 'Top Operation Permission', 1, 'http://top.gamehetu.com', '', '0000-00-00 00:00:00'),
(2, 'bi', 'zh_CN', 'BI数据分析系统', 'Business Intelligence', 1, 'http://bi.gamehetu.com', '', '0000-00-00 00:00:00'),
(3, 'boss', 'zh_CN', 'BOSS商业运营支撑系统', 'Business & Operation Support System', 1, 'http://boss.gamehetu.com', '', '0000-00-00 00:00:00'),
(4, 'aos', 'zh_CN', 'AOS辅助运营系统', '', 1, '', '', '0000-00-00 00:00:00'),
(5, 'dmc', 'zh_CN', '数据监控中心', '', 1, '', '', '0000-00-00 00:00:00'),
(6, 'rms', 'zh_CN', '风控管理系统', '', 1, '', '', '0000-00-00 00:00:00'),
(7, 'as', 'zh_CN', '审计系统', '', 1, '', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `class`
--

CREATE TABLE `class` (
  `id` int(11) UNSIGNED NOT NULL,
  `class_id` varchar(32) DEFAULT '',
  `name` varchar(32) DEFAULT '',
  `icon` varchar(255) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `games`
--

CREATE TABLE `games` (
  `id` int(11) UNSIGNED NOT NULL,
  `game_id` varchar(32) DEFAULT '',
  `class_id` varchar(32) DEFAULT '',
  `version` varchar(32) DEFAULT '',
  `name` varchar(64) DEFAULT '',
  `en_name` varchar(64) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `domain` varchar(255) DEFAULT '',
  `icon` varchar(255) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='游戏应用';

-- --------------------------------------------------------

--
-- 表的结构 `lang`
--

CREATE TABLE `lang` (
  `id` int(11) UNSIGNED NOT NULL,
  `lang` varchar(5) DEFAULT '',
  `name` varchar(32) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `logs_login`
--

CREATE TABLE `logs_login` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `ip` varchar(15) DEFAULT '',
  `location` varchar(32) DEFAULT '',
  `user_agent` varchar(225) DEFAULT '',
  `referer` text,
  `result` tinyint(4) DEFAULT '0',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录日志';

-- --------------------------------------------------------

--
-- 表的结构 `logs_operation`
--

CREATE TABLE `logs_operation` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(10) DEFAULT '0',
  `resource` varchar(64) DEFAULT '',
  `data_id` int(10) DEFAULT '0',
  `data` text,
  `ip` varchar(15) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志';

-- --------------------------------------------------------

--
-- 表的结构 `resources`
--

CREATE TABLE `resources` (
  `id` int(11) UNSIGNED NOT NULL,
  `app` varchar(32) DEFAULT '',
  `name` varchar(32) DEFAULT '',
  `resource` varchar(64) DEFAULT '',
  `type` enum('menu','node','link') DEFAULT 'node',
  `parent` int(11) DEFAULT '0',
  `sort` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `icon` varchar(64) DEFAULT '',
  `remark` varchar(64) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `update_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 资源';

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(32) DEFAULT '',
  `parent` int(10) DEFAULT '0',
  `remark` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `update_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 角色';

--
-- 转存表中的数据 `roles`
--

INSERT INTO `roles` (`id`, `name`, `parent`, `remark`, `status`, `create_time`, `update_time`) VALUES
(100, '管理员', 0, '', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `role_resource`
--

CREATE TABLE `role_resource` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) DEFAULT '0',
  `resource_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 角色&资源';

-- --------------------------------------------------------

--
-- 表的结构 `role_user`
--

CREATE TABLE `role_user` (
  `id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 角色&用户';

-- --------------------------------------------------------

--
-- 表的结构 `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `ticket` varchar(255) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='凭证';

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(32) DEFAULT '',
  `password` varchar(225) DEFAULT '',
  `name` varchar(64) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `mobile` varchar(20) DEFAULT '',
  `secret_key` varchar(64) DEFAULT '',
  `avatar` varchar(512) DEFAULT '',
  `create_time` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `update_time` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限部分 - 用户';

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `status`, `mobile`, `secret_key`, `avatar`, `create_time`, `update_time`) VALUES
(1000, 'joe@xxtime.com', '', 'Mr Chu', 1, '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `user_app`
--

CREATE TABLE `user_app` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `app_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户&应用关联';

-- --------------------------------------------------------

--
-- 表的结构 `user_game`
--

CREATE TABLE `user_game` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `game_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户&游戏关联';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lang`
--
ALTER TABLE `lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`lang`);

--
-- Indexes for table `logs_login`
--
ALTER TABLE `logs_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `logs_operation`
--
ALTER TABLE `logs_operation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appResource` (`app`,`resource`,`sort`) USING BTREE;

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_resource`
--
ALTER TABLE `role_resource`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket` (`ticket`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_app`
--
ALTER TABLE `user_app`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `app_id` (`app_id`);

--
-- Indexes for table `user_game`
--
ALTER TABLE `user_game`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `apps`
--
ALTER TABLE `apps`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- 使用表AUTO_INCREMENT `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;
--
-- 使用表AUTO_INCREMENT `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `lang`
--
ALTER TABLE `lang`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;
--
-- 使用表AUTO_INCREMENT `logs_login`
--
ALTER TABLE `logs_login`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `logs_operation`
--
ALTER TABLE `logs_operation`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;
--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
--
-- 使用表AUTO_INCREMENT `role_resource`
--
ALTER TABLE `role_resource`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;
--
-- 使用表AUTO_INCREMENT `user_app`
--
ALTER TABLE `user_app`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `user_game`
--
ALTER TABLE `user_game`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
