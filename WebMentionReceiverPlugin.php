<?php
namespace Craft;

class WebMentionReceiverPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Webmention Receiver');
	}

	function getVersion()
	{
		return '0.1';
	}

	function getDeveloper()
	{
		return 'Shane Hudson';
	}

	function getDeveloperUrl()
	{
		return 'http://shanehudson.net';
	}
	
}
