<?php

use PhpOption\Option;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Optional;
use Illuminate\Support\Collection;
use Dotenv\Environment\DotenvFactory;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HigherOrderTapProxy;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

if (! function_exists('append_config')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;

                $array[$start] = Arr::pull($array, $key);
            }
        }

        return $array;
    }
}

if (! function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     *
     * @deprecated Arr::add() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if (! function_exists('array_collapse')) {
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array  $array
     * @return array
     *
     * @deprecated Arr::collapse() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}

if (! function_exists('array_divide')) {
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     *
     * @deprecated Arr::divide() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}

if (! function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     *
     * @deprecated Arr::dot() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}

if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     *
     * @deprecated Arr::except() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (! function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     *
     * @deprecated Arr::first() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if (! function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     *
     * @deprecated Arr::flatten() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}

if (! function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     *
     * @deprecated Arr::forget() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}

if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     *
     * @deprecated Arr::get() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     *
     * @deprecated Arr::has() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}

if (! function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     *
     * @deprecated Arr::last() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if (! function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     *
     * @deprecated Arr::only() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     *
     * @deprecated Arr::pluck() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (! function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     *
     * @deprecated Arr::prepend() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (! function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     *
     * @deprecated Arr::pull() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (! function_exists('array_random')) {
    /**
     * Get a random value from an array.
     *
     * @param  array  $array
     * @param  int|null  $num
     * @return mixed
     *
     * @deprecated Arr::random() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_random($array, $num = null)
    {
        return Arr::random($array, $num);
    }
}

if (! function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     *
     * @deprecated Arr::set() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (! function_exists('array_sort')) {
    /**
     * Sort the array by the given callback or attribute name.
     *
     * @param  array  $array
     * @param  callable|string|null  $callback
     * @return array
     *
     * @deprecated Arr::sort() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_sort($array, $callback = null)
    {
        return Arr::sort($array, $callback);
    }
}

if (! function_exists('array_sort_recursive')) {
    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array  $array
     * @return array
     *
     * @deprecated Arr::sortRecursive() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}

if (! function_exists('array_where')) {
    /**
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     *
     * @deprecated Arr::where() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}

if (! function_exists('array_wrap')) {
    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param  mixed  $value
     * @return array
     *
     * @deprecated Arr::wrap() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}

if (! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param  mixed  $value
     * @return bool
     */
    function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (! function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     *
     * @deprecated Str::camel() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param  object|string  $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (! function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string|array|int  $key
     * @param  mixed   $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

if (! function_exists('e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param  \Illuminate\Contracts\Support\Htmlable|string  $value
     * @param  bool  $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     *
     * @deprecated Str::endsWith() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        static $variables;

        if ($variables === null) {
            $variables = (new DotenvFactory([new EnvConstAdapter, new PutenvAdapter, new ServerConstAdapter]))->createImmutable();
        }

        return Option::fromValue($variables->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return value($default);
            });
    }
}

if (! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param  mixed  $value
     * @return bool
     */
    function filled($value)
    {
        return ! blank($value);
    }
}

if (! function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array  $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}

if (! function_exists('kebab_case')) {
    /**
     * Convert a string to kebab case.
     *
     * @param  string  $value
     * @return string
     *
     * @deprecated Str::kebab() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function kebab_case($value)
    {
        return Str::kebab($value);
    }
}

if (! function_exists('last')) {
    /**
     * Get the last element from an array.
     *
     * @param  array  $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}

if (! function_exists('object_get')) {
    /**
     * Get an item from an object using "dot" notation.
     *
     * @param  object  $object
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (! function_exists('optional')) {
    /**
     * Provide access to optional objects.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function optional($value = null, callable $callback = null)
    {
        if (is_null($callback)) {
            return new Optional($value);
        } elseif (! is_null($value)) {
            return $callback($value);
        }
    }
}

if (! function_exists('preg_replace_array')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * @return string
     */
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int  $sleep
     * @param  callable  $when
     * @return mixed
     *
     * @throws \Exception
     */
    function retry($times, callable $callback, $sleep = 0, $when = null)
    {
        $attempts = 0;
        $times--;

        beginning:
        $attempts++;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if (! $times || ($when && ! $when($e))) {
                throw $e;
            }

            $times--;

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (! function_exists('snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     *
     * @deprecated Str::snake() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}

if (! function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     *
     * @deprecated Str::startsWith() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (! function_exists('str_after')) {
    /**
     * Return the remainder of a string after a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     *
     * @deprecated Str::after() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_after($subject, $search)
    {
        return Str::after($subject, $search);
    }
}

if (! function_exists('str_before')) {
    /**
     * Get the portion of a string before a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     *
     * @deprecated Str::before() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_before($subject, $search)
    {
        return Str::before($subject, $search);
    }
}

if (! function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     *
     * @deprecated Str::contains() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}

if (! function_exists('str_finish')) {
    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     *
     * @deprecated Str::finish() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }
}

if (! function_exists('str_is')) {
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|array  $pattern
     * @param  string  $value
     * @return bool
     *
     * @deprecated Str::is() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}

if (! function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int     $limit
     * @param  string  $end
     * @return string
     *
     * @deprecated Str::limit() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_limit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }
}

if (! function_exists('str_plural')) {
    /**
     * Get the plural form of an English word.
     *
     * @param  string  $value
     * @param  int     $count
     * @return string
     *
     * @deprecated Str::plural() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }
}

if (! function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     *
     * @deprecated Str::random() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (! function_exists('str_replace_array')) {
    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string  $search
     * @param  array   $replace
     * @param  string  $subject
     * @return string
     *
     * @deprecated Str::replaceArray() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_replace_array($search, array $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }
}

if (! function_exists('str_replace_first')) {
    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     *
     * @deprecated Str::replaceFirst() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (! function_exists('str_replace_last')) {
    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     *
     * @deprecated Str::replaceLast() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (! function_exists('str_singular')) {
    /**
     * Get the singular form of an English word.
     *
     * @param  string  $value
     * @return string
     *
     * @deprecated Str::singular() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_singular($value)
    {
        return Str::singular($value);
    }
}

if (! function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $title
     * @param  string  $separator
     * @param  string  $language
     * @return string
     *
     * @deprecated Str::slug() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
}

if (! function_exists('str_start')) {
    /**
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $prefix
     * @return string
     *
     * @deprecated Str::start() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_start($value, $prefix)
    {
        return Str::start($value, $prefix);
    }
}

if (! function_exists('studly_case')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     *
     * @deprecated Str::studly() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function studly_case($value)
    {
        return Str::studly($value);
    }
}

if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Throwable|string  $exception
     * @param  array  ...$parameters
     * @return mixed
     *
     * @throws \Throwable
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (! function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Throwable|string  $exception
     * @param  array  ...$parameters
     * @return mixed
     * @throws \Throwable
     */
    function throw_unless($condition, $exception, ...$parameters)
    {
        if (! $condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (! function_exists('title_case')) {
    /**
     * Convert a value to title case.
     *
     * @param  string  $value
     * @return string
     *
     * @deprecated Str::title() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function title_case($value)
    {
        return Str::title($value);
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param  string  $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (! function_exists('transform')) {
    /**
     * Transform the given value if it is present.
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @param  mixed  $default
     * @return mixed|null
     */
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('windows_os')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}


if (! function_exists('table_config')) {
    function table_config($table, $array)
    {
        $string = json_encode($array);
        try{
            if(getDriver()=='mysql'){
                Schema::getConnection()->statement("ALTER TABLE $table comment = '$string'");
            }elseif(getDriver()=='pgsql'){
                Schema::getConnection()->statement("COMMENT ON TABLE $table IS '$string'");
            }
        }catch(\Exception $e){
        }
    }
}

if (! function_exists('createMany')) {
    function createMany($table, $array)
    {
        
    }
}
function _joinRecursive($joinMax,&$kembar,&$fieldSelected,&$allColumns,&$joined,&$model,$tableName,$params){
    $tableStringClass = "\App\Models\BasicModels\\$tableName";
    $currentModel = new $tableStringClass;
    
    foreach( $currentModel->joins as $join ){
        $arrayJoins=explode("=",$join);
        $arrayParents=explode(".",$arrayJoins[0]);

        if(count($arrayParents)>2){
            $parent = $arrayParents[1];
            $fullParent = $arrayParents[0].".".$arrayParents[1];
        }else{
            $parent = $arrayParents[0];
            $fullParent=$parent;
        }
        // if(in_array($parent, $joined)){        
        //     continue;
        // }//PENTING
        $onParent = $arrayJoins[0];
        $onMe = $arrayJoins[1];
        $joined[]=$fullParent;
        $parentClassString = "\App\Models\BasicModels\\$parent";

        if( !class_exists($parentClassString) ){
            continue;
        }
        if(isset($params->caller) && $params->caller==$parent){
            continue;                
        }
        if( !isset($kembar[$parent]) ){
            $kembar[$parent] = 1;
        }else{
            $kembar[$parent] = $kembar[$parent]+1;
        }
        
        $parentName = $fullParent;
        if($kembar[$parent]>1){
            $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
            $onParentArray=explode(".",$onParent);
            if( count( $onParentArray )>2 ){
                $onParent = $onParentArray[1].".".$onParentArray[2];
            }
            $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
        }
        $model = $model->leftJoin($parentName,$onParent,"=",$onMe);
        $parentClass = new $parentClassString;
        if($kembar[$parent]>1){
            $parentName = $parent.(string)$kembar[$parent];
        }
        foreach($parentClass->columns as $column){
            $colTemp        = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
            $fieldSelected[]= $colTemp;
            $allColumns[]   = "$parentName.$column";
        }
        if($joinMax>1){
            _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
        }
    }
    
}
function _customGetData($model,$params)
{
    $table = $model->getTable();
    $className = class_basename( $model );
    
    $givenScopes = [];
    if($table == config( "parentTable") && req('scopes')){
        $scopes = explode(",", req('scopes'));
        foreach( $scopes as $scope ){
            if( !$model->hasNamedScope($scope) ){
                abort(422,json_encode([
                    'message'=>"Scope $scope tidak ditemukan",
                    "resource"=>$className
                ]));
            }
        }
        $givenScopes = $scopes;
    }

    $isParent = $className == (@app()->request->route()[2]['detailmodelname'] || @app()->request->route()[2]['modelname']);
    $joinMax = isset($params->joinMax)?$params->joinMax:0;
    $pureModel=$model;    
    $modelCandidate = "\\".get_class($model);
    // $modelCandidate = "\App\Models\CustomModels\\$table";
    $modelExtender  = new $modelCandidate;
    $fieldSelected=[];
    $metaColumns = [];
    foreach($model->columns as $column){
        $fieldSelected[] = "$table.$column";
        $metaColumns[$column] = "frontend";
    }
    $allColumns = $fieldSelected;
    $kembar = [];
    $joined = [];
    $enableJoin = $params->join;
    if( $isParent ){
        $enableJoin = req('join') ?? $params->join;
    }else{
        $enableJoin = req($className."_join") ?? true;
    }
    $enableJoin = is_bool($enableJoin) ? $enableJoin : (strtolower($enableJoin) === 'false' ? false : true);
    if( $enableJoin ){
        foreach( $model->joins as $join ){
            $arrayJoins=explode("=",$join);
            $arrayParents=explode(".",$arrayJoins[0]);

            if(count($arrayParents)>2){
                $parent = $arrayParents[1];
                $fullParent = $arrayParents[0].".".$arrayParents[1];
            }else{
                $parent = $arrayParents[0];
                $fullParent=$parent;
            }
            
            $joined[]=$parent;
            $onParent = $arrayJoins[0];
            $onMe = $arrayJoins[1];
            $parentClassString = "\App\Models\BasicModels\\$parent";
            
            if( !class_exists($parentClassString) ){
                continue;
            }
            if($params->caller && $params->caller==$parent){
                continue;                
            }
            if( !isset($kembar[$parent]) ){
                $kembar[$parent] = 1;
            }else{
                $kembar[$parent] = $kembar[$parent]+1;
            }
            $parentName = $fullParent;
            if($kembar[$parent]>1){
                $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
                // $onParent = str_replace($parent,"tes".$parent.(string)$kembar[$parent],$onParent); //OLD CODE
                $onParentArray=explode(".",$onParent);
                if( count( $onParentArray )>2 ){
                    $onParent = $onParentArray[1].".".$onParentArray[2];
                }
                $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
            }
            $model = $model->leftJoin($parentName,$onParent,"=",$onMe);
            $parentClass = new $parentClassString;
            if($kembar[$parent]>1){
                $parentName = $parent.(string)$kembar[$parent];
            }
            foreach($parentClass->columns as $column){
                $colTemp        = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
                $fieldSelected[]= $colTemp;
                $allColumns[]   = "$parentName.$column";
            }
            
            if($joinMax>0){
                _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
            }
        }
    }
    if($params->selectfield || ($isParent && req('selectfield')) || req($className."_selectfield")){
        $rawSelectFields = req($className."_selectfield") ?? req('selectfield') ?? $params->selectfield;
        $selectFields = str_replace(["this.","\n","  ","\t"],["$table.","","",""], $rawSelectFields);
        $selectFields = explode(",", $selectFields);
        $fieldSelected= $selectFields;
    }
    
    if( isset($params->addSelect) && $params->addSelect!=null ){
        $addSelect = str_replace("this.","$table.",strtolower($params->addSelect));
        $fieldSelected = array_merge( $fieldSelected, explode(",",$addSelect));
    }
    
    if( $params->addJoin || ($isParent && req('addjoin')) || req($className."_addjoin") ){
        $addJoin = req($className."_addjoin") ?? req('addjoin') ?? $params->addJoin;            
        $joiningString = str_replace("this.","$table.",strtolower($addJoin));
        $joins = explode( ",", $joiningString );
        foreach($joins as $join){
            $join = strtolower($join);
            if(strpos( $join, " and ")!==FALSE){
                $join = explode(" and ",$join);
                $joinedTable=explode(".",$join[0])[0];
                $model = $model->leftJoin($joinedTable, function($q)use($join){
                    foreach($join as $statement){
                        $statement = str_replace(" ","",$statement);
                        $explodes = explode(".",$statement);
                        if( count($explodes)>2 ){
                            $parent = "{$explodes[0]}.{$explodes[1]}";
                        }else{
                            $parent = $explodes[0];
                        }
                        $onParent = explode("=",$statement)[0];
                        $onMe = explode("=",$statement)[1];
                        $q->on($onParent,"=",$onMe);
                    }
                });
            }else{
                $candParent = explode("=", $join)[0];
                $explodes = explode(".", $candParent);
                if( count($explodes)>2 ){
                    $parent = $explodes[0].".".$explodes[1];
                }else{
                    $parent = $explodes[0];
                }
                $onParent = explode("=",$join)[0];
                $onMe = explode("=",$join)[1];
                $model = $model->leftJoin($parent,$onParent,"=",$onMe);
            }
        }
    }
    
    if(method_exists($modelExtender, "extendJoin")){
        $model = $modelExtender->extendJoin($model);
    }
    
    if($params->search){
        $searchfield = $params->searchfield;
        $string  = strtolower($params->search);
        $additionalString = getDriver()=="pgsql"?"::text":"";

        $isAutoPrefix = req('auto_prefix')===null?true:req('auto_prefix');
        $isAutoPrefix = $isAutoPrefix==='false'?false:true;
        $model = $model->where(
            function ($query)use($allColumns,$string,$additionalString, $searchfield,$table,$isAutoPrefix) {
                if($searchfield!=null){
                    $searchfieldArray = explode(",", strtolower($searchfield) );
                    foreach($searchfieldArray as $fieldSearching){
                        if($isAutoPrefix && strpos($fieldSearching,".")===false){
                            $fieldSearching = "this.$fieldSearching";
                        }
                        $fieldSearching = str_replace( "this.","$table.", $fieldSearching );
                        // if(in_array($fieldSearching,$allColumns)){
                            $query->orWhereRaw(DB::raw("LOWER($fieldSearching$additionalString) LIKE '%$string%'"));
                            // $query->orWhereRaw(DB::raw("LOWER($fieldSearching$additionalString) LIKE '%?%'"),[$string]); // calon
                        // }
                        // $found = null;
                        // foreach($allColumns as $col){
                        //     if(strpos($col, $fieldSearching)){
                        //         $query->orWhereRaw(DB::raw("LOWER($col$additionalString) LIKE '%$string%'"));
                        //     }
                        // }
                    }
                }else{
                    foreach($allColumns as $column){
                        if((strpos($column, '.id') !== false)||(strpos($column, '_id') !== false) ){
                            continue;
                        }
                        $query->orWhereRaw(DB::raw("LOWER($column$additionalString) LIKE '%$string%'"));
                        // $query->orWhereRaw(DB::raw("LOWER($column$additionalString) LIKE '%?%'"),[$string]); // calon
                    }
                }
        });
    }
    /**
     * Filter direct params misal this.column:21
     */
    $requestDataArr = (array)req();
    $directFilter = [];
    foreach($requestDataArr as $key => $val){
        if(Str::startsWith($key, "this_")){
            $directFilter[]=$key;
            $model = $model->where(str_replace("this_","$table.",$key ), $val);
        }
    }
    if($params->where_raw){
        $model = $model->whereRaw(str_replace("this.","$table.",urldecode( $params->where_raw) ) );
    }

    if( isRoute('read_list_detail') ){
        $parentModelName = @app()->request->route()[2]['modelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['id'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if( isRoute('read_list_sub_detail') ){
        $parentModelName = @app()->request->route()[2]['detailmodelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['detailid'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if(isset($params->notIn) && $params->notIn!==null && strpos($params->notIn,":")!==false ){
        $columnNotIn = explode(":", $params->notIn)[0];
        $idNotIn = explode(",", explode(":", $params->notIn)[1]);
        $model = $model->whereNotIn(str_replace("this.","$table.", $columnNotIn), $idNotIn );
    }
    
    if( req( $className."_filters" ) || ($isParent && req ("filters" )) ){
        $additionalString = getDriver()=="pgsql"?"::text":"";
        $currentFilters = req( $className."_filters") ?? req("filters");
        $filters = explode(",", $currentFilters);
        $filterOperators = req( $className."_filters_operator") ?? req("filters_operator");
        $operator = $filterOperators ?? (getDriver()=="pgsql"?"~*":'LIKE');
        $aliases = [];
        if( method_exists( $modelExtender, 'aliases') ){
            $aliases = $modelExtender->aliases();
        }
        $isAutoPrefix = req('auto_prefix')===null?true:req('auto_prefix');
        $isAutoPrefix = $isAutoPrefix==='false'?false:true;
        $model = $model->where( function($q)use($filters,$aliases,$additionalString,$operator,$table,$isAutoPrefix){
            foreach($filters as $filter){
                $filterKeys = explode( "=", $filter);
                if( count($filterKeys)>2 ){
                    trigger_error("maaf pencarian tidak boleh mengandung tanda (=)");
                }
                $keyFilter = $filterKeys[0];
                $keyAliased = array_search( $keyFilter,$aliases ) ;
                if( $keyAliased ){
                    $keyFilter = $keyAliased;
                }
                if( $isAutoPrefix && strpos($keyFilter,".")===false  ){
                    $keyFilter = "$table.$keyFilter";
                }
                $q->whereRaw( "{$keyFilter}$additionalString $operator '{$filterKeys[1]}'");
            }
        });
    }
    
    if( req("query_name") ){
        $rawWhere = DB::table("default_params")->select("prepared_query","params")
            ->where("name",req("query_name"))->first();
        if(!$rawWhere){
            trigger_error(json_encode(["errors"=>"query_name ".req('query_name')." does not exist"]));
        }

        $whereStr = $rawWhere->prepared_query;
        if( !empty($rawWhere->params) ){
            $paramsArr = explode(",", $rawWhere->params);
            $backendParams = [];
            $frontendParams = [];
            $frontendParamSent = (array) req();
            foreach($paramsArr as $param){
                if( strpos($param,"backend_")===false ){
                    if(!in_array($param, array_keys($frontendParamSent)) ) {
                        trigger_error(json_encode(["errors"=>"parameter $param does not exist"]));
                    }
                    $frontendParams[] = $param;
                }else{
                    $backendParams[] = $param;
                }
            }

            $acceptedParams = array_only( $frontendParamSent, $frontendParams );
            if( config( req("query_name") ) ){
                $acceptedParams = array_merge( $acceptedParams, config( req("query_name") ) );
            }

            $model = $model->where(function($q)use($table, $whereStr, $acceptedParams){
                $q->whereRaw(str_replace("this.","$table.", $whereStr ), $acceptedParams );
            });
        }else{
            $model = $model->where(function($q)use($table,$whereStr){
                $q->whereRaw(str_replace("this.","$table.",$whereStr ) );
            });
        }
    }
    
    if(  req("orin") && strpos(req("orin"),":")!==false ){
        $columnIn = explode(":", req("orin"))[0];
        $idsIn = explode(",", explode(":", req("orin"))[1]);
        $model = $model->orWhereRaw( str_replace("this.","$table.", $columnIn)." IN (".implode(',',$idsIn).")" );
    }
    if(isset($params->group_by) && $params->group_by!=null){
        $model = $model->groupBy( DB::raw(str_replace("this.", "$table.", urldecode($params->group_by) )) );
    }
    
    if($params->order_by_raw){
        $model = $model->orderByRaw( str_replace("this.","$table.",urldecode($params->order_by_raw) ) );
    }elseif($params->order_by){
        $order =  str_replace("this.","$table.", $params->order_by);
        if( method_exists( $modelExtender, 'aliases') ){
            $aliases = $modelExtender->aliases();
            if(is_array($aliases)){
                $key = array_search( $order,$aliases ) ;
                if( $key ){
                    $order = $key;
                }
            }
        }
       $model=$model->orderBy(\DB::raw($order),$params->order_type==null?"asc":$params->order_type);
    }
    $final  = $model->select(DB::raw(implode(",",$fieldSelected) ));
    
    $finalObj = (object)[
        'type'=>'get', 'caller'=>$params->caller
    ];

    if(!$params->caller){
       $data = $final->scopes($givenScopes)->final($finalObj);
       if(req('simplest')){
           $data = $data->simplePaginate($params->paginate,["*"], 'page', $page = $params->page);
       }else{
            $data = $data->paginate($params->paginate,["*"], 'page', $page = $params->page);
       }
    }else{
       $data = $final->scopes($givenScopes)->final($finalObj)->get(); 
    }
    if( req("transform")==='false' ){
        if(!$params->caller){
            $addData = collect(['processed_time' => round(microtime(true)-config("start_time"),5)]);
            $data = $addData->merge($data);
        }
        return $data;
    }
    if($params->caller){
        $tempData=$data->toArray();
        $fixedData=[];
        $index=0;
        foreach($tempData as $i => $row){
            $keys=array_keys($row);
            foreach($keys as $key){
                if( count(explode(".", $key))>2 ){
                    $newKeyArray = explode(".", $key);
                    $newKey = $newKeyArray[1].".".$newKeyArray[2];
                    $tempData[$i][$newKey] = $tempData[$i][$key];
                    unset($tempData[$i][$key]);
                }
            }
        }
        foreach($tempData as $row){
            $transformedData = reformatDataResponse($row);
            if(method_exists($modelExtender, "transformRowData")){
                $transformedData = $modelExtender->transformRowData(reformatDataResponse($row));
                if( gettype($transformedData)=='boolean' ){
                    continue;
                }
            }

            $fixedData[$index] = $transformedData;
            foreach(["create","update","delete","read"] as $akses){
                $func = $akses."roleCheck";
                if( method_exists( $modelExtender, $func) ){
                    $fixedData[$index] = array_merge( ["meta_$akses"=>in_array( $akses, ['create','list'] ) ? $modelExtender->$func() : $modelExtender->$func( $row['id'] )], $fixedData[$index]);
                }
            }

            if($pureModel->useEncryption){
                $currentId = $pureModel->decrypt($fixedData[$index]['id']);
            }else{
                $currentId = $fixedData[$index]['id'];
            }

            foreach($pureModel->details as $detail){
                $detailArray = explode(".",$detail);
                $detailClass = $detail;
                if( count($detailArray)>1 ){
                    $detailClass = $detailArray[1];
                }          
                // $modelCandidate = "\App\Models\CustomModels\\$detailClass";
                // $model      = new $modelCandidate;
                $model      = getCustom($detailClass);
                $details    = $model->details;
                $columns    = $model->columns;
                $fkName     = $pureModel->getTable();
                if(!in_array($fkName."_id",$columns)){
                    $realJoins = $model->joins;
                    foreach($realJoins as $val){
                        $valArray = explode("=",$val);
                        if($valArray[0]==$fkName.".id"){
                            $fkName = $valArray[1];
                            break;
                        }
                    }
                }else{
                    $fkName.="_id";
                }
                $p = (Object)[];
                $p->where_raw   = $fkName."=".$currentId;
                $p->order_by    = null;
                $p->order_type  = null;
                $p->order_by_raw= null;
                $p->search      = null;
                $p->searchfield = null;
                $p->selectfield = null;
                $p->paginate    = null;
                $p->page        = null;
                $p->addSelect   = null;
                $p->addJoin     = null;
                $p->join        = true;
                $p->joinMax     = 0;
                $p->group_by    = null;
                $p = $model->overrideGetParams($p,null);
                $p->caller      = $pureModel->getTable();
                $detailArray = explode('.', $detail);
                $fixedData[$index][ count($detailArray)==1? $detail : $detailArray[1] ]  = $model->customGet($p);
            }
            $index++;
        }
        $func="transformArrayData";
        if( method_exists( $modelExtender, $func )  ){
            $newFixedData = $modelExtender->$func( $fixedData );
            $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $fixedData;
        }
        $data   = $fixedData;
    }else{
        $tempData = $data->toArray()["data"];
        $fixedData=[];
        $index=0;        
        foreach($tempData as $i => $row){
            $keys=array_keys($row);
            foreach($keys as $key){
                if( count(explode(".", $key))>2 ){
                    $newKeyArray = explode(".", $key);
                    $newKey = $newKeyArray[1].".".$newKeyArray[2];
                    $tempData[$i][$newKey] = $tempData[$i][$key];
                    unset($tempData[$i][$key]);
                }
            }
        }
        foreach($tempData as $row){
            $transformedData = reformatDataResponse($row);
            if(method_exists($modelExtender, "transformRowData")){
                $transformedData = $modelExtender->transformRowData(reformatDataResponse($row));
                if( gettype($transformedData)=='boolean' ){
                    continue;
                }
            }

            $fixedData[$index] = $transformedData;
            foreach(["create","update","delete","read"] as $akses){
                $func = $akses."roleCheck";
                if( method_exists( $modelExtender, $func) ){
                    $fixedData[$index] = array_merge( ["meta_$akses"=>in_array( $akses, ['create','list'] ) ? $modelExtender->$func() : $modelExtender->$func( $row['id'] )], $fixedData[$index]);
                }
            }
            $index++;
        }
        $func="transformArrayData";
        if( method_exists( $modelExtender, $func )  ){
            $newFixedData = $modelExtender->$func( $fixedData );
            $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $fixedData;
        }
        $data = array_merge([
            "data"=>$fixedData
        ],[
            // "metaScript"=>method_exists( $modelExtender, "metaScriptList" )?$modelExtender->metaScriptList():null,
            "total"=>req('simplest')?null: $data->total(),
            "current_page"=>$data->currentPage(),
            "per_page"=>$data->perPage(),
            "from"=>$data->firstItem(),
            "to"=>$data->lastItem(),
            "last_page"=>req('simplest')?null:$data->lastPage(),
            "has_next"=>$data->hasMorePages(),
            "prev"=>$data->previousPageUrl(),
            "next"=>$data->nextPageUrl(),
            "processed_time"=>round(microtime(true)-config("start_time"),5)
        ]);
    }
    return $data;
}

function _customFind($model, $params)
{
    $table = $model->getTable();
    $className = class_basename( $model );
    $givenScopes = [];
    if($table == config( "parentTable") && req('scopes')){
        $scopes = explode(",", req('scopes'));
        foreach( $scopes as $scope ){
            if( !$model->hasNamedScope($scope) ){
                abort(422,json_encode([
                    'message'=>"Scope $scope tidak ditemukan",
                    "resource"=>$className
                ]));
            }
        }
        $givenScopes = $scopes;
    }
    $joinMax = isset($params->joinMax)?$params->joinMax:0;
    $pureModel = $model;
    $modelCandidate = "\\".get_class($model);
    // $modelCandidate = "\App\Models\CustomModels\\$table";
    $idToFind = $pureModel->useEncryption ? $pureModel->decrypt($params->id) : $params->id;
    $modelExtender  = new $modelCandidate;
    $fieldSelected=[];
    $metaColumns=[];
    foreach($model->columns as $column){
        $fieldSelected[] = "$table.$column";
        $metaColumns[$column] = "frontend";
    }
    // if(!in_array(class_basename($model),array_keys(config('tables')))){
    //     $func = "metaFields";
    //     if( method_exists( $model, $func) ){
    //         $metaColumns = array_merge( $metaColumns, $model->$func($model->columns) );
    //     }
    //     config(['tables'=>array_merge(config('tables'), [class_basename($model)=>$metaColumns]) ]);
    // }
    $joined=[];
    $allColumns = $fieldSelected;
    if( $params->join ){
        $kembar = [];
        foreach( $model->joins as $join ){
            $arrayJoins=explode("=",$join);
            $arrayParents=explode(".",$arrayJoins[0]);

            if(count($arrayParents)>2){
                $parent = $arrayParents[1];
                $fullParent = $arrayParents[0].".".$arrayParents[1];
            }else{
                $parent = $arrayParents[0];
                $fullParent = $parent;
            }

            $joined[]=$parent;
            $onParent = $arrayJoins[0];
            $onMe = $arrayJoins[1];
            $parentClassString = "\App\Models\BasicModels\\$parent";
    
            if( !class_exists($parentClassString) ){
                continue;
            }
            if( !isset($kembar[$parent]) ){
                $kembar[$parent] = 1;
            }else{
                $kembar[$parent] = $kembar[$parent]+1;
            }
            $parentName = $fullParent;
            if($kembar[$parent]>1){
                $parentName = "$fullParent AS ".$parent.(string)$kembar[$parent];
                $onParentArray=explode(".",$onParent);
                if( count( $onParentArray )>2 ){
                    $onParent = $onParentArray[1].".".$onParentArray[2];
                }
                $onParent = str_replace($parent,$parent.(string)$kembar[$parent],$onParent);
            }
            $model = $model->leftJoin($parentName,$onParent,"=",$onMe);
            $parentClass = new $parentClassString;
            if($kembar[$parent]>1){
                $parentName = $parent.(string)$kembar[$parent];
            }
            foreach($parentClass->columns as $column){
                $colTemp        = "$parentName.$column AS ".'"'.$parentName.".".$column.'"';
                $fieldSelected[]= $colTemp;
                $allColumns[]   = "$parentName.$column";
            }
        }
        if($joinMax>0){
            _joinRecursive($joinMax,$kembar,$fieldSelected,$allColumns,$joined,$model,$parent,$params);
        }
    }
    if($params->selectfield || req('selectfield')){
        $rawSelectFields = req('selectfield') ?? $params->selectfield;
        $selectFields = str_replace(["this.","\n","  ","\t"],["$table.","","",""], $rawSelectFields);
        $selectFields = explode(",", $selectFields);
        $fieldSelected= $selectFields;
    }
    
    if( isset($params->addSelect) && $params->addSelect!=null ){
        $addSelect = str_replace("this.","$table.",strtolower($params->addSelect));
        $fieldSelected = array_merge( $fieldSelected, explode(",",$addSelect));
    }
    
    if( $params->addJoin || req('addjoin') ){
        $addJoin = req('addjoin') ?? $params->addJoin;
        $joiningString = str_replace("this.","$table.",strtolower($addJoin));
        $joins = explode( ",", $joiningString );
        foreach($joins as $join){
            if(strpos( $join, " and ")!==FALSE){
                $join = explode(" and ",$join);
                $joinedTable=explode(".",$join[0])[0];
                $model = $model->leftJoin($joinedTable, function($q)use($join){
                    foreach($join as $statement){
                        $statement = str_replace(" ","",$statement);
                        $explodes = explode(".",$statement);
                        if( count($explodes)>2 ){
                            $parent = "{$explodes[0]}.{$explodes[1]}";
                        }else{
                            $parent = $explodes[0];
                        }
                        $onParent = explode("=",$statement)[0];
                        $onMe = explode("=",$statement)[1];
                        $q->on($onParent,"=",$onMe);
                    }
                });
            }else{
                $candParent = explode("=",$join)[0];
                $explodes = explode(".",$candParent);
                if( count($explodes)>2 ){
                    $parent = $explodes[0].".".$explodes[1];
                }else{
                    $parent = $explodes[0];
                }
                $onParent = explode("=",$join)[0];
                $onMe = explode("=",$join)[1];
                $model = $model->leftJoin($parent,$onParent,"=",$onMe);
            }
        }
    }
    
    if(method_exists($modelExtender, "extendJoin")){
        $model = $modelExtender->extendJoin($model);
    }
    
    if( isRoute('read_list_detail') ){
        $parentModelName = @app()->request->route()[2]['modelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['id'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }

    if( isRoute('read_list_sub_detail') ){
        $parentModelName = @app()->request->route()[2]['detailmodelname'];
        $parentModel = getCustom($parentModelName);
        $parentTable = getTableOnly($parentModel->getTable());
        $parentId = @app()->request->route()[2]['detailid'];
        if($parentModel->useEncryption){
            $parentId = $parentModel->decrypt($parentId);
        }

        $model = $model->where(function($q)use( $parentTable, $parentId ){
            $q->where( $parentTable."_id", $parentId );
        });
    }
    
    $finalObj = (object)[
        'type'=>'find', 'caller'=>null
    ];

    $data = $model->scopes($givenScopes)->select(DB::raw(implode(",",$fieldSelected) ))->final($finalObj)->find($idToFind);
    if( !$data ){
        abort(404, json_encode([
            'message'=>"Maaf Data tidak ditemukan"
        ]));
    }
    $data=$data->toArray();
    $keys=array_keys($data);
    foreach($keys as $key){
        if( count(explode(".", $key))>2 ){
            $newKeyArray = explode(".", $key);
            $newKey = $newKeyArray[1].".".$newKeyArray[2];
            $data[$newKey] = $data[$key];
            unset($data[$key]);
        }
    }
    $data = reformatDataResponse($data);
    if(method_exists($modelExtender, "transformRowData") && (!req("transform") || (req("transform") && req("transform")=='true'))){
        $data = $modelExtender->transformRowData($data);
    }
    if($params->single){
        return $data;
    }
    
    $id = $idToFind;
    foreach($pureModel->details as $detail){
        $detailArray = explode(".",$detail);
        $detailClass = $detail;
        if( count($detailArray)>1 ){
            $detailClass = $detailArray[1];
        }
        // $modelCandidate = "\App\Models\CustomModels\\$detailClass";
        // $model          = new $modelCandidate;
        $model      = getCustom($detailClass);
        $fk_child = array_filter($model->joins,function($join)use($pureModel){
            $parentString       = explode("=",$join)[0];
            $parentArray        = explode(".",$parentString);
            $parentNameString   = $parentArray[ 0 ] ;
            if( count( $parentArray )>2 ){
                $parentNameString   = $parentArray[ 0 ].".".$parentArray[ 1 ] ;
            }
            if( $parentNameString == $pureModel->getTable() ){
                return $parentNameString;
            }
        });
        $fk_child = explode( "=",array_values($fk_child) [ 0 ] )[1];
        $p = (Object)[];
        $p->where_raw   = "$fk_child=$id";
        $p->order_by    = null;
        $p->order_type  = null;
        $p->order_by_raw= null;
        $p->search      = null;
        $p->searchfield = null;
        $p->selectfield = null;
        $p->paginate    = null;
        $p->page        = null;
        $p->addSelect   = null;
        $p->addJoin     = null;
        $p->join        = true;
        $p->joinMax     = 0;
        $p->group_by    = null;
        $p = $model->overrideGetParams($p);
        $p->caller      = $pureModel->getTable();
        $detailArray = explode('.', $detail);

        $data[count($detailArray)==1? $detail : $detailArray[1] ]  = $model->customGet($p);
    }
    
    $keys   =   array_keys($data);
    foreach($keys as $key){
        if( count(explode(".", $key))>2 ){
            $newKeyArray = explode(".", $key);
            $newKey = $newKeyArray[1].".".$newKeyArray[2];
            $data[$newKey] = $data[$key];
            unset($data[$key]);
        }
    }
    $func="transformArrayData";
    if( method_exists( $modelExtender, $func )  ){
        $newFixedData = $modelExtender->$func( $data );
        $fixedData = gettype($newFixedData)=='array' ? $newFixedData : $data;
        $data   = $fixedData;
    }
    return $data;
}

function sanitizeString( $string, $force_lowercase = true, $anal = false ) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
                "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                "â€”", "â€“", ",", "<",  ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return $clean;
}
function _uploadexcel($model, $request)
{
    $data = \Excel::toArray( null,$request->file);
      	$rows = $data[0];
        $headings = $rows[0];
        $forbiddenHeadings = [];
      	$bulkData = [];
        $invalidRows = [];
        foreach($headings as $col => $heading){
            if( !in_array($heading,$model->columns) ){
                $forbiddenHeadings[] = $heading;
            }
        }
        if(count($forbiddenHeadings)>0){
            return response()->json(["invalid_columns"=>$forbiddenHeadings],400);
        }
        try{
            DB::beginTransaction();
            $hitung=0;
            foreach($rows as $baris => $array){
              if($baris==0){ continue; }
                $row = [];
                foreach($headings as $col => $heading){
                    $row[$heading] = $array[$col];
                }
                array_merge($row,["created_at"=>\Carbon\Carbon::now(),"updated_at"=>\Carbon\Carbon::now() ]);
                    $validator = \Validator::make($row, $model->importValidator);
                  if ( $validator->fails()) {
                      foreach($validator->errors()->all() as $error){
                          $invalidRows[] = "[INVALID]".$error." in row[$baris]";
                      }
                  }
                $bulkData[]=$row;
                $hitung++;
                if($hitung>999){
                    if( count($invalidRows)>0 ){
                      return response()->json($invalidRows,400);
                    }
                    $hitung=0;                 
                    DB::table($model->getTable())->insert($bulkData);
                    $bulkData=[];
                }
           }
            if( count($invalidRows)>0 ){
              return response()->json($invalidRows,400);
            }
            DB::table($model->getTable())->insert($bulkData);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json([$e->getMessage()],400);
        }
        DB::commit();
      	return response()->json(["status"=>"success","data"=>$bulkData],200);
}
function uploadfile($model, $req, $uniqueName=null, $extension=true ){
    $modelArray = explode("\\",get_class($model));
    $modelName = end($modelArray);
    $validator = Validator::make($req->all(), [
        'file' => 'max:25000|mimes:pdf,doc,docx,xls,xlsx,odt,odf,zip,tar,tar.xz,tar.gz,rar,jpg,jpeg,png,bmp,mp4,mp3,mpg,mpeg,mkv,3gp,ods'
    ]);
    if ( $validator->fails()) {
        return $validator->errors()->all();
    }
    $code= Carbon::now()->format('his').crc32(uniqid());
    if($uniqueName){
        $fileName = $uniqueName.($extension?$req->file->extension():'');
    }else{
        $fileName = $req->filename?$req->filename.($extension?$req->file->extension():''):sanitizeString($req->file->getClientOriginalName());
        $fileName = $code."_".$fileName;
    }
    Storage::disk('uploads')->putFileAs(
        $modelName, $req->file, $fileName
    );
    return url("/uploads/$modelName/".$fileName);
}
function ff($data,$id=""){
    $channel=env("LOG_CHANNEL",env('APP_NAME',uniqid()));
    $client = new \GuzzleHttp\Client();
    $socketServer=env("LOG_SENDER");
    try{
        if(!in_array(gettype($data),["object","array"])){
            $data = [$data];
        }
        $dtrace = (object)debug_backtrace(1,true)[0];
      	// ff($dtrace['class'],$dtrace['function']);
        $data = is_object($data)?array($data):$data;
        $filename = explode("/",$dtrace->file);
        $data = array_merge($data,[ "debug_id"=>$id." [".str_replace(".php","",end($filename))."-$dtrace->line]"]);        
        $client->post(
            "$socketServer/$channel",
            [
                'form_params' => $data
            ]
        );
    }catch(\Exception $e){
        $client->post(
            "$socketServer/$channel",
            [
                'form_params' => ["debug_error"=>$e->getMessage(),"debug_id"=>$id]
            ]
        );
    }
}
function reformatData($arrayData,$model=null){
    $dataKey=["date","tgl","tanggal","_at","etd","eta"];
    $dateFormat = env("FORMAT_DATE_FRONTEND","d/m/Y");
    foreach($arrayData as $key=>$data){
        $datatype=getDataType($model,$key);
        if(is_array($data)){
            continue;
        }
        $isDate=in_array($datatype,['date','datetime','timestamp']);
        if($isDate){
            try{
                $newData = Carbon::createFromFormat($dateFormat, $data)->format('Y-m-d');
                $arrayData[$key] = $newData;   
            }catch(Exception $e){
                
            }
        }elseif( gettype($data)=='boolean' && !$data ){
            $arrayData[$key] = "false";
        }elseif( str_replace(["null","NULL"," "],["","",""],$data)==''){
            $arrayData[$key] = null;
        }
    }
    return $arrayData;
}
function reformatDataResponse($arrayData){
    $dataKey=["date","tgl","tanggal","_at","etd","eta"];
    $dateFormat = env("FORMAT_DATE_FRONTEND","d/m/Y");
    foreach($arrayData as $key=>$data){
        $isDate=false;
        foreach($dataKey as $dateString){
            if(strpos(strtolower($key),$dateString)!==false && count(explode("-",$data))>2){
                $isDate=true;
                break;
            }
        }
        if($isDate){
            try{
                $newData = Carbon::createFromFormat("Y-m-d", $data)->format($dateFormat);
                $arrayData[$key] = $newData;
            }catch(Exception $e){}
        }
    }
    return $arrayData;
}

function getReportHeader($model,$params=[]){
    $p = (object)array_merge([
        "where_raw"=>null,
        "order_by"=>null,
        "order_type"=>"ASC",
        "page"=>"1",
        "order_by_raw"=>null,
        "search"=>null,
        "searchfield"=>null,
        "selectfield"=>null,
        "paginate"=>9999,
        "join"=>true,
        "caller"=>null,
        "joinMax"=>3
    ], $params);
    return $model->customGet($p);
}

function js($script){
    return base64_encode(base64_encode($script)); 
}

function str_replace_once($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

function getArrayFromString($formula){     
    $arr=[];   	
    $string="";
    foreach(str_split($formula) as $i => $char ){          	
        if( in_array($char,["-","+","/","*"]) ){
            $arr[]=$string;
            $string="";
            $arr[]=$char;
        }else{
            $string.=$char;
        }
    }
    $arr[]=$string;
    return $arr;
};
function testingformula(&$formula){
    $arr=getArrayFromString($formula);
    foreach($arr as $index => $calc){
        if(in_array($calc,["/","*"])){
        if($calc=='/'){
            $hasil=$arr[$index-1]/$arr[$index+1];
            $formula=str_replace_once($arr[$index-1].$arr[$index].$arr[$index+1],$hasil,$formula);
        }elseif($calc=='*'){
            $hasil=$arr[$index-1]*$arr[$index+1];
            $formula=str_replace_once($arr[$index-1].$arr[$index].$arr[$index+1],$hasil,$formula);
        }
    break;
    }
}
    if(strpos($formula,"/")!==false ||  strpos($formula,"*")!==false){
        testingformula($formula);
    }
}
function mathString($formula){
    $formula = str_replace(" ","",$formula);
    testingformula($formula);
    $arr = getArrayFromString($formula);
    $hasil = 0;
    foreach($arr as $index => $calc){
        if($index%2==0){
        if($index==0){
            $hasil=$calc;
        }else{
            if($arr[$index-1]=='-'){
                $hasil-=$calc;
            }elseif($arr[$index-1]=='+'){
                $hasil+=$calc;
            }elseif($arr[$index-1]=='/'){
                $hasil/=$calc;
            }elseif($arr[$index-1]=='*'){
                $hasil*=$calc;
            }
        }          
        }
    }
    return $hasil;
}
function getOutStanding($model, $row,$formula){
    $formula = str_replace(" ","",$formula);
    $arr = getArrayFromString($formula);
    $simpanan=[];
    foreach($arr as $index => $mathString){
        if($index%2==0 && !is_numeric($mathString)){
            $arrString = explode(".",$mathString);
            if(count($arrString)==1){
                $formula = str_replace_once($mathString, $row[$mathString], $formula);
            }else{
                $heirs = $model->heirs;
                $var = "\App\Models\BasicModels\\".$arrString[0];
                $simpanan[$arrString[0]]=[];
                $child = new $var;
                $childJoins = $child->joins;
                $simpanan[$arrString[0]]['heirs']=$child->heirs;
                if(in_array($arrString[0], $heirs)){
                    $whereKey = "";
                    foreach($childJoins as $join){
                        if( strpos($join,$model->getTable().".id") !==false ){
                            $whereKey = explode("=", $join)[1];
                            break;
                        }
                    };
                    $data = $child->selectRaw("sum($arrString[1]) as sumqty")
                            ->where($whereKey, $row['id'])
                            ->first();
                    $sum = $data->sumqty?$data->sumqty:0;
                    $formula = str_replace_once($mathString, $sum, $formula);
                    if($sum>0){
                        $simpanan[$arrString[0]]['data']=$child->select("id")
                            ->where($whereKey, $row['id'])->get()->toArray();
                    }else{
                        $simpanan[$arrString[0]]['data']=[];
                    }
                }else{
                    foreach($simpanan as $key => $keys ){                          
                        if(in_array($arrString[0], $keys['heirs'])){                            
                            $whereKey = "";
                            foreach($childJoins as $join){
                                if( strpos($join,"$key.id") !==false ){
                                    $whereKey = explode("=", $join)[1];
                                    break;
                                }
                            };
                            $ids=[];
                            foreach($keys['data'] as $row){
                                $ids[] = $row["id"];
                            }
                            $data = $child->selectRaw("sum($arrString[1]) as sumqty")
                                    ->whereIn($whereKey, $ids)
                                    ->first();
                            $sum = $data->sumqty?$data->sumqty:0;
                            $formula = str_replace_once($mathString, $sum, $formula);
                            if($sum>0){
                                $simpanan[$arrString[0]]['data']=$child->select("id")
                                    ->whereIn($whereKey, $ids)->get()->toArray();
                            }else{
                                $simpanan[$arrString[0]]['data']=[];
                            }
                            break;                            
                        }
                    }
                }
            }
        }
    }
    return mathString($formula);
};
function getDataType($model,$col){
    $columns = $model->columnsFull;
    foreach($columns as $column){
        $column = explode(":", $column);
        if($column[0]==$col){
            return $column[1];
            break;
        }
    }
    return null;
}
function Api(){
    return new \Api(new Illuminate\Http\Request(),true);
}
function SendEmail($to,$subject,$template){
    try{
        \Mail::to($to)->send(new \MailTemplate($subject, $template ));         
    }catch(\Exception $e){
        return $e->getMessage();
    }
    return true;
}
function SendEmailAsync($to,$subject,$template){
    try{
        \Queue::push(new App\Jobs\SendEmail([
            "to"        => $to,
            "subject"   => $subject,
            "template"  => $template
        ]));
    }catch(\Exception $e){
        return $e->getMessage();
    }
    return true;
}
function Async($class,$func,$args){
    dispatch(new \App\Jobs\Background(get_class($class),$func,$args));
}
function getBasic($name){
    $string = "\App\Models\BasicModels\\$name";
    return class_exists( $string )?new $string:null;
}
function getCustom($name){

    if( config("custom_$name") ){
        return config("custom_$name");
    }
    
    $string = "\App\Models\CustomModels\\$name";
    $calledClass = class_exists( $string )?new $string:null;
    if($calledClass){
        config( ["custom_$name" => $calledClass] );
    }
    return $calledClass;
}

function getRoute(){
    return @app()->request->route()[1]['as'];
}
function isRoute($val){
    return @app()->request->route()[1]['as']==$val;
}

function getRawData($query){
    try{        	
        $res = (array)\DB::select("$query limit 1")[0];
        return array_values($res)[0];
    }catch(\Exception $e){
        return null;
    }
}

function renderpdf( $config,$arrayData,$pageConfig=[],$type="pdf" ){
    $client = new \GuzzleHttp\Client();    
    $pageConfig = array_merge(["break"=>false,"title"=>"documentpdf","fontsize"=>12,"size"=>"A4","orientation"=>"P","preview"=>false],
    $pageConfig);
    $payLoad = [
        'config'=>$config,
        'data'=>$arrayData,
        'type'=>$type
    ];
    $payLoad = array_merge($payLoad,$pageConfig);
    try{    
        $response = $client->post(
            env('HTMLPDF_RENDERER'),
            [
                'json' => $payLoad,
                'headers' => [
                    'Authorization' => 'Bearer 57aa62501a7fe0d3b71de5712cdb1998',
                    'Accept' => 'application/json',
                ]
            ],
        );
    }catch(\Exception $e){
        return $e->getMessage()." ".$e->getLine();
    }
    return response($response->getBody())
    ->withHeaders([
        'Content-Type' => $type=='html'?'text/html':'application/pdf',//'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',//'text/html',//'application/pdf',
        'Pragma' => 'public',
        'Content-Disposition' => "inline; filename=".$pageConfig['title'].".pdf",//"attachment;filename=$judulsaja.xlsx",//'inline; filename="coba.pdf"'', // 
        'Cache-Control'=>'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
        'Last-Modified'=>gmdate('D, d M Y H:i:s').' GMT',
        'Expires'=>'Mon, 26 Jul 1997 05:00:00 GMT'
    ]);
}

function renderHTML( $config,$arrayData,$pageConfig=["break"=>false,"title"=>"documenthtml","size"=>"A4","orientation"=>"P","preview"=>false] ){
   return renderPDF( $config,$arrayData,$pageConfig,"html" );
}
function renderXLS( $config,$arrayData,$pageConfig=[] ){
    $client = new \GuzzleHttp\Client();
    try{    
        $pageConfig = array_merge(["break"=>false,"fontsize"=>11,"sheetname"=>"header","title"=>"documentOffice2007","size"=>"A4","orientation"=>"P"],
                        $pageConfig);
        $payLoad = [
            'config'=>$config,
            'data'=>$arrayData,
        ];
        $payLoad = array_merge($payLoad,$pageConfig);
        $response = $client->post(
            env('XLS_RENDERER'),
            [
                'json' => $payLoad,
                'headers' => [
                    'Authorization' => 'Bearer 57aa62501a7fe0d3b71de5712cdb1998',
                    'Accept' => 'application/json',
                ]
            ],
        );
    }catch(\Exception $e){
        return $e->getMessage()." ".$e->getLine();
    }
    
    return response($response->getBody())
    ->withHeaders([
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Pragma' => 'public',
        'Content-Disposition' => "attachment;filename=".$pageConfig['title'].".xlsx",
        'Cache-Control'=>'private, must-revalidate, post-check=0, pre-check=0, max-age=1',
        'Last-Modified'=>gmdate('D, d M Y H:i:s').' GMT',
        'Expires'=>'Mon, 26 Jul 1997 05:00:00 GMT'
    ]);
}

function setLog($data){
    umask(0000);
    $dtrace = (object)debug_backtrace(1,true)[0];
    $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
    $filename = str_replace(".php",".json",end($filenameArr));
    $path = base_path("logs");
    if( ! File::exists($path) ){
        File::makeDirectory( $path, 493, true);
    }
    $agent = new \Jenssegers\Agent\Agent;
    $data = is_array($data) ? $data:(array)$data;
    $data['___client_timestamp'] = \Carbon::now();
    $data['___client_address'] = app()->request->ip();
    $data['___client_browser'] = $agent->browser();
    $data['___client_platform'] = $agent->platform();
    return File::put("$path/$filename",json_encode($data));
}
function getLog($filename=null,$string=false){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $path = base_path("logs/$filename");
    if( ! File::exists($path) ){
        return null;
    }
    if($string){
        return File::get($path);
    }
    return json_decode(File::get($path),true);
}
function getTest($filename=null,$string=false){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $table = $filename;
    $filename = Str::camel(ucfirst($filename));
    $path = base_path("tests/$filename"."Test.php");
    if( ! File::exists($path) ){
        return str_replace( [
            "___class___","__resource__"
        ],[
            $filename, $table
        ],File::get( base_path("templates/test.stub") ) );
    }
    if($string){
        return File::get($path);
    }
    return File::get($path);
}
function removeLog($filename=null){
    if($filename===null){
        $dtrace = (object)debug_backtrace(1,true)[0];
        $filenameArr = explode(php_uname('s')=='Linux'?"/":"\\",$dtrace->file);
        $filename = str_replace(".php",".json",end($filenameArr));
    }
    $path = base_path("logs/$filename");
    if( ! File::exists($path) ){        
        return false;
    }
    return File::delete("$path");
}

function req($key=null){
    // if(app()->request->method()=="GET" ){
    //     $data = (object)config('request');
    // }else{
    //     $data = json_decode(file_get_contents('php://input'));
    // }
    $data = json_decode(json_encode( config('request') ));
    if($key!==null){
        return isset($data->$key)? $data->$key : null;
    }
    return $data;
}
function isJson($args) {
    json_decode($args);
    return (json_last_error()===JSON_ERROR_NONE);
}
function getDriver(){
    return Schema::getConnection()->getDriverName();
}
function isVersion( $var ){
    return (strpos(app()->version(), "^$var.")!==false);
}

/**
 * Casts from request param for all basic models
 */
function getCastsParam():array{
    $casters = [];
    if(req('casts')){
        try{
            $rawCasters = explode(",", req('casts'));
        
            foreach($rawCasters as $key => $caster){
                $casterArr = explode(":", $caster, 2);
                $casters[$casterArr[0]] = $casterArr[1];
            }
        }catch(\Exception $e){
            abort(500,json_encode(["error"=>["casts parameter has wrong format"]]));
        }
    }
    return $casters;
}

/**
 * Get error exception postgresql
 */
function pgsqlParseError( string $msg ):string {
    if(strpos($msg,'SQLSTATE')!==false){
        try{
            $errors = explode("ERROR: ",$msg,2);
            $exception = explode("\n",$errors[1],2);
            $msg = $exception[0];
        }catch(\Exception $e){
            ff($e->getMessage());
        }
    }
    return $msg;
}

function getTableOnly(string $tableName){
    if( Str::contains($tableName, ".") ){
        $exploded = explode(".", $tableName);
        return end($exploded);
    }
    return $tableName;
}

function getModelNameByLevel( int $level = 1 ){
    $name = 'modelname';
    if( $level === 2 ){
        $name = 'detailmodelname';
    }elseif( $level === 2 ){
        $name = 'subdetailmodelname';
    }else{
        return null;
    }

    return @app()->request->route()[2][ $name ];
}

function saveFileToCache( $modelName, $field, $file, $user_id='anonymous', $seconds = 1800 ){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($file->getClientOriginalName());
    $path =  $file->getRealPath();
    $blob = base64_encode(\File::get($path));
    \Cache::put( $key, $blob, $seconds);
    
    return $key;
}

function pullFileFromCache($modelName, $field, $filename, $user_id='anonymous'){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($filename);

    $cacheContent = \Cache::get( $key );
    if(!$cacheContent){
        return null;
    }
    
    $contents = base64_decode( $cacheContent );
    return $contents;
}

function moveFileFromCache($modelName, $field, $filename, $user_id='anonymous', $oldFile = null ){
    $key = $modelName."_".$field."_".$user_id."_".sanitizeString($filename);
    $contents = pullFileFromCache($modelName, $field, $filename, $user_id);

    if( !$contents ){
        if(!$oldFile){
            abort(422, json_encode(['message'=>"File `$filename` tidak ada atau telah melebihi 30 menit, upload ulang dan segera submit"]));
        }else{
            return $oldFile;
        }
    }
    
    $code = \Carbon::now()->format('his').crc32(uniqid());
    $fixedFileName = $code.":::".$filename;
    if(!File::exists(public_path("uploads/$modelName"))){
        umask(0000);
        File::makeDirectory( public_path("uploads/$modelName"), 493, true);
    }

    //  remove old file
    if( $oldFile && File::exists( public_path("uploads/$modelName/$oldFile") ) ){
        File::delete( public_path( "uploads/$modelName/$oldFile" ) );
    }

    $path = \File::put(public_path("uploads/$modelName/$fixedFileName"), $contents);
    return $fixedFileName;
}