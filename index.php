<?php

require "vendor/autoload.php";

use Phalcon\Cop\Parser;

$parser = new Parser();
$params = $parser->parse();

$backstopConfig = [
	'url' => $params['url'],
	'referenceUrl' => $params['referenceUrl']
];

$scenarioConfig = [
	'misMatchThreshold' => 1,
	'removeSelectors' => [],
	'delay' => 2000
];

const DEFAULT_JSON = '
{
	"id":"backstop_default",
   "viewports":[
      {
         "label":"phone",
         "width":320,
         "height":480
      },
      {
         "label":"desktop",
         "width":1200,
         "height":768
      }
   ],
   "onBeforeScript":"puppet/onBefore.js",
   "onReadyScript":"puppet/onReady.js",
   "scenarios":[
   ],
   "paths":{
      "bitmaps_reference":"backstop_data/bitmaps_reference",
      "bitmaps_test":"backstop_data/bitmaps_test",
      "engine_scripts":"backstop_data/engine_scripts",
      "html_report":"backstop_data/html_report",
      "ci_report":"backstop_data/ci_report"
   },
   "report":[
      "browser"
   ],
   "engine":"puppeteer",
   "engineOptions":{
      "args":[
         "--no-sandbox"
      ]
   },
   "asyncCaptureLimit":5,
   "asyncCompareLimit":50,
   "debug":false,
   "debugWindow":false
}
';

const DEFAULT_SCENARIO = '
{
	"label": "label",
	"cookiePath": "backstop_data/engine_scripts/cookies.json",
	"url": "http://example.com",
	"referenceUrl": "http://dev.example.com",
	"readyEvent": "",
	"readySelector": "",
	"delay": 2000,
	"hideSelectors": [],
	"removeSelectors": [],
	"hoverSelector": "",
	"clickSelector": "",
	"postInteractionWait": 0,
	"selectors": [],
	"selectorExpansion": true,
	"expect": 0,
	"misMatchThreshold": 2000,
	"requireSameDimensions": true
}
';

$uris = [];

if (($csvFile = fopen($params['uris'], "r")) !== FALSE) {
	while (($data = fgetcsv($csvFile, 1000, ",")) !== FALSE) {
		$uris[] = $data[0];
	}

	// Close the file
	fclose($csvFile);
}

$scenarios = [];
foreach ($uris as $page) {
	$scenario = json_decode(DEFAULT_SCENARIO);

	$scenario->label = $page;
	$scenario->url = $backstopConfig['url'] . $page;
	$scenario->referenceUrl = $backstopConfig['referenceUrl'] . $page;

	$scenarios[] = $scenario;
}

$backstopData = json_decode(DEFAULT_JSON);
$backstopData->scenarios = $scenarios;

$backstopJson = json_encode($backstopData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

$fp = fopen('backstop.json', 'w');
fwrite($fp, $backstopJson);
fclose($fp);
