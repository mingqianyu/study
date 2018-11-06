<?php 
session_start();
include "conn.php";
//$_COOKIE 
//var_dump($_COOKIE);
echo '<hr>';
if (empty($_SESSION['username'])) {
	echo '欢迎你游客';
}else{
	if ($_SESSION['username'] == 'admin') {
		echo "欢迎管理员".$_SESSION['username'];
		//如果是管理员，那就可以显示管理用户界面
		$sql = "select * from member";
		$result = mysqli_query($link,$sql);
		if (mysqli_num_rows($result)>0) {
			//取出所有的数据
			echo "<table border='1'>";
			echo "<tr><th>编号</th><th>用户名</th><th>操作</th></tr>";
			while ($rows = mysqli_fetch_assoc($result)) {
				echo "<tr><td> {$rows['id']} </td><td> {$rows['username']} </td>
				<td>
					<a href='show.php?id={$rows['id']}&islook=1'>查看</a>
					<a href='show.php?id={$rows['id']}&islook=0'>修改</a>
					<a href='delete.php?id={$rows['id']}'>删除</a>
				</td>
				</tr>";
			}
			echo "</table>";
		}
	}else{
		echo "欢迎你普通用户".$_COOKIE['username'];
	}
}

?>
