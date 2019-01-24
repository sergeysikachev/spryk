<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Spryk\Model\Spryk\Definition\Argument\Callback;

use SprykerSdk\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface;

class RemoveActionSuffixCallback implements CallbackInterface
{
    public const ACTION_SUFFIX = 'Action';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'RemoveActionSuffixCallback';
    }

    /**
     * @param \SprykerSdk\Spryk\Model\Spryk\Definition\Argument\Collection\ArgumentCollectionInterface $argumentCollection
     * @param mixed|null $value
     *
     * @return mixed
     */
    public function getValue(ArgumentCollectionInterface $argumentCollection, $value)
    {
        if (mb_substr($value, - mb_strlen(static::ACTION_SUFFIX)) === static::ACTION_SUFFIX) {
            $value = mb_substr($value, 0, mb_strlen($value) - mb_strlen(static::ACTION_SUFFIX));
        }

        return lcfirst($value);
    }
}