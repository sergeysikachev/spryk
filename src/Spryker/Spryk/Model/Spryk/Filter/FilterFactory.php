<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Filter;

use Spryker\Spryk\Model\Spryk\Builder\Template\Extension\TwigFilterExtension;
use Twig\Extension\ExtensionInterface;

class FilterFactory
{
    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface[]
     */
    public function getFilterCollection(): array
    {
        return [
            $this->createCamelBackFilter(),
            $this->createClassNameShortFilter(),
            $this->createEnsureControllerSuffixFilter(),
            $this->createRemoveControllerSuffixFilter(),
            $this->createRemoveActionSuffixFilter(),
            $this->createDasherizeFilter(),
            $this->createUnderscoreFilter(),
            $this->createCamelCaseFilter(),
            $this->createLowerCaseFirstFilter(),
            $this->createCamelCaseToWhitespaceFilter(),
        ];
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Builder\Template\Extension\TwigFilterExtension|\Twig\Extension\ExtensionInterface
     */
    public function createFilterExtension(): ExtensionInterface
    {
        return new TwigFilterExtension($this->getFilterCollection());
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createCamelBackFilter(): FilterInterface
    {
        return new CamelBackFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createClassNameShortFilter(): FilterInterface
    {
        return new ClassNameShortFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createEnsureControllerSuffixFilter(): FilterInterface
    {
        return new EnsureControllerSuffixFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createRemoveControllerSuffixFilter(): FilterInterface
    {
        return new RemoveControllerSuffixFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createRemoveActionSuffixFilter(): FilterInterface
    {
        return new RemoveActionSuffixFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createDasherizeFilter(): FilterInterface
    {
        return new DasherizeFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createUnderscoreFilter(): FilterInterface
    {
        return new UnderscoreFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createCamelCaseFilter(): FilterInterface
    {
        return new CamelCaseFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createLowerCaseFirstFilter(): FilterInterface
    {
        return new LowerCaseFirstFilter();
    }

    /**
     * @return \Spryker\Spryk\Model\Spryk\Filter\FilterInterface
     */
    public function createCamelCaseToWhitespaceFilter(): FilterInterface
    {
        return new CamelCaseToWhitespaceFilter();
    }
}