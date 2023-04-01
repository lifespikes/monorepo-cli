<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use RuntimeException;
use Composer\Command\BaseCommand;
use LifeSpikes\MonorepoCLI\Functions;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestPackagesCommand extends BaseCommand
{
    private OutputInterface $output;

    public function configure()
    {
        $this->setName('workspace:test')
            ->addArgument('package', InputArgument::OPTIONAL, 'Test a specific package', 'All')
            ->setDescription('Run test suites of one or all packages');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $package = $input->getArgument('package');

        $output->writeln('<info>Using testing engine: </info>' . Functions::config()->testEngine);

        if ($package === 'All') {
            $output->writeln('Running tests for all packages');

            foreach (Functions::package_paths(PackageType::COMPOSER) as $path) {
                $this->runPackageTests($path);
            }

            $output->writeln('All tests completed');

            return 0;
        }

        if (!($packagePath = realpath(__DIR__ . '/' . Functions::config()->packageDir . '/' . $package))) {
            $output->writeln('<error>Package not found</error>');
            return 1;
        }

        $this->runPackageTests($packagePath);

        return 0;
    }

    public function runPackageTests(string $path)
    {
        [$src, $test] = [$path . '/src', $path . '/tests'];
        $this->output->writeln('Running tests: ' . basename($path));

        if (!file_exists($src) || !file_exists($test)) {
            $this->output->writeln(
                'Unable to find "src" or "tests" directory, skipping.'
            );
            return;
        }

        if (($engine = Functions::config()->testEngine) === 'kahlan') {
            throw new RuntimeException('Kahlan was disabled as a testing engine');
        } elseif ($engine === 'pest') {
            Functions::pest_cmd(sprintf(
                '--test-directory "%s" --bootstrap "%s" --stop-on-failure',
                $path . '/tests',
                Functions::cwd_path('vendor/autoload.php')
            ));
        }
    }
}
