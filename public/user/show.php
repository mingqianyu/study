<?php
/**
 * Created by PhpStorm.
 * User: zhanglei
 * Date: 2018/10/23
 * Time: 1:27 AM
 */
//获取用户id
$memberId = $_GET['id'];
$islook = $_GET['islook'];

$connect = mysqli_connect('localhost', 'root', '123456', 'jingyingba');

if (mysqli_errno($connect)) {
    echo "链接数据库失败！" . mysqli_error($connect); exit();
}

//设置编码
mysqli_set_charset($connect, 'utf-8');

//获取用户数据
$sql = "select * from member where id = " . $memberId;

//执行查询
$result = mysqli_query($connect, $sql);

$user = mysqli_fetch_assoc($result);
if (empty($user)) {
    echo "用户不存在！"; exit();
} else {
	if ($islook) {
		echo "<table border='1' cellspacing='0' cellpadding='0' style='border-collapse: collapse; margin: 0 auto;'><tr align='center'><td width='200px'>id</td><td width='200px'>" . $user['id'] . "</td></tr><tr align='center'><td>用户名</td><td>" . $user['username'] . "</td></tr><tr align='center'><td>手机号</td><td>" . $user['telphone'] . "</td></tr><tr align='center'><td>性别</td><td>" . ($user['sex'] === 1 ? '男' : '女') . "</td></tr><tr align='center'><td>分数</td><td>" . $user['score'] . "</td></tr></table>";
	} else {
		echo "<form action='doedit.php' method='post'>用户名:<input type='text' name='username' value='". $user['username'] ."'><input type='hidden' value='". $user['id'] ."'><input type='submit' value='提交'></form>";
	}
}
//释放资源
mysqli_free_result($result);
mysqli_close($connect);

