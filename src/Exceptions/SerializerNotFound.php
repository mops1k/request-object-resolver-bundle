<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SerializerNotFound extends BadRequestHttpException implements ObjectResolverException
{
}
