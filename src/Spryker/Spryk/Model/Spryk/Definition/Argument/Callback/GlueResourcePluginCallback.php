<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Definition\Argument\Callback;

use Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface;

class GlueResourcePluginCallback implements CallbackInterface
{
    protected const FILENAME_SUFIX = 'ResourceRoutePlugin';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GlueResourcePluginCallback';
    }

    /**
     * @param \Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface $argumentCollection
     * @param mixed|null $value
     *
     * @throws \Spryker\Spryk\Exception\ArgumentNotFoundException
     *
     * @return mixed
     */
    public function getValue(ArgumentCollectionInterface $argumentCollection, $value)
    {
        $resourceName = $argumentCollection->getArgument('resourceName')->getValue();
        $resourceName = str_replace(static::FILENAME_SUFIX, '', $resourceName);

        return $resourceName . static::FILENAME_SUFIX . '.php';
    }
}
