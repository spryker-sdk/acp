<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Helper;

use Codeception\Module;
use Transfer\ValidateRequestTransfer;
use Transfer\ValidateResponseTransfer;

class ValidatorHelper extends Module
{
    use AcpHelperTrait;

    /**
     * @return void
     */
    public function haveValidConfigurations(): void
    {
        $structure = $this->getValidBaseStructure();

        $this->getAcpHelper()->mockDirectoryStructure($structure);
    }

    /**
     * @return array<array<array<\array>>>
     */
    protected function getValidBaseStructure(): array
    {
        return [
            'config' => [
                'app' => [
                    'translation.json' => file_get_contents(codecept_data_dir('valid/translation/translation.json')),
                    'configuration.json' => file_get_contents(codecept_data_dir('valid/configuration/translation.json')),
                    'manifest' => [
                        'de_DE.json' => file_get_contents(codecept_data_dir('valid/manifest/de_DE.json')),
                        'en_US.json' => file_get_contents(codecept_data_dir('valid/manifest/en_US.json')),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Transfer\ValidateRequestTransfer
     */
    public function haveValidateRequest(): ValidateRequestTransfer
    {
        $config = $this->getAcpHelper()->getConfig();

        $validateRequest = new ValidateRequestTransfer();
        $validateRequest->setManifestPath($config->getDefaultManifestPath());
        $validateRequest->setConfigurationFile($config->getDefaultConfigurationFile());
        $validateRequest->setTranslationFile($config->getDefaultTranslationFile());

        return $validateRequest;
    }

    /**
     * @param \Transfer\ValidateResponseTransfer $validateResponseTransfer
     *
     * @return array
     */
    public function getMessagesFromValidateResponseTransfer(ValidateResponseTransfer $validateResponseTransfer): array
    {
        $messages = [];

        foreach ($validateResponseTransfer->getErrors() as $messageTransfer) {
            $messages[] = $messageTransfer->getMessage();
        }

        return $messages;
    }

    /**
     * @return \Transfer\ValidateRequestTransfer
     */
    public function haveChannelsValidateRequest(): ValidateRequestTransfer
    {
        $config = $this->getAcpHelper()->getConfig();

        $validateRequest = new ValidateRequestTransfer();
        $validateRequest->setConfigurationFile($config->getDefaultProjectConfigurationFile());

        return $validateRequest;
    }
}
