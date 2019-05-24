<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Spryk\Model\Spryk\Configuration\Extender\Extenders;

use SprykerSdk\Spryk\Model\Spryk\Configuration\Extender\SprykConfigurationExtenderInterface;

class TargetPathExtender extends AbstractExtender implements SprykConfigurationExtenderInterface
{
    /**
     * @param array $sprykConfig
     *
     * @return array
     */
    public function extend(array $sprykConfig): array
    {
        if ($this->isProject($sprykConfig)) {
            $this->buildProjectPath($sprykConfig);
        }

        return $sprykConfig;
    }

    /**
     * @param array $sprykConfig
     *
     * @return array
     */
    protected function buildProjectPath(array $sprykConfig): array
    {
        $arguments = $this->getArguments($sprykConfig);

        if ($arguments === []) {
            return $sprykConfig;
        }

        if (!isset($arguments['targetPath'])) {
            return $sprykConfig;
        }

        $hasTargetPathDefault = isset($arguments['targetPath']['default']);

        $targetPath = $hasTargetPathDefault
            ? $arguments['targetPath']['default']
            : $arguments['targetPath']['value'];

        $targetPath = $this->buildTargetPath($targetPath);

        if ($hasTargetPathDefault) {
            $arguments['targetPath']['default'] = $targetPath;
        } else {
            $arguments['targetPath']['value'] = $targetPath;
        }

        $sprykConfig = $this->setArguments($arguments, $sprykConfig);

        return $sprykConfig;
    }

    /**
     * @param string $targetPath
     *
     * @return string
     */
    protected function buildTargetPath(string $targetPath): string
    {
        $pathPattern = sprintf('/\%1$ssrc\%1$s.+|\%1$stests\%1$s.+/', DIRECTORY_SEPARATOR);

        preg_match($pathPattern, $targetPath, $result);

        if ($result === []) {
            return $targetPath;
        }

        return ltrim(array_shift($result), DIRECTORY_SEPARATOR);
    }
}
