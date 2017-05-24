<?php
namespace Bfc\Core\Validator;

class Validator
{
    /**
     * @var \Bfc\Core\Object\ObjectManager
     * @inject
     */
    protected $objectManager = null;

    /**
     * @var \SplObjectStorage
     */
    protected $validators = null;

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $this->validators = new \SplObjectStorage();
    }

    /**
     * Validate the value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value = null)
    {
        $hasError = 0;

        $validators = $this->getValidators();
        if ($validators->count() > 0) {
            /** @var AbstractValidator $validator */
            foreach ($validators as $validator) {
                $result = $validator->validate($value);
                if (!$result) {
                    $hasError++;
                }
            }
        }

        return $hasError
            ? false
            : true;
    }

    /**
     * Get the validators.
     *
     * @return \SplObjectStorage
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Adds a new validator to the collection.
     *
     * @param string $validatorClass
     * @param array $options
     */
    public function addValidator($validatorClass, array $options = [])
    {
        $validator = $this->objectManager->get($validatorClass, $options);
        $this->validators->attach($validator);
    }

    /**
     * Removes the specified validator.
     *
     * @param ValidatorInterface $validator
     *
     * @throws \Exception
     */
    public function removeValidator(ValidatorInterface $validator)
    {
        if (!$this->validators->contains($validator)) {
            throw new \Exception('Cannot remove validator because its not in the collection.', time());
        }
        $this->validators->detach($validator);
    }
}