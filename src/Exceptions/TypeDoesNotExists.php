<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class TypeDoesNotExists extends BadRequestHttpException implements ObjectResolverException
{
}
