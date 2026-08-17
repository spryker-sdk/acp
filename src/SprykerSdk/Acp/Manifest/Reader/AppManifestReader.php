<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Manifest\Reader;

use SprykerSdk\Acp\AcpConfig;
use SprykerSdk\Acp\Exception\InvalidConfigurationException;
use SprykerSdk\Acp\Exception\InvalidTranslationException;
use SprykerSdk\Acp\Validator\Finder\FinderInterface;
use Transfer\ManifestCollectionTransfer;
use Transfer\ManifestConfigurationTransfer;
use Transfer\ManifestCriteriaTransfer;
use Transfer\ManifestTransfer;
use Transfer\ManifestTranslationTransfer;

class AppManifestReader implements AppManifestReaderInterface
{
    /**
     * @var \SprykerSdk\Acp\AcpConfig
     */
    protected AcpConfig $acpConfig;

    /**
     * @var \SprykerSdk\Acp\Validator\Finder\FinderInterface
     */
    protected FinderInterface $finder;

    /**
     * @param \SprykerSdk\Acp\AcpConfig $acpConfig
     * @param \SprykerSdk\Acp\Validator\Finder\FinderInterface $finder
     */
    public function __construct(AcpConfig $acpConfig, FinderInterface $finder)
    {
        $this->acpConfig = $acpConfig;
        $this->finder = $finder;
    }

    /**
     * @param \Transfer\ManifestCriteriaTransfer $manifestCriteriaTransfer
     *
     * @return \Transfer\ManifestCollectionTransfer
     */
    public function getManifestCollection(ManifestCriteriaTransfer $manifestCriteriaTransfer): ManifestCollectionTransfer
    {
        $manifestCollectionTransfer = new ManifestCollectionTransfer();

        $this->extendCollectionByManifest($manifestCollectionTransfer, $manifestCriteriaTransfer);
        $this->extendCollectionByConfiguration($manifestCollectionTransfer, $manifestCriteriaTransfer);
        $this->extendCollectionByTranslation($manifestCollectionTransfer, $manifestCriteriaTransfer);

        return $manifestCollectionTransfer;
    }

    /**
     * @param \Transfer\ManifestCollectionTransfer $manifestCollectionTransfer
     * @param \Transfer\ManifestCriteriaTransfer $manifestCriteriaTransfer
     *
     * @return void
     */
    protected function extendCollectionByManifest(
        ManifestCollectionTransfer $manifestCollectionTransfer,
        ManifestCriteriaTransfer $manifestCriteriaTransfer,
    ): void {
        $manifestFolder = $this->acpConfig->getDefaultManifestFolder();

        if (
            $manifestCriteriaTransfer->getManifestConditions() !== null &&
            $manifestCriteriaTransfer->getManifestConditions()->getManifestFolder() !== null
        ) {
            $manifestFolder = $manifestCriteriaTransfer->getManifestConditions()->getManifestFolder();
        }

        if (!$this->finder->hasFiles($manifestFolder)) {
            return;
        }

        foreach ($this->finder->getFiles($manifestFolder) as $manifestFile) {
            $manifestTransfer = (new ManifestTransfer())
                ->setLocaleName($manifestFile->getBasename('.json'));

            $manifestCollectionTransfer->addManifest($manifestTransfer);
        }
    }

    /**
     * @param \Transfer\ManifestCollectionTransfer $manifestCollectionTransfer
     * @param \Transfer\ManifestCriteriaTransfer $manifestCriteriaTransfer
     *
     * @throws \SprykerSdk\Acp\Exception\InvalidConfigurationException
     *
     * @return void
     */
    protected function extendCollectionByConfiguration(
        ManifestCollectionTransfer $manifestCollectionTransfer,
        ManifestCriteriaTransfer $manifestCriteriaTransfer,
    ): void {
        $configurationFilePath = $this->acpConfig->getDefaultConfigurationFile();

        if (
            $manifestCriteriaTransfer->getManifestConditions() !== null &&
            $manifestCriteriaTransfer->getManifestConditions()->getConfigurationFilePath() !== null
        ) {
            $configurationFilePath = $manifestCriteriaTransfer->getManifestConditions()->getConfigurationFilePath();
        }

        if (!$this->finder->hasFile($configurationFilePath)) {
            return;
        }

        $configurationFile = $this->finder->getFile($configurationFilePath);

        $manifestConfigurationTransfer = (new ManifestConfigurationTransfer())
            ->setConfiguration(json_decode((string)file_get_contents($configurationFile->getPathname()), true));

        if (json_last_error()) {
            throw new InvalidConfigurationException(sprintf(
                'There was an error while decoding the configuration file "%s". Error: %s',
                $configurationFile->getPathname(),
                json_last_error_msg(),
            ));
        }

        $manifestCollectionTransfer->setConfiguration($manifestConfigurationTransfer);
    }

    /**
     * @param \Transfer\ManifestCollectionTransfer $manifestCollectionTransfer
     * @param \Transfer\ManifestCriteriaTransfer $manifestCriteriaTransfer
     *
     * @throws \SprykerSdk\Acp\Exception\InvalidTranslationException
     *
     * @return void
     */
    protected function extendCollectionByTranslation(
        ManifestCollectionTransfer $manifestCollectionTransfer,
        ManifestCriteriaTransfer $manifestCriteriaTransfer,
    ): void {
        $translationFilePath = $this->acpConfig->getDefaultTranslationFile();

        if (
            $manifestCriteriaTransfer->getManifestConditions() !== null &&
            $manifestCriteriaTransfer->getManifestConditions()->getTranslationFilePath() !== null
        ) {
            $translationFilePath = $manifestCriteriaTransfer->getManifestConditions()->getTranslationFilePath();
        }

        if (!$this->finder->hasFile($translationFilePath)) {
            return;
        }

        /** @var \Symfony\Component\Finder\SplFileInfo $translationFile */
        $translationFile = $this->finder->getFile($translationFilePath);

        $manifestTranslationFile = (new ManifestTranslationTransfer())
            ->setTranslations(json_decode((string)file_get_contents($translationFile->getPathname()), true));

        if (json_last_error()) {
            throw new InvalidTranslationException(sprintf(
                'There was an error while decoding the translation file "%s". Error: %s',
                $translationFile->getPathname(),
                json_last_error_msg(),
            ));
        }

        $manifestCollectionTransfer->setTranslation($manifestTranslationFile);
    }
}
