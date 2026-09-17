<?php

namespace DVC\ContaoCustomCatalog\Util;

final class Coordinates
{
    public static function pair(mixed $lat, mixed $lng): ?array
    {
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }
        $lat = (float) $lat;
        $lng = (float) $lng;
        return is_finite($lat) && is_finite($lng) && abs($lat) <= 90 && abs($lng) <= 180
            ? [$lat, $lng] : null;
    }

    public static function fromRaw(mixed $raw): ?array
    {
        if (is_string($raw)) {
            if (str_starts_with($raw, 'a:')) {
                $raw = @unserialize($raw, ['allowed_classes' => false]);
            } else {
                $raw = explode(',', $raw);
            }
        }
        return is_array($raw) && count($raw) === 2
            ? self::pair($raw[0] ?? null, $raw[1] ?? null) : null;
    }

    public static function fromModel(object $model): ?array
    {
        return self::fromRaw($model->address ?? null)
            ?? self::pair($model->address_lat ?? null, $model->address_lng ?? null);
    }
}
