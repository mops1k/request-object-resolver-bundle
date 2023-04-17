<?php

declare(strict_types=1);

namespace RequestObjectResolverBundle;

/**
 * @deprecated
 */
interface ValidationGroupsInterface extends RequestModelInterface
{
    /**
     * @return null|array<mixed>
     */
    public static function validationGroups(): ?array;
}
