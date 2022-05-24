<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Validator\Manifest\Validator;

use SprykerSdk\Acp\AcpConfig;
use SprykerSdk\Acp\Validator\FileValidatorInterface;
use Transfer\MessageTransfer;
use Transfer\ValidateResponseTransfer;

class RequiredFieldsFileValidator implements FileValidatorInterface
{
    /**
     * @var \SprykerSdk\Acp\AcpConfig
     */
    protected AcpConfig $config;

    /**
     * @param \SprykerSdk\Acp\AcpConfig $config
     */
    public function __construct(AcpConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $data
     * @param string $fileName
     * @param \Transfer\ValidateResponseTransfer $validateResponseTransfer
     * @param array|null $context
     *
     * @return \Transfer\ValidateResponseTransfer
     */
    public function validate(
        array $data,
        string $fileName,
        ValidateResponseTransfer $validateResponseTransfer,
        ?array $context = null
    ): ValidateResponseTransfer {
        foreach ($this->config->getRequiredManifestFieldNames() as $requiredManifestFieldName) {
            if (!isset($data[$requiredManifestFieldName])) {
                $messageTransfer = new MessageTransfer();
                $messageTransfer->setMessage(sprintf('Field "%s" must be present in the manifest file "%s" but was not found.', $requiredManifestFieldName, $fileName));
                $validateResponseTransfer->addError($messageTransfer);
            }
        }

        return $validateResponseTransfer;
    }
}
