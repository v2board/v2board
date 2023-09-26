ALTER TABLE `v2_server`
ADD `last_check_at` int(11) NULL AFTER `rate`;

ALTER TABLE `v2_server`
ADD `network` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `rate`;

ALTER TABLE `v2_server`
ADD `settings` text COLLATE 'utf8_general_ci' NULL AFTER `network`;

ALTER TABLE `v2_server`
ADD `show` tinyint(1) NOT NULL DEFAULT '0' AFTER `settings`;

ALTER TABLE `v2_user`
CHANGE `enable` `enable` tinyint(1) NOT NULL DEFAULT '1' AFTER `transfer_enable`;

ALTER TABLE `v2_order`
ADD `type` int(11) NOT NULL COMMENT '1新购2续费3升级' AFTER `plan_id`;

ALTER TABLE `v2_user`
ADD `commission_rate` int(11) NULL AFTER `password`;

ALTER TABLE `v2_user`
ADD `balance` int(11) NOT NULL DEFAULT '0' AFTER `password`;

CREATE TABLE `v2_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
);

ALTER TABLE `v2_notice`
ADD `img_url` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `content`;

CREATE TABLE `v2_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `level` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `v2_ticket_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `v2_ticket`
ADD `last_reply_user_id` int(11) NOT NULL AFTER `user_id`;

ALTER TABLE `v2_user`
CHANGE `last_login_at` `last_login_at` int(11) NULL AFTER `is_admin`;

ALTER TABLE `v2_server_log`
CHANGE `node_id` `server_id` int(11) NOT NULL AFTER `user_id`,
CHANGE `u` `u` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `server_id`,
CHANGE `d` `d` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `u`,
CHANGE `rate` `rate` int(11) NOT NULL AFTER `d`;

ALTER TABLE `v2_server`
DROP `last_check_at`;

ALTER TABLE `v2_server`
CHANGE `name` `name` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `group_id`;

ALTER TABLE `v2_plan`
CHANGE `month_price` `month_price` int(11) NULL DEFAULT '0' AFTER `content`,
CHANGE `quarter_price` `quarter_price` int(11) NULL DEFAULT '0' AFTER `month_price`,
CHANGE `half_year_price` `half_year_price` int(11) NULL DEFAULT '0' AFTER `quarter_price`,
CHANGE `year_price` `year_price` int(11) NULL DEFAULT '0' AFTER `half_year_price`;

ALTER TABLE `v2_server`
ADD `parent_id` int(11) NULL AFTER `group_id`;

CREATE TABLE `v2_mail_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
);

CREATE TABLE `v2_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` char(32) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `type` tinyint(1) NOT NULL,
  `value` int(11) NOT NULL,
  `limit_use` int(11) DEFAULT NULL,
  `started_at` int(11) NOT NULL,
  `ended_at` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
);

ALTER TABLE `v2_order`
ADD `discount_amount` int(11) NULL AFTER `total_amount`;

ALTER TABLE `v2_server_log`
CHANGE `rate` `rate` decimal(10,2) NOT NULL AFTER `d`;

ALTER TABLE `v2_order`
DROP `method`;

ALTER TABLE `v2_invite_code`
ADD `pv` int(11) NOT NULL DEFAULT '0' AFTER `status`;

ALTER TABLE `v2_user`
ADD `password_algo` char(10) COLLATE 'utf8_general_ci' NULL AFTER `password`;

ALTER TABLE `v2_server`
CHANGE `tls` `tls` tinyint(4) NOT NULL DEFAULT '0' AFTER `server_port`;

ALTER TABLE `v2_server`
ADD `rules` text COLLATE 'utf8_general_ci' NULL AFTER `settings`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `v2_user`
ADD `discount` int(11) NULL AFTER `balance`;

ALTER TABLE `v2_order`
ADD `surplus_amount` int(11) NULL COMMENT '剩余价值' AFTER `discount_amount`;

