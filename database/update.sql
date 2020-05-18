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

SET NAMES utf8mb4;

INSERT INTO `v2_tutorial` (`id`, `title`, `description`, `icon`, `steps`, `show`, `created_at`, `updated_at`) VALUES
(1,	'Windows',	'兼容 Windows 7 以上的版本',	'fab fa-2x fa-windows',	'[{\"default_area\":\"<div><div>下载 V2rayN 客户端。</div><div>下载完成后解压，解压完成后运行V2rayN</div><div>运行时请右键，以管理员身份运行</div></div>\",\"download_url\":\"/downloads/V2rayN.zip\"},{\"default_area\":\"<div>点击订阅按钮，选择订阅设置点击添加，输入如下内容后点击确定保存</div>\",\"safe_area\":\"<div>备注：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$app_name}}</code></div>\\n<div>地址(url)：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$subscribe_url}}</code></div>\",\"img_url\":\"https://i.loli.net/2019/11/21/UkcHNtERTnjLVS8.jpg\"},{\"default_area\":\"<div>点击订阅后，从服务器列表选择服务器</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/BgPGFQ3kCSuIRjJ.jpg\"},{\"default_area\":\"<div>点击参数设置，找到Http代理，选择PAC模式后按确定保存即启动代理。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/vnVykKEFT8Lzo3f.jpg\"}]',	1,	1577972408,	1577980882),
(2,	'Android',	'兼容 Android 6 以上的版本',	'fab fa-2x fa-android',	'[{\"default_area\":\"<div>下载 V2rayNG 客户端。</div>\",\"safe_area\":\"\",\"download_url\":\"/downloads/V2rayNG.apk\"},{\"default_area\":\"<div>打开 V2rayNG 点击左上角的菜单图标打开侧边栏，随后点击 订阅设置，点击右上角的➕按钮新增订阅。</div><div>按照下方内容进行填写，填写完毕后点击右上角的☑️按钮。</div>\",\"safe_area\":\"<div>备注：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$app_name}}</code></div>\\n<div>地址(url)：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$subscribe_url}}</code></div>\",\"download_url\":\"\",\"img_url\":\"https://i.loli.net/2019/11/21/ghuVkTe6LBqRxSO.jpg\"},{\"default_area\":\"<div>再次从侧边栏进入 设置 页面，点击 路由模式 将其更改为 \\b绕过局域网及大陆地址。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/Tf1AGoXZuhJrwOq.jpg\"},{\"default_area\":\"<div>随后从侧边栏回到 配置文件 页面，点击右上角的省略号图标选择更新订阅。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/UtfPShQXupRmB4L.jpg\"},{\"img_url\":\"https://i.loli.net/2019/11/21/ZkbNsSrAg3m5Dny.jpg\",\"default_area\":\"<div>点击选择您需要的节点，点击右下角的V字按钮即可连接。</div>\"}]',	1,	1577972534,	1577981610),
(3,	'macOS',	'兼容 Yosemite 以上的版本',	'fab fa-2x fa-apple',	'[{\"default_area\":\"<div>下载 ClashX 客户端，安装后运行。</div>\",\"download_url\":\"/downloads/ClashX.dmg\",\"img_url\":\"https://i.loli.net/2019/11/20/uNGrjl2noCL1f5B.jpg\"},{\"default_area\":\"<div>点击通知栏 ClashX 图标保持选中状态，按快捷键 ⌘+M(订阅快捷键)，在弹出的窗口点击添加输入下方信息</div>\",\"safe_area\":\"<div>Url：<code onclick=\\\"safeAreaCopy(\'{{$subscribe_url}}\')\\\">{{$subscribe_url}}</code></div>\\n<div>Config Name：<code onclick=\\\"safeAreaCopy(\'{{$app_name}}\')\\\">{{$app_name}}</code></div>\",\"img_url\":\"https://i.loli.net/2019/11/20/8eB13mRbFuszwxg.jpg\"},{\"default_area\":\"<div>点击通知栏 ClashX 图标保持选中状态，按快捷键 ⌘+S(设置为系统代理快捷键)，即连接完成</div>\"}]',	1,	1577979855,	1577981646),
(4,	'iOS',	'兼容 iOS 9 以上的版本',	'fab fa-2x fa-apple',	'[{\"default_area\":\"<div>iOS上使用请在iOS浏览器中打开本页</div>\"},{\"default_area\":\"<div>在 App Store 登录本站提供的美区 Apple ID 下载客户端。</div><div>为了保护您的隐私，请勿在手机设置里直接登录，仅在 App Store 登录即可。</div><div>登陆完成后点击下方下载会自动唤起下载。</div>\",\"safe_area\":\"<div>Apple ID：<code onclick=\\\"safeAreaCopy(\'{{$apple_id}}\')\\\">{{$apple_id}}</code></div><div>密码：<code onclick=\\\"safeAreaCopy(\'{{$apple_id_password}}\')\\\">点击复制密码</code></div>\",\"download_url\":\"https://apps.apple.com/us/app/shadowrocket/id932747118\",\"img_url\":\"https://i.loli.net/2019/11/21/5idkjJ61stWgREV.jpg\"},{\"default_area\":\"<div>待客户端安装完成后，点击下方一键订阅按钮会自动唤起并进行订阅</div>\",\"safe_area\":\"\",\"img_url\":\"https://i.loli.net/2019/11/21/ZcqlNMb3eg5Uhxd.jpg\",\"download_url\":\"shadowrocket://add/sub://{{$b64_subscribe_url}}?remark={{$app_name}}\"},{\"default_area\":\"<div>选择节点进行链接，首次链接过程授权窗口请一路允许。</div>\",\"img_url\":\"https://i.loli.net/2019/11/21/9Zdxksr7Ey6hjlm.jpg\"}]',	1,	1577982016,	1577983283);

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
