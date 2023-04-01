<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use LifeSpikes\MonorepoCLI\Functions;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PostCreateProjectCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('ls-scaffold-post-create');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        while (true) {
            /* Loop until we get a valid and confirmed package name */
            if (
                !($project = $this->askForPackageInfo($helper, $input, $output)) ||
                !$this->confirmProjectInfo($project, $helper, $input, $output)
            ) {
                continue;
            }

            $output->writeln('<comment>Preparing project...</comment>');
            $this->updateComposer($project);
            Functions::composer_cmd('workspace:create backend');

            if (!file_exists(Functions::cwd_path('.env'))) {
                Functions::shell_cmd('cp .env.example .env');
            }

            Functions::shell_cmd('php artisan key:generate');

            $output->writeln('<comment>Ready! Now go and build something beautiful!</comment>');
            $output->writeln('<comment>TIP: Change the default project readme to get started.</comment>');

            return 0;
        }
    }

    private function updateComposer(array $package)
    {
        $contents = json_decode(
            file_get_contents(($path = Functions::cwd_path('composer.json'))),
            true
        );

        $contents['name'] = "lifespikes/$package[name]";
        $contents['description'] = $package['description'];
        $contents['extra'] = [
            'monorepo-cli'  =>  [
                'ignorePackages'    =>  [
                    'monorepo-cli'
                ],
                'owner' =>  $package['name']
            ]
        ];

        file_put_contents($path, json_encode($contents, JSON_PRETTY_PRINT));
    }

    private function askForPackageInfo($helper, $input, $output): array|bool
    {
        $nameQuestion = new Question('<info>Project name in kebab-case: </info>');
        $descQuestion = new Question('<info>Short description: </info>');

        $project = [
            'name'          =>  $helper->ask($input, $output, $nameQuestion),
            'description'   =>  $helper->ask($input, $output, $descQuestion),
        ];

        if (preg_match_all('/[^a-z-]+/m', $project['name'])) {
            $output->writeln(
                '<error>'.
                $project['name'] .
                ' - Invalid package name, use only lowercase letters and dashes ' .
                '</error>'
            );

            return false;
        }

        return $project;
    }

    private function confirmProjectInfo($project, $helper, $input, $output): bool
    {
        $confirmation = new ConfirmationQuestion(
            '<info>Does this information look correct?</info>' . "\n\n" .
            json_encode($project, JSON_PRETTY_PRINT) . "\n\n" .
            '<info>(y/n)</info> <comment>[yes]</comment> ',
            true
        );

        return $helper->ask($input, $output, $confirmation);
    }
}
