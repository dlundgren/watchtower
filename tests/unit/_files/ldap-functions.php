<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015
 * @version   0.1
 */
namespace WatchTower\Sentry\Authentication;

	/**
	 * This file is here purely to emulate the PHP ldap extension functions so that
	 * the tests can be run to ensure that the LDAP adapters are running nearly as
	 * expected, without needing to setup a full LDAP server.
	 */

/**
 * Class to hold the mock without introducing global var
 *
 * @package WatchTower\Sentry\Authentication
 */
class MockLdap
{
	public static $mock;
}

/**
 * Connect to an LDAP server
 *
 * @link http://php.net/manual/en/function.ldap-connect.php
 * @param null $hostname
 * @param int  $port
 * @return bool|\stdClass
 */
function ldap_connect($hostname = null, $port = 389)
{
	$mockLdapResource =& MockLdap::$mock;

	$mockLdapResource->hostname = $hostname;
	$mockLdapResource->port     = $port;

	if (isset($mockLdapResource->failConnect)) {
		return false;
	}

	return $mockLdapResource;
}

/**
 * Bind to LDAP directory
 *
 * @link http://php.net/manual/en/function.ldap-bind.php
 * @param      $link_identifier
 * @param null $bind_rdn
 * @param null $bind_password
 * @return bool
 */
function ldap_bind($link_identifier, $bind_rdn = null, $bind_password = null)
{
	if ($bind_rdn === '____FAIL____') {
		// test for ldap_bind($ldap, ldap_get_dn)...)
		return false;
	}

	$link_identifier->bind_rdn      = $bind_rdn;
	$link_identifier->bind_password = $bind_password;
	if (isset($link_identifier->failBind)) {
		return false;
	}

	return true;
}

/**
 * Search LDAP tree
 *
 * @link http://php.net/manual/en/function.ldap-search.php
 * @param       $link_identifier
 * @param       $base_dn
 * @param       $filter
 * @param array $attributes
 * @param null  $attrsonly
 * @param null  $sizelimit
 * @param null  $timelimit
 * @param null  $deref
 * @return bool
 */
function ldap_search($link_identifier, $base_dn, $filter, array $attributes = null, $attrsonly = null,
					 $sizelimit = null, $timelimit = null, $deref = null)
{
	if (isset($link_identifier->failGroupSearch) && $attributes == ['memberOf']) {
		return false;
	}

	if (isset($link_identifier->failSearch)) {
		return false;
	}

	return isset($link_identifier->returnSearch) ? $link_identifier->returnSearch : null;
}

/**
 * Return first result id
 *
 * @link http://php.net/manual/en/function.ldap-first-entry.php
 * @param $link_identifier
 * @param $result_identifier
 * @return bool
 */
function ldap_first_entry($link_identifier, $result_identifier)
{
	return isset($link_identifier->failFirstEntry) ? false : (isset($link_identifier->returnFirstEntry) ? $link_identifier->returnFirstEntry : null);
}

/**
 * Get the DN of a result entry
 *
 * @link http://php.net/manual/en/function.ldap-get-dn.php
 * @param $link_identifier
 * @param $result_entry_identifier
 * @return bool|string
 */
function ldap_get_dn($link_identifier, $result_entry_identifier)
{
	if (isset($link_identifier->failBindGetDn)) {
		return '____FAIL____';
	}

	return isset($link_identifier->failGetDn) ? false : (isset($link_identifier->returnGetDn) ? $link_identifier->returnGetDn : null);
}

/**
 * Get attributes from a search result entry
 *
 * @link http://php.net/manual/en/function.ldap-get-attributes.php
 * @param $link_identifier
 * @param $result_entry_identifier
 * @return
 */
function ldap_get_attributes($link_identifier, $result_entry_identifier)
{
	return isset($link_identifier->returnGetAttributes) ? $link_identifier->returnGetAttributes : null;
}

/**
 * Unbind from LDAP directory
 *
 * @link http://php.net/manual/en/function.ldap-unbind.php
 * @param $link_identifier
 */
function ldap_unbind($link_identifier)
{
	if ($link_identifier) {
		$link_identifier->closed = true;
	}
}

/**
 * Free result memory
 *
 * @link http://php.net/manual/en/function.ldap-free-result.php
 * @param resource $result_identifier
 * @return bool
 */
function ldap_free_result($result_identifier)
{
	$mockLdapResource =& MockLdap::$mock;

	if (isset($mockLdapResource->resultFreed)) {
		$mockLdapResource->resultFreed++;
	}
	else {
		$mockLdapResource->resultFreed = 1;
	}

	return true;
}
