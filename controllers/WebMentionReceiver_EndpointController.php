<?php
namespace Craft;
require dirname(__DIR__) . '/vendor/autoload.php';

use Mf2; // indieweb/Mf2
use BarnabyWalters\Mf2 as MfC; //barnabywalters/mf-cleaner

class WebMentionReceiver_EndpointController extends BaseController  {
	protected $allowAnonymous = true;	
	
	public function actionEndpoint()  {
		$mention = $this->getMention();
		$this->saveMention($mention);
		$templatesPath = craft()->path->getPluginsPath().'webmentionreceiver/templates/';
		craft()->path->setTemplatesPath($templatesPath);
		$this->renderTemplate('_endpoint.html');
	}

	private function getMention()  {
		# Written by Jeremy Keith
		# Licensed under a CC0 1.0 Universal (CC0 1.0) Public Domain Dedication
		# http://creativecommons.org/publicdomain/zero/1.0/
		 
		if (!isset($_POST['source']) || !isset($_POST['target'])) {
		  	header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
		  	// Show explaination of webmention
		  	exit;
		}
		 
		ob_start();
		$ch = curl_init($_POST['source']);
		curl_setopt($ch,CURLOPT_USERAGENT,'shanehudson.net (webmention.org)');
		curl_setopt($ch,CURLOPT_HEADER,0);
		$ok = curl_exec($ch);
		curl_close($ch);
		$source = ob_get_contents();
		ob_end_clean();
		header($_SERVER['SERVER_PROTOCOL'] . ' 202 Accepted');
		if (stristr($source, $_POST['target'])) {
			return $source;
		}
	}

	private function saveMention($mention)  {
		
		$sectionId = 4;
		$typeId = 6;

		$output = Mf2\parse($mention);
		$flat =  MfC\flattenMicroformats($output);
		$hentries = MfC\findMicroformatsByType($flat, 'h-entry');
		foreach($hentries as $hentry)  {
			$entry = new EntryModel();
			$entry->sectionId = $sectionId;
			$entry->typeId = $typeId; 
		    	$entry->authorId = 1; // 1 for Admin
			$entry->enabled = true;
			$entry->postDate = $hentry['properties']['published'][0];
			$entry->getContent()->setAttributes(array(
				'title' => 'webmention',
				'body' => $hentry['properties']['content'][0]['html'],
				'the_url' => $_POST['source'],
				'relatedPostSlug' => $_POST['target']
			));

			
			if ( !craft()->entries->saveEntry($entry) )  {
				echo "Could not save webmention";
			}
		}		
	}
}
