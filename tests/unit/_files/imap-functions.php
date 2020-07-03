<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
* @copyright 2015
 * @version   0.1
 */
namespace WatchTower\Sentry\Authentication;

	/**
	 * This file is here purely to emulate the PHP imap extension functions so that
	 * the tests can be run to ensure that the IMAP adapter is running nearly as
	 * expected, without needing to setup a full IMAP server
	 */

/**
 * Class to hold the mock without introducing global var
 *
 * @package WatchTower\Sentry\Authentication
 */
class MockImap
{
	public static $mock;
}

/**
 * Open an IMAP stream to a mailbox
 *
 * @link http://php.net/manual/en/function.imap-open.php
 * @param       $mailbox
 * @param       $username
 * @param       $password
 * @param int   $options
 * @param int   $n_retries
 * @param array $params
 * @return bool|\stdClass
 */
function imap_open($mailbox, $username, $password, $options = 0, $n_retries = 0, array $params = null)
{
	$mockImapResource =& MockImap::$mock;

	$mockImapResource->mailbox   = $mailbox;
	$mockImapResource->password  = $password;
	$mockImapResource->username  = $username;
	$mockImapResource->options   = $options;
	$mockImapResource->n_retries = $n_retries;
	$mockImapResource->params    = $params;

	if (isset($mockImapResource->failOpen) || isset($mockImapResource->failOpenConnection)) {
		return false;
	}

	return $mockImapResource;
}

function imap_last_error()
{
	if (isset(MockIMap::$mock->failOpen)) {
		return 'Invalid username / password';
	}
	if (isset(MockImap::$mock->failOpenConnection)) {
		return 'Connection failed to somewhere,993: Connection timed out';
	}

	return false;
}

/**
 * Close an IMAP stream
 *
 * @link http://php.net/manual/en/function.imap-close.php
 * @param resource $imap_stream
 * @param int      $flag
 * @return bool
 */
function imap_close($imap_stream, $flag = 0)
{
	if (is_resource($imap_stream)) {
		$imap_stream->closed = true;
	}

	return true;
}
