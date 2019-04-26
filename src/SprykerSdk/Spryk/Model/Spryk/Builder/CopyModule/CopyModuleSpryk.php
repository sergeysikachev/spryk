<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Spryk\Model\Spryk\Builder\CopyModule;

use Generated\Shared\Transfer\DataImporterReaderConfigurationTransfer;
use SprykerSdk\Spryk\Model\Spryk\Builder\SprykBuilderInterface;
use SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface;
use SprykerSdk\Spryk\Model\Spryk\Filter\DasherizeFilter;
use SprykerSdk\Spryk\SprykConfig;
use SprykerSdk\Spryk\Style\SprykStyleInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CopyModuleSpryk implements SprykBuilderInterface
{
    protected const ARGUMENT_SOURCE_PATH = 'sourcePath';
    protected const ARGUMENT_ORGANIZATION = 'organization';
    protected const ARGUMENT_MODULE = 'module';

    protected const ARGUMENT_TARGET_PATH = 'targetFilePath';
    protected const ARGUMENT_TO_ORGANIZATION = 'toOrganization';
    protected const ARGUMENT_TO_MODULE = 'toModule';

    /**
     * @var \SprykerSdk\Spryk\SprykConfig
     */
    protected $config;

    /**
     * @var \SprykerSdk\Spryk\Model\Spryk\Filter\DasherizeFilter
     */
    protected $dasherizeFilter;

    /**
     * @param \SprykerSdk\Spryk\SprykConfig $config
     * @param \SprykerSdk\Spryk\Model\Spryk\Filter\DasherizeFilter $dasherizeFilter
     */
    public function __construct(SprykConfig $config, DasherizeFilter $dasherizeFilter)
    {
        $this->config = $config;
        $this->dasherizeFilter = $dasherizeFilter;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'copyModule';
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return bool
     */
    public function shouldBuild(SprykDefinitionInterface $sprykDefinition): bool
    {
        return true;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param \SprykerSdk\Spryk\Style\SprykStyleInterface $style
     *
     * @return void
     */
    public function build(SprykDefinitionInterface $sprykDefinition, SprykStyleInterface $style): void
    {
        $sourceFiles = $this->getSourceFiles($sprykDefinition);
        foreach ($sourceFiles as $sourceFile) {
            $this->copySourceFile($sourceFile, $sprykDefinition, $style);
        }
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return \Symfony\Component\Finder\Finder|\Symfony\Component\Finder\SplFileInfo[]
     */
    protected function getSourceFiles(SprykDefinitionInterface $sprykDefinition): Finder
    {
        $sourcePaths = [
            $this->getSourcePath($sprykDefinition),
            rtrim($this->getSourcePath($sprykDefinition), DIRECTORY_SEPARATOR) . 'Extension',
        ];
        $sourcePaths = array_filter($sourcePaths, 'glob');

        $finder = new Finder();
        $finder->in($sourcePaths)->files()->ignoreDotFiles(false);

        return $finder;
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getSourcePath(SprykDefinitionInterface $sprykDefinition): string
    {
        return $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_SOURCE_PATH);
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $fileInfo
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param \SprykerSdk\Spryk\Style\SprykStyleInterface $style
     *
     * @return void
     */
    protected function copySourceFile(SplFileInfo $fileInfo, SprykDefinitionInterface $sprykDefinition, SprykStyleInterface $style): void
    {
        $targetPath = $this->buildTargetPath($fileInfo, $sprykDefinition);

        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0777, true);
        }

        $sourceFileContent = $fileInfo->getContents();
        $targetFileContent = $this->prepareTargetFileContent($sourceFileContent, $sprykDefinition);

        file_put_contents($targetPath, $targetFileContent);

        $style->report(sprintf(
            'Copied "<fg=green>%s</>" to "<fg=green>%s</>"',
            rtrim($fileInfo->getRelativePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileInfo->getFilename(),
            $targetPath
        ));
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function buildTargetPath(SplFileInfo $fileInfo, SprykDefinitionInterface $sprykDefinition): string
    {
        $searchAndReplaceMap = $this->buildSearchAndReplaceMapForFileName($sprykDefinition);
        $module = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_MODULE);

        $sourcePathRelative = ($fileInfo->getRelativePath()) ? $fileInfo->getRelativePath() . DIRECTORY_SEPARATOR : '';
        $targetPath = $this->getTargetPath($sprykDefinition);

        if (preg_match(sprintf('/\/%sExtension\//', $module), $fileInfo->getPathname())) {
            $targetPath = rtrim($targetPath, DIRECTORY_SEPARATOR) . 'Extension' . DIRECTORY_SEPARATOR;
        }

        $targetPath .= str_replace(array_keys($searchAndReplaceMap), array_values($searchAndReplaceMap), $sourcePathRelative) . $fileInfo->getFilename();

        return $targetPath;
    }

    /**
     * @param SprykDefinitionInterface $sprykDefinition
     *
     * @return array
     */
    protected function buildSearchAndReplaceMapForFileName(SprykDefinitionInterface $sprykDefinition): array
    {
        $organization = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_ORGANIZATION);
        $module = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_MODULE);

        $toOrganization = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TO_ORGANIZATION);
        $toModule = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TO_MODULE);

        return [
            sprintf('/%s/', $organization) => sprintf('/%s/', $toOrganization),
            sprintf('/%s/', $module) => sprintf('/%s/', $toModule),
            sprintf('/%sExtension/', $module) => sprintf('/%sExtension/', $toModule),
        ];
    }

    /**
     * @param string $content
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function prepareTargetFileContent(string $content, SprykDefinitionInterface $sprykDefinition): string
    {
        $searchAndReplaceMap = $this->buildSearchAndReplaceMapForFileContent($sprykDefinition);

        return preg_replace(array_keys($searchAndReplaceMap), array_values($searchAndReplaceMap), $content);
    }

    /**
     * @param SprykDefinitionInterface $sprykDefinition
     *
     * @return array
     */
    protected function buildSearchAndReplaceMapForFileContent(SprykDefinitionInterface $sprykDefinition): array
    {
        $organization = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_ORGANIZATION);
        $organizationDashed = $this->dasherize($this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_ORGANIZATION));
        $module = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_MODULE);
        $moduleDashed = $this->dasherize($this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_MODULE));

        $toOrganization = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TO_ORGANIZATION);
        $toOrganizationDashed = $this->dasherize($this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TO_ORGANIZATION));
        $toModule = $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TO_MODULE);
        $toModuleDashed = $this->dasherize($this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_MODULE));

        return [
            sprintf('/%s\\\\(\w+)\\\\%s(?=[\\\\;])/', $organization, $module) => sprintf('%s\\\\${1}\\\\%s', $toOrganization, $toModule),
            sprintf('/%s\\\\(\w+)\\\\%sExtension(?=[\\\\;])/', $organization, $module) => sprintf('%s\\\\${1}\\\\%sExtension', $toOrganization, $toModule),
            sprintf('/%s\/%s(?=[\"\.\)\/\n])/', $organizationDashed, $moduleDashed) => sprintf('%s/%s', $toOrganizationDashed, $toModuleDashed),
            sprintf('/%s\/%s-extension(?=[\"\.\)\/\n])/', $organizationDashed, $moduleDashed) => sprintf('%s/%s-extension', $toOrganizationDashed, $toModuleDashed),
            sprintf('/"%s\\\\/', $organization) => sprintf('"%s\\', $toOrganization),
            sprintf('/"src\/%s/', $organization) => sprintf('"src/%s', $toOrganization),
            sprintf('/%s\s/', $module) => sprintf('%s ', $toModule),
            sprintf('/%sExtension\s/', $module) => sprintf('%sExtension ', $toModule),
        ];
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getTargetPath(SprykDefinitionInterface $sprykDefinition): string
    {
        return $this->config->getRootDirectory() . $this->getArgumentValueByName($sprykDefinition, static::ARGUMENT_TARGET_PATH);
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param string $argumentName
     *
     * @return string
     */
    protected function getArgumentValueByName(SprykDefinitionInterface $sprykDefinition, string $argumentName): string
    {
        $constantValue = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument($argumentName)
            ->getValue();

        return $constantValue;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function dasherize(string $string): string
    {
        return $this->dasherizeFilter->filter($string);
    }
}
