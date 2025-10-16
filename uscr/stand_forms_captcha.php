<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header('Content-type: text/html;charset=windows-1251');

setlocale(LC_ALL, "ru_RU.CP1251");


$gCidTimeout=10;
//========UpdateCID
$lCID=$_COOKIE['CID'];

if (strlen($lCID)<=0) {
    $lCID=uniqid('dg1', true);
}

SetCookie('CID', $lCID, time()+365*24*3600);
//========UpdateCID



require_once('check_ip.php');


$form_type = (int) $_REQUEST['form_type'];
require_once("$form_type/reciever.php");


$siteUrl		= parse_url(getenv('HTTP_REFERER'));
$siteName		= $siteUrl['host'];
$lSubject = '=?cp1251?B?' . base64_encode("Сообщение с сайта $siteName") . '?=';
$senderSubject = '=?cp1251?B?' . base64_encode("Ваше сообщение с сайта $siteName отправлено") . '?=';
$mailNameInBody	= "С сайта $siteName было отправлено сообщение:";


require_once('Color.php');
require_once('SimpleCaptcha.php');

function checkCaptchaAnswer() {
	session_start();
	$answer = $_REQUEST['captcha_answer'];
	return SimpleCaptcha::isValidAnswer($answer);
}


//====================Format data functions========================================
//Cecking for required fields
function mailform_checkFields() {
	if(empty($_REQUEST['formdata']) && empty($_REQUEST['formRequired'])) {
		return false;
	}

	if (!isset($_REQUEST['formdata'])) {
		$_REQUEST['formdata'] = array();
	}

	if (array_key_exists('Телефон', $_REQUEST['formRequired']) && array_key_exists('EMAIL_E-mail', $_REQUEST['formRequired'])) {
		if (strlen($_REQUEST['formRequired']['Телефон']) < 3 && strlen($_REQUEST['formRequired']['EMAIL_E-mail']) < 5) return false;

	}


	if (isset($_REQUEST['formRequired'])) {
		foreach ($_REQUEST['formRequired'] as $key => $val) {
			if (!is_array($val) && strlen($val) <= 0) {
				//return false;
			} else {
				$_REQUEST['formdata'][$key] = $val;
			}
		}
	}

	return true;
}



//format data. Extract MAIL and PERSON of sender (if exists)
function formatFormData() {
	global $EMAIL, $PERSON;

	foreach ($_REQUEST['formdata'] as $key => $value) {

		if (strstr($key, "EMAIL_")) {
			$EMAIL = $value;
			unset($_REQUEST['formdata'][$key]);
			$key = substr($key, 6);
		}
		if (strstr($key, "PERSON_")) {
			$PERSON .= $value.' ';
			unset($_REQUEST['formdata'][$key]);
			$key = substr($key, strpos($key, '_')+1);
		}
		if (strcmp(substr($key, 0, 1), '.') == 0) {
			$addToKey = substr($key, 1);
			$_REQUEST['formdata'][$addToKey] .= $value;
			unset($_REQUEST['formdata'][$key]);
			continue;
		}

		if(!is_array($value)) {
			$_REQUEST['formdata'][$key] = trim(str_replace("\n", "<br>", $value));
		}

	}
}

function dataSpecialFormat($pData) {
	return $pData;
}


function dataSort($pDataArray) {
	$map = array(
		"Имя" => null,
		"Телефон" => null,
		"E-mail" => null,
		"Организация" => null,
		"Сообщение" => null,
	);

	if (count($map) > 0) {
		foreach ($pDataArray as $key => $value) {
			$map[$key] = $value;
		}
	}
	else {
		$map = $pDataArray;
	}

	return $map;
}
//======================================================================================



//===================Generating mail body from formatted data===========================
//======================================================================================

