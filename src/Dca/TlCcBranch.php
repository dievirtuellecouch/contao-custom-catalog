<?php

namespace DVC\ContaoCustomCatalog\Dca;

use DVC\ContaoCustomCatalog\Model\ProductModel;

class TlCcBranch
{
    /**
     * Returns product options as [id => label] using public title and optional titleAddition.
     */
    public function getProductOptions(): array
    {
        $options = [];
        $collection = ProductModel::findAll(['order' => 'title']);

        if (null === $collection) {
            return $options;
        }

        foreach ($collection as $product) {
            $label = trim((string) ($product->title ?: $product->name));
            $addition = trim((string) $product->titleAddition);
            if ($addition !== '' && strcasecmp($addition, $label) !== 0) {
                $label .= ' ('.$addition.')';
            }
            $options[(int) $product->id] = $label !== '' ? $label : ('#'.$product->id);
        }

        return $options;
    }
}

