<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykTest\Spryk\Integration;

use Codeception\Test\Unit;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Spryk
 * @group Integration
 * @group CreateSprykerModuleTest
 * Add your own group annotations below this line
 */
class CreateSprykerModuleTest extends Unit
{
    /**
     * @var \SprykTest\SprykIntegrationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testCreatesModule(): void
    {
        $this->tester->run($this, [
            '--moduleName' => 'FooBar',
        ]);

        static::assertDirectoryExists($this->tester->getModuleDirectory() . 'src');
    }
}
