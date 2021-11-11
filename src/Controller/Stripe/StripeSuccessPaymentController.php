<?php

namespace App\Controller\Stripe;

use App\Entity\Order;
use App\Services\CartServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeSuccessPaymentController extends AbstractController
{
    /**
     * @Route("/stripe-payment-success/{StripeCheckoutSessionId}", name="stripe_payment_success")
     */
    public function index(?Order $order, CartServices $cartServices, EntityManagerInterface $manager): Response // Le ? signifie que s'il ne trouve pas d'ordre, il doit renvoyer NULL.
    {
        if(!$order || $order->getUser() !== $this->getUser()){//Si on n'arrive pas à recuperer la commande, ou bien si la commande recuperée est differente de celle du client connecté,  on redirige le client vers la page d'accueil.
            return $this->redirectToRoute("home");
        }

        // Est ce que la commande est déjà payé? Si elle n'est pas payé on envoie un mail
        if(!$order->getIsPaid()){ // Si la commande n'est pas payée
            // commande payée On met le setIsPaid à true et on supprime le panier
            $order->setIsPaid(true);
            $manager->flush();       // On ne fait pas de persist() , car l'objet  existe déjà en BD. Le Flush , ne fait que modifier l'objet en BD. Dans notre cas il était à False, et donc il passe à true maintenant dans la DB
            $cartServices->deleteCart();

            //Un email au client

        }
        return $this->render('stripe/stripe_success_payment/index.html.twig', [
            'controller_name' => 'StripeSuccessPaymentController',
            'order'=> $order
        ]);
    }
}
