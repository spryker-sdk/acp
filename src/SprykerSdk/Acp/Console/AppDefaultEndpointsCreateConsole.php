<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Transfer\CreateDefaultEndpointsRequestTransfer;

/**
 * @method \SprykerSdk\Acp\AcpFacadeInterface getFacade()
 */
class AppDefaultEndpointsCreateConsole extends AbstractConsole
{
    /**
     * @var string
     */
    public const OPTION_SCHEMA_FILE = 'schema-file';

    /**
     * @var string
     */
    public const OPTION_SCHEMA_FILE_SHORT = 's';

    /**
     * @var string
     */
    public const OPTION_CONFIGURATION_FILE = 'configuration-file';

    /**
     * @var string
     */
    public const OPTION_CONFIGURATION_FILE_SHORT = 'c';

    /**
     * @var string
     */
    public const OPTION_ADD_LOCAL_FILE = 'add-local';

    /**
     * @var string
     */
    public const OPTION_DEFAULTS_FILE_PATH = 'defaults-path';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:default-endpoints:create')
            ->setDescription('Creates default ACP-related endpoints in PBC OpenAPI schema.')
            ->addOption(static::OPTION_SCHEMA_FILE, static::OPTION_SCHEMA_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultOpenapiFile())
            ->addOption(static::OPTION_CONFIGURATION_FILE, static::OPTION_CONFIGURATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultConfigurationFile())
            ->addOption(static::OPTION_ADD_LOCAL_FILE, '', InputOption::VALUE_NONE, 'Add local registry.yml file instead of using remote link');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $createDefaultEndpointsRequestTransfer = new CreateDefaultEndpointsRequestTransfer();
        $createDefaultEndpointsRequestTransfer->setSchemaFile($input->getOption(static::OPTION_SCHEMA_FILE))
            ->setConfigurationFile($input->getOption(static::OPTION_CONFIGURATION_FILE))
            ->setAddLocal($input->getOption(static::OPTION_ADD_LOCAL_FILE));

        $createDefaultEndpointsResponseTransfer = $this->getFacade()
            ->createDefaultEndpoints($createDefaultEndpointsRequestTransfer);

        if ($createDefaultEndpointsResponseTransfer->getErrors()->count() === 0) {
            $this->printMessages($output, $createDefaultEndpointsResponseTransfer->getMessages());

            return static::CODE_SUCCESS;
        }

        $this->printMessages($output, $createDefaultEndpointsResponseTransfer->getErrors());

        return static::CODE_ERROR;
    }
}
