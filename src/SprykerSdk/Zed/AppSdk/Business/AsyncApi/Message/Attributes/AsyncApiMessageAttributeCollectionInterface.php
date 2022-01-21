<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Zed\AppSdk\Business\AsyncApi\Message\Attributes;

interface AsyncApiMessageAttributeCollectionInterface
{
    /**
     * @return iterable<\SprykerSdk\Zed\AppSdk\Business\AsyncApi\Message\Attributes\AsyncApiMessageAttributeInterface>
     */
    public function getAttributes(): iterable;

    /**
     * @param string $attributeName
     *
     * @return \SprykerSdk\Zed\AppSdk\Business\AsyncApi\Message\Attributes\AsyncApiMessageAttributeInterface|\SprykerSdk\Zed\AppSdk\Business\AsyncApi\Message\Attributes\AsyncApiMessageAttributeCollectionInterface|null
     */
    public function getAttribute(string $attributeName);
}
