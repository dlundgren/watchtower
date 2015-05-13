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

use WatchTower\Exception\InvalidArgument;
use WatchTower\Exception\Domain;

/**
 * Generic Identity
 *
 * This class implements the basics for errors, status, identity, credential for an identity.
 *
 * @package WatchTower\Identity
 */
class GenericIdentity
	implements Identity
{
	/**
	 * @var bool Whether or not the Identity is locked
	 */
	private $locked = false;

	/**
	 * @var int The state of the identity
	 */
	private $state;

	/**
	 * @var mixed The identified object
	 */
	private $identified;

	/**
	 * @var string The identity
	 */
	private $identity;

	/**
	 * @var string The credential
	 */
	private $credential;

	/**
	 * @var array List of errors
	 */
	private $errors = [];

	public function __construct($identity)
	{
		$this->identity = $identity;
	}

	/**
	 * Returns if the identity is anonymous (default)
	 *
	 * @return bool
	 */
	public function isAnonymous()
	{
		return $this->state === Identity::ANONYMOUS;
	}

	/**
	 * Returns if the identity is identified
	 *
	 * @return bool
	 */
	public function isIdentified()
	{
		return $this->state === Identity::IDENTIFIED;
	}

	/**
	 * Returns if the identity is authenticated
	 *
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->state === Identity::AUTHENTICATED;
	}

	/**
	 * Marks the identity as anonymous
	 */
	public function setAnonymous()
	{
		$this->state = Identity::ANONYMOUS;
	}

	/**
	 * Marks the identity as identified
	 */
	public function setIdentified($identified)
	{
		$this->identified = $identified;
		$this->state      = Identity::IDENTIFIED;
	}

	/**
	 * Marks the identity as authenticated
	 */
	public function setAuthenticated()
	{
		$this->state = Identity::AUTHENTICATED;
	}

	/**
	 * Returns if there are errors
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	/**
	 * Returns the errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Clears the list of Errors
	 *
	 * @return mixed
	 */
	public function clearErrors()
	{
		$this->errors = [];
	}

	/**
	 * Adds an error
	 *
	 * @param string $message
	 */
	public function addError($message)
	{
		$this->errors[] = $message;
	}

	/**
	 * Sets the error
	 *
	 * This will clear the current errors
	 *
	 * @param array $messages
	 */
	public function setErrors(array $messages = [])
	{
		$this->errors = $messages;
	}

	/**
	 * Returns the identified identity
	 *
	 * @return mixed
	 */
	public function identified()
	{
		return $this->identified;
	}

	/**
	 * Returns the identity
	 *
	 * @return mixed
	 */
	public function identity()
	{
		return $this->identity;
	}

	/**
	 * Returns the credential
	 *
	 * @return mixed
	 */
	public function credential()
	{
		return $this->credential;
	}

	/**
	 * Sets the identity
	 *
	 * @param string $identity
	 * @throws \InvalidArgumentException When the identity is not set
	 */
	public function setIdentity($identity)
	{
		if ($this->locked) {
			throw new Domain("Identity is locked and cannot be modified");
		}

		if (empty($identity)) {
			throw new InvalidArgument("Identity cannot be empty");
		}

		$this->identity = $identity;
	}

	/**
	 * Sets the credential
	 *
	 * @param string $credential
	 * @throws \InvalidArgumentException When the identity is not set
	 */
	public function setCredential($credential = null)
	{
		if ($this->locked) {
			throw new Domain("Identity is locked and cannot be modified");
		}

		if (empty($credential)) {
			throw new InvalidArgument("Credential cannot be empty");
		}

		$this->credential = $credential;
	}

	/**
	 * Locks the Identity
	 *
	 * This should lock the Identity object to make the identity/credential fields immutable.
	 *
	 * @return mixed
	 */
	public function lock()
	{
		$this->locked = true;
	}
}
