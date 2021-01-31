<?php

namespace IfWrong\ParamsIO;

use Illuminate\Support\Facades\Validator;

trait ValidateParamsIO
{
    /**
     * 验证器
     * @var Validator
     */
    protected $validator = null;
    /**
     * validator验证规则
     * @var array
     */
    protected $rules = [];

    /**
     * validator错误信息
     * @var array
     */
    protected $messages = [];

    /**
     * 入餐错误
     * @return array
     */
    public function errors()
    {
        $result = [];
        $validator = $this->getValidator();
        if ($validator->fails()) {
            $result = $validator->errors();
        }
        return $result;
    }

    /**
     * 生成验证器
     * @return \Illuminate\Contracts\Validation\Validator|Validator
     */
    public function getValidator()
    {
        $validator = $this->validator;
        if (is_null($validator)) {
            $validator = $this->validator = Validator::make($this->getData(), $this->getRules(), $this->getMessages());
        }
        return $validator;
    }

    /**
     * 获取待验证数据
     * @return mixed
     */
    abstract public function getData();

    /**
     * 实现获取验证规则
     * @return mixed
     */
    abstract public function getRules();

    /**
     * 获取消息
     * @return mixed
     */
    abstract public function getMessages();

    /**
     * 验证是否成功
     * @return bool
     */
    public function fails()
    {
        return $this->getValidator()->fails();
    }
}