ALTER TABLE `v2_order`
ADD `refund_amount` int(11) NULL COMMENT '退款金额' AFTER `surplus_amount`;

ALTER TABLE `v2_tutorial`
ADD `category_id` int(11) NOT NULL AFTER `id`;

ALTER TABLE `v2_tutorial`
DROP `description`;

ALTER TABLE `v2_plan`
CHANGE `month_price` `month_price` int(11) NULL AFTER `content`,
CHANGE `quarter_price` `quarter_price` int(11) NULL AFTER `month_price`,
CHANGE `half_year_price` `half_year_price` int(11) NULL AFTER `quarter_price`,
CHANGE `year_price` `year_price` int(11) NULL AFTER `half_year_price`,
ADD `onetime_price` int(11) NULL AFTER `year_price`;

ALTER TABLE `v2_user`
DROP `enable`,
ADD `banned` tinyint(1) NOT NULL DEFAULT '0' AFTER `transfer_enable`;

ALTER TABLE `v2_user`
CHANGE `expired_at` `expired_at` bigint(20) NULL DEFAULT '0' AFTER `token`;

ALTER TABLE `v2_tutorial`
DROP `icon`;

ALTER TABLE `v2_server`
CHANGE `settings` `networkSettings` text COLLATE 'utf8_general_ci' NULL AFTER `network`,
CHANGE `rules` `ruleSettings` text COLLATE 'utf8_general_ci' NULL AFTER `networkSettings`;

ALTER TABLE `v2_server`
CHANGE `tags` `tags` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `server_port`,
CHANGE `rate` `rate` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `tags`,
CHANGE `network` `network` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `rate`,
CHANGE `networkSettings` `networkSettings` text COLLATE 'utf8_general_ci' NULL AFTER `network`,
CHANGE `tls` `tls` tinyint(4) NOT NULL DEFAULT '0' AFTER `networkSettings`,
ADD `tlsSettings` text COLLATE 'utf8_general_ci' NULL AFTER `tls`;

ALTER TABLE `v2_order`
ADD `balance_amount` int(11) NULL COMMENT '使用余额' AFTER `refund_amount`;

ALTER TABLE `v2_server`
CHANGE `network` `network` text COLLATE 'utf8_general_ci' NOT NULL AFTER `rate`,
ADD `dnsSettings` text COLLATE 'utf8_general_ci' NULL AFTER `ruleSettings`;

ALTER TABLE `v2_order`
ADD `surplus_order_ids` text NULL COMMENT '折抵订单' AFTER `balance_amount`;

ALTER TABLE `v2_order`
CHANGE `status` `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待支付1开通中2已取消3已完成4已折抵' AFTER `surplus_order_ids`;

CREATE TABLE `v2_server_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `server_id` int(11) NOT NULL,
  `u` varchar(255) NOT NULL,
  `d` varchar(25) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
);

ALTER TABLE `v2_tutorial`
ADD `sort` int(11) NULL AFTER `show`;

ALTER TABLE `v2_server`
ADD `sort` int(11) NULL AFTER `show`;

ALTER TABLE `v2_plan`
ADD `sort` int(11) NULL AFTER `show`;

ALTER TABLE `v2_plan`
CHANGE `month_price` `month_price` int(11) NULL AFTER `content`,
CHANGE `quarter_price` `quarter_price` int(11) NULL AFTER `month_price`,
CHANGE `half_year_price` `half_year_price` int(11) NULL AFTER `quarter_price`,
CHANGE `year_price` `year_price` int(11) NULL AFTER `half_year_price`,
ADD `reset_price` int(11) NULL AFTER `onetime_price`;

ALTER TABLE `v2_server_log`
ADD `id` bigint NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

ALTER TABLE `v2_server_log`
ADD `log_at` int(11) NOT NULL AFTER `rate`;

