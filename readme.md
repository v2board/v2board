
## 依赖环境:

- PHP7.3+
- composer
- MySQL5.5+

----

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

---

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

## 注意
每次修改 `.env` 文件后需要执行 `docker run --rm -v $(pwd):/app composer sh init.sh`


## 其他
Telegram Channel: [@v2board](https://t.me/v2board)