<?php
namespace Bfc\Core\Filter;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * Characters used in European countries.
     *
     * @var string
     */
    protected $characters = 'A-Za-zßÜÖÄüöäÀÁÂÃÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕØŠÙÚÛÝŸŽÞàáâãåæçèéêëìíîïðñòóôõøšùúûýþÿž';

    /**
     * This contains the supported options, their default values.
     *
     * @var array
     */
    protected $supportedOptions = [
        // 'optionName' => ['default value', 'requare: true|false'],
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructs the filter and sets filter options.
     *
     * @param array $options Options for the filter.
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        // check for options given but not supported
        $unsupportedOptions = array_diff_key($options, $this->supportedOptions);
        if ($unsupportedOptions !== []) {
            throw new \Exception('Unsupported filter option(s) found: ' . implode(', ', array_keys($unsupportedOptions)), time());
        }

        // check for required options being set
        array_walk(
            $this->supportedOptions,
            function ($supportedOptionData, $supportedOptionName, $options) {
                if (isset($supportedOptionData[1]) && $supportedOptionData[1] === true && !array_key_exists($supportedOptionName, $options)) {
                    throw new \Exception('Required filter option not set: ' . $supportedOptionName, time());
                }
            },
            $options
        );

        // merge with default values
        $this->options = array_merge(
            array_map(
                function ($value) {
                    return $value[0];
                },
                $this->supportedOptions
            ),
            $options
        );
    }

    /**
     * @return string
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * Apply the filter.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function applyFilter($value)
    {
        if ($this->isEmpty($value) === false) {
            $value = $this->filter($value);
        }

        return $value;
    }

    /**
     * Returns the options of this filter.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Check if the value is empty.
     *
     * @param mixed $value
     *
     * @return bool
     */
    final protected function isEmpty($value)
    {
        return $value === null || $value === '';
    }

    /**
     * Filter the value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public abstract function filter($value);
}