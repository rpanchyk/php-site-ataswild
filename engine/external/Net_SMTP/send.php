<?php
require_once dirname(__FILE__) . '/../../inc/cde.inc.php';

// http://dev.1c-bitrix.ru/community/webdev/group/18/blog/1073/

require_once 'SMTP.php';

function custom_mail($emailFrom, $emailTo, $subject, $message, $smtpServerHost, $smtpServerPort = '25', $smtpServerUser = '', $smtpServerPass = '', $additionalHeaders = '')
{
	if (!($smtp = new Net_SMTP($smtpServerHost, intval($smtpServerPort))))
		return false;

	if (PEAR::isError($e = $smtp->connect()))
		return false;

	if (!empty($smtpServerUser) && PEAR::isError($e = $smtp->auth($smtpServerUser, $smtpServerPass)))
		return false;

	$smtp->mailFrom($emailFrom);
	$smtp->rcptTo($emailTo);

	$eol = "\r\n";

	$additionalHeaders .= (!empty($additionalHeaders) ? $eol : '') . 'Subject: ' . $subject;
	$additionalHeaders .= $eol . 'To: ' . $emailTo;
	$additionalHeaders .= $eol . 'From: ' . $emailFrom;
	$additionalHeaders .= $eol . 'Reply-To: ' . $emailFrom;
	$additionalHeaders .= $eol . 'Content-Type: text/plain; charset=utf-8';
	$additionalHeaders .= $eol . 'Content-Transfer-Encoding: 8bit';

	if (PEAR::isError($e = $smtp->data($additionalHeaders . "\r\n\r\n" . $message)))
		return false;

	$smtp->disconnect();

	return true;
}
