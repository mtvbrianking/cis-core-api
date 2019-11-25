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
