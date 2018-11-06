<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/17
 * Time: 下午12:47
 */

namespace app\index\controller;

use PHPExcel_IOFactory;
use Exception;
use think\facade\Log;
use think\facade\Request;

class ImportQuestion extends Base
{
    protected $importMessage = "";

    public function import()
    {
        $userInfo = getUserInfoByToken(Request::header('X-Token'));
        if (empty($userInfo)) {
            return $this->failedJson(403, 1003);
        }

        $file = request()->file('question');
        $info = $file->validate(['size'=>500 * 1024 * 1024,'ext'=>'xlsx,xls,xlsm'])->move( '../uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $filePath = '../uploads/' . $info->getSaveName();
            try {
                $result = $this->importToDB($filePath, $userInfo['id']);
                if (empty($result)) {
                    if (empty($this->importMessage)) {
                        return $this->successJson([], '导入成功！');
                    } else {
                        return $this->successJson([], $this->importMessage . '其它题目已成功导入！');
                    }

                } else {
                    return $this->failedJson(500, 2001, $result);
                }
            } catch (Exception $e) {
                return $this->failedJson(500, 2001, $e->getMessage());
            }
        }else{
            // 上传失败获取错误信息
            $error = $file->getError();
            Log::error('上传题目文件错误：' . $error);
            return $this->failedJson(500, 2000);
        }
    }

