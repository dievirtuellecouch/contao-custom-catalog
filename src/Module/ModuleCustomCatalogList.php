<?php

namespace DVC\ContaoCustomCatalog\Module;

use Contao\Module;
use DVC\ContaoCustomCatalog\Model\BranchModel;

class ModuleCustomCatalogList extends Module
{
    protected $strTemplate = 'mod_dvc_cc_branch_list';

    protected function compile(): void
    {
        // Force AJAX reload support regardless of DCA checkbox
        $this->Template->allowAjaxReload = true;

        $options = ['order' => 'name'];
        $entries = [];
        $where = (string) ($this->custom_sql_where ?? '');
        if ($where !== '') {
            $collection = BranchModel::findBy(['published=?', $where], [1], $options) ?: [];
            $entries = self::toArray($collection);
            if (empty($entries)) {
                $collection = BranchModel::findBy(['published=?'], [1], $options) ?: [];
                $entries = self::toArray($collection);
            }
        } else {
            $geo = (string) (\Contao\Input::get('branches_geo_address') ?? '');
            $radius = (float) (\Contao\Input::get('branches_geo') ?? 0);
            $center = null; // [lat, lng]
            $latParam = \Contao\Input::get('branches_lat');
            $lngParam = \Contao\Input::get('branches_lng');
            if (is_numeric($latParam) && is_numeric($lngParam)) {
                $center = [floatval($latParam), floatval($lngParam)];
            }

            if ($geo !== '') {
                // Try to determine a center coordinate based on an exact zipcode or city match
                $zip = null;
                if (preg_match('~(\d{4,5})~', $geo, $m)) {
                    $zip = $m[1];
                }
                $centerModel = null;
                if ($zip) {
                    $centerModel = BranchModel::findOneBy(['published=? AND address_zipcode=?'], [1, $zip]);
                }
                if (!$centerModel) {
                    $centerModel = BranchModel::findOneBy(['published=? AND address_city=?'], [1, $geo]);
                }
                if ($centerModel) {
                    $center = self::extractLatLng($centerModel);
                }
                // If no center yet (no API key geocode or no exact match), approximate from LIKE matches
                if (!$center) {
                    $like = '%'.$geo.'%';
                    $approx = BranchModel::findBy([
                        'published=?',
                        '(address_zipcode LIKE ? OR address_city LIKE ?)'
                    ], [1, $like, $like]) ?: [];
                    $approxEntries = iterator_to_array($approx);
                    $coords = [];
                    foreach ($approxEntries as $row) {
                        $c = self::extractLatLng($row);
                        if ($c) { $coords[] = $c; }
                    }
                    if (!empty($coords)) {
                        $sumLat = 0.0; $sumLng = 0.0; $n = count($coords);
                        foreach ($coords as [$la,$lo]) { $sumLat += $la; $sumLng += $lo; }
                        $center = [$sumLat / $n, $sumLng / $n];
                    }
                }

                if ($center && $radius > 0) {
                    // Distance-based filtering using Haversine in PHP
                    $entries = [];
                    $collections = [];
                    // Only consider published records
                    $collections[] = BranchModel::findBy(['published=?'], [1], []) ?: [];
                    foreach ($collections as $collection) {
                        foreach ($collection as $row) {
                            $coords = self::extractLatLng($row);
                            if (!$coords) { continue; }
                            $dist = self::haversine($center[0], $center[1], $coords[0], $coords[1]);
                            if ($dist <= $radius) {
                                $row->_distance_km = $dist;
                                $entries[] = $row;
                            }
                        }
                        if (!empty($entries)) { break; }
                    }
                    usort($entries, fn($a, $b) => ($a->_distance_km <=> $b->_distance_km));
                } else {
                    // Fallback: LIKE-based search on zipcode/city/name/alias
                    $like = '%'.$geo.'%';
                    $columns = [
                        'published=?',
                        '(address_zipcode LIKE ? OR address_city LIKE ? OR name LIKE ? OR alias LIKE ?)'
                    ];
                    $values = [1, $like, $like, $like, $like];
                    $collection = BranchModel::findBy($columns, $values, $options) ?: [];
                    $entries = self::toArray($collection);
                }
            } else {
                $collection = BranchModel::findBy(['published=?'], [1], $options) ?: [];
                $entries = self::toArray($collection);
            }
        }
        // Filter out entries with empty title (name) and ensure published strictly
        $entries = array_values(array_filter($entries, function($row) {
            $name = trim((string)($row->name ?? ''));
            $pubOk = ((int)($row->published ?? 0) === 1);
            return $name !== '' && $pubOk;
        }));

        // Determine base path from jumpTo, not current page
        $basePath = '';
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
        } catch (\Throwable) {
            $basePath = '';
        }
        $this->Template->empty = empty($entries);
        $this->Template->entries = array_map(fn($row) => new \DVC\ContaoCustomCatalog\Template\EntryWrapper($row), $entries);
        $this->Template->basePath = $basePath;
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
                // Serialized array [lat, lng]
                if (preg_match('~^a:\\d+:\\{.*\\}$~s', $raw)) {
                    $arr = @unserialize($raw);
                    if (is_array($arr) && isset($arr[0], $arr[1])) { return [floatval($arr[0]), floatval($arr[1])]; }
                }
            }
        } catch (\Throwable) {}
        // Alternative: dedicated columns address_lat/address_lng
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
}
