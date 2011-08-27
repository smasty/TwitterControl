<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components;

use Nette,
	Nette\InvalidStateException,
	Nette\Application\UI\Control,
	Nette\Http\Url,
	Nette\Utils\Json,
	Nette\Utils\JsonException,
	Nette\Utils\Strings,
	Nette\Utils\Html;


/**
 * TwitterControl.
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
 * @version 1.0
 * @license The MIT License, http://opensource.org/licenses/mit-license
 */
class TwitterControl extends Control {


	/** @var string */
	private $templateFile = 'TwitterControl.latte';


	/** @var array */
	private $config;

	/** @var array */
	private $tweetCache = array();


	const VERSION = '1.0';


	/**
	 * Create the TwitterControl.
	 * @param array|string|int $config Config options (array) or Twitter screen name (string) or Twitter user ID (int)
	 * @return void
	 */
	public function __construct($config){
		$e = new InvalidStateException('No screenName/userId specified.');

		if(!$config)
			throw $e;
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

		if($this->config['userId'] == null && $this->config['screenName'] == null)
			throw $e;
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
	 * Render control
	 * @return void
	 */
	protected function doRender(){
		$this->template->setFile($this->getTemplateFile());
		$this->template->config = (object) $this->config;
		$this->template->tweets = $this->loadTweets();
		$this->template->render();
	}


	/**
	 * Get the template file name.
	 * @return string
	 */
	public function getTemplateFile(){
		return dirname($this->getReflection()->getFileName()) . '/' . $this->templateFile;
	}


	/**
	 * Set the template file name, relative to class directory.
	 * @param string $filename
	 * @return TwitterControl fluent interface
	 */
	public function setTemplateFile($filename){
		$this->templateFile = $filename;
		return $this;
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


	/**
	 * Load Tweets from Twitter.
	 * @return array|null
	 */
	public function loadTweets(){
		$path = (string) $this->generateRequestUrl();
		if(isset($this->tweetCache[$path])){
			return $this->tweetCache[$path];
		}
		try{
			return $this->tweetCache[$path] = Json::decode(@file_get_contents($path)); // intentional @ shut-up
		} catch(JsonException $e){
			return null;
		}
	}


	/**
	 * Custom helpers registration.
	 * @param string $class
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL){
		$template = parent::createTemplate($class);

		$template->registerHelper('avatar', function($url){
			return str_replace('_normal.', '_reasonably_small.', $url);
		});
		$template->registerHelper('tweetify', callback($this, 'formatTweet'));
		$template->registerHelper('timeAgo', callback($this, 'relativeTime'));
		$template->registerHelper('twUrl', function($user, $status = null){
			return "http://twitter.com/$user" . ($status ? "/statuses/$status" : '');
		});
		$template->registerHelper('intent', function($status, $action){
			switch($action){
				case 'reply':
					return "http://twitter.com/intent/tweet?in_reply_to=$status";
				case 'retweet':
					return "http://twitter.com/intent/retweet?tweet_id=$status";
				case 'favorite':
					return "http://twitter.com/intent/favorite?tweet_id=$status";
			}
		});

		return $template;
	}


	/**
	 * Relative time template helper.
	 *
	 * Based on David Grudl's timeAgoInWords, New BSD License.
	 * @param mixed $time
	 * @return string
	 */
	public function relativeTime($time){
		if(!$time)
			return false;
		elseif(is_numeric($time))
			$time = (int) $time;
		elseif($time instanceof DateTime)
			$time = $time->format('U');
		else
			$time = strtotime($time);

		$delta = time() - $time;

		$delta = round($delta / 60);
		if ($delta <= 1) return 'just now';
		if ($delta < 45) return "$delta minutes ago";
		if ($delta < 90) return '1 hour ago';
		if ($delta < 1440) return round($delta / 60) . ' hours ago';
		if ($delta < 2880) return date('j M', $time);
		if ($delta < 1051920) return date('j M', $time);
		return date('j M y', $time);
	}


	/**
	 * Tweet formatter - template helper.
	 * @param \stdClass $tweet
	 * @return string
	 */
	public function formatTweet(\stdClass $tweet){
		$entities = array();
		if(!isset($tweet->entities)){
			return $tweet->text;
		}
		foreach($tweet->entities->user_mentions as $mention){
			$entities[$mention->indices[0]] = array(
				'type' => 'mention',
				'screenName' => $mention->screen_name,
				'name' => $mention->name
			);
		}
		foreach($tweet->entities->hashtags as $hashtag){
			$entities[$hashtag->indices[0]] = array(
				'type' => 'hashtag',
				'text' => $hashtag->text
			);
		}
		foreach($tweet->entities->urls as $url){
			$entities[$url->indices[0]] = array(
				'type' => 'url',
				'url' => $url->url,
				'display' => $url->display_url,
				'expanded' => $url->expanded_url
			);
		}
		if(isset($tweet->entities->media)){
			foreach($tweet->entities->media as $media){
				$entities[$media->indices[0]] = array(
					'type' => 'media',
					'url' => $media->url,
					'display' => $media->display_url,
					'expanded' => $media->expanded_url,
					'mediaType' => $media->type,
					'mediaUrl' => $media->media_url
				);
			}
		}
		$pos = 0;
		$text_end = Strings::length($tweet->text) - 1;
		$html = '';
		while($pos <= $text_end){

			if(!isset($entities[$pos])){
				$html .= mb_substr($tweet->text, $pos, 1);
				++$pos;
				continue;
			}
			switch($entities[$pos]['type']){
				case 'mention':
					$html .= Html::el('span', '@')
						->class('mention')
						->add(Html::el('a', $entities[$pos]['screenName'])
							->href("http://twitter.com/{$entities[$pos]['screenName']}")
							->target('_blank')
							->title("{$entities[$pos]['name']} - @{$entities[$pos]['screenName']}"));
					$pos += Strings::length($entities[$pos]['screenName']) + 1;
					break;

				case 'hashtag':
					$html .= Html::el('a', "#{$entities[$pos]['text']}")
						->class('hashtag')
						->href("http://twitter.com/search/?q=%23{$entities[$pos]['text']}")
						->target('_blank');
					$pos += Strings::length($entities[$pos]['text']) + 1;
					break;

				case 'url':
					$html .= Html::el('a', $entities[$pos]['display'] ?: $entities[$pos]['url'])
						->class('link')
						->href($entities[$pos]['url'])
						->target('_blank')
						->title($entities[$pos]['expanded'] ?: $entities[$pos]['url']);
					$pos += Strings::length($entities[$pos]['url']);
					break;

				case 'media':
					$html .= Html::el('a', $entities[$pos]['display'] ?: $entities[$pos]['url'])
						->class('link media')
						->href($entities[$pos]['url'])
						->target('_blank')
						->title($entities[$pos]['expanded'] ?: $entities[$pos]['url'])
						->data(array(
							'media-url' => $entities[$pos]['mediaUrl'],
							'media-type'=> $entities[$pos]['mediaType']
						));
					$pos += Strings::length($entities[$pos]['url']);
					break;
			}
		}
		return $html;
	}


}