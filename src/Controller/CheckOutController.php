<?php


namespace App\Controller;


use App\Entity\Orders;
use App\Repository\ProductRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class CheckOutController extends AbstractController
{
    /**
     * @Route("/checkout")
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function index(Request $request,
                             ProductRepository $productRepository,
                             SessionInterface $session,
                          MailerInterface $mailer): Response
    {
        $basket = $session->get('basket', []);
        $total = array_sum(array_map(static function ($product) {
            return $product->getPrice();
        }, $basket));
        $order = new Orders;

        $form = $this->createFormBuilder($order)
            ->add('name', TextType::class)
            ->add('email', TextType::class)
            ->add('address', TextareaType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Confirm Order'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();

            foreach ($basket as $item) {
                $order->getProducts()
                    ->add($productRepository
                        ->find($item->getId()
                        ));
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $this->sendEmailConfirmation($order, $mailer);

            $session->set('basket', []);
            return $this->render('confirmOder.html.twig');
        }

        return $this->render('checkout.html.twig', [
            'total' => $total,
            'form' => $form->createView()
        ]);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    private function sendEmailConfirmation($order, MailerInterface $mailer): void
    {
        $email = (new TemplatedEmail())
            ->from('symfony@monir.com')
            ->to(new Address($order->getEmail(), $order->getName()))
            ->subject("Order Confirmation")
            ->htmlTemplate('emails/order.html.twig')
            ->context(['order'=>$order]);

        $mailer->send($email);

    }
}