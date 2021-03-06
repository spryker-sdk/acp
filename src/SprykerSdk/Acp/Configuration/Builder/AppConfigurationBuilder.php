<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Configuration\Builder;

use Transfer\AppConfigurationRequestTransfer;
use Transfer\AppConfigurationResponseTransfer;
use Transfer\MessageTransfer;

class AppConfigurationBuilder implements AppConfigurationBuilderInterface
{
   /**
    * @param \Transfer\AppConfigurationRequestTransfer $appConfigurationRequestTransfer
    *
    * @return \Transfer\AppConfigurationResponseTransfer
    */
    public function createConfiguration(AppConfigurationRequestTransfer $appConfigurationRequestTransfer): AppConfigurationResponseTransfer
    {
        $appConfigurationResponseTransfer = new AppConfigurationResponseTransfer();

        $result = $this->writeToFile(
            $appConfigurationRequestTransfer->getConfigurationFileOrFail(),
            [
                'properties' => $this->getFormattedProperties($appConfigurationRequestTransfer),
                'fieldsets' => $this->getFormattedFieldsets($appConfigurationRequestTransfer),
                'required' => $appConfigurationRequestTransfer->getRequired(),
            ],
        );

        if ($result) {
            $appConfigurationResponseTransfer->addMessage((new MessageTransfer())->setMessage(sprintf('Configuration file written to %s', $appConfigurationRequestTransfer->getConfigurationFile())));
        }

        return $appConfigurationResponseTransfer;
    }

    /**
     * @param \Transfer\AppConfigurationRequestTransfer $appConfigurationRequestTransfer
     *
     * @return array
     */
    protected function getFormattedProperties(AppConfigurationRequestTransfer $appConfigurationRequestTransfer): array
    {
        $properties = [];

        $requiredFields = $appConfigurationRequestTransfer->getRequired();

        foreach ($appConfigurationRequestTransfer->getProperties() as $name => $property) {
            $properties[$name] = ['type' => strtolower($property['type'])];
            $properties[$name]['widget']['id'] = strtolower($property['widget']);

            if ($property['widget'] === 'Text') {
                $properties[$name]['placeholder'] = $name;
                $properties[$name]['widget']['id'] = 'textline';
            }

            if (in_array($name, $requiredFields)) {
                $properties[$name]['isRequired'] = true;
            }

            if ($property['widget'] === 'Checkbox') {
                $properties[$name]['items']['type'] = strtolower($property['itemsType']);
                $properties[$name]['items']['widget']['id'] = strtolower($property['itemsType']);

                foreach ($property['items'] as $item) {
                    $properties[$name]['items']['oneOf'][] = [
                        'description' => $item,
                        'enum' => [$item],
                    ];
                }
            }

            if ($property['widget'] === 'Radio') {
                foreach ($property['items'] as $item) {
                    $properties[$name]['oneOf'][] = [
                        'description' => $item,
                        'enum' => [$item],
                    ];
                }
            }
        }

        return $properties;
    }

    /**
     * @param \Transfer\AppConfigurationRequestTransfer $appConfigurationRequestTransfer
     *
     * @return array
     */
    protected function getFormattedFieldsets(AppConfigurationRequestTransfer $appConfigurationRequestTransfer): array
    {
        $fieldsets = [];

        foreach ($appConfigurationRequestTransfer->getFieldsets() as $group => $fields) {
            $fieldsets[] = [
                'id' => $group,
                'title' => $group,
                'fields' => $fields,
            ];
        }

        return $fieldsets;
    }

    /**
     * @param string $targetFile
     * @param array $configurationFile
     *
     * @return bool
     */
    protected function writeToFile(string $targetFile, array $configurationFile): bool
    {
        $dirname = dirname($targetFile);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0770, true);
        }

        return (bool)file_put_contents($targetFile, json_encode($configurationFile, JSON_PRETTY_PRINT));
    }
}
