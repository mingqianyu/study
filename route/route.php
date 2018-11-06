<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//中间件
$callBack = function(\think\Request $request,\Closure $next){
    $requireType = $request->header('X-Requested-With');
    if (!isset($requireType) || empty($requireType) || $requireType != 'XMLHttpRequest') {
        return json(array('status' => 0, 'code' => 1001, 'msg' => '请求不合法！', 'data' => []), 403);
    }

    //检测 token 是否有效
    $passCheckTokens = ['index/login/index', 'index/login/choicerole', 'index/login/verify'];
    $action = $request->module() . '/' . $request->controller() . '/' . $request->action();
    if (!in_array(strtolower($action), $passCheckTokens)) {
        $token = $request->header('X-Token');
        if (empty($token)) {
            return json(array('status' => 0, 'code' => 1003, 'msg' => 'token 无效！', 'data' => []), 403);
        } else {
            if (!checkToken($token)) {
                return json(array('status' => 0, 'code' => 1003, 'msg' => 'token 无效！', 'data' => []), 403);
            }
        }
    }

    return $next($request);
};

Route::post('login', 'index/Login/index')->middleware($callBack);

Route::post('choiceRole', 'index/Login/choiceRole')->middleware($callBack);

Route::post('classList', 'index/MyClass/index')->middleware($callBack);

Route::get('subjectList', 'index/Subject/index')->middleware($callBack);

Route::get('validSubjects', 'index/Subject/validSubjects')->middleware($callBack);

Route::post('addSubject', 'index/Subject/addSubject')->middleware($callBack);

Route::post('editSubject', 'index/Subject/editSubject')->middleware($callBack);

Route::post('deleteSubject', 'index/Subject/deleteSubject')->middleware($callBack);

Route::get('knowledgeList', 'index/Knowledge/index')->middleware($callBack);

Route::get('validKnowledges', 'index/Knowledge/validKnowledges')->middleware($callBack);

Route::post('addKnowledge', 'index/Knowledge/addKnowledge')->middleware($callBack);

Route::post('editKnowledge', 'index/Knowledge/editKnowledge')->middleware($callBack);

Route::post('deleteKnowledge', 'index/Knowledge/deleteKnowledge')->middleware($callBack);

Route::get('courseStageList', 'index/CourseStage/index')->middleware($callBack);

Route::get('validCourseStages', 'index/CourseStage/validCourseStages')->middleware($callBack);

Route::post('addCourseStage', 'index/CourseStage/addCourseStage')->middleware($callBack);

Route::post('editCourseStage', 'index/CourseStage/editCourseStage')->middleware($callBack);

Route::post('deleteCourseStage', 'index/CourseStage/deleteCourseStage')->middleware($callBack);

Route::get('getCourseStageById', 'index/CourseStage/getCourseStageById')->middleware($callBack);

Route::get('courseList', 'index/Course/index')->middleware($callBack);

Route::get('validCourses', 'index/Course/validCourses')->middleware($callBack);

Route::post('addCourse', 'index/Course/addCourse')->middleware($callBack);

Route::post('editCourse', 'index/Course/editCourse')->middleware($callBack);

Route::post('deleteCourse', 'index/Course/deleteCourse')->middleware($callBack);

Route::get('questionList', 'index/Question/index')->middleware($callBack);

Route::post('addQuestion', 'index/Question/addQuestion')->middleware($callBack);

Route::post('editQuestion', 'index/Question/editQuestion')->middleware($callBack);

Route::post('deleteQuestion', 'index/Question/deleteQuestion')->middleware($callBack);

Route::get('getAllCategoryTree', 'index/Question/getAllCategoryTree')->middleware($callBack);

Route::get('paperList', 'index/Paper/index')->middleware($callBack);

Route::post('addPaper', 'index/Paper/addPaper')->middleware($callBack);

Route::get('getCategoryTreeBySubjectId', 'index/Paper/getCategoryTreeBySubjectId')->middleware($callBack);

Route::get('refreshPaper', 'index/Paper/refreshPaper')->middleware($callBack);

Route::get('getPaperQuestions', 'index/Paper/getPaperQuestions')->middleware($callBack);

Route::post('changePaperStatus', 'index/Paper/changePaperStatus')->middleware($callBack);

Route::get('deletePaper', 'index/Paper/deletePaper')->middleware($callBack);

Route::post('getPaperResults', 'index/PaperResult/getPaperResults')->middleware($callBack);

Route::get('getSelfPaperResult', 'index/PaperResult/getSelfPaperResult')->middleware($callBack);

Route::get('startExam', 'index/PaperResult/startExam')->middleware($callBack);

Route::post('submitAnswer', 'index/PaperResult/submitAnswer')->middleware($callBack);

Route::post('submitPaper', 'index/PaperResult/submitPaper')->middleware($callBack);

Route::post('readAnswerQuestion', 'index/PaperResult/readAnswerQuestion')->middleware($callBack);

Route::post('getReadAnswerQuestion', 'index/PaperResult/getReadAnswerQuestion')->middleware($callBack);

Route::post('getExercise', 'index/Exercise/getExercise')->middleware($callBack);

Route::post('verifyExercise', 'index/Exercise/verifyExercise')->middleware($callBack);

Route::post('getErrorQuestions', 'index/ErrorQuestion/getErrorQuestions')->middleware($callBack);

Route::post('importQuestion', 'index/ImportQuestion/import')->middleware($callBack);

Route::get('verify', 'index/Login/verify');
return [

];