ALTER TABLE `v2_mail_log`
CHANGE `error` `error` text COLLATE 'utf8_general_ci' NULL AFTER `template_name`;

ALTER TABLE `v2_plan`
CHANGE `month_price` `month_price` int(11) NULL AFTER `content`,
CHANGE `quarter_price` `quarter_price` int(11) NULL AFTER `month_price`,
CHANGE `half_year_price` `half_year_price` int(11) NULL AFTER `quarter_price`,
CHANGE `year_price` `year_price` int(11) NULL AFTER `half_year_price`;

ALTER TABLE `v2_server_log`
ADD INDEX log_at (`log_at`);

ALTER TABLE `v2_user`
ADD `telegram_id` bigint NULL AFTER `invite_user_id`;

ALTER TABLE `v2_server_stat`
ADD `online` int(11) NOT NULL AFTER `d`;

ALTER TABLE `v2_server_stat`
ADD INDEX `created_at` (`created_at`);

CREATE TABLE `v2_server_trojan` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group_id` varchar(255) NOT NULL,
  `tags` varchar(255) NULL,
  `name` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) COMMENT='trojan伺服器表' COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_server_stat`
CHANGE `d` `d` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `u`,
DROP `online`;

ALTER TABLE `v2_user`
CHANGE `v2ray_uuid` `uuid` varchar(36) COLLATE 'utf8_general_ci' NOT NULL AFTER `last_login_ip`;

ALTER TABLE `v2_server_trojan`
ADD `rate` varchar(11) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `name`;

ALTER TABLE `v2_server_log`
ADD `method` varchar(255) NOT NULL AFTER `rate`;

ALTER TABLE `v2_coupon`
ADD `limit_plan_ids` varchar(255) NULL AFTER `limit_use`;

ALTER TABLE `v2_server_trojan`
ADD `server_port` int(11) NOT NULL AFTER `port`;

ALTER TABLE `v2_server_trojan`
ADD `parent_id` int(11) NULL AFTER `group_id`;

ALTER TABLE `v2_server_trojan`
ADD `allow_insecure` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许不安全' AFTER `server_port`,
CHANGE `show` `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示' AFTER `allow_insecure`;

ALTER TABLE `v2_server_trojan`
ADD `server_name` varchar(255) NULL AFTER `allow_insecure`;

UPDATE `v2_server` SET
`ruleSettings` = NULL
WHERE `ruleSettings` = '{}';

ALTER TABLE `v2_plan`
ADD `two_year_price` int(11) NULL AFTER `year_price`,
ADD `three_year_price` int(11) NULL AFTER `two_year_price`;

ALTER TABLE `v2_user`
ADD `is_staff` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_admin`;

CREATE TABLE `v2_server_shadowsocks` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `group_id` varchar(255) NOT NULL,
  `parent_id` int(11) NULL,
  `tags` varchar(255) NULL,
  `name` varchar(255) NOT NULL,
  `rate` varchar(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `server_port` int(11) NOT NULL,
  `cipher` varchar(255) NOT NULL,
  `show` tinyint NOT NULL DEFAULT '0',
  `sort` int(11) NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_coupon`
CHANGE `code` `code` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `id`;

CREATE TABLE `v2_knowledge` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `language` char(5) NOT NULL COMMENT '語言',
  `category` varchar(255) NOT NULL COMMENT '分類名',
  `title` varchar(255) NOT NULL COMMENT '標題',
  `body` text NOT NULL COMMENT '內容',
  `sort` int(11) NULL COMMENT '排序',
  `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '顯示',
  `created_at` int(11) NOT NULL COMMENT '創建時間',
  `updated_at` int(11) NOT NULL COMMENT '更新時間'
) COMMENT='知識庫' COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_order`
ADD `coupon_id` int(11) NULL AFTER `plan_id`;

ALTER TABLE `v2_server_stat`
ADD `method` varchar(255) NOT NULL AFTER `server_id`;

ALTER TABLE `v2_server`
ADD `alter_id` int(11) NOT NULL DEFAULT '1' AFTER `network`;

ALTER TABLE `v2_user`
DROP `v2ray_alter_id`,
DROP `v2ray_level`;

DROP TABLE `v2_server_stat`;

CREATE TABLE `v2_stat_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL COMMENT '节点id',
  `server_type` char(11) NOT NULL COMMENT '节点类型',
  `u` varchar(255) NOT NULL,
  `d` varchar(255) NOT NULL,
  `record_type` char(1) NOT NULL COMMENT 'd day m month',
  `record_at` int(11) NOT NULL COMMENT '记录时间',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='节点数据统计';

