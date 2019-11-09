<?php

namespace App\Controller;

use App\Model\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductsController extends AbstractController
{
    /**
     * @Route("/products/{name}")
     * @param Request $request
     * @param $name
     * @return Response|null
     */
    public function indexAction(Request $request, $name)
    {
        $obj_product = new Products();
        $response = null;

        if ($name == 'create') {

            //Getting parameters from a GET request
            $id_category = $request->get('id_category', rand(1, 5));
            $title = $request->get('title', 'New Product');
            $price = $request->get('price', rand(10, 100));
            $stock = $request->get('stock', rand(1, 10));

            //Adding product
            if ($obj_product->setProduct($id_category, $title, $price, $stock)) {
                $response = new Response($this->printProducts());
            } else {
                $response = new Response("Error adding product!");
            }
        } elseif ($name == 'move') {

            //Getting parameters from a GET request
            $id_product = $request->get('id_product');
            $new_id_category = $request->get('id_category');

            //Change category for product
            if ($obj_product->changeProduct($id_product, 'id_category', $new_id_category)) {
                $response = new Response($this->printProducts());
            } else {
                $response = new Response("Product category change error!");
            }
        }

        return $response;
    }

    public function printProducts()
    {
        $all_products = (new Products)->getData('products');
        $all_categories = (new Products)->getData('categories');

        $categories = [];
        foreach ($all_categories as $category) {
            $categories[$category['id']] = $category['title']." (".$category['id'].")";
        }

        return $this->render("products/products.show.html.twig", [
            'products' => $all_products,
            'categories' => $categories
        ]);
    }
}