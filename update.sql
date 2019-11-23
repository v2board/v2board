ALTER TABLE `v2_server`
ADD `last_check_at` int(11) NULL AFTER `rate`;
ALTER TABLE `v2_server`
ADD `network` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `rate`;
ALTER TABLE `v2_server`
ADD `settings` text COLLATE 'utf8_general_ci' NULL AFTER `network`;
/* 2019-11-18 */
ALTER TABLE `v2_server`
ADD `show` tinyint(1) NOT NULL DEFAULT '0' AFTER `settings`;
/* 2019-11-23 */
ALTER TABLE `v2_user`
CHANGE `enable` `enable` tinyint(1) NOT NULL DEFAULT '1' AFTER `transfer_enable`;