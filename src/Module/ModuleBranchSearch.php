<?php

namespace DVC\ContaoCustomCatalog\Module;

use Contao\Environment;
use Contao\Module;
use Contao\RequestToken;
use Contao\System;

class ModuleBranchSearch extends Module
{
    protected $strTemplate = 'mod_dvc_cc_branch_search';

    protected function compile(): void
    {
        // CSRF token
        try {
            $this->Template->requestToken = (string) \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        } catch (\Throwable) {
            $this->Template->requestToken = method_exists(RequestToken::class, 'get') ? RequestToken::get() : '';
        }
        // Always reload this module itself to keep the search independent from the list module
        $this->Template->reloadElement = sprintf('mod::%d', (int) $this->id);
        // Do not render initial list output; results should only appear after search
        $this->Template->initialList = '';
        $this->Template->formId = sprintf('cc_filter_%d', (int) $this->id);
        try {
            $request = System::getContainer()->get('request_stack')->getCurrentRequest();
            $this->Template->action = $request?->getPathInfo() ?? (string) (Environment::get('requestUri') ?? '');
        } catch (\Throwable) {
            $this->Template->action = (string) (Environment::get('requestUri') ?? '');
        }
        $this->Template->searchPlaceholder = 'PLZ oder Ort';
        $this->Template->defaultRadius = (string) ($this->dvc_cc_default_radius ?? '20');
        $this->Template->mapsApiKey = (string) ($this->dvc_cc_maps_api_key ?? ($_ENV['GOOGLE_MAPS_API_KEY'] ?? $_SERVER['GOOGLE_MAPS_API_KEY'] ?? ''));

        // Build detail URL pattern based on module jumpTo target page
        $basePath = '';
        $detailUrlPattern = '';
        try {
            $req = \Contao\System::getContainer()->get('request_stack')->getCurrentRequest();
            $host = ($req?->getSchemeAndHttpHost() ?? '').($req?->getBaseUrl() ?? '');
            if ((int)($this->jumpTo ?? 0) > 0) {
                $page = \Contao\PageModel::findByPk((int)$this->jumpTo);
                if ($page) {
                    $url = (string) $page->getFrontendUrl();
                    $basePath = rtrim($host.$url, '/');
                }
            }
            if ($basePath === '') {
                $basePath = rtrim($host.($req?->getPathInfo() ?? ''), '/');
            }
            $detailUrlPattern = rtrim($basePath, '/').'/'.'%s';
        } catch (\Throwable) {
            $basePath = '';
            $detailUrlPattern = '/%s';
        }

        // Build results independently of the list module
        $geo = (string) (\Contao\Input::get('branches_geo_address') ?? '');
        $radius = (float) (\Contao\Input::get('branches_geo') ?? 0);
        $latParam = \Contao\Input::get('branches_lat');
        $lngParam = \Contao\Input::get('branches_lng');
        $hasQuery = ($geo !== '') || (is_numeric($latParam) && is_numeric($lngParam));
        $entries = [];
        if ($hasQuery) {
            $center = null;
            if (is_numeric($latParam) && is_numeric($lngParam)) {
                $center = [floatval($latParam), floatval($lngParam)];
            }
            if (!$center && $geo !== '') {
                $zip = null;
                if (preg_match('~(\\d{4,5})~', $geo, $m)) { $zip = $m[1]; }
                $centerModel = null;
                if ($zip) { $centerModel = \DVC\ContaoCustomCatalog\Model\BranchModel::findOneBy(['published=? AND address_zipcode=?'], [1, $zip]); }
                if (!$centerModel) { $centerModel = \DVC\ContaoCustomCatalog\Model\BranchModel::findOneBy(['published=? AND address_city=?'], [1, $geo]); }
                if ($centerModel) { $center = self::extractLatLng($centerModel); }
                if (!$center) {
                    $like = '%'.$geo.'%';
                    $approx = \DVC\ContaoCustomCatalog\Model\BranchModel::findBy(['published=?', '(address_zipcode LIKE ? OR address_city LIKE ?)'], [1, $like, $like]) ?: [];
                    $approxEntries = self::toArray($approx);
                    $coords = [];
                    foreach ($approxEntries as $row) { $c = self::extractLatLng($row); if ($c) { $coords[] = $c; } }
                    if (!empty($coords)) {
                        $sumLat = 0.0; $sumLng = 0.0; $n = count($coords);
                        foreach ($coords as [$la,$lo]) { $sumLat += $la; $sumLng += $lo; }
                        $center = [$sumLat / $n, $sumLng / $n];
                    }
                }
                // ZIP prefix heuristic (offline geocoding): try 5→2 digit prefixes to approximate center
                if (!$center && $zip) {
                    for ($len = 5; $len >= 2; $len--) {
                        $prefix = substr($zip, 0, $len);
                        if ($prefix === '') { continue; }
                        $approx = \DVC\ContaoCustomCatalog\Model\BranchModel::findBy(['published=?', 'address_zipcode LIKE ?'], [1, $prefix.'%']) ?: [];
                        $approxEntries = self::toArray($approx);
                        $coords = [];
                        foreach ($approxEntries as $row) { $c = self::extractLatLng($row); if ($c) { $coords[] = $c; } }
                        if (!empty($coords)) {
                            $sumLat = 0.0; $sumLng = 0.0; $n = count($coords);
                            foreach ($coords as [$la,$lo]) { $sumLat += $la; $sumLng += $lo; }
                            $center = [$sumLat / $n, $sumLng / $n];
                            break;
                        }
                    }
                }
            }

            if ($center && $radius > 0) {
                // Haversine distance filter
                $entriesTemp = [];
                $collections = [];
                $collections[] = \DVC\ContaoCustomCatalog\Model\BranchModel::findBy(['published=?'], [1], []) ?: [];
                foreach ($collections as $collection) {
                    foreach (self::toArray($collection) as $row) {
                        $coords = self::extractLatLng($row);
                        if (!$coords) { continue; }
                        $dist = self::haversine($center[0], $center[1], $coords[0], $coords[1]);
                        if ($dist <= $radius) { $row->_distance_km = $dist; $entriesTemp[] = $row; }
                    }
                    if (!empty($entriesTemp)) { break; }
                }
                usort($entriesTemp, fn($a, $b) => ($a->_distance_km <=> $b->_distance_km));
                $entries = $entriesTemp;
            } else {
                // Fallback LIKE search on zipcode/city/name/alias
                $like = '%'.$geo.'%';
                $columns = ['published=?', '(address_zipcode LIKE ? OR address_city LIKE ? OR name LIKE ? OR alias LIKE ?)'];
                $values  = [1, $like, $like, $like, $like];
                $collection = \DVC\ContaoCustomCatalog\Model\BranchModel::findBy($columns, $values, ['order' => 'name']) ?: [];
                $entries = self::toArray($collection);
            }
        }

        // Final filtering similar to list module
        $entries = array_values(array_filter($entries, function($row) {
            $name = trim((string)($row->name ?? ''));
            $pubOk = ((int)($row->published ?? 0) === 1);
            return $name !== '' && $pubOk;
        }));

        $this->Template->hasQuery = $hasQuery;
        $this->Template->basePath = $basePath;
        $this->Template->detailUrlPattern = $detailUrlPattern;
        $this->Template->empty = empty($entries);
        $this->Template->entries = array_map(fn($row) => new \DVC\ContaoCustomCatalog\Template\EntryWrapper($row), $entries);
    }

    private static function toArray(mixed $collection): array
    {
        if ($collection instanceof \Traversable) {
            return iterator_to_array($collection);
        }
        if (is_array($collection)) {
            return $collection;
        }
        return [];
    }

    private static function extractLatLng(object $model): ?array
    {
        try {
            $raw = (string) ($model->address ?? '');
            if ($raw !== '') {
                if (strpos($raw, ',') !== false) {
                    [$la, $lo] = array_map('trim', explode(',', $raw, 2));
                    if ($la !== '' && $lo !== '') { return [floatval($la), floatval($lo)]; }
                }
                if (preg_match('~^a:\\d+:\\{.*\\}$~s', $raw)) {
                    $arr = @unserialize($raw);
                    if (is_array($arr) && isset($arr[0], $arr[1])) { return [floatval($arr[0]), floatval($arr[1])]; }
                }
            }
        } catch (\Throwable) {}
        if (isset($model->address_lat, $model->address_lng) && is_numeric($model->address_lat) && is_numeric($model->address_lng)) {
            return [floatval($model->address_lat), floatval($model->address_lng)];
        }
        return null;
    }

    private static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371.0; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
