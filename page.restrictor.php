<?php $ver = '0.1yq-MiniHEXe/beta';

$sig = '201002230115';

/* -------------------------------------------------------------

	Page Restrictor (C) Airport1 - http://www.airport1.de

	You may use this script, but copying or redistribution is prohibited!
	The blocking criteria is part of our database, it is copyrighted by
	§87b I UrhG (German law). More information on http://www.bot-trap.de/

	Install of the "PRES":
	1. Upload this file as page.restrictor.php to your webserver
	2. Chmod this file to at least 664 (so it can update itself!)
	3. Include this file in your own project, e.g. into your index.php
	AT THE TOP with: require_once('page.restrictor.php');
	4. Check whether step 2 works: call yourdomain.de/?pres=update

	Needing help or wanting more? First look into our http://www.bot-trap.de/wiki
	For more ask in the board.

	IMPORTANT NOTICE: Bot-Trap is UNCOMMERCIAL, leaded on an VOLUNTARY base.
	If you are webmaster of a high traffic site and so more stressing our
	update server you should reconsider placing a backlink to us as compensation.

	SORRY: currently it's a little bit nasty code, because it's a mix of
	different contributions. Furthermore some parts are generated code,
	and we are still in research how to tune up this code!

	--------------------------------------------------------------- */


