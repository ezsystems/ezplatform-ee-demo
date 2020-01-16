<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Twig;

use App\QueryType\ContentSiblingQueryType;
use App\ValueObject\ContentSiblingQueryParameters;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ContentPaginationExtension extends AbstractExtension
{
    private const DEFAULT_TEMPLATE = '@standard/pagination/default.html.twig';

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \App\QueryType\ContentSiblingQueryType */
    private $contentSiblingQueryType;

    /** @var \Twig\Environment */
    private $environment;

    public function __construct(
        SearchServiceInterface $searchService,
        ContentServiceInterface $contentService,
        RouterInterface $router,
        ContentSiblingQueryType $contentSiblingQueryType,
        Environment $environment
    ) {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
        $this->router = $router;
        $this->contentSiblingQueryType = $contentSiblingQueryType;
        $this->environment = $environment;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_pagination', [$this, 'contentPagination'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function contentPagination(string $template, Content $content, int $parentLocationId): string
    {
        $previousContent = $this->getSiblingContent(
            $content->contentInfo,
            $parentLocationId,
            ContentSiblingQueryParameters::PREVIOUS_CONTENT
        );
        $nextContent = $this->getSiblingContent(
            $content->contentInfo,
            $parentLocationId,
            ContentSiblingQueryParameters::NEXT_CONTENT
        );

        return $this->environment->render($this->getTemplate($template), [
            'previous' => $previousContent ? $this->getSiblingContentURL($previousContent) : null,
            'next' => $nextContent ? $this->getSiblingContentURL($nextContent) : null,
        ]);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function getSiblingContent(
        ContentInfo $content,
        int $parentLocationId,
        string $siblingContentPosition
    ): ?Content {
        $queryParameters = new ContentSiblingQueryParameters(
            $parentLocationId,
            $content->modificationDate,
            $siblingContentPosition,
            1
        );

        $query = $this->getQuery($queryParameters);
        $result = current($this->searchService->findContent($query)->searchHits);

        return $result->valueObject ?? null;
    }

    private function getQuery(ContentSiblingQueryParameters $queryParameters): Query
    {
        return $this->contentSiblingQueryType->getQuery([
            'parentLocationId' => $queryParameters->getParentLocationId(),
            'modificationDate' => $queryParameters->getModificationDate()->getTimestamp(),
            'limit' => $queryParameters->getLimit(),
            'operator' => $queryParameters->getOperator(),
            'sortDirection' => $queryParameters->getSortDirection(),
        ]);
    }

    private function getSiblingContentURL(Content $content): string
    {
        return $this->router->generate('ez_urlalias', [
            'locationId' => $content->getVersionInfo()->getContentInfo()->mainLocationId,
        ]);
    }

    private function getTemplate(?string $template = null): string
    {
        return $this->environment->getLoader()->exists($template) ? $template : self::DEFAULT_TEMPLATE;
    }
}