    /**
     * 导入输入到数据库
     * @param $filePath
     * @param $userId
     * @return string
     * @throws \PHPExcel_Reader_Exception | \PHPExcel_Exception | Exception
     */
    protected function importToDB($filePath, $userId)
    {
        $this->importMessage = '';

        //加载文件
        $inputFileType = PHPExcel_IOFactory::identify($filePath);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($filePath);
        $knowledgeSheet = $objPHPExcel->getSheetByName('知识点表');
        $questionSheet = $objPHPExcel->getSheetByName('题目表');
        if (empty($knowledgeSheet) || empty($questionSheet)) {
            return "表结构不规范！";
        }
        $knowledgeRow = $knowledgeSheet->getHighestRow();
        $questionRow = $questionSheet->getHighestRow();


        //导入知识点
        try {
            Db('Question', [], false)->startTrans();
            $knowledgeError = "";
            for ($i = 2; $i < $knowledgeRow; $i++) {
                $knowledgeName = $knowledgeSheet->getCell("A" . $i)->getValue(); //获取知识点名字
                $subjectCodes = explode(',', $knowledgeSheet->getCell("B" . $i)->getValue()); //获取所属学科编码
                if (empty($knowledgeName) || !preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\/_\-\s（）\(\)]{1,200}$/u', $knowledgeName) || empty($subjectCodes)) {
                    $knowledgeError .= '知识点表第' . $i . '行有问题<br>';
                }

                //查看学科是否存在
                $subjectIds = array();
                foreach ($subjectCodes as $subjectCode) {
                    $subject = Db('Subject', [], false)->where('code', $subjectCode)->find();
                    if (empty($subject)) {
                        $knowledgeError .= '知识点表第' . $i . '行学科有问题<br>';
                    }
                    $subjectIds[] = $subject['id'];
                }

                if (empty($knowledgeError)) {
                    //查看知识点名是否重复
                    $count = Db('knowledge', [], false)->where('name', htmlspecialchars($knowledgeName, ENT_QUOTES))->count();
                    if ($count == 0) {
                        $insertData = array(
                            'name' => htmlspecialchars($knowledgeName, ENT_QUOTES),
                            'subject_ids' => createIdStr($subjectIds),
                            'create_user_id' => $userId,
                            'create_time' => time(),
                            'status' => 1
                        );
                       $insertResult = Db('knowledge', [], false)->insert($insertData);
                       if (empty($insertResult)) {
                           $knowledgeError .= '知识点表第' . $i . '行插入失败<br>';
                       }
                    }
                }
            }

            $questionError = "";
            $repeatQuestion = "";
            $typeMap = array(
                '选择' => 'choice',
                '判断' => 'judge',
                '简答' => 'answer'
            );
            $levelMap = array(
                '易' => 'easy',
                '中' => 'middle',
                '难' => 'hard'
            );
            $isInterviewMap = array(
                '是' => 1,
                '否' => 0
            );
            for ($j = 2; $j < $questionRow; $j++) {
                $title = $questionSheet->getCell("A" . $j)->getValue(); //获取标题
                $options = $questionSheet->getCell("B" . $j)->getValue(); //获取选项
                $answer = $questionSheet->getCell("C" . $j)->getValue(); //获取答案
                $analysis = $questionSheet->getCell("D" . $j)->getValue(); //获取解析
                $type = $questionSheet->getCell("E" . $j)->getValue(); //获取题目类型
                $level = $questionSheet->getCell("F" . $j)->getValue(); //获取难易程度
                $knowledgeName = $questionSheet->getCell("G" . $j)->getValue(); //获取知识点名称
                $isInterview = $questionSheet->getCell("H" . $j)->getValue(); //是否是面试题
                if (empty($title) || ($type == '选择' && empty($options)) || empty($answer) || !in_array($type, array('选择', '判断', '简答')) || !in_array($level, array('易', '中', '难')) || !in_array($isInterview, array('是', '否'))) {
                    $questionError .= '题目表第' . $j . '行数据有问题！<br>';
                }

                //查询知识点是否存在
                $knowledge = Db('Knowledge', [], false)->where('name', htmlspecialchars(trim($knowledgeName), ENT_QUOTES))->find();
                if (empty($knowledge)) {
                    $questionError .= '题目表第' . $j . '行知识点不存在!<br>';
                }

                //过滤参数
                $title = htmlspecialchars($title, ENT_QUOTES);
                if ($type === '选择') {
                    $options = explode('[++++]', $options);
                    if (is_array($options) && !empty($options)) {
                        $newOptions = array();
                        $index = 0;
                        foreach ($options as $value) {
                            $option = htmlspecialchars(trim($value), ENT_QUOTES);
                            if (!empty($option)) {
                                if (strtolower(intToChar($index)) !== strtolower(substr($option, 0, 1))) {
                                    $questionError .= '题目表第' . $j . '行选项有问题!<br>';
                                }
                                $newOptions[] = $option;
                            } else {
                                $questionError .= '题目表第' . $j . '行选项有问题!<br>';
                                $newOptions[] = '';
                            }
                            $index++;
                        }
                        $options = implode('[++++]', $newOptions);
                    } else {
                        $questionError .= '题目表第' . $j . '行选项有问题!<br>';
                        $options = '';
                    }
                } else if ($type === '判断') {
                    $options = "";
                    if (!in_array($answer, array('T', 'F'))) {
                        $questionError .= '题目表第' . $j . '行答案有问题!<br>';
                    }
                } else if ($type === '简答') {
                    $options = "";
                }

                $answer = htmlspecialchars(trim($answer), ENT_QUOTES);

                $analysis = htmlspecialchars($analysis, ENT_QUOTES);

                if (empty($questionError)) {
                    //判断 title 是否已存在
                    $titleCount = Db('Question', [], false)->where('title', $title)->count();
                    if ($titleCount > 0) {
                        $repeatQuestion .= '题目表第' . $j . '行题目已存在!<br>';
                    } else {
                        $insertData = [
                            'title' => $title,
                            'options' => $options,
                            'answer' => $answer,
                            'analysis' => $analysis,
                            'knowledge_id' => $knowledge['id'],
                            'type' => $typeMap[$type],
                            'level' => $levelMap[$level],
                            'isInterview' => $isInterviewMap[$isInterview],
                            'create_user_id' => $userId,
                            'create_time' => time(),
                            'status' => 1
                        ];
                        $result = Db('Question', [], false)->insert($insertData);
                        if (empty($result)) {
                            $questionError .= '题目表第' . $j . '行插入失败!<br>';
                        }
                    }
                }
            }


            if (!empty($knowledgeError) || !empty($questionError)) {
                Db('Question', [], false)->rollback();
                $this->importMessage = $knowledgeError . $questionError . $repeatQuestion;
                return $knowledgeError . $questionError;
            } else {
                Db('Question', [], false)->commit();
                $this->importMessage = $repeatQuestion;
                return "";
            }
        } catch (Exception $e) {
            Db('Question', [], false)->rollback();
            throw $e;
        }
    }
}

