<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Because CI can't validate file input, So create it, Then can use * within allowed_types when setting upload parameters
 */
class File_validation
{
    public function __construct(){}

    private $validationArray=array();   //验证对象数组
    private $validationErrors=array();  //验证未通过对象数组

    //设置规则 -- 拆分出每一个规则并绑定到验证对象
    public function set_rules($field,$label='',$rules='')
    {
        //如果有多个验证对象
        if(is_array($field)) {
            foreach ($field as $rulesObj) {
                $this->splitRules($rulesObj);
            }
        } //否则只有一个验证对象
        else {
            $rulesObj = array('field' => $field, 'label' => $label, 'rules' => $rules);
            $this->splitRules($rulesObj);
        }
    }

    /**
     * 将验证对象的多个规则分割绑定每一个规则
     * @param $obj=array('field'=>$field,'label'=>$label,'rules'=>$rule)
     */
    private function splitRules($obj){
        $arrayRule = explode('|', $obj['rules']);
        //如果验证对象有多个验证规则
        if (is_array($arrayRule)) {
            foreach ($arrayRule as $rule) {
                $this->validationArray[] = array('field' => $obj['field'], 'label' => $obj['label'], 'rules' => $rule);
            }
        } //如果验证对象只有一个验证规则
        else {
            $this->validationArray[] = $obj;
        }
    }

    public function run()
    {
        $result=true;   //默认返回验证结果为真 -- 通过验证
        foreach($this->validationArray as $validation)
        {
            $result=$this->validate($validation) && $result;
        }
        return $result;
    }

    /**
     * 验证单个规则对象
     * @param $obj=array('field'=>$field,'label'=>$label,'rules'=>$rule)
     * @return bool
     *
     */
    private function validate($obj)
    {
        $result=true;   //默认返回验证结果为真 -- 通过验证
        $field=$obj['field'];
        $rule=$obj['rules'];

        //如果有参数则取出来放入数组 -- [param1,param2] 为参数标识，多个参数用,分隔
        $length=strlen($rule);
        $position=stripos($rule,'[');
        if($position)
        {
            $str=substr($rule,$position+1,$length-$position-2); //注意顺序 -- 因为下面要改变$rule值所以先运算
            $rule=substr($rule,0,$position);
            $parameters=explode(',',$str);
        }

        switch($rule)
        {
            case 'required':
                //如果上传的是多个文件
                if(is_array($_FILES[$field]['size']))
                {
                    foreach ($_FILES[$field]['size'] as $size)
                    {
                        if($size==0)
                        {
                            $this->validationErrors[$field]=$obj['label'].'：上传的每一个文件大小必须>0！';
                            $result=false;
                            break;  //此为跳出foreach
                        }
                    }
                }
                //否则上传的是单个文件：
                else
                {
                    //如果上传文件大小为0 -- 即未上传文件
                    if($_FILES[$field]['size']==0)
                    {
                        $this->validationErrors[$field]='必须上传 '.$obj['label'];
                        $result=false;
                    }
                }
                break;
            case 'file_type':
                //如果上传的是多个文件
                if(is_array($_FILES[$field]['name']))
                {
                    foreach ($_FILES[$field]['name'] as $key=>$value)
                    {
                        //如果有上传文件则验证 -- 文件大小>0
                        if($_FILES[$field]['size'][$key]>0)
                        {
                            $result=$this->validateFormat($value,$parameters);
                            if(!$result)
                            {
                                $this->validationErrors[$field]=$obj['label']. '：上传的每一个文件必须是 '.$str. ' 格式';
                                break;  //此为跳出foreach
                            }
                        }
                    }
                }
                //否则上传的是单个文件：
                else
                {
                    //如果有上传文件则作验证 -- 文件大小>0
                    if($_FILES[$field]['size']>0)
                    {
                        $result=$this->validateFormat($_FILES[$field]['name'],$parameters);
                        if(!$result)
                        {
                            $this->validationErrors[$field]=$obj['label']. ' 必须是 '.$str. ' 格式';
                        }
                    }
                }
                break;
        }
        return $result;
    }

    private function validateFormat($fileName,$allowFormat)
    {
        $result=true;

        //获得上传文件后缀名
        $strArray=explode('.',$fileName);
        $ext=end($strArray);
        //如果规则是多种文件格式
        if(is_array($allowFormat))
        {
            if(!in_array($ext,$allowFormat))
            {
                $result=false;
            }
        }
        //否则只有一个格式 -- 直接判断后缀名是否相等
        else
        {
            if($ext!=$allowFormat)
            {
                $result=false;
            }
        }
        return $result;
    }


    public function file_validation_errors()
    {
        $errors='';
        foreach($this->validationErrors as $error)
        {
            $errors.= "<p>$error</p>";
        }
        return $errors;
    }
}