#!/bin/bash

path="$( cd "$( dirname "$0"  )" && pwd  )"
cd ${path}

instances=4
version() {
    echo -e "V2Board version: $(cat ${path}/config/app.php | grep -w 'version' | grep -vwi 'V2board' | awk '{print $3}' | sed $'s:\'::g')"
    echo -e "shell bash updated date: 2021-08-02"
}

Show_Help() {
    version
    echo -e "\033[1;31mUsage: $(echo $0 | awk -F "/" '{print $NF}')  command ...[parameters]....
    --help, -h                  Show this help message, More: https://github.com/v2board/v2board
    --version, -v, -V           Show version info
    --master                    Designated branch to master
    --dev                       Designated branch to dev
    --tag 1.5.2                 Designated branch to one tag
    \033[0m"
}
ARG_NUM=$#
TEMP=`getopt -o hvV --long help,master,dev,tag: -- "$@" 2>/dev/null`
[ $? != 0 ] && echo -e "\033[1;33mERROR: unknown argument! \033[0m" && Show_Help && exit 1
eval set -- "${TEMP}"
while :; do
  [ -z "$1" ] && break;
  case "$1" in
    -h|--help)
      Show_Help; exit 0
      ;;
    -v|-V|--version)
      version; exit 0
      ;;
    --master)
      master_flag="y"; shift 1
      ;;
    --dev)
      dev_flag="y"; shift 1
      ;;
    --tag)
      tag_ver=$2; shift 2
      ;;
    --)
      shift
      ;;
    *)
      echo -e "\033[1;33mERROR: unknown argument! \033[0m" && Show_Help && exit 1
      ;;
  esac
done

update(){
    php_path=$(which php)
    if [ $? -eq 0 ]; then
        rm -rf composer.lock
        ${php_path} ${path}/composer.phar update -vvv
        ${php_path} ${path}/artisan v2board:update
        ${php_path} ${path}/artisan config:cache
        v2board_queue_check=$(systemctl list-unit-files | grep -w "v2board@")
        if [ $? -eq 0 ]; then
            echo -e "\n正在重启队列服务"
            for ((num=1; num<=${instances}; num ++))
            do
                systemctl restart v2board@queue${num}
            done
            echo -e "队列服务启动完成"
            echo -e "检查队列服务启动状态"
            systemctl status v2board@queue*| grep -Ew "Service|Active|Main PID" | grep -vw "systemd"
        else
            cat > /etc/systemd/system/v2board@.service<<-EOF
[Unit]
Description=V2Board %i Service
After=network.target
Wants=network.target

[Service]
Type=simple
ExecStart=${php_path} ${path}/artisan queue:work --queue=send_email,send_telegram,stat_server
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
            systemctl daemon-reload
            echo -e "\n正在启动队列服务"
            for ((num=1; num<=${instances}; num ++))
            do
                systemctl enable v2board@queue${num}
                systemctl restart v2board@queue${num}
            done
            echo -e "队列服务启动完成"
            echo -e "检查队列服务启动状态"
            systemctl status v2board@queue*| grep -Ew "Service|Active|Main PID" | grep -vw "systemd"
        fi
    else
        echo -e "\033[1;33mNot Found PHP, please install first\033[0m"
        exit 1
    fi
}

main(){
    if [ "${master_flag}" == "y" ]; then
        git fetch --all && git reset --hard origin/master && git pull origin master
    elif [ "${dev_flag}" == "y" ]; then
        git fetch --all && git reset --hard origin/dev && git pull origin dev
    elif [ -n "${tag_ver}" ]; then
        git fetch --all && git reset --hard origin/master && git pull origin ${tag_ver}
    else
        git fetch --all && git reset --hard origin/master && git pull origin master
    fi
    update
    exit 0
}

main ${master_flag} ${dev_flag} ${tag_ver}