function generateMailBody($pFilelds) {

	$style = ' style="background-color: #E9F2F5"';

	$formContent = '<table cellspacing="2" cellpadding="0" border="0" width="570" class="form_t" style="font: 11px Verdana;border: 1px solid #91BBC9" align=center><col width="200"><col width="*">';
	$formContent .= '<tr><th colspan="2" class="title_td" style="background-color: #A5D0E5; padding: 3px 0px 3px 0px;">Сообщение</th></tr>';

	foreach ($pFilelds as $field => $value) {
		strlen($style) <= 0 ? $style = ' style="background-color: #E9F2F5"' : $style = '';

		$valueHtml = $value;
		if(is_array($value)) {
			$valueHtml = '<ul><li>'. implode('</li><li>', $value) .'</li></ul>';
		}

		$formContent .= "<tr$style><td  style=\"padding: 3px; vertical-align: top; font-weight: bold; text-align: justify;white-space: nowrap; padding-right: 10px; font-weight: normal;\">$field</td><td style=\"padding: 3px; vertical-align: top; font-weight: bold; text-align: justify;\">$valueHtml</td></tr>\n";
	}

	if ($_FILES['formFile']['error'] === 0) {
		strlen($style) <= 0 ? $style = ' class="n"' : $style = '';
		$formContent .= "<tr$style><td class=\"left\">Файл оригинала (во вложении)</td><td>{$_FILES['formFile']['name']}</td></tr>\n";
	}
	return $formContent.'</table>';
}
//======================================================================================



//======================Parse attached file in MIME format=============================
//======================================================================================

function mimeFile($pFileId, $pBoundary, &$pName) {
	if ($_FILES['formFiles']['error'][$pFileId] == 0) {
		$mimefile = chunk_split(base64_encode(file_get_contents($_FILES['formFiles']['tmp_name'][$pFileId])));
		$pName .= "<br> {$_FILES['formFiles']['name'][$pFileId]}";
		$file = "
--$pBoundary
Content-Type: {$_FILES['formFiles']['type'][$pFileId]};
	name=\"{$_FILES['formFiles']['name'][$pFileId]}\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment;
	filename=\"{$_FILES['formFiles']['name'][$pFileId]}\"

$mimefile

";
		return $file;
	}
	else {
		return false;
	}
}

//======================================================================================
//======================================================================================






$EMAIL = '';
$PERSON = '';

$checkPassed = mailform_checkFields();
$captchaCkeckPassed = checkCaptchaAnswer();
formatFormData();

//Definening of sender (for mail header)
$lMailFrom = '=?cp1251?B?' . base64_encode("Посетитель сайта $siteName") . '?=' . " <web@$siteName>";
$senderMailFrom = '=?cp1251?B?' . base64_encode("$siteName") . '?=' . " <noreply@$siteName>";


$HEADERS = <<<HEADERS_EOT
From: $lMailFrom
Content-Type: text/html; charset=windows-1251
HEADERS_EOT;

$HEADERS_to_SENDER = <<<HEADERS_EOT
From: $senderMailFrom
Content-Type: text/html; charset=windows-1251
HEADERS_EOT;


/*====================MIME=========================
 *	If present atached file(s), then
 *	mail will in MIME format, else
 *	it will just HTML message.
 ==================================================*/
if (array_key_exists('formFiles', $_FILES)) {
	$boundary = md5(uniqid(microtime()).$_SERVER['REMOTE_ADDR']);
	$mimedFiles 		= "";
	$attachedFileName  = "";
	for ($i = 0; $i<count($_FILES['formFiles']['name']); $i++) {
		if ($tryGetFiles = mimeFile($i, $boundary, $attachedFileName)) {
			$mimedFiles .= "\n\n";
			$mimedFiles .= $tryGetFiles;
		}
	}
	if (strlen($mimedFiles) > 0) {
		$mimedFiles .= "--{$boundary}--";

		$_REQUEST['formdata']['Вложенные файлы'] = substr($attachedFileName, 5);

		$HEADERS = <<<MIME_HEADERS_EOT
From: $lMailFrom
MIME-Version: 1.0
Content-Type: multipart/mixed;
	boundary="$boundary"
	{{_lHeadersEnd_}}
This is a multi-part message in MIME format.

--$boundary
Content-Type: text/html;
	charset="windows-1251"
Content-Transfer-Encoding: Quot-Printed
MIME_HEADERS_EOT;
	}
}
/*==================================================*/


$OUT = generateMailBody($_REQUEST['formdata']);

