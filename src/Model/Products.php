<?php

namespace App\Model;

use App\Common\DataBase;

class Products extends DataBase
{
    private $id;
    private $id_category;
    private $title;
    private $price;
    private $stock;
    private $products;

    public function __construct()
    {
        $this->products = $this->getData('products');
    }

    private function addToArray()
    {
        return array(
            "id" => $this->id,
            "id_category" => $this->id_category,
            "title" => $this->title,
            "price" => $this->price,
            "stock" => $this->stock
        );
    }

    private function getAutoProductId()
    {
        $arr_id = array();
        foreach ($this->products as $product) {
            $arr_id[] = $product['id'];
        }

        return max($arr_id)+1;
    }

    private function getProduct($id)
    {
        foreach ($this->products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
    }

    public function changeProduct($id_product, $cell, $data)
    {
        if (!empty($id_product) && !empty($data)) {
            $product = $this->getProduct($id_product);
            $product[$cell] = $data;

            return $this->setData($product, 'products', $id_product);
        } else {
            return false;
        }
    }

    public function setProduct($id_category, $title, $price, $stock)
    {
        $this->id_category = $id_category;
        $this->title = $title;
        $this->price = $price;
        $this->stock = $stock;
        $this->id = $this->getAutoProductId();

        $arrData = $this->addToArray();

        array_push($this->products, $arrData);

        return $this->setData($this->products, 'products');
    }
}
