<?php

/**
 * Class Orders_model
 * 订单信息
 */

class Orders_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();

        //加载自写类
        $this->load->model('public_model');
    }

    //创建订单
    public function orderApply($freightID,$brokerage,$orderUser)
    {
        try
        {
            $record=array(
                'freightID'=>$freightID,
                'brokerage'=>$brokerage,
                'orderUser'=>$orderUser,
                'orderStatus'=>0
            );
            $update=array(
                'recordStatus'=>2,
                'signOrder'=>$this->public_model->get_auto_increment('orders')
            );

            //错误处理待完成
            $this->db->trans_start();
            $this->db->insert('orders',$record);
            $this->db->where('aID',$freightID)->update('freights',$update);
            if($this->db->affected_rows()==0){
                throw new Exception('未能更新，没有匹配的货源数据。');
            }
            $this->db->trans_complete();
            return array(true,'');
        }
        catch (Exception $e)
        {
            return array(false,$e);
        }
    }

    //用户订单信息
    public function getUserOrders($orderUser='',$orderStatus)
    {
        $query=$this->db->select('orderID,orderUser,orderTime,freightID,beginProvince,beginCity,endProvince,endCity');
        $query=$query->where('orderUser',$orderUser);
        //数据库字段orderStatus=0为进行中
        if($orderStatus=='进行中'){
            $query=$query->where('orderStatus',0);
        }//数据库字段orderStatus=-1或1为已完成
        elseif($orderStatus=='已完成'){
            $query=$query->where('orderStatus<>',0);
        }
        $query=$query->order_by('orderID','desc')->get('v_order_freight');
        return $query->result();
    }

}