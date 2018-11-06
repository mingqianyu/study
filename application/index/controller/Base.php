<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/28
 * Time: 上午11:26
 */

namespace app\index\controller;

use think\App;
use think\response\Json;
use think\Controller;

class Base extends Controller
{
    protected $errorCode;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->errorCode =  Config('errorcode.');
    }

    /**
     * 成功
     * @param $data
     * @param $msg
     * @return  Json
     */
    protected function successJson($data = [], $msg = '')
    {
        $message = '请求成功！';
        if (!empty($msg)) {
            $message = $msg;
        }
        return json(['success' => 1, 'code' => '', 'msg' => $message, 'data' => $data]);
    }

    /**
     * 失败
     * @param integer $code
     * @param integer  $errorCode
     * @param string $msg
     * @return Json
     */
    protected function failedJson($code, $errorCode, $msg = '')
    {
        $message = $this->errorCode[$errorCode];
        if (!empty($msg)) {
           $message = $msg;
        }
        return json(['success' => 0, 'code' => $errorCode, 'msg' => $message, 'data' => []], $code);
    }
}