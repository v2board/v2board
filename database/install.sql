-- Adminer 4.7.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `v2_coupon`;
CREATE TABLE `v2_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(8) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `type` tinyint(1) NOT NULL,
  `value` int(11) NOT NULL,
  `limit_use` int(11) DEFAULT NULL,
  `started_at` int(11) NOT NULL,
  `ended_at` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_invite_code`;
CREATE TABLE `v2_invite_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` char(32) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `pv` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_mail_log`;
CREATE TABLE `v2_mail_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_notice`;
CREATE TABLE `v2_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `img_url` varchar(255) DEFAULT NULL,
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
  `discount_amount` int(11) DEFAULT NULL,
  `surplus_amount` int(11) DEFAULT NULL COMMENT '剩余价值',
  `refund_amount` int(11) DEFAULT NULL COMMENT '退款金额',
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
  `month_price` int(11) DEFAULT '0',
  `quarter_price` int(11) DEFAULT '0',
  `half_year_price` int(11) DEFAULT '0',
  `year_price` int(11) DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_server`;
CREATE TABLE `v2_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `server_port` int(11) NOT NULL,
  `tls` tinyint(4) NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `rate` varchar(11) NOT NULL,
  `network` varchar(11) NOT NULL,
  `settings` text,
  `rules` text,
  `show` tinyint(1) NOT NULL DEFAULT '0',
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
  `server_id` int(11) NOT NULL,
  `u` varchar(255) NOT NULL,
  `d` varchar(255) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_ticket`;
CREATE TABLE `v2_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `last_reply_user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `level` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:已开启 1:已关闭',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_ticket_message`;
CREATE TABLE `v2_ticket_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `v2_tutorial`;
CREATE TABLE `v2_tutorial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `steps` text,
  `show` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `v2_tutorial` (`id`, `title`, `description`, `icon`, `steps`, `show`, `created_at`, `updated_at`) VALUES
