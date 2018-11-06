<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/29
 * Time: 下午3:20
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Knowledge extends Base
{
    /**
     * 获取所有知识点列表
     * @return \think\response\Json
     */
    public function index()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $subjectId = input("?get.subjectId") ? input("get.subjectId") : 0;

        $name = input('?get.name') ? input('get.name') : '';

        $page = input("?get.page") ? input("get.page") : 0;

        $pageSize = input("?get.pageSize") ? input("get.pageSize") : 0;

        if (!is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0 || !is_numeric($subjectId) || $subjectId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取总数
            $countDb = Db('Knowledge', [], false);

            if ($subjectId > 0) {
                $countDb = $countDb->where('subject_ids', 'like', '%[' . $subjectId . ']%');
            }

            if (!empty($name)) {
                $countDb = $countDb->where('name', 'like', '%' . $name . '%');
            }

            $count = $countDb->count();

            //获取数据
            $db = Db('Knowledge', [], false);

            if ($subjectId > 0) {
                $db = $db->where('subject_ids', 'like', '%[' . $subjectId . ']%');
            }

            if (!empty($name)) {
                $db = $db->where('name', 'like', '%' . $name . '%');
            }


            $db->order('id', 'desc');

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $knowledgeList = $db->select();
            if (empty($knowledgeList)) {
                $knowledgeList = array();
            } else {
                foreach ($knowledgeList as &$knowledge) {
                    $knowledge['subject_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $knowledge['subject_ids'])));
                    if ($knowledge['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $knowledge['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $knowledge['createName'] = $createUser['name'];
                        } else {
                            $knowledge['createName'] = '';
                        }
                    } else {
                        $knowledge['createName'] = '';
                    }

                    if ($knowledge['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $knowledge['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $knowledge['editName'] = $editUser['name'];
                        } else {
                            $knowledge['editName'] = '';
                        }
                    } else {
                        $knowledge['editName'] = '';
                    }
                }
            }

            return $this->successJson(['totalCount' => $count, 'currentPage' => $page, 'pageSize' => $pageSize, 'knowledgeList' => $knowledgeList]);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 获取有效的知识点
     * @return \think\response\Json
     */
    public function validKnowledge()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $subjectId = input("?get.subjectId") ? input("get.subjectId") : 0;

        $name = input('?get.name') ? input('get.name') : '';

        if (!is_numeric($subjectId) || $subjectId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            $map[] = ['status', '=', 1];
            if ($subjectId > 0) {
                $map[] = ['subject_ids', 'like', '%[' . $subjectId . ']%'];
            }

            if (!empty($name)) {
                $map[] = ['name', 'like', '%' . $name . '%'];
            }

            $knowledgeList = Db('Knowledge', [], false)->where($map)->order('name', 'asc')->select();
            if (empty($knowledgeList)) {
                $knowledgeList = array();
            } else {
                foreach ($knowledgeList as &$knowledge) {
                    $knowledge['subject_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $knowledge['subject_ids'])));
                    if ($knowledge['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $knowledge['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $knowledge['createName'] = $createUser['name'];
                        } else {
                            $knowledge['createName'] = '';
                        }
                    } else {
                        $knowledge['createName'] = '';
                    }

                    if ($knowledge['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $knowledge['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $knowledge['editName'] = $editUser['name'];
                        } else {
                            $knowledge['editName'] = '';
                        }
                    } else {
                        $knowledge['editName'] = '';
                    }
                }
            }
            return $this->successJson($knowledgeList);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 添加知识点
     * @return \think\response\Json
     */
    public function addKnowledge()
    {
        //获取当前用户的信息
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $name = input('?post.name') ? input('post.name') : '';
        $subjectIds = input('?post.subjectIds') ? input('post.subjectIds/a') : '';
        Log::info(json_encode($subjectIds));
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            empty($name) || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,200}$/u', $name)
            || !checkValidIds($subjectIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Knowledge', [], false)->where('name', $name)->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1300);
            }

            $insertData = [
                'name' => htmlspecialchars($name, ENT_QUOTES),
                'subject_ids' => createIdStr($subjectIds),
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'status' => $status
            ];
            $result = Db('Knowledge', [], false)->insert($insertData);
            if (empty($result)) {
                return $this->failedJson(500, 1301);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 编辑知识点
     * @return \think\response\Json
     */
    public function editKnowledge()
    {
        //获取当前用户的信息
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input('?post.id') ? input('post.id') : '';
        $name = input('?post.name') ? input('post.name') : '';
        $subjectIds = input('?post.subjectIds') ? input('post.subjectIds/a') : '';
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            empty($id) || !is_numeric($id) || $id < 0
            || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,200}$/u', $name)
            || !checkValidIds($subjectIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Knowledge', [], false)->where([['name', '=', $name], ['id', '<>', $id]])->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1300);
            }

            $editData = [
                'id'            => $id,
                'name'          => $name,
                'subject_ids'    => createIdStr($subjectIds),
                'edit_user_id'  => $userInfo['id'],
                'edit_time'     => time(),
                'status'        => $status
            ];

            $result = Db('Knowledge', [], false)->update($editData);
            if (empty($result)) {
                return $this->failedJson(500, 1302);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除知识点
     * @return \think\response\Json
     */
    public function deleteKnowledge()
    {
        //先判断是否有权限删除
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数 /^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{1,20}$/u
        $id = input('?post.id') ? input('post.id') : '';

        //判断参数是否合法
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //检测知识点是否在使用
            $questionCount = Db('Question', [], false)->where('knowledge_id', $id)->count();
            $courseStages = Db('CourseStage', [], false)->where('knowledge_ids', 'like','%[' . $id . ']%')->count();


            if ($questionCount > 0 || $courseStages > 0) {
                return $this->failedJson(403, 1303);
            }

            $result = Db('Knowledge', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1304);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }
}