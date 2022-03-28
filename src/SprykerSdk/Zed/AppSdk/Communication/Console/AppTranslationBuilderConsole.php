<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Zed\AppSdk\Communication\Console;

use Generated\Shared\Transfer\AppTranslationRequestTransfer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @method \SprykerSdk\Zed\AppSdk\Business\AppSdkFacadeInterface getFacade()
 */
class AppTranslationBuilderConsole extends AbstractConsole
{
    /**
     * @var array<string, string>
     */
    protected array $translations = [];

    /**
     * @var string
     */
    public const TRANSLATION_FILE = 'translation-file';

    /**
     * @var string
     */
    public const TRANSLATION_FILE_SHORT = 't';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:translation:create')
        ->setDescription('Create a translation file.')
        ->addOption(static::TRANSLATION_FILE, static::TRANSLATION_FILE_SHORT, InputOption::VALUE_REQUIRED, '', $this->getConfig()->getDefaultAppTranslationFile());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appTranslationRequestTransfer = new AppTranslationRequestTransfer();

        $appTranslationRequestTransfer->setTranslationFile($input->getOption(static::TRANSLATION_FILE));

        $this->getTranslationsInput($input, $output);

        $appTranslationRequestTransfer->setTranslations($this->translations);

        $appTranslationResponseTransfer = $this->getFacade()->appTranslationCreate($appTranslationRequestTransfer);

        if ($appTranslationResponseTransfer->getErrors()->count() === 0) {
            return static::CODE_SUCCESS;
        }

        // @codeCoverageIgnoreStart
        if ($output->isVerbose()) {
            foreach ($appTranslationResponseTransfer->getErrors() as $error) {
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
     *
     * @return void
     */
    protected function getTranslationsInput(InputInterface $input, OutputInterface $output): void
    {
        do {
            $translationKey = $this->askTextQuestion($input, $output, 'Please enter a translation key: ');

            $this->setLocale(
                $input,
                $output,
                $translationKey,
            );
        } while ($this->askForConfirmation($input, $output, 'Do you want to add more translations?') == 'Yes');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $translationKey
     *
     * @return void
     */
    protected function setLocale(InputInterface $input, OutputInterface $output, string $translationKey): void
    {
        do {
            $localeName = $this->askTextQuestion($input, $output, 'Please enter a locale name: ');
            $translationValue = $this->askTextQuestion($input, $output, 'Please enter a translation value: ');

            $this->translations[$translationKey][$localeName] = $translationValue;
        } while ($this->askForConfirmation($input, $output, 'Do you want to add more locales?') == 'Yes');
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
        return $this->getHelper('question')->ask($input, $output, new ChoiceQuestion($questionText, ['Yes', 'No'], 0));
    }
}
