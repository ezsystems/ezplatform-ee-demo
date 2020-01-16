<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Layout;

use Twig\Extension\RuntimeExtensionInterface;

final class CSSRenderer extends AbstractRenderer implements LayoutRendererInterface, RuntimeExtensionInterface
{
    public const CSS_FIELD_NAME = 'css';
    public const CSS_TAG = '<style>%s</style>';

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function render(?int $contentId = null): string
    {
        $cssText = $this->getContentHelper()->getContentFieldValue($contentId, self::CSS_FIELD_NAME);

        return $cssText ? sprintf(self::CSS_TAG, $cssText) : '';
    }
}
