<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SerializerNotFound extends BadRequestHttpException implements ObjectResolverException
{
}
