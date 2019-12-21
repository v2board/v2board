<img src="https://user-gold-cdn.xitu.io/2019/11/18/16e7f1c39b59e52b?w=500&h=500&f=png&s=85055" alt="logo" width="130" height="130" align="right"/>

# **V2Board**

- PHP7.3+
- Composer
- MySQL5.5+
- Redis
- Laravel

## 演示

[Demo](https://v2board.com)
演示站点由 👉[Moack](https://www.moack.co.kr/index.php?rp=/announcements/5/11.html)👈 强力驱动

## 本地环境部署

1. 下载 composer
    > ```shell script
    > wget https://getcomposer.org/download/1.9.0/composer.phar
    > php composer.phar install
    > ```
2. 从 `install.sql` 文件中恢复表
3. 执行 `cp .env.example .env` 然后配置它
4. 执行配置脚本
    > ```shell script
    > sh init.sh
    > ```
    > 

> 每次修改 `.env` 文件后需要执行 `php artisan config:cache` 重建缓存


## Docker 环境部署
> 首先 `cp docker-compose.yml.example docker-compose.yml` 选择性修改
1. 执行 `docker-compose run --rm db` 进入 docker 容器
2. 从 `install.sql` 文件中恢复表后退出容器，执行 `docker-compose down`
3. 执行 `cp .env.example .env` 然后配置它
4. 执行配置脚本
    > ```shell script
    > docker run --rm -v $(pwd):/app composer install
    > docker run --rm -v $(pwd):/app composer sh init.sh
    > ```
4. 执行 `docker-compose up -d` 启动服务
> 每次修改 `.env` 文件后需要执行 `docker run --rm -v $(pwd):/app composer artisan config:cache` 重建缓存

## 注意

伪静态：
```
location /downloads {
}

location / {  
	try_files $uri $uri/ /index.php$is_args$query_string;  
}
```

## 投喂
ETH&(USDT-ERC20): 0x84F85A89105B93F74c3b5db6410Ee8630F01063f

## 其他
Telegram Channel: [@v2board](https://t.me/v2board)