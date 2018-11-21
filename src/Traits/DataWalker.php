<?php

namespace Omnipay\Adyen\Traits;

trait DataWalker
{
    /**
     * Expand any keys using "dot.tokens" to nested child arrays.
     * @param array $arr
     * @return array
     */
    protected function expandKeys(array $arr)
    {
        // See https://stackoverflow.com/questions/51573147

        $result = [];

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = $this->expandKeys($value);
            }

            foreach (array_reverse(explode('.', $key)) as $key) {
                $value = [$key => $value];
            }

            $result = array_merge_recursive($result, $value);
        }

        return $result;
    }

    /**
     * Get a data item using "dot-notation".
     *
     * @param string $key key to the nested array value
     * @param mixed $default the default value if no matching key found
     * @param array $target an alternative structure to walk than the getData() source
     * @return mixed the key vakue or the default value
     */
    public function getDataItem($key, $default = null, array $target = null)
    {
        if ($target === null) {
            $target = $this->getData();
        }

        if (! is_array($target)) {
            return $default;
        }

        if (is_null($key) || trim($key) == '') {
            return $target;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
                continue;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
                continue;
            }

            if ($default instanceof \Closure) {
                return $default();
            }

            return $default;
        }

        return $target;
    }
}
