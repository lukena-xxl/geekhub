<?php


namespace App\Model;

use App\Common\DataBase;


class ProductMove extends DataBase
{
    private function getProduct($id)
    {
        $products = $this->getData('products');
        foreach ($products as $product) {
            if ($product['id'] == $id) {
                return $product;
                break;
            }
        }
    }

    public function changeProduct($id_product, $cell, $data)
    {
        if (!empty($id_product) && !empty($data)) {

            $product = $this->getProduct($id_product);
            $product[$cell] = $data;

            if($this->setData($product, 'products', $id_product)){
                $categories = [];
                foreach ($this->getData('categories') as $category) {
                    $categories[$category['id']] = $category['title']." (".$category['id'].")";
                }

                return array(
                    'products' => $this->getData('products'),
                    'categories' => $categories
                );
            }
        } else {
            return false;
        }
    }
}