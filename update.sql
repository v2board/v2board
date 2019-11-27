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
/* 2019-11-25 */
ALTER TABLE `v2_order`
ADD `type` int(11) NOT NULL COMMENT '1新购2续费3升级' AFTER `plan_id`;
/* 2019-11-27 */
ALTER TABLE `v2_user`
ADD `commission_rate` int(11) NULL AFTER `password`;