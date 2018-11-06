<?php
//开启session
session_start();

//接收参数
$username = $_POST['username'];
$password = $_POST['password'];

//判断参数是否为空
if (empty($username) || empty($password)) {
	echo "<script>alert('用户名或密码不能为空!');</script>"; exit();
}

//连接数据库
$link = mysqli_connect("localhost", "root", "123456", "jingyingba");

//创建要执行的 sql 语句
$sql = "select * from member where username = '$username' and password = '" . md5($password) . "'";

//执行sql
$result = mysqli_query($link, $sql);

//判断是否有此用户
$rows = mysqli_num_rows($result);
if ($rows > 0) {
	//保存登录状态
	$_SESSION['username'] = $username;
	$_SESSION['islogin'] = 1;

	//跳转到主页面
	header("location:main.php");
} else {
	echo "用户登录失败！";
}
mysqli_close($link);

