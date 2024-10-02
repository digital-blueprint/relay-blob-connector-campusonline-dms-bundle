<?php

declare(strict_types=1);

namespace Dbp\Relay\BlobConnectorCampusonlineDmsBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationConfigDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ROLE_USER = 'ROLE_USER';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_blob_connector_campusonline_dms');
        $treeBuilder->getRootNode()
            ->append(AuthorizationConfigDefinition::create()
                ->addPolicy(self::ROLE_USER, 'false', 'Returns true if the current user is authorized to use the API')
                ->getNodeDefinition());

        return $treeBuilder;
    }
}
