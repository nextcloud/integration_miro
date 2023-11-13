<?php

namespace OCA\Miro\Tests;

use OCA\Miro\AppInfo\Application;
use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase {

	public function testDummy() {
		$app = new Application();
		$this->assertEquals('integration_miro', $app::APP_ID);
	}
}
