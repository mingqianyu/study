<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/17
 * Time: 上午11:29
 */

namespace app\index\controller;

use think\Exception;
use think\facade\Request;

class ErrorQuestion extends Base
{
    public function getErrorQuestions()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        //获取参数
        $category = input('?post.category') ? input('post.category/a') : array();

        $page = input("?post.page") ? input("post.page") : 0;

        $pageSize = input("?post.pageSize") ? input("post.pageSize") : 0;

        if (!is_numeric($page) || $page < 0 || !is_numeric($pageSize) || $pageSize < 0) {
            return $this->failedJson(400, 1000);
        }

        $isValidCategory = $this->isValidCategory($category);

        if ($isValidCategory !== true) {
            return $this->failedJson(400, $isValidCategory);
        }

        try {
            //获取错误题目
            $totalCount = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview, eq.count')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->leftJoin('ErrorQuestion eq', 'q.id = eq.question_id')
                ->where([['q.status', '=', 1], ['eq.user_id', '=', $userInfo['id']]])
                ->whereIn('q.knowledge_id', $category['knowledgeIds'])
                ->count();

            $errorQuestions = Db('Question', [], false)
                ->alias('q')->field('q.id, q.title, q.options, q.answer, q.analysis, q.knowledge_id, k.name knowledgeName, q.type, q.level, q.isInterview, eq.count')
                ->leftJoin('Knowledge k', 'q.knowledge_id = k.id')
                ->leftJoin('ErrorQuestion eq', 'q.id = eq.question_id')
                ->where([['q.status', '=', 1], ['eq.user_id', '=', $userInfo['id']]])
                ->whereIn('q.knowledge_id', $category['knowledgeIds']);

            if ($page > 0 && $pageSize > 0) {
                $start = ($page - 1) * $pageSize;
                $errorQuestions = $errorQuestions->limit($start, $pageSize);
            }
            $errorQuestions = $errorQuestions->select();

            if (empty($errorQuestions)) {
                $errorQuestions = array();
            }
            return $this->successJson(['totalCount' => $totalCount, 'page' => $page, 'pageSize' => $pageSize, 'errorQuestions' => $errorQuestions]);
        } catch (Exception $e) {
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