<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Helper;

use Codeception\Module;
use Generated\Shared\Transfer\OpenApiRequestTransfer;
use Generated\Shared\Transfer\OpenApiTransfer;
use org\bovigo\vfs\vfsStream;
use SprykerSdk\Zed\AppSdk\AppSdkConfig;
use SprykerTest\Shared\Testify\Helper\ConfigHelperTrait;
use SprykerTest\Zed\Testify\Helper\Business\BusinessHelperTrait;

class OpenApiHelper extends Module
{
    use BusinessHelperTrait;
    use ConfigHelperTrait;

    /**
     * @var string|null
     */
    protected ?string $rootUrl = null;

    /**
     * @return \Generated\Shared\Transfer\OpenApiRequestTransfer
     */
    public function haveOpenApiAddRequest(): OpenApiRequestTransfer
    {
        $this->getValidatorHelper()->mockRoot($this->getRootUrl());

        $config = $this->getValidatorHelper()->getConfig() ?? new AppSdkConfig();

        $openApiTransfer = new OpenApiTransfer();
        $openApiTransfer
            ->setTitle('Test title')
            ->setVersion('0.1.0');

        $openApiRequestTransfer = new OpenApiRequestTransfer();
        $openApiRequestTransfer
            ->setTargetFile($config->getDefaultOpenApiFile())
            ->setOpenApi($openApiTransfer);

        return $openApiRequestTransfer;
    }

    /**
     * @return string
     */
    protected function getRootUrl(): string
    {
        if (!$this->rootUrl) {
            $this->rootUrl = vfsStream::setup('root')->url();
        }

        return $this->rootUrl;
    }

    /**
     * @return \SprykerSdkTest\Helper\ValidatorHelper
     */
    protected function getValidatorHelper(): ValidatorHelper
    {
        return $this->getModule('\\' . ValidatorHelper::class);
    }

    /**
     * return void
     *
     * @return void
     */
    public function haveOpenApiFile(): void
    {
        $this->prepareOpenApiFile(codecept_data_dir('api/openapi/valid/valid_openapi.yml'));
    }

    /**
     * @param string $pathToOpenApi
     *
     * @return void
     */
    protected function prepareOpenApiFile(string $pathToOpenApi): void
    {
        $filePath = sprintf('%s/config/api/openapi/openapi.yml', $this->getRootUrl());

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0770, true);
        }
        file_put_contents($filePath, file_get_contents($pathToOpenApi));

        $this->getValidatorHelper()->mockRoot($this->getRootUrl());
    }
}
