<?php

/**
 * dbBotTrap
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/de/addons/dbconnect.php
 * @copyright 2009-2012 phpManufaktur by Ralf Hertsch
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License (GPL)
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');

if (DEBUG) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}
else {
	ini_set('display_errors', 0);
	error_reporting(E_ERROR);
}


$tool = new botTrapTool();
$tool->action();

class botTrapTool {

	const request_action 						= 'act';

	const action_default            = 'def';
	const action_start							= 'start';
	const action_help								= 'help';

	private $tab_navigation_array = array(
		self::action_start						=> bt_tab_start,
		self::action_help							=> bt_tab_help
	);

	private $page_link 					= '';
	private $img_url						= '';
	private $template_path			= '';
	private $error							= '';
	private $message						= '';

	private $swNavHide					= array();

	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=dbbottrap';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL.'/modules/'.basename(dirname(__FILE__)).'/img/';
	} // __construct()

	/**
    * Set $this->error to $error
    *
    * @param STR $error
    */
  public function setError($error) {
    $this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    *
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    *
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    *
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    *
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    *
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    return -1;
  } // getVersion()

	/**
   * Verhindert XSS Cross Site Scripting
   *
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) {
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()

  public function action() {
  	// prevent Cross Site Scripting
  	foreach ($_REQUEST as $key => $value) {
  		$_REQUEST[$key] = $this->xssPrevent($value);
  	}

    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
  	switch ($action):
  	case self::action_help:
  		$this->show(self::action_help, $this->dlgHelp());
  		break;
    default:
  		$this->show(self::action_start, $this->dlgStart());
  		break;
  	endswitch;
  } // action


  /**
   * Erstellt eine Navigationsleiste
   *
   * @param $action - aktives Navigationselement
   * @return STR Navigationsleiste
   */
  public function getNavigation($action) {
  	$result = '';
  	foreach ($this->tab_navigation_array as $key => $value) {
  		if (!in_array($key, $this->swNavHide)) {
	  		($key == $action) ? $selected = ' class="selected"' : $selected = '';
	  		$result .= sprintf(	'<li%s><a href="%s">%s</a></li>',
	  												$selected,
	  												sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
	  												$value
	  												);
  		}
  	}
  	$result = sprintf('<ul class="nav_tab">%s</ul>', $result);
  	return $result;
  } // getNavigation()

  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   *
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   *
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	if ($this->isError()) {
  		$content = $this->getError();
  		$class = ' class="error"';
  	}
  	else {
  		$class = '';
  	}
  	$parser = new tParser();
  	$parser->add('navigation', $this->getNavigation($action));
  	$parser->add('class', $class);
  	$parser->add('content', $content);
  	$parser->parseTemplateFile($this->template_path.'backend.body.htt');
  	$parser->echoHTML();
  } // show()

	public function dlgStart() {
		global $ver;
		global $sig;
		$result = '';
		if (file_exists(WB_PATH.'/page.restrictor.php')) {
			(isset($ver)) ? $bt_version = $ver : $bt_version = '-unknown-';
			(isset($sig)) ? $bt_signatur = $sig : $bt_signatur = '-unknown-';
			$result = sprintf(bt_msg_bt_info, $bt_version, $bt_signatur);
			if (defined('PRES_LOG_FILE')) {
				// es ist eine LOG Datei definiert
				if (file_exists(PRES_LOG_FILE)) {
					// LOG File existiert
					$file_array = file(PRES_LOG_FILE);
					$file_array = array_reverse($file_array);
					foreach ($file_array as $entry) {
						$entry_array = explode(' - ', $entry);
						$result .= sprintf(	'<div class="bt_entry"><span class="bte_label">%s</span><span class="bte_date">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_ip">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_client">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_reference">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_request">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_reason">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_data">%s</span><br />'.
																'<span class="bte_label">%s</span><span class="bte_captcha">%s</span></div>',
																bt_label_date,
																$entry_array[0],
																bt_label_ip,
																$entry_array[1],
																bt_label_agent,
																$entry_array[2],
																bt_label_reference,
																$entry_array[3],
																bt_label_request,
																$entry_array[4],
																bt_label_reason,
																$entry_array[5],
																bt_label_data,
																$entry_array[6],
																bt_label_captcha_state,
																$entry_array[7]);
					} // foreach

				}
				else {
					// LOG File definiert aber nicht gefunden...
					$basename = basename(PRES_LOG_FILE);
					$result .= sprintf(bt_msg_log_def_not_exists, $basename);
				}
			}
			else {
				// keine Logdatei definiert
				$result .= bt_msg_no_log_defined;
			}
		}
		else {
			// Page Resctrictor nicht gefunden...
			$result = bt_error_missing_pr;
		}
		return $result;
	} // dlgStart()

	public function dlgHelp() {
		$help_file = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'_help.html';
		if (file_exists($help_file)) {
			return file_get_contents($help_file);
		}
		return sprintf(bt_error_missing_help, basename($help_file));
	} // dlgHelp()

} // botTrapTool
?>