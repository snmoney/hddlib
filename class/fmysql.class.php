<?php

/**
 * @author Flamen 
 * @email snmoney#gmail.com 
 * @copyright 2014
 */
 
CLASS FMYSQL{
    
    private $port;// = NAS_DB_PORT;
    private $host;// = NAS_DB_HOST;    
	private $username;// = NAS_DB_USER ;
	private $password;// = NAS_DB_PASS ;

    private $database;// = NAS_DB_DATABASE;

	//set default charset as utf8
	private $charset;// = 'UTF8';
    private $errno = 0;
    private $error;
    private $last_sql;
    
    function __construct($do_replication = true)
	{
		$this->port = DB_PORT;
		$this->host = DB_HOST;
		$this->username = DB_USER ;
		$this->password = DB_PASS ;

        $this->database = DB_DATABASE;

		//set default charset as utf8
		$this->charset = 'UTF8';
        
        $this->do_replication = $do_replication;
	}
    
    /**
	 * @ignore
     * 参数意义不明？？
	 */
	private function connect($is_master=true)
	{
		$host = $this->host;
        if (!$db = mysqli_connect($host, $this->username, $this->password, $this->database, $this->port)) {
            $this->error = mysqli_connect_error();
            $this->err = $this->error;
            $this->errno = mysqli_connect_errno();
            return false;
        }
        
        mysqli_set_charset($db, $this->charset);
        return $db;
    }
    
    /**
     *
     * @ignore
     *
     */
    private function db_read() {
        if (isset($this->db_read)) {
            mysqli_ping($this->db_read);
            return $this->db_read;
        } else {
            if (!$this->do_replication){
                return $this->db_write();
            } else {
                $this->db_read = $this->connect(false);
                return $this->db_read;
            }
        }
    }
    
    /**
     *
     * @ignore
     *
     */
    private function db_write() {
        if (isset($this->db_write)) {
            mysqli_ping($this->db_write);
            return $this->db_write;
        } else {
            $this->db_write = $this->connect(true);
            return $this->db_write;
        }
    }
    
    /**
     *
     * @ignore
     *
     */
    private function save_error($dblink) {
        $this->error = mysqli_error($dblink);
        $this->err = $this->error;
        $this->errno = mysqli_errno($dblink);
    }

    
//     public function getLine($sql){
//         $rs = mysql_query($sql,$this->db);
//         $this->errno = mysql_errno($this->db);
//         if($this->errno) $this->err = mysql_error($this->db);        
//         unset($r);
//         if($r=mysql_fetch_array($rs)){
//             return $r;
//         }else{
//             return null;
//         }
//     }
    
    /**
     * 运行Sql,以数组方式返回结果集第一条记录
     *
     * @param string $sql
     * @return array
     */
    public function getLine($sql){
        return $this->get_line($sql);
    }
    
    /**
     * 同getLine,向前兼容
     *
     * @param string $sql
     * @return array
     * @ignore
     */
    public function get_line($sql){
        $data = $this->get_data($sql);
        if ($data) {
            return @reset($data);
        } else {
            return null;
        }
    }
    
//     public function runSql($sql){
//         $rs = mysql_query($sql,$this->db);
//         $this->errno = mysql_errno($this->db);
//         if($this->errno) $this->err = mysql_error($this->db);
//         //return true;//不严谨 ..等下再补把
//     } 

    /**
     * 运行Sql语句,不返回结果集
     *
     * @param string $sql
     * @return mysqli_result|bool
     */
    public function runSql($sql){
        return $this->run_sql($sql);
    }
    
    /**
     * 同runSql,向前兼容
     *
     * @param string $sql
     * @return bool
     * @author EasyChen
     * @ignore
     */
    public function run_sql($sql){
        $this->last_sql = $sql;
        $dblink = $this->db_write();
        if ($dblink === false) {
            return;
        }
        $ret = mysqli_query( $dblink, $sql );
        $this->save_error( $dblink );
    }
    
//     public function getData($sql){
//         $rs = mysql_query($sql,$this->db);
//         $this->errno = mysql_errno($this->db);
//         if($this->errno) $this->err = mysql_error($this->db);
//         unset($arr);
//         $arr = array();
//         if($rs){
//             while($r = mysql_fetch_array($rs)){
//                $arr[] = $r;
//             }
//         }
//         return $arr;
//     }
    
    /**
     * 运行Sql,以多维数组方式返回结果集
     *
     * @param string $sql            
     * @return array
     * @author EasyChen
     */
    public function getData($sql) {
        return $this->get_data($sql);
    }
    
    /**
     * 同getData,向前兼容
     *
     * @ignore
     *
     */
    public function get_data($sql) {
        $this->last_sql = $sql;
        $data = Array();
        $i = 0;
        $dblink = $this->do_replication? $this->db_read(): $this->db_write();
        $result = mysqli_query($dblink, $sql);
        
        $this->save_error($dblink);
        
        if (is_bool($result)) {
            return $data;
        } else {
            while ($Array = mysqli_fetch_array($result, 1)) {
                $data[$i++] = $Array;
            }
        }
        
        mysqli_free_result($result);
        return $data;
    }
    
    /**
     * 同mysqli_last_id函数
     * PHP's mysqli_last_id()在id为big int时,会出现溢出,用Sql查询替代掉
     *
     * @return int
     * @author EasyChen
     */
    public function lastId() {
        return $this->last_id();
    }
    
    /**
     * 同lastId,向前兼容
     *
     * @return int
     * @author EasyChen
     * @ignore
     *
     */
    public function last_id() {
        $result = mysqli_insert_id($this->db_write());
        return $result;
    }
    
    /**
     * 关闭数据库连接
     *
     * @return bool
     * @author EasyChen
     */
    public function closeDb() {
        return $this->close_db();
    }
    
    /**
     * 同closeDb,向前兼容
     *
     * @return bool
     * @author EasyChen
     * @ignore
     *
     */
    public function close_db() {
        if (isset($this->db_read))
            @mysqli_close($this->db_read);
        
        if (isset($this->db_write))
            @mysqli_close($this->db_write);
    }
    
    /**
     * FMYSQL::escape()
     * 同mysqli_real_escape_string
     *
     * @param string $str            
     * @return string
     * @author EasyChen
     */
    public function escape($str) {
        if (isset($this->db_read)) {
            $db = $this->db_read;
        } elseif (isset($this->db_write)) {
            $db = $this->write;
        } else {
            $db = $this->db_read();
        }
        return mysqli_real_escape_string($db, $str);
    }
    
    /**
     * 返回错误码
     * 
     * @return int
     */
    public function errno() {
        return $this->errno;
    }
    
    /**
     * 返回错误信息
     * 
     * @return string
     */
    public function error() {
        return $this->error;
    }
    
    /**
     * 返回错误信息, error的别名
     */
    public function errmsg() {
        return $this->error();
    }
}

?>
