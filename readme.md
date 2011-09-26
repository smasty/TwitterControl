* TwitterControl for Nette Framework 2.0, http://github.com/smasty/TwitterControl
* Copyright 2011 Martin Srank (http://smasty.net)
* Licensed under terms of the MIT License (http://opensource.org/licenses/mit-license)

Version 2.0

About
=====

TwitterControl is a simple but very powerful visual component for
Nette Framework for displaying tweets on your site.

It supports various display options, can display and properly style
retweets, replies, user info header, with ability to directly retweet,
favorite or reply to a particular tweet.

In default style, it also fulfill the Twitter Display Guidelines.


Usage
=====

In presenter:

	protected function createComponentTwitterFeed($name){
		return new Smasty\Components\Twitter\Control(array(
			'screenName' => 'TesterJohnny',
			'tweetCount' => 10
		));
	}


In presenter template:

	// Default look
	{control twitterFeed}

	// Full look
	{control twitterFeed:full}

	// Medium look
	{control twitterFeed:medium}

	// Minimal look
	{control twitterFeed:minimal}

	// Custom look
	{control twitterFeed, tweetCount => 5, replies => false}


Don't forget to embed the CSS style file along with the sprite image,
and also the JavaScript file.
You can find them in the 'client-side' directory.


Available config options
========================

	screenName  Twitter screen name (either screenName or userId is required)
	userId      Twitter user ID (takes precedence over screenName, if both specified)
	tweetCount  Number of tweets to load (max. 200)

	header      Render component header with user info
	avatars     Render avatars next to tweets
	retweets    Include retweets
	replies     Include replies
	intents     Render tweet intents (reply, retweet, favorite)

All config options can be passed either to the class constructor, or the render method.
Screen name or User ID, however, have to be specified in constructor.
