<?php

checkIp($_SERVER['REMOTE_ADDR']);

function checkIp($IP) {
		$MAX_COUNT=5;

		$str=file_get_contents('LastIPCount.txt');

		if (strpos($str, "$IP ")===false) {
			file_put_contents('LastIPCount.txt',"$IP 1");
			return;
		}

		$count=substr($str,strpos($str, ' ')+1);
		$count++; 
		file_put_contents('LastIPCount.txt',"$IP $count");

			if ($count > $MAX_COUNT) {
				exit('
<html><head>
	<title>Превышен лимит отправок форм с вашего IP-адреса</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta name="viewport" content="width=300">	
	</head>
<body>
	
	<br><br><br><br>
	<table align="center" width="250" border="0" cellpadding="15" style="border: 1px solid black; background-color: white; font: bold 12px Arial, Helvetica, Verdana">
		<tr>
			<td align="center">
				<font color=#ff0000>
					Данные НЕ ОТПРАВЛЕНЫ !!!<br>
					<br>
					Превышен лимит отправок форм с вашего IP-адреса (' . $IP . ')
				</font>
				<br>
				<br>
				<a href="javascript: history.back(1)">Вернуться</a>
			</td>
		</tr>
	</table>					
</body></html>
');
			}

}


  
?>
