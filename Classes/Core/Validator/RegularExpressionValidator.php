<?php
namespace Bfc\Core\Validator;

/**
 * Validator based on regular expressions.
 */
class RegularExpressionValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'regularExpression' => ['', 'The regular expression to use for validation, used as given', 'string', true]
    ];

    /**
     * Checks if the given value matches the specified regular expression.
     *
     * @param mixed $value
     *
     * @return bool
     * @throws \Exception
     */
    public function isValid($value)
    {
        $result = preg_match($this->options['regularExpression'], $value);
        if ($result === false) {
            throw new \Exception('regularExpression "' . $this->options['regularExpression'] . '" in RegularExpressionValidator contained an error.', time());
        }

        if ($result === 0) {
            return false;
        }

        return true;
    }
}