<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Services\UpdateManager;


/**
 * @Route("/category", name="category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/show", name="_show_all")
     */
    public function showAllCategories()
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository->findAllWithNumProducts();

        return $this->render('category/show_all.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/show/{id}", name="_show", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function showCategory($id)
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $category = $repository->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        } else {
            return $this->render('category/show.html.twig', [
                'controller_name' => 'CategoryController',
                'category' => $category,
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
    public function addCategory(Request $request, ValidatorInterface $validator, UpdateManager $updateManager)
    {
        if ($request->request->has('title')) {
            $entityManager = $this->getDoctrine()->getManager();

            $category = new Category();
            $category->setTitle($request->request->get('title'));

            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            } else {
                $entityManager->persist($category);
                $entityManager->flush();

                $message = "Added a new category with the name \"" . $category->getTitle() . "\" and the identifier \"" . $category->getId() . "\"";

                $updateManager->notifyOfUpdate($message);

                return $this->render('category/access_add.html.twig', [
                    'controller_name' => 'CategoryController',
                    'category' => $category,
                ]);
            }
        } else {
            return $this->render('category/add.html.twig', [
                'controller_name' => 'CategoryController',
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
    public function editCategory(Request $request, ValidatorInterface $validator, UpdateManager $updateManager, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        } else {
            if ($request->request->has('id')) {
                $category->setTitle($request->request->get('title'));

                $errors = $validator->validate($category);
                if (count($errors) > 0) {
                    return new Response((string) $errors, 400);
                } else {
                    $entityManager->flush();

                    $message = "Category with identifier \"" . $category->getId() . "\" has been changed";
                    $updateManager->notifyOfUpdate($message);

                    return $this->redirectToRoute('category_show', [
                        'id' => $category->getId()
                    ]);
                }
            } else {
                return $this->render('category/edit.html.twig', [
                    'controller_name' => 'CategoryController',
                    'category' => $category,
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
    public function deleteCategory(UpdateManager $updateManager, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        } else {
            $entityManager->remove($category);

            $products = $entityManager->getRepository(Product::class)->findBy(['id_category' => $category->getId()]);

            if (count($products)>0) {
                foreach ($products as $product) {
                    $entityManager->remove($product);
                }
            }

            $entityManager->flush();

            $message = "The category with identifier \"" . $id . "\" and all products in this category have been deleted";
            $updateManager->notifyOfUpdate($message);

            return $this->render('category/access_delete.html.twig', [
                'controller_name' => 'CategoryController',
                'category' => $category,
            ]);
        }
    }
}
