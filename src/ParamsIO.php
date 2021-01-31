<?php

namespace IfWrong\ParamsIO;

use IfWrong\ParamsIO\ValidateParamsIO;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 类参数IO
 * Class ParamsIO
 *
 */
class ParamsIO
{
    use ValidateParamsIO;

    /**
     * 日志tag
     *
     * @var string
     */
    protected $logTag = '';

    /**
     * 自定义mget忽略属性
     *
     * @var array
     */
    protected $except = [
        'camelKey',
        'greedy',
        'logTag',
        'except',
        'validator',
        'rules',
        'messages',
    ];

    /**
     * 初始化的时候没有定义的属性是否保存
     * @var bool
     */
    protected $greedy = false;

    /**
     * 将字段转化为驼峰形式
     * @var bool
     */
    protected $camelKey = false;

    /**
     * ParamsIO constructor.
     *
     * @param null|self|array $props
     * @param $logTag
     */
    public function __construct($props = null, $logTag = '')
    {
        if ($logTag) {
            $this->logTag = $logTag;
        }
        if ($props) {
            $src = $this->mget();
            if ($this->greedy) {
                $dst = is_array($props) ? $props : $props->mget();
            } else {
                $keys = array_keys($src);
                if (is_array($props)) {
                    $dst = Arr::only($props, $keys);
                } else {
                    $dst = $props->mget($keys);
                }
            }
            $this->mset($dst, $this->camelKey);
        }
    }

    /**
     * 批量获取属性
     *
     * @param array $keys
     * @return array
     */
    public function mget($keys = [], $except = [])
    {
        $except = array_merge($this->except, $except);
        $vals = [];
        if (is_array($keys)) {
            if (!$keys) {
                $keys = array_keys(get_object_vars($this));
            }
            $keys = array_diff($keys, $except);
            foreach ($keys as $key) {
                $vals[$key] = $this->getAttr($key);
            }
        }
        return $vals;
    }

    /**
     * 获取属性
     *
     * @param  $key
     * @return array|mixed
     */
    protected function getAttr($key)
    {
        $method = sprintf('get%sAttr', ucfirst($key));
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return data_get($this, $key);
        }
    }

    /**
     * 批量设置属性
     *
     * @param array $vals
     * @param bool $camelKey
     */
    public function mset($vals = [], $camelKey = true)
    {
        if (is_array($vals)) {
            if ($camelKey) {
                $vals = $this->camelKeys($vals);
            }
            foreach ($vals as $key => $val) {
                data_set($this, $key, $val);
            }
        }
    }

    /**
     * 将key设置为驼峰类型
     * @param array $vals
     * @return array
     */
    public function camelKeys($vals = [])
    {
        $newVals = [];
        foreach ($vals as $key => $val) {
            $newKey = Str::camel($key);
            $newVals[$newKey] = $val;
        }
        return $newVals;
    }

    /**
     * 输入参数打印
     *
     * @param array $input
     * @param string $message
     */
    public function in($input = [], $message = 'In')
    {
        if ($input) {
            $input = is_array($input) ? $input : [$input];
        } else {
            $input = $this->mget();
        }
        $this->info($message, $input);
    }

    /**
     * 打印日志
     *
     * @param $message
     * @param array $context
     */
    public function info($message, $context = [])
    {
        $context = is_array($context) ? $context : [$context];
        Log::info($this->logTag . '|' . $message, $context);
    }

    /**
     * 输出参数打印
     *
     * @param array $output
     * @param string $message
     * @return array[]|bool
     */
    public function out($output = [], $message = 'Out')
    {
        $output = is_array($output) ? $output : [$output];
        $this->info($message, $output);
        return $output;
    }

    /**
     * 返回待验证数据
     *
     * @return array
     */
    public function getData()
    {
        return $this->mget();
    }

    /**
     * 获取验证规则
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * 获取验证消息
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param  $key
     * @return array|mixed
     */
    public function __get($key)
    {
        return $this->getAttr($key);
    }

    /**
     * @param $key
     * @param $val
     */
    public function __set($key, $val)
    {
        data_set($this, $key, $val);
    }
}
