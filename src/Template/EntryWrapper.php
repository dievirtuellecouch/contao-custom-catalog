<?php

namespace DVC\ContaoCustomCatalog\Template;

class EntryWrapper
{
    private object $model;

    public function __construct(object $model)
    {
        $this->model = $model;
    }

    public function __get(string $key)
    {
        return $this->model->{$key} ?? null;
    }

    public function field(string $name): FieldWrapper
    {
        $value = $this->model->{$name} ?? null;
        $options = [];
        if ($name === 'address') {
            $options = [
                'street'  => $this->model->address_street ?? null,
                'zipcode' => $this->model->address_zipcode ?? null,
                'city'    => $this->model->address_city ?? null,
            ];
        }
        return new FieldWrapper($value, $options, $name);
    }

    public function links(string $type): object
    {
        // Keep for backward compatibility; prefer module-built base paths in templates
        if ($type === 'detail') {
            $alias = $this->model->alias ?: $this->model->id;
            $base = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
            return (object) ['url' => sprintf('%s/%s', $base, $alias)];
        }
        return (object) [];
    }
}

class FieldWrapper
{
    public function __construct(private mixed $value, private array $options = [], private ?string $fieldName = null)
    {
    }

    public function html(): string
    {
        return (string) $this->value;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function option(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }
}
