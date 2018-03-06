<?php

/**
 * 公用 model
 */
class Public_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();
    }

    //取得指定表当前auto_increment
    public function get_auto_increment($table)
    {
        $sql="select auto_increment from information_schema.tables where table_schema='freight' and table_name='$table'";
        return $this->db->query($sql)->row()->auto_increment;
    }


}