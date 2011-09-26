<?php
/**
 * TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
 * Copyright 2011 Martin Srank (http://smasty.net)
 * Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)
 */

namespace Smasty\Components\Twitter;

use Nette,
	Nette\Http\Url,
	Nette\Utils\Html,
	Nette\Utils\Strings;


/**
 * TwitterControl tweet loader interface.
 *
 * @author Martin Srank, http://smasty.net
 */
class StandardFormatter extends Nette\Object implements IFormatter {


	public function formatTweet(\stdClass $tweet){
		if(!isset($tweet->entities))
			return $tweet->text;

		$entities = $this->generateEntities($tweet);
		return $this->injectEntities($entities, $tweet->text);
		;
	}


	/**
	 * Relative time template helper.
	 *
	 * Based on David Grudl's timeAgoInWords, New BSD License.
	 * @param mixed $time
	 * @return string
	 */
	public function formatTime($time){
		if(!$time)
			return false;
		elseif(is_numeric($time))
			$time = (int) $time;
		elseif($time instanceof \DateTime)
			$time = $time->format('U');
		else
			$time = strtotime($time);

		$delta = time() - $time;

		$delta = round($delta / 60);
		if($delta <= 1)
			return 'just now';
		if($delta < 45)
			return "$delta minutes ago";
		if($delta < 90)
			return '1 hour ago';
		if($delta < 1440)
			return round($delta / 60) . ' hours ago';
		if($delta < 2880)
			return date('j M', $time);
		if($delta < 1051920)
			return date('j M', $time);
		return date('j M y', $time);
	}


	public function formatUserUrl($user, $status = null){
		return new Url("http://twitter.com/$user" . ($status !== null ? "/statuses/$status" : ''));
	}


	public function formatIntentUrl($status, $action){
		$base = 'http://twitter.com/intent';
		switch($action){
			case self::INTENT_REPLY:
				return new Url("$base/tweet?in_reply_to=$status");

			case self::INTENT_RETWEET:
				return new Url("$base/retweet?tweet_id=$status");

			case self::INTENT_FAVORITE:
				return new Url("$base/favorite?tweet_id=$status");
		}
	}


	/**
	 * Generate array of entites for given tweet.
	 * @param \stdClass $tweet
	 * @return array
	 */
	protected function generateEntities(\stdClass $tweet){
		$entities = array();
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
		return $entities;
	}


	/**
	 * Inject entities to their position in the given text.
	 * @param array $entities
	 * @param string $text
	 * @return string
	 */
	protected function injectEntities(array $entities, $text){
		$i = 0;
		$end = Strings::length($text) - 1;
		$html = '';
		while($i <= $end){
			if(!isset($entities[$i])){
				$html .= iconv_substr($text, $i, 1, 'UTF-8');
				$i++;
				continue;
			}
			$entity = $entities[$i];
			switch($entity['type']){
				case 'mention':
					$html .= $this->createMentionEntity($entity);
					$i += Strings::length($entity['screenName']) + 1;
					break;

				case 'hashtag':
					$html .= $this->createHashtagEntity($entity);
					$i += Strings::length($entity['text']) + 1;
					break;

				case 'url':
					$html .= $this->createUrlEntity($entity);
					$i += Strings::length($entity['url']);
					break;

				case 'media':
					$html .= $this->createMediaEntity($entity);
					$i += Strings::length($entity['url']);
					break;
			}
		}
		return $html;
	}


	/**
	 * Create HTML for Mention entity.
	 * @param array $entity
	 * @return Html
	 */
	protected function createMentionEntity($entity){
		return Html::el('span', '@')
				->class('mention')
				->add(
					Html::el('a', $entity['screenName'])
						->href("http://twitter.com/$entity[screenName]")
						->target('_blank')
						->title("$entity[name] - @$entity[screenName]")
				);
	}


	/**
	 * Create HTML for Hashtag entity.
	 * @param array $entity
	 * @return Html
	 */
	protected function createHashtagEntity($entity){
		return Html::el('a', "#$entity[text]")
				->class('hashtag')
				->href("http://twitter.com/search/?q=%23$entity[text]")
				->target('_blank');
	}


	/**
	 * Create HTML for URL entity.
	 * @param array $entity
	 * @return Html
	 */
	protected function createUrlEntity($entity){
		return Html::el('a', $entity['display'] ? : $entity['url'])
				->class('link')
				->href($entity['url'])
				->target('_blank')
				->title($entity['expanded'] ? : $entity['url']);
	}


	/**
	 * Create HTML for Media entity.
	 * @param array $entity
	 * @return Html
	 */
	protected function createMediaEntity($entity){
		return Html::el('a', $entity['display'] ? : $entity['url'])
				->class('link media media-' . $entity['mediaType'])
				->href($entity['url'])
				->target('_blank')
				->title($entity['expanded'] ? : $entity['url'])
				->data(array(
					'media-url' => $entity['mediaUrl'],
					'media-type' => $entity['mediaType']
				));
	}


}
