<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/9/28
 * Time: 上午9:07
 */

namespace app\index\controller;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Cache;
use think\facade\Log;
use think\captcha\Captcha;

class Login extends Base
{
    public function index()
    {
        $phone = input('?post.phone') ?  input('post.phone') : "";
        $password = input('?post.password') ? input('post.password') : "";
        $code = input('?post.code') ? input('post.code') : "";

        if (empty($phone) || !is_numeric($phone) || strlen($phone) != 11 || empty($password)) {
            return $this->failedJson(400, 1000, '用户名或密码不能为空！');
        }

        if (empty($code)) {
            return $this->failedJson(400, 1102, '请输入验证码！');
        }

        //判断验证码是否有效
        if (!captcha_check($code)) {
            return $this->failedJson(400, 1102);
        }

        //先判断是否是管理员
        try {
            $user = Db('User', [], false)->where([['phone', '=', $phone], ['password', '=', md5('lanou_' . $password)], ['role', '=', 'admin'], ['status', '=', 1]])->find();
        } catch (Exception $e) {
            if (!($e instanceof ModelNotFoundException) && !($e instanceof DataNotFoundException)) {
                return $this->failedJson(500, 1002);
            }
        }

        if (!empty($user)) {

            return $this->successJson(array('id' => $user['id'], 'name' => $user['name'], 'role' => $user['role'], 'token' => $this->createToken($user)));
        }

        //获取 OA 用户信息
        $param = array(
            "question" => "sso_login",
            "username" => $phone,
            "password" => $password
        );
        $result = json_decode(curl_request(env('OA_HOST_PLUS') . '/app/app/index.php', $param), true);

        if (empty($result) || $result['status'] != '1010') {
            Log::error(json_encode($result));
            return $this->failedJson(403, 1100);
        }

        return $this->successJson($result);
    }

