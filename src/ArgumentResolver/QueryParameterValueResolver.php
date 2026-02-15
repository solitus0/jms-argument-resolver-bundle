<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;

class QueryParameterValueResolver extends AbstractValueResolver
{
    protected function getData(Request $request): string
    {
        $data = $request->query->all();

        return json_encode($data);
    }
}
