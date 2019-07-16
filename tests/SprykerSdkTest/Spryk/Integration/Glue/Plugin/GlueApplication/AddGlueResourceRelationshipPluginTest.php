<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Spryk\Integration\Glue\Plugin\GlueApplication;

use Codeception\Test\Unit;

class AddGlueResourceRelationshipPluginTest extends Unit
{
    /**
     * @var \SprykerSdkTest\SprykIntegrationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testAddsGlueResourceRelationshipPlugin(): void
    {
        $this->tester->run($this, [
            '--module' => 'FooBar',
            '--resourceType' => 'foo-bars',
            '--relationshipParameter' => 'baz',
            '--mode' => 'core',
        ]);

        static::assertFileExists($this->tester->getModuleDirectory() . 'src/Spryker/Glue/FooBar/Plugin/GlueApplication/FooBarByBazResourceRelationshipPlugin.php');
        static::assertFileExists($this->tester->getModuleDirectory() . 'src/Spryker/Glue/FooBar/Processor/Expander/FooBarByBazExpanderInterface.php');
        static::assertFileExists($this->tester->getModuleDirectory() . 'src/Spryker/Glue/FooBar/Processor/Expander/FooBarByBazExpander.php');
    }

    /**
     * @return void
     */
    public function testAddsGlueResourceRelationshipPluginOnProjectLayer(): void
    {
        $this->tester->run($this, [
            '--module' => 'FooBar',
            '--resourceType' => 'foo-bars',
            '--relationshipParameter' => 'baz',
            '--mode' => 'project',
        ]);

        static::assertFileExists($this->tester->getProjectModuleDirectory('FooBar', 'Glue') . 'Plugin/GlueApplication/FooBarByBazResourceRelationshipPlugin.php');
        static::assertFileExists($this->tester->getProjectModuleDirectory('FooBar', 'Glue') . 'Processor/Expander/FooBarByBazExpanderInterface.php');
        static::assertFileExists($this->tester->getProjectModuleDirectory('FooBar', 'Glue') . 'Processor/Expander/FooBarByBazExpander.php');
    }
}