// small script to check correct install, call it by ?pres=check
if(!function_exists('presCheck')) { // topic 89130
	function presCheck($sig, $ver)
	{
		$rightsPres = (@is_writable(PRES_BASEFILE)) ? 'YES' : 'NO';
		$canUpdate = (@is_callable('fsockopen')) ? 'YES' : 'NO';
		$rightsLog = (@is_writable(PRES_LOG_FILE)) ? 'YES' : 'NO';
		$isLogged = (@file_exists(PRES_LOG_FILE)) ? 'YES, is writable: ' . $rightsLog : 'NO';

		$presConsts = array(
		'PRES_BLACKLIST_IP', 'PRES_WHITELIST_IP',
		'PRES_BLACKLIST_IPR', 'PRES_WHITELIST_IPR',
		'PRES_BLACKLIST_UA', 'PRES_WHITELIST_UA',
		'PRES_BLACKLIST_URI',
		'PRES_LOCK_MSG',
		'PRES_LOG_FILE',
		'PRES_CHECK_GOOGLEBOT',
		'PRES_CHECK_QUERY'
		);

		$presDefinedConsts = '';
		foreach ($presConsts as $presConst) {
			if (defined($presConst)) $presDefinedConsts .= "$presConst<br>";
		}

		die("Page Restrictor v$ver - Signatures $sig - Last modified: " . @date ('d.m.Y H:i:s', filemtime(PRES_BASEFILE)) . ' - FileSize: '.filesize(PRES_BASEFILE).'<br>
			<br>local (request_uri): ' . @$_SERVER['REQUEST_URI'] . '<br>uri (php_self): ' . @$_SERVER['PHP_SELF'] . '<br>server: ' . @$_SERVER['HTTP_HOST'] . '
			<br>is writable: ' . $rightsPres . '<br>does fsockopen() work: ' . $canUpdate . '
			<br><br>LogFile exists: ' . $isLogged . '
			<br><br>Constants defined:<br>' . $presDefinedConsts);
	}
}

// auto updater to update script itself with new signatures
if (!function_exists('presAutoUpdater')) {
	function presAutoUpdater($sig)
	{
		if (!@is_callable('fsockopen')) return;

		$fp = @fsockopen("www.bot-trap.de", 80, $errno, $errstr, 10);
		if (!$fp) {
			die("AutoUpdater failed: $errstr ($errno)");
		}
		$thisHost = htmlspecialchars(@$_SERVER['HTTP_HOST']);
		$thisServerName = htmlspecialchars(@$_SERVER['SERVER_NAME']);
		if (@function_exists('stream_set_timeout')) @stream_set_timeout($fp, 10);
		@fwrite($fp,
		"GET /download.php?do=update&sig=$sig&server=$thisHost&servername=$thisServerName&nocache=0 HTTP/1.0\r\n" .
		"Host: www.bot-trap.de\r\n" .
		"User-Agent: PRES AutoUpdater\r\n" .
		"Referer: .\r\n" .
		"Connection: close\r\n" .
		"\r\n"
		);
		$response = '';
		while (!feof($fp)) {
			$response .= @fread($fp, 10240);
		}
		@fclose($fp);
		$content = trim((string)strstr($response, "\r\n\r\n"));
		unset($response);
		if (substr($content, - 2) == '?>') { // overwrite only if file could be downloaded completely
			$fp = @fopen(PRES_BASEFILE, 'wb');
			@fwrite($fp, $content);
			@fclose($fp);
		} elseif (isset($_GET['pres']) && $_GET['pres'] == 'update') {
			if ($content == 'You have the latest Version!')
			die('Unable to Update: Already up-to-date!');
			elseif ($content == 'You are not allowed to Update!')
			die('Unable to Update: Not allowed');
			else
			die('Unable to Update: Incomplete file transfer or other error');
		}
		if (isset($_GET['pres']) && $_GET['pres'] == 'update')
		die("Page Restrictor Update tried. Check and compare against old Version here: <a href='?pres=check'>click</a>"); //  to v$ver with signatures from $sig
	}
}

// throw a http 403 spam protection message with a captcha
if (!function_exists('presRestrictMessage')) {
	function presRestrictMessage($reason, $data)
	{
		global $sig;

		if (!defined('PRES_LOCK_MSG')) {
			$PRES_messageEN = '
		<td>
		<b>Sorry for the inconvenience!</b><br>
		Obviously your access to this site has been suspended by mistake.<br>
		By solving the arithmetical problem you can visit this website temporarily (Cookies needed).<br>
		Please visit the <a href="http://www.bot-trap.de/unlock.php?sig='.$sig.'" target="_blank">Complaint Board</a> and <b>tell us to remove the lock restriction</b>.
		</td>
		';
			$PRES_messageDE =  '
		<td>
		<b>Entschuldigen Sie bitte diese Unannehmlichkeit!</b><br>
		Offensichtlich wurde Ihnen der Zugang zu dieser Site f&auml;lschlicherweise verweigert.<br>
		Durch L&ouml;sung der Rechenaufgabe k&ouml;nnen Sie diese Webseite tempor&auml;r besuchen (Cookies ben&ouml;tigt).<br>
		Bitte melden Sie sich <a href="http://www.bot-trap.de/unlock.php?sig='.$sig.'" target="_blank">im Beschwerdeforum</a>, um die <b>Sperrung aufzuheben</b>.
		</td>
		';
			
			$forbiddenCustomMessage = 
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">    
		<html>
			<head>
			<title>Page Restrictor Ping</title>
			</head>    
			<body>
				<h1>Web spam protection / Schutzma&szlig;nahme gegen Web Spam</h1>
				<table cellpadding="5" border="1">
				<tr>
		' . ((substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == "de") ? $PRES_messageDE . $PRES_messageEN : $PRES_messageEN . $PRES_messageDE) .
			'
				</tr>
				<tr>
					<td colspan="2" align="center">
				{PRES_CAPTCHA}
					</td>
				</tr>
				</table>
				<hr>
				<i>protected by the site owner with help and on the base of 
					<a href="http://www.airport1.de/" target="_blank">Airport1</a> PageRestrictor, 
					<a href="http://www.bot-trap.de/">Bot-Trap.de</a>
				</i>
				<!-- preslock -->    
			</body>
		</html>
		';
		} else {
			$forbiddenCustomMessage = PRES_LOCK_MSG;
		}
		if (session_id() == '') session_start();
		if (!isset($_SESSION['pres_unlock'])) {
		
			pres_log($reason, $data);
			
			// NEW:
			// if ( defined('PRES_STAT') && @is _callable('fsockopen') ) { // topic 56885 - in first weeks every client pres collects statistics ( || true ), later only these with defined PRES_STAT constant
			$rnd = rand(1,10); // its enough if about every 10th lock is reported
			if ($rnd == 8 && @is_callable('fsockopen')) {
				$fp = @fsockopen("www.bot-trap.de", 80, $errno, $errstr, 10);
				if ($fp) {
					$cip =  @$_SERVER['REMOTE_ADDR']; // topic 83862
					@fwrite($fp, "GET /stat.php?reason=$reason&data=$data&cip=$cip&sig=$sig HTTP/1.0\r\nHost: www.bot-trap.de\r\nUser-Agent: PRES FreqStat\r\nReferer: .\r\nConnection: close\r\n\r\n");
					@fclose($fp);
				}
			}

			pres_captcha($forbiddenCustomMessage, $reason, $data);
		}
	}
}

// extra function, as some want to log captcha solvers only, see topic 88977
if (!function_exists('pres_log')) {
	function pres_log($reason, $data, $captchaState = "?") {
		if (PRES_LOG_FILE != '') {
			//TODO : limit the log file e.g. for a default size if no constant PRES_LOG_LIMIT set? see topic http://www.bot-trap.de/forum/index.php?topic=89131.0
			$fp = @fopen(PRES_LOG_FILE, 'a+');
			if ($fp) { // log only iffile available
				$thisHost = (!empty($_SERVER['REMOTE_HOST'])) ? htmlspecialchars(@$_SERVER['REMOTE_HOST']) : @gethostbyaddr(PRES_CLIENT_IP);
				$log = @date('d.m.Y H:i:s') . ' - ' . PRES_CLIENT_IP . " $thisHost - " . PRES_CLIENT_UA . ' - ' . PRES_CLIENT_REF . ' - ' . PRES_REQUEST . " - $reason - $data - $captchaState\n";
				fwrite($fp, $log);
				fclose($fp);
			}
		}
	}			
}

// math captcha so people can free themself if locked by mistake
if (!function_exists('pres_captcha')) {
	function pres_captcha($forbiddenCustomMessage, $reason, $data)
	{
		global $sig; // workaround to detect castrated pres
		
		$pres_numbers = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
		$pres_operands = array('+', '-', '*');
		$pres_number1 = $pres_numbers[array_rand($pres_numbers)];
		$pres_number2 = $pres_numbers[array_rand($pres_numbers)];
		$pres_operand_key = array_rand($pres_operands);
		$pres_operand = $pres_operands[$pres_operand_key];

		if (isset($_POST['pres_submit']) && $_POST['pres_submit'] == 'go') {
			$pres_code = $_POST['pres_code'];
			$pres_number1 = (int)$pres_code {
				0} ;
			$pres_operand = (int)$pres_code {
				1} ;
			$pres_number2 = (int)$pres_code {
				2} ;
			switch ($pres_operand) {
			case 0: if ($pres_number1 + $pres_number2 == - (int)$_POST['pres_captcha']) $pres_unlock = 1;
				break;
			case 1: if ($pres_number1 - $pres_number2 == - (int)$_POST['pres_captcha']) $pres_unlock = 1;
				break;
			case 2: if ($pres_number1 * $pres_number2 == - (int)$_POST['pres_captcha']) $pres_unlock = 1;
				break;
			default: die('PRES error (CAPTCHA-Check)');
			}
			if (isset($_POST['email']) && $_POST['email'] != '') { // extra bot trap (bots may fill this field)
				pres_log($reason, $data, "B");
				unset($pres_unlock);
				header ("Location: http://www.bot-trap.de/spam.php?reason=$reason&data=$data&request=" . PRES_REQUEST);
				die ("NEARLY OK!");
			}
			if (isset($pres_unlock)) {
				pres_log($reason, $data, "Y");
				if (session_id() == '') session_start(); // avoid notice
				$_SESSION['pres_unlock'] = true;
				die (str_pad("OK! <img src='http://www.bot-trap.de/mal.php?reason=$reason&data=$data&request=" . PRES_REQUEST . "' width=1 height=1> <a href='" . @$_SERVER['REQUEST_URI'] . "'>click</a>", 1024));
			} else {
				pres_log($reason, $data, "N");				 
				die (str_pad('NOT OK!', 1024)); // fillup to avoid IE BUG
			}
		} else {
			header("HTTP/1.0 403 Forbidden");
			$presCAPTCHA = '<form action="' . @$_SERVER['REQUEST_URI'] . '" method="post"><p>
		(' . $pres_number1 . ' ' . $pres_operand . ' ' . $pres_number2 . ') &times; (&ndash;1) <span style="display:none;visibility:none"><input type="text" name="email" value=""> result:</span> = <input type="text" name="pres_captcha" size="3">
		<input type="hidden" name="pres_code" value="' . $pres_number1 . $pres_operand_key . $pres_number2 . '">
		<input type="submit" name="pres_submit" value="go"> sig:' . $sig . '
	</p></form>';
			// for korizon detection whether pres lock works
			if (strpos($forbiddenCustomMessage, '<!-- preslock -->') === false) {
				$forbiddenCustomMessage .= '<!-- preslock -->';
			}

			if (strpos($forbiddenCustomMessage, '{PRES_CAPTCHA}') === false) {
				$forbiddenCustomMessage .= $presCAPTCHA;
			} else {
				$forbiddenCustomMessage = str_replace('{PRES_CAPTCHA}', $presCAPTCHA, $forbiddenCustomMessage);
			}
			die ($forbiddenCustomMessage);
		}
	}
}

// check for correct install: headers SHOULD NOT be sent before, else Math Captcha will NEVER work!
if (@headers_sent() && !defined('PRES_SUPPRESS_WARNINGS')) {
	echo '<h4 style="font-color:red">Warning: Page Restrictor not installed correctly - Headers already sent!</h4>';
	return;
}

// pres base config
define('PRES_BASEPATH', str_replace('\\', '/', @dirname(__FILE__)));
define('PRES_BASEFILE', PRES_BASEPATH . '/' . @basename(__FILE__));
if (!defined('PRES_LOG_FILE')) define('PRES_LOG_FILE', PRES_BASEPATH . '/page.restrictor.log');

// client signatures
if (!defined('PRES_CLIENT_IP')) define('PRES_CLIENT_IP', @$_SERVER['REMOTE_ADDR']); // topic 56653
define('PRES_CLIENT_UA', htmlspecialchars(@$_SERVER['HTTP_USER_AGENT']));
define('PRES_CLIENT_REF', (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '')); // topic 36673
define('PRES_REQUEST', htmlspecialchars(@$_SERVER['HTTP_HOST']) . @$_SERVER['REQUEST_URI']);

// faster access than constants
$presCI = PRES_CLIENT_IP;
$presCIL = ip2long($presCI);
$presO1 = (($presCIL >= 0) ? $presCIL>>24 : (($presCIL &0x7fffffff)>>24)|0x80); // topic 57403
$presO234 = $presCIL &0xffffff;
$presO234h = dechex($presO234);

// optional: check for googlebot, iff REAL leave script, else deny
if (defined('PRES_CHECK_GOOGLEBOT') && stristr(PRES_CLIENT_UA, 'googlebot')) {
	$presRDNS = @gethostbyaddr(PRES_CLIENT_IP);
	if (strpos($presRDNS, '.googlebot.com') === strlen($presRDNS) - 14 && @gethostbyname($presRDNS) == PRES_CLIENT_IP) { // topic 30365
		return;
	} else {
		presRestrictMessage('botfake', PRES_CLIENT_UA);
	}
}

// user actions and a guess possibility to launch auto updater
$presRnd = rand(1, 10000);
if (isset($_GET['pres'])) {
	switch ($_GET['pres']) {
	case 'update' : $presRnd = 64;
		break;
	case 'check' : presCheck($sig, $ver);
		break;
	}
}
if ($presRnd == 64) presAutoUpdater($sig);

// if ip is own server ip leave script [but: risk of shared hosting abuse]
if ($presCI == @$_SERVER['SERVER_ADDR']) return;

// BEGIN silt
// binary tree to divide the search space, topic 37425, using strings as (also pure integer) php arrays are 10 times slower
if ($presO1 < 133) { // 0..132
  if ($presO1 < 78) { // 0..77
    if ($presO1 < 60) { // 0..59
      if ($presO1 < 24) {

if     ($presO1 == 8) { $presIPs=' 71a1b 9d102 115441 11aad0 1504fe '; }
elseif ($presO1 == 12) { $presIPs=' 63af9 182f0a 6668a7 6a9604 818851 b5cc24 bef195 '; }
elseif ($presO1 == 18) { $presIPRs='f60200/f602ff'; }

      } else {

if     ($presO1 == 24) { $presIPRs='2c0000/2f1463 2f1465/2fffff 160000/163fff'; $presIPs=' bb39 16e1f 8d13b c190d 11503d 12acc5 133c21 1467b5 15538d 3e8ffd 3f0e7b 486cc2 626d90 6fa018 802151 80224f 8b3678 97dffb 9ae40b 9f0cb3 ab9a8b acdcda bb64ab bdc9db bf3faa c58c75 e7f7fc fdc120 ff15f3 '; }
elseif ($presO1 == 38) { $presIPRs='63ab00/63abff 640831/640836 642940/64297f 695300/6953ff'; $presIPs=' 621342 627854 65947e 664a13 67a79d 6be482 '; }
elseif ($presO1 == 41) { $presIPRs='cf0000/cf1fff cfc000/cfdfff eace00/eaceff f80000/f8ffff'; $presIPs=' 48db4d 691906 7085b2 838133 8d0bd0 8d5194 91c37d 98134f 9a0214 be1011 c48225 c4cfb5 c4f40a c4f4f3 cbd88e cbd8e6 cbe9d6 cbeb25 cc5e4a ccc3d4 cda8b5 cfa206 d205c7 d2082d d57e06 d72292 d72c2a d7a086 daee2e db3965 dbdfef dc281d dc4b03 dc4b10 dce8e6 dd9216 de025d df3c3c df9d0e e20feb e2160d f976f8 fc1eae '; }
elseif ($presO1 == 58) { $presIPRs='94800/948ff 168000/168fff 3c0000/3fffff 480000/4fffff 780000/7fffff 8c0000/8fffff 940000/97ffff 9c0000/9fffff d605a0/d605af e00000/efffff f60000/f7ffff fc0000/fdffff'; $presIPs=' 4466a 1495de 1bda41 1bdd6a 1e2034 306349 313a39 386c72 4409f2 892f09 898464 b004ef b02130 b1d1d8 b525ee bc02cd bf9e2c d3da4a d8975e dafa6f ddcca4 f0ed20 f18611 f8d867 '; }
elseif ($presO1 == 59) { $presIPRs='580000/5effff 6a0000/6affff b00000/b9ffff'; $presIPs=' 38996 4623d 75be3 f69c0 13eb36 1437c7 18e5b4 24629a 25a14e 275c94 27b568 333ca9 346129 3a96a1 3c7f31 5f6748 5fb968 6c35fe 78f417 7db120 917f53 a1928a a25239 a43383 a456f7 '; }
      
      }
    } else { // 60..77    
      if ($presO1 < 69) {

if     ($presO1 == 60) { $presIPRs='380000/39ffff b78000/b7ffff c20000/c3ffff d00000/d9ffff f80000/f8ffff'; $presIPs=' a8667 aa453 131807 13180a 15ce0a 24b583 3184c2 35b47c acb30e be3bf0 bf592c bfeae2 dce067 f23ec8 f70629 fb1bc8 fb36d0 fba5b5 '; }
elseif ($presO1 == 61) { $presIPRs='60000/6bfff 110000/12ffff 134100/1341ff 135600/1356ff 13f900/13f9ff 180000/1bffff 1ca200/1ca2ff 200000/2bffff 328000/32ffff 370000/37ffff 3c2000/3c7fff 420000/42ffff 4e0000/55ffff 5a0000/5affff 5f9000/5f90ff 600000/6fffff 800000/81ffff 840000/847fff 885d00/885dff 900000/93ffff 990a40/990a5f b70000/b8ffff bf0000/bfffff dc0000/e7ffff f00000/f3ffff f60000/f6ffff f7de00/f7deff f80000/ffffff'; $presIPs=' 58b6a 7a36a 849ea c3d52 109a52 1337f0 148adc 148b8f 1fd582 2f0747 3e47d6 3f009c 3f0a96 43f0bd 591645 595186 7a2482 8235d2 82f644 87a2bd 87a2be 87a2dd 87a5ce 87a827 87a921 883b22 8b69a3 8cf3c1 9995cd 9ea3bf a0c8d4 a0d4f2 b1f8ca b20e7d b24a27 bab79e babe84 c3a68d e8cefb eb754b f70726 f70c95 f72574 '; }
elseif ($presO1 == 62) { $presIPRs='3da400/3da4ff 43f400/43f4ff 4bca00/4bcaff 4bdc00/4bdcff 760000/76ffff 8c1700/8c17ff 8d3000/8d38ff 8d3a00/8d3aff 9609d8/96d8dd 9c8f80/9c8f97 9fe540/9fe547 abc200/abc3ff b70000/b71fff c1f000/c1ffff d54000/d57fff'; $presIPs=' 16ef2 16ef5 256d6 29c82 2b6e5 3efaa 18ea15 296fa4 2b1bbe 2ba37d 3c881c 43eb94 4bfcc0 506254 50c8b8 519162 57f437 59bbd5 5e14a5 65405b 6c66ec 6d4b01 6d5713 71560f 7a9005 801041 8c8992 8e5664 8fad61 9225d0 948001 95a18d 95a211 95cf5a 998cad 9f3a4b a2d849 a59c02 ada192 b2d91c b73c81 c10d88 c1d3de c1e1ac c1e404 c1e860 c5289a cdd4a3 ceb342 d443cf d5000d d66521 d7e572 d89d22 da3d56 db8473 e17bc2 e90901 e9215e e98ab4 f3e0b3 f86b12 '; }
elseif ($presO1 == 63) { $presIPRs='d80000/dfffff'; $presIPs=' 917a1a 927624 c14994 d6ec74 e6b0ca fd9edc '; }
elseif ($presO1 == 64) { $presIPRs='0/1d7a1 1d7a7/3ffff 52000/53fff 164000/165fff 1b0500/1b05ff 220000/22c5ab 2f0000/2fffff 3e8000/3ea7ff 3ea900/3effff 478000/47bfff 485930/48593f bf0000/bf7fff f60f00/f63fff f6a580/f6a5ff'; $presIPs=' 66d32 f42ea f9fa9 10a50c 143817 1562c4 1562ce 167da2 193848 1b3b0b 260332 260536 35fd27 384039 384262 3842d2 38439a 384e0a 4ea5d9 4f4872 55a3b6 59eddb 5e43cb 737b8e 78b372 78ef83 78ef94 7a0dc4 7a0dfc 7c8aa6 7e4784 82a638 82a97b 8ae3d1 b67cd4 ba8084 ba87f6 bb1c04 bcc861 c10317 ca66da caa399 d35d2a d35d2d d6bfd2 eb3698 eb9dea f6a11e f6a12a f6a1be f6a50a f6a532 f6b222 fb975b '; }
elseif ($presO1 == 65) { $presIPRs='138000/13bfff 27acc0/27acff 310200/3102ff 620000/627fff 62e000/62e01f f00000/fdffff'; $presIPs=' 12c05b 24f151 310e0a 311345 4e07b2 63e609 78ee05 d09770 d09771 d09773 d09774 d09775 '; }
elseif ($presO1 == 66) { $presIPRs='be600/be6ff 1c8b00/1c8bff 1d7300/1d73ff 1e0000/1fffff 24e000/24ffff 2de000/2dffff 4fa300/4fa3ff 5a4000/5a7fff 628000/62ffff 66ed00/66edff a60000/a7ffff c58000/c5ffff dc0000/dc1fff e86000/e87fff f91000/f910d3 f910d5/f911ff'; $presIPs=' 7cb18 b7ac2 27426b 295871 29a6d9 29bf5d 338f7c 3fa507 601020 608040 60dc45 60e535 60f845 622eef 6d190c 7ac657 8726e5 873fb7 92c11d 938828 96e0f5 9a1ecf bfa435 c43489 d7c323 dd5b37 e382c7 e473ae e51ace e6dd0f e6e6e6 eb7c84 f648af f6fc49 f910d4 f922aa fb4ce2 fe628e '; }
elseif ($presO1 == 67) { $presIPRs='13a763/13ad53 13ad55/13ffff 120000/13a761 f0000/fffff 640000/67ffff 800000/87ffff 980000/9bffff 9f0000/9f3fff cd4000/cd7fff d26000/d26fff'; $presIPs=' 171424 17cef2 2b0521 371c36 3741d5 3748a4 3748a5 3c2e32 540342 6c2e7c 70fe03 95c036 a53b1e a756d0 b99364 c03343 c770d6 ca001f ca02a4 ca0304 ca0b1a ca10a3 ca1416 ca17bd ca1b93 ca2352 ca292c ca2aa1 ca389e ca3cf6 ccfe14 cd25a2 cd3178 cd8a8a cf8880 d22752 d2da57 d450af d451f2 d7eeba da74a2 db33af db33c8 dc6588 dcc1d2 dcd0d5 de0a08 e18af2 e1b272 e3888a e420b1 e44812 e4bb82 e4d4ca ea6ea8 f24597 '; }
elseif ($presO1 == 68) { $presIPRs='90000/9ffff 2a0000/2a1fff 6c8000/6cbfff b28000/b2ffff'; $presIPs=' f5b8a 1b5d08 23d254 242ef8 2afbec 2c1267 2dfdfe 2e13a8 2fadad 311a7c 318a5a 31c269 42a16b 446822 446b40 51d6a8 53f1c6 65a499 6de996 6eaca6 93059c b8fbde c29ce0 c4b7a1 '; }

      } else {

if     ($presO1 == 69) { $presIPRs='900000/92a7bd 92a7bf/92ffff 926d0/926df 29a000/29bfff 2e0000/2e1fff 388000/38ffff 3ab200/3ab2ff 409b00/409bff 410000/413fff 488000/48ffff 54cf00/54cfff 591000/591fff 5a0000/5affff 5d0000/5dffff a32100/a321ff'; $presIPs=' 1ef8c 4955e 9d0b4 d45f5 e47a4 10c184 10e652 2731c7 2e8036 2f0916 30f177 403877 47deba 47debb 49aac6 4ceba3 686cb2 6a6bfd 72853f 76763a 76ba21 78adcf 7e7516 7f029d 7fcbe3 93ea7a a18501 a2479a a259e2 a25c55 a25f17 a26bfa a280f4 a3de71 a4c4b4 ae30cb af0a5a b6a35a bfd3c9 e8208c ed3b99 fe0f87 fe9b8e ff018d '; }
elseif ($presO1 == 70) { $presIPRs='a80102/a8e600 a8e602/bfffff a00000/a80100 246700/2467ff 260b00/260b1f 261f00/261fff 262600/2626ff 540000/57ffff fc8000/fc8fff'; $presIPs=' 231107 246493 2464e2 46f01b 663fc3 663fd9 6ea8ae 777f31 8a6cd6 '; }
elseif ($presO1 == 71) { $presIPRs='80000/85539 8553b/fffff 1212e2/12ffff'; $presIPs=' 388208 39b179 3fe648 4442f6 523fde 57626b 5e8361 5f026b 9adbfe a848c5 c0a050 c161b7 c3a1be c8edb1 cde330 cffc66 e7e760 e97f23 ea8526 ec0958 ed2238 fae81a '; }
elseif ($presO1 == 72) { $presIPRs='1d5c77/1d5c79 1d5c7b/1d5fff 1d5c73/1d5c75 1d435d/1d5c71 1d435b/1d435b 1d4359/1d4359 1d4357/1d4357 1d4355/1d4355 1d4353/1d4353 1d41f1/1d4351 1d4000/1d41ef 20000/23fff 38b00/38bff ea400/ea4ff 164000/165fff 200000/207811 207813/20ffff 2e8200/2e82ff 2fc000/2fffff 346000/34603f 360000/36ffff 3794c0/3794df 379980/37999f 379c00/379cff 5ef920/5ef927 a78300/a783ff c70000/c7ffff eca780/eca79f'; $presIPs=' 88f1a b572a b572c b574b 128f9e 129607 129632 1296fb 129706 17555f 25d568 25d9ee 25f414 2c2539 2c304d 2c3186 2c36b9 2c3825 2c395c 2c3f54 3327cf 332f8a 33e1ce 378ff9 3792c8 407676 9bd5b3 a7fb1b af369e c1396c c17934 cb9ea8 d380ac d70775 d82b7c f97f3e '; }
elseif ($presO1 == 74) { $presIPRs='320000/321fff 325500/3255ff 76c000/76c1ff d00800/d008ff d01000/d010ff dccf00/dccfff dcd700/dcd7ff dcdb00/dcdbff de0400/de04ff'; $presIPs=' 74416 c3815 32778e 343232 34601c 3472fa 34f592 35a0d2 35c142 361602 36c8b2 36d6ed 3706d2 370fc2 3715e2 375682 3e2fbb 51ac99 5639e6 56aaf2 56eeba 5e77ed 689863 c0b21e c14af2 c1d431 c3c15a c85919 c8f643 ce62c3 d00e34 d04d7b d06104 d093d5 d0c6b8 de024a de035a de03ba '; }
elseif ($presO1 == 75) { $presIPRs='65bf00/65bfff'; $presIPs=' 107b42 180f61 1c8983 1e976c 20be95 24b5c8 2896a0 418b17 4403fa 491b6b 5eeebb 65aa88 65b38d 65b48e 65bbc9 65bbd0 65c5a4 65cc70 65d7fd 65db83 65e178 65e7bd 65f668 65fcee 76f397 7711cd 7db082 7dba9a 7dde52 7de29a 7e6bf2 7f5f89 829431 8625e6 8784eb 8827b8 8aa936 8d7435 96a8e6 9731d2 ad2eac d3b95c e4e432 ef87c8 '; }
elseif ($presO1 == 76) { $presIPs=' c7c4c dc699 f0b63 10e076 115dda 18bef8 1ffb45 476bac 490132 493adc 6533b4 68ebbd 6f3444 7114f9 7298d7 7311ad a4c472 aeea77 d134d9 da69c1 e11f22 ee72b6 f9244e '; }
elseif ($presO1 == 77) { $presIPRs='297900/297aff 4ff600/4ff6ff 583200/5833ff 5e2000/5e20ff dd8200/dd82ff de2800/de28ff de8300/de837f'; $presIPs=' 15cfb6 170e77 1b1b41 1b624f 1e39f9 1e3bd4 1f2eab 1f65ba 250b97 280a43 28132e 282afd 2ec821 306194 3db5ec 3df63e 4344fa 4436a1 443824 463651 470552 494545 4f51f8 4f9a84 4faf2a 4fb33b 4ff501 547968 57201d 57220f 57cfba 5816f9 5816fb 581705 58191b 58191c 5842fb 5b33b9 5c5f0c 5c5f0f 5dc535 6234ee 64ba77 68d9e9 68d9f2 68fa59 6ac296 7af8b2 d060ab d113bb d13c66 d1422d d1c511 d3b87f d3c0a0 de0220 e4c113 e9ab90 eb29fb ed904d f3e189 f4d4e7 f6072b f76004 f8553c f97477 f9fbb6 fa0e3d fb0f26 fd84fa fe3ce1 fed034 '; }

      }
    }
  } else { // 78..132
    if ($presO1 < 96) { // 78..95 
      if ($presO1 < 86) {

if     ($presO1 == 78) { $presIPRs='1ab300/1ab3ff 1abb00/1abbff 2eff58/2eff5f 2f2280/2f22bf 2f3700/2f373f 6e3200/6e32ff'; $presIPs=' 26d39 39079 d0cf9 1aa3b8 1becab 1d0b09 1d0b37 1d59b8 1d6296 1f4110 21ed93 242793 24d56e 25dfbf 26cac3 28e0a8 29cc7d 2e0279 2e5007 2e5de6 2e8265 2e988d 2eb0b9 2ec7ad 2ef41e 2fa530 45e559 5351f4 54069e 54bf09 562b0f 57d899 607779 608cc8 65edc4 6a2672 6af690 6d1778 6e6007 6ef711 6ffc1e 818a77 81c9bd 81df19 81e8f4 839839 89a385 8a58f6 8a5922 8b80b6 8c8a27 8e8cc2 8eaf46 9f20da 9f668d 9f7086 ba083e ba966b bb5bd4 e37ea9 e6d803 '; }
elseif ($presO1 == 79) { $presIPRs='748f00/748fff ab7c00/ab7cff ae4800/ae48ff'; $presIPs=' 55637 1dda58 27b8b5 27ded9 27ea7a 3119f4 32b910 33b2dd 341434 35cee7 629cbd 63066a 65c082 69f20a 6e5809 6f9e63 6fa15b 6fbfdd 748862 787846 7d1ee1 8731c1 881e45 8832cd 89e988 8b793f 8c0cf9 8c27e3 8ca62d 8ca8fc 8d0712 9109c2 947bd1 9586e2 95cef0 99191a 9a139b 9f1b32 a28be5 a2aa31 a37a9d a47e7b aa2d7e ab7e3e ae4faa af7248 b0741e b1083b b173ee b17cf4 b321ac b64798 b711c9 b738b6 b7c7b6 bc68dd bcab76 '; }
elseif ($presO1 == 80) { $presIPRs='431000/431014 431016/431fff cf200/cf241 cf248/cf287 3acd00/3acdff 40af00/40afff 490600/4906ff 537100/5371ff 565400/5654ff 569000/5693ff 574000/575fff 79cc00/79ccff bef600/bef6ff ed8400/ed847f ed9d00/ed9d7f f67340/f6735f'; $presIPs=' e2782 300619 305d07 39bf0a 43d9f6 490409 4a6e95 4cb34c 4cbf3d 4f1d3f 4f3654 51b7b2 522017 52201a 52201b 522e94 5651d3 58f220 5bbf71 60db0c 614020 6158f6 63b987 68c244 6c3020 6c36b4 6de30a 785992 993df1 99d308 9c074a a7ee4e e21a74 e2c350 e47209 e4e28a e9c447 ed9835 eeb9f6 eff2ba f0caaa f0d32b f0d793 f1584e f39945 f6301e f6330b f633a9 f64b2a f8dd39 f97d0a fa00b5 fa3015 fd38f4 '; }
elseif ($presO1 == 81) { $presIPRs='11d000/11d003 11d005/11d07f 33b00/33bff f8000/fffff 5cc000/5cdfff a98200/a982ff a98700/a987ff a98d00/a98dff a99100/a991ff a9a800/a9a8ff a9af00/a9afff a9bc00/a9bcff b0e200/b0e2ff b11600/b117ff be1900/be50ff befc00/beffff c81400/c814ff c8f300/c8f3ff'; $presIPs=' 2c3ac 39668 67bfc 7ab0f 7ab34 9511e ee13d 14677e 1858a2 18d321 193a17 1ce845 1e3f64 5450f5 55e057 58e9ce 5a16d1 5ac006 5ac21d 5ecdad 6dc97f 89c9a5 a98a0d a99316 a995ee a99bf6 a9b894 a9b920 aade09 abea33 ad134a b121d5 c4989c c6f09c c81042 c8f7ed c8f804 c8f88d c8f8ac cd3fce d1b191 d66de6 d6b850 dd6722 dda932 dffe22 ecef9b ed6ee5 '; }
elseif ($presO1 == 82) { $presIPRs='50f800/50ffff 625600/6256ff 62c000/62ffff 64dc00/64dcff 660000/663fff 678000/678fff 72a000/72a0ff 83c0d0/83c0df 879400/8794ff 89c000/89ffff 95e700/95e7ff a5f000/a5ffff a6a300/a6a31f c04400/c044ff c3f6c0/c3f6df cf6600/cf66ff d02e00/d02eff eb9c00/eb9fff'; $presIPs=' 42725d 42aa6f 470134 5cfbfe 5eb4f0 60130a 72f23c 741fa6 74cad4 8023fb 8047a0 8b450a 8c5b29 91f025 95f608 96a592 9784b0 a1e710 a52d9e a57390 b164b1 b60f54 c161c7 c23ec8 c24c13 c2af4a c4020a c6cd23 c7cda8 cdfae6 ce81a0 cf31b2 cf5969 d00a0f d057e8 d40526 df7ac1 dfa389 dfbe30 e17fc4 e30c12 e4fc14 e638af ed0fc0 f069d1 f36806 f5a060 '; }
elseif ($presO1 == 83) { $presIPRs='40d010/40d01f a77000/a770ff a77400/a774ff aa6600/aa66ff aba200/aba2ff dc9000/dc90ff de1600/de17ff eddd00/edddff ee2c00/ee2cff'; $presIPs=' 1515a 17c1d c88a5 10df8e 404d02 4160a6 45d1c2 5228ce 573890 57422c 5b561d 5c442a 6165d2 6746e6 674882 857dca 8a885c 8aa404 8d0902 8fbc02 927082 95c736 a41b4a a902d5 a90421 a9097a a90b0c a90b7f aa6977 ab9af8 ac3129 afbfe0 ce67d1 da43c2 ddb8aa de60e2 e2f5ae e3e124 e9265e ea0e96 ea60c2 edde0c eee35d f00182 f0a3ea f2585c fbad44 '; }
elseif ($presO1 == 84) { $presIPRs='108000/108fff 10fc00/10fcff 13b000/13bfff 261000/261fff'; $presIPs=' ca966 13a108 13a808 1451c4 167a05 17318c 207004 249bfb 2641b4 26a04d 281756 37b85b 6f251a 71f443 cdf486 e09285 e0ad16 e0bd82 f45180 fc221f fef84c '; }
elseif ($presO1 == 85) { $presIPRs='d67f18/d68107 d68109/d68bff d665f8/d67f16 d65cc0/d665f6 d6519b/d65cbe d6514c/d65199 d64a47/d6514a d649a7/d64a45 d64263/d649a5 d64230/d64261 d6201d/d6422e d61000/d6201b 1fba00/1fba06 1fba08/1fbbff 199200/1994b7 1994b9/1995ff 198665/198b0a 198b0c/1990ff 198100/198655 198657/198663 af800/afa2a afa2c/affff accd4/acdd0 acdd2/ace76 ac803/accd2 ac711/ac801 ac000/ac329 ac32b/ac70f ace78/af6ff d8400/d84ff ed800/ed8ff 195600/1956ff 197c00/197cff 199800/1998ff 19ec00/19ecff 580e00/580ff8 581c00/581cff 728000/729fff 7c4298/7c429f 800000/807fff 8cf800/8cfaff 8d9b00/8d9bff 8e0100/8e03ff 9a9000/9acfff 9f2800/9f2bff b90000/b9ffff be02c0/be02df c1c000/c1dfff'; $presIPs=' c211e c4095 d6221 d8243 d8626 ec632 f50fb 1139b6 119107 11a745 11b0f0 11bcd2 11d3a4 11e991 14eb61 15601f 194710 194733 19786e 1978c6 4872b5 49d3b0 49ed42 521c04 580832 5b0496 5c931f 5cdefe 5d7672 5e21f2 5fe628 5fe632 733ab4 7cd93f 7d205a 7d6a3c 7ddfc6 8d9857 9e89c3 a04093 ad4174 ad7639 ae128a b74b91 ba6742 ba83f9 c0fda8 c10007 c1eb7c c27f0a c27f0b d68f65 d69524 d69e0a dde590 e50f9c e55da7 e90c64 e9c883 ec32a9 ede885 ee642b ee7499 ee7e6b f9a02c f9df16 '; }

      } else {

if     ($presO1 == 86) { $presIPRs='260000/26ffff 60e200/60e2ff'; $presIPs=' b88bb ebbe8 220b2f 22dc22 2bc246 39f57d 39f583 579097 58f77e 60e35a 60e458 60e558 60e559 646703 64d14d 65889e 6bfb52 6eb656 6f402d 6fdfb4 7aa42e 81d43c 9c00f7 b0efe0 b70e48 '; }
elseif ($presO1 == 87) { $presIPRs='764000/765684 765686/767fff 6a1200/6a12c3 6a12c5/6a12ff 62de00/62deff 65f000/65f0ff 6a8800/6a88ff 6ad400/6ad4ff cf8a00/cf91ff e60a00/e60aff e60d00/e60dff e80100/e801ff ecf200/ecf2ff'; $presIPs=' 185236 48d65d 65184c 67f55c 6a0640 6a0fce 6a168d 6a1985 6a1e06 6a2350 6a2fec 6a4174 6a4598 6a5e4a 6a662b 6a6d84 6a8bde 6a920b 6a96dc 6ab535 6ad864 6adf44 6af11f 6af158 6af323 6afb85 6afe8f 6fa921 74e504 75b9a5 75cd1d 77b7a2 77cb59 77ec2b 77efa1 77fa26 7e80a6 7e8a9b 8b1983 8b1eb7 8b56c0 8b595c c10cde c22d1c c2d45a ccbf82 cd20a9 cdd2b3 cddfb5 cde8e4 ce9276 d0a450 d2c479 e0ac47 e0e106 e60ba6 e62f08 e6372a e63820 e63a08 e63c95 e65b1e e823d5 eacde2 eba8e2 ec1dcf ec6917 ecc5b6 ecc749 ed78b4 ef1713 f12c28 f5791a f86e8d f8b13e f93e72 fae621 fafcf1 fafdf2 ff5e63 '; }
elseif ($presO1 == 88) { $presIPRs='f30000/fad07b fad07d/ffffff ef4000/f16b92 f16b94/f17fff c6da8a/c6e231 c6e233/c6ffff c63b36/c6da88 c62c5d/c63b34 c62932/c62c5b c62508/c62930 c6133c/c62506 c60fc5/c6133a c60e93/c60fc3 c60e66/c60e91 c60916/c60e64 c60548/c60914 c60000/c60546 548000/549fff bf3f00/bf3fff bf4200/bf42ff dc0000/dcffff e00000/ef0fff'; $presIPs=' 260f4 2edf9 3b6e62 4fa56a 500a01 50c662 50cd74 50d87c 50de47 50de73 51237c 51fbc8 52a98c 55e59c 57dacd 65b81f 7112bd 927e59 959f4a 95b6ca 95caa2 95da3a a103e5 a164b8 a172c3 a40ced a4513c a59fba a72a21 abb404 abeabd afa533 b89c80 bf0e41 bf1c14 bf2a66 bf2b07 bf3ee3 bf4d5b bf52b6 bf5e3c bf6532 bf674a bf6c4c bf6d36 c42bc2 c7f6cc ca7c79 d4c908 d4c95e d5982b dba68c '; }
elseif ($presO1 == 89) { $presIPRs='1c0000/1c7fff 1f5800/1f58ff 1f8c00/1f8c1f 294700/2947ff 6c4000/6c40ff 6c4200/6c42ff 6c7900/6c79ff 6f8000/6fbfff 7a1d00/7a1dff 95c000/95ffff b20000/b3ffff bdbf00/bdbfff bf4000/bf5fff cfd000/cfd0ff cfd800/cfd8ff f8a800/f8a8ff f8ac00/f8acff f9f200/f9f2ff f9f700/f9f7ff'; $presIPs=' 10afc2 1bfebe 1f8084 22628e 22c90f 24af0a 24ff12 25c758 287549 2df1ac 488a4d 6141c0 68460f 698021 6ad986 6b683c 6b683d 6b6863 6be282 6ef31c 730280 7ae034 7b096d 7b0972 7b0d29 7b2450 8608bb 8881e0 88b3aa 89764d 8c3e5a 8cb896 9141d7 917207 964fe5 977434 97743d 978d45 9b502a 9ccf9e a2ffd6 a36b86 a392c0 a6325a af60ee b4f5a3 b86c04 b958eb b9e4d1 bd853f bd9a76 bec99c c94368 c985ed cb42dd cb8682 cc2114 d02057 d0525c d44b72 d487e8 d72565 da3fca ddaf3c df2f17 dffe28 e44d2d e46091 e5474e e7bf43 eb0932 ebe996 ef4bfa ef9d34 f58996 f8ab13 f9f332 fa07a0 fd6927 fdc36f '; }
elseif ($presO1 == 90) { $presIPRs='998000/99ffff'; $presIPs=' d2e2 26580c 82a589 96287c 9d3037 b08ae9 b0eacd b15679 b32dba b40d48 b67e72 b74a51 ba15a0 bb09e0 bb87b7 '; }
elseif ($presO1 == 91) { $presIPRs='4cdc00/4cdcff 4cf600/4cf6ff 4e6a00/4e6aff 795400/7954ff 7c0000/7cffff 900800/9008ff c47c00/c47cff c82d00/c82dff c93300/c933ff cacf00/cacfff ce7100/ce71ff d01000/d010ff d37500/d375ff d4e200/d4e2ff d62c00/d62dff'; $presIPs=' 408280 40a2ea 40a672 40c5f3 42a8fc 42a915 43ea21 4d4dcd 4d7b93 4e8b0f 4e8d0f 4eb554 5a955b 6388b5 63e8ad 664d93 71b77a 71f5f6 78155d 7862c4 786602 791910 791fb8 7935b7 794ddc 7951cf 795c9f 798fac 79963c 799a63 799e46 79a737 79b0ce 7aaf6f 7d83f8 860af7 8e018b 8e3a64 8f51aa 8f6d28 965102 96dde0 97f7d0 98a037 98a743 bb74fd bea30d bef30a c0d01c c12d07 c2543d c36466 c37c0a c6e331 c7329c c80c4f c9400b c9400e c94033 cb56cc cba72f cdadec ce3d29 ceb7ce ceb7fc cece1c cf058b cf06aa cf09e3 d0f204 d1b720 d1c450 d29c0d d2b0fd d35242 d55b1c d631fd d650d8 d650df d661fa '; }
elseif ($presO1 == 92) { $presIPRs='338300/3383ff 338f00/338fff 3d9400/3d94ff 642300/6423ff 643d00/643dff 700000/71ffff f04495/f0449f f1a400/f1a4ff f1b600/f1b6ff'; $presIPs=' 361f5 c6726 183439 1d1b3f 2461c6 27ef97 2f75d8 3076e5 3387ca 33935a 3393c5 33a228 3436b1 378297 3c1e69 3db3aa 3e345a 3ea003 3ee008 406ddd 46ac82 4f1c46 50d1b6 52e568 52e7a1 52e8e6 549f7b 552132 64c2a5 659318 659766 72f91e 73b2a3 7c0877 7d411d 7d53b2 7ec322 e850fc ebf1fe f04189 f189e8 f19a26 f1a542 f309a6 f3b662 f9a42e fbff10 ffd30e '; }
elseif ($presO1 == 93) { $presIPRs='7ff120/7ff13f ae5d00/ae5dff b68800/b688ff b69a00/b69aff ba7600/ba76ff be8e00/be8eff'; $presIPs=' a8c59 b20f3 1548b3 21540c 229bf6 22ae15 22f09e 22f414 299e8f 2a14c6 2c5575 2d7279 2eca5a 2f1ac8 3e04cf 3efc2c 402456 492582 503ca0 516e03 545a65 568fe9 59f725 5f6447 611563 61a912 61fc5d 64acc5 6dfbde 7044e2 709069 709814 7c0128 7ffd0a 886a96 8aa629 8d249f 9451c4 988860 9892dc 9d03a2 9e901b 9e911b 9e921d 9e921e 9e93f3 9e941f 9e951f 9e9520 9e9718 a7f5b2 ae5851 ae5fa0 afcc1a b2c5c3 b686a7 b687d9 b68b43 b69f76 ba7fab bac869 be8d0a '; }
elseif ($presO1 == 94) { $presIPRs='4cd500/4cd5ff 64b500/64b5ff 663100/6631ff 663f00/663fff 8e8100/8e817f 8e8200/8e83ff 8e8600/8e86ff'; $presIPs=' 1344cc 13ab28 1433d6 15061a 170c0e 170d9c 171266 1712dc 171779 17201c 172f8b 17562e 1773cf 1791e4 1791e5 17d492 17d7b8 17dc36 17de06 17e3a1 17e728 17e83f 17e89f 17f236 17f46c 18d013 18d016 18d017 1993bb 1995c6 1995db 199a9d 199f33 19b1c6 1aa3c5 1b2552 1b8ae3 1d4a6a 1e8502 253903 28448e 2919b7 292368 292f36 2951c0 29a99a 29dcfd 29dfe0 2a3c01 2c1755 33b60c 390074 4259a5 4442c2 46006a 47ed65 4bcfa6 4bd681 4bd80c 4be584 4be70c 4be981 4c579a 4c6281 4daaaf 4ee0e0 4ee131 53174b 5ab15b 602e81 606590 608431 60ec54 620f22 62afdf 6363a9 64bddc 655216 6633c4 67526e 71418d 7c7805 7ff9fd 8810f2 882d37 8837d0 89c67a 9ad811 9e6f3f 9ebc23 aa6744 b20861 b21e08 b2344d b23eba b23ee9 b2d081 b30d84 b3183f b36c70 b36ca2 b381b5 b3858f b388f1 b39bed b3ab3b b3c92c b401b0 b410c6 b41d9e b4a231 b50ae4 b50fed b51013 b54a6d b5d5d3 b5e255 b5ed90 b5fd8c b76a02 b7c20a bd8cc1 bda26b bdcd17 bdf14b be3f82 c0f4d8 c115ce c7f13e d3095d d59c53 d5f3d5 e4532b e62320 e87a68 ec8670 f29fa8 f40202 f868b4 fa4498 fb75b6 fd576b '; }
elseif ($presO1 == 95) { $presIPRs='476400/4764ff a8b200/a8b2ff a8b700/a8b7ff a9be00/a9beff d70000/d701ff'; $presIPs=' 10c9a9 133322 18299d 1832f3 18a823 18c819 18c8cb 1912d9 193e61 195e1c 1980e8 19b324 19de6a 19e517 1a13ef 1a4a92 1a66ac 1a6fa6 1a74a0 1a9a2a 1af4e7 1af5ee 1b0116 1b7c29 1b88fb 1bb325 1bc717 1c6942 1d0886 1d8132 1dc609 1e5a0b 1e7a05 1f0bad 209664 20da19 217c41 231322 233ae4 237480 23ae76 25a2d6 2b4c47 2bfdf2 34367b 349aa3 34b0d2 34b4c2 352208 35db0f 369129 370a0d 3874d6 3ab135 3ab307 3ab48b 3ab507 3ab59c 3bc295 3bca3f 3e9308 406ea2 4385e6 439e34 439ec5 4440f8 44456f 444ba3 45c22d 474d2d 4ceb42 4e4749 4e6b8f 4e77e2 4e8197 51cbcf 5314b5 532f90 57f688 59a7ed 5b5ab3 5bdeff 60920e 6910fb 69a735 6b378c 6c8e8a 6c8e96 6c96eb 6ee0cc 6f2499 820baa 84025a 8404a4 840f05 8434db 8434dc 843a6c 847c56 848530 849fd5 84a17d 84d64d 85084f 8508f1 85170f 853f17 8543f8 854ce0 857516 8585c6 85a837 85c5cf 85cb75 85e684 85ed43 85ef2b 8608fa 860cbe 864156 8698b3 8724bf 8725cb 879038 879ffc 87dac4 87e86a 8bf6ae 8f1c40 9a6674 9af7bd a543ce a5b9ae a9baaa b50447 bc9694 bd8c2e bd9c3e bdaa46 d0c00a d31583 d31b1c d31e24 d355e0 d752e5 dc0a08 dc1b67 dc2c5d dc2f05 dc3d94 dc458e dda577 de0844 df52ca ec7511 ef37a1 efb4e4 '; }

      }
    } else { // 96..132    
      if ($presO1 < 122) {

if     ($presO1 == 96) { $presIPs=' 9aa54 9ac87 bb16b dfb91 18010a 18ace9 1e1f80 1f4bbf 1f56b8 1f5c95 1fa6e4 2fe3c6 38b323 3948da 399ab3 f1af36 '; }
elseif ($presO1 == 97) { $presIPs=' 4a5d20 4a5e9f 4a90c4 c448ae daf1c5 '; }
elseif ($presO1 == 98) { $presIPRs='820200/8202ff'; $presIPs=' 7e26c2 7fa756 8cf5a0 8ed2c8 ac1e8a bd094b bdd409 cbec3c ce8efe d186c6 e0c705 e81857 e845c3 e853b1 e99c38 f64b38 f7b727 fb1bd5 fc0ff5 fc9515 fc9bcc fee7ba '; }
elseif ($presO1 == 99) { $presIPRs='56800/568ff'; $presIPs=' 56b4f 1c5bb1 286ef1 38040e 38ef73 40911b ab5eb0 e58385 e97c1d '; }
elseif ($presO1 == 108) { $presIPs=' 64a7 '; }
elseif ($presO1 == 109) { $presIPRs='445840/44585f 7a0300/7a03ff'; $presIPs=' 286eda 28f4f5 4220cb 4acd7d 5636a0 5733c8 5ad832 68a018 68bb59 6e5d40 79092e 7923f2 7a0192 7a0197 7a0277 7a02ed 7a02f1 7a047b 7a0484 7a04f6 7a07d2 7a0841 7a085b 7a089c 7a0ae6 7a0c68 7a0c98 7a0dfd 7a0e7b 7a0f0a 7a0f2f 7a0ff6 7a1073 7a10ae 7a1160 7a12a3 7a139e 7a1482 7a15c2 a0c2e8 a0e5c2 a780c8 a9da08 a9db98 a9ec6a a9faa8 ab0c65 b80a59 b80b59 b80c9e b81ef4 b82ced b85041 b85529 b859aa b87890 b88021 b88435 b88f64 b8a15b b8a333 b8a46e b8b031 b8b228 b8d08e b8d18c b8d198 b8f7cd b8fc32 c04b83 c0a84b c0a901 c0b21a c41101 c41105 f50ae1 f5814e f5a6d8 '; }
elseif ($presO1 == 110) { $presIPRs='2d9000/2d91ff'; $presIPs=' 23c674 24005a 2cd1e9 2d88b1 2d93e1 32900e 43509f 5d86b2 89c9e4 8a2619 8ac679 8afea4 8b01bb 8b33fd 8b39fd 8b44ee a66856 ac123f ae80cb '; }
elseif ($presO1 == 111) { $presIPs=' 446984 768b4a '; }
elseif ($presO1 == 112) { $presIPs=' 5b914e 90e417 9feebd c64025 c6c19b c9b10e '; }
elseif ($presO1 == 113) { $presIPs=' 1681b2 1681e5 1e4205 23fed0 3b7304 442a87 448ab6 605acc 6a0632 8094df 80f8fa a2c961 a585ae a60d2c a685b4 a777aa a792b5 a7ea64 a80381 a86010 a9ee33 a9fc0d aa0430 aa9fd8 c71149 f0ff9e '; }
elseif ($presO1 == 114) { $presIPs=' 183e73 1879e2 187afb 20e99a 280db7 281166 28432c 2a3ab3 2b13b3 2b790e 2bc542 2e98e3 34e9a4 5044f0 505d47 6cb1dc 7938d7 88ca54 89817e ea9088 f612eb ff5507 '; }
elseif ($presO1 == 115) { $presIPs=' 170427 25f9d6 440290 44035e 4590d7 4895c5 48c0d9 499a94 59924f 5d924c 5f38a5 62d124 6c6032 6c704b 759945 75a1e5 75a4e3 75cd09 7c7119 7d575a 93c98e 93e8ca b8c03d ba424c ba6b23 f03ae3 f0ff3f '; }
elseif ($presO1 == 116) { $presIPs=' b9c8 ac62a fadcc 37e283 493d98 49c005 4b4f1b 61162b 7bdd71 7f1bbc e71eb1 ece60c fe4d72 '; }
elseif ($presO1 == 117) { $presIPs=' 8b0f 109d2 3000f 4febc be02a 19838a 4a6212 57a5d6 673b82 79d0fe c2c34a c32063 c36467 c3ab37 c69ce6 c6cae3 c8d71b f1c902 fee0f3 ff1aab '; }
elseif ($presO1 == 118) { $presIPRs='62a300/62a3ff 62d400/62d4ff'; $presIPs=' a8aa 4437e8 4483f9 44bf81 45e070 527271 5b01cd 6011b2 602792 602b36 6054ee 606b1d 60861b 60d28d 60df73 60ea09 62d63a 6b8b90 6ba65c 6fd89d 8515a1 88f63e 8d8d07 a90209 af1645 af28d9 af507c d905a5 d905a6 '; }
elseif ($presO1 == 119) { $presIPRs='3e8000/3e80ff'; $presIPs=' 6fd1f 1b3efe 335da2 462865 462866 52f843 5de5d3 74238d 838b47 83c8fa 872d29 8dd66d 912809 9371f2 988c8b a1815a a181e5 a1a1bd ab8754 adc947 b03c5b c3f7fd eb1204 '; }
elseif ($presO1 == 120) { $presIPRs='1c4000/1c40ff'; $presIPs=' 3230dd 3d0660 3ea79e 6a0982 7234cd 76fef8 8a76d5 8a76ea 8d7107 '; }
elseif ($presO1 == 121) { $presIPRs='10000/13a05 13a07/13fff 349a00/349aff 7c0000/7dffff 800000/9fffff'; $presIPs=' 7b97e 87c2a a75e1 aa332 ca7f3 cf9cf e814f 1604d3 162a16 1c2245 58048d 60bd48 60caae 65d636 6e50a8 788fe3 a17a36 a4c057 aca458 ad761f c9fff2 cc0002 f1291e f23bf2 f263eb f2b502 f5a696 f6355f f695a2 f6dc0e f7da32 fe4de6 '; }

      } else {

if     ($presO1 == 122) { $presIPRs='200000/2fffff'; $presIPs=' 3922b 5a4ae 5b057 102f67 34f253 3721a6 806415 8b733d 9a119e 9cb850 a06316 a66531 a70afe a8ca7e a8cd06 a8f93d a9746b a9c47b a9db8c aa0ee5 aa21f1 ab056e adb2ab adf2d3 ae7110 af50d2 b01f0c b1737e b1dc1f b294bf b2a5d7 b7fff6 c05786 c78c9e c952ad c96644 cb3587 cd5f0e cd5f1b d0af2d dc1e0e dd2196 e3932e fc016d fcf204 fcfbcc '; }
elseif ($presO1 == 123) { $presIPRs='7d4400/7d45ff 7d9c00/7d9cff'; $presIPs=' 5dfe2 e34b2 ef8a5 100e5a 1056b9 10996f 13be8a 1572cd 1819cb 1876e0 187de4 1a186c 1a387d 1a421d 1a552d 1a7781 1ad072 1afb6c 1bb14c 1c6ab1 1e4c08 1eb3b4 357dce 6c6c93 71ada1 7cd141 8a1272 8f6205 975a85 a15529 ba8aaa c926e8 c9599d c9ad66 c9c53a c9d8f8 ca9ef9 cb45ed cc3611 d6acae d87f41 ec3d8c ec78c5 ed0bad ed5f08 edc8fd edcc0e '; }
elseif ($presO1 == 124) { $presIPRs='300000/3fffff 680000/6abfff 6ac400/6bffff 730000/7301ff 730600/7306ff d9c600/d9c6ff d9e000/d9ffff'; $presIPs=' 5e3e 698d5 7980c 280772 2b3b80 2b9f03 52ddc2 7304be 7304c0 7304d4 79df80 7aaaf8 7b62c9 7b8c51 7c5dba 7cc0d3 8a7383 a04346 c31201 cf633d cfa82a ed563e f45364 f4fd44 f7d0cb f82030 '; }
elseif ($presO1 == 125) { $presIPRs='405e00/405eff 640000/67ffff 780000/78ffff 800000/9fffff b00000/bfffff e00000/e7ffff f00000/fbffff'; $presIPs=' 2720ca 282f70 29b53b 2e3183 3cf0c9 406015 464db5 4ce60a a01121 a011c8 a0d283 a14a04 a187ba a18e7c a1cba7 a2cd24 a354b0 a36fef a4750d a55ddb a6aa6c a7b31b d15902 d1783a d69988 ea051e '; }
elseif ($presO1 == 128) { $presIPRs='708b00/708bff'; $presIPs=' 3b14e3 3b14e4 870b95 bbdfd4 dce702 f170e7 f315e0 '; }
elseif ($presO1 == 129) { $presIPRs='150000/15ffff'; $presIPs=' 313ccd 5dcd8a bacd47 c20849 f7f7f0 '; }
elseif ($presO1 == 130) { $presIPs=' 245f66 3fb1c0 4b5753 '; }
elseif ($presO1 == 131) { $presIPs=' 6b213e af110a '; }
elseif ($presO1 == 132) { $presIPs=' bbe601 bbe602 efb5e5 '; }

      }
    }
  }
} else { // 133..255
  if ($presO1 < 169) { // 133..168
    if ($presO1 < 151) { // 133..150    
      if ($presO1 < 143) {

if     ($presO1 == 134) { $presIPs=' 25ff05 4c0a42 5be229 5be22b 630c66 99b811 '; }
elseif ($presO1 == 135) { $presIPs=' c4bbcb '; }
elseif ($presO1 == 139) { $presIPs=' 1e2d27 82c022 '; }
elseif ($presO1 == 140) { $presIPRs='750000/8affff'; $presIPs=' 6d11b4 711231 7158a0 71d690 71fc2d 742184 '; }
elseif ($presO1 == 141) { $presIPs=' 182dfb 33709a 4c2d22 4c2d23 55061f 59fbf5 d70cf2 '; }
elseif ($presO1 == 142) { $presIPRs='a50000/a5ffff'; $presIPs=' 20d0e7 20d0e8 20d0e9 20d0ea a6037a d97d68 '; }

      } else {

if     ($presO1 == 143) { $presIPs=' e1e5ee '; }
elseif ($presO1 == 144) { $presIPs=' 8c2e0a 8c2e0d '; }
elseif ($presO1 == 145) { $presIPs=' 24ea74 6464be fd4f4d fd668a '; }
elseif ($presO1 == 146) { $presIPs=' 67fe0b a403c5 '; }
elseif ($presO1 == 147) { $presIPRs='2e0000/2effff 6e0000/6effff'; $presIPs=' 1d7206 531ea7 5b012b 9c6294 '; }
elseif ($presO1 == 148) { $presIPRs='e90000/e9ffff'; $presIPs=' 538432 a87f0a f5bf03 '; }
elseif ($presO1 == 149) { $presIPs=' feb68f '; }
elseif ($presO1 == 150) { $presIPRs='465400/4654ff'; $presIPs=' fe1e4b fea103 fea3f1 '; }

      }
    } else { // 151..168    
      if ($presO1 < 160) {

if     ($presO1 == 151) { $presIPs=' 84f13 152c68 643b0b '; }
elseif ($presO1 == 153) { $presIPs=' 133571 '; }
elseif ($presO1 == 155) { $presIPs=' 21dff4 61c1b7 e60f72 '; }
elseif ($presO1 == 156) { $presIPs=' 117d7b 38fae2 '; }
elseif ($presO1 == 157) { $presIPRs='160000/16ffff'; $presIPs=' 5285e3 8acca6 '; }
elseif ($presO1 == 158) { $presIPs=' 4005f1 403d01 4048e6 406001 4074fe 8206fd 8206fe c2e579 fa21e3 '; }
elseif ($presO1 == 159) { $presIPs=' 944dc6 d657f5 '; }

      } else {

if     ($presO1 == 160) { $presIPs=' 2439ad '; }
elseif ($presO1 == 161) { $presIPs=' 4c89cd 9f0421 '; }
elseif ($presO1 == 162) { $presIPs=' 3e81fd 476408 535f10 '; }
elseif ($presO1 == 163) { $presIPRs='d0000/20ffff b40000/b4ffff'; }
elseif ($presO1 == 164) { $presIPRs='3a0000/3affff'; $presIPs=' 4daa42 4ef839 642aa2 '; }
elseif ($presO1 == 165) { $presIPs=' 6285ea '; }
elseif ($presO1 == 167) { $presIPs=' 13f87e '; }
elseif ($presO1 == 168) { $presIPRs='bb0000/bbffff'; $presIPs=' aa83d acfbfd d75b4a '; }

      }
    }
  } else { // 169..255
    if ($presO1 < 204) { // 169..203    
      if ($presO1 < 195) {

if     ($presO1 == 169) { $presIPRs='e53200/e532ff'; }
elseif ($presO1 == 173) { $presIPRs='2d6d10/2d6d17'; $presIPs=' 36c48 9c4ee d6029 ef91a 123d78 1273df 1747ce 19e832 1ef8ab 2d5932 2d6baa 2d72b2 2de6f9 2ded7a 3261b5 328c64 33d106 3ec9c1 42f878 5018b1 502802 506521 a0451c a0711c d406aa d4ca82 d4cc82 d4e8c2 d4f662 '; }
elseif ($presO1 == 174) { $presIPRs='7b84f8/7b84ff 7f8400/7f84ff 8e4b00/8e4bff 8e6800/8e68ff'; $presIPs=' 229d62 2440e2 24c4de 24f19d 25cd43 2f7181 3279d6 33b193 3600f4 36096e 375397 3789ae 393754 39b9f2 70180b 745e70 780e2a 790085 7b9be2 7bae22 8103a0 810b04 811ae2 811c12 812ceb 81315d 813527 81404f 81414f 814842 81558e 816033 816579 816ba1 817965 817e26 81882f 81885e 818c29 81925e 819767 8197d5 8198d0 81a1ce 81b973 81be38 81bf09 81fa46 81fd24 84ef4a 85071a 853d42 8553e2 85b142 85f94d 85f95f 85fca2 8e4437 8f956b 8f9a66 8fae44 8fec2b 8ff09d 8ff3cc 8ff657 8ff75b '; }
elseif ($presO1 == 178) { $presIPs=' 31fc47 31fc91 31fd4e 5c59b0 5d12ab 5d248d 5d32c6 5d81cb 5d9ad5 5dab3b 5dbbff 5dc19f 5dc763 5ddf2b 5e0356 5f07c7 5f1306 5f34f9 5f6b50 5f85a1 5fe58e 7b01a8 7b021b 7b08bd 7b0ac4 7b1067 7b10bc 7b1133 7b1193 7ba8ce b030d0 b03feb b092d6 b09fd8 b0a40e b0af16 b0afe0 b0b43f b0c6c4 '; }
elseif ($presO1 == 180) { $presIPs=' d7d564 '; }
elseif ($presO1 == 183) { $presIPs=' 26e33 572769 573863 57f6fb '; }
elseif ($presO1 == 184) { $presIPs=' 4904e9 '; }
elseif ($presO1 == 186) { $presIPs=' 919f3 9f03a a33c9 a4164 125208 1d67a7 3a82c1 50719d 50da05 51589a 5169da 5170b3 5241fc 54d003 627940 69f4a9 7ae230 89afb5 8e0f60 '; }
elseif ($presO1 == 187) { $presIPs=' 36410 4cd5a 4cd9a 52a56 5381f 550f1 55c1d 64142 9a33d 9dbae a797d b26fa be4db f194b 101304 103a03 114022 1474d7 15310a 159626 16386b 170b8a 188ac4 18d5c1 19896b 1a47ed 223eb2 23c395 23e262 23e4c8 242c40 2432b8 281c28 287c17 28ba0f 28f912 29808f 2de032 2de13c 321682 344a80 3828ed 39f033 3a3a1f 3aaf83 41125f 41155f 43d79b 446ddc 4d2f4c 4e2155 593dd2 594f81 59b319 5b24fe 651384 '; }
elseif ($presO1 == 188) { $presIPRs='284500/2845ff 48e100/48e1ff 5c4900/5c4d7f a34400/a344ff'; $presIPs=' 27207 2fa07 108652 11bf7a 11c1aa 123499 12a887 280edd 282755 282f8f 282fbb 282fbc 282fbd 2832cc 283d95 2843c6 2843ea 2843eb 2843ec 284c17 284cc2 284fc9 2852c6 286487 286e0f 286e4b 288409 28a373 3897b6 38fbd7 3a401d 48d52c 48e40d 48e649 500133 5c088a 5c48a1 5c48a2 5c48a8 7097c6 73866a 73876c 738ab6 73aa88 7bed24 86094b 8624f7 862820 8a00e7 8a20e6 9e90dd a26763 a347fc a5202d a52519 b03246 ba4adf bab49a badda6 bb011f bb112c bb2b43 bba6f4 c13d9e c1d5ec c1dbc5 c1ef44 d8720f e61b81 '; }
elseif ($presO1 == 189) { $presIPRs='6d3178/6d317f'; $presIPs=' 417b7 57a78 61ca7 652b4 6945d 6da39 80718 a45e8 bcd68 bda59 ca065 e604a 128fc2 12a28d 12f70b 132ab8 1350cf 137745 13c59e 13e405 13fbeb 1443ca 14c85a 183284 1aff93 1d6b93 1dd532 1eaca6 1f39ec 1fb6ea 1fca26 1fe42f 1fef2d 2005f6 201bb2 2149e0 231b65 232943 248fdd 265a1e 2ee73f 3005f0 3321db 332d1d 33ee61 3589e5 358e49 3724de 37a42e 38642a 3d0f03 3e32a6 3f428c 3f4318 3f5456 452736 459392 462589 46692f 468dc1 476020 47aeda 481197 483901 492c0a 4a9f03 4ba806 4d1c4a 4e1d88 4ea123 4f2cdf 4f39a4 4f6091 500103 5015e6 594758 5abb84 5afa8a 5b001e 5b83e6 5dcfc5 5ddff1 5defa7 6006d2 6036f4 617eaa 62654a 659042 683146 686040 69851e 6c1a84 6c4e92 6c8dc3 6f2774 6f826e 6f8491 70db22 72e223 734012 73b6cd 78d1c6 79f067 7b1952 7c817e cd59ca '; }
elseif ($presO1 == 190) { $presIPRs='74000/74fff 310000/31ffff'; $presIPs=' 20e81 239b9 a1bfb 150944 160dcd 166780 1669d6 18f745 1bc25a 1be6c9 1c9f78 1cee9c 2433f2 246ea3 29d8e6 2a659d 2c5e8f 325a30 32d835 3330d1 335a99 33f113 37eec8 4711d0 51df03 548794 63b207 63d8c2 6c992c 791e32 798a3a 7d772a 7e4139 80e1b6 8702c7 879836 879d37 907fca 90f949 915ffa 92a08e 938606 982ba2 985872 98dc0d a23ab3 a3efc7 a630f3 ac01c4 acfff3 ad494d ae185a aeea2d aef055 b024db b09537 b0b57c b0f2df b14013 b250fd b6299e bc002f bc134d bcb9f0 bcc90e bd23a5 c52732 c65f27 ca6ed2 cc82c2 ce78d7 d01c39 d53133 e2e402 f410d4 f462c4 f8fe51 '; }
elseif ($presO1 == 192) { $presIPRs='fbe2c8/fbe2cf'; $presIPs=' 3a34fc 5b3c0a 5b3c0b 72470d a604fe a719fa bc30fe c0a977 '; }
elseif ($presO1 == 193) { $presIPRs='11d000/11d0ff 13a400/13a7ff 19c500/19c5ff 211400/2114ff 259800/2598ff 8a9d00/8a9dff a48300/a485ff aa4400/aa44ff c24000/c25fff c89600/c896ff ee3c00/ee3fff'; $presIPs=' 1b952 61405 167847 167848 1bf362 1cb80f 1cf915 211c49 213d91 242502 24ba17 259c92 2802e5 280571 355315 4a4028 4dd332 57ad6a 59f869 6518f5 679458 6e55f8 6f6fdd 749dc3 882807 a4caa6 a593ae a5e422 a90407 a9d006 aa2adc aa2c84 aa8e6c aaee34 aaee92 ab83e9 ab83eb ab9bae ab9be6 abfa12 abfb96 ad9406 ae7a43 afaf84 bcc506 c282ab c29f71 c9d998 db690a e20607 e71125 f7fa25 fc3090 feb7f3 '; }
elseif ($presO1 == 194) { $presIPRs='84a00/84bff 21bd00/21bdff 32b000/32b0ff 3feb00/3febff 6d1600/6d16ff 9a4200/9a42ff a58000/a5ffff ba3500/ba35ff'; $presIPs=' 19602 31206 328be 6e75a 90b16 199204 1db20e 241613 2c1253 2c1539 2c15a2 2c610e 2c8e08 2cab53 2cc162 2ce422 2dbafc 48ee3d 4f3c37 5210fc 555ba4 5f3b82 6ea04e 6ea556 709f02 7ed0be 876919 95f718 96dce8 96dce9 96e066 96e0d4 997108 9ad349 a513c7 a9ea0d a9ea4d b78d3a bb2001 bb8404 bb94f9 cef555 d10892 d177a2 d45e17 dc52f1 e64b82 f66604 f6776a fe8973 '; }

      } else {

if     ($presO1 == 195) { $presIPRs='2f000/2f1ff 5a800/5a8ff 22b300/22b4ff 27d200/27d2ff 2ff700/2ff7ff 428000/42ffff 463600/4636ff 582000/5821ff 807800/807fff 9be821/9be83f be0d00/be0dff bf3600/bf36ff e1b000/e1b3ff e5ec00/e5ecff e5f100/e5f2ff e66300/e663ff fc4000/fc40ff'; $presIPs=' 2d8ba 2fd56 3c092 ad0be 17738b 17fd4d 1c00e8 1d23de 1e49d6 254596 2545be 2a6619 2eef0a 347022 37822c 382ce1 3af866 3cd002 45568c 456c08 456de2 463797 475a35 4c2583 4e4ddb 576607 58e202 61117d 62ad02 717fe2 71d6ce 74e9d2 7f9675 7fada9 7fadaa 801213 894058 947c43 9e8c5d a0fd04 a86d3c a8c8bc b0925c b30eec b681c3 b807fb b87da2 b8d932 bc1c82 bd8fdc bea623 c36dce ce600d cf0f4f d008eb d23953 d8c568 d8c569 db79af dd15ab e19108 e19cac e2ae04 e324c5 e42d2f e53e9d e5eb25 e5eb26 e5eb2d e5ed26 e5ed2b e68282 ea3d21 ea4064 eeb096 f25c07 f3bd24 f3eeeb fc49bd '; }
elseif ($presO1 == 196) { $presIPRs='70000/7ffff 19ff00/19ffff 280a00/280aff cfc600/cfc6ff'; $presIPs=' ce480 cf395 225c06 239eb2 239eb3 239eb4 239eb5 239eb6 239eb7 239eb8 249986 280822 283843 2e47fb c99705 c9e472 ca8f6b cd797a cd7bbe cda96b cdc7ef d309d2 d8433e db7d42 '; }
elseif ($presO1 == 198) { $presIPRs='acd655/acd679 acd67b/adffff aa0000/acd653 36ca00/36caff'; $presIPs=' 41726a 52b819 91740d 984602 afa6ca '; }
elseif ($presO1 == 199) { $presIPRs='c50200/c502ff fd6000/fd7fff'; $presIPs=' 503d36 503d3c 785adf '; }
elseif ($presO1 == 200) { $presIPRs='0/ffff 19daf8/19daff 292b80/292bff 2e0300/2e03ff 3aa000/3aafff 440000/440fff 48c001/48dffe 508000/509fff 510000/511fff 550000/550fff 580000/583fff 587800/587fff 58c000/58ffff 5d0000/5d3fff 780000/787fff 7f0000/7f7fff 8f8200/8f82ff 900000/901fff 960000/960fff c70001/c77ffe ce8001/cefffe eac800/eac8ff'; $presIPs=' 56613 e6039 120a07 15e882 18e3aa 19c561 1c57d2 1f2a03 2322e2 239669 2ad15a 2b8dac 2f0fd5 327b08 33cbc8 379914 37ef1f 3b109c 3bce22 45646a 479552 4b51be 558f27 59338d 5d939a 5da1dc 602724 60eea3 644ca3 64501e 654601 665f02 69b66d 6a8b8a 7aa019 7d5314 81190e 8c49cd 8dcaa2 8e7c4a 94d5bc 96a31e 97cfcc a108b0 a343c3 a88e1a a9484c ae2c25 ae9e8a b1e404 baf240 c35f26 c9b482 cc3e31 cc9a1d cddaba cf08ee d7043b d70f22 d94c25 d9c195 d9f9da db68bd de40aa e2861d ee5331 f1f405 f3c284 f5fd6a fd7312 '; }
elseif ($presO1 == 201) { $presIPRs='0/ffff 60000/6ffff 110000/11ffff 250000/253fff 4e0001/4ffffe 530000/53ffff 566ad0/566ad7 740000/74ffff 824f00/824fff 930000/937fff f20000/f2ffff'; $presIPs=' 33b5d bc00c 12349a 136696 137238 165f89 175812 1849d2 1a49b5 1c6e63 1e4872 24dcb2 287f0a 2b00f5 2d7b84 2d8eb2 3620de 3fdd86 427d25 438b54 467cd4 479048 4a0e31 4accad 4affd7 5099d7 51132d 581579 5815ad 58651d 586d73 59c5bb 5c414c 5da2dc 5da51c 5fb533 5fe3a2 7a4c04 7a6401 864212 901ee3 90f7c5 a772b6 ab6a1e ace8e1 c4ef52 c8dd72 c9ae7e cdd08a d09073 d1c572 d43264 da0c36 da0ca2 daf66b e18a89 e1e244 e84bb0 eb84e1 ec375a ee7fd2 f38441 f47be2 fa9ce3 '; }
elseif ($presO1 == 202) { $presIPRs='1c0000/1dffff 3f6500/3f65ff 58e000/58ffff 5d2400/5d25ff 6c3200/6c32ff 954000/955fff 9c0000/9c5fff b80000/b9ffff c54000/c540ff d40000/d7ffff'; $presIPs=' 159e0b 299bb3 2b7c15 2c0ec2 2c35ed 2c4191 33b4db 390a26 394554 43ec4a 44b23e 4624f2 474e82 48f7f4 490d7a 4b23e4 527711 6315a2 631d1b 664826 67cfe0 73036b 74a059 7d8d43 80b433 831e96 8362a5 83e234 853d0e 8eaaf2 8f81c8 8f8cfa 8f943d 8f9f02 907d52 941a42 a4cbfc ab2a07 c93947 e01fb8 eff24b '; }
elseif ($presO1 == 203) { $presIPRs='6cb00/6cbff 280000/2fffff 4cb000/4cbfff 511000/511fff 515100/5151ff 54a000/54bfff 57b200/57b2ff 588000/589fff 83f800/83ffff 918000/91bfff a20000/a2ffff c28000/c2ffff e20000/e8ffff f00000/f3ffff f90000/fbffff fc1000/ffffff'; $presIPs=' a6ef0 c02a0 189432 3f1ac8 491de7 510a78 5249c6 52506a 525e25 6000a7 6f86c4 73d104 7c95a8 8397cc 83a013 8e29b2 922b10 930444 996c7b acb65a b14a8a b14a8d bbcdf1 c5a8ab c5c40a c67e2b c6ade4 c73213 c8b44a d2c733 ed359e f73071 '; }

      }
    } else { // 204..255
      if ($presO1 < 213) {

if     ($presO1 == 204) { $presIPRs='7cb500/7cb6ff'; $presIPs=' 89c8e a8930 c0bca f5032 10e762 2d083a 7cb442 98cda0 a06e7a bcde89 d55dc5 ec97d9 ec9e97 ecad42 ecad81 ecc556 ecd377 ecd483 ecd48a ecd4e7 ecf18d '; }
elseif ($presO1 == 205) { $presIPRs='d18e00/d18eff'; $presIPs=' c84e88 cb86c5 d1aa22 d3ecef eaebb6 '; }
elseif ($presO1 == 206) { $presIPRs='33e000/33efff a96e40/a96e5f'; $presIPs=' 31898f 37b432 405c10 531d35 68e18f e15d3a f8efba '; }
elseif ($presO1 == 207) { $presIPRs='d24000/d26fb7 d26fb9/d27fff 76c00/76cff 2c8000/2cffff 437500/4375ff 438000/43ffff 630000/637fff 96a000/96bfff e20000/e2ffff'; $presIPs=' 395aa1 3df164 53c8cf 58d208 72cdd1 869bee b2b469 bd792c c04b51 c25769 cca006 dac800 e43ed6 f4b3d4 f8e4a6 ffe881 '; }
elseif ($presO1 == 208) { $presIPRs='32c000/33ffff 358000/35bfff 50c000/50c7ff 59d000/59d0ff 600000/603fff 621d00/621dff 650000/653fff 6db500/6db5ff 6df800/6df8ff'; $presIPs=' 2b7005 2b7eca 2ca5c2 326598 3b2541 3b7ca3 43fdaa 464856 4790ba 4b5822 4bd49c 4d6062 4d9752 4ddd53 55f2d4 57f078 587c53 5b7114 5d0080 5d95b0 6094e3 60d595 616782 736ff3 736ff8 738afe 788e17 7a048e 7c1cb5 b41a34 bb5b32 '; }
elseif ($presO1 == 209) { $presIPRs='33a000/33bfff 3e0000/3e7fff 440000/443fff 550000/557fff 7efe00/7efeff ac2100/ac21ff c8e000/c8ffff'; $presIPs=' 139845 1d175c 22aded 2c72b2 3bd179 3df406 4d8939 5c9c35 6996ed 6bd906 6bd9a4 806074 97a41c 9fc4af a04168 a1348b a9b04d be1163 caa84c cd5b23 d49153 d5dce3 d8cb6d ef7269 fe0f82 fe0f9a '; }
elseif ($presO1 == 210) { $presIPRs='130000/13ffff 164000/16bfff 480000/4effff 5a0000/5bffff 680000/6adfff 6d0000/77ffff 790000/7bffff 7d0000/7fffff b20000/b3ffff b42000/b4dfff c10000/c13fff cc0000/cdffff d38000/d3ffff d45f00/d45fff d90000/dadfff dc8000/dfffff f50000/f57fff'; $presIPs=' 8b17 54782 62dda 6b081 d4d42 d7341 20c85f 33e1ac 360282 403b83 53e12d 6292a6 9e06c9 a6d263 abd8d3 d44ece d49423 d496ea d4d6b8 '; }
elseif ($presO1 == 211) { $presIPRs='ac0000/c00f37 c00f39/c7ffff 140000/14ffff 250000/27ffff 2b0000/2dbfff 3c0000/3cffff 3f4000/3fffff 4a8000/4affff 5a0000/61ffff 680000/77ffff 880000/8fffff 906000/907fff a10000/a2ffff ca0200/ca02ff ce0000/e1ffff e80000/ffffff'; $presIPs=' 92f61 e8e82 2fed11 4c5478 4e5939 4f3d08 638d0a 641cf0 9044b1 93f88e 9be50d '; }
elseif ($presO1 == 212) { $presIPRs='70e500/70e59a 70e59c/70e5ff ca000/ca1ff 182000/183fff 22af40/22af5f 22b940/22b95f 48b700/48b7ff 5f3600/5f36ff 5f3f00/5f3fff 618400/6184ff 64fac0/64faff 660000/6600ff 70e300/70e3ff 73e100/73e1ff 74db00/74dbff 75ae00/75aeff 75b700/75b7ff 7a4000/7a5fff 8a4000/8a46ff 8a7100/8a71ff 9ea000/9eafff a28000/a29fff af7000/af70ff b21c00/b21cff cb5f00/cb5fff e36d00/e36dff eb6a00/eb6bff'; $presIPs=' 1f811 660c3 8c4ae 9ab8d c7222 10c9c6 10f840 11535c 120003 120304 18813a 1944b2 271c3a 2aec8c 2b326b 2b382a 2c9115 2d34e5 3072d9 33dddd 34a73d 38808f 3c41ae 3dfc58 3e6114 45f282 4921ed 4ae92b 4b16af 4b643a 4c258c 4c258e 4c25e2 4dd70d 4e4672 4fb3c4 5020e6 584d44 58839e 5f3ad0 643c85 64fe69 694ec4 6f1593 6fc71e 70e091 70f018 71f3c6 74dc64 750aab 75670b 75742c 75a2c0 75ac50 75af25 75b89b 75bb64 7c6eed 8a2f0d 8a2f0f 8a2f10 8a5615 8e8f74 90e8c2 969399 a6fdde af0582 af37c8 b211f8 b906b5 d1c602 de9382 e31cb5 e33507 e336ae e34c53 e3674a e37296 e37703 e388cd e38d03 e9ccb2 e9d3ac e9dd2e f75642 '; }
    
      } else {

if     ($presO1 == 213) { $presIPRs='efc5d9/efd62f efd631/efd7cf 856712/856d46 856d48/857151 856000/856710 600000/60b2c5 60b2c7/60ffff 48100/481ff 2a0000/2affff 5bd800/5bd9ff 857155/857fff 8c3800/8c3bff 8d9700/8d97ff a15500/a155ff aca000/acbfff ae3000/ae307f ae30c0/ae30ff b0e000/b0ffff b3f500/b3f5ff b97400/b974ff ba2600/ba26ff c654c0/c654df efc000/efc23a efc23c/efc5d7 efd7d1/efdbcb efdbda/efffff fbbdc9/fbbdcb'; $presIPs=' 1dc04 54626 54642 5470c 85c91 97948 152252 152f33 159422 1a468c 21f9a6 27eea3 29d4a9 2e56b2 2e8a4c 3783cc 3d81f4 3de39a 47009c 495a3d 4b2709 4f5dbc 508026 5182d8 52ce9e 533f39 556b1a 5e4e41 6ac5e3 706cf6 7aaee3 84e765 86a0e5 874025 901533 949bba 950f74 95bd60 96e426 977766 9b0d6b 9b19c2 9cc005 a242a1 a541b2 a55910 a55b16 a55b84 ac22c6 ace496 ae3cea afd2dc b0a1c9 b24005 b2b613 b3ff52 b493a7 b7c353 ba3ad6 ba3d1a bd1520 be4627 bf0080 c03a2e c03c13 c27802 c5b7fe d09206 d2c29d dce9e6 e1d289 e3d667 e3f393 e54e04 e78d22 ebd91a ecd016 f0ec65 fba2d1 fddeb7 fe5b7a '; }
elseif ($presO1 == 216) { $presIPRs='2d3ab8/2d3abf 3c0000/3fffff 4b0000/4b3fff 680f00/680fff 767500/7675ff a3bc00/a3bcff da8000/dafffe f08000/f09fff'; $presIPs=' 7ad14 cdbd9 e7aee 188377 188e2d 188e2e 188e2f 18aef5 26d8dc 40a9f0 569936 682f56 6ac618 75808f 77670c 91052a 910b5e 910e8e 9111be 97450c 9b84d9 a77165 cb2dc7 e07c7c eb8194 f44174 f5c0aa f5ca22 f5cc75 '; }
elseif ($presO1 == 217) { $presIPRs='83d00/83dff a2600/a26ff c1000/c10ff 1573a0/1573bf 178000/179fff 1eb400/1eb4ff 44bf00/44bfff 4fbe00/4fbeff 72d7c0/72d7ff 818000/818fff 931d00/931dff 96f560/96f57f acb100/acb1ff c8b000/c8d1ff dbc000/dbefff'; $presIPs=' 59582 7c66a af602 d50a1 14764b 170681 170907 18f044 191acc 1c6716 36975e 41029e 421644 421651 4344f4 44a4d3 455407 4681a2 48f23c 48f23d 4b5209 4d36a2 4da537 4ddf14 4e89f3 4fb6f5 569443 56aca0 5b0e31 5b46ee 6e4c5a 7203dc 72d314 72dc22 738e50 742f3a 74c314 74c318 76403c 777e92 92f60d 9454b3 95be01 963a3d 9693c2 9983d9 a01230 a01437 a08753 a0f1eb a506fe a648fe a7d472 aa900c ac1d59 ad11cd aef17a aef2ac bc2021 c4a423 c4a626 c707a2 c71a4a c8af89 d982a5 db36a2 db5813 '; }
elseif ($presO1 == 218) { $presIPRs='e1039e/e103e1 e103e3/e7ffff d80000/e1039c 180000/19ffff 240000/2fffff 37e380/37e3bf 388000/38ffff 438000/45ffff 660000/67ffff 6f0000/8fffff a00000/b7ffff ba0800/ba08ff ba0c00/ba0cff bc0000/bdffff ca0000/ca3fff ea0000/efffff f80000/f8ffff'; $presIPs=' 45dcd 49025 60f24 61303 7bfb6 8fbbb ee3c5 107b9b 1a5b4a 1c6792 1cbce6 3815e2 384002 3a28a0 4d8161 5c08a5 5dc935 5df875 5df87b 6c12f5 6c2caa c915b3 c915b4 c915b5 d2c7fe f1ee8d '; }
elseif ($presO1 == 219) { $presIPRs='0/3fffff 500000/51ffff 5f0000/5f7fff 800000/8cffff 8e3500/8e357f 978000/99ffff 9f4000/9fffff f02400/f024ff f80000/ffffff'; $presIPs=' 534a90 553e5c 5a5b45 5bfb0b 5db2a2 5e2cca 5ec198 76a14d 8e0c1e 8efdae 96a846 96e365 9dc813 b32e18 bf405f ca08c9 da370b '; }
elseif ($presO1 == 220) { $presIPRs='0/3fffff 480000/57ffff 600000/63ffff 740000/77ffff 9e0000/9e7fff a30000/a5ffff b53500/b535ff c00000/cfffff ff0700/ff07ff'; $presIPs=' 807abb 8753b5 a2f10b ad6b13 afd693 b22f52 b33d3e b55ee0 b55ee6 bdd0bc bde302 e19993 e4f675 e5cf62 e8ed7a eabcde fe0179 '; }
elseif ($presO1 == 221) { $presIPRs='0/37fff a0000/affff 100000/6fffff 820d00/820dff 8a0000/92cd7f 92cdc0/a8ffff c00000/c3ffff c80000/cbffff ce0000/ceffff d00000/d4ffff d61bf8/d61bff d80000/e7ffff f00000/ffffff'; $presIPs=' 4dc8c 68742 72a413 7a77c3 82a2e7 d66663 d701e0 d7d1e2 ee11f5 ef5bf3 '; }
elseif ($presO1 == 222) { $presIPRs='200000/3fffff 40ac00/49ffff 4c0000/4fffff 600000/7affff 7c0000/7cffff 7fc000/7fffff 880000/8fffff a6a000/a6a0ff b80000/bfffff e70100/e701ff e72a00/e732ff ea0200/ea02ff ec0000/ec3fff ecc000/ecffff ed4e00/ed4eff ee4000/ee7fff efe300/efe3ff f00000/f7ffff fc0000/ffffff'; $presIPs=' 4ac802 587926 5ba39e 82ce66 8645b4 9268f6 9a80f7 a585c6 aa6aca b23a75 '; }

      }
    }
  }
}
// END silt

// BEGIN ua
$presUAs = '8484 boston project|::fetch|acoon-robot|adshadow|alligator|arachmo|arste.info|askpeter.info|asterias|atspider|autoemailspider|backdoorbot|backstreet browser|backweb|batchftp|bejibot|betabot|bitacle bot|biz360|blackspider|blackwidow|blowfish|botalot|buddy|bulk|cherrypicker|china local browse|chinaclaw|cityreview robot|contentsmartz|copyright sheriff|cr4nk.|crescent|curl/|da 3.|da 4.|da 5.|da 7.|datacha0s|datafountains|dc-sakura|demo bot|disco pump|ditto|dlman|dontbuylists.com|download demon|download druid|download express|download master|download ninja|download wonder|download.exe|downloader|dreampassport|drip|dts agent|ebrowse|ecatch|educate search|efp@gmx.net|eirgrabber|emailcollector|emailharv|emailsearch|emailsiphon|emailwolf|extractor|extractorpro|extreme|eyenetie|fdm 1.|fdm 2.|fetch api request|filehound|flashget|franklin locator|freshdownload|full web bot|gaisbot/|geniebot|getbot|getright|getweb|go!zilla|go-ahead-got-it|grafula|guestbook auto submitter|guglibot|here will be link to crawler site|hidownload|html2jpg|htmlparser|http://www.seexie.com|http::lite|http_request|httrack|iaskspider|ieautodiscovery|igetter|industry program|indy library|infometrics|installshield digitalwizard|interarchy|interget|internet ninja|iplexx|iptccheck.bot.js|irc search|irvine|iupui research|ivia|jakarta commons-httpclient|java/1|jbh agent|jetcar|jobo|joc web spider|justview|kapere|kontiki client|larbin|leechftp|leechget|letscrawl.com|lftp|libcurl-agent|libwhisker|libwww-perl|lightningdownload|lincoln state web browser|linkwalker|litefinder.net|lwp-request|medusa|mfc foundation class library|mfhttpscan|microsoft url control|midown tool|missauga locate|missigua locator|missouri college browse|mister pix|mizzu labs|mj12bot|mo college|morfeus fucking scanner|morfeus strikes again|mozilla 2.0|mozilla/0.91 beta|mozilla/2.0|mozilla/3.0 (compatible)|mozilla/4.0 (compatible;)|mozilla/5.0 (searchbot|mrcarlito|mulder|mvaclient|myob/6.66|nasa search|nearsite|netants|netbrain_crawler|netpumper|netspider|newt activex|nsauditor|offline explorer|ozelot|pagegrabber|pagenest|page_prefetcher|pagmiedownload|papa foto|pbrowse|penthesilea|peval|photolizenzen|phpcrawl|pictureripper|pingbot/|pmafind|pockey|poe-component-client-http|poirot|poodle predictor|port huron labs|production bot|psycheclone|puf/|puxarapido|pycurl|python-urllib|reget|retriever|revolt|ripper|rss popper - mozilla|sbl-bot|scann|searchbot admin@google.com|seo(http://seox.us/)|seoprofiler|similarpages|sitesnagger|sitesucker|slow scraper|snoopy|sogou spider|sogou web spider|sohu agent|sosospider|spacebison|spbot|speeddownload|ssm agent|ssurf|stardownloader|superbot|superhttp|superinfobot|takeout|teleport|teragramcrawler|toata dragostea|tsurf|twisted pagegetter|uri::fetch/|urlgetfile|useragent:|utilmind httpget|vadixbot|vb wininet|verdacht vergehen|virus_detector|vsyncrawler|w3c-webcon|web downloader|web image collector|web search 00|webauto|webbandit|webcapture|webcopier|webdatacentrebot|webfetch|webimage|webminer|webpix|webreaper|websauger|website explorer|webster|webstripper|websucker|webvulncrawl|webwhacker|webzip|wells search|wep search|west wind internet protocols|wget|wikiwix-bot|wildsoft surfer|willow internet crawler|winget|winhttprequest|wir suchen nur geklaute inhalte|wtabot|www-mechanize|www.aconon.com|www.webintegration.at|www.whoisde.de|www.xrss.eu|wwwoffle|xaldon webspider|xerka webbot|zmeu|_spider_botname _spider_botinfo';
// END ua

// ip+ipr check: skip if client ip whitelisted
if (!defined('PRES_WHITELIST_IP') || strpos('|' . PRES_WHITELIST_IP . '|', '|' . $presCI . '|') === false) {
	if (defined('PRES_BLACKLIST_IP') && strpos('|' . PRES_BLACKLIST_IP . '|', '|' . $presCI . '|') !== false) {
		presRestrictMessage('ip', $presCI);
	}

	if (isset($presIPs) && strpos($presIPs, ' ' . $presO234h . ' ') !== false) {
		presRestrictMessage('ip', $presCI);
	}

	if (defined('PRES_BLACKLIST_IPR')) {
		$presTmp = explode('|', PRES_BLACKLIST_IPR);
		foreach($presTmp AS $presElem) {
			$presVal = explode('/', $presElem);
			if ($presCIL >= ip2long($presVal[0]) && $presCIL <= ip2long($presVal[1])) {
				presRestrictMessage('iprange', $presElem);
			}
		}
	}

	if (isset($presIPRs)) {
		$presTmp = explode(' ', $presIPRs);
		foreach($presTmp AS $presElem) {
			$presVal = explode('/', $presElem);
			if ($presO234 >= hexdec($presVal[0]) && $presO234 <= hexdec($presVal[1])) {
				$presVal = $presO1 . '.' . substr(long2ip(hexdec($presVal[0])), 2) . '/' . $presO1 . '.' . substr(long2ip(hexdec($presVal[1])), 2);
				if (!defined('PRES_WHITELIST_IPR') || strpos('|' . PRES_WHITELIST_IPR . '|', '|' . $presVal . '|') === false) {
					presRestrictMessage('iprange', $presVal);
				}
			}
		}
	}
}

// ua check
if (defined('PRES_BLACKLIST_UA')) {
	$presUAs .= ((isset($presUAs)) ? '|':'') . PRES_BLACKLIST_UA;
}
if (isset($presUAs)) {
	$presCUA = strtolower(PRES_CLIENT_UA);
	$presTmp = explode('|', $presUAs);
	foreach($presTmp AS $presElem) {
		if (strpos($presCUA, $presElem) !== false) {
			if (!defined('PRES_WHITELIST_UA') || strpos('|' . PRES_WHITELIST_UA . '|', '|' . $presElem . '|') === false) {
				presRestrictMessage('ua', $presElem);
			}
		}
	}
}

// optional: uri check
if (defined('PRES_BLACKLIST_URI')) {
	$presTmp = explode('|', PRES_BLACKLIST_URI);
	$presCURI = strtolower(PRES_REQUEST);
	foreach($presTmp AS $presElem) {
		if (strpos($presCURI, $presElem) !== false) {
			presRestrictMessage('uri', $presElem);
		}
	}
}

// optional: topic 79818; very simple query injection check via scheme
if (defined('PRES_CHECK_QUERY')) {
	$presQuery = @$_SERVER['QUERY_STRING'];
	if ($presQuery != '') {
		if (strpos($presQuery, '://') !== false) {
			if (strpos($presQuery, @$_SERVER['HTTP_HOST']) === false) {
				presRestrictMessage('query',  htmlspecialchars($presQuery));       
			}
		}
	}
}

// free mem
$presTmp = null;

?>