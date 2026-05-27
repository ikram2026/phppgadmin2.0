<?php

declare(strict_types=1);

/*
 * phpPgAdmin 2.0
 * Modernized Decorator System
 * Compatible with:
 * - PHP 8.4+
 * - PostgreSQL 18+
 */

/*

|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function field(string $fieldName, mixed $default = null): FieldDecorator
{
    return new FieldDecorator($fieldName, $default);
}

function merge(...$arrays): ArrayMergeDecorator
{
    return new ArrayMergeDecorator($arrays);
}

function concat(...$values): ConcatDecorator
{
    return new ConcatDecorator($values);
}

function callback(callable $callback, mixed $params = null): CallbackDecorator
{
    return new CallbackDecorator($callback, $params);
}

function ifempty(
    mixed $value,
    mixed $empty,
    mixed $full = null
): IfEmptyDecorator {
    return new IfEmptyDecorator($value, $empty, $full);
}

function url(
    mixed $base,
    mixed $vars = null,
    mixed ...$additionalVars
): UrlDecorator {

    if (!empty($additionalVars)) {

        array_unshift($additionalVars, $vars);

        return new UrlDecorator(
            $base,
            new ArrayMergeDecorator($additionalVars)
        );
    }

    return new UrlDecorator($base, $vars);
}

function replace(
    string $str,
    array $params
): ReplaceDecorator {
    return new ReplaceDecorator($str, $params);
}

/*

|--------------------------------------------------------------------------
| Value Resolution
|--------------------------------------------------------------------------
*/

