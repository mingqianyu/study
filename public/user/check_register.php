<?php 
header("content-type:text/html;charset=utf-8");
//第一步 链接数据库
$link = mysqli_connect("localhost","root","123456","jingyingba");
mysqli_set_charset($link,"utf-8");//设置编码
//第二部 获取客户端表单数据
$user = $_POST['username'];
$pass = $_POST['password'];
$pass2 = $_POST['password2'];
if ($pass != $pass2) {
	echo "<script>alert('两次密码输入不一致');</script>";
	exit();
}
//第三部 组织sql语句
$sql = "insert into member(username,password) values ('$user',md5('$pass'))";
//第四部 发送sql语句，执行查询
$result = mysqli_query($link,$sql);//这里返回的是布尔型的值
//第五部  判断执行结果 函数返回前一次 MySQL 操作（SELECT、INSERT、UPDATE、REPLACE、DELETE）所影响的记录行数
if (mysqli_affected_rows($link)>0) {
	echo "<script>alert('注册成功');window.location='login.php';</script>";
}else{
	echo "<script>alert('注册失败');window.location='register.php';</script>";
}
//第六部 关闭数据库对象
mysqli_close($link);

 ?>