<?php

namespace App\Controller\Stripe;

use App\Services\CartServices;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeCheckoutSessionController extends AbstractController
{
    /**
     * @Route("/create-checkout-session", name="create_checkout_session")
     */
    public function index(CartServices $cartServices): Response
    {
        $cart = $cartServices->getFullCart(); //ça nous recupère tout le contenue du panier

        Stripe::setApiKey('sk_test_51Ju2k8CRBuwdGxzZgD0KNVGyFFM1oq7AMILXAAtssVljmB54juCrVh91zjjRRp7h4aH3QvnORgODBNQ183osQeln00Rugymg5X');

        $line_items = [];

        // Ici nous allons boucler sur tous les produits du panier, recuperer plus haut
        foreach($cart['products'] as $data_product)
        {   
           /* [
               'quantity' => 5,
               'product' => Objet 
            ]*/
            $product = $data_product['product'];
            $line_items[] = [
                'price_data' => [
                    'Currency' => 'usd',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getName(),
                        'image' => [$_ENV['YOUR_DOMAIN'].'/uploads/products/'.$product->getImage()],
                    ],
                ],
                'quantity' => $data_product['quantity'],
               
            ];
            
        }

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-success',
            'cancel_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-cancel',
        ]);

        return $this->json(['id' => $checkout_session->id]);
    }
}
