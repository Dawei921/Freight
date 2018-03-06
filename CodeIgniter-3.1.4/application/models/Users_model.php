<?php

/**
 * Class Users_model
 * 用户信息
 */

class Users_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        //连接数据库 -- 从配置文件
        $this->load->database();
        $this->load->library('upload');

        //加载自写类
        $this->load->model('public_model');
    }

    //注册用户 -- 成功返回aID，否则返回false
    public function register()
    {
        try
        {
            //先上传文件获取文件名再存入数据库
            //文件名使用users表aID+序号.扩展名
            //此设置默认会在已存在文件时自动在文件名后加一个数字来存储
            $aID=$this->public_model->get_auto_increment('users');
            $uploadPath='upload/users/';
            $uploadConf=array(
                'upload_path'=>FCPATH.$uploadPath,
                'allowed_types'=>'*',
                'file_name'=>$aID.'_'
                );
            $this->upload->initialize($uploadConf);
            //如果是身份证类型
            if($this->input->post('credentialType')==1) {
                $result = $this->upload->do_upload('photo1');
                if(!$result)
                {
                    throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
                }
                $file1 = $uploadPath . $this->upload->data('file_name');

                $result = $result && $this->upload->do_upload('photo2');
                if(!$result)
                {
                    throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
                }
                $file2 = $uploadPath . $this->upload->data('file_name');

                $result = $result && $this->upload->do_upload('photo3');
                if(!$result)
                {
                    throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
                }
                $file3 = $uploadPath . $this->upload->data('file_name');

                $files=$file1.",".$file2.",".$file3;
            }//否则是营业执照类型
            else{
                $result = $this->upload->do_upload('photo1');
                if(!$result)
                {
                    throw new Exception('文件上传遇到错误：'.$this->upload->display_errors());
                }
                $file1 = $uploadPath . $this->upload->data('file_name');

                $files=$file1;
            }

            //从输入类里取出表单数据 -- 在Controller里已验证通过
            //注意上传文件使用相对路径名存入数据库，这样在取出后要再加 base_url() 前缀
            $data=array(
                'credentialType'=>$this->input->post('credentialType'),
                'credentialNumber'=>$this->input->post('credentialNumber'),
                'fullName'=>$this->input->post('fullName'),
                'mobileNumber'=>$this->input->post('mobileNumber'),
                'encryptPassword'=>$this->input->post('userPassword'),
                'credentialPhotos'=>$files
                );
            //使用用查询构造器写入数据库并返回执行结果成功与否
            $result=$this->db->insert('users',$data);
            if(!$result){
                throw new Exception('写入数据库出错：'.$this->db->error());
            }

            return array(true,$aID);
        }
        catch (Exception $e)
        {
            //遇到错误取消操作，删除已上传文件
            if(isset($file1)){unlink(FCPATH.$uploadPath.$file1);}
            if(isset($file2)){unlink(FCPATH.$uploadPath.$file2);}
            if(isset($file3)){unlink(FCPATH.$uploadPath.$file3);}

            return array(false,$e);
        }
    }

    //注册用户列表
    public function getRegisterUsers(){
        return $this->db->select('userID,fullName,credentialType,credentialNumber')->where('recordStatus',0)->get('v_users')->result();
    }

    //取得用户信息
    public function getUserInfo($userID){
        return $this->db->select('userID,fullName,credentialType,credentialNumber,credentialPhotos,mobileNumber,drivingLicenseID,drivingLicense,recordStatus')->where('userID',$userID)->get('v_users')->row();
    }

    //用户注册信息审核
    public function userVerify($userID,$verify,$reason){
        try{
            $data=array(
                'recordStatus'=>$verify,
                'reason'=>$reason
            );
            $result=$this->db->where('aID',$userID)->update('users',$data);
            if(!$result){
                throw new Exception('写入数据库出错：'.$this->db->error());
            }
            if($this->db->affected_rows()==0){
                throw new Exception('未能更新，没有匹配的用户数据。');
            }

            return array(true,'');
        }
        catch(Exception $e){
           return array(false,$e);
        }
    }

    //用户更改密码
    public function changePassword($user,$password){
        try{
            $result=$this->db->where('credentialNumber',$user)->update('users',array('encryptPassword'=>$password));
            if(!$result){
                throw new Exception('写入数据库出错：'.$this->db->error());
            }
            if($this->db->affected_rows()==0){
                throw new Exception('未能更新，没有匹配的用户数据。');
            }

            return array(true,'');
        }
        catch (Exception $e){
            return array(false,$e);
        }
    }

    //credentialNumber是否已存在于users表
    //若存在则返回aID（auto_increment）字段
    //若不存在则势必返回为空
    public function isExistUser($credentialNumber,$password)
    {
        //为空则忽略$password参数
        if($password=="")
        {
            $result=$this->db->select('aID')->where('credentialNumber',$credentialNumber)->get('users')->num_rows();
        }
        else
        {
            $result=$this->db->select('aID,fullName,vehicleCount')->where(array('credentialNumber'=>$credentialNumber,'encryptPassword'=>$password))->get('users')->row();
        }
        return $result;
    }

    //取得证件类型表
    public function getcredentialType()
    {
        $query=$this->db->select('aID,typeName')->get('credential_types');
        return $query->result();
    }

}