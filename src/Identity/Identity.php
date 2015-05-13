<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Identity;

/**
 * Identity interface
 *
 * @package WatchTower
 */
interface Identity
{
	/**
	 * Constants that identify the status of the identity
	 */
	const ANONYMOUS = 1;
	const IDENTIFIED = 2;
	const AUTHENTICATED = 3;

	/**
	 * Returns if the identity is anonymous (default)
	 *
	 * @return bool
	 */
	public function isAnonymous();

	/**
	 * Returns if the identity is identified
	 *
	 * @return bool
	 */
	public function isIdentified();

	/**
	 * Returns if the identity is authenticated
	 *
	 * @return bool
	 */
	public function isAuthenticated();

	/**
	 * Marks the identity as anonymous
	 */
	public function setAnonymous();

	/**
	 * Marks the identity as identified
	 *
	 * Saves the data into the identified property
	 *
	 * @param mixed $identification
	 */
	public function setIdentified($identification);

	/**
	 * Marks the identity as authenticated
	 */
	public function setAuthenticated();

	/**
	 * Returns if there are errors
	 *
	 * @return bool
	 */
	public function hasErrors();

	/**
	 * Returns the errors
	 *
	 * @return array
	 */
	public function getErrors();

	/**
	 * Clears the list of Errors
	 * @return mixed
	 */
	public function clearErrors();

	/**
	 * Adds an error
	 *
	 * @param string $message
	 */
	public function addError($message);

	/**
	 * Sets the errors
	 *
	 * This will effectively clear the current errors
	 *
	 * @param array $messages
	 */
	public function setErrors(array $messages = []);

	/**
	 * Returns the identified
	 *
	 * @return mixed
	 */
	public function identified();

	/**
	 * Returns the identity
	 *
	 * @return string
	 */
	public function identity();

	/**
	 * Returns the credential
	 *
	 * @return string
	 */
	public function credential();

	/**
	 * Sets the identity
	 *
	 * @param string $identity
	 * @throws \InvalidArgumentException When the identity is not set
	 */
	public function setIdentity($identity);

	/**
	 * Sets the credential
	 *
	 * @param string $credential
	 * @throws \InvalidArgumentException When the identity is not set
	 */
	public function setCredential($credential = null);

	/**
	 * Locks the Identity
	 *
	 * This should lock the Identity object to make the identity/credential fields immutable.
	 *
	 * @return mixed
	 */
	public function lock();

}
