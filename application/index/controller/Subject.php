<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/29
 * Time: 上午11:43
 */

namespace app\index\controller;

use think\Db;
use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Subject extends Base
{
    /**
     * 学科列表
     * @return \think\response\Json
     */
    public function index()
    {
        try {
            $userInfo = getUserInfoByToken(Request::header('X-Token'));
            if (empty($userInfo)) {
                return $this->failedJson(403, 1003);
            }

            if ($userInfo['role'] == 'student') {
                return $this->failedJson(403, 1001);
            }

            $subjects = Db('Subject', [], false)->order('id', 'desc')->select();
            if (empty($subjects)) {
                $subjects = [];
            }
            return $this->successJson($subjects);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 可用的学科列表
     * @return \think\response\Json
     */
    public function validSubjects()
    {
        try {
            $userInfo = getUserInfoByToken(Request::header('X-Token'));
            if (empty($userInfo)) {
                return $this->failedJson(403, 1003);
            }
            if ($userInfo['role'] == 'student') {
                return $this->failedJson(403, 1001);
            }
            $subjects = Db('Subject', [], false)->where('status', 1)->order('name', 'asc')->select();
            if (empty($subjects)) {
                $subjects = [];
            }
            return $this->successJson($subjects);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * @return \think\response\Json
     */
    public function addSubject()
    {
        //先判断是否有权限
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'admin') {
            return $this->failedJson(403, 1001);
        }

        //获取参数 /^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u
        $code = input('?post.code') ? input('post.code') : '';
        $name = input('?post.name') ? input('post.name') : '';
        $status = input('?post.status') ? input('post.status') : '';

        //判断参数是否合法
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u', $name) || !preg_match('/^[a-zA-Z0-9]{1,10}$/', $code) || !is_numeric($status) || $status > 1 || $status < 0)
        {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Subject', [], false)->where('name', $name)->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1201);
            }

            //判断 code 是否存在
            $codeCount = Db('Subject', [], false)->where('code', $code)->count();
            if ($codeCount > 0) {
                return $this->failedJson(409, 1200);
            }

            $insertData = [
                'name' => $name,
                'code' => $code,
                'status' => $status
            ];
            $result = Db('Subject', [], false)->insert($insertData);
            if (empty($result)) {
                return $this->failedJson(500, 1202);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 编辑学科
     * @return \think\response\Json
     */
    public function editSubject()
    {
        //先判断是否有权限
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'admin') {
            return $this->failedJson(403, 1001);
        }

        //获取参数 /^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u
        $id = input('?post.id') ? input('post.id') : '';
        $code = input('?post.code') ? input('post.code') : '';
        $name = input('?post.name') ? input('post.name') : '';
        $status = input('?post.status') ? input('post.status') : '';

        //判断参数是否合法
        if (
            empty($id) || !is_numeric($id) || $id <= 0
            ||!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u', $name)
            || !preg_match('/^[a-zA-Z0-9]{1,10}$/', $code)
            || !is_numeric($status) || $status > 1 || $status < 0
        )
        {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Subject', [], false)->where([['name', '=', $name], ['id', '<>', $id]])->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1201);
            }

            //判断 code 是否存在
            $codeCount = Db('Subject', [], false)->where([['code', '=', $code], ['id', '<>', $id]])->count();
            if ($codeCount > 0) {
                return $this->failedJson(409, 1200);
            }

            $editData = [
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'status' => $status
            ];
            $result = Db('Subject', [], false)->update($editData);
            if (empty($result)) {
                return $this->failedJson(500, 1203);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除学科
     * @return \think\response\Json
     */
    public function deleteSubject()
    {
        //先判断是否有权限
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'admin') {
            return $this->failedJson(403, 1001);
        }

        //获取参数 /^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u
        $id = input('?post.id') ? input('post.id') : '';

        //判断参数是否合法
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //检测学科是否在使用
            $classCount = Db('Class', [], false)->where('subject_id', $id)->count();
            $courseCount = Db('Course', [], false)->where('subject_id', $id)->count();
            $courseStageCount = Db('CourseStage', [], false)->where('subject_id', $id)->count();
            $knowledgeCount = Db('Knowledge', [], false)->where('subject_ids', 'like', '%[' . $id .']%')->count();
            if ($classCount > 0 || $courseCount > 0 || $courseStageCount > 0 || $knowledgeCount > 0) {
                return $this->failedJson(403, 1204);
            }

            $result = Db('Subject', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1205);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }
}