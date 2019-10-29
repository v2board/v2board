
## Environment:

- PHP7.3+
- composer
- MySQL5.5+

## Steps

### Before

执行 `cp .env.example .env` 然后配置它;

### 本地环境部署

1. 下载 composer
    > ```shell script
    > wget https://getcomposer.org/download/1.9.0/composer.phar
    > php composer.phar install
    > ```
2. 初始化项目
    > ```shell script
    > sh init.sh
    > ```
    > 
3. 从 `install.sql` 文件中恢复表


### Docker 环境部署
1. 初始化项目
    > ```shell script
    > docker-compose run --rm backend composer install
    > docker-compose run --rm backend sh init.sh
    > ```
2. 进入 docker 容器从 `install.sql` 文件中恢复表
3. 执行 `docker-compose up -d` 启动服务
