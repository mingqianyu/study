<?php
session_start();
header("content-type:text/html;charset=utf-8");
//天龙八部 第一部链接数据库
$link = mysqli_connect('localhost','root','123456','jingyingba');
//第二部接收客户端传过来的用户名和密码
$name = $_POST['username'];
$pass = $_POST['password'];
//第三部 声明SQL语句
$sql = "select * from member where username ='$name' and password = md5('$pass')";
// echo $sql;
// exit();
//第四部 发送sql语句执行查询 mysqli_query,返回的对象
$result = mysqli_query($link,$sql);
//第五部 判断执行结果 mysqli_num_rows 返回结果集中的行数
if (mysqli_num_rows($result)>0) {
	//第六部
	//mysql_fetch_assoc() 函数从结果集中取得一行作为关联数组。
	$row = mysqli_fetch_assoc($result);
	//设置cookie为查询到的整条用户信息
	// setcookie("user",$row,time()+3600,'/');//错误的方法
//	setcookie("username",$row['username'],time()+3600,'/');
//	setcookie("islogin",1,time()+3600,'/');//设置登陆标识

	echo "<script>alert('登陆成功')</script>";
	echo "<script>window.location='main.php';</script>";
}else{
	echo '用户名密码错误';
}
//第七部 释放结果集
mysqli_free_result($result);
//第八部 关闭数据库对象
mysqli_close($link);




 ?>