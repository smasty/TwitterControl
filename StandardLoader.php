<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components\Twitter;

use Nette,
	Nette\Http\Url,
	Nette\Utils\Json,
	Nette\Utils\JsonException,
	Nette\Diagnostics\Debugger;


/**
 * TwitterControl tweet loader using Twitter JSON REST API via HTTP stream wrapper.
 *
 * @author Martin Srank, http://smasty.net
 */
class StandardLoader extends Nette\Object implements ILoader {


	/** @var array */
	private $config = array();

	/** @var array */
	private $tweetCache = array();


	public function getTweets(array $config){
		$this->config = $config;

		$path = (string) $this->generateRequestUrl();
		if(isset($this->tweetCache[$path])){
			return $this->tweetCache[$path];
		}

		Debugger::tryError();
		$content = file_get_contents($path);
		if(Debugger::catchError($e)){
			throw new TwitterException($e->getMessage(), $e->getCode(), $e);
			return;
		}

		try{
			return $this->tweetCache[$path] = Json::decode($content);
		} catch(JsonException $e){
			throw new TwitterException($e->getMessage(), $e->getCode(), $e);
		}
	}


	/**
	 * Generate URL for Twitter JSON API request.
	 * @return Url
	 */
	protected function generateRequestUrl(){
		$url = new Url('https://api.twitter.com/1/statuses/user_timeline.json');

		if($this->config['userId'])
			$url->appendQuery('user_id=' . $this->config['userId']);
		elseif($this->config['screenName'])
			$url->appendQuery('screen_name=' . $this->config['screenName']);

		if($this->config['tweetCount'])
			$url->appendQuery('count=' . $this->config['tweetCount']);
		if($this->config['retweets'])
			$url->appendQuery('include_rts=true');
		if(!$this->config['replies'])
			$url->appendQuery('exclude_replies=true');

		$url->appendQuery('include_entities=true');
		return $url;
	}


}