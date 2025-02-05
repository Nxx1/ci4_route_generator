<?php

namespace Akara\RouteGenerator;

#[\Attribute]
class RoutePath
{
    public function __construct(
        public string $path,
        public string $method,
        public array $filters = [],
    ) {}
}
