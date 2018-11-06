<?php 
if (!empty($_COOKIE) && $_COOKIE['islogin']==1) {
	echo "<script>alert('已经登陆');window.location='main.php';</script>";
}
 ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>用户登录</title>
</head>
<body>
<center>
<form action="check_login.php" method="post">
	用户名：<input type="text" name="username"><br>
	密&nbsp;&nbsp;码： <input type="password" name="password"><br>
	<input type="submit" value="登陆">
</form>
</center>
</body>
</html>