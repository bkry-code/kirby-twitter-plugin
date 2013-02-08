<?php

/**
 * @author Dave Mackintosh <davemackintosh.co.uk>
 */
class Kirby_Twitter {

	/**
	 * Whether we're currently querying Twitter or not.
	 */
	public $fetching = false;

	/**
	 * The new or last fetched status(s)
	 */
	public $results  = null;

	/**
	 * The cache, a file handle for this class
	 */
	private $_cache    = null;

	/**
	 * The desired user handle
	 */
	protected $_user_handle = '';

	/**
	 * The name of the cache to store and read from
	 */
	protected static $cache_name = 'twitter.cache';
	
	public $api = 'https://api.twitter.com/1/statuses/user_timeline.json?';
	
	public $qs  = 'screen_name=%s&count=%d';

	/**
	 * We still want the constructor so don't privatise
	 *
	 * @param The desired users handle
	 */
	public function __construct ($user_handle) {
		$this->_user_handle = $user_handle;
		$this->_cache = c::get(self::$cache_name);

		# Just set this to false to be sure
		$this->fetching = false;

		return $this;
	}

	/**
	 * Creates a curl request to Twitter for the information we
	 * want and will return an array of the results in their raw
	 * format.
	 *
	 * @param $num = int
	 * @return array;
	 */
	public function fetch ($num = 1) {
		if (!$this->fetching) {
			$that = $this;
			return kurl::Instance()
				->url($this->api . sprintf($this->qs, $this->_user_handle, $num))
				->returnData()
				->verify(false)
				->execute(function ($data, $error) use ($that, $num) {
					# The request has finished
					$that->fetching = false;
					
					# If there was an error it was likely unreachable
					# so fetch from cache
					if ($error) {
						return $that->fromCache($num);
					} else {
						$that->updateCache(json_decode($data));
					}
					
					# Decode our result
					$that->results = json_decode($data);
					
					return $that->results;
				});
		}
	}

	/**
	 * Checks for previous results and requests new ones (if possible)
	 * then returns the result at the implied index.
	 *
	 * @param @index = int
	 * @return array
	 */
	public function get ($index = 0) {
		if (!$this->results) {
			$this->fetch(0);
		}
		return (object) $this->results[$index];
	}

	/**
	 * Updates the cache object with new results
	 *
	 * @param $data
	 * @return Kirby_Twitter
	 */
	public function updateCache (array $data) {
		c::set(self::$cache_name, serialize($data));
		return $this;
	}
	
	/**
	 * Reads the status' in the cache when live isn't
	 * available.
	 *
	 * @return array 
	 */
	public function fromCache () {
		if (empty($this->_cache)) {
			return (object) array(
				"error" => "The cache wasn't loaded properly or is disabled.",
				"code"   => -1
			);
		}

		return unserialize($this->_cache);
	}

	/**
	 * Singleton behaviour
	 */
	public static function Instance ($user_handle) {
        static $inst = null;
        if ($inst === null) {
            $inst = new Kirby_Twitter($user_handle);
        }
        return $inst;
    }

}