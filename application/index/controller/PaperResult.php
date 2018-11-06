<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/15
 * Time: 下午5:11
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class PaperResult extends Base
{
    /**
     * 获取试卷所有学生的结果
     */
    public function getPaperResults()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?post.paperId') ? input('post.paperId') : 0; //试卷id
        if (empty($paperId) || !is_int($paperId) || $paperId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where('id', $paperId)->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            } else {
                if ($paper['status'] < 1) {
                    return $this->failedJson(403, 1816);
                }
            }

            //查询操作用户是否在班级中
            if ($userInfo['role'] != 'admin') {
                $map[] = ['class_id', '=', $paper['class_id']];
                $map[] = ['user_id', '=', $userInfo['id']];
                $map[] = ['status', '=', 1];
                $count = Db('ClassUser', [], false)->where($map)->count();
                if (!$count) {
                    return $this->failedJson(403, 1700);
                }
            }

            //获取此班级所有学生
            $students = Db('ClassUser', [], false)
                ->alias('c')
                ->field('u.id, u.name, u.role')
                ->leftJoin('User u', 'c.user_id = u.id')
                ->where([['c.class_id', '=', $paper['class_id']],['u.role', '=', 'student'], ['c.status', '=', 1], ['u.status', '=', 1]])
                ->select();

            //获取成绩
            if (!empty($students)) {
                foreach ($students as &$student) {
                    $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paperId], ['user_id', '=', $student['id']]])->find();
                    if (empty($paperResult)) {
                        $student['paperResult'] = array();
                    } else {
                        if ($paperResult['read_user_id'] > 0) {
                            $readUser = Db('User', [], false)->where('id', $paperResult['read_user_id'])->find();
                            if (empty($readUser)) {
                                $paperResult['readUserName'] = '';
                            } else {
                                $paperResult['readUserName'] = $readUser['name'];
                            }

                        } else {
                            $paperResult['readUserName'] = '';
                        }
                        $student['paperResult'] = $paperResult;
                    }
                }
            } else {
                $students = array();
            }

            return $this->successJson($students);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    /**
     * 获取自己的试题结果
     * @return \think\response\Json
     */
    public function getSelfPaperResult()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?get.paperId') ? input('get.paperId') : 0; //试卷id
        if (empty($paperId) || !is_numeric($paperId) || $paperId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $paper['class_id']];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }

            //判断试卷状态
            if (!$paper['status']) {
                return $this->failedJson(403, 1801);
            }

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paper['id']], ['user_id', '=', $userInfo['id']]])->find();
            if (empty($paperResult) || $paperResult['status'] < 2) {
                return $this->failedJson(403, 1808);
            }

            //获取考试题
            $questions = Paper::getQuestions($paper['question_ids'], true);

            if (!empty($paperResult)) {
                $answers = explode('[++++]', $paperResult['answers']);
            } else {
                $answers = [];
            }

            return $this->successJson(['questions' => $questions, 'answers' => $answers, 'totalScore' => $paperResult['objective_score'] + $paperResult['subjective_score']]);

        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    public function startExam()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?get.paperId') ? input('get.paperId') : 0; //试卷id
        if (empty($paperId) || !is_numeric($paperId) || $paperId < 0) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $paper['class_id']];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }

            //判断试卷状态
            if (!$paper['status']) {
                return $this->failedJson(403, 1801);
            }

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paper['id']], ['user_id', '=', $userInfo['id']]])->find();
            if (!empty($paperResult) && $paperResult['status'] > 1) {
                return $this->failedJson(403, 1802);
            }

            //加入考试状态
            if (empty($paperResult)) {
                $beginTime = time();
                $insertDate = array(
                   'user_id' => $userInfo['id'],
                   'paper_id' => $paperId,
                   'begin_time' => $beginTime,
                   'answers' => '',
                   'status' => 1
                );
                $result = Db('PaperResult', [], false)->insert($insertDate);
                if (empty($result)) {
                    return $this->failedJson(500, 1803);
                }
            } else {
                $beginTime = $paperResult['begin_time'];
            }

            if ($paper['time'] > 0) {
                $countDownTime = $paper['time'] - (time() - $beginTime);
                if ($countDownTime <= 0) {
                    return $this->failedJson(403, 1804);
                }
            } else {
                $countDownTime = -1;
            }

            //获取考试题
            $questions = Paper::getQuestions($paper['question_ids'], false);

            if (!empty($paperResult) && !empty($paperResult['answers'])) {
                $answers = explode('[++++]', $paperResult['answers']);
            } else {
                $answers = [];
            }

            $index = 0;
            foreach ($questions as &$question) {
                if (empty($answers)) {
                    $question['answerValue'] = '';
                } else {
                    $question['answerValue'] = $answers[$index];
                }
                $index++;
            }
            return $this->successJson(['countDownTime' => $countDownTime, 'questions' => $questions, 'answers' => $answers]);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    public function submitAnswer()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?post.paperId') ? input('post.paperId') : 0;

        $questionId = input('?post.questionId') ? input('post.questionId') : 0;

        $answer = input('?post.answer') ? input('post.answer') : '';

        //判断参数是否合法
        if (
            empty($paperId) || !is_int($paperId) || $paperId < 0
            || empty($questionId) || !is_int($questionId) || $questionId < 0
            || empty($answer)
        )
        {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //判断试卷状态
            if (!$paper['status']) {
                return $this->failedJson(403, 1801);
            }


            //查询操作用户是否在班级中
            $map[] = ['class_id', '=', $paper['class_id']];
            $map[] = ['user_id', '=', $userInfo['id']];
            $map[] = ['status', '=', 1];
            $count = Db('ClassUser', [], false)->where($map)->count();
            if (!$count) {
                return $this->failedJson(403, 1700);
            }

            //获取当前考试题目
            $question = Db('Question', [], false)->where('id', $questionId)->find();
            if (empty($question)) {
                return $this->failedJson(404, 1805);
            }

            if ($question['type'] == 'choice' && !preg_match('/^[A-Z]$/', $answer)) {
                return $this->failedJson(400, 1000);
            } else if ($question['type'] == 'judge' && !in_array($answer, array('F', 'T'))) {
                return $this->failedJson(400, 1000);
            } else {
                $answer = htmlspecialchars($answer, ENT_QUOTES);
            }

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paperId], ['user_id', '=', $userInfo['id']]])->find();
            if (!empty($paperResult) && $paperResult['status'] > 1) {
                return $this->failedJson(403, 1802);
            }

            if (empty($paperResult)) {
                return $this->failedJson(500, 1002);
            }

            if ($paper['time'] > 0) {
                $countDownTime = $paper['time'] - (time() - $paperResult['begin_time']);
                if ($countDownTime <= 0) {
                    return $this->failedJson(403, 1804);
                }
            }

            //添加结果
            $answers = array();
            if (!empty($paperResult['answers'])) {
                $answers = explode('[++++]', $paperResult['answers']);
            }

            $questionIds = explode('|', str_replace(']', '', str_replace('[', '', $paper['question_ids'])));

            Log::info(json_encode($answers));

            Log::info(json_encode($answer));

            //添加结果
            for ($i = 0; $i < count($questionIds); $i++) {
                $i = intval($i);
                if (!isset($answers[$i]) || empty($answers[$i])) {
                    $answers[$i] = '';
                }

                if (intval($questionIds[$i]) === $questionId) {
                    $answers[$i] = $answer;
                }
            }

            Log::info(json_encode($answers));

            //更新答案
            $answerStr = implode('[++++]', $answers);

            $updateData = array(
                'id' => $paperResult['id'],
                'answers' => $answerStr
            );
            $updateResult = Db('PaperResult', [], false)->update($updateData);
