<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Layout;

use Twig\Extension\RuntimeExtensionInterface;

final class JSRenderer extends AbstractRenderer implements LayoutRendererInterface, RuntimeExtensionInterface
{
    public const JS_FIELD_NAME = 'js';
    public const JS_TAG = '<script>%s</script>';

    /**
     * {@inheritdoc}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function render(?int $contentId = null): string
    {
        $jsText = $this->getContentHelper()->getContentFieldValue($contentId, self::JS_FIELD_NAME);

        return $jsText ? sprintf(self::JS_TAG, $jsText) : '';
    }
}
