# TwitterControl for Nette Framework

* Copyright 2012 [Smasty](http://smasty.net)
* Licensed under terms of the [MIT License](#the-mit-license)
* Version 2.0.2

## About

TwitterControl is a simple but very powerful visual component for
Nette Framework for displaying tweets on your site.

It supports various display options, can display and properly style
retweets, replies, user info header, with ability to directly retweet,
favorite or reply to a particular tweet.

In default style, it also fulfill the Twitter Display Guidelines.


## Installation

Preferred way of installation is using [Composer](http://getcomposer.org).
Add the following dependency to your `composer.json` file and you're ready to go.

```json
{
	"require": {
		"smasty/TwitterControl": "dev-master"
	}
}
```

## Usage

### In presenter:

```php
<?php
protected function createComponentTwitterFeed($name){
	return new Smasty\Components\Twitter\Control(array(
		'screenName' => 'TesterJohnny',
		'tweetCount' => 10
	));
}
```


### In presenter template:

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


## Available config options

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

## The MIT License

Copyright (c) 2012 Smasty, http://smasty.net/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.