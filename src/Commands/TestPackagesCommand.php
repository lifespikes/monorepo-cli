<?php

namespace LifeSpikes\MonorepoCLI\Commands;

use Composer\Command\BaseCommand;
use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function LifeSpikes\MonorepoCLI\pest_cmd;
use function LifeSpikes\MonorepoCLI\cwd_path;
use function LifeSpikes\MonorepoCLI\kahlan_cmd;
use function LifeSpikes\MonorepoCLI\get_packages;
use function LifeSpikes\MonorepoCLI\package_list;
use function LifeSpikes\MonorepoCLI\package_paths;
use function \LifeSpikes\MonorepoCLI\config;

class TestPackagesCommand extends BaseCommand
{
    private OutputInterface $output;

    public function configure()
    {
        $this->setName('workspace:test')
            ->addArgument('package', InputArgument::OPTIONAL, 'Test a specific package', 'All')
            ->setDescription('Run test suites of one or all packages with Kahlan');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $package = $input->getArgument('package');

        $output->writeln('<info>Using testing engine: </info>' . config()->testEngine);

        if ($package === 'All') {
            $output->writeln('Running tests for all packages');

            foreach (package_paths(PackageType::COMPOSER) as $path) {
                $this->runPackageTests($path);
            }

            $output->writeln('All tests completed');

            return 0;
        }

        if (!($packagePath = realpath(__DIR__ . '/' . config()->packageDir . '/' . $package))) {
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

        if (($engine = config()->testEngine) === 'kahlan') {
            kahlan_cmd(sprintf(
                '--src="%s" --spec="%s" --grep="*Test.php" --grep="*test.php" --ff=2 --cc',
                $path . '/src',
                $path . '/tests'
            ));
        } elseif ($engine === 'pest') {
            pest_cmd(sprintf(
                '--test-directory "%s" --bootstrap "%s" --stop-on-failure',
                $path . '/tests',
                cwd_path('vendor/autoload.php')
            ));
        }
    }
}
