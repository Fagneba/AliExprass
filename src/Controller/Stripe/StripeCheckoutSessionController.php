<?php

namespace App\Controller\Stripe;

use Stripe\Stripe;
use App\Entity\Cart;
use Stripe\Checkout\Session;
use App\Services\CartServices;
use App\Services\OrderServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeCheckoutSessionController extends AbstractController
{
    /**
     * @Route("/create-checkout-session/{reference}", name="create_checkout_session")
     */
    public function index(?Cart $cart, OrderServices $orderServices, EntityManagerInterface $manager): Response // le ? veut dire que s'il n'arrive pas à recuperer le panier grace au Parameter Converter , il renvoie NULL
    {
       $user = $this->getUser();
        if(!$cart){ // Cela veut dire : if on n'arrive pas à recuperer le panier ( la reference est donc incorrect)
            return $this->redirectToRoute("home");
        }

        //Si on recupère le panier alors on continue
        $order = $orderServices->createOrder($cart); //Nous alllons enregistrer la commande

        Stripe::setApiKey('sk_test_51Ju2k8CRBuwdGxzZgD0KNVGyFFM1oq7AMILXAAtssVljmB54juCrVh91zjjRRp7h4aH3QvnORgODBNQ183osQeln00Rugymg5X');

        $checkout_session = Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => $orderServices->getLineItems($cart),
            'mode' => 'payment',
            'success_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-success/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-cancel/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeCheckoutSessionId($checkout_session->id);
        $manager->flush();

        return $this->json(['id' => $checkout_session->id]);
    }
}
