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
  `updated_at` int(11) NOT NULL,
);

ALTER TABLE `v2_order`
ADD `discount_amount` int(11) NULL AFTER `total_amount`;

CREATE TABLE `v2_tutorial` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL,
  `description` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL,
  `icon` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL,
  `steps` text NULL,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
);