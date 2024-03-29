<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Acp\Console;

use Codeception\Test\Unit;
use SprykerSdk\Acp\Console\RegisterConsole;
use SprykerSdkTest\Acp\Tester;
use Symfony\Component\Console\Output\OutputInterface;
use Transfer\RegisterRequestTransfer;

/**
 * @group SprykerSdkTest
 * @group Acp
 * @group Console
 * @group RegisterConsoleTest
 */
class RegisterConsoleTest extends Unit
{
    /**
     * @var \SprykerSdkTest\Acp\Tester
     */
    protected Tester $tester;

    public function testRequestBuilderReturnsValidRequest(): void
    {
        // Arrange
        $registerRequestTransfer = (new RegisterRequestTransfer())
            ->setAppIdentifier('1234-5678-9012-3456')
            ->setBaseUrl('http://www.example.com/')
            ->setAcpApiFile(codecept_data_dir('valid/api/api.json'))
            ->setConfigurationFile(codecept_data_dir('valid/configuration/configuration.json'))
            ->setTranslationFile(codecept_data_dir('valid/translation/translation.json'))
            ->setManifestPath(codecept_data_dir('valid/manifest/'));

        // Act
        $requestBody = $this->tester->getFacade()->getRegistrationRequestBody($registerRequestTransfer);

        // Assert
        $this->assertJson($requestBody);
        $decodedRequest = json_decode($requestBody, true);
        $this->assertNotEmpty($decodedRequest['data']['attributes']['id']);
        $this->assertSame('1234-5678-9012-3456', $decodedRequest['data']['attributes']['id']);
        $this->assertNotEmpty($decodedRequest['data']['attributes']['baseUrl']);
        $this->assertSame('http://www.example.com', $decodedRequest['data']['attributes']['baseUrl'], 'Without trailing /');
        $this->assertNotEmpty($decodedRequest['data']['attributes']['api']);
        $this->assertIsString($decodedRequest['data']['attributes']['api']);
        $this->assertNotEmpty($decodedRequest['data']['attributes']['manifest']);
        $this->assertIsString($decodedRequest['data']['attributes']['manifest']);
        $this->assertNotEmpty($decodedRequest['data']['attributes']['configuration']);
        $this->assertIsString($decodedRequest['data']['attributes']['configuration']);
        $this->assertNotEmpty($decodedRequest['data']['attributes']['translation']);
        $this->assertIsString($decodedRequest['data']['attributes']['translation']);
    }

    /**
     * @return void
     */
    public function testRegisterAppReturnsSuccessfulResponseWhenAppWasRegisteredInAcp(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse();

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--appIdentifier' => '1234-5678-9012-3456',
            '--baseUrl' => 'http://www.example.com/',
            '--authorizationToken' => '1234-5678-9012-3456',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_SUCCESS, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertSame("App successfully registered or updated in ACP.\n", $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppReturnsSuccessfulResponseWhenAppWasUpdatedInAcp(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse(409, 201);

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--appIdentifier' => '1234-5678-9012-3456',
            '--baseUrl' => 'http://www.example.com/',
            '--authorizationToken' => '1234-5678-9012-3456',
        ], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_SUCCESS, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertSame("App successfully registered or updated in ACP.\n", $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppReturnsErrorResponseWhenATokenIsWrong(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse(403);

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--appIdentifier' => '1234-5678-9012-3456',
            '--baseUrl' => 'http://www.example.com/',
            '--authorizationToken' => 'bad-token',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertSame("Could not register the App in ACP. Use -v to see errors.\n", $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppPrintsErrorWhenAppIdentifierIsNotPassed(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse();

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--baseUrl' => 'http://www.example.com/',
            '--authorizationToken' => '1234-5678-9012-3456',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertStringContainsString('You need to pass an AppIdentifier with the option `--appIdentifier`.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppPrintsErrorWhenBaseUrlIsNotPassed(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse();

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--appIdentifier' => '1234-5678-9012-3456',
            '--authorizationToken' => '1234-5678-9012-3456',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertStringContainsString('You need to pass a base URL to your App with the option `--baseUrl`.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppPrintsErrorWhenAuthorizationTokenIsNotPassed(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse();

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--appIdentifier' => '1234-5678-9012-3456',
            '--baseUrl' => 'http://www.example.com/',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertStringContainsString('You need to pass an authorization token with the option `--authorizationToken`.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testRegisterAppPrintsErrorWhenAppShouldBeMadePrivateButTenantIdentifierIsNotPassed(): void
    {
        // Arrange
        $this->tester->haveValidConfigurations();
        $registerConsole = $this->tester->getRegisterConsoleWithAtrsResponse();

        // Act
        $commandTester = $this->tester->getConsoleTester($registerConsole);
        $commandTester->execute([
            '--private' => true,
            '--appIdentifier' => '1234-5678-9012-3456',
            '--baseUrl' => 'http://www.example.com/',
            '--authorizationToken' => '1234-5678-9012-3456',
        ]);

        // Assert
        $this->assertSame(RegisterConsole::CODE_ERROR, $commandTester->getStatusCode());
        $this->assertStringContainsString('You need to pass a Tenant Identifier with the option `--tenantIdentifier` when you want this App to be only visible to you.', $commandTester->getDisplay());
    }
}
