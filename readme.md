# 呼叫中心系统同步

##开发环境
---
* Mac OS X EI Capitan 10.11.2
* PHP 7.0
* Nginx 1.10.2 (该项目无需HTTP Server)
* MariaDB 10.1.19
* Composer 1.2.1
* Laravel 5.3.28
* guzzlehttp/guzzle 6.2

##部署环境
---
> 建议使用Linux 搭建LNPM环境，Windows下未进行测试；

硬件：
* CPU：i5 及以上
* RAM：4G 及以上
* HDD：500G 及以上

软件：
* CentOS >=7.0
* PHP >=5.6

##命令说明
---
###直接加压完整的项目包
加压后进入项目根目录，执行如下命令：

```sh
php artisan sync:record -h

Usage:
  sync:record [options] [--] <method> <hotline> <username> <password> <seed> <limit> [<startId>]

Arguments:
  method                in or out   #in代表呼入记录，out代表呼出记录
  hotline               Your hotline    #热线号码
  username              Your username   #用户名
  password              Your Password   #密码
  seed                  Encryption parameters   #加密选项
  limit                 The maximum value of no more than 5000  #取出数据限制，天润API每个请求最多5000
  startId                [default: "1"]     #起始ID，用户设置从哪个ID开始获取数据

Options:
      --sql             Sync records to local database      #可选项，将记录写入本地数据库（需要配置数据库）
      --file            Download record file to loacl disk  #可选项，将自动下载录音文件到本地
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
      --env[=ENV]       The environment the command should run under
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Sync Call-in/Call-out Record
```

> 需要注意的是，下载录音文件时可能会出现假死的情况，此时需要终止命令并重新开始，可以查询最后一个文件的ID，并填入下次命令的startID选项！（假死的原因尚未找到原因，可能是零碎文件过多，也可能是 Guzzlehttp 的多线程异步请求导致的。）

##数据库配置

如果需要保存呼入呼出记录的话需要配置数据库设置，编辑项目目录下的.env文件，修改如下配置：

```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

配置完成后执行如下命令创建数据库表：

```sh
php artisan migrate
```

不出意外的话数据库中将会出现5个表：
callin      //存储呼入记录
callout     //存储呼出记录
migrations
password_resets
users

##运行
---
For example:

`php artisan sync:record in 61564630 admin admin aaa 5000 --sql --file`

`php artisan sync:record out 61564630 admin admin aaa 5000 --sql --file`

##录音文件保存位置
---
./sotrage/app/records/{callin or callout}/date/*.mp3

_Develop By `George` & Powered by [Laravel](https://laravel.com)_
