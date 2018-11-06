<?php 
include "conn.php";
$id = $_GET['id'];
$sql = "delete from member where id = $id";
$result = mysqli_query($link,$sql);
if (mysqli_affected_rows($link)>0) {
	echo "<script>alert('删除成功');window.location='main.php';</script>";
}else{
	echo "<script>alert('删除失败');window.location='main.php';</script>";
}
mysql_close($link);
 ?>