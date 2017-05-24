<?php

/**
 * Class mapping.
 *
 * @var array $classMap The key of array is namespace and value is array of absolute paths where a class can be found.
 */
$classMap = [
    'Pp\\CodeSample\\' => [
        __DIR__ . '/Classes/',
    ],
];

require_once __DIR__ . '/Classes/Core/Autoload/ClassLoader.php';
\Bfc\Core\Autoload\ClassLoader::initialize($classMap);