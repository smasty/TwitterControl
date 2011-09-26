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
	 * Geerate array of entites for given tweet.
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


	protected function injectEntities($entities, $text){
		$i = 0;
		$end = Strings::length($text) - 1;
		$html = '';
		while($i <= $end){
			if(!isset($entities[$i])){
				$html .= iconv_substr($text, $i, 1, 'UTF-8');
				++$i;
				continue;
			}
			switch($entities[$i]['type']){
				case 'mention':
					$html .= Html::el('span', '@')
						->class('mention')
						->add(Html::el('a', $entities[$i]['screenName'])
						->href("http://twitter.com/{$entities[$i]['screenName']}")
						->target('_blank')
						->title("{$entities[$i]['name']} - @{$entities[$i]['screenName']}"));
					$i += Strings::length($entities[$i]['screenName']) + 1;
					break;

				case 'hashtag':
					$html .= Html::el('a', "#{$entities[$i]['text']}")
						->class('hashtag')
						->href("http://twitter.com/search/?q=%23{$entities[$i]['text']}")
						->target('_blank');
					$i += Strings::length($entities[$i]['text']) + 1;
					break;

				case 'url':
					$html .= Html::el('a', $entities[$i]['display'] ? : $entities[$i]['url'])
						->class('link')
						->href($entities[$i]['url'])
						->target('_blank')
						->title($entities[$i]['expanded'] ? : $entities[$i]['url']);
					$i += Strings::length($entities[$i]['url']);
					break;

				case 'media':
					$html .= Html::el('a', $entities[$i]['display'] ? : $entities[$i]['url'])
						->class('link media')
						->href($entities[$i]['url'])
						->target('_blank')
						->title($entities[$i]['expanded'] ? : $entities[$i]['url'])
						->data(array(
						'media-url' => $entities[$i]['mediaUrl'],
						'media-type' => $entities[$i]['mediaType']
						));
					$i += Strings::length($entities[$i]['url']);
					break;
			}
		}
		return $html;
	}


}
