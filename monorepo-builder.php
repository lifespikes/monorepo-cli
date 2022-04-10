<?php

declare(strict_types=1);

use LifeSpikes\MonorepoCLI\Enums\PackageType;
use Support\Workers\CopyReleaseFileWorker;
use Support\Workers\NpmVersionMatchWorker;
use Support\Workers\UpdateRootVersionWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use function LifeSpikes\MonorepoCLI\package_list;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    /* Include our package directory */
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        'packages'
    ]);

    $parameters->set(Option::PACKAGE_DIRECTORIES_EXCLUDES, [
        ...package_list(PackageType::NODE),
        'monorepo-cli'
    ]);

    /* Release workers */

    $services->set(CopyReleaseFileWorker::class);
    $services->set(UpdateReplaceReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(AddTagToChangelogReleaseWorker::class);
    $services->set(NpmVersionMatchWorker::class);
    $services->set(UpdateRootVersionWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);
};
