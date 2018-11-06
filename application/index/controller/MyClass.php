<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/29
 * Time: 上午10:48
 */

namespace app\index\controller;


use think\Exception;
use think\facade\Request;

class MyClass extends Base
{
    public function index()
    {
        //获取参数检测参数是否有效
        $userId = input('?post.userId') ? input('post.userId') : 0;

        $className = input('?post.className') ? input('post.className') : '';

        if (empty($userId) || !is_numeric($userId) || $userId <= 0) {
            return $this->failedJson(400, 1000);
        }




        //获取班级列表
        try {
            $user = getUserInfoByToken(Request::header('X-Token'));
            if (empty($user)) {
                return $this->failedJson(403, 1003);
            }
            if ($user['role'] == 'admin') {
                $classList = Db('Class', [], false)->where('status', 1)->select();
            } else {
                $classList = Db('Class', [], false)->alias('c')
                    ->field('c.id, c.name, c.subject_id, c.open_date, c.graduate_date, c.status')
                    ->leftJoin('ClassUser cu', 'c.id = cu.class_id')
                    ->where([['cu.status', '=', 1], ['c.status', '=', 1], ['cu.user_id', '=', $userId]]);
                if (!empty($className)) {
                    $classList = $classList->where('name', 'like', '%' . $className . '%');
                }
                $classList = $classList->group('c.id')
                    ->order('c.open_date', 'desc')
                    ->select();
            }

            if (empty($classList)) {
                $classList = array();
            }
            return $this->successJson($classList);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }
}