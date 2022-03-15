<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Zed\AppSdk\Communication\Console;

use Symfony\Component\Console\Application;

class ConsoleBootstrap extends Application
{
    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'AppSdk', $version = '1')
    {
        parent::__construct($name, $version);

        $this->setCatchExceptions(false);
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        foreach ($this->getCommands() as $command) {
            $commands[$command->getName()] = $command;
        }

        return $commands;
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    private function getCommands(): array
    {
        return [
            new AsyncApiValidateConsole(),
            new ManifestValidateConsole(),
            new ConfigurationValidateConsole(),
            new TranslationValidateConsole(),
            new ValidateConsole(),
        ];
    }
}
