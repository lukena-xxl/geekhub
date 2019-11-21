<?php


namespace App\Model;

use App\Common\DataBase;


class ProductCreate extends DataBase
{
    private $id;
    private $id_category;
    private $title;
    private $price;
    private $stock;

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

    public function setProduct($id_category, $title, $price, $stock)
    {
        $this->id_category = $id_category;
        $this->title = $title;
        $this->price = $price;
        $this->stock = $stock;

        $products = $this->getData('products');
        $arr_id = array();
        foreach ($products as $product) {
            $arr_id[] = $product['id'];
        }
        $this->id = max($arr_id)+1;
        $arrData = $this->addToArray();

        array_push($products, $arrData);

        if ($this->setData($products, 'products')) {
            $categories = [];
            foreach ($this->getData('categories') as $category) {
                $categories[$category['id']] = $category['title']." (".$category['id'].")";
            }

            return array(
                'products' => $products,
                'categories' => $categories
            );
        } else {
            return false;
        }
    }
}