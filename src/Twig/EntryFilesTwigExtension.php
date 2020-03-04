<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Psr\Container\ContainerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;

final class EntryFilesTwigExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
            new TwigFunction('maybe_encore_entry_script_tags', [$this, 'maybeRenderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('maybe_encore_entry_link_tags', [$this, 'maybeRenderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function getWebpackJsFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getJavaScriptFiles($entryName);
    }

    public function getWebpackCssFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getCssFiles($entryName);
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
    }
    
    public function maybeRenderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        try {         
            return $this->getTagRenderer()
                ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
        }
        catch (EntrypointNotFoundException $e) {
            return '';
        }
    }

    public function maybeRenderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        try { 
            return $this->getTagRenderer()
                ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
        }
        catch (EntrypointNotFoundException $e) {
            return '';
        }
    }
    
    private function getEntrypointLookup(string $entrypointName): EntrypointLookupInterface
    {
        return $this->container->get('webpack_encore.entrypoint_lookup_collection')
            ->getEntrypointLookup($entrypointName);
    }

    private function getTagRenderer(): TagRenderer
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }
}
