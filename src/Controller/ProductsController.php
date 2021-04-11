<?php


namespace App\Controller;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(ProductRepository $productRepository): Response
    {
        $bikes = $productRepository->findAll();
        return $this->render('index.html.twig', [
            'bikes' => $bikes
        ]);
    }

    /**
     * @Route("/products/{id}")
     */
    public function productDetails(
        $id,
        Request $request,
        ProductRepository $productRepository,
        SessionInterface $session): Response
    {
        $bike = $productRepository->find($id);
        if ($bike === null) {
            throw $this->createNotFoundException("This product doesn't exist");
        }

        $basket = $session->get('basket', []);
        if ($request->isMethod('POST')) {
            $basket[$bike->getId()]=$bike;
            $session->set('basket', $basket);
        }
        $isBasket=array_key_exists($bike->getId(),$basket);
        return $this->render('productDetails.html.twig', [
            'bike' => $bike,
            'inBasket' => $isBasket
        ]);
    }
}