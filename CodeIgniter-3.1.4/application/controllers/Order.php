<?php

/**
 * Class Order
 * 订单操作 Controller
 */

class Order extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');

        //加载自写类
        $this->load->model('orders_model');
    }

    //创建订单 -- ajax
    public function apply($freightID,$brokerage){
        $result=$this->orders_model->orderApply($freightID,$brokerage,$this->session->userID);
        //如果写入成功 -- 提示并重新转到订单页面
        if ($result[0]) {
            echo "订单提交已成功。";
        } //否则 -- 提示失败，是否返回
        else {
            echo $result[1];
        }
    }

}

