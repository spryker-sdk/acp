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
class AppManifestValidateConsole extends AbstractConsole
{
    /**
     * @var string
     */
    public const MANIFEST_PATH = 'manifest-path';

    /**
     * @var string
     */
    public const MANIFEST_PATH_SHORT = 'm';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:manifest:validate')
            ->setDescription('Validates the manifest files.')
            ->addOption(static::MANIFEST_PATH, static::MANIFEST_PATH_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultManifestPath());
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
        $validateRequestTransfer->setManifestPath($input->getOption(static::MANIFEST_PATH));

        $validateResponseTransfer = $this->getFacade()->validateAppManifest($validateRequestTransfer);

        if ($validateResponseTransfer->getErrors()->count() === 0) {
            $this->printMessagesVerbose($output, $validateResponseTransfer->getMessages());

            return static::CODE_SUCCESS;
        }

        $this->printMessagesVerbose($output, $validateResponseTransfer->getErrors());

        return static::CODE_ERROR;
    }
}
