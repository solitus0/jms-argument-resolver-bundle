<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;

final class RequestPayloadValueResolver extends AbstractValueResolver
{
    protected function getData(Request $request): string
    {
        if ($this->isJsonRequest($request)) {
            $content = $request->getContent();

            return empty($content) ? '{}' : $content;
        }

        return $this->getFormPayload($request);
    }

    private function isJsonRequest(Request $request): bool
    {
        $contentType = strtolower($request->headers->get('Content-Type', ''));

        return str_contains($contentType, 'json');
    }

    private function getFormPayload(Request $request): string
    {
        $data = $request->request->all();
        $files = $request->files->all();

        foreach ($files as $key => $file) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $file;
            }
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
