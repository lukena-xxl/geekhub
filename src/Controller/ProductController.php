<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\ProductCreate;
use App\Model\ProductMove;
use App\Services\Products\ProductUpdateManager;


class ProductController extends AbstractController
{
    private $productUpdateManager;

    public function __construct(ProductUpdateManager $productUpdateManager)
    {
        $this->productUpdateManager = $productUpdateManager;
    }

    /**
     * @Route("/product/create")
     * @param Request $request
     * @return Response|null
     */
    public function createAction(Request $request)
    {
        //Getting parameters from a GET request
        $id_category = $request->get('id_category', rand(1, 5));
        $title = $request->get('title', 'New Product');
        $price = $request->get('price', rand(10, 100));
        $stock = $request->get('stock', rand(1, 10));

        $product = new ProductCreate();
        $result = $product->setProduct($id_category, $title, $price, $stock);

        if ($result) {
            $message = "Added a new product named \"".$title."\"";
            $response = new Response($this->viewProducts($result));
        } else {
            $message = "Error adding product named \"".$title."\"";
            $response = new Response($message);
        }

        $this->productUpdateManager->notifyOfProductUpdate($message);

        return $response;
    }

    /**
     * @Route("/product/move")
     * @param Request $request
     * @return Response|null
     */
    public function moveAction(Request $request)
    {
        //Getting parameters from a GET request
        $id_product = $request->get('id_product');
        $new_id_category = $request->get('id_category');

        $product = new ProductMove();
        $result = $product->changeProduct($id_product, 'id_category', $new_id_category);

        if ($result) {
            $message = "Category successfully changed for product with identifier: ".$id_product;
            $response = new Response($this->viewProducts($result));
        } else {
            $message = "Category change error for product with identifier: ".$id_product;
            $response = new Response($message);
        }

        $this->productUpdateManager->notifyOfProductUpdate($message);

        return $response;
    }

    private function viewProducts($result)
    {
        $template = $this->render("products/products.show.html.twig", [
            'products' => $result['products'],
            'categories' => $result['categories']
        ]);

        return $template;
    }
}