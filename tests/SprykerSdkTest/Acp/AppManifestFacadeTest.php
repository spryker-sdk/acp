<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdkTest\Acp;

use Codeception\Test\Unit;
use Transfer\ManifestCriteriaTransfer;

/**
 * @group SprykerSdk
 * @group Acp
 * @group AppManifestFacadeTest
 */
class AppManifestFacadeTest extends Unit
{
    /**
     * @var string
     */
    public const INVALID_LOCALE = 'en_U';

    /**
     * @var \SprykerSdkTest\Acp\Tester
     */
    protected Tester $tester;

    /**
     * @return void
     */
    public function testAddManifestAddsANewManifestFile(): void
    {
        // Arrange
        $manifestRequestTransfer = $this->tester->haveManifestCreateRequest();

        // Act
        $manifestResponseTransfer = $this->tester->getFacade()->createAppManifest(
            $manifestRequestTransfer,
        );

        // Assert
        $this->tester->assertManifestResponseHasNoErrors($manifestResponseTransfer);
        $this->assertFileExists($manifestRequestTransfer->getManifestPath());
    }

    /**
     * @return void
     */
    public function testAddManifestWithInvalidLocaleReturnsErrorResponse(): void
    {
        // Arrange
        $manifestRequestTransfer = $this->tester->haveManifestCreateRequest();
        $manifestRequestTransfer->getManifestOrFail()->setLocaleName(static::INVALID_LOCALE);

        // Act
        $manifestResponseTransfer = $this->tester->getFacade()->createAppManifest(
            $manifestRequestTransfer,
        );

        // Assert
        $this->assertCount(
            1,
            $this->tester->getMessagesFromManifestResponseTransfer($manifestResponseTransfer),
            sprintf(
                'Expected to get exactly "1" error, got "%s". Either there is no error or you have more than expected',
                $manifestResponseTransfer->getErrors()->count(),
            ),
        );
    }

    /**
     * @return void
     */
    public function testGetManifestCollectionShouldReturnCollection(): void
    {
        // Arrange
        $manifestCriteriaTransfer = new ManifestCriteriaTransfer();
        $this->tester->haveValidTranslationWithManifestAndConfiguration();

        // Act
        $collection = $this->tester->getFacade()->getManifestCollection($manifestCriteriaTransfer);

        // Assert
        $this->assertNotEmpty($collection->getTranslation()->getTranslations());
    }
}
