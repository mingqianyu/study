<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/17
 * Time: 上午10:14
 */
//$questions = [['id' => 2], ['id' => 1], ['id' => 3], ['id' => 4]];
//$count = 3;
//$store = array();
//$totalCount = count($questions);
//$newQuestions = array();
//$newQuestionsIds = array();
//while ($count > 0) {
//    $index = mt_rand(0, $totalCount - 1);
//    $question = $questions[$index];
//    if (!isset($store[$question['id']])) {
//        $store[$question['id']] = $question['id'];
//        $newQuestions[] = $question;
//        $newQuestionsIds[] = $question['id'];
//        $count--;
//    }
//}
//
//var_dump($newQuestionsIds);
$array = explode('[++++]', '[++++][++++][++++]B[++++][++++]F[++++][++++]我是前三个字[++++]我是前四个字[++++]我是前是一个字');

var_dump($array);

var_dump(implode('[++++]', $array));

