<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Translation\Builder;

use Transfer\AppTranslationRequestTransfer;
use Transfer\AppTranslationResponseTransfer;

class AppTranslationBuilder implements AppTranslationBuilderInterface
{
    /**
     * @param \Transfer\AppTranslationRequestTransfer $appTranslationRequestTransfer
     *
     * @return \Transfer\AppTranslationResponseTransfer
     */
    public function createTranslation(AppTranslationRequestTransfer $appTranslationRequestTransfer): AppTranslationResponseTransfer
    {
        $appTranslationResponseTransfer = new AppTranslationResponseTransfer();

        $this->writeToFile(
            $appTranslationRequestTransfer->getTranslationFileOrFail(),
            $appTranslationRequestTransfer->getTranslations(),
        );

        return $appTranslationResponseTransfer;
    }

    /**
     * @param string $targetFile
     * @param array $translations
     *
     * @return bool
     */
    protected function writeToFile(string $targetFile, array $translations): bool
    {
        $dirname = dirname($targetFile);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0770, true);
        }

        return (bool)file_put_contents($targetFile, json_encode($translations, JSON_PRETTY_PRINT));
    }
}
