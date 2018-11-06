<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/17
 * Time: 上午9:22
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Log;
use think\facade\Request;

class Exercise extends Base
{
    /**
     * 获取练习题
     * @return \think\response\Json
     */
    public function getExercise()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        //获取参数
        $category = input('?post.category') ? input('post.category/a') : array();

        $isValidCategory = $this->isValidCategory($category);

        if ($isValidCategory !== true) {
            return $this->failedJson(400, $isValidCategory);
        }

        try {
            //获取题目
            $questions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->where('q.status', 1)
                ->whereIn('q.knowledge_id', $category['knowledgeIds'])
                ->select();
            if (empty($questions)) {
                return $this->failedJson(404, 1901);
            }

            //随机获取题目
            $question = $questions[mt_rand(0, count($questions) - 1)];

            //记录到练习题
            $insertData = array(
                'user_id' => $userInfo['id'],
                'question_id' => $question['id'],
                'category' => json_encode($category),
                'exercise_time' =>  time()
            );
            $insertId = Db('Exercise', [], false)->insertGetId($insertData);
            if (!$insertId) {
                return $this->failedJson(500, 1902);
            }
            return $this->successJson(['exerciseId' => $insertId, 'question' => $question]);
        } catch (Exception $e) {
            return $this->failedJson(500, 1002);
        }
    }

    //检验练习题
    public function verifyExercise()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        //获取参数
        $exerciseId = input('?post.exerciseId') ? input('post.exerciseId') : 0;
        $answer = input('?post.answer') ? input('post.answer') : '';

        //判断参数是否有效
        if (
            empty($exerciseId) || !is_int($exerciseId) || $exerciseId < 0
            || empty($answer)
        ) {
            return $this->failedJson(400, 1000);
        }

        try {
            //获取练习题
            $exercise = Db('Exercise', [], false)->where('id', $exerciseId)->find();
            if (empty($exercise)) {
                return $this->failedJson(404, 1903);
            }

            //获取题目
            $question = Db('Question', [], false)->where('id', $exercise['question_id'])->find();
            if (empty($question)) {
                return $this->failedJson(500, 1002);
            }

            //更新练习题
            $updateExercise = array(
                'id' => $exerciseId,
                'answer' => $answer,
                'answer_time' => time()
            );
            $updateExerciseResult = Db('Exercise', [], false)->update($updateExercise);
            if (!$updateExerciseResult) {
                return $this->failedJson(500, 1002);
            }

            //是否正确
            $isCorrect = false;
            if ($question['type'] === 'choice' || $question['type'] === 'judge') {
                if ($question['answer'] === $answer) {
                    $isCorrect = true;
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
            return $this->successJson(['isCorrect' => $isCorrect, 'answer' => $question['answer'], 'analysis' => $question['analysis']]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->failedJson(500, 1002);
        }
    }

    private function isValidCategory($category)
    {
        if (empty($category) || !is_array($category)) {
            return 1000;
        }

        if (!isset($category['courseIds']) || empty($category['courseIds']) || !is_array($category['courseIds'])) {
            return 1900;
        }

        foreach ($category['courseIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1900;
            }
        }

        if (!isset($category['courseStageIds']) || empty($category['courseStageIds']) || !is_array($category['courseStageIds'])) {
            return 1900;
        }

        foreach ($category['courseStageIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1900;
            }
        }

        if (!isset($category['knowledgeIds']) || empty($category['knowledgeIds']) || !is_array($category['knowledgeIds'])) {
            return 1900;
        }

        foreach ($category['knowledgeIds'] as $value) {
            if (!is_numeric($value) || $value <= 0) {
                return 1900;
            }
        }
        return true;
    }
}