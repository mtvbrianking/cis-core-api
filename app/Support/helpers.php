<?php

if (! function_exists('array_get')) {
    /**
     * Get array value by key or default.
     *
     * @param array $haystack The array
     * @param mixed $needle   The searched value
     * @param mixed $default
     *
     * @return mixed
     */
    function array_get(array $haystack, $needle, $default = null)
    {
        return isset($haystack[$needle]) ? $haystack[$needle] : $default;
    }
}
