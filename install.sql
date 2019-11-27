-- Adminer 4.7.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `v2_invite_code`;
CREATE TABLE `v2_invite_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` char(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_order`;
CREATE TABLE `v2_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_user_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1新购2续费3升级',
  `cycle` varchar(255) NOT NULL,
  `trade_no` varchar(36) NOT NULL,
  `callback_no` varchar(255) DEFAULT NULL,
  `total_amount` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `commission_status` tinyint(1) NOT NULL DEFAULT '0',
  `commission_balance` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_plan`;
CREATE TABLE `v2_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `transfer_enable` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `renew` tinyint(1) NOT NULL DEFAULT '1',
  `content` text,
  `month_price` int(11) NOT NULL DEFAULT '0',
  `quarter_price` int(11) NOT NULL DEFAULT '0',
  `half_year_price` int(11) NOT NULL DEFAULT '0',
  `year_price` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_server`;
CREATE TABLE `v2_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `server_port` int(11) NOT NULL,
  `tls` tinyint(4) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `rate` varchar(11) NOT NULL,
  `network` varchar(11) NOT NULL,
  `settings` text,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `last_check_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_server_group`;
CREATE TABLE `v2_server_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_server_log`;
CREATE TABLE `v2_server_log` (
  `user_id` int(11) NOT NULL,
  `u` varchar(255) NOT NULL,
  `d` varchar(255) NOT NULL,
  `rate` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_user`;
CREATE TABLE `v2_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_user_id` int(11) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `commission_rate` int(11) DEFAULT NULL,
  `commission_balance` int(11) NOT NULL DEFAULT '0',
  `t` int(11) NOT NULL DEFAULT '0',
  `u` bigint(20) NOT NULL DEFAULT '0',
  `d` bigint(20) NOT NULL DEFAULT '0',
  `transfer_enable` bigint(20) NOT NULL DEFAULT '0',
  `enable` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` int(11) NOT NULL,
  `last_login_ip` int(11) DEFAULT NULL,
  `v2ray_uuid` varchar(36) NOT NULL,
  `v2ray_alter_id` tinyint(4) NOT NULL DEFAULT '2',
  `v2ray_level` tinyint(4) NOT NULL DEFAULT '0',
  `group_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `remind_expire` tinyint(4) DEFAULT '1',
  `remind_traffic` tinyint(4) DEFAULT '1',
  `token` char(32) NOT NULL,
  `expired_at` bigint(20) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2019-11-27 11:46:36