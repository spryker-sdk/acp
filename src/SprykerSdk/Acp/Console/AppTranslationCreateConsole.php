<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Acp\Console;

use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Transfer\AppTranslationRequestTransfer;
use Transfer\AppTranslationResponseTransfer;
use Transfer\ManifestCollectionTransfer;
use Transfer\ManifestConditionsTransfer;
use Transfer\ManifestCriteriaTransfer;
use const SIGINT;

/**
 * @method \SprykerSdk\Acp\AcpFacadeInterface getFacade()
 */
class AppTranslationCreateConsole extends AbstractConsole implements SignalableCommandInterface
{
    protected array $translations = [];

    /**
     * @var string
     */
    public const TRANSLATION_FILE = 'translation-file';

    /**
     * @var string
     */
    public const MANIFEST_FOLDER = 'manifest-folder';

    /**
     * @var string
     */
    public const CONFIGURATION_FILE = 'configuration-file';

    /**
     * @var string
     */
    public const TRANSLATION_FILE_SHORT = 't';

    /**
     * @var string
     */
    public const MANIFEST_FOLDER_SHORT = 'm';

    /**
     * @var string
     */
    public const CONFIGURATION_FILE_SHORT = 'c';

    /**
     * @var string
     */
    protected const CHOICE_NEW_LOCALE = 'Select this to add a new locale';

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected InputInterface $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:translation:create')
            ->setDescription('Create a translation file.')
            ->addOption(static::TRANSLATION_FILE, static::TRANSLATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultTranslationFile())
            ->addOption(static::MANIFEST_FOLDER, static::MANIFEST_FOLDER_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultManifestFolder())
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
        $this->input = $input;
        $this->output = $output;

        $this->printWelcomeMessage($input, $output);

        $manifestCollectionTransfer = $this->getManifestCollection(
            $input->getOption(static::MANIFEST_FOLDER),
            $input->getOption(static::CONFIGURATION_FILE),
            $input->getOption(static::TRANSLATION_FILE),
        );

        $existingKeysToTranslate = $this->getFacade()->getExistingKeysToTranslate($manifestCollectionTransfer);
        $existingTranslations = $this->getExistingTranslations($manifestCollectionTransfer);
        $existingLocales = $this->getExistingLocales($manifestCollectionTransfer);

        $localeName = '';

        do {
            if (count($existingLocales) > 0) {
                $localeName = $this->chooseLocale($input, $output, $existingLocales);
            }

            if (!$localeName || $localeName === static::CHOICE_NEW_LOCALE) {
                $localeName = $this->createNewLocale($input, $output);
            }

            $this->getTranslationsInput($input, $output, $localeName, $existingKeysToTranslate, $existingTranslations);
        } while ($this->askForConfirmation($input, $output, 'Would you like to add translations for another locale?') === 'Yes');

        $appTranslationResponseTransfer = $this->saveTranslations($input, $this->translations);

        if ($appTranslationResponseTransfer->getErrors()->count() === 0) {
            $this->printMessages($output, $appTranslationResponseTransfer->getMessages());

            return static::CODE_SUCCESS;
        }

        $this->printMessages($output, $appTranslationResponseTransfer->getErrors());

