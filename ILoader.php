<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components\Twitter;


/**
 * TwitterControl tweet loader interface.
 *
 * @author Martin Srank, http://smasty.net
 */
interface ILoader {


	/**
	 * Get the loaded tweets, formatted according to Twitter REST API JSON format.
	 * @param array $config Configuration options
	 * @return array|null
	 */
	public function getTweets(array $config);


}