<?php
date_default_timezone_set('PRC');
session_start();

//数据库连接
define("DB_HOST","{your mysql host}");
define("DB_PORT","{your mysql port}");
define("DB_DATABASE","{your mysql db name}");
define("DB_USER","{your mysql username}");
define("DB_PASS","{your mysql passwd}");

$hint = "";

include_once("../class/fmysql.class.php");
$s = new FMYSQL();  

if($keyword = _GET("key")){ //执行搜索
          
    //把关键词切割
    $parts = explode(" ",trim($keyword)); //自己输入时注意    
    unset($keys);    
    foreach($parts as $key){
        if($key){
            $keys[] = $key;
        }
    }
    
    if($keys){
        $sql = "SELECT N.dname, N.ts, T.* FROM `disk_name` N,  `disk_tree` T WHERE T.`diskid` = N.`id` ";
        
        $sql_path = "";
        $sql_filename = "";
        foreach($keys as $k){
            $sql_path .= "AND T.`path` LIKE '%".$s->escape($k)."%' ";
            $sql_filename .= "AND T.`filename` LIKE '%".$s->escape($k)."%' ";
        }
        //if($sql_path){
        $sql_path = substr($sql_path,3);  
        $sql_filename = substr($sql_filename,3);             
        //}
        
        $sql .= "AND ((".$sql_path.") OR (".$sql_filename."));";    
        
        if(!$rs = $s->getData($sql)){
            $hint = "“{$keyword}”有 0 条相关结果";
        }else{
            $hint = "“{$keyword}”共有".count($rs)."条相关结果";
        }
    }
    
}else{
    
    
    $sql = "SELECT count(*) as `dcount` FROM `disk_name`;";
    $rs = $s->getLine($sql);
    $dcount = $rs["dcount"];
    $sql = "SELECT count(*) as `fcount` FROM `disk_tree`;";
    $rs = $s->getLine($sql);    
    $fcount = $rs["fcount"];    
    $s->closeDb();
    
    $hint = "本库目前有 {$dcount}个磁盘，{$fcount}条记录。";
    $key = "";
    $rs = false;
} 


function _GET($key,$def=null){
    return isset($_GET[$key])?$_GET[$key]:$def;
}

?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>硬盘仓索引</title>

    <style>

        *{margin: 0;padding:0}
        html,body {height: 100%}

        body {
            color: #FDFDFD;
            background: #353535;
        }

        .container {
            position: relative;
            margin: 0 auto;
            width: 100%;
            max-width: 1200px;
            text-align: center;
        }

        h1 {
            text-shadow: 2px 2px 1px rgba(0,0,0,0.5);
            margin: 35px 0;
        }
        
        #hint {
            margin: 20px 0;
            font-style: italic;
            color: rgba(200,230,255,0.7);               
        }

        table {
            width: 100%;;
            background: transparent;
        }
        

        tr:hover {
            background: rgba(255,255,255,0.1);
        }
        th,td {
            margin: 1px;
            background: #404040;
            padding: 11px 0;
            border-bottom: 3px #AAA solid;
            word-break: break-all;
        }
        td {

            border-bottom: 1px #AAA dotted;
        }

        .block_search {
            margin: 20px 0 50px;
        }

        input {
            width: 400px;
            height: 40px;
            font-size: 1.2rem;
            padding: 0 1rem;

            background: transparent;
            color: yellow;
            border: 0;
            border-bottom: 1px #AAA solid;
        }

        button {
            height: 40px;
            padding: 0 1rem;
            margin: 0 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        tr td:nth-child(2){
            color: #a7fff8;
            text-shadow: 1px 1px 0 #000;;
        }


        tr td:nth-child(3){
            color: orange;
            text-shadow: 1px 1px 0 #000;;
        }
        
        tr td:nth-child(4){
            font-size: 0.8rem;
            font-style: italic;
        }

        tr td:nth-child(5){
            color: #aaa;
            font-size: 0.8rem;
            text-shadow: 1px 1px 0 #000;;
        }

        .hit {
            color: red;
        }
               
        footer {
            position: relative;
            bottom: 0;
            margin: 50px 0;
            font-size: 0.8rem;
            text-shadow: 1px 1px 0 #000;;
        }

    </style>

</head>
<body>
<div class="container">

    <h1>仓库盘内容检索</h1>

    <div class="block_search">
        <form method="get" >
            <input type="text" name="key" value="<?php echo $keyword;?>" />
            <button type="submit">Find</button>
        </form>
    </div>
    <div id="hint"><?php echo $hint;?></div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">所在硬盘</th>
                <th style="width: 40%;">路径</th>
                <th style="width: 30%;">文件名</td>
                <th style="width: 8%;">大小</th>
                <th style="width: 10%;">登记日期</th>
            </tr>
        </thead>
        <tbody>
            <!--//sample
            <tr>
                <td>WD2T1_1</td>
                <td>j:/xxxx/dasd</td>
                <td>xxxxx.iso</td>
                <td>800MB</td>
                <td>2012-12-12</td>
            </tr>
            -->
            <?php
            if($rs){
                foreach($rs as $r){
            ?>
            <tr>
                <td><?php echo $r["dname"];?></td>
                <td><?php echo markKeyword($r["path"],$keys);?></td>
                <td><?php echo markKeyword($r["filename"],$keys);?></td>
                <td><?php echo $r["filesize"];?></td>
                <td><?php echo date("Y/m/d",strtotime($r["ts"]));?></td>
            </tr>
            <?php                    
                }   
                
            }       
            
            function markKeyword($str,$keys){
                foreach($keys as $key){
                    $str = str_ireplace($key,"<span class='hit'>".$key."</span>",$str);   
                }                
                return $str;
            }     
            ?>
        </tbody>
    </table>
    <!--//
    <p>
    debug: <br />
    <?php 
        echo $sql;
    ?>
    </p>
    -->
    <footer>
        本应用仅供 呱呱独享的 moment, wow awsome! 2020 &copy;
    </footer>
</div>
</body>
</html>
