<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Helper;

use Codeception\Module;
use Codeception\Stub;
use Codeception\TestInterface;
use Generated\Shared\Transfer\ValidateRequestTransfer;
use Generated\Shared\Transfer\ValidateResponseTransfer;
use org\bovigo\vfs\vfsStream;
use SprykerSdk\Zed\AppSdk\AppSdkConfig;
use SprykerSdk\Zed\AppSdk\Business\AppSdkBusinessFactory;
use SprykerSdk\Zed\AppSdk\Business\AppSdkFacade;
use SprykerSdk\Zed\AppSdk\Business\AppSdkFacadeInterface;

class ValidatorHelper extends Module
{
    protected ?string $rootPath = null;

    /**
     * @param string $rootPath
     *
     * @return void
     */
    public function mockRoot(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return void
     */
    public function haveValidConfigurations(): void
    {
        $structure = $this->getValidBaseStructure();

        $root = vfsStream::setup('root', null, $structure);
        $this->mockRoot($root->url());
    }

    /**
     * @return array<array<array<\array>>>
     */
    protected function getValidBaseStructure(): array
    {
        return [
            'config' => [
                'app' => [
                    'translation' => [
                        'translation.json' => file_get_contents(codecept_data_dir('valid/translation/translation.json')),
                    ],
                    'manifest' => [
                        'de_DE.json' => file_get_contents(codecept_data_dir('valid/manifest/de_DE.json')),
                        'en_US.json' => file_get_contents(codecept_data_dir('valid/manifest/en_US.json')),
                    ],
                    'configuration' => [
                        'configuration.json' => file_get_contents(codecept_data_dir('valid/configuration/translation.json')),
                    ],
                ],
                'api' => [
                    'asyncapi' => [
                        'asyncapi.yml' => file_get_contents(codecept_data_dir('api/asyncapi/valid/base_asyncapi.schema.yml')),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \SprykerSdk\Zed\AppSdk\Business\AppSdkFacadeInterface
     */
    public function getFacade(): AppSdkFacadeInterface
    {
        return new AppSdkFacade($this->getFactory());
    }

    /**
     * @return \SprykerSdk\Zed\AppSdk\Business\AppSdkBusinessFactory|null
     */
    public function getFactory(): ?AppSdkBusinessFactory
    {
        $config = $this->getConfig();

        if ($config === null) {
            return null;
        }

        return new AppSdkBusinessFactory($this->getConfig());
    }

    /**
     * @return \SprykerSdk\Zed\AppSdk\AppSdkConfig|null
     */
    public function getConfig(): ?AppSdkConfig
    {
        if ($this->rootPath === null) {
            return null;
        }

        return Stub::make(AppSdkConfig::class, [
            'getProjectRootPath' => function () {
                return $this->rootPath;
            },
        ]);
    }

    /**
     * @return \Generated\Shared\Transfer\ValidateRequestTransfer
     */
    public function haveValidateRequest(): ValidateRequestTransfer
    {
        $config = $this->getConfig() ?? new AppSdkConfig();

        $validateRequest = new ValidateRequestTransfer();
        $validateRequest->setAsyncApiFile($config->getDefaultAsyncApiFile());
        $validateRequest->setManifestPath($config->getDefaultManifestPath());
        $validateRequest->setConfigurationFile($config->getDefaultConfigurationFile());
        $validateRequest->setTranslationFile($config->getDefaultTranslationFile());

        return $validateRequest;
    }

    /**
     * @param \Generated\Shared\Transfer\ValidateResponseTransfer $validateResponseTransfer
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
     * @param \Codeception\TestInterface $test
     *
     * @return void
     */
    public function _after(TestInterface $test): void
    {
        $this->rootPath = null;
    }
}
