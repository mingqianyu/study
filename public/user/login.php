<?php
	session_start(); //开启session
	//判断用户是否登录
	if (!empty($_SESSION['username']) && $_SESSION['islogin'] === 1) {
		echo "<div>您已经登录了，请勿重复登录！</div>"; exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta charset="utf8">
</head>
<body>
	<form action="check_login.php" method="post" >
		用户名：<input type="text" name="username" placeholder="请输入用户名">
		密码：<input type="password" name="password" placeholder="请输入密码">
		<input type="submit" name="" value="提交">
	</form>
</body>
</html>