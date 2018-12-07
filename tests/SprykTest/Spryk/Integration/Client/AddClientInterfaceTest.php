<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykTest\Spryk\Integration\Client;

use Codeception\Test\Unit;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Spryk
 * @group Integration
 * @group Client
 * @group AddClientInterfaceTest
 * Add your own group annotations below this line
 */
class AddClientInterfaceTest extends Unit
{
    protected const SPRYK_NAME = 'AddClientInterface';

    /**
     * @var \SprykTest\SprykIntegrationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testAddsClientInterfaceFile(): void
    {
        $this->tester->run($this, [
            '--moduleName' => 'FooBar',
        ]);

        static::assertFileExists($this->tester->getModuleDirectory() . 'src/Spryker/Client/FooBar/FooBarClientInterface.php');
    }
}
