<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Zed\AppSdk\Communication\Console;

use Codeception\Test\Unit;
use SprykerSdk\Zed\AppSdk\Communication\Console\AbstractConsole;
use SprykerSdk\Zed\AppSdk\Communication\Console\AddAsyncApiConsole;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group SprykerSdkTest
 * @group Zed
 * @group AppSdk
 * @group Communication
 * @group Console
 * @group AddAsyncApiConsoleTest
 */
class AddAsyncApiConsoleTest extends Unit
{
    /**
     * @var \SprykerSdkTest\CommunicationTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testAddAsyncApiReturnsSuccessCodeWhenAsyncApiWasAdded(): void
    {
        // Arrange
        $this->tester->mockRootPath();
        $commandTester = $this->tester->getConsoleTester(AddAsyncApiConsole::class);

        // Act
        $commandTester->execute(
            [
                AddAsyncApiConsole::ARGUMENT_TITLE => 'My Apps AsyncAPI title',
            ],
        );

        // Assert
        $this->assertSame(AbstractConsole::CODE_SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @return void
     */
    public function testAddAsyncApiReturnsErrorCodeAndPrintsErrorMessagesWhenAsyncApiCouldNotBeAdded(): void
    {
        $this->tester->haveAsyncApiFile();
        $commandTester = $this->tester->getConsoleTester(AddAsyncApiConsole::class, false);

        // Act
        $commandTester->execute(
            [
                AddAsyncApiConsole::ARGUMENT_TITLE => 'My Apps AsyncAPI title',
            ],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE],
        );

        // Assert
        $this->assertSame(AbstractConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());
    }
}
