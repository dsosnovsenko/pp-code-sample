<?php
namespace Bfc\Core\Filter;

class AlphanumericFilter extends AlphaFilter
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        // The maximal length of string, default 0, required = false
        'length' => [0, false],
        // Whether to allow white space characters, default not allow, required = false
        'allowWhiteSpace' => [false, false],
        // Allow numeric characters, default 0-9, required = false
        'allowCharacters' => ['0-9', false],
    ];
}