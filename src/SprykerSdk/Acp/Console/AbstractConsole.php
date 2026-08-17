<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Console;

use ArrayObject;
use SprykerSdk\Acp\AcpConfig;
use SprykerSdk\Acp\AcpFacade;
use SprykerSdk\Acp\AcpFacadeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractConsole extends Command
{
    /**
     * @var int
     */
    public const CODE_SUCCESS = 0;

    /**
     * @var int
     */
    public const CODE_ERROR = 1;

    /**
     * @var \SprykerSdk\Acp\AcpConfig|null
     */
    protected ?AcpConfig $config = null;

    /**
     * @var \SprykerSdk\Acp\AcpFacadeInterface|null
     */
    protected ?AcpFacadeInterface $facade = null;

    /**
     * @param string|null $name
     * @param \SprykerSdk\Acp\AcpConfig|null $config
     */
    public function __construct(?string $name = null, ?AcpConfig $config = null)
    {
        $this->config = $config;

        parent::__construct($name);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \SprykerSdk\Acp\AcpConfig $config
     *
     * @return void
     */
    public function setConfig(AcpConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return \SprykerSdk\Acp\AcpConfig
     */
    protected function getConfig(): AcpConfig
    {
        if ($this->config === null) {
            $this->config = new AcpConfig();
        }

        return $this->config;
    }

    /**
     * @param \SprykerSdk\Acp\AcpFacadeInterface $facade
     *
     * @return void
     */
    public function setFacade(AcpFacadeInterface $facade): void
    {
        $this->facade = $facade;
    }

    /**
     * @return \SprykerSdk\Acp\AcpFacadeInterface
     */
    protected function getFacade(): AcpFacadeInterface
    {
        if ($this->facade === null) {
            $this->facade = new AcpFacade();
        }

        return $this->facade;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \ArrayObject<int, \Transfer\MessageTransfer> $messageTransfers
     *
     * @return void
     */
    protected function printMessages(OutputInterface $output, ArrayObject $messageTransfers): void
    {
        foreach ($messageTransfers as $messageTransfer) {
            $output->writeln($messageTransfer->getMessageOrFail());
        }
    }

    /**
     * @return \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected function getQuestionHelper(): QuestionHelper
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        return $questionHelper;
    }
}
