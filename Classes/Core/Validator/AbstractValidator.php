<?php
namespace Bfc\Core\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * Specifies whether this validator accepts empty values.
     *
     * If this is TRUE, the validators isValid() method is not called in case of an empty value
     * Note: A value is considered empty if it is NULL or an empty string!
     * By default all validators except for NotEmpty and the Composite Validators accept empty values
     *
     * @var bool
     */
    protected $acceptsEmptyValues = true;

    /**
     * This contains the supported options, their default values, types and descriptions.
     *
     *
     * @var array
     */
    protected $supportedOptions = [
        //
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructs the validator and sets validation options.
     *
     * @param array $options Options for the validator.
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        // check for options given but not supported
        $unsupportedOptions = array_diff_key($options, $this->supportedOptions);
        if ($unsupportedOptions !== []) {
            throw new \Exception('Unsupported validation option(s) found: ' . implode(', ', array_keys($unsupportedOptions)), time());
        }

        // check for required options being set
        array_walk(
            $this->supportedOptions,
            function ($supportedOptionData, $supportedOptionName, $options) {
                if (isset($supportedOptionData[3]) && $supportedOptionData[3] === true && !array_key_exists($supportedOptionName, $options)) {
                    throw new \Exception('Required validation option not set: ' . $supportedOptionName, time());
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
     * Checks if the given value is valid.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value)
    {
        $hasError = 0;
        if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
            $hasError = $this->isValid($value);
        }

        return $hasError;
    }

    /**
     * Returns the options of this validator.
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
     * Check if $value is valid. If it is not valid, needs to add an error to result.
     *
     * @param mixed $value
     *
     * @return bool
     */
    abstract protected function isValid($value);
}