#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# 扫描硬盘分区构建文件索引存库
# Copyright 2020 <snmoney@gmail.com>
# win version

import os

import mysql.connector
from mysql.connector import Error
from mysql.connector import errorcode

# 避免很多广告文件或者无关的小文件会增加数据库负担
# 像解压的漫画之类的可以忽略文件，只检索到目录就够了
# 只有文件夹 与 大于 5M 的文件会被记录，太小的文件则直接忽略，
minFileSize = 5000000

#数据库链接配置
db_cfg = mysql.connector.connect(
            host="{你的mysql数据库IP}",
            user="{用户名}",
            passwd="{密码}",
            database="{数据库名称，默认hddlib}"
        )
db = db_cfg.cursor()

def sizeReadable(size, is_disk=False, precision=2):

    formats = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
    unit = 1000.0 if is_disk else 1024.0
    if not(isinstance(size, float) or isinstance(size, int)):
        raise TypeError('a float number or an integer number is required!')
    if size < 0:
        raise ValueError('number must be non-negative')
    for i in formats:
        size /= unit
        if size < unit:
            return str(round(size, precision)) + i
    return str(round(size, precision)) + i


inpDrv = input("扫描的盘符（不带，如 c 代表 C:\ ）:")
inpDrv = inpDrv.upper()
inpDiskName = input("给磁盘一个名称：")

scanPath = inpDrv+":\\"

#获得卷标号
disksn = ''
res = os.popen("dir "+scanPath)
res_lines = res.read()

for line in res_lines.splitlines():
    if "序列号" in line : #win10简体中文版适用，其他版本可能需要调整
        parts = line.strip().split(" ")
        disksn = parts[1]
        break

#print("盘符，磁盘名, 扫描目录：", inpDrv, inpDiskName, scanPath) #debug

print((inpDiskName))

#写入一个磁盘名
sql = "INSERT INTO `disk_name`(`dname`) VALUES ('"+inpDiskName+"');"
sql_dsn = "INSERT INTO `disk_name`(`dname`, `dsn`) VALUES ('"+inpDiskName+"', '"+disksn+"');"

if disksn : #有磁盘序号的处理逻辑
    sql = "SELECT `id` FROM `disk_name` WHERE `dsn` LIKE '"+ disksn +"' LIMIT 1;";
    db.execute(sql)
    res = db.fetchone()
    if res : #存在记录，需要预处理
        diskId = res[0]
        #清除已经有的记录
        sql = "DELETE FROM `disk_tree` WHERE `diskid` = "+diskId+" ;"
        db.execute(sql)
        db_cfg.commit()

    else: #没有结果 insert
        try:
            db.execute(sql_dsn)
            db_cfg.commit()

            # 获取 diskId
            diskId = str(db.lastrowid)

        except mysql.connector.Error as err:
            print("mysql_err @diskname insert:", inpDiskName, err.msg)
            diskId = 0
            # exit()
else:
    try:
        #db.execute(sql, vals)
        db.execute(sql)
        db_cfg.commit()

        #获取 diskId
        diskId = str(db.lastrowid)

    except mysql.connector.Error as err:
        print("mysql_err @diskname insert:", inpDiskName, err.msg)
        diskId = 0
        #exit()


sql = "INSERT INTO `disk_tree`(`diskid`, `path`, `filename`, `filesize`) VALUES (%s, %s, %s, %s);"


fileTree = os.walk(scanPath)

matchCount = 0;
for i in fileTree:

    #如果是空目录则记录目录名称
    if(len(i[2])==0):
        filename = "__DIR__" #特殊名称标记为目录
        vals = (diskId, i[0], filename, '0')
        db.execute(sql, vals)

    else :
        bigfilecount = 0 #如果目录下没有5M的文件，还是需要记录一个空目录
        for f in i[2]:
            try:
                realPath = os.path.join(i[0], f)
                fileSize = os.path.getsize(realPath)
                if fileSize >= minFileSize : #小于5M不入库
                    #print(realPath, sizeReadable(fileSize)) #debug
                    matchCount += 1
                    bigfilecount += 1
                    vals = (diskId, i[0], f, sizeReadable(fileSize))
                    db.execute(sql, vals)
            except OSError as err :
                #部分文件可能损坏或特殊的文件名导致文件系统无法正确操作
                #skip
                print(str(err))

        if bigfilecount == 0 :
            filename = "__DIR__"
            vals = (diskId, i[0], filename, '0')
            db.execute(sql, vals)

    db_cfg.commit() #每个文件夹提交一次

print("all match files:", matchCount)
