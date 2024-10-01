<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Tests;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Dbp\Relay\BlobBundle\DbpRelayBlobBundle;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\DbpRelayBlobConnectorCampusonlineDmsBundle;
use Dbp\Relay\CoreBundle\DbpRelayCoreBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Nelmio\CorsBundle\NelmioCorsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new TwigBundle();
        yield new NelmioCorsBundle();
        yield new MonologBundle();
        yield new DoctrineBundle();
        yield new DoctrineMigrationsBundle();
        yield new ApiPlatformBundle();
        yield new DbpRelayBlobBundle();
        yield new DbpRelayBlobConnectorCampusonlineDmsBundle();
        yield new DbpRelayCoreBundle();
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        $routes->import('@DbpRelayCoreBundle/Resources/config/routing.yaml');
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader)
    {
        $container->import('@DbpRelayCoreBundle/Resources/config/services_test.yaml');
        $container->extension('framework', [
            'test' => true,
            'secret' => '',
            'annotations' => false,
        ]);

        $container->extension('dbp_relay_blob_connector_campusonline_dms', []);

        $container->extension('dbp_relay_blob', [
            'database_url' => 'sqlite:///:memory:',
            'reporting_interval' => '0 9 * * MON',
            'cleanup_interval' => '0 * * * *',
            'file_integrity_checks' => false,
            'additional_auth' => false,
            'integrity_check_interval' => '0 0 1 * *',
            'bucket_size_check_interval' => '0 2 * * 1',
            'quota_warning_interval' => '0 6 * * *',
            'buckets' => [
                'test-bucket' => [
                    'service' => 'Dbp\Relay\BlobBundle\Tests\DummyFileSystemService',
                    'internal_bucket_id' => '018e0ed8-e6d7-794f-8f60-42efe27ef49e',
                    'bucket_id' => 'test-bucket',
                    'key' => '08d848fd868d83646778b87dd0695b10f59c78e23b286e9884504d1bb43cce93',
                    'quota' => 500, // in MB
                    'output_validation' => true,
                    'notify_when_quota_over' => 70, // in percent of quota
                    'report_when_expiry_in' => 'P62D', // in Days, 62 = two 31 day months
                    'bucket_owner' => 'manuel.kocher@tugraz.at',
                    'link_expire_time' => 'PT1M',
                    'reporting' => [
                        'dsn' => 'smtp:localhost',
                        'from' => 'noreply@tugraz.at',
                        'to' => 'tamara.steinwender@tugraz.at',
                        'subject' => 'Blob file deletion reporting',
                        'html_template' => 'emails/reporting.html.twig',
                    ],
                    'integrity' => [
                        'dsn' => 'smtp:localhost',
                        'from' => 'noreply@tugraz.at',
                        'to' => 'manuel.kocher@tugraz.at',
                        'subject' => 'Blob file integrity check report',
                        'html_template' => 'emails/integrity.html.twig',
                    ],
                ],
            ],
        ]);
    }
}
