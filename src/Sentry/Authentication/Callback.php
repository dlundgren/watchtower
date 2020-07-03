<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 */

namespace WatchTower\Sentry\Authentication;

use WatchTower\Sentry\GenericCallback;

/**
 * Authentication only callback sentry
 *
 * @package WatchTower\Sentry\Identification
 */
class Callback
	extends GenericCallback
{
	protected $eventInstance = 'WatchTower\Event\Authenticate';
}
