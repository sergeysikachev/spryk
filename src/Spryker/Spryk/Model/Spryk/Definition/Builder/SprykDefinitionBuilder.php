<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Definition\Builder;

use Spryker\Spryk\Model\Spryk\Configuration\Loader\SprykConfigurationLoaderInterface;
use Spryker\Spryk\Model\Spryk\Definition\Argument\Resolver\ArgumentResolverInterface;
use Spryker\Spryk\Model\Spryk\Definition\SprykDefinition;
use Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface;
use Spryker\Spryk\Style\SprykStyleInterface;

class SprykDefinitionBuilder implements SprykDefinitionBuilderInterface
{
    const SPRYK_BUILDER_NAME = 'spryk';
    const ARGUMENTS = 'arguments';

    /**
     * @var \Spryker\Spryk\Model\Spryk\Configuration\Loader\SprykConfigurationLoaderInterface
     */
    protected $sprykLoader;

    /**
     * @var \Spryker\Spryk\Model\Spryk\Definition\Argument\Resolver\ArgumentResolverInterface
     */
    protected $argumentResolver;

    /**
     * @var \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface[]
     */
    protected $definitionCollection = [];

    /**
     * @var string|null
     */
    protected $calledSpryk;

    /**
     * @var \Spryker\Spryk\Style\SprykStyleInterface
     */
    protected $style;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Configuration\Loader\SprykConfigurationLoaderInterface $sprykLoader
     * @param \Spryker\Spryk\Model\Spryk\Definition\Argument\Resolver\ArgumentResolverInterface $argumentResolver
     */
    public function __construct(SprykConfigurationLoaderInterface $sprykLoader, ArgumentResolverInterface $argumentResolver)
    {
        $this->sprykLoader = $sprykLoader;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @param \Spryker\Spryk\Style\SprykStyleInterface $style
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\Builder\SprykDefinitionBuilderInterface
     */
    public function setStyle(SprykStyleInterface $style): SprykDefinitionBuilderInterface
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @param string $sprykName
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    public function buildDefinition(string $sprykName): SprykDefinitionInterface
    {
        if ($this->calledSpryk === null) {
            $this->calledSpryk = $sprykName;
        }

        if (!isset($this->definitionCollection[$sprykName])) {
            $sprykConfiguration = $this->sprykLoader->loadSpryk($sprykName);
            $argumentCollection = $this->argumentResolver->resolve($sprykConfiguration[static::ARGUMENTS], $sprykName, $this->style);

            $sprykDefinition = $this->createDefinition($sprykName, $sprykConfiguration[static::SPRYK_BUILDER_NAME]);
            $this->definitionCollection[$sprykName] = $sprykDefinition;

            $sprykDefinition->setArgumentCollection($argumentCollection);
            $sprykDefinition->setPreSpryks($this->getPreSpryks($sprykConfiguration));
            $sprykDefinition->setPostSpryks($this->getPostSpryks($sprykConfiguration));
            $sprykDefinition->setConfig($this->getConfig($sprykConfiguration));
        }

        return $this->definitionCollection[$sprykName];
    }

    /**
     * @param string $sprykName
     * @param string $builderName
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface
     */
    protected function createDefinition(string $sprykName, string $builderName): SprykDefinitionInterface
    {
        $sprykDefinition = new SprykDefinition();
        $sprykDefinition
            ->setBuilder($builderName)
            ->setSprykName($sprykName);

        return $sprykDefinition;
    }

    /**
     * @param array $sprykConfiguration
     *
     * @return array
     */
    protected function getConfig(array $sprykConfiguration): array
    {
        if (!isset($sprykConfiguration['config'])) {
            return [];
        }

        return $sprykConfiguration['config'];
    }

    /**
     * @param array $sprykConfiguration
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[]
     */
    protected function getPreSpryks(array $sprykConfiguration): array
    {
        $preSpryks = [];
        if (isset($sprykConfiguration['preSpryks'])) {
            foreach ($sprykConfiguration['preSpryks'] as $preSprykName) {
                if ($this->calledSpryk === $preSprykName) {
                    $preSpryks[] = $this->definitionCollection[$this->calledSpryk];
                    continue;
                }

                $preSpryks[] = $this->buildDefinition($preSprykName);
            }
        }

        return $preSpryks;
    }

    /**
     * @param array $sprykConfiguration
     *
     * @return \Spryker\Spryk\Model\Spryk\Definition\SprykDefinition[]
     */
    protected function getPostSpryks(array $sprykConfiguration): array
    {
        $postSpryks = [];
        if (isset($sprykConfiguration['postSpryks'])) {
            foreach ($sprykConfiguration['postSpryks'] as $postSprykName) {
                $postSpryks[] = $this->buildDefinition($postSprykName);
            }
        }

        return $postSpryks;
    }
}
