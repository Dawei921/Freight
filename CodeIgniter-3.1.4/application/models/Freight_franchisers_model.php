<?php

/**
 * Class Freight_franchisers_model
 * 货源反馈信息
 */

class Freight_franchisers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();
    }

    //接单反馈处理
    public function freightFranchiser($freightID,$franchiserUser,$franchiser,$vehicle=null,$drivers=null)
    {
        try
        {
            //运费太低
            if ($franchiser == -1){
                $record=array(
                    'freightID'=>$freightID,
                    'franchiserUser'=>$franchiserUser,
                    'franchiser'=>$franchiser
                );
            }//我想接单
            elseif ($franchiser == 1){
                $record=array(
                    'freightID'=>$freightID,
                    'franchiserUser'=>$franchiserUser,
                    'franchiser'=>$franchiser,
                    'vehicle'=>$vehicle,
                    'drivers'=>$drivers
                );
            }

            //使用用查询构造器写入数据库并返回执行结果成功与否
            $result=$this->db->insert('freight_franchisers',$record);
            if(!$result)
            {
                throw new Exception("写入数据库出错：".$this->db->error());
            }

            return array(true,'');
        }
        catch (Exception $e)
        {
            return array(false,$e);
        }
    }

    //接单反馈信息
    public function getFranchisers($freightID){

        $query=$this->db->select("franchiserID,franchiser,franchiserTime,franchiserUser")
            ->where('freightID',$freightID)
            ->order_by('franchiserID','desc')
            ->get('v_freight_franchisers');
        return $query->result();
    }

    //我想接单信息
    public function getHopeFreights($franchiserUser='')
    {
        $query=$this->db->select('franchiserID,freightID,beginProvince,beginCity,endProvince,endCity,publishTime')
            ->where('franchiserUser',$franchiserUser)
            ->order_by('franchiserID','desc')
            ->get('v_hope_freights');
        return $query->result();
    }

    //同意接单
    public function freightReply($freightID,$franchiserID)
    {
        try
        {
            $update1=array(
                'replyUser'=>$this->session->userID,
                'reply'=>1
            );
            $update2=array(
                'recordStatus'=>1,
                'signFranchiser'=>$franchiserID
            );

            //错误处理待完成
            $this->db->trans_start();
            //注意：此处另外加入了set()调用sql内部函数赋值
            $this->db->set('replyTime','now()',false)->where('aID',$franchiserID)->update('freight_franchisers',$update1);
            if($this->db->affected_rows()==0){
                throw new Exception('未能更新，没有匹配的反馈数据。');
            }
            $this->db->where('aID',$freightID)->update('freights',$update2);
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

    //同意接单信息
    public function getReply($freightID,$franchiserUser){

        $query=$this->db->select("aID as franchiserID,replyTime,replyUser")
            ->where(array('freightID'=>$freightID,'franchiserUser'=>$franchiserUser,'reply'=>1))
            ->get('freight_franchisers');
        return $query->row();
    }

    //增加驾驶员
    public function addDriver($drivingLicense,$driver,$inputUser){
        try{
            $result=$this->db->insert('drivers',array('drivingLicense'=>$drivingLicense,'driver'=>$driver,'inputUser'=>$inputUser));
            if(!$result){
                throw new Exception("写入数据库出错：".$this->db->error());
            }

            return array(true,$this->db->insert_id());
        }
        catch (Exception $e){
            return array(false,$e);
        }
    }

}