        return static::CODE_ERROR;
    }

    /**
     * @return array
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT];
    }

    /**
     * @param int $signal
     *
     * @return void
     */
    public function handleSignal(int $signal): void
    {
        // @codeCoverageIgnoreStart
        exit($this->doHandleSignal($this->input, $this->output, $signal, $this->translations));
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int $signal
     * @param array $translations
     *
     * @return int
     */
    public function doHandleSignal(
        InputInterface $input,
        OutputInterface $output,
        int $signal,
        array $translations,
    ): int {
        if (
            $signal === SIGINT
            && $input !== null
            && $output !== null
            && $translations
        ) {
            $appTranslationResponseTransfer = $this->saveTranslations($input, $translations);
            if ($appTranslationResponseTransfer->getErrors()->count() === 0) {
                $this->printMessages($output, $appTranslationResponseTransfer->getMessages());
            }

            $this->printMessages($output, $appTranslationResponseTransfer->getErrors());
        }

        return static::CODE_SUCCESS;
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
        return $this->getQuestionHelper()->ask($input, $output, new Question($questionText)) ?: '';
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $localeName
     * @param array<string> $existingKeysToTranslate
     * @param array<string, array<string, string>> $existingTranslations
     *
     * @return void
     */
    protected function getTranslationsInput(
        InputInterface $input,
        OutputInterface $output,
        string $localeName,
        array $existingKeysToTranslate,
        array $existingTranslations,
    ): void {
        $this->getTranslationValuesForKeysThatAreNotTranslated($input, $output, $localeName, $existingKeysToTranslate, $existingTranslations);

        while ($this->askForConfirmation($input, $output, 'Would you like to add new translations?') === 'Yes') {
            $this->printHelper($output, $localeName);

            $keyToTranslate = $this->askTextQuestion($input, $output, 'Please enter a translation key: ');
            if (!$keyToTranslate) {
                $this->writeLeftProcessMessage();

                return;
            }
            $translationValue = $this->askTextQuestion($input, $output, 'Please enter a translation value: ');
            if (!$translationValue) {
                $this->writeLeftProcessMessage();

                return;
            }
            $this->translations[$keyToTranslate][$localeName] = $translationValue;
        }
    }

    /**
     * When there are translations missing for translation keys this prints an info text and asks for the values of the
     * translation keys one by one until the user aborts this step.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $localeName
     * @param array $existingKeysToTranslate
     * @param array $existingTranslations
     *
     * @return void
     */
    protected function getTranslationValuesForKeysThatAreNotTranslated(
        InputInterface $input,
        OutputInterface $output,
        string $localeName,
        array $existingKeysToTranslate,
        array $existingTranslations,
    ): void {
        $translationKeysWithoutValues = $this->getTranslationKeysWithoutValue($existingKeysToTranslate, $existingTranslations, $localeName);

        if (!count($translationKeysWithoutValues)) {
            $output->writeln(sprintf('<info>We haven\'t found any missing translations for the locale</info> <comment>%s</comment>', $localeName));

            return;
        }

        $output->writeln(sprintf('<info>We found missing translations for the locale</info> <comment>%s</comment>', $localeName));
        $this->printHelper($output, $localeName);

        foreach ($translationKeysWithoutValues as $keyToTranslate) {
            $translationValue = $this->askTextQuestion(
                $input,
                $output,
                sprintf('Please enter a translation for <comment>%s</comment>: ', $keyToTranslate),
            );

            if (!$translationValue) {
                $this->writeLeftProcessMessage();

                return;
            }

            $this->translations[$keyToTranslate][$localeName] = $translationValue;
        }

        $output->writeln(sprintf('<info>All missing translations for the locale <comment>%s</comment> added.</info>', $localeName));
    }

    /**
     * Filters out already existing translations and returns a list of translation keys that do not have translation value.
     *
     * Returns an empty array if all translation keys have values.
     *
     * @param array $existingKeysToTranslate
     * @param array $existingTranslations
     * @param string $localeName
     *
     * @return array
     */
    protected function getTranslationKeysWithoutValue(array $existingKeysToTranslate, array $existingTranslations, string $localeName): array
    {
        $translationKeysWithoutValue = [];

        foreach ($existingKeysToTranslate as $keyToTranslate) {
            if (isset($existingTranslations[$keyToTranslate][$localeName])) {
                $this->translations[$keyToTranslate][$localeName] = $existingTranslations[$keyToTranslate][$localeName];

                continue;
            }

            $translationKeysWithoutValue[] = $keyToTranslate;
        }

        return $translationKeysWithoutValue;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $localeName
     *
     * @return void
     */
    protected function printHelper(OutputInterface $output, string $localeName): void
    {
        $output->writeln(sprintf('<info>The following inputs will be used for your selected locale</info> <comment>%s</comment>', $localeName));
        $output->writeln('');
        $output->writeln('<info>When you like to leave the process hit the <comment>Enter</comment> key until you see "<comment>Left the process, will continue with next steps</comment>". Already entered data will be automatically saved.</info>');
        $output->writeln('');
    }

    /**
     * This question is asked when locales can be extracted from existsing manifest files.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array<string> $existingLocales
     *
     * @return string
     */
    protected function chooseLocale(InputInterface $input, OutputInterface $output, array $existingLocales): string
    {
        array_unshift($existingLocales, static::CHOICE_NEW_LOCALE);

        return $this->getQuestionHelper()->ask(
            $input,
            $output,
            new ChoiceQuestion(
                'We found the following locales, please select one you\'d like to define translations for:',
                $existingLocales,
            ),
        );
    }

    /**
     * This question is asked when no locales could be extracted from manifest files.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return string
     */
    protected function createNewLocale(InputInterface $input, OutputInterface $output): string
    {
        return $this->askTextQuestion(
            $input,
            $output,
            'Please enter a locale name you would like to define translations for: ',
        );
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
        string $questionText,
    ): string {
        return $this->getQuestionHelper()->ask($input, $output, new ChoiceQuestion($questionText, [1 => 'Yes', 2 => 'No'], 1));
    }

    /**
     * @return void
     */
    protected function writeLeftProcessMessage(): void
    {
        $this->output->writeln('<comment>Left the process, will continue with next steps.</comment>');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function printWelcomeMessage(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Welcome to the App translation builder.',
            '',
            'Translations will be used to show translated text for displaying informations in the App Store Catalog and for displaying a translated form on the configuration page of the app.',
            '',
            'For each translation you will be prompted to enter details.',
            '',
            'When the process is done a translation file will be created in: ' . $input->getOption(static::TRANSLATION_FILE),
            'When you have a typo or anything else you\'d like to change you can do that manually in the created file after this process is finished.',
            '',
        ]);
    }

    /**
     * @param string $manifestFolder
     * @param string $configurationFilePath
     * @param string $translationFilePath
     *
     * @return \Transfer\ManifestCollectionTransfer
     */
    protected function getManifestCollection(
        string $manifestFolder,
        string $configurationFilePath,
        string $translationFilePath,
    ): ManifestCollectionTransfer {
        $manifestConditionsTransfer = (new ManifestConditionsTransfer())
            ->setManifestFolder($manifestFolder)
            ->setConfigurationFilePath($configurationFilePath)
            ->setTranslationFilePath($translationFilePath);

        $manifestCriteriaTransfer = (new ManifestCriteriaTransfer())
            ->setManifestConditions($manifestConditionsTransfer);

        return $this->getFacade()->getManifestCollection($manifestCriteriaTransfer);
    }

    /**
     * @param \Transfer\ManifestCollectionTransfer $manifestCollectionTransfer
     *
     * @return array<string>
     */
    protected function getExistingLocales(ManifestCollectionTransfer $manifestCollectionTransfer): array
    {
        $locales = [];

        foreach ($manifestCollectionTransfer->getManifests() as $manifestTransfer) {
            $locales[] = $manifestTransfer->getLocaleNameOrFail();
        }

        return $locales;
    }

    /**
     * @param \Transfer\ManifestCollectionTransfer $manifestCollectionTransfer
     *
     * @return array<string, array<string, string>>
     */
    protected function getExistingTranslations(ManifestCollectionTransfer $manifestCollectionTransfer): array
    {
        $translations = $manifestCollectionTransfer->getTranslation();

        return $translations ? $translations->getTranslations() : [];
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param array $translations
     *
     * @return \Transfer\AppTranslationResponseTransfer
     */
    protected function saveTranslations(InputInterface $input, array $translations): AppTranslationResponseTransfer
    {
        $appTranslationRequestTransfer = (new AppTranslationRequestTransfer())
            ->setTranslationFile($input->getOption(static::TRANSLATION_FILE))
            ->setTranslations($translations);

        return $this->getFacade()->createAppTranslation($appTranslationRequestTransfer);
    }
}
