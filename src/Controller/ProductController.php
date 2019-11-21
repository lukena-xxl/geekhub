<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Services\UpdateManager;


/**
 * @Route("/product", name="product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     */
    public function showAllProducts()
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $products = $repository->findAllWithNameCategories();

        return $this->render('product/show_all.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showProduct($id)
    {
        $repository = $this->getDoctrine()->getRepository(Product::class);
        $product = $repository->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        } else {
            return $this->render('product/show.html.twig', [
                'controller_name' => 'ProductController',
                'product' => $product,
            ]);
        }
    }

    /**
     * @Route("/add", name="_add")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function addProduct(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $entityManager = $this->getDoctrine()->getManager();

            $product = new Product();
            $product->setTitle($request->request->get('title'));
            $product->setIdCategory($request->request->get('id_category'));
            $product->setPrice($request->request->get('price'));
            $product->setStock($request->request->get('stock'));

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager->persist($product);
                $entityManager->flush();

                $message = "Added a new product with the name \"" . $product->getTitle() . "\" and the identifier \"" . $product->getId() . "\"";

                $updateManager->notifyOfUpdate($message);

                return $this->render('product/access_add.html.twig', [
                    'controller_name' => 'ProductController',
                    'product' => $product,
                ]);
            }
        } else {
            $repository = $this->getDoctrine()->getRepository(Category::class);
            $categories = $repository->findAll();

            return $this->render('product/add.html.twig', [
                'controller_name' => 'ProductController',
                'categories' => $categories,
            ]);
        }
    }

    /**
     * @Route("/edit/{id}", name="_edit", requirements={"id"="\d+"})
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UpdateManager $updateManager
     * @return Response
     */
    public function editProduct(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        } else {
            if ($request->request->has('id')) {
                $product->setTitle($request->request->get('title'));
                $product->setIdCategory($request->request->get('id_category'));
                $product->setPrice($request->request->get('price'));
                $product->setStock($request->request->get('stock'));

                $errors = $validator->validate($product);
                if (count($errors) > 0) {
                    return new Response((string) $errors, 400);
                } else {
                    $entityManager->flush();

                    $message = "Product with identifier \"" . $product->getId() . "\" has been changed";
                    $updateManager->notifyOfUpdate($message);

                    return $this->redirectToRoute('product_show', [
                        'id' => $product->getId()
                    ]);
                }
            } else {
                $repository = $this->getDoctrine()->getRepository(Category::class);
                $categories = $repository->findAll();

                return $this->render('product/edit.html.twig', [
                    'controller_name' => 'ProductController',
                    'product' => $product,
                    'categories' => $categories,
                ]);
            }
        }
    }

    /**
     * @Route("/delete/{id}", name="_delete", requirements={"id"="\d+"})
     * @param UpdateManager $updateManager
     * @param $id
     * @return Response
     */
    public function deleteProduct(UpdateManager $updateManager, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        } else {
            $entityManager->remove($product);
            $entityManager->flush();

            $message = "Product with identifier \"" . $id . "\" has been removed";
            $updateManager->notifyOfUpdate($message);

            return $this->render('product/access_delete.html.twig', [
                'controller_name' => 'ProductController',
                'product' => $product,
            ]);
        }
    }
}
