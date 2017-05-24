<?php
namespace Bfc\Core\Filter;

class Filter
{
    /**
     * @var \Bfc\Core\Object\ObjectManager
     * @inject
     */
    protected $objectManager = null;

    /**
     * @var \SplObjectStorage
     */
    protected $filters = null;

    /**
     * Filter constructor.
     */
    public function __construct()
    {
        $this->filters = new \SplObjectStorage();
    }

    /**
     * Apply added filters to value.
     *
     * @note Before apply a filter(s) it muss be added with self::addFilter().
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function apply($value)
    {
        $filters = $this->getFilters();
        if ($filters->count() > 0) {
            /** @var AbstractFilter $filter */
            foreach ($filters as $filter) {
                $value = $filter->applyFilter($value);
            }
        }

        return $value;
    }

    /**
     * Get the filter.
     *
     * @return \SplObjectStorage
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Adds a new filter to the collection.
     *
     * @param string $filterClass
     * @param array $options
     */
    public function addFilter($filterClass, array $options = [])
    {
        $filter = $this->objectManager->get($filterClass, $options);
        $this->filters->attach($filter);
    }

    /**
     * Removes the specified filter.
     *
     * @param FilterInterface $filter
     *
     * @throws \Exception
     */
    public function removeFilter(FilterInterface $filter)
    {
        if (!$this->filters->contains($filter)) {
            throw new \Exception('Cannot remove filter because its not in the collection.', time());
        }
        $this->filters->detach($filter);
    }
}