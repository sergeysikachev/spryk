<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest;

use Codeception\Actor;
use Codeception\Stub;
use Spryker\Spryk\Console\SprykDumpConsole;
use Spryker\Spryk\Console\SprykRunConsole;
use Spryker\Spryk\SprykConfig;
use Spryker\Spryk\SprykFacade;
use Spryker\Spryk\SprykFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class SprykConsoleTester extends Actor
{
    use _generated\SprykConsoleTesterActions;

//    /**
//     * @return string
//     */
//    public function getRootDirectory(): string
//    {
//        return codecept_data_dir();
//    }
//
//    /**
//     * @param string $module
//     *
//     * @return string
//     */
//    public function getModuleDirectory(string $module = 'FooBar'): string
//    {
//        return sprintf('%svendor/spryker/spryker/Bundles/%s/', $this->getRootDirectory(), $module);
//    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    public function getConsoleTester(Command $command)
    {
        $application = new Application();
        $application->add($command);

        if ($command instanceof SprykRunConsole || $command instanceof SprykDumpConsole) {
            $command->setFacade($this->getFacadeWithMockedConfig());
        }

        $command = $application->find($command->getName());

        return new CommandTester($command);
    }

    /**
     * @return \Spryker\Spryk\SprykFacade
     */
    protected function getFacadeWithMockedConfig(): SprykFacade
    {
        $sprykConfig = $this->getSprykConfigMock();

        $sprykFactory = new SprykFactory();
        $sprykFactory->setConfig($sprykConfig);

        $sprykFacade = new SprykFacade();
        $sprykFacade->setFactory($sprykFactory);

        return $sprykFacade;
    }

    /**
     * @return object|\Spryker\Spryk\SprykConfig
     */
    protected function getSprykConfigMock()
    {
        $sprykConfig = Stub::make(new SprykConfig(), [
            'getRootDirectory' => function () {
                return $this->getRootDirectory();
            },
            'getSprykDirectories' => function () {
                return [$this->getRootDirectory() . 'config/spryks/'];
            },
            'getRootSprykDirectories' => function () {
                return [$this->getRootDirectory() . 'config/'];
            },
            'getTemplateDirectories' => function () {
                return [$this->getRootDirectory() . 'config/templates/'];
            },
        ]);

        return $sprykConfig;
    }

    /**
     * @return string
     */
    public function getRootDirectory(): string
    {
        return realpath(__DIR__ . '/../../tests/_data/') . DIRECTORY_SEPARATOR;
    }
}
