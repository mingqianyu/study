<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/12
 * Time: 上午10:15
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Paper extends Base
{
    //获取试卷
    public function index()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        //获取参数
        $classId = input('?get.classId') ? input('get.classId') : 0;

        $pageName = input('?get.pageName') ? input('get.pageName') : '';

        $page = input("?get.page") ? input("get.page") : 0;

        $pageSize = input("?get.pageSize") ? input("get.pageSize") : 0;

        if (!is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0 || !is_numeric($classId) || $classId < 0) {
            return $this->failedJson(400, 1000);
        }
        //判断用户是否有权限
        if ($userInfo['role'] != 'admin') {
            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $classId];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }
        }

        try {
            $dbCount = Db('Paper', [], false)
                ->alias('p')
                ->field('p.id, p.title, p.question_ids, p.time, p.rule, p.class_id, c.name className, p.total_score, p.create_user_id, u1.name createName, p.create_time, p.edit_user_id, u2.name editName, p.edit_time, p.is_need_read, p.status')
                ->leftJoin('User u1', 'p.create_user_id = u1.id')
                ->leftJoin('User u2', 'p.edit_user_id = u2.id')
                ->leftJoin('Class c', 'p.class_id = c.id');

            if ($userInfo['role'] == 'student') {
                $dbCount = $dbCount->where('p.status', 1);
            }

            if ($classId > 0) {
                $dbCount = $dbCount->where('p.class_id', $classId);
            }

            if (!empty($pageName)) {
                $dbCount = $dbCount->where('p.title', 'like', '%' . $pageName . '%');
            }

            $totalCount = $dbCount->count();



            $db = Db('Paper', [], false)
                ->alias('p')
                ->field('p.id, p.title, p.question_ids, p.time, p.rule, p.class_id, c.name className, p.total_score, p.create_user_id, u1.name createName, p.create_time, p.edit_user_id, u2.name editName, p.edit_time, p.is_need_read, p.status')
                ->leftJoin('User u1', 'p.create_user_id = u1.id')
                ->leftJoin('User u2', 'p.edit_user_id = u2.id')
                ->leftJoin('Class c', 'p.class_id = c.id');

            if ($userInfo['role'] == 'student') {
                $db = $db->where('p.status', 1);
            }

            if ($classId > 0) {
                $db = $db->where('p.class_id', $classId);
            }

            if (!empty($pageName)) {
                $db = $db->where('p.title', 'like', '%' . $pageName . '%');
            }

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $db = $db->limit($start, $pageSize);
            }

            $paperList = $db->order('p.id', 'desc')->select();

            if (empty($paperList)) {
                $paperList = array();
            } else {
                if ($userInfo['role'] == 'student') {
                    foreach ($paperList as &$paper) {
                        $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paper['id']], ['user_id', '=', $userInfo['id']]])->find();
                        if (empty($paperResult)) {
                            $paper['resultStatus'] = 0;
                        } else {
                            $paper['resultStatus'] = $paperResult['status'];
                        }
                    }
                }
            }
            return $this->successJson(['totalCount' => $totalCount, 'page' => $page, 'pageSize' => $pageSize, 'papers' => $paperList] );
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 添加试卷
     * @return \think\response\Json
     */
    public function addPaper()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $title = input('?post.title') ? input('post.title') : '';       //试卷名称

        $classId = input('?post.classId') ? input('post.classId') : 0;  //班级id

        $rule = input('?post.rule') ? input('post.rule/a') : array(); //出题规则

        $time = input('?post.time') ? input('post.time') : 0;  //考试时间 单位秒

        $isNeedRead = input('?post.isNeedRead') ? input('post.isNeedRead') : ''; //是否需要阅卷

        //判断参数是否有效
        if (
            empty($title) || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $title)
            || !is_numeric($classId) || $classId <= 0
            || empty($rule) || !is_array($rule)
            || !is_numeric($time)
            || !is_numeric($isNeedRead) || $isNeedRead < 0 || $isNeedRead > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        //判断用户是否有权限
        if ($userInfo['role'] != 'admin') {
            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $classId];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }
        }

        //判断出题规则是否有效
        $isValidRule = $this->isValidRule($rule);
        if ($isValidRule !== true) {
            return $this->failedJson(400, $isValidRule);
        }

        try {
            //判断 name 是否已存在
            $titleCount = Db('Paper', [], false)->where('title', $title)->count();
            if ($titleCount > 0) {
                return $this->failedJson(409, 1732);
            }

            //创建试卷
            $totalQuestionArray = $this->createPaper($rule);
            if ($totalQuestionArray === 1002) {
                return $this->failedJson(500, 1002);
            }

            $totalQuestionIdStr = createIdStr($totalQuestionArray[0]);

            $insertData = array(
                'title' => htmlspecialchars($title, ENT_QUOTES),
                'question_ids' => $totalQuestionIdStr,
                'time' => $time,
                'rule' => json_encode($rule),
                'class_id' => $classId,
                'total_score' => ($rule['choice']['count'] * $rule['choice']['score'] + $rule['judge']['count'] * $rule['judge']['score'] + $rule['answer']['count'] * $rule['answer']['score']),
                'create_user_id' => $userInfo['id'],
                'create_time' => time(),
                'is_need_read' => $isNeedRead,
                'status' => 0
            );

            $id = Db('Paper', [], false)->insertGetId($insertData);
            if (empty($id)) {
                return $this->failedJson(500, 1721);
            }

            return $this->successJson(['id' => $id, 'questions' => $totalQuestionArray[1]]);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 刷新试卷
     * @return \think\response\Json
     */
    public function refreshPaper()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input('?get.id') ? input('get.id') : 0;

        if (!is_numeric($id) || $id <= 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断是否可以刷新试卷
            $paperResultCount = Db('PaperResult', [], false)->where('paper_id', $id)->count();
            if ($paperResultCount > 0) {
                return $this->failedJson(403, 1724);
            }

            //获取试卷
            $paper = Db('Paper', [], false)->where('id', $id)->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1722);
            }

            //判断试卷是否已发布
            if ($paper['status']) {
                return $this->failedJson(403, 1725);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }


        //判断用户是否有权限
        if ($userInfo['role'] != 'admin') {
            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $paper['class_id']];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }
        }


        //判断出题规则是否有效
        $rule = json_decode($paper['rule'], true);
        $isValidRule = $this->isValidRule($rule);
        if ($isValidRule !== true) {
            return $this->failedJson(400, $isValidRule);
        }

        //创建试卷
        try {
            //创建试卷
            $totalQuestionArray = $this->createPaper($rule);
            if ($totalQuestionArray === 1002) {
                Log::error("系统错误！");
                return $this->failedJson(500, 1002);
            }

            $totalQuestionIdStr = createIdStr($totalQuestionArray[0]);

            $updateDate = array(
                'id' => $id,
                'question_ids' => $totalQuestionIdStr,
                'edit_user_id' => $userInfo['id'],
                'edit_time' => time()
            );

            $result = Db('Paper', [], false)->update($updateDate);
            if (empty($result)) {
                return $this->failedJson(500, 1723);
            }

            return $this->successJson(['questions' => $totalQuestionArray[1]]);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 编辑试卷
     */
    public function editPaper()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input('?post.Id') ? input('post.Id') : 0;

        $title = input('?post.title') ? input('post.title') : '';       //试卷名称

        $rule = input('?post.rule') ? input('post.rule/a') : array(); //出题规则

        $time = input('?post.time') ? input('post.time') : 0;  //考试时间 单位秒

        $isNeedRead = input('?post.isNeedRead') ? input('post.isNeedRead') : ''; //是否需要阅卷

        if (
            !is_int($id) || $id <= 0
        || empty($title) || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,100}$/u', $title)
        || empty($rule) || !is_array($rule)
        || !is_numeric($time)
        || !is_numeric($isNeedRead) || $isNeedRead < 0 || $isNeedRead > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断 title 是否已存在
            $titleCount = Db('Paper', [], false)->where([['title', '=', $title], ['id', '<>', $id]])->count();
            if ($titleCount > 0) {
                return $this->failedJson(409, 1604);
            }

            //判断是否可以编辑试卷
            $paperResultCount = Db('PaperResult', [], false)->where('paper_id', $id)->count();
            if ($paperResultCount > 0) {
                return $this->failedJson(403, 1726);
            }

            //获取试卷
            $paper = Db('Paper', [], false)->where('id', $id)->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1722);
            }

            //判断试卷是否已发布
            if ($paper['status']) {
                return $this->failedJson(403, 1725);
            }
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }


        //判断用户是否有权限
        if ($userInfo['role'] != 'admin') {
            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $paper['class_id']];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }
        }


        //创建试卷
        try {
            if ($this->isChangedRule($rule, json_decode($paper['rule'], true))) {
                //判断出题规则是否有效
                $isValidRule = $this->isValidRule($rule);
                if ($isValidRule !== true) {
                    return $this->failedJson(400, $isValidRule);
                }

                //创建试卷
                $totalQuestionArray = $this->createPaper($rule);
                if ($totalQuestionArray === 1002) {
                    return $this->failedJson(500, 1002);
                }

                $totalQuestionIdStr = createIdStr($totalQuestionArray[0]);

                $updateDate = array(
                    'id' => $id,
                    'title' => $title,
                    'question_ids' => $totalQuestionIdStr,
                    'rule' => json_encode($rule),
                    'time' => $time,
                    'totalScore' => ($rule['choice']['count'] * $rule['choice']['score'] + $rule['judge']['count'] * $rule['judge']['score'] + $rule['answer']['count'] * $rule['answer']['score']),
                    'is_need_read' => $isNeedRead,
                    'edit_user_id' => $userInfo['id'],
                    'edit_time' => time()
                );
                $returnQuestions = $totalQuestionArray[1];
            } else {
                $updateDate = array(
                    'id' => $id,
                    'title' => $title,
                    'time' => $time,
                    'is_need_read' => $isNeedRead,
                    'edit_user_id' => $userInfo['id'],
                    'edit_time' => time()
                );
                $returnQuestions = [];
            }

            $result = Db('Paper', [], false)->update($updateDate);
            if (empty($result)) {
                return $this->failedJson(500, 1728);
            }

            return $this->successJson(['questions' => $returnQuestions]);

        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 删除试卷
     */
    public function deletePaper()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $id = input('?get.id') ? input('get.id') : 0;

        if (!is_numeric($id) || $id <= 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //判断是否可以删除
            $paperResultCount = Db('PaperResult', [], false)->where('paper_id', $id)->count();
            if ($paperResultCount > 0) {
                return $this->failedJson(403, 1724);
            }

            //获取试卷
            $paper = Db('Paper', [], false)->where('id', $id)->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1722);
            }

            //判断试卷是否已发布
            if ($paper['status']) {
                return $this->failedJson(403, 1725);
            }

            //判断用户是否有权限
            if ($userInfo['role'] != 'admin') {
                //查询操作用户是否在班级中
                $map[] = ['class_id', '=', $paper['class_id']];
                $map[] = ['user_id', '=', $userInfo['id']];
                $map[] = ['status', '=', 1];
                $count = Db('ClassUser', [], false)->where($map)->count();
                if (!$count) {
                    return $this->failedJson(403, 1700);
                }
            }

            $result = Db('Paper', [], false)->where('id', $id)->delete();
            if (empty($result)) {
                return $this->failedJson(500, 1730);
            }
            return $this->successJson();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 改变试卷状态
     * @return \think\response\Json
     */
    public function changePaperStatus()
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
        $status = input('?post.status') ? input('post.status') : '';

        //判断参数是否有效
        if (
            !is_int($id) || $id <= 0
            || !is_int($status) || $status < 0 || $status > 1
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取试卷
            $paper = Db('Paper', [], false)->where('id', $id)->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1722);
            }

            //判断用户是否有权限
            if ($userInfo['role'] != 'admin') {
                //查询操作用户是否在班级中
                $map[] = ['class_id', '=', $paper['class_id']];
                $map[] = ['user_id', '=', $userInfo['id']];
                $map[] = ['status', '=', 1];
                $count = Db('ClassUser', [], false)->where($map)->count();
                if (!$count) {
                    return $this->failedJson(403, 1700);
                }
            }

            //更新状态
            $updateStatus = array(
                'id' => $id,
                'status' => $status
            );
            $result = Db('Paper', [], false)->update($updateStatus);
            if (empty($result)) {
                return $this->failedJson(500, 1729);
            }

            return $this->successJson();

        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }


   /**
    * 获取此试卷题目
    */
   public function getPaperQuestions()
   {
       $userInfo = getUserInfoByToken(Request::header('X-Token'));
       if (empty($userInfo)) {
           return $this->failedJson(403, 1003);
       }

       if ($userInfo['role'] == 'student') {
           return $this->failedJson(403, 1001);
       }

       //获取参数
       $id = input('?get.id') ? input('get.id') : 0;

       //判断参数是否有效
       if (
           !is_numeric($id) || $id <= 0
       ) {
           return $this->failedJson(400, 1000);
       }

       try {
           //获取试卷
           $paper = Db('Paper', [], false)->where('id', $id)->find();
           if (empty($paper)) {
               return $this->failedJson(404, 1722);
           }

           //判断用户是否有权限
           if ($userInfo['role'] != 'admin') {
               //查询操作用户是否在班级中
               $map[] = ['class_id', '=', $paper['class_id']];
               $map[] = ['user_id', '=', $userInfo['id']];
               $map[] = ['status', '=', 1];
               $count = Db('ClassUser', [], false)->where($map)->count();
               if (!$count) {
                   return $this->failedJson(403, 1700);
               }
           }

           //获取题目
           $questionsArray = self::getQuestions($paper['question_ids'], true);

           return $this->successJson(['questions' => $questionsArray]);
       } catch (Exception $e) {
           return $this->failedJson(500, 1002);
       }
   }

    /**
     * 获取题目
     * @param $questionIds
     * @param $needAnswer
     * @param $type
     * @return array
     * @throws
     */
   public static function getQuestions($questionIds, $needAnswer = false, $type = 'all')
   {
       $questionsArray = array();
       if (!empty($questionIds)) {
           $questionIdsArray = explode('|', str_replace(']', '', str_replace('[', '', $questionIds)));
           foreach ($questionIdsArray as $questionId) {
               $map = array();
               $map[] = ['q.id', '=', $questionId];
               if (in_array($type, ['choice', 'judge', 'answer'])) {
                   $map[] = ['q.type', '=', $type];
               }
               if ($needAnswer) {
                   $question = Db('Question', [], false)->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')->leftJoin('Knowledge k', 'q.knowledge_id = k.id')->where($map)->find();
               } else {
                   $question = Db('Question', [], false)->alias('q')->field('q.id, q.title, q.options, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')->leftJoin('Knowledge k', 'q.knowledge_id = k.id')->where($map)->find();
               }
               if (!empty($question)) {
                   $questionsArray[] = $question;
               }
           }
       }

       return $questionsArray;
   }


    public function getCategoryTreeBySubjectId()
    {
        //获取参数
        $subjectId = input("?get.subjectId") ? input("get.subjectId") : 0;
        if (empty($subjectId) || !is_numeric($subjectId) || $subjectId < 0) {
            return $this->failedJson(400, 1000);
        }
        try {
            $subjects = Db('Subject', [], false)->field('id value, name label')->where('id', $subjectId)->select();
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
                                        $knowledges[] = array('value' => 'kn' . $knowledge['id'], 'label' => $knowledge['name']);
                                    }
                                }
                                unset($courseStage['knowledge_ids']);
                                $courseStage['value'] = 'cs' . $courseStage['value'];
                                $courseStage['children'] = $knowledges;

                            } else {
                                $courseStage['children'] = array();
                            }
                        }
                        $course['value'] = 'cu' . $course['value'];
                        $course['children'] = &$courseStages;
                    } else {
                        $course['children'] = array();
                    }
                }
                $subject['value'] = 'su' . $subject['value'];
                $subject['children'] = $courses;
            }
            return $this->successJson($subjects);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }


    /**
     * 判断规则是否改变
     * @param $firstRule
     * @param $secondRule
     * @return bool
     */
    private function isChangedRule($firstRule, $secondRule)
    {
        if (count($firstRule['courseIds']) !=  count($secondRule['courseIds'])) {
            return true;
        }

        foreach ($firstRule['courseIds'] as $firstValue) {
            $has = false;
            foreach ($secondRule['courseIds'] as $secondValue) {
                if ($firstValue === $secondValue) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                return true;
            }
        }

        if (count($firstRule['courseStageIds']) !=  count($secondRule['courseStageIds'])) {
            return true;
        }

        foreach ($firstRule['courseStageIds'] as $firstValue) {
            $has = false;
            foreach ($secondRule['courseStageIds'] as $secondValue) {
                if ($firstValue === $secondValue) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                return true;
            }
        }

        if (count($firstRule['knowledgeIds']) !=  count($secondRule['knowledgeIds'])) {
            return true;
        }

        foreach ($firstRule['knowledgeIds'] as $firstValue) {
            $has = false;
            foreach ($secondRule['knowledgeIds'] as $secondValue) {
                if ($firstValue === $secondValue) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                return true;
            }
        }

        if ($firstRule['choice']['count'] !== $secondValue['choice']['count'] || $firstRule['choice']['hard'] !== $secondValue['choice']['hard'] || $firstRule['choice']['middle'] !== $secondValue['choice']['middle'] ||  $firstRule['choice']['easy'] !== $secondValue['choice']['easy'] ||  $firstRule['choice']['score'] !== $secondValue['choice']['score']) {
            return true;
        }

        if ($firstRule['judge']['count'] !== $secondValue['judge']['count'] || $firstRule['judge']['hard'] !== $secondValue['judge']['hard'] || $firstRule['judge']['middle'] !== $secondValue['judge']['middle'] ||  $firstRule['judge']['easy'] !== $secondValue['judge']['easy'] ||  $firstRule['judge']['score'] !== $secondValue['judge']['score']) {
            return true;
        }

        if ($firstRule['answer']['count'] !== $secondValue['answer']['count'] || $firstRule['answer']['hard'] !== $secondValue['answer']['hard'] || $firstRule['answer']['middle'] !== $secondValue['answer']['middle'] ||  $firstRule['answer']['easy'] !== $secondValue['answer']['easy'] ||  $firstRule['answer']['score'] !== $secondValue['answer']['score']) {
            return true;
        }

        if ($firstRule['totalCount'] !== $secondRule['totalCount']) {
            return true;
        }
        return false;
    }

    /**
     * 创建试卷
     * @param $rule
     * @return array|int
     * @throws
     */
    private function createPaper($rule)
    {
        $totalQuestions = array();
        $totalQuestionIds = array();

        //创建choice easy question
        if ($rule['choice']['easy']) {
            $choiceEasyMap[] = ['q.level', '=', 'easy'];
            $choiceEasyMap[] = ['q.type', '=', 'choice'];
            $choiceEasyMap[] = ['q.status', '=', 1];

            $choiceEasyQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($choiceEasyMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();
            if (empty($choiceEasyQuestions)) {
                return 1002;
            }

            $selectChoiceEasyQuestionArray = $this->randomQuestions($choiceEasyQuestions, $rule['choice']['easy']);

            pushArray($totalQuestionIds, $selectChoiceEasyQuestionArray[0]);
            pushArray($totalQuestions, $selectChoiceEasyQuestionArray[1]);
        }



        //创建choice middle question
        if ($rule['choice']['middle']) {
            $choiceMiddleMap[] = ['q.level', '=', 'middle'];
            $choiceMiddleMap[] = ['q.type', '=', 'choice'];
            $choiceMiddleMap[] = ['q.status', '=', 1];

            $choiceMiddleQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($choiceMiddleMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();
            if (empty($choiceMiddleQuestions)) {
                return 1002;
            }

            $selectChoiceMiddleQuestionArray = $this->randomQuestions($choiceMiddleQuestions, $rule['choice']['middle']);

            pushArray($totalQuestionIds, $selectChoiceMiddleQuestionArray[0]);
            pushArray($totalQuestions, $selectChoiceMiddleQuestionArray[1]);
        }


        //创建 choice hard question
        if ($rule['choice']['hard']) {
            $choiceHardMap[] = ['q.level', '=', 'hard'];
            $choiceHardMap[] = ['q.type', '=', 'choice'];
            $choiceHardMap[] = ['q.status', '=', 1];

            $choiceHardQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($choiceHardMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($choiceHardQuestions)) {
                return 1002;
            }

            $selectHardQuestionArray = $this->randomQuestions($choiceHardQuestions, $rule['choice']['hard']);

            pushArray($totalQuestionIds, $selectHardQuestionArray[0]);
            pushArray($totalQuestions, $selectHardQuestionArray[1]);
        }


        //创建judge easy question
        if ($rule['judge']['easy']) {
            $judgeEasyMap[] = ['q.level', '=', 'easy'];
            $judgeEasyMap[] = ['q.type', '=', 'judge'];
            $judgeEasyMap[] = ['q.status', '=', 1];

            $judgeEasyQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($judgeEasyMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($judgeEasyQuestions)) {
                return 1002;
            }

            $judgeEasyQuestionArray = $this->randomQuestions($judgeEasyQuestions, $rule['judge']['easy']);

            pushArray($totalQuestionIds, $judgeEasyQuestionArray[0]);
            pushArray($totalQuestions, $judgeEasyQuestionArray[1]);
        }


        //创建judge middle question
        if ($rule['judge']['middle']) {
            $judgeMiddleMap[] = ['q.level', '=', 'middle'];
            $judgeMiddleMap[] = ['q.type', '=', 'judge'];
            $judgeMiddleMap[] = ['q.status', '=', 1];

            $judgeMiddleQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($judgeMiddleMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($judgeMiddleQuestions)) {
                return 1002;
            }

            $judgeMiddleQuestionArray = $this->randomQuestions($judgeMiddleQuestions, $rule['judge']['middle']);

            pushArray($totalQuestionIds, $judgeMiddleQuestionArray[0]);
            pushArray($totalQuestions, $judgeMiddleQuestionArray[1]);
        }


        //创建judge hard question
        if ($rule['judge']['hard']) {
            $judgeHardMap[] = ['q.level', '=', 'hard'];
            $judgeHardMap[] = ['q.type', '=', 'judge'];
            $judgeHardMap[] = ['q.status', '=', 1];

            $judgeHardQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($judgeHardMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($judgeHardQuestions)) {
                return 1002;
            }

            $judgeHardQuestionArray = $this->randomQuestions($judgeHardQuestions, $rule['judge']['hard']);

            pushArray($totalQuestionIds, $judgeHardQuestionArray[0]);
            pushArray($totalQuestions, $judgeHardQuestionArray[1]);
        }


        //创建answer easy question
        if ($rule['answer']['easy']) {
            $answerEasyMap[] = ['q.level', '=', 'easy'];
            $answerEasyMap[] = ['q.type', '=', 'answer'];
            $answerEasyMap[] = ['q.status', '=', 1];

            $answerEasyQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($answerEasyMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($answerEasyQuestions)) {
                return 1002;
            }

            $answerEasyQuestionArray = $this->randomQuestions($answerEasyQuestions, $rule['answer']['easy']);

            pushArray($totalQuestionIds, $answerEasyQuestionArray[0]);
            pushArray($totalQuestions, $answerEasyQuestionArray[1]);
        }


        //创建 answer middle question
        if ($rule['answer']['middle']) {
            $answerMiddleMap[] = ['q.level', '=', 'middle'];
            $answerMiddleMap[] = ['q.type', '=', 'answer'];
            $answerMiddleMap[] = ['q.status', '=', 1];

            $answerMiddleQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($answerMiddleMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($answerMiddleQuestions)) {
                return 1002;
            }

            $answerMiddleQuestionArray = $this->randomQuestions($answerMiddleQuestions, $rule['answer']['middle']);

            pushArray($totalQuestionIds, $answerMiddleQuestionArray[0]);
            pushArray($totalQuestions, $answerMiddleQuestionArray[1]);
        }


        //创建answer hard question
        if ($rule['answer']['hard']) {
            $answerHardMap[] = ['q.level', '=', 'hard'];
            $answerHardMap[] = ['q.type', '=', 'answer'];
            $answerHardMap[] = ['q.status', '=', 1];

            $answerHardQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where($answerHardMap)
                ->whereIn('q.knowledge_id', $rule['knowledgeIds'])
                ->order('q.id', 'asc')
                ->select();

            if (empty($answerHardQuestions)) {
                return 1002;
            }

            $answerHardQuestionArray = $this->randomQuestions($answerHardQuestions, $rule['answer']['hard']);

            pushArray($totalQuestionIds, $answerHardQuestionArray[0]);
            pushArray($totalQuestions, $answerHardQuestionArray[1]);
        }
        return [$totalQuestionIds, $totalQuestions];
    }


    private function isValidRule($rule)
    {
        Log::info(json_encode($rule));
        if (empty($rule) || !is_array($rule)) {
            return 1000;
        }

        if (!isset($rule['courseIds']) || empty($rule['courseIds']) || !is_array($rule['courseIds'])) {
            return 1701;
        }

        foreach ($rule['courseIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1701;
            }
        }

        if (!isset($rule['courseStageIds']) || empty($rule['courseStageIds']) || !is_array($rule['courseStageIds'])) {
            return 1702;
        }

        foreach ($rule['courseStageIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1702;
            }
        }

        if (!isset($rule['knowledgeIds']) || empty($rule['knowledgeIds']) || !is_array($rule['knowledgeIds'])) {
            return 1703;
        }

        foreach ($rule['knowledgeIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1703;
            }
        }

        if (!isset($rule['choice']) || empty($rule['choice']) || !is_array($rule['choice'])) {
            return 1704;
        }

        if (
            !isset($rule['choice']['count']) || !is_int($rule['choice']['count'])
            || !isset($rule['choice']['hard']) || !is_int($rule['choice']['hard'])
            || !isset($rule['choice']['middle']) || !is_int($rule['choice']['middle'])
            || !isset($rule['choice']['easy']) || !is_int($rule['choice']['easy'])
            || !isset($rule['choice']['score']) || !is_numeric($rule['choice']['score'])
            || ($rule['choice']['count'] !== ($rule['choice']['hard'] + $rule['choice']['middle'] + $rule['choice']['easy']))

        ) {
            return 1705;
        }

        if (!isset($rule['judge']) || empty($rule['judge']) || !is_array($rule['judge'])) {
            return 1706;
        }

        if (
            !isset($rule['judge']['count']) || !is_int($rule['judge']['count'])
            || !isset($rule['judge']['hard']) || !is_int($rule['judge']['hard'])
            || !isset($rule['judge']['middle']) || !is_int($rule['judge']['middle'])
            || !isset($rule['judge']['easy']) || !is_int($rule['judge']['easy'])
            || !isset($rule['judge']['score']) || !is_numeric($rule['answer']['score'])
            || ($rule['judge']['count'] !== ($rule['judge']['hard'] + $rule['judge']['middle'] + $rule['judge']['easy']))

        ) {
            return 1707;
        }

        if (!isset($rule['judge']) || empty($rule['judge']) || !is_array($rule['judge'])) {
            return 1708;
        }

        if (
            !isset($rule['answer']['count']) || !is_int($rule['answer']['count'])
            || !isset($rule['answer']['hard']) || !is_int($rule['answer']['hard'])
            || !isset($rule['answer']['middle']) || !is_int($rule['answer']['middle'])
            || !isset($rule['answer']['easy']) || !is_int($rule['answer']['easy'])
            || !isset($rule['answer']['score']) || !is_numeric($rule['answer']['score'])
            || ($rule['answer']['count'] !== ($rule['answer']['hard'] + $rule['answer']['middle'] + $rule['answer']['easy']))

        ) {
            return 1709;
        }

        if (!isset($rule['totalCount']) || !is_int($rule['totalCount']) || $rule['totalCount'] != ($rule['choice']['count'] + $rule['judge']['count'] + $rule['answer']['count']) ) {
            return 1710;
        }

        if (($rule['choice']['count'] * $rule['choice']['score'] + $rule['judge']['count'] * $rule['judge']['score'] + $rule['answer']['count'] * $rule['answer']['score']) !== 100) {
            return 1711;
        }

        //查看choice hard question 是否足够
        $choiceHardMap[] = ['level', '=', 'hard'];
        $choiceHardMap[] = ['type', '=', 'choice'];
        $choiceHardMap[] = ['status', '=', 1];

        $choiceHardCount = Db('Question', [], false)->where($choiceHardMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($choiceHardCount < $rule['choice']['hard']) {
            return 1712;
        }

        //查看choice middle question 是否足够
        $choiceMiddleMap[] = ['level', '=', 'middle'];
        $choiceMiddleMap[] = ['type', '=', 'choice'];
        $choiceMiddleMap[] = ['status', '=', 1];

        $choiceMiddleCount = Db('Question', [], false)->where($choiceMiddleMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($choiceMiddleCount < $rule['choice']['middle']) {
            return 1713;
        }

        //查看choice easy question 是否足够
        $choiceEasyMap[] = ['level', '=', 'easy'];
        $choiceEasyMap[] = ['type', '=', 'choice'];
        $choiceEasyMap[] = ['status', '=', 1];

        $choiceEasyCount = Db('Question', [], false)->where($choiceEasyMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($choiceEasyCount < $rule['choice']['easy']) {
            return 1714;
        }

        //查看judge hard question 是否足够
        $judgeHardMap[] = ['level', '=', 'hard'];
        $judgeHardMap[] = ['type', '=', 'judge'];
        $judgeHardMap[] = ['status', '=', 1];

        $judgeHardCount = Db('Question', [], false)->where($judgeHardMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($judgeHardCount < $rule['judge']['hard']) {
            return 1715;
        }

        //查看judge middle question 是否足够
        $judgeMiddleMap[] = ['level', '=', 'middle'];
        $judgeMiddleMap[] = ['type', '=', 'judge'];
        $judgeMiddleMap[] = ['status', '=', 1];

        $judgeMiddleCount = Db('Question', [], false)->where($judgeMiddleMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($judgeMiddleCount < $rule['judge']['middle']) {
            return 1716;
        }

        //查看judge easy question 是否足够
        $judgeEasyMap[] = ['level', '=', 'easy'];
        $judgeEasyMap[] = ['type', '=', 'judge'];
        $judgeEasyMap[] = ['status', '=', 1];

        $judgeEasyCount = Db('Question', [], false)->where($judgeEasyMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($judgeEasyCount < $rule['judge']['easy']) {
            return 1717;
        }

        //查看answer hard question 是否足够
        $answerHardMap[] = ['level', '=', 'hard'];
        $answerHardMap[] = ['type', '=', 'answer'];
        $answerHardMap[] = ['status', '=', 1];

        $answerHardCount = Db('Question', [], false)->where($answerHardMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($answerHardCount < $rule['answer']['hard']) {
            return 1718;
        }

        //查看answer middle question 是否足够
        $answerMiddleMap[] = ['level', '=', 'middle'];
        $answerMiddleMap[] = ['type', '=', 'answer'];
        $answerMiddleMap[] = ['status', '=', 1];

        $answerMiddleCount = Db('Question', [], false)->where($answerMiddleMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($answerMiddleCount < $rule['answer']['middle']) {
            return 1719;
        }

        //查看answer easy question 是否足够
        $answerEasyMap[] = ['level', '=', 'easy'];
        $answerEasyMap[] = ['type', '=', 'answer'];
        $answerEasyMap[] = ['status', '=', 1];

        $answerEasyCount = Db('Question', [], false)->where($answerEasyMap)->whereIn('knowledge_id', $rule['knowledgeIds'])->count();

        if ($answerEasyCount < $rule['answer']['easy']) {
            return 1720;
        }
        return true;
    }

    private function randomQuestions($questions, $count)
    {
        $store = array();
        $totalCount = count($questions);
        $newQuestions = array();
        $newQuestionsIds = array();
        while ($count > 0) {
            $index = mt_rand(0, $totalCount - 1);
            $question = $questions[$index];
            if (!isset($store[$question['id']])) {
                $store[$question['id']] = $question['id'];
                $newQuestions[] = $question;
                $newQuestionsIds[] = $question['id'];
                $count--;
            }
        }
        return [$newQuestionsIds, $newQuestions];
    }
}