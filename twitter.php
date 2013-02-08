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
	 */
	public function __construct ($user_handle) {
		$this->_user_handle = $user_handle;
		$this->_cache = c::get(self::$cache_name);

		if ($this->_cache === null) {
			c::set(self::$cache_name, "Handle: $user_handle\n");
			$this->_cache = c::get(self::$cache_name);
		}

		# Just set this to false to be sure
		$this->fetching = false;

		return $this;
	}

	private function _read ($in) {
		# Get our lines
		$lines = explode('\n', $in);

		# Foreach line, we want the key and value pair
		foreach ($lines as $line) {
			# So get dat stuff
			$key_val = explode(': ', $line);
		}

		return $this;
	}

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
						$that->updateCache($data);
					}
					
					# Decode our result
					$that->results = json_decode($data);
					
					return $that->results;
				});
		}
	}

	public function get ($index = 0) {
		if (!$this->results) {
			$this->fetch(0);
		}
		return (object) $this->results[$index];
	}

	public function updateCache ($data) {
		c::set(self::$cache_name, serialize($data));
		return $this;
	}
	
	public function fromCache ($num = 1) {
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