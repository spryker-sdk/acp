<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Acp;

use Codeception\Test\Unit;

/**
 * @group SprykerSdk
 * @group Acp
 * @group AppManifestValidatorFacadeTest
 */
class AppManifestValidatorFacadeTest extends Unit
{
    /**
     * @var \SprykerSdkTest\Acp\Tester
     */
    protected Tester $tester;

    /**
     * @return void
     */
    public function testValidateManifestReturnsSuccessfulResponseWhenFilesExistsAndContainValidData(): void
    {
        // Arrange
        $this->tester->haveValidManifestFile();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $this->assertCount(0, $validateResponseTransfer->getErrors(), sprintf(
            'Expected that no validation errors given but there are errors. Errors: "%s"',
            implode(', ', $this->tester->getMessagesFromValidateResponseTransfer($validateResponseTransfer)),
        ));
        $this->assertCount(0, $validateResponseTransfer->getErrors(), 'Expected that validation was successful but was not.');
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenFilesExistsButARequiredFieldIsMissing(): void
    {
        // Arrange
        $this->tester->haveManifestFileWithMissingRequiredFields();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $providerMissingErrorMessage = $validateResponseTransfer->getErrors()[0];
        $categoriesEmptyErrorMessage = $validateResponseTransfer->getErrors()[1];
        $pagesEmptyErrorMessage = $validateResponseTransfer->getErrors()[2];
        $developedByMissingErrorMessage = $validateResponseTransfer->getErrors()[3];

        $this->assertSame('Field "provider" must be present in the manifest file "en_US.json" but was not found.', $providerMissingErrorMessage->getMessage());
        $this->assertSame('Field "categories" cannot be empty in manifest file "en_US.json".', $categoriesEmptyErrorMessage->getMessage());
        $this->assertSame('Field "pages" cannot be empty in manifest file "en_US.json".', $pagesEmptyErrorMessage->getMessage());
        $this->assertSame('Field "developedBy" must be present in the manifest file "en_US.json" but was not found.', $developedByMissingErrorMessage->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenRequiredFieldIsMissingInPageBlock(): void
    {
        // Arrange
        $this->tester->haveManifestFileWithMissingRequiredFieldsInPageBlock();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $expectedErrorMessage = $validateResponseTransfer->getErrors()[0];
        $this->assertSame('Page block field "type" in page "Overview" must be present in the manifest file "en_US.json" but was not found.', $expectedErrorMessage->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenPageBlockTypeFieldHasInvalidType(): void
    {
        // Arrange
        $this->tester->haveManifestFileWithInvalidPageBlockType();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $expectedErrorMessage = $validateResponseTransfer->getErrors()[0];
        $this->assertSame('Page block type "invalid" not allowed in page "Overview" in the manifest file "en_US.json".', $expectedErrorMessage->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenBusinessModelHasInvalidName(): void
    {
        // Arrange
        $this->tester->haveManifestFileWithInvalidBusinessModel();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $expectedErrorMessage = $validateResponseTransfer->getErrors()[0];
        $this->assertSame('The business model with name "B2C-MARKETPLACE" is not allowed in the manifest file "en_US.json".', $expectedErrorMessage->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenFilesNotFound(): void
    {
        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $this->assertCount(1, $validateResponseTransfer->getErrors());

        $expectedErrorMessage = $validateResponseTransfer->getErrors()[0];
        $this->assertSame('No manifest files found.', $expectedErrorMessage->getMessage(), 'Manifest file "vfs://root/config/app/manifest/en_US.json" not found');
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenJSONIsInvalid(): void
    {
        // Arrange
        $this->tester->haveInvalidManifestFile();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $expectedErrorMessage = $validateResponseTransfer->getErrors()[0];
        $this->assertSame('Manifest file "vfs://root/config/app/manifest/en_US.json" contains invalid JSON. Error: "Syntax error".', $expectedErrorMessage->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateManifestReturnsFailedResponseWhenFilesExistsButDevelopedByFieldIsInvalid(): void
    {
        // Arrange
        $this->tester->haveManifestFileWithInvalidDevelopedByRequiredField();

        // Act
        $validateResponseTransfer = $this->tester->getFacade()->validateAppManifest(
            $this->tester->haveValidateRequest(),
        );

        // Assert
        $developedByMissingErrorMessage = $validateResponseTransfer->getErrors()[0];
        $developedByEmptyErrorMessage = $validateResponseTransfer->getErrors()[1];

        $this->assertSame('Field "developedBy" must be present in the manifest file "en_US.json" but was not found.', $developedByMissingErrorMessage->getMessage());
        $this->assertSame('Field "developedBy" cannot be empty in manifest file "de_DE.json".', $developedByEmptyErrorMessage->getMessage());
    }
}
