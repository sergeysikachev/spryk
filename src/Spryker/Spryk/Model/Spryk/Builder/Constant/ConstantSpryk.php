<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Builder\Constant;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Spryker\Spryk\Exception\EmptyFileException;
use Spryker\Spryk\Exception\ReflectionException;
use Spryker\Spryk\Model\Spryk\Builder\SprykBuilderInterface;
use Spryker\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface;
use Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface;
use Spryker\Spryk\Style\SprykStyleInterface;

class ConstantSpryk implements SprykBuilderInterface
{
    const ARGUMENT_TARGET = 'target';
    const ARGUMENT_TARGET_PATH = 'targetPath';
    const ARGUMENT_TARGET_FILE_NAME = 'targetFileName';
    const ARGUMENT_TEMPLATE = 'template';
    const ARGUMENT_CONSTANT_NAME = 'constantName';

    /**
     * @var \Spryker\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @param \Spryker\Spryk\Model\Spryk\Builder\Template\Renderer\TemplateRendererInterface $renderer
     */
    public function __construct(TemplateRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'constant';
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return bool
     */
    public function shouldBuild(SprykDefinitionInterface $sprykDefinition): bool
    {
        return (!$this->constantExists($sprykDefinition));
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param \Spryker\Spryk\Style\SprykStyleInterface $style
     *
     * @return void
     */
    public function build(SprykDefinitionInterface $sprykDefinition, SprykStyleInterface $style): void
    {
        $targetFileContent = $this->getTargetFileContent($sprykDefinition);

        $templateName = $this->getTemplateName($sprykDefinition);

        $constantContent = $this->renderer->render(
            $templateName,
            $sprykDefinition->getArgumentCollection()->getArguments()
        );

        $search = '{';
        $positionOfOpeningBrace = strpos($targetFileContent, $search);
        if ($positionOfOpeningBrace !== false) {
            $targetFileContent = substr_replace($targetFileContent, $constantContent, $positionOfOpeningBrace + 1, strlen($search));
        }

        $this->putTargetFileContent($sprykDefinition, $targetFileContent);

        $style->report(sprintf(
            'Added constant "<fg=green>%s</>" to "<fg=green>%s</>"',
            $sprykDefinition->getArgumentCollection()->getArgument('constantName'),
            $sprykDefinition->getArgumentCollection()->getArgument('target')
        ));
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return bool
     */
    protected function constantExists(SprykDefinitionInterface $sprykDefinition): bool
    {
        $targetFileContent = $this->getTargetFileContent($sprykDefinition);
        $constantToCheck = sprintf('const %s', $this->getConstantName($sprykDefinition));

        if (strpos($targetFileContent, $constantToCheck) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getTargetArgument(SprykDefinitionInterface $sprykDefinition): string
    {
        return $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_TARGET)
            ->getValue();
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getTemplateName(SprykDefinitionInterface $sprykDefinition): string
    {
        $templateName = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_TEMPLATE)
            ->getValue();

        return $templateName;
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string
     */
    protected function getConstantName(SprykDefinitionInterface $sprykDefinition): string
    {
        $constantName = $sprykDefinition
            ->getArgumentCollection()
            ->getArgument(static::ARGUMENT_CONSTANT_NAME)
            ->getValue();

        return $constantName;
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @throws \Spryker\Spryk\Exception\EmptyFileException
     *
     * @return string
     */
    protected function getTargetFileContent(SprykDefinitionInterface $sprykDefinition): string
    {
        $targetFileName = $this->getTargetFileName($sprykDefinition);

        $targetFileContent = file_get_contents($targetFileName);
        if ($targetFileContent === false || strlen($targetFileContent) === 0) {
            throw new EmptyFileException(sprintf('Target file "%s" seems to be empty', $targetFileName));
        }

        return $targetFileContent;
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @throws \Spryker\Spryk\Exception\ReflectionException
     *
     * @return string
     */
    protected function getTargetFileName(SprykDefinitionInterface $sprykDefinition): string
    {
        $targetFilename = $this->getTargetFileNameFromReflectionClass($sprykDefinition);

        if ($targetFilename === null) {
            throw new ReflectionException('Filename is not expected to be null!');
        }

        return $targetFilename;
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     * @param string $newContent
     *
     * @return void
     */
    protected function putTargetFileContent(SprykDefinitionInterface $sprykDefinition, string $newContent): void
    {
        $targetFilename = $this->getTargetFileName($sprykDefinition);

        file_put_contents($targetFilename, $newContent);
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return \Roave\BetterReflection\Reflection\ReflectionClass|\Roave\BetterReflection\Reflection\Reflection
     */
    protected function getReflection(SprykDefinitionInterface $sprykDefinition)
    {
        $betterReflection = new BetterReflection();

        return $betterReflection->classReflector()->reflect($this->getTargetArgument($sprykDefinition));
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\SprykDefinitionInterface $sprykDefinition
     *
     * @return string|null
     */
    protected function getTargetFileNameFromReflectionClass(SprykDefinitionInterface $sprykDefinition)
    {
        $targetReflection = $this->getReflection($sprykDefinition);
        if (!($targetReflection instanceof ReflectionClass)) {
            return null;
        }

        return $targetReflection->getFileName();
    }
}
