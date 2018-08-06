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

        // My original attempt. Works, but less elegant.

        $result = [];

        while (count($arr)) {
            // Shift the first element off the array - both key and value.
            // We are treating this like a stack of elements to work through,
            // and some new elements may be added to the stack as we go.

            $value = reset($arr);
            $key = key($arr);
            unset($arr[$key]);

            if (strpos($key, '.') !== false) {
                list($base, $ext) = explode('.', $key, 2);

                if (! array_key_exists($base, $arr)) {
                    // This will be another array element on the end of the
                    // arr stack, to recurse into.

                    $arr[$base] = [];
                }

                // Add the value nested one level in.
                // Value at $arr['bar.baz.biz'] is now at $arr['bar']['baz.biz']
                // We may also add to this element before we get to processing it,
                // for example $arr['bar.baz.bam'] to $arr['bar']['baz.bam']
                // which then get further processed to $arr['bar']['baz']['biz', 'bam']

                $arr[$base][$ext] = $value;
            } elseif (is_array($value)) {
                // We already have an array value, so give the value
                // the same treatment in case any keys need expanding further.

                $result[$key] = $this->expandKeys($value);
            } else {
                // A scalar value with no expandable key.

                $result[$key] = $value;
            }
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

            if ($default instanceof Closure) {
                return $default();
            } else {
                return $default;
            }
        }

        return $target;
    }
}
