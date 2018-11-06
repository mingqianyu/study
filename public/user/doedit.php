<?php
//获取参数
$id = $_POST['id'];
$username = $_POST['username'];

$link = mysqli_connect('localhost', 'root', '123456', 'jingyingba');

$sql = "update member set username = '$username' where id = " . $id;
$result = mysqli_query($sql);

if (mysqli_affected_rows($link) > 0) {
	echo "更新成功！";
	header("location: main.php");
} else {
	echo "更新失败！";
}