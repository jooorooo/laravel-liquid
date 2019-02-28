<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

class TemplateTest extends TestCase
{
	const CACHE_DIR = 'cache_dir';

	/** @var string full path to cache dir  */
	protected $cacheDir;

	protected function setUp() {
		parent::setUp();

		$this->cacheDir = __DIR__ . DIRECTORY_SEPARATOR . self::CACHE_DIR;
	}

	protected function tearDown() {
		parent::tearDown();

		// Remove tmp cache files
		array_map('unlink', glob($this->cacheDir . DIRECTORY_SEPARATOR . '*'));
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSetCacheInvalidKey() {
		$template = new LiquidEngine();
		$template->setFiles(array());
	}

	/**
	 * @expectedException \Liquid\LiquidException
	 */
	public function testSetCacheInvalidClass() {
		$template = new LiquidEngine();
		$template->setFiles(array('cache' => 'no_such_class'));
	}

	public function testSetCacheThroughArray() {
		$template = new LiquidEngine();
		$template->setFiles(array('cache' => 'file', 'cache_dir' => $this->cacheDir));
		$this->assertInstanceOf('\Liquid\Cache\File', $template::getCache());
	}

	public function testSetCacheThroughCacheObject() {
		$template = new LiquidEngine();
		$cache = new Cache\File(array('cache_dir' => $this->cacheDir));
		$template->setFiles($cache);
		$this->assertEquals($cache, $template::getCache());
	}

	public function testTokenizeStrings() {
		$this->assertEquals(array(' '), LiquidEngine::tokenize(' '));
		$this->assertEquals(array('hello world'), LiquidEngine::tokenize('hello world'));
	}

	public function testTokenizeVariables() {
		$this->assertEquals(array('{{funk}}'), LiquidEngine::tokenize('{{funk}}'));
		$this->assertEquals(array(' ', '{{funk}}', ' '), LiquidEngine::tokenize(' {{funk}} '));
		$this->assertEquals(array(' ', '{{funk}}', ' ', '{{so}}', ' ', '{{brother}}', ' '), LiquidEngine::tokenize(' {{funk}} {{so}} {{brother}} '));
		$this->assertEquals(array(' ', '{{  funk  }}', ' '), LiquidEngine::tokenize(' {{  funk  }} '));
	}

	public function testTokenizeBlocks() {
		$this->assertEquals(array('{%comment%}'), LiquidEngine::tokenize('{%comment%}'));
		$this->assertEquals(array(' ', '{%comment%}', ' '), LiquidEngine::tokenize(' {%comment%} '));
		$this->assertEquals(array(' ', '{%comment%}', ' ', '{%endcomment%}', ' '), LiquidEngine::tokenize(' {%comment%} {%endcomment%} '));
		$this->assertEquals(array('  ', '{% comment %}', ' ', '{% endcomment %}', ' '), LiquidEngine::tokenize("  {% comment %} {% endcomment %} "));
	}

	public function testBlackspace() {
		$template = new LiquidEngine();
		$template->parse('  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(array('  '), $nodelist);
	}

	public function testVariableBeginning() {
		$template = new LiquidEngine();
		$template->parse('{{funk}}  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(2, count($nodelist));
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[0]);
		$this->assertInternalType('string', $nodelist[1]);
	}

	public function testVariableEnd() {
		$template = new LiquidEngine();
		$template->parse('  {{funk}}');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(2, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
	}

	public function testVariableMiddle() {
		$template = new LiquidEngine();
		$template->parse('  {{funk}}  ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(3, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
	}

	public function testVariableManyEmbeddedFragments() {
		$template = new LiquidEngine();
		$template->parse('  {{funk}}  {{soul}}  {{brother}} ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(7, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[3]);
		$this->assertInternalType('string', $nodelist[4]);
		$this->assertInstanceOf('\Liquid\Variable', $nodelist[5]);
		$this->assertInternalType('string', $nodelist[6]);
	}

	public function testWithBlock() {
		$template = new LiquidEngine();
		$template->parse('  {% comment %}  {% endcomment %} ');

		$nodelist = $template->getRoot()->getNodelist();

		$this->assertEquals(3, count($nodelist));
		$this->assertInternalType('string', $nodelist[0]);
		$this->assertInstanceOf('\Liquid\Tag\TagComment', $nodelist[1]);
		$this->assertInternalType('string', $nodelist[2]);
	}
}
