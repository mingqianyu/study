<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/29
 * Time: 下午5:01
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class CourseStage extends Base
{
    /**
     * 获取所有课程阶段列表
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
            $countDB = Db('CourseStage', [], false);

            if ($subjectId > 0) {
                $countDB = $countDB->where('subject_id', $subjectId);
            }

            if (!empty($name)) {
                $countDB = $countDB->where('name', 'like', '%' . $name . '%');
            }

            $count = $countDB->count();

            $db = Db('CourseStage', [], false);

            if ($subjectId > 0) {
                $db = $db->where('subject_id', $subjectId);
            }

            if (!empty($name)) {
                $db = $db->where('name', 'like', '%' . $name . '%');
            }

            $db->order('id', 'desc');

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $courseStageList = $db->select();

            if (empty($courseStageList)) {
                $courseStageList = array();
            } else {
                foreach ($courseStageList as &$courseStage) {
                    if ($courseStage['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $courseStage['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $courseStage['createName'] = $createUser['name'];
                        } else {
                            $courseStage['createName'] = '';
                        }
                    } else {
                        $courseStage['createName'] = '';
                    }

                    if ($courseStage['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $courseStage['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $courseStage['editName'] = $editUser['name'];
                        } else {
                            $courseStage['editName'] = '';
                        }
                    } else {
                        $courseStage['editName'] = '';
                    }

                    if (!empty($courseStage['knowledge_ids'])) {
                        $ids = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                        $nameArray = array();
                        foreach ($ids as $id) {
                            $knowledge = Db('Knowledge', [], false)->where('id', $id)->find();
                            if (!empty($knowledge)) {
                                $nameArray[] = $knowledge['name'];
                            } else {
                                $nameArray[] = '';
                            }
                        }
                        $courseStage['knowledge_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                        $courseStage['knowledge_names'] = $nameArray;
                    } else {
                        $courseStage['knowledge_ids'] = array();
                        $courseStage['knowledge_names'] = array();
                    }
                }
            }
            return $this->successJson(['totalCount' => $count, 'page' => $page, 'pageSize' => $pageSize, 'courseStageList' => $courseStageList]);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 根据 id 获取课程阶段
     * @return \think\response\Json
     */
    public function getCourseStageById()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input("?get.id") ? input("get.id") : 0;


        if (!is_numeric($id) || $id < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            $courseStage = Db('CourseStage', [], false)->where('id', $id)->find();
            if (empty($courseStage)) {
                return $this->failedJson(404, 1405);
            } else {
                if (!empty($courseStage['knowledge_ids'])) {
                    $ids = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                    $nameArray = array();
                    foreach ($ids as $id) {
                        $knowledge = Db('Knowledge', [], false)->where('id', $id)->find();
                        if (!empty($knowledge)) {
                            $nameArray[] = $knowledge['name'];
                        } else {
                            $nameArray[] = '';
                        }
                    }
                    $courseStage['knowledge_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                    $courseStage['knowledge_names'] = $nameArray;
                } else {
                    $courseStage['knowledge_ids'] = array();
                    $courseStage['knowledge_names'] = array();
                }
            }
            return $this->successJson($courseStage);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 获取有效的课程阶段
     * @return \think\response\Json
     */
    public function validCourseStages()
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
                $map[] = ['subject_id', '=',  $subjectId];
            }

            if (!empty($name)) {
                $map[] = ['name', 'like', '%' . $name . '%'];
            }

            $courseStageList = Db('CourseStage', [], false)->where($map)->select();
            if (empty($courseStageList)) {
                $courseStageList = array();
            } else {
                foreach ($courseStageList as &$courseStage) {
                    if ($courseStage['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $courseStage['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $courseStage['createName'] = $createUser['name'];
                        } else {
                            $courseStage['createName'] = '';
                        }
                    } else {
                        $courseStage['createName'] = '';
                    }

                    if ($courseStage['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $courseStage['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $courseStage['editName'] = $editUser['name'];
                        } else {
                            $courseStage['editName'] = '';
                        }
                    } else {
                        $courseStage['editName'] = '';
                    }

                    if (!empty($courseStage['knowledge_ids'])) {
                        $ids = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                        $nameArray = array();
                        foreach ($ids as $id) {
                            $knowledge = Db('Knowledge', [], false)->where('id', $id)->find();
                            if (!empty($knowledge)) {
                                $nameArray[] = $knowledge['name'];
                            } else {
                                $nameArray[] = '';
                            }
                        }
                        $courseStage['knowledge_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                        $courseStage['knowledge_names'] = $nameArray;
                    } else {
                        $courseStage['knowledge_ids'] = array();
                        $courseStage['knowledge_names'] = array();
                    }
                }
            }
            return $this->successJson($courseStageList);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 添加课程阶段
     * @return \think\response\Json
     */
    public function addCourseStage()
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
        $subjectId = input('?post.subjectId') ? input('post.subjectId') : '';
        $knowledgeIds = input('?post.knowledgeIds') ? input('post.knowledgeIds/a') : array();
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $name)
            || empty($subjectId) || !is_numeric($subjectId) || $subjectId < 0
            || !is_array($knowledgeIds) || empty($knowledgeIds) || !checkValidIds($knowledgeIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('CourseStage', [], false)->where('name', $name)->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1400);
            }

            $insertData = [
                'name' => $name,
                'subject_id' => $subjectId,
                'knowledge_ids' => createIdStr($knowledgeIds),
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'status' => $status
            ];
            $result = Db('CourseStage', [], false)->insert($insertData);
            if (empty($result)) {
                return $this->failedJson(500, 1401);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 编辑课程阶段
     * @return \think\response\Json
     */
    public function editCourseStage()
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
        $subjectId = input('?post.subjectId') ? input('post.subjectId') : '';
        $knowledgeIds = input('?post.knowledgeIds') ? input('post.knowledgeIds/a') : array();
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            empty($id) || !is_numeric($id) || $id < 0
            || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $name)
            || empty($subjectId) || !is_numeric($subjectId) || $subjectId < 0
            || !is_array($knowledgeIds) || empty($knowledgeIds) || !checkValidIds($knowledgeIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('CourseStage', [], false)->where([['name', '=', $name], ['id', '<>', $id]])->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1400);
            }

            $editData = [
                'id'            => $id,
                'name'          => $name,
                'subject_id'    => $subjectId,
                'knowledge_ids' => createIdStr($knowledgeIds),
                'edit_user_id'  => $userInfo['id'],
                'edit_time'     => time(),
                'status'        => $status
            ];

            $result = Db('CourseStage', [], false)->update($editData);

            if (empty($result)) {
                return $this->failedJson(500, 1402);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除课程阶段
     * @return \think\response\Json
     */
    public function deleteCourseStage()
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
            //检测课程阶段是否在使用
            $courseCount = Db('Course', [], false)->where('course_stage_ids', 'like','%[' . $id . ']%')->count();


            if ($courseCount > 0) {
                return $this->failedJson(403, 1403);
            }

            $result = Db('CourseStage', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1404);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }
}