<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry;

use WatchTower\Event\Event;

/**
 * Generic sentry for callbacks on any event
 *
 * Typically used for rapid prototyping
 *
 * @package WatchTower\Sentry
 */
class GenericCallback
	implements Sentry
{
	/*
	 * The name of this guardian
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The callback to call when an event is handled
	 *
	 * @var \Closure
	 */
	private $callback;

	/**
	 * Type of instance that this callback listens for
	 *
	 * Default: listen for any event
	 *
	 * @var string
	 */
	protected $eventInstance;

	/**
	 * Sets up the callback
	 *
	 * @param string   $name
	 * @param \Closure $callback
	 */
	public function __construct($name, \Closure $callback)
	{
		$this->name     = $name;
		$this->callback = $callback;
	}

	/**
	 * Handles the event
	 *
	 * @param Event $event
	 * @return mixed|void
	 */
	public function discern(Event $event)
	{
		if ($this->eventInstance && !($event instanceof $this->eventInstance)) {
			return;
		}

		$callback = $this->callback->bindTo($this, $this);
		$callback($event);
	}
}