function value(
    mixed $var,
    array $fields,
    ?string $escape = null
): mixed {

    if ($var instanceof Decorator) {
        $val = $var->value($fields);
    } else {
        $val = $var;
    }

    // Modernization Fix: Convert non-string database properties (ints/objects) safely to strings
    if (is_object($val) && method_exists($val, '__toString')) {
        $val = (string)$val;
    } elseif (is_scalar($val)) {
        $val = (string)$val;
    }

    if (!is_string($val)) {
        return $val;
    }

    return match ($escape) {

        'xml' => strtr($val, [
            '&' => '&amp;',
            "'" => '&apos;',
            '"' => '&quot;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]),

        'html' => htmlspecialchars(
            $val,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        ),

        'url' => rawurlencode($val),

        default => $val,
    };
}

function value_xml(
    mixed $var,
    array $fields
): mixed {
    $resolved = value($var, $fields, 'xml');
    return is_array($resolved) ? implode('', $resolved) : (string) $resolved;
}

function value_xml_attr(
    string $attr,
    mixed $var,
    array $fields
): string {

    $resolved = value($var, $fields, 'xml');
    $val = is_array($resolved) ? implode('', $resolved) : (string) $resolved;

    if ($val === '') {
        return '';
    }

    return sprintf(
        ' %s="%s"',
        $attr,
        $val
    );
}

function value_url(
    mixed $var,
    array $fields
): mixed {
    return value($var, $fields, 'url');
}

/*

|--------------------------------------------------------------------------
| Base Decorator
|--------------------------------------------------------------------------
*/

abstract class Decorator
{
    protected mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function value(array $fields): mixed
    {
        return $this->value;
    }

    /**
     * Magic method to allow casting decorators to strings automatically.
     */
    public function __toString(): string
    {
        global $data;
        $fields = is_object($data) && isset($data->fields) ? $data->fields : [];
        $resolved = $this->value($fields);
        return is_array($resolved) ? implode('', $resolved) : (string) $resolved;
    }
}

/*

|--------------------------------------------------------------------------
| FieldDecorator
|--------------------------------------------------------------------------
*/

#[AllowDynamicProperties]
class FieldDecorator extends Decorator
{
    protected string $fieldName;
    protected mixed $defaultValue = null;

    public function __construct(
        string $fieldName,
        mixed $default = null
    ) {
        parent::__construct(null);
        $this->fieldName = $fieldName;
        $this->defaultValue = $default;
    }

    public function value(array $fields): mixed
    {
        return $fields[$this->fieldName]
            ?? $this->defaultValue;
    }
}

/*

|--------------------------------------------------------------------------
| ArrayMergeDecorator
|--------------------------------------------------------------------------
*/

class ArrayMergeDecorator extends Decorator
{
    protected array $arrays;

    public function __construct(array $arrays)
    {
        parent::__construct(null);
        $this->arrays = $arrays;
    }

    public function value(array $fields): array
    {
        $merged = [];

        foreach ($this->arrays as $var) {
            $resolved = value($var, $fields);
            if (is_array($resolved)) {
                $merged = array_merge($merged, $resolved);
            }
        }

        return $merged;
    }
}

/*

|--------------------------------------------------------------------------
| ConcatDecorator
|--------------------------------------------------------------------------
*/

class ConcatDecorator extends Decorator
{
    protected array $values;

    public function __construct(array $values)
    {
        parent::__construct(null);
        $this->values = $values;
    }

    public function value(array $fields): string
    {
        $result = '';

        foreach ($this->values as $var) {
            $resolved = value($var, $fields);
            $result .= is_array($resolved) ? implode('', $resolved) : (string) $resolved;
        }

        return trim($result);
    }
}

/*

|--------------------------------------------------------------------------
| CallbackDecorator
|--------------------------------------------------------------------------
*/

class CallbackDecorator extends Decorator
{
    protected $callback;
    protected mixed $params;

    public function __construct(
        callable $callback,
        mixed $params = null
    ) {
        parent::__construct(null);
        $this->callback = $callback;
        $this->params = $params;
    }

    public function value(array $fields): mixed
    {
        return call_user_func(
            $this->callback,
            $fields,
            $this->params
        );
    }
}

/*

|--------------------------------------------------------------------------
| IfEmptyDecorator
|--------------------------------------------------------------------------
*/

class IfEmptyDecorator extends Decorator
{
    protected mixed $checkValue;
    protected mixed $emptyValue;
    protected mixed $fullValue;

    public function __construct(
        mixed $value,
        mixed $empty,
        mixed $full = null
    ) {
        parent::__construct(null);
        $this->checkValue = $value;
        $this->emptyValue = $empty;
        $this->fullValue = $full;
    }

    public function value(array $fields): mixed
    {
        $resolved = value($this->checkValue, $fields);

        if (empty($resolved)) {
            return value($this->emptyValue, $fields);
        }

        return $this->fullValue !== null
            ? value($this->fullValue, $fields)
            : $resolved;
    }
}

/*

|--------------------------------------------------------------------------
| UrlDecorator
|--------------------------------------------------------------------------
*/

#[AllowDynamicProperties]
class UrlDecorator extends Decorator
{
    protected mixed $baseUrl;
    protected mixed $queryVars;

    public function __construct(
        mixed $base,
        mixed $queryVars = null
    ) {
        parent::__construct(null);
        $this->baseUrl = $base;
        $this->queryVars = $queryVars;
    }

    public function value(array $fields): string
    {
        $resolvedBase = value($this->baseUrl, $fields);
        $url = is_array($resolvedBase) ? implode('', $resolvedBase) : (string) $resolvedBase;

        if ($url === '') {
            return '';
        }

        if (!empty($this->queryVars)) {
            $queryVars = value($this->queryVars, $fields);
            if (is_array($queryVars)) {
                $queryString = http_build_query($queryVars, '', '&', PHP_QUERY_RFC3986);
                if (!empty($queryString)) {
                    $url .= '?' . $queryString;
                }
            }
        }

        return $url;
    }
}

/*

|--------------------------------------------------------------------------
| ReplaceDecorator
|--------------------------------------------------------------------------
*/

class ReplaceDecorator extends Decorator
{
    protected string $string;
    protected array $params;

    public function __construct(string $str, array $params)
    {
        parent::__construct(null);
        $this->string = $str;
        $this->params = $params;
    }

    public function value(array $fields): string
    {
        $str = $this->string;
        foreach ($this->params as $k => $v) {
            $resolvedVal = value($v, $fields);
            $str = str_replace($k, is_array($resolvedVal) ? implode('', $resolvedVal) : (string) $resolvedVal, $str);
        }
        return $str;
    }
}

