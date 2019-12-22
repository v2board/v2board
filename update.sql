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