ALTER TABLE `v2_server`
ADD `last_check_at` int(11) NULL AFTER `rate`;
ALTER TABLE `v2_server`
ADD `network` varchar(11) COLLATE 'utf8_general_ci' NOT NULL AFTER `rate`;
ALTER TABLE `v2_server`
ADD `settings` text COLLATE 'utf8_general_ci' NULL AFTER `network`;