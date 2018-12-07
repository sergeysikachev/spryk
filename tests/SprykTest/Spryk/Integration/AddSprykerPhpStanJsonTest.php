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
 * @group AddSprykerPhpStanJsonTest
 * Add your own group annotations below this line
 */
class AddSprykerPhpStanJsonTest extends Unit
{
    /**
     * @var \SprykTest\SprykIntegrationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testAddsPhpStanJsonFile(): void
    {
        $this->tester->run($this, [
            '--moduleName' => 'FooBar',
            '--organization' => 'Spryker',
        ]);

        static::assertFileExists($this->tester->getModuleDirectory() . 'phpstan.json');
    }
}
