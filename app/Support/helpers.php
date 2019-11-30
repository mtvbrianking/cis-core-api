<?php

if (! function_exists('array_get')) {
    /**
     * Get array value by key or default.
     *
     * ```
     * array_get($arr, 'users.role.name', null)
     * ```
     *
     * @param array  $haystack The array
     * @param string $needle   The searched value
     * @param mixed  $default
     *
     * @return mixed
     */
    function array_get(array $haystack, $needle, $default = null)
    {
        $keys = explode('.', $needle);

        foreach ($keys as $idx => $needle) {
            if (! isset($haystack[$needle])) {
                return $default;
            }

            if ($idx === (sizeof($keys) - 1)) {
                return $haystack[$needle];
            }

            $haystack = $haystack[$needle];
        }

        return $default;
    }
}

if (! function_exists('array_remove_empty')) {
    /**
     * Remove empty elements from array.
     *
     * @param array $haystack The array
     *
     * @return mixed
     */
    function array_remove_empty(array $haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = array_remove_empty($haystack[$key]);
            }

            if (empty($haystack[$key]) && $haystack[$key] != 0) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }
}