    public function choiceRole()
    {
        $userId = input('?post.userId') ?  input('post.userId') : 0;
        $institutionId = input('?post.institutionId') ?  input('post.institutionId') : 0;
        $role = input('?post.role') ? input('post.role') : "";
        $name = input('?post.name') ? input('post.name') : "";
        $phone = input('?post.phone') ? input('post.phone') : "";
        if (empty($phone) || !is_numeric($phone) || strlen($phone) != 11 || empty($userId) || !is_numeric($userId) || $userId <= 0 || empty($institutionId) || !is_numeric($institutionId) || ($role != 'T' && $role != 'S')) {
            return $this->failedJson(400, 1000);
        }

        $platform = 'lanouoa';
        $token = 'lanouoa' . $userId;

        //查看是否绑定
        try {
            $bindUser = Db('UserBind', [], false)->where('third_id', $token)->find();
        } catch (Exception $e) {
            if (!($e instanceof ModelNotFoundException) && !($e instanceof DataNotFoundException)) {
                return $this->failedJson(500, 1002);
            }
        }

        if (empty($bindUser)) {
            Db('user', [], false)->startTrans();
            try {
                $user = array(
                    'name' => $name,
                    'phone' => $phone,
                    'role' => $role == 'T' ? 'teacher' : 'student',
                    'status' => 1
                );

                $id = Db('User', [], false)->insertGetId($user);

                $user['id'] = $id;

                if (!empty($id)) {
                    $bindUser = [
                        'user_id' => $id,
                        'third_id' => $token,
                        'platform' => $platform
                    ];
                    $result = Db('UserBind', [], false)->insert($bindUser);
                }

                if ($id && $result) {
                    Db('User', [], false)->commit();
                } else {
                    Db('User', [], false)->rollback();
                    return $this->failedJson(500, 1002);
                }

            } catch (Exception $e) {
                Db('User', [], false)->rollback();
                return $this->failedJson(500, 1002);
            }

        } else {
            try {
                $user = Db('User', [], false)->where('id', $bindUser['user_id'])->find();
                if ($user['status'] == 0) {
                    return $this->failedJson(403, 1101);
                }
            } catch (Exception $e) {
                if (!($e instanceof ModelNotFoundException) && !($e instanceof DataNotFoundException)) {
                    return $this->failedJson(500, 1002);
                }
            }

        }

        $param = array(
            "question" => "sso_myclass_list",
            "userId" => $userId,
            "institutions_id" => $institutionId,
            "role" => $role
        );

        $subjectMap = [
            'html5' => 'h5',
            '3dp' => '3d',
            'android' => 'android',
            'art' => 'art',
            'bd' => 'jbd',
            'cocos' => 'cocos',
            'gbc' => 'gbc',
            'ios' => 'ios',
            'java' => 'java',
            'nmo' => 'nmo',
            'php' => 'php',
            'pm' => 'pm',
            'python' => 'python',
            'test' => 'test',
            'ui' => 'ui',
            'va' => 'vrar',
            'wsap' => 'ws',
            'default' => 'h5'
        ];

        $classList = json_decode(curl_request(env('OA_HOST_SAAS') . '/do/api.php', $param), true);

        if (empty($classList) || $classList['status'] != '1010') {
            return $this->failedJson(500, 1002);
        }

        Log::info(json_encode($classList));
        Db('Class', [], false)->startTrans();
        try {
            foreach ($classList['list'] as $class) {
                //判断班级是否已存在
                 $localClass = Db('Class', [], false)->where('name', $class['class'])->find();
                 Log::info(json_encode($localClass));
                if (empty($localClass)) {
                    //通过code获取学科id
                    $subject = Db('Subject', [], false)->where('code', $subjectMap[empty($class['category_tag']) ? 'default' : $class['category_tag']])->find();
                    if (empty($subject)) {
                        Db('Class', [], false)->rollback();
                        return $this->failedJson(500, 1002);
                    }
                    //存入班级
                    $classData = [
                        'name' => $class['class'],
                        'subject_id' => $subject['id'],
                        'open_date' => empty($class['class_openingDate']) ? date('Y-m-d') : $class['class_openingDate'],
                        'graduate_date' => empty($class['class_graduateDate']) ? date('Y-m-d', mktime(0, 0, 0, date('m') + 4, date('d'), date('Y'))) : $class['class_graduateDate'],
                        'status' => 1
                    ];
                    $classId = Db('Class', [], false)->insertGetId($classData);
                    if (!empty($classId)) {
                        $classUserData = [
                            'class_id' => $classId,
                            'user_id' => $user['id'],
                            'status' => 1
                        ];
                        $result = Db('ClassUser', [], false)->insert($classUserData);
                        if (empty($result)) {
                            Db('Class', [], false)->rollback();
                            return $this->failedJson(500, 1002);
                        }
                    } else {
                        Db('Class', [], false)->rollback();
                        return $this->failedJson(500, 1002);
                    }
                } else {
                    //查看是否关联此班级
                    $count = Db('ClassUser', [], false)->where([['user_id', '=', $user['id']],['class_id', '=', $localClass['id']]])->count();
                    if ($count <= 0) {
                        $classUserData = [
                            'class_id' => $localClass['id'],
                            'user_id' => $user['id'],
                            'status' => 1
                        ];
                        $result = Db('ClassUser', [], false)->insert($classUserData);
                        if (empty($result)) {
                            Db('Class', [], false)->rollback();
                            return $this->failedJson(500, 1002);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if (!($e instanceof ModelNotFoundException) && !($e instanceof DataNotFoundException)) {
                Db('Class', [], false)->rollback();
                Log::error($e->getMessage());
                return $this->failedJson(500, 1002);
            }
        }
        Db('Class', [], false)->commit();
        $token = $this->createToken($user);
        $user['token'] = $token;
        return $this->successJson($user);
    }

    /**
     * 创建 token
     * @param $user
     * @return string
     */
    private function createToken($user)
    {
        $token = md5($user['id'] . time());
        Cache::set($user['id'], $token, 24 * 60 * 60);
        Cache::set($token, json_encode($user), 24 * 60 * 60);
        return $token;
    }

    /**
     * 生成验证码
     * @return mixed
     */
    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }
}