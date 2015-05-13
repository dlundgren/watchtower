<?php
/**
 * WatchTower Authentication system
 *
 * @license   MIT License
 * @author    David Lundgren
 * @link      http://dlundgren.github.io/watchtower
 * @copyright 2015. David Lundgren
 */
namespace WatchTower\Sentry\Authentication;

use WatchTower\Event\Authenticate;
use WatchTower\Event\Event;
use WatchTower\Sentry\Sentry;
use WatchTower\Sentry\Traits\ReturnsName;
use WatchTower\Sentry\Traits\SetsErrorOnEvent;

/**
 * Website scraping authentication sentry
 *
 */
class WebsiteScrape
	implements Sentry
{
	use ReturnsName, SetsErrorOnEvent;

	/**
	 * @var string The sentry name
	 */
	private $name = 'website-scrape';

	/**
	 * @var string The URL
	 */
	private $url = 'http://example.com/?u={{username}}&p={{password}}';

	/**
	 * @var null|string String to match in the response
	 */
	private $matchStringInResponse;

	/**
	 * @var bool Break the chain on failure?
	 */
	private $breakChainOnFailure;

	/**
	 * Initializes the Website Scrape settings
	 *
	 * @param string $name                  The name the sentry is known as
	 * @param string $url                   The URL of the website to do scraping authentication against
	 * @param string $matchStringInResponse What string should be found to indicate success
	 * @param bool   $breakChainOnFailure   If there is a problem should it cause a stop?
	 */
	public function __construct($name, $url, $matchStringInResponse = null, $breakChainOnFailure = false)
	{
		$this->name                  = $name;
		$this->url                   = $url;
		$this->matchStringInResponse = $matchStringInResponse;
		$this->breakChainOnFailure   = $breakChainOnFailure;
	}

	/**
	 * Returns whether or not the given identity/credential are valid
	 *
	 * @param Event $event
	 * @return boolean
	 */
	public function discern(Event $event)
	{
		if (!($event instanceof Authenticate)) {
			return;
		}

		$identity = $event->identity();
		$search   = ['{{username}}', '{{password}}'];
		$replace  = [urlencode($identity->identity()), urlencode($identity->credential())];
		$url      = str_replace($search, $replace, $this->url);

		// I do not like the use @ but this the only way to suppress the warning
		$response = @file_get_contents($url);
		if ($response === false) {
			$this->setErrorOnEvent($event, Sentry::INTERNAL, "Unable to contact the url: $this->url");
			return;
		}

		if (strpos($response, $this->matchStringInResponse) === false) {
			$this->setErrorOnEvent($event, Sentry::INVALID, "Invalid credentials");
		}
	}
}
