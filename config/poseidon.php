<?php

// 修改完本文件后，执行以下命令配置才能生效
// php artisan config:cache

return array (
  // 每隔多长时间同步一次配置文件、用户、上报服务器信息
  'check_rate' => 120,
  // 本地 api, dokodemo-door,　监听在哪个端口，不能和服务端口相同
  'local_api_port' => 10084,
);
