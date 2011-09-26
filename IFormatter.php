<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components\Twitter;


/**
 * TwitterControl tweet formatter interface.
 *
 * @author Martin Srank, http://smasty.net
 */
interface IFormatter {


	const INTENT_REPLY = 'reply',
		INTENT_RETWEET = 'retweet',
		INTENT_FAVORITE = 'favorite';


	/**
	 * Format given tweet object.
	 * @param \stdClass $tweet
	 * @return \Nette\Utils\Html
	 */
	public function formatTweet(\stdClass $tweet);


	/**
	 * Format given time.
	 * @param mixed $time
	 * @return string
	 */
	public function formatTime($time);


	/**
	 * Format URL for user and his statuses.
	 * @param string|int $user User screenName or ID
	 * @param int|null $status Status ID
	 * @return \Nette\Http\Url
	 */
	public function formatUserUrl($user, $status = null);


	/**
	 * Format URL for tweet intents.
	 * @param int $status
	 * @param string $action
	 * @return \Nette\Http\Url
	 */
	public function formatIntentUrl($status, $action);


}