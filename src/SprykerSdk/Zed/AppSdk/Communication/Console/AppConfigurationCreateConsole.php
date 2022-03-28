<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Zed\AppSdk\Communication\Console;

use Generated\Shared\Transfer\AppConfigurationRequestTransfer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @method \SprykerSdk\Zed\AppSdk\Business\AppSdkFacadeInterface getFacade()
 */
class AppConfigurationCreateConsole extends AbstractConsole
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $requiredFields = [];

    /**
     * @var array
     */
    protected $fieldsets = [];

    /**
     * @var string
     */
    public const CONFIGURATION_FILE = 'configuration-file';

    /**
     * @var string
     */
    public const CONFIGURATION_FILE_SHORT = 'c';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:configuration:create')
            ->setDescription('Create configuration file.')
            ->addOption(static::CONFIGURATION_FILE, static::CONFIGURATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultConfigurationFile());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appConfigurationRequestTransfer = new AppConfigurationRequestTransfer();

        $appConfigurationRequestTransfer->setConfigurationFile($input->getOption(static::CONFIGURATION_FILE));

        $output->writeln([
            'Welcome to the App configuration builder.',
            '',
            'For each configuration you will be prompted to enter details.',
            'When the process is done a configuration file will be created in: ' . $appConfigurationRequestTransfer->getConfigurationFile(),
            'When you have a typo or anything else you\'d like to change you can do that manually in the created file after this process is finished.',
            '',
            'Only use translation keys for names. These fields need to be displayed in different languages.',
            '',
        ]);

        $this->getPropertiesInput($input, $output);

        $this->getFieldsetInput($input, $output);

        $appConfigurationRequestTransfer->setProperties($this->properties);

        $appConfigurationRequestTransfer->setRequired($this->requiredFields);

        $appConfigurationRequestTransfer->setFieldsets($this->fieldsets);

        $appConfigurationResponseTransfer = $this->getFacade()->appConfigurationCreate($appConfigurationRequestTransfer);

        if ($appConfigurationResponseTransfer->getErrors()->count() === 0) {
            return static::CODE_SUCCESS;
        }

        // @codeCoverageIgnoreStart
        if ($output->isVerbose()) {
            foreach ($appConfigurationResponseTransfer->getErrors() as $error) {
                $output->writeln($error->getMessageOrFail());
            }
        }

        return static::CODE_ERROR;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $questionText
     *
     * @return string
     */
    protected function askTextQuestion(InputInterface $input, OutputInterface $output, string $questionText): string
    {
        return $this->getHelper('question')->ask($input, $output, new Question($questionText));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $questionText
     * @param array $questionOptions
     * @param string|int $defaultSelected
     *
     * @return string
     */
    protected function askChoiceQuestion(
        InputInterface $input,
        OutputInterface $output,
        string $questionText,
        array $questionOptions,
        $defaultSelected
    ): string {
        return $this->getHelper('question')->ask($input, $output, new ChoiceQuestion($questionText, $questionOptions, $defaultSelected));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $questionText
     *
     * @return string
     */
    protected function askForConfirmation(
        InputInterface $input,
        OutputInterface $output,
        string $questionText
    ): string {
        return $this->getHelper('question')->ask($input, $output, new ChoiceQuestion($questionText, [1 => 'Yes', 2 => 'No'], 1));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $questionText
     * @param array $questionOptions
     * @param string|int $defaultSelected
     *
     * @return array
     */
    protected function askMultipleChoiceQuestion(
        InputInterface $input,
        OutputInterface $output,
        string $questionText,
        array $questionOptions,
        $defaultSelected
    ): array {
        $question = new ChoiceQuestion($questionText, $questionOptions, $defaultSelected);
        $question->setMultiselect(true);

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function getPropertiesInput(InputInterface $input, OutputInterface $output): void
    {
        do {
            $propertyName = $this->getPropertyName($input, $output);

            $this->setIsRequired(
                $propertyName,
                $this->askForConfirmation($input, $output, 'Is this a required field?'),
            );

            $this->setWidget(
                $input,
                $output,
                $propertyName,
                $this->askChoiceQuestion($input, $output, 'Please select a widget: ', [1 => 'Text', 2 => 'Radio', 3 => 'Checkbox'], 1),
            );
        } while ($this->askForConfirmation($input, $output, 'Do you want to add more configurations?') == 'Yes');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return string
     */
    protected function getPropertyName(InputInterface $input, OutputInterface $output): string
    {
        $propertyName = $this->askTextQuestion($input, $output, 'Please enter a name: ');

        if (array_key_exists($propertyName, $this->properties) && ($this->askForConfirmation($input, $output, 'You have already defined this configuration do you want to override it?') == 'No')) {
            $propertyName = $this->getPropertyName($input, $output);
        }

        return $propertyName;
    }

    /**
     * @param string $propertyName
     * @param string $required
     *
     * @return void
     */
    protected function setIsRequired(string $propertyName, string $required): void
    {
        if ($required == 'Yes') {
            $this->requiredFields[] = $propertyName;
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     * @param string $widget
     *
     * @return void
     */
    protected function setWidget(InputInterface $input, OutputInterface $output, string $propertyName, string $widget): void
    {
        $this->properties[$propertyName]['widget'] = $widget;
        switch ($widget) {
            case 'Checkbox':
                $this->getTypeForCheckboxWidget($input, $output, $propertyName);

                break;
            case 'Radio':
                $this->getTypeForRadioWidget($input, $output, $propertyName);

                break;
            case 'Text':
                $this->getTypeForTextWidget($input, $output, $propertyName);

                break;
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     *
     * @return void
     */
    protected function getTypeForCheckboxWidget(InputInterface $input, OutputInterface $output, string $propertyName): void
    {
        $this->setWidgetType(
            $propertyName,
            $this->askChoiceQuestion($input, $output, 'Please select a type: ', [1 => 'Array'], 1),
        );

        $this->setItemsType(
            $propertyName,
            $this->getWidgetTypeOption($input, $output),
        );

        $this->getWidgetOptions($input, $output, $propertyName);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     *
     * @return void
     */
    protected function getTypeForRadioWidget(InputInterface $input, OutputInterface $output, string $propertyName): void
    {
        $this->setWidgetType(
            $propertyName,
            $this->getWidgetTypeOption($input, $output),
        );

        $this->getWidgetOptions($input, $output, $propertyName);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     *
     * @return void
     */
    protected function getTypeForTextWidget(InputInterface $input, OutputInterface $output, string $propertyName): void
    {
        $this->setWidgetType(
            $propertyName,
            $this->getWidgetTypeOption($input, $output),
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return string
     */
    protected function getWidgetTypeOption(InputInterface $input, OutputInterface $output): string
    {
        return $this->askChoiceQuestion($input, $output, 'Please select a type: ', [1 => 'String', 2 => 'Int'], 1);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     *
     * @return void
     */
    protected function getWidgetOptions(InputInterface $input, OutputInterface $output, string $propertyName): void
    {
        $output->writeln(['', 'You selected a widget that requires options. For each option you will be prompted to enter details.', '']);
        do {
            $this->setWidgetOptions(
                $input,
                $output,
                $propertyName,
                $this->askTextQuestion($input, $output, 'Please enter an option: '),
            );
        } while ($this->askForConfirmation($input, $output, 'Do you want to add more options?') == 'Yes');
    }

    /**
     * @param string $propertyName
     * @param string $type
     *
     * @return void
     */
    protected function setWidgetType(string $propertyName, string $type): void
    {
        $this->properties[$propertyName]['type'] = $type;
    }

    /**
     * @param string $propertyName
     * @param string $type
     *
     * @return void
     */
    protected function setItemsType(string $propertyName, string $type): void
    {
        $this->properties[$propertyName]['itemsType'] = $type;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     * @param string $item
     *
     * @return void
     */
    protected function setWidgetOptions(InputInterface $input, OutputInterface $output, string $propertyName, string $item): void
    {
        if ($this->checkWidgetOptionValueType($input, $output, $propertyName, $item) === true) {
            if (!isset($this->properties[$propertyName]['items']) || !in_array($item, $this->properties[$propertyName]['items'])) {
                $this->properties[$propertyName]['items'][] = $item;
            }
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $propertyName
     * @param string $item
     *
     * @return bool
     */
    protected function checkWidgetOptionValueType(InputInterface $input, OutputInterface $output, string $propertyName, string $item): bool
    {
        if (($this->properties[$propertyName]['type'] === 'Int' || (isset($this->properties[$propertyName]['itemsType']) && $this->properties[$propertyName]['itemsType'] === 'Int')) && !is_numeric($item)) {
            if ($this->askForConfirmation($input, $output, 'You entered string value while int expected. Do you want to switch type?') == 'Yes') {
                $this->setItemsType(
                    $propertyName,
                    $this->getWidgetTypeOption($input, $output),
                );
            }

            return false;
        }

        return true;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function getFieldsetInput(InputInterface $input, OutputInterface $output): void
    {
        if ($this->askForConfirmation($input, $output, 'Do you want to group the configurations?') == 'Yes') {
            $fieldsetOptions = array_keys($this->properties);

            do {
                $fieldsetOptions = array_diff($fieldsetOptions, $this->setGroupFields(
                    $this->askTextQuestion($input, $output, 'Please enter a group name: '),
                    $this->askMultipleChoiceQuestion($input, $output, 'Please select all fields that should be in this group', $fieldsetOptions, ''),
                ));
            } while ($fieldsetOptions && $this->askForConfirmation($input, $output, 'Do you want to add more group configurations?') == 'Yes');
        }
    }

    /**
     * @param string $groupName
     * @param array $fields
     *
     * @return array
     */
    protected function setGroupFields(string $groupName, array $fields): array
    {
        return $this->fieldsets[$groupName] = $fields;
    }
}
