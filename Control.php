<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components\Twitter;

use Nette,
	Nette\InvalidStateException,
	Nette\Diagnostics\Debugger,
	Nette\Application\UI\Control as NetteControl;


/**
 * TwitterControl renderable component.
 *
 * Available config options:
 * - screenName => Twitter screen name (either screenName or userId is required)
 * - userId => Twitter user ID (takes precedence over screenName, if both specified)
 * - tweetCount => Number of tweets to load (max. 200)
 *
 * - header => Render component header with user info
 * - avatars => Render avatars next to tweets
 * - retweets => Include retweets
 * - replies => Include replies
 * - intents => Render tweet intents (reply, retweet, favorite)
 *
 * @author Martin Srank, http://smasty.net
 */
class Control extends NetteControl {


	/** @var string */
	public static $templateDirectory = '/templates';

	/** @var string */
	private $templateFile = '/TwitterControl.latte';

	/** @var array */
	private $config;

	/** @var ILoader */
	private $loader;

	/** @var IFormatter */
	private $formatter;


	const VERSION = '2.0.2';


	/**
	 * Create the TwitterControl.
	 * @param array|string|int $config Config options (array) or Twitter screen name (string) or Twitter user ID (int)
	 * @return void
	 */
	public function __construct($config){
		if(!$config)
			throw new InvalidStateException('No configuration given.');
		if(is_scalar($config))
			$config = array((is_numeric($config) ? 'userId' : 'screenName') => $config);

		$defaults = array(
			'screenName' => null,
			'userId' => null,
			'tweetCount' => 5,
			'header' => true,
			'avatars' => true,
			'retweets' => true,
			'replies' => true,
			'intents' => true
		);

		$this->config = $config + $defaults;

		if(!$this->config['userId'] && !$this->config['screenName'])
			throw new InvalidStateException('No screenName/userId specified.');
	}


	/**
	 * Render with predefined config.
	 * @param array $config Config overrides
	 * @return void
	 */
	public function render(array $config = null){
		if($config !== null)
			$this->config = $config + $this->config;
		$this->doRender();
	}


	/**
	 * Render full control (header, avatars, retweets, replies, intents)
	 * @param array $config Config overrides
	 * @return void
	 */
	public function renderFull(array $config = null){
		$overrides = array(
			'header' => true,
			'avatars' => true,
			'retweets' => true,
			'replies' => true,
			'intents' => true
		);
		$this->config = $overrides + (array) $this->config;

		if($config !== null)
			$this->config = $config + $this->config;
		$this->doRender();
	}


	/**
	 * Render medium control (avatars, retweets, replies; no header, no intents)
	 * @param array $config Config overrides
	 * @return void
	 */
	public function renderMedium(array $config = null){
		$overrides = array(
			'header' => false,
			'avatars' => true,
			'retweets' => true,
			'replies' => true,
			'intents' => false
		);
		$this->config = $overrides + $this->config;

		if($config !== null)
			$this->config = $config + $this->config;
		$this->doRender();
	}


	/**
	 * Render minimal control (replies, retweets; no header, no avatars, no intents)
	 * @param array $config Config overrides
	 * @return void
	 */
	public function renderMinimal(array $config = null){
		$overrides = array(
			'header' => false,
			'avatars' => false,
			'retweets' => true,
			'replies' => true,
			'intents' => false
		);
		$this->config = $overrides + $this->config;

		if($config !== null)
			$this->config = $config + $this->config;
		$this->doRender();
	}


	/**
	 * Get the tweet loader.
	 * @return ILoader;
	 */
	public function getLoader(){
		if($this->loader === null)
			$this->loader = new StandardLoader;
		return $this->loader;
	}


	/**
	 * Set the tweet loader.
	 * @param ILoader $loader
	 * @return void
	 */
	public function setLoader(ILoader $loader){
		$this->loader = $loader;
	}


	/**
	 * Get the tweet formatter.
	 * @return IFormatter
	 */
	public function getFormatter(){
		if($this->formatter === null)
			$this->formatter = new StandardFormatter;
		return $this->formatter;
	}


	/**
	 * Set the tweet formatter.
	 * @param IFormatter $formatter
	 * @return void
	 */
	public function setFormatter(IFormatter $formatter){
		$this->formatter = $formatter;
	}


	/**
	 * Get the template file name.
	 * @return string
	 */
	public function getTemplateFile(){
		return dirname($this->reflection->fileName)
			. static::$templateDirectory
			. $this->templateFile;
	}


	/**
	 * Set the template file name, relative to the template directory.
	 * @param string $filename
	 * @return TwitterControl fluent interface
	 */
	public function setTemplateFile($filename){
		$this->templateFile = $filename;
		return $this;
	}


	/**
	 * Render the component.
	 * @return void
	 */
	protected function doRender(){
		$this->template->setFile($this->getTemplateFile());
		$this->template->config = (object) $this->config;
		ob_start();
		try{
			$this->template->tweets = $this->getLoader()->getTweets($this->config);
			$this->template->render();
			ob_end_flush();
		} catch(TwitterException $e){
			if(Debugger::$productionMode){
				Debugger::log($e, Debugger::WARNING);
				ob_end_clean();
			} else{
				throw $e;
				ob_end_flush();
			}
		}
	}


	/**
	 * Custom helpers registration.
	 * @param string $class
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL){
		$template = parent::createTemplate($class);

		$formatter = $this->getFormatter();
		$template->registerHelper('avatar', function($url){
				return str_replace('_normal.', '_reasonably_small.', $url);
			});
		$template->registerHelper('tweetFormat', callback($formatter, 'formatTweet'));
		$template->registerHelper('timeFormat', callback($formatter, 'formatTime'));
		$template->registerHelper('userLink', callback($formatter, 'formatUserUrl'));
		$template->registerHelper('intentLink', callback($formatter, 'formatIntentUrl'));

		return $template;
	}


}


class TwitterException extends \Exception {}