ALTER TABLE `v2_stat_server`
ADD UNIQUE `server_id_server_type_record_at` (`server_id`, `server_type`, `record_at`);

ALTER TABLE `v2_stat_server`
ADD INDEX `record_at` (`record_at`),
ADD INDEX `server_id` (`server_id`);

ALTER TABLE `v2_user`
DROP `enable`;

ALTER TABLE `v2_user`
    ADD `remarks` text COLLATE 'utf8_general_ci' NULL AFTER `token`;

CREATE TABLE `v2_payment` (
                              `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              `payment` varchar(16) NOT NULL,
                              `name` varchar(255) NOT NULL,
                              `config` text NOT NULL,
                              `enable` tinyint(1) NOT NULL DEFAULT '0',
                              `sort` int(11) DEFAULT NULL,
                              `created_at` int(11) NOT NULL,
                              `updated_at` int(11) NOT NULL
) COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_order`
    ADD `payment_id` int(11) NULL AFTER `coupon_id`;

ALTER TABLE `v2_payment`
    ADD `uuid` char(32) NOT NULL AFTER `id`;

ALTER TABLE `v2_user`
    ADD UNIQUE `email_deleted_at` (`email`, `deleted_at`),
DROP INDEX `email`;

ALTER TABLE `v2_user`
DROP `deleted_at`;

ALTER TABLE `v2_user`
    ADD UNIQUE `email` (`email`),
DROP INDEX `email_deleted_at`;

ALTER TABLE `v2_user`
    ADD `commission_type` tinyint NOT NULL DEFAULT '0' COMMENT '0: system 1: cycle 2: onetime' AFTER `discount`;

ALTER TABLE `v2_order`
    ADD `paid_at` int(11) NULL AFTER `commission_balance`;

ALTER TABLE `v2_server_log`
    ADD INDEX `user_id` (`user_id`),
ADD INDEX `server_id` (`server_id`);

ALTER TABLE `v2_ticket_message`
    CHANGE `message` `message` text COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `ticket_id`;

ALTER TABLE `v2_coupon`
    ADD `limit_use_with_user` int(11) NULL AFTER `limit_use`;

ALTER TABLE `v2_user`
    ADD `password_salt` char(10) COLLATE 'utf8_general_ci' NULL AFTER `password_algo`;

CREATE TABLE `v2_commission_log` (
                                     `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                     `invite_user_id` int(11) NOT NULL,
                                     `user_id` int(11) NOT NULL,
                                     `trade_no` char(36) NOT NULL,
                                     `order_amount` int(11) NOT NULL,
                                     `get_amount` int(11) NOT NULL,
                                     `created_at` int(11) NOT NULL,
                                     `updated_at` int(11) NOT NULL
) COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_plan`
    ADD `reset_traffic_method` tinyint(1) NULL AFTER `reset_price`;

ALTER TABLE `v2_server`
    RENAME TO `v2_server_v2ray`;

ALTER TABLE `v2_payment`
    ADD `icon` varchar(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `name`;

ALTER TABLE `v2_coupon`
    ADD `limit_period` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `limit_plan_ids`;

ALTER TABLE `v2_order`
    CHANGE `cycle` `period` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `type`;

ALTER TABLE `v2_server_v2ray`
DROP `alter_id`;

ALTER TABLE `v2_user`
    CHANGE `commission_type` `commission_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0: system 1: period 2: onetime' AFTER `discount`;

ALTER TABLE `v2_coupon`
    ADD `show` tinyint(1) NOT NULL DEFAULT '0' AFTER `value`;

ALTER TABLE `v2_notice`
    ADD `show` tinyint(1) NOT NULL DEFAULT '0' AFTER `content`;

ALTER TABLE `v2_order`
    ADD `actual_commission_balance` int(11) NULL COMMENT '实际支付佣金' AFTER `commission_balance`;

ALTER TABLE `v2_server_v2ray`
    CHANGE `port` `port` char(11) NOT NULL AFTER `host`;

CREATE TABLE `v2_stat_user` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `user_id` int(11) NOT NULL,
                                `server_id` int(11) NOT NULL,
                                `server_type` char(11) NOT NULL,
                                `server_rate` decimal(10,2) NOT NULL,
                                `u` bigint(20) NOT NULL,
                                `d` bigint(20) NOT NULL,
                                `record_type` char(2) NOT NULL,
                                `record_at` int(11) NOT NULL,
                                `created_at` int(11) NOT NULL,
                                `updated_at` int(11) NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `v2_payment`
    ADD `notify_domain` varchar(128) COLLATE 'utf8mb4_general_ci' NULL AFTER `config`;

ALTER TABLE `v2_stat_user`
    ADD INDEX `server_id` (`server_id`),
ADD INDEX `user_id` (`user_id`),
ADD INDEX `record_at` (`record_at`);

ALTER TABLE `v2_stat_server`
    CHANGE `u` `u` bigint NOT NULL AFTER `server_type`,
    CHANGE `d` `d` bigint NOT NULL AFTER `u`;

ALTER TABLE `v2_payment`
    ADD `handling_fee_fixed` int(11) NULL AFTER `notify_domain`,
ADD `handling_fee_percent` decimal(5,2) NULL AFTER `handling_fee_fixed`;

ALTER TABLE `v2_order`
    ADD `handling_amount` int(11) NULL AFTER `total_amount`;

DELIMITER $$

DROP PROCEDURE IF EXISTS `path-2022-03-29` $$
CREATE PROCEDURE `path-2022-03-29`()
BEGIN

    DECLARE IndexIsThere INTEGER;

SELECT COUNT(1) INTO IndexIsThere
FROM INFORMATION_SCHEMA.STATISTICS
WHERE table_name   = 'v2_stat_user'
  AND   index_name   = 'server_id';

IF IndexIsThere != 0 THEN
         TRUNCATE TABLE `v2_stat_user`;
END IF;

END $$

DELIMITER ;
CALL `path-2022-03-29`();
DROP PROCEDURE IF EXISTS `path-2022-03-29`;

ALTER TABLE `v2_stat_user`
    ADD UNIQUE `server_rate_user_id_record_at` (`server_rate`, `user_id`, `record_at`);
ALTER TABLE `v2_stat_user`
    ADD INDEX `server_rate` (`server_rate`);
ALTER TABLE `v2_stat_user`
DROP INDEX `server_id_user_id_record_at`;
ALTER TABLE `v2_stat_user`
DROP INDEX `server_id`;

ALTER TABLE `v2_stat_user`
DROP `server_id`;
ALTER TABLE `v2_stat_user`
DROP `server_type`;

ALTER TABLE `v2_notice`
    ADD `tags` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `img_url`;

ALTER TABLE `v2_ticket`
ADD `reply_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:待回复 1:已回复' AFTER `status`;

ALTER TABLE `v2_server_v2ray`
DROP `settings`;

ALTER TABLE `v2_ticket`
DROP `last_reply_user_id`;

ALTER TABLE `v2_server_shadowsocks`
    ADD `obfs` char(11) NULL AFTER `cipher`,
ADD `obfs_settings` varchar(255) NULL AFTER `obfs`;

ALTER TABLE `v2_plan`
    CHANGE `name` `name` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `transfer_enable`,
    CHANGE `content` `content` text COLLATE 'utf8mb4_general_ci' NULL AFTER `renew`;

ALTER TABLE `v2_mail_log`
    COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_mail_log`
    CHANGE `email` `email` varchar(64) NOT NULL AFTER `id`,
    CHANGE `subject` `subject` varchar(255) NOT NULL AFTER `email`,
    CHANGE `template_name` `template_name` varchar(255) NOT NULL AFTER `subject`,
    CHANGE `error` `error` text NULL AFTER `template_name`;

ALTER TABLE `v2_user`
    ADD `speed_limit` int(11) NULL AFTER `plan_id`;

ALTER TABLE `v2_plan`
    ADD `speed_limit` int(11) NULL AFTER `transfer_enable`;
ALTER TABLE `v2_server_v2ray`
    CHANGE `port` `port` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `host`;
ALTER TABLE `v2_server_shadowsocks`
    CHANGE `port` `port` varchar(11) NOT NULL AFTER `host`;
ALTER TABLE `v2_server_trojan`
    CHANGE `port` `port` varchar(11) NOT NULL COMMENT '连接端口' AFTER `host`;

ALTER TABLE `v2_server_shadowsocks`
    ADD `route_id` varchar(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `group_id`;

ALTER TABLE `v2_server_trojan`
    ADD `route_id` varchar(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `group_id`;

ALTER TABLE `v2_server_v2ray`
    COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_server_v2ray`
    CHANGE `group_id` `group_id` varchar(255) NOT NULL AFTER `id`,
    CHANGE `route_id` `route_id` varchar(255) NULL AFTER `group_id`,
    CHANGE `host` `host` varchar(255) NOT NULL AFTER `parent_id`,
    CHANGE `port` `port` varchar(11) NOT NULL AFTER `host`,
    CHANGE `tags` `tags` varchar(255) NULL AFTER `tls`,
    CHANGE `rate` `rate` varchar(11) NOT NULL AFTER `tags`,
    CHANGE `network` `network` text NOT NULL AFTER `rate`,
    CHANGE `rules` `rules` text NULL AFTER `network`,
    CHANGE `networkSettings` `networkSettings` text NULL AFTER `rules`,
    CHANGE `tlsSettings` `tlsSettings` text NULL AFTER `networkSettings`,
    CHANGE `ruleSettings` `ruleSettings` text NULL AFTER `tlsSettings`,
    CHANGE `dnsSettings` `dnsSettings` text NULL AFTER `ruleSettings`;

ALTER TABLE `v2_server_v2ray`
    ADD `route_id` varchar(255) COLLATE 'utf8mb4_general_ci' NULL AFTER `group_id`;


CREATE TABLE `v2_server_route` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `remarks` varchar(255) NOT NULL,
                                   `match` varchar(255) NOT NULL,
                                   `action` varchar(11) NOT NULL,
                                   `action_value` varchar(255) DEFAULT NULL,
                                   `created_at` int(11) NOT NULL,
                                   `updated_at` int(11) NOT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `v2_server_route`
    CHANGE `match` `match` text COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `remarks`;

ALTER TABLE `v2_order`
    ADD UNIQUE `trade_no` (`trade_no`);

ALTER TABLE `v2_plan`
    CHANGE `content` `content` text COLLATE 'utf8mb4_general_ci' NULL AFTER `renew`;

ALTER TABLE `v2_plan`
    COLLATE 'utf8mb4_general_ci';

ALTER TABLE `v2_server_v2ray`
    RENAME TO `v2_server_vmess`;

ALTER TABLE `v2_server_vmess`
    CHANGE `network` `network` varchar(11) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `rate`;

CREATE TABLE `v2_server_hysteria` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `group_id` varchar(255) NOT NULL,
                                      `route_id` varchar(255) DEFAULT NULL,
                                      `name` varchar(255) NOT NULL,
                                      `parent_id` int(11) DEFAULT NULL,
                                      `host` varchar(255) NOT NULL,
                                      `port` varchar(11) NOT NULL,
                                      `server_port` int(11) NOT NULL,
                                      `tags` varchar(255) DEFAULT NULL,
                                      `rate` varchar(11) NOT NULL,
                                      `show` tinyint(1) NOT NULL DEFAULT '0',
                                      `sort` int(11) DEFAULT NULL,
                                      `up_mbps` int(11) NOT NULL,
                                      `down_mbps` int(11) NOT NULL,
                                      `server_name` varchar(64) DEFAULT NULL,
                                      `insecure` tinyint(1) NOT NULL DEFAULT '0',
                                      `created_at` int(11) NOT NULL,
                                      `updated_at` int(11) NOT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `v2_plan`
    ADD `capacity_limit` int(11) NULL AFTER `reset_traffic_method`;

ALTER TABLE `v2_stat_order`
    CHANGE `record_at` `record_at` int(11) NOT NULL AFTER `id`,
    CHANGE `record_type` `record_type` char(1) COLLATE 'utf8_general_ci' NOT NULL AFTER `record_at`,
    CHANGE `order_count` `paid_count` int(11) NOT NULL COMMENT '订单数量' AFTER `record_type`,
    CHANGE `order_amount` `paid_total` int(11) NOT NULL COMMENT '订单合计' AFTER `paid_count`,
    CHANGE `commission_count` `commission_count` int(11) NOT NULL AFTER `paid_total`,
    CHANGE `commission_amount` `commission_total` int(11) NOT NULL COMMENT '佣金合计' AFTER `commission_count`,
    ADD `order_count` int(11) NOT NULL AFTER `record_type`,
    ADD `order_total` int(11) NOT NULL AFTER `order_count`,
    ADD `register_count` int(11) NOT NULL AFTER `order_total`,
    ADD `invite_count` int(11) NOT NULL AFTER `register_count`,
    ADD `transfer_used_total` varchar(32) NOT NULL AFTER `invite_count`,
    RENAME TO `v2_stat`;

CREATE TABLE `v2_log` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `title` varchar(255) NOT NULL,
                          `level` varchar(11) DEFAULT NULL,
                          `host` varchar(255) DEFAULT NULL,
                          `uri` varchar(255) NOT NULL,
                          `method` varchar(11) NOT NULL,
                          `data` text,
                          `ip` varchar(128) DEFAULT NULL,
                          `context` text,
                          `created_at` int(11) NOT NULL,
                          `updated_at` int(11) NOT NULL,
                          PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `v2_log`
    CHANGE `title` `title` text COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `id`;

CREATE TABLE `v2_server_vless` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `group_id` text NOT NULL,
                                   `route_id` text,
                                   `name` varchar(255) NOT NULL,
                                   `parent_id` int(11) DEFAULT NULL,
                                   `host` varchar(255) NOT NULL,
                                   `port` int(11) NOT NULL,
                                   `server_port` int(11) NOT NULL,
                                   `tls` tinyint(1) NOT NULL,
                                   `tls_settings` text,
                                   `flow` varchar(11) DEFAULT NULL,
                                   `network` varchar(11) NOT NULL,
                                   `network_settings` text,
                                   `tags` text,
                                   `rate` varchar(11) NOT NULL,
                                   `show` tinyint(1) NOT NULL DEFAULT '0',
                                   `sort` int(11) DEFAULT NULL,
                                   `created_at` int(11) NOT NULL,
                                   `updated_at` int(11) NOT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `v2_server_vless`
    CHANGE `flow` `flow` varchar(64) COLLATE 'utf8mb4_general_ci' NULL AFTER `tls_settings`;
