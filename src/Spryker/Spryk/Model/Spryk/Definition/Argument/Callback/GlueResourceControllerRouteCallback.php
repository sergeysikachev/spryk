<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Spryk\Model\Spryk\Definition\Argument\Callback;

use Spryker\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface;

class GlueResourceControllerRouteCallback implements CallbackInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GlueResourceControllerRouteCallback';
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
        $constantSufix = preg_replace('/(?<!^)([A-Z])/', '-\\1', $resourceName);

        return strtolower($constantSufix) . '-resource';
    }
}