(1,	'Windows',	'兼容 Windows 7 以上的版本',	'fab fa-2x fa-windows',	'[{\"default_area\":\"<div><div>下载 V2rayN 客户端。</div><div>下载完成后解压，解压完成后运行V2rayN</div><div>运行时请右键，以管理员身份运行</div></div>\",\"download_url\":\"/downloads/V2rayN.zip\"},{\"default_area\":\"<div>点击订阅按钮，选择订阅设置点击添加，输入如下内容后点击确定保存</div>\",\"safe_area\":\"<div>备注：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$app_name}}</code></div>\\n<div>地址(url)：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$subscribe_url}}</code></div>\",\"img_url\":\"https://i.loli.net/2019/11/21/UkcHNtERTnjLVS8.jpg\"},{\"default_area\":\"<div>点击订阅后，从服务器列表选择服务器</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/BgPGFQ3kCSuIRjJ.jpg\"},{\"default_area\":\"<div>点击参数设置，找到Http代理，选择PAC模式后按确定保存即启动代理。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/vnVykKEFT8Lzo3f.jpg\"}]',	1,	1577972408,	1581777396),
(2,	'Android',	'兼容 Android 6 以上的版本',	'fab fa-2x fa-android',	'[{\"default_area\":\"<div>下载 V2rayNG 客户端。</div>\",\"safe_area\":\"\",\"download_url\":\"/downloads/V2rayNG.apk\"},{\"default_area\":\"<div>打开 V2rayNG 点击左上角的菜单图标打开侧边栏，随后点击 订阅设置，点击右上角的➕按钮新增订阅。</div><div>按照下方内容进行填写，填写完毕后点击右上角的☑️按钮。</div>\",\"safe_area\":\"<div>备注：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$app_name}}</code></div>\\n<div>地址(url)：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$subscribe_url}}</code></div>\",\"download_url\":\"\",\"img_url\":\"https://i.loli.net/2019/11/21/ghuVkTe6LBqRxSO.jpg\"},{\"default_area\":\"<div>再次从侧边栏进入 设置 页面，点击 路由模式 将其更改为 \\b绕过局域网及大陆地址。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/Tf1AGoXZuhJrwOq.jpg\"},{\"default_area\":\"<div>随后从侧边栏回到 配置文件 页面，点击右上角的省略号图标选择更新订阅。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/UtfPShQXupRmB4L.jpg\"},{\"img_url\":\"https://i.loli.net/2019/11/21/ZkbNsSrAg3m5Dny.jpg\",\"default_area\":\"<div>点击选择您需要的节点，点击右下角的V字按钮即可连接。</div>\"}]',	1,	1577972534,	1577984397),
(3,	'macOS',	'兼容 Yosemite 以上的版本',	'fab fa-2x fa-apple',	'[{\"default_area\":\"<div>下载 ClashX 客户端，安装后运行。</div>\",\"download_url\":\"/downloads/ClashX.dmg\",\"img_url\":\"https://i.loli.net/2019/11/20/uNGrjl2noCL1f5B.jpg\"},{\"default_area\":\"<div>点击通知栏 ClashX 图标保持选中状态，按快捷键 ⌘+M(订阅快捷键)，在弹出的窗口点击添加输入下方信息</div>\",\"safe_area\":\"<div>Url：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$app_name}}</code></div>\\n<div>Config Name：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$subscribe_url}}</code></div>\",\"img_url\":\"https://i.loli.net/2019/11/20/8eB13mRbFuszwxg.jpg\"},{\"default_area\":\"<div>点击通知栏 ClashX 图标保持选中状态，按快捷键 ⌘+S(设置为系统代理快捷键)，即连接完成</div>\"}]',	1,	1577979855,	1581951993),
(4,	'iOS',	'兼容 iOS 9 以上的版本',	'fab fa-2x fa-apple',	'[{\"default_area\":\"<div>iOS上使用请在iOS浏览器中打开本页</div>\"},{\"default_area\":\"<div>在 App Store 登录本站提供的美区 Apple ID 下载客户端。</div><div>为了保护您的隐私，请勿在手机设置里直接登录，仅在 App Store 登录即可。</div><div>登陆完成后点击下方下载会自动唤起下载。</div>\",\"safe_area\":\"<div>Apple ID：<code onclick=\\\"safeAreaCopy(\'{{$apple_id}}\')\\\">{{$apple_id}}</code></div><div>密码：<code onclick=\\\"safeAreaCopy(\'{{$apple_id_password}}\')\\\">点击复制密码</code></div>\",\"download_url\":\"https://apps.apple.com/us/app/shadowrocket/id932747118\",\"img_url\":\"https://i.loli.net/2019/11/21/5idkjJ61stWgREV.jpg\"},{\"default_area\":\"<div>待客户端安装完成后，点击下方一键订阅按钮会自动唤起并进行订阅</div>\",\"safe_area\":\"\",\"img_url\":\"https://i.loli.net/2019/11/21/ZcqlNMb3eg5Uhxd.jpg\",\"download_url\":\"shadowrocket://add/sub://{{$b64_subscribe_url}}?remark={{$app_name}}\"},{\"default_area\":\"<div>选择节点进行链接，首次链接过程授权窗口请一路允许。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/9Zdxksr7Ey6hjlm.jpg\"}]',	1,	1577982016,	1577983283);

DROP TABLE IF EXISTS `v2_user`;
CREATE TABLE `v2_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invite_user_id` int(11) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `password_algo` char(10) DEFAULT NULL,
  `balance` int(11) NOT NULL DEFAULT '0',
  `discount` int(11) DEFAULT NULL,
  `commission_rate` int(11) DEFAULT NULL,
  `commission_balance` int(11) NOT NULL DEFAULT '0',
  `t` int(11) NOT NULL DEFAULT '0',
  `u` bigint(20) NOT NULL DEFAULT '0',
  `d` bigint(20) NOT NULL DEFAULT '0',
  `transfer_enable` bigint(20) NOT NULL DEFAULT '0',
  `enable` tinyint(1) NOT NULL DEFAULT '1',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` int(11) DEFAULT NULL,
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


-- 2020-02-17 15:11:16
