<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Transfer\ValidateRequestTransfer;

/**
 * @method \SprykerSdk\Acp\AcpFacadeInterface getFacade()
 */
class AppTranslationValidateConsole extends AbstractConsole
{
    /**
     * @var string
     */
    public const TRANSLATION_FILE = 'translation-file';

    /**
     * @var string
     */
    public const TRANSLATION_FILE_SHORT = 't';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:translation:validate')
            ->setDescription('Validates the translation file.')
            ->addOption(static::TRANSLATION_FILE, static::TRANSLATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultTranslationFile())
            ->addOption(AppManifestValidateConsole::MANIFEST_PATH, AppManifestValidateConsole::MANIFEST_PATH_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultManifestPath())
            ->addOption(AppConfigurationValidateConsole::CONFIGURATION_FILE, AppConfigurationValidateConsole::CONFIGURATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultConfigurationFile());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $validateRequestTransfer = new ValidateRequestTransfer();
        $validateRequestTransfer->setTranslationFile($input->getOption(static::TRANSLATION_FILE));
        $validateRequestTransfer->setManifestPath($input->getOption(AppManifestValidateConsole::MANIFEST_PATH));
        $validateRequestTransfer->setConfigurationFile($input->getOption(AppConfigurationValidateConsole::CONFIGURATION_FILE));

        $validateResponseTransfer = $this->getFacade()->validateAppTranslation($validateRequestTransfer);

        if ($validateResponseTransfer->getErrors()->count() === 0) {
            $this->printMessagesVerbose($output, $validateResponseTransfer->getMessages());

            return static::CODE_SUCCESS;
        }

        $this->printMessagesVerbose($output, $validateResponseTransfer->getErrors());

        return static::CODE_ERROR;
    }
}
