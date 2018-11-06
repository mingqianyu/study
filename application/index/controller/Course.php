<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/10
 * Time: 下午12:02
 */

namespace app\index\controller;


use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Course extends Base
{
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

        $page = input("?get.page") ? input("get.page") : 0;

        $pageSize = input("?get.pageSize") ? input("get.pageSize") : 0;

        if (!is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0 || !is_numeric($subjectId) || $subjectId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取总数
            $countDB = Db('Course', [], false);

            if ($subjectId > 0) {
                $countDB = $countDB->where('subject_id', $subjectId);
            }

            $count = $countDB->count();

            $db = Db('Course', [], false);

            if ($subjectId > 0) {
                $db = $db->where('subject_id', $subjectId);
            }

            $db->order('id', 'desc');

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $courseList = $db->select();

            if (empty($courseList)) {
                $courseList = array();
            } else {
                foreach ($courseList as &$course) {
                    if ($course['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $course['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $course['createName'] = $createUser['name'];
                        } else {
                            $course['createName'] = '';
                        }
                    } else {
                        $course['createName'] = '';
                    }

                    if ($course['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $course['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $course['editName'] = $editUser['name'];
                        } else {
                            $course['editName'] = '';
                        }
                    } else {
                        $course['editName'] = '';
                    }

                    if (!empty($course['course_stage_ids'])) {
                        $ids = explode('|', str_replace(']', '', str_replace('[', '', $course['course_stage_ids'])));
                        $nameArray = array();
                        foreach ($ids as $id) {
                            $courseStage = Db('CourseStage', [], false)->where('id', $id)->find();
                            if (!empty($courseStage)) {
                                $nameArray[] = $courseStage['name'];
                            } else {
                                $nameArray[] = '';
                            }
                        }
                        $course['course_stage_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $course['course_stage_ids'])));
                        $course['course_stage_names'] = $nameArray;
                    } else {
                        $course['course_stage_ids'] = '';
                        $course['course_stage_names'] = '';
                    }
                }
            }
            return $this->successJson(['totalCount' => $count, 'page' => $page, 'pageSize' => $pageSize, 'courseList' => $courseList]);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    public function validCourses()
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

        $page = input("?get.page") ? input("get.page") : 0;

        $pageSize = input("?get.pageSize") ? input("get.pageSize") : 0;

        if (!is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0 || !is_numeric($subjectId) || $subjectId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            $db = Db('Course', [], false);

            if ($subjectId > 0) {
                $db = $db->where('subject_id', $subjectId);
            }

            $db->order('name', 'asc');

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $courseList = $db->select();

            if (empty($courseList)) {
                $courseList = array();
            } else {
                foreach ($courseList as &$course) {
                    if ($course['create_user_id'] > 0) {
                        $createUser = Db('User', [], false)->where('id', $course['create_user_id'])->find();
                        if (!empty($createUser)) {
                            $course['createName'] = $createUser['name'];
                        } else {
                            $course['createName'] = '';
                        }
                    } else {
                        $course['createName'] = '';
                    }

                    if ($course['edit_user_id'] > 0) {
                        $editUser = Db('User', [], false)->where('id', $course['edit_user_id'])->find();
                        if (!empty($createUser)) {
                            $course['editName'] = $editUser['name'];
                        } else {
                            $course['editName'] = '';
                        }
                    } else {
                        $course['editName'] = '';
                    }

                    if (!empty($course['course_stage_ids'])) {
                        $ids = explode('|', str_replace(']', '', str_replace('[', '', $course['course_stage_ids'])));
                        $nameArray = array();
                        foreach ($ids as $id) {
                            $courseStage = Db('CourseStage', [], false)->where('id', $id)->find();
                            if (!empty($courseStage)) {
                                $nameArray[] = $courseStage['name'];
                            } else {
                                $nameArray[] = '';
                            }
                        }
                        $course['course_stage_ids'] = explode('|', str_replace(']', '', str_replace('[', '', $course['course_stage_ids'])));
                        $course['course_stage_names'] = $nameArray;
                    } else {
                        $course['course_stage_ids'] = '';
                        $course['course_stage_names'] = '';
                    }
                }
            }
            return $this->successJson($courseList);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 添加课程
     * @return \think\response\Json
     */
    public function addCourse()
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
        $courseStageIds = input('?post.courseStageIds') ? input('post.courseStageIds/a') : array();
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $name)
            || empty($subjectId) || !is_numeric($subjectId) || $subjectId < 0
            || !is_array($courseStageIds) || empty($courseStageIds) || !checkValidIds($courseStageIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Course', [], false)->where('name', $name)->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1500);
            }

            $insertData = [
                'name' => $name,
                'subject_id' => $subjectId,
                'course_stage_ids' => createIdStr($courseStageIds),
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'status' => $status
            ];
            $result = Db('Course', [], false)->insert($insertData);
            if (empty($result)) {
                return $this->failedJson(500, 1501);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 编辑课程
     * @return \think\response\Json
     */
    public function editCourse()
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
        $courseStageIds = input('?post.courseStageIds') ? input('post.courseStageIds/a') : array();
        $status = input('?post.status') ? input('post.status') : '';

        //检测参数是否合法
        if (
            empty($id) || !is_numeric($id) || $id < 0
            || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $name)
            || empty($subjectId) || !is_numeric($subjectId) || $subjectId < 0
            || !is_array($courseStageIds) || empty($courseStageIds) || !checkValidIds($courseStageIds)
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 name 是否已存在
            $nameCount = Db('Course', [], false)->where([['name', '=', $name], ['id', '<>', $id]])->count();
            if ($nameCount > 0) {
                return $this->failedJson(409, 1500);
            }

            $editData = [
                'id'            => $id,
                'name'          => $name,
                'subject_id'    => $subjectId,
                'course_stage_ids' => createIdStr($courseStageIds),
                'edit_user_id'  => $userInfo['id'],
                'edit_time'     => time(),
                'status'        => $status
            ];

            $result = Db('Course', [], false)->update($editData);

            if (empty($result)) {
                return $this->failedJson(500, 1502);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除课程
     * @return \think\response\Json
     */
    public function deleteCourse()
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
            $result = Db('Course', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1504);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }
}