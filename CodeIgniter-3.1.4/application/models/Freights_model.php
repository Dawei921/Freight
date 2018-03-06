<?php

/**
 * Class Freights_model
 * 货源信息
 */

class Freights_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();
        $this->load->library('session');
        $this->load->library('upload');

        //加载自写类
        $this->load->model('public_model');
        //CI不支持multiple多文件上传，因此加入自定义多文件上传类
        $this->load->library('multiple_upload');

    }

    //写入发货信息 -- freight
    public function publish()
    {
        try
        {
            //先上传文件获取文件名再存入数据库
            //文件名使用freights表aID+序号.扩展名
            //此设置默认会在已存在文件时自动在文件名后加一个数字来存储
            $uploadPath='upload/freights/';
            $uploadConf=array(
                'upload_path'=>FCPATH.$uploadPath,
                'allowed_types'=>'*',
                'file_name'=>$this->public_model->get_auto_increment('freights').'_'
                );
            $this->upload->initialize($uploadConf);
            $uploadResult=$this->multiple_upload->do_multiple_upload('screen',$this->upload);
            if(!$uploadResult)
            {
                throw new Exception('文件上传遇到错误：'.$this->multiple_upload->dispaly_multiple_errors());
            }
            //返回上传文件名数组并加上上传相对路径组合成用,分隔的字符串以存入数据库
            $filesArray=$this->multiple_upload->multiple_data('file_name');
            $separator=",".$uploadPath;
            $filesStr=implode($separator,$filesArray);
            $filesStr=$uploadPath.$filesStr;

            //从输入类里取出表单数据 -- 在Controller里已验证通过
            //注意上传文件使用相对路径名存入数据库，这样在取出后要再加 base_url() 前缀
            $record=array(
                'vehicleLongness'=>$this->input->post('vehicleLongness'),
                'vehicleType'=>$this->input->post('vehicleType'),
                'beginPosition'=>$this->input->post('beginPosition'),
                'beginProvince'=>$this->input->post('beginProvince'),
                'beginCity'=>$this->input->post('beginCity'),
                'beginDistrict'=>$this->input->post('beginDistrict'),
                'beginStreet'=>$this->input->post('beginStreet'),
                'endPosition'=>$this->input->post('endPosition'),
                'endProvince'=>$this->input->post('endProvince'),
                'endCity'=>$this->input->post('endCity'),
                'endDistrict'=>$this->input->post('endDistrict'),
                'endStreet'=>$this->input->post('endStreet'),
                'distance'=>$this->input->post('distance'),
                'expenses'=>$this->input->post('expenses'),
                'covering'=>$this->input->post('covering'),
                'cubage'=>$this->input->post('cubage'),
                'weight'=>$this->input->post('weight'),
                'goodsType'=>$this->input->post('goodsType'),
                'screen'=>$filesStr,
                'descib'=>$this->input->post('descib'),
                'phone'=>$this->input->post('phone'),
                'recordStatus'=>0,
                'publishUser'=>$this->session->userID
                );

            //使用用查询构造器写入数据库并返回执行结果成功与否
            $result=$this->db->insert('freights',$record);
            if(!$result)
            {
                throw new Exception("写入数据库出错：".$this->db->error());
            }

            return array(true,'');
        }
        catch (Exception $e)
        {
            //遇到错误取消操作，删除已上传文件
            //因为是单表数据所以不必使用事务回滚
            foreach ($filesArray as $fileName)
            {
                unlink(FCPATH.$uploadPath.$fileName);
            }
            return array(false,$e);
        }
    }

    //按出发地城市检索可接单货源数据
    public function getAllowOrderFreights($beginProvince='',$beginCity='')
    {
        $query=$this->db->select('freightID,beginProvince,beginCity,endProvince,endCity,vehicleLongness,longnessUnit,vehicleType,weight,publishTime');
        if($beginProvince!=''){$query=$query->where('beginProvince',$beginProvince);}
        if($beginCity!=''){$query=$query->where('beginCity',$beginCity);}
        $query=$query->where('recordStatus',0)
                        ->order_by('freightID','desc')
                        ->get('v_freights');
        return $query->result();
    }

    //检索用户货源数据
    public function getUserFreights($owner='',$freightStatus='')
    {
        $query=$this->db->select('aID as freightID,beginProvince,beginCity,endProvince,endCity,publishTime,recordStatus');
        $query=$query->where('publishUser',$owner);
        //数据库字段recordStatus=0或1为进行中
        if($freightStatus=='进行中'){
            $query=$query->where('recordStatus>',-1)->where('recordStatus<',2);
        }//数据库字段recordStatus=-1或2为已完成
        elseif($freightStatus=='已完成'){
            $query=$query->where('recordStatus<>',0)->where('recordStatus<>',1);
        }
        $query=$query->order_by('freightID','desc')->get('freights');
        return $query->result();
    }

    //货源详情信息
    public function getFreightDetail($freightID)
    {
        $query=$this->db->select('freightID,beginPosition,beginProvince,beginCity,beginDistrict,endPosition,endProvince,endCity,endDistrict,vehicleLongness,longnessUnit,vehicleType,weight,cubage,distance,goodsType,expenses,recordStatus,publishUser,vehicle,franchiserUser,mobileNumber')
            ->where('freightID',$freightID)
            ->get('v_freights');
        return $query->row();
    }

}