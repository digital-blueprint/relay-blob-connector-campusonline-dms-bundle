<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use Dbp\Relay\BlobBundle\DbpRelayBlobBundle;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\DbpRelayBlobConnectorCampusonlineDmsBundle;
use Dbp\Relay\BlobLibrary\Api\BlobApi;
use Dbp\Relay\CoreBundle\TestUtils\CoreTestKernelTrait;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use CoreTestKernelTrait;

    public const TEST_BUCKET_ID = 'document-bucket';

    protected function registerAdditionalBundles(): iterable
    {
        yield new DoctrineBundle();
        yield new DoctrineMigrationsBundle();
        yield new DbpRelayBlobBundle();
        yield new DbpRelayBlobConnectorCampusonlineDmsBundle();
    }

    protected function configureAdditionalContainer(ContainerConfigurator $container): void
    {
        $testConfig = [
            'authorization' => [
                'roles' => [
                    'ROLE_USER' => 'user.get("MAY_USE_CO_DMS_API")',
                ],
            ],
            'blob_type' => 'my_document_version',
        ];
        $testConfig = array_merge($testConfig, BlobApi::getCustomModeConfig(self::TEST_BUCKET_ID));

        $container->extension('dbp_relay_blob_connector_campusonline_dms', $testConfig);

        $container->extension('dbp_relay_blob', DocumentServiceTest::getBLobTestConfig());
    }
}
