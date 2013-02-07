<?php

class Kirby_Twitter {

	/**
	 * Whether we're currently querying Twitter or not.
	 */
	private $_fetching = false;

	/**
	 * The new or last fetched status(s)
	 */
	private $_results  = null;

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

	/**
	 * 
	 */
	public function __construct ($user_handle) {
		$this->_user_handle = $user_handle;
		$this->_cache = c::get(self::$cache_name);

		if ($this->_cache === null) {
			c::set(self::$cache_name, "Handle: $user_handle\n");
			$this->_cache = c::get(self::$cache_name);
		}



		var_dump($this->_cache);
	}

}