# hddlib

为管理脱机仓库盘写的一个硬盘文件索引小脚本

## 运行环境

扫描录入：win10 + python3

查询检索: php 5.4或以上 + mysql

## 安装步骤

### 1. 导入 `hddlib.sql` 到 数据库

可使用phpmyadmin或 mysql GUI工具，账号需要有创建库权限。

### 2. 编辑 `scanhdd.py` 头部的数据库链接信息

按自己情况修改

```python
#数据库链接配置
db_cfg = mysql.connector.connect(
            host="{你的mysql数据库IP}",
            user="{用户名}",
            passwd="{密码}",
            database="{数据库名称，默认hddlib}"
        )
```

### 3. 编辑 `php/index.php` 头部的数据库连接信息

同上, 然后把文件放到 www 下的任意目录，例如 /wwwroot/hddlib/index.php

```php
//数据库连接
define("DB_HOST","{your mysql host}");
define("DB_PORT","{your mysql port}");
define("DB_DATABASE","{your mysql db name}");
define("DB_USER","{your mysql username}");
define("DB_PASS","{your mysql passwd}");
```

## 用法

扫描：

接上仓库盘，记住盘符 例如是 `Z:`

在 `CMD` 中输入以下指令，依次输入盘符和任意命名仓库盘名称
> python3 scanhdd.py
```
foo\bar>扫描的盘符（不带，如 c 代表 C:\ ）: z
foo\bar>给磁盘一个名称：一个仓库盘
all match files:999
```

扫描完毕后会提示扫描到符合条件文件的总数


检索：

访问`index.php`所在路径，输入文件名的部分查询
