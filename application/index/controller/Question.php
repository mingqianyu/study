<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/10
 * Time: 下午5:53
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Question extends Base
{
    /**
     * 题目列表
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

        $title = input('?get.title') ? input('get.title') : '';

        $type = input('?get.type') ? input('get.type') : '';

        $level = input('?get.level') ? input('get.level') : '';

        $page = input("?get.page") ? input("get.page") : 0;

        $pageSize = input("?get.pageSize") ? input("get.pageSize") : 0;

        if (
            !is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0
            || !is_numeric($subjectId) || $subjectId < 0
            || (!empty($type) && !in_array($type, array('choice', 'judge', 'answer')))
            || (!empty($level) && !in_array($level, array('hard', 'middle', 'easy')))

        ) {
            return $this->failedJson(400, 1000);
        }

        try {

            $dbCount = Db('Question', [], false)
                ->alias('q')
                ->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, q.category, k.name knowledgeName, q.type, q.level, q.isInterview, u1.name createName, q.create_user_id, q.create_time, u2.name editName, q.edit_user_id, q.edit_time, q.status')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->leftJoin('User u1', 'u1.id = q.create_user_id')
                ->leftJoin('User u2', 'u2.id = q.edit_user_id');

            if ($subjectId > 0) {
                $dbCount->where('k.subject_ids', 'like', '%[' . $subjectId . ']%');
            }

            if (!empty($title)) {
                $dbCount->where('q.title', 'like', '%' . $title . '%');
            }

            if (!empty($type)) {
                $dbCount->where('q.type', '=', $type);
            }

            if (!empty($level)) {
                $dbCount->where('q.level', '=', $level);
            }

            $totalCount = $dbCount->count();


            $db = Db('Question', [], false)
                ->alias('q')
                ->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.category, q.type, q.level, q.isInterview, u1.name createName, q.create_user_id, q.create_time, u2.name editName, q.edit_user_id, q.edit_time, q.status')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->leftJoin('User u1', 'u1.id = q.create_user_id')
                ->leftJoin('User u2', 'u2.id = q.edit_user_id');

            if ($subjectId > 0) {
                $db->where('k.subject_ids', 'like', '%[' . $subjectId . ']%');
            }

            if (!empty($title)) {
                $db->where('q.title', 'like', '%' . $title . '%');
            }

            if (!empty($type)) {
                $db->where('q.type', '=', $type);
            }

            if (!empty($level)) {
                $db->where('q.level', '=', $level);
            }

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $db = $db->order('q.id', 'desc');

            $questionList = $db->select();

            if (empty($questionList)) {
                $questionList = array();
            }
            return $this->successJson(['totalCount' => $totalCount, 'page' => $page, 'pageSize' => $pageSize, 'questions' => $questionList]);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 添加题目
     * @return \think\response\Json
     */
    public function addQuestion()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $title = input('?post.title') ? input('post.title') : '';

        $options = input('?post.options') ? input('post.options/a') : array();

        $answer = input('?post.answer') ? input('post.answer') : '';

        $analysis = input('?post.analysis') ? input('post.analysis') : '';

        $knowledgeId = input("?post.knowledgeId") ? input("post.knowledgeId") : 0;

        $category = input("?post.category") ? input("post.category") : "";

        $type = input('?post.type') ? input('post.type') : '';

        $level = input('?post.level') ? input('post.level') : '';

        $isInterview = input('?post.isInterview') ? input('post.isInterview') : '';

        $status = input('?post.status') ? input('post.status') : '';

        //判断参数是否有效
        if (
            empty($title)
            || ($type == 'choice' && (empty($options) || !is_array($options)))
            || empty($answer)
            || !is_numeric($knowledgeId) || $knowledgeId <= 0
            || (!empty($category) && !preg_match('/[,\d]+/', $category))
            || !in_array($type, array('choice', 'judge', 'answer'))
            || !in_array($level, array('hard', 'middle', 'easy'))
            || !is_numeric($isInterview) || $isInterview < 0 || $isInterview > 1
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        //过滤参数

        $title = htmlspecialchars($title, ENT_QUOTES);

        if (is_array($options) && !empty($options)) {
            $newOptions = array();
            foreach ($options as $value) {
                $newOptions[] = htmlspecialchars($value, ENT_QUOTES);
            }
            $options = implode('[++++]', $newOptions);
        } else {
            $options = '';
        }

        $answer = htmlspecialchars($answer, ENT_QUOTES);

        $analysis = htmlspecialchars($analysis, ENT_QUOTES);


        try {
            //判断 title 是否已存在
            $titleCount = Db('Question', [], false)->where('title', $title)->count();
            if ($titleCount > 0) {
                return $this->failedJson(409, 1604);
            }
            $insertData = [
                'title' => $title,
                'options' => $options,
                'answer' => $answer,
                'analysis' => $analysis,
                'knowledge_id' => $knowledgeId,
                'category' => $category,
                'type' => $type,
                'level' => $level,
                'isInterview' => $isInterview,
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'status' => $status
            ];
            $result = Db('Question', [], false)->insert($insertData);
            if (empty($result)) {
                return $this->failedJson(500, 1600);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }


    /**
     * 编辑题目
     * @return \think\response\Json
     */
    public function editQuestion()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input('?post.id') ? input('post.id') : 0;

        $title = input('?post.title') ? input('post.title') : '';

        $options = input('?post.options') ? input('post.options/a') : array();

        $answer = input('?post.answer') ? input('post.answer') : '';

        $analysis = input('?post.analysis') ? input('post.analysis') : '';

        $knowledgeId = input("?post.knowledgeId") ? input("post.knowledgeId") : 0;

        $category = input("?post.category") ? input("post.category") : "";

        $type = input('?post.type') ? input('post.type') : '';

        $level = input('?post.level') ? input('post.level') : '';

        $isInterview = input('?post.isInterview') ? input('post.isInterview') : '';

        $status = input('?post.status') ? input('post.status') : '';

        //判断参数是否有效
        if (
            empty($id) || $id <= 0
            || empty($title)
            || ($type == 'choice' && (empty($options) || !is_array($options)))
            || empty($answer)
            || !is_numeric($knowledgeId) || $knowledgeId <= 0
            || (!empty($category) && !preg_match('/[,\d]+/', $category))
            || !in_array($type, array('choice', 'judge', 'answer'))
            || !in_array($level, array('hard', 'middle', 'easy'))
            || !is_numeric($isInterview) || $isInterview < 0 || $isInterview > 1
            || !is_numeric($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        //过滤参数

        $title = htmlspecialchars($title, ENT_QUOTES);

        if (is_array($options) && !empty($options)) {
            $newOptions = array();
            foreach ($options as $value) {
                $newOptions[] = htmlspecialchars($value, ENT_QUOTES);
            }
            $options = implode('[++++]', $newOptions);
        } else {
            $options = '';
        }

        $answer = htmlspecialchars($answer, ENT_QUOTES);

        $analysis = htmlspecialchars($analysis, ENT_QUOTES);


        try {
            //判断 name 是否已存在
            $titleCount = Db('Question', [], false)->where([['title', '=', $title], ['id', '<>', $id]])->count();
            if ($titleCount > 0) {
                return $this->failedJson(409, 1604);
            }

            $updateDate = [
                'id' => $id,
                'title' => $title,
                'options' => $options,
                'answer' => $answer,
                'analysis' => $analysis,
                'knowledge_id' => $knowledgeId,
                'category' => $category,
                'type' => $type,
                'level' => $level,
                'isInterview' => $isInterview,
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'status' => $status
            ];
            $result = Db('Question', [], false)->update($updateDate);

            if (empty($result)) {
                return $this->failedJson(500, 1601);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除题目
     * @return \think\response\Json
     */
    public function  deleteQuestion()
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
            $paperCount = Db('Paper', [], false)->where('question_ids', 'like', '%[' . $id . ']%')->count();
            $errorQuestionCount = Db('ErrorQuestion', [], false)->where('question_id',  $id)->count();


            if ($paperCount > 0 || $errorQuestionCount > 0) {
                return $this->failedJson(403, 1603);
            }

            $result = Db('Question', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1602);
            } else {
                return $this->successJson();
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    public function getAllCategoryTree()
    {
        try {
            $subjects = Db('Subject', [], false)->field('id value, name label')->where('status',  1)->select();
            foreach ($subjects as &$subject) {
                $courses = Db('Course', [], false)->field('id value, name label, course_stage_ids')->where([['status', '=', 1], ['subject_id', '=', $subject['value']]])->select();
                foreach ($courses as &$course) {
                    if (!empty($course['course_stage_ids'])) {
                        $ids = explode('|', str_replace(']', '', str_replace('[', '', $course['course_stage_ids'])));
                        $courseStages = array();
                        foreach ($ids as $id) {
                            $courseStage = Db('CourseStage', [], false)->where('id', $id)->find();
                            if (!empty($courseStage)) {
                                $courseStages[] = array('value' => $courseStage['id'], 'label' => $courseStage['name'], 'knowledge_ids' => $courseStage['knowledge_ids']);
                            }
                        }
                        unset($course['course_stage_ids']);
                        foreach ($courseStages as &$courseStage) {
                            if (!empty($courseStage['knowledge_ids'])) {
                                $ids = explode('|', str_replace(']', '', str_replace('[', '', $courseStage['knowledge_ids'])));
                                $knowledges = array();
                                foreach ($ids as $id) {
                                    $knowledge = Db('Knowledge', [], false)->where('id', $id)->find();
                                    if (!empty($knowledge)) {
                                        $knowledges[] = array('value' => $knowledge['id'], 'label' => $knowledge['name']);
                                    }
                                }
                                unset($courseStage['knowledge_ids']);
                                $courseStage['children'] = $knowledges;
                            } else {
                                $courseStage['children'] = array();
                            }
                        }
                        $course['children'] = &$courseStages;
                    } else {
                        $course['children'] = array();
                    }
                }
                $subject['children'] = $courses;
            }
            return $this->successJson($subjects);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }
}