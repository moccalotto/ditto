<?php

namespace Moccalotto\Ditto;

use LogicException;

/**
 * Generic immutable DTO.
 */
class Ditto
{
    /**
     * @var array
     */
    protected $content = [];

    /**
     * @var array
     */
    protected $validMetaKeys = [];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * Constructor.
     *
     * @param string[] $validMetaKeys
     */
    public function __construct(array $validMetaKeys)
    {
        $this->validMetaKeys = $validMetaKeys;
    }

    /**
     * Set the data.
     *
     * @param array       $content The data to transfer
     *
     * @return Ditto
     */
    public function withContent($content)
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
    }

    /**
     * Check if a given meta key is allowed.
     *
     * @param string $key
     *
     * @return bool
     */
    public function metaKeyAllowed($key)
    {
        return in_array($key, $this->validMetaKeys);
    }

    /**
     * Throw an exception if $key is not valid meta key.
     *
     * @throws LogicException if the key is not allowed
     */
    public function ensureMetaKeyAllowed($key)
    {
        if (!$this->metaKeyAllowed($key)) {
            throw new LogicException(sprintf('Meta key "%s" is not allowed', $key), 0);
        }
    }

    /**
     * Set metadata.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Ditto
     */
    public function withMetadata($key, $value)
    {
        $this->ensureMetaKeyAllowed($key);

        $clone = clone $this;
        $clone->metadata[$key] = $value;

        return $clone;
    }

    /**
     * Does a given metadata key exist?
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasMetadata($key)
    {
        return array_key_exists($key, $this->metadata);
    }

    /**
     * Get metadata variable.
     *
     * @param string $key
     * @param mixed  $defaultValue if this value is NOT given and the value is NOT found,
     *                             an OutOfBoundsException will be thrown
     *
     * @return mixed
     */
    public function metadata($key, $defaultValue = null)
    {
        if (!$this->hasMetadata($key)) {
            if (func_num_args() === 1) {
                throw new OutOfBoundsException(sprintf('Metadata key "%s" does not exisst', $key));
            }

            return $defaultValue;
        }

        return $this->metadata[$key];
    }

    /**
     * Magic methods.
     */
    public function __call($methodName, $params)
    {
        // hasError() => hasMetadata('error')
        // hasSuccess() => hasMetadata('success')
        if (strpos($methodName, 'has') === 0) {
            $key = lcfirst(substr($methodName, 3));

            return $this->hasMetadata($key);
        }

        // withMessage('foo') => withMetadata('message', 'foo')
        // withSuccess() => withMetadata('success', true)
        if (strpos($methodName, 'with') === 0) {
            $key = lcfirst(substr($methodName, 4));
            $value = array_key_exists(0, $params) ? $params[0] : true;

            return $this->withMetadata($key, $value);
        }

        // withMessage('foo') => withMetadata('message', 'foo')
        // withSuccess() => withMetadata('success', true)
        if (strpos($methodName, 'with') === 0) {
            $key = lcfirst(substr($methodName, 4));
            $value = array_key_exists(0, $params) ? $params[0] : true;

            return $this->withMetadata($key, $value);
        }

        // foo() => metadata('foo')
        if (array_key_exists($methodName, $this->metadata)) {
            return $this->metadata[$methodName];
        }

        throw new LogicException(sprintf('Unknown method name: "%s"', $methodName), 0);
    }

    /**
     * Static magic.
     *
     * @param string $validMetaKeys,... The available metadata keys.
     *
     * @return Ditto a new instance
     */
    public static function createFor()
    {
        return new self(func_get_args());
    }

    /**
     * Return all the content data.
     *
     * @return array
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Find an object within an array or an object.
     *
     * @param string $key       the key we are searching for
     * @param mixed  $container The array, ArrayAccess or object we are inspecting
     *
     * @return mixed the value found - possibly an instance of NonExistingValue if no value was found
     */
    protected function findInContainer($key, $container)
    {
        if (is_array($container) || $container instanceof ArrayAccess) {
            return isset($container[$key]) ? $container[$key] : new NonExistingValue();
        }

        if (is_object($container)) {
            return isset($container->$key) ? $container->$key : new NonExistingValue();
        }

        return new NonExistingValue();
    }

    /**
     * Get a data entry.
     *
     * @param string $path The path used to find the data
     *
     * @return mixed Return the data found via path if it exsts
     *               - or an instance of NonExistingValue if the value did not exist
     */
    protected function find($path)
    {
        $pathElements = explode('/', $path);

        $current = $this->content;

        foreach ($pathElements as $key) {
            $current = $this->findInContainer($key, $current);
        }

        return $current;
    }

    /**
     * Get a data entry.
     *
     * @param string $path         The path used to find the data
     * @param mixed  $defaultValue The value to return in the data was not found
     *
     * @return mixed Return the data found via path if it exsts - or an $defaultValue if the data was not found
     */
    public function getOr($path, $defaultValue)
    {
        $found = $this->find($path);

        if ($found instanceof NonExistingValue) {
            return $defaultValue;
        }

        return $found;
    }

    /**
     * Get a data entry.
     *
     * @param string $path The path used to find the data
     *
     * @return mixed Return the data found via path
     *
     * @throws NotFoundException if The data could not be found
     */
    public function get($path)
    {
        $found = $this->find($path);

        if ($found instanceof NonExistingValue) {
            throw new NotFoundException($path);
        }

        return $found;
    }

    /**
     * Check if the data contains an entry with the given path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path)
    {
        $found = $this->find($path);

        return !($found instanceof NonExistingValue);
    }

    /**
     * Ensure that the data an entry on the given path.
     *
     * @param string $path
     *
     * @throws NotFoundException if the path does not exist.
     */
    public function mustHave($path)
    {
        if (!$this->has($path)) {
            throw new NotFoundException(sprintf(
                'Value with the path "%s" not found.',
                $path
            ));
        }
    }

    /**
     * Create a new Ditto object using the data located in $path as its content.
     *
     * @param string $path
     *
     * @return Ditto
     */
    public function on($path)
    {
        $content = $this->get($path);

        return $this->withContent($content);
    }
}