//Generate final message
$mailBodyStyles =<<< EOF_STYLES
body {font: 10px Verdana;}
table {font: 11px Verdana;border: 1px solid #91BBC9}
th {background-color: #A5D0E5; padding: 3px 0px 3px 0px;}
tr {background-color: #E9F2F5}
tr.n {background-color: #F6F6F6}
td {padding: 3px; vertical-align: top; font-weight: bold; text-align: justify;}
td.left {white-space: nowrap; padding-right: 10px; font-weight: normal;}
EOF_STYLES;


$eMSG=<<<EOF_MAIL_LETTER
<style type="text/css">
$mailBodyStyles
</style>

<div style="text-align: center">
	$mailNameInBody
</div><br><br>




$OUT



<br><br>
<hr style="height: 1px">
<div align="right" style="font: 12px Verdana">Обработка формы: <a href="https://effect.com.ua" style="color: #0E60B4;font: bold 12px Verdana">Эffect</a></div>


$mimedFiles


EOF_MAIL_LETTER;


$msgToSender = <<<MAIL_TO_SENDER

<div style="text-align: center">
	Ваше сообщение, отправленное на сайте <a href="https://$siteName">$siteName</a>:
</div><br><br>




$OUT



<br><br>
<hr style="height: 1px">
<div align="right" style="font: 12px Verdana">Обработка формы: <a href="https://effect.com.ua" style="color: #0E60B4;font: bold 12px Verdana">Эffect</a></div>


MAIL_TO_SENDER;



$allChecksPassed = $captchaCkeckPassed && $checkPassed;

$wrongCaptchAnswerNotification = '';
if(!$captchaCkeckPassed) {
	$wrongCaptchAnswerNotification = '<img src="img/fail_captcha_txt.png" alt="" width="202" height="37" class="text">';
}


if($allChecksPassed === false) {
	$eMSG='';
	echo <<<FAIL
<!DOCTYPE html>
<html><head>
<title>Обязательные поля не завполнены, либо заполнены не корректно</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta name="viewport" content="width=400">
<style>
html,body,div,span,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,address,img,q,small,strong,b,dl,dt,dd,ol,ul,li,form,label,legend,article,canvas,figure,hgroup,audio,video{margin:0;padding:0;font-size:100%}
html,body{height:100%;width:100%;}
img{display:block;clear:both;margin:0 auto}
img.text{margin-top:40px}
img.button{margin-top:88px;cursor:pointer;-webkit-transition: all 0.1s ease;-moz-transition: all 0.1s ease;-o-transition: all 0.1s ease;-ms-transition: all 0.1s ease;transition: all 0.1s ease;}
img.button:hover{-webkit-transform: scale(0.95);-moz-transform: scale(0.95);-o-transform: scale(0.95);-ms-transform: scale(0.95);transform: scale(0.95);}
</style>
</head>
<body>
<div style="width:400px;margin: 0 auto;min-height: 100%;">
	<img src="/uscr/img/att.png" width="188" height="213" alt="Обязательные поля не завполнены, либо заполнены не корректно" style="padding-top:3%"><img src="/uscr/img/fail_txt.png" alt="" width="304" height="38" class="text">$wrongCaptchAnswerNotification<img src="/uscr/img/back.png" alt="Вернуться" width="143" height="33" class="button" onclick="javascript:history.back(1);return">
</div>
</body></html>
FAIL;
} else {


	if (mail(
	                $RECIPIENTS
	                , $lSubject
	                , $eMSG
	                , $HEADERS
	        )
	) {

		if(isset($_REQUEST['backto']) && strlen($_REQUEST['backto']) > 4) {
			header("Location: /{$_REQUEST['backto']}");
		}

		$referer = $_SERVER["HTTP_REFERER"];
		if(!$referer) $referer = '/';

		echo<<<ITSOK
<!DOCTYPE html>
<html><head>
<title>Данные успешно обработаны</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<meta name="viewport" content="width=400">
<style>
html,body,div,span,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,address,img,q,small,strong,b,dl,dt,dd,ol,ul,li,form,label,legend,article,canvas,figure,hgroup,audio,video{margin:0;padding:0;font-size:100%}
html,body{height:100%;width:100%;background:#fff;}
img{display:block;clear:both;margin:0 auto}
img.text{margin-top:40px}
img.button{margin-top:40px;cursor:pointer;-webkit-transition: all 0.1s ease;-moz-transition: all 0.1s ease;-o-transition: all 0.1s ease;-ms-transition: all 0.1s ease;transition: all 0.1s ease;}
img.button:hover{-webkit-transform: scale(0.95);-moz-transform: scale(0.95);-o-transform: scale(0.95);-ms-transform: scale(0.95);transform: scale(0.95);}
</style>
</head><body>
<div style="width:400px;margin: 0 auto;min-height: 100%;">
        <img src="/uscr/img/ok.jpg" width="344" height="422" alt="Данные успешно обработаны" style="padding:20px 0;"><img src="/uscr/img/back.png" alt="Вернуться" width="143" height="33" class="button" onclick=
"window.location.href='{$referer}'">
</body></html>
ITSOK;

	}
}
?>
