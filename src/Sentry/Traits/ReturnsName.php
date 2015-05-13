<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Traits;

/**
 * Common Trait for returning the name of the class
 *
 * @package WatchTower\Traits
 */
trait ReturnsName
{
	/**
	 * Returns the name of the class
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->name;
	}
}
