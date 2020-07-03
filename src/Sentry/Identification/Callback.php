<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 */

namespace WatchTower\Sentry\Identification;

use WatchTower\Sentry\GenericCallback;

/**
 * Identification only callback sentry
 *
 * @package WatchTower\Sentry\Identification
 */
class Callback
	extends GenericCallback
{
	protected $eventInstance = 'WatchTower\Event\Identify';
}