//            if (empty($updateResult)) {
//                return $this->failedJson(500, 1806);
//            }
            return $this->successJson();


        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    public function submitPaper()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] != 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?post.paperId') ? input('post.paperId') : 0;


        //判断参数是否合法
        if (
            empty($paperId) || !is_int($paperId) || $paperId < 0
        )
        {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //判断试卷状态
            if (!$paper['status']) {
                return $this->failedJson(403, 1801);
            }

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paperId], ['user_id', '=', $userInfo['id']]])->find();
            if (!empty($paperResult) && $paperResult['status'] > 1) {
                return $this->failedJson(403, 1802);
            }

            if (empty($paperResult)) {
                return $this->failedJson(500, 1002);
            }

            //计算客观题分数
            $answers = array();
            if (!empty($paperResult['answers'])) {
                $answers = explode('[++++]', $paperResult['answers']);
            }

            $questionIds = explode('|', str_replace(']', '', str_replace('[', '', $paper['question_ids'])));

            //添加结果
            $rule = json_decode($paper['rule'], true);
            if (empty($rule)) {
                return $this->failedJson(500, 1002);
            }

            $objectiveScore = 0;
            if (!empty($answers)) {
                for ($i= 0; $i < count($questionIds); $i++) {
                    //获取当前考试题目
                    $question = Db('Question', [], false)->where('id', $questionIds[$i])->find();
                    if (empty($question)) {
                        return $this->failedJson(500, 1805);
                    }
                    if ($question['type'] == 'choice' || $question['type'] == 'judge') {
                        if (strtolower($question['answer']) === strtolower($answers[$i])) {
                            if ($question['type'] == 'choice') {
                                $objectiveScore += $rule['choice']['score'];
                            } else if ($question['type'] == 'judge') {
                                $objectiveScore += $rule['judge']['score'];
                            }
                        } else {
                            //查找错题是否已存在
                            $errorQuestion = Db('ErrorQuestion', [], false)->where([['question_id', '=', $question['id']],['user_id', '=', $userInfo['id']]])->find();
                            if (empty($errorQuestion)) {
                                $errorQuestion = array(
                                    'question_id' => $question['id'],
                                    'user_id' => $userInfo['id'],
                                    'count' => 1,
                                    'status' => 1
                                );
                                $insertErrorQuestion = Db('ErrorQuestion', [], false)->insert($errorQuestion);
                                if (empty($insertErrorQuestion)) {
                                    return $this->failedJson(500, 1002);
                                }
                            } else {
                                $updateErrorQuestion = array(
                                    'id' => $errorQuestion['id'],
                                    'count' => $errorQuestion['count'] + 1
                                );
                                $updateErrorQuestionResult = Db('ErrorQuestion', [], false)->update($updateErrorQuestion);
                                if (empty($updateErrorQuestionResult)) {
                                    return $this->failedJson(500, 1002);
                                }
                            }
                        }
                    }
                }
            }

            $updateData = array(
                'id' => $paperResult['id'],
                'objective_score' => $objectiveScore,
                'status' => 2
            );
            $updateResult = Db('PaperResult', [], false)->update($updateData);
            if (empty($updateResult)) {
                return $this->failedJson(500, 1807);
            }
            return $this->successJson(['objectiveScore' => $objectiveScore]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    public function getReadAnswerQuestion()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        //获取参数
        $paperId = input('?post.paperId') ? input('post.paperId') : 0; //试卷id

        $studentId = input('?post.studentId') ? input('post.studentId') : 0; //用户id


        if (
            empty($paperId) || !is_int($paperId) || $paperId < 0
            || empty($studentId) || !is_int($studentId) || $studentId < 0
        ) {
            return $this->failedJson(400, 1000);
        }


        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //获取出题规则
            $rule = json_decode($paper['rule'], true);


            //查询操作用户是否在班级中
            if ($userInfo['role'] != 'admin') {
                $map[] = ['class_id', '=', $paper['class_id']];
                $map[] = ['user_id', '=', $userInfo['id']];
                $map[] = ['status', '=', 1];
                $count = Db('ClassUser', [], false)->where($map)->count();
                if (!$count) {
                    return $this->failedJson(403, 1700);
                }
            }

            //获取简答题
            $answerQuestions = Paper::getQuestions($paper['question_ids'], true, 'answer');

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paperId], ['user_id', '=', $studentId]])->find();

            if (empty($paperResult) || $paperResult['status'] < 2) {
                return $this->failedJson(403, 1808);
            }


            if (empty($paperResult['answers'])) {
                return $this->failedJson(500, 1813);
            }

            $studentAnswerScores = array();
            if (!empty($paperResult['subjective_score_str'])) {
                $studentAnswerScores = explode(',', $paperResult['subjective_score_str']);
            }

            $studentAnswers = explode('[++++]', $paperResult['answers']);
            $totalQuestionCount = count($studentAnswers);
            $answerQuestionCount = count($answerQuestions);
            $index = 0;
            for ($i = $totalQuestionCount - $answerQuestionCount; $i < $totalQuestionCount; $i++) {
                $answerQuestions[$index]['studentAnswer'] = $studentAnswers[$i];
                $answerQuestions[$index]['ruleAnswerScore'] = $rule['answer']['score'];
                if (empty($studentAnswerScores) || empty($studentAnswerScore = $studentAnswerScores[$index])) {
                    $answerQuestions[$index]['studentAnswerScore'] = 0;
                } else {
                    $answerQuestions[$index]['studentAnswerScore'] = $studentAnswerScore;
                }
                $index++;
            }
            return $this->successJson($answerQuestions);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    public function readAnswerQuestion()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        if ($userInfo['role'] == 'student') {
            return $this->failedJson(403, 1001);
        }

        //获取参数
        $paperId = input('?post.paperId') ? input('post.paperId') : 0; //试卷id

        $studentId = input('?post.studentId') ? input('post.studentId') : 0; //用户id

        $answerScores = input('?post.answerScores') ? input('post.answerScores/a') : array();


        if (
            empty($paperId) || !is_int($paperId) || $paperId < 0
            || empty($studentId) || !is_int($studentId) || $studentId < 0
            || empty($answerScores) || !is_array($answerScores) || !checkValidIds($answerScores)
        ) {
            return $this->failedJson(400, 1000);
        }


        try {
            //获取要考试的试卷
            $paper = Db('paper', [], false)->where([['id', '=', $paperId], ['status', '=', 1]])->find();
            if (empty($paper)) {
                return $this->failedJson(404, 1800);
            }

            //获取出题规则
            $rule = json_decode($paper['rule'], true);
            if (count($answerScores) !== $rule['answer']['count']) {
                return $this->failedJson(400, 1811);
            }
            foreach ($answerScores as $score) {
                if ($score > $rule['answer']['score']) {
                    return $this->failedJson(400, 1810);
                }
            }

            //查询操作用户是否在班级中
            if ($userInfo['role'] != 'admin') {
                $map[] = ['class_id', '=', $paper['class_id']];
                $map[] = ['user_id', '=', $userInfo['id']];
                $map[] = ['status', '=', 1];
                $count = Db('ClassUser', [], false)->where($map)->count();
                if (!$count) {
                    return $this->failedJson(403, 1700);
                }
            }

            //查看考试状态
            $paperResult = Db('PaperResult', [], false)->where([['paper_id', '=', $paperId], ['user_id', '=', $studentId]])->find();
            if (empty($paperResult) || $paperResult['status'] < 2) {
                return $this->failedJson(403, 1808);
            }

            //判断试卷是否已批阅
            if (!empty($paperResult) && $paperResult['status'] > 2) {
                return $this->failedJson(403, 1809);
            }

            //更新简答题分数
            $subjectiveScoreStr = implode(',', $answerScores);
            $subjectiveScore = array_sum($answerScores);

            $updateData = array(
                'id' => $paperResult['id'],
                'subjective_score' => $subjectiveScore,
                'subjective_score_str' => $subjectiveScoreStr,
                'read_user_id' => $userInfo['id'],
                'read_time' => time(),
                'status' => 3
            );

            $updateResult = Db('paperResult', [], false)->update($updateData);
            if (!$updateResult) {
                return $this->failedJson(500, 1812);
            }
            return $this->successJson();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }
}