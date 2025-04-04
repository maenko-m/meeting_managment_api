<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler Pass to identify and register custom processors.
 *  *
 * @internal
 */
final class CustomProcessorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * Process services tagged as 'swagger.processor'.
     *
     * @param ContainerBuilder $container The container builder
     */
    public function process(ContainerBuilder $container): void
    {
        // Find the OpenAPI generator service.
        $definition = $container->findDefinition('nelmio_api_doc.open_api.generator');

        foreach ($this->findAndSortTaggedServices('nelmio_api_doc.swagger.processor', $container) as $reference) {
            $tags = $container->findDefinition((string) $reference)->getTag('nelmio_api_doc.swagger.processor');

            // See if the processor has a 'before' attribute.
            $before = null;
            foreach ($tags as $tag) {
                if (isset($tag['before'])) {
                    $before = $tag['before'];
                }
            }

            $definition->addMethodCall('addNelmioProcessor', [$reference, $before]);
        }
    }
}
