<?php

namespace App\Controller;

use App\Form\CheckoutType;
use App\Services\CartServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    private $cartServices;
    function __construct(CartServices $cartServices)
    {
        $this->cartServices = $cartServices;
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();//On recupère l'utilisateur 
        $cart = $this->cartServices->getFullCart(); //On recupère le panier

        if(!isset($cart['product'])){//Si le panier n'est pas definit c'est unitile de l'afficher
            return $this->redirectToRoute("home"); // On le redirige vers la page d'acceuil
        }

        if(!$user->getAddresses()->getValues()){ // Nous allons encore verifier si l'utilisateur connecté a definit son ou ses adresses, sinon inutile de continuer
            $this->addFlash('checkout_message', 'Please add an address to your account without continuing !');// On Ajoute un message flash pour lui dire d'ajouter une adresse avant de continuer .
            return $this->redirectToRoute("address_new"); // On le redirige vers la page de création d'addresse
        }

        // On va initialiser le formulaire 
        $form = $this->createForm(CheckoutType::class,null,['user'=>$user]);

         //Et on fournit les données à notre template 
        return $this->render('checkout/index.html.twig',[
            'cart' => $cart,
            'checkout' => $form->createView()
            
        ]);
    }

     /**
     * @Route("/checkout/confirm", name="checkout_confirm")
     */
    public function confirm(Request $request): Response
    {
        $user = $this->getUser();//On recupère l'utilisateur 
        $cart = $this->cartServices->getFullCart(); //On recupère le panier

        if(!isset($cart['product'])){//Si le panier n'est pas definit c'est unitile de l'afficher
            return $this->redirectToRoute("home"); // On le redirige vers la page d'acceuil
        }

        if(!$user->getAddresses()->getValues()){ // Nous allons encore verifier si l'utilisateur connecté a definit son ou ses adresses, sinon inutile de continuer
            $this->addFlash('checkout_message', 'Please add an address to your account without continuing !');// On Ajoute un message flash pour lui dire d'ajouter une adresse avant de continuer .
            return $this->redirectToRoute("address_new"); // On le redirige vers la page de création d'addresse
        }

        // On va initialiser le formulaire 
        $form = $this->createForm(CheckoutType::class,null,['user'=>$user]);

        // On va annaliser la requete en injectant dans notre fonction Request $request
        $form->handleRequest($request);

        //est ce que le formulaire est soumis et valide ?
        if($form->isSubmitted() && $form->isValid()){

            $data = $form->getData(); // On recupère le formulaire avecles donnés qui sont envoyés

            //On recupère les données qu'on a sur notre pages checkout notamment l'adresse et le transport
            $address = $data['address'];
            $carrier = $data['carrier']; 
            $information = $data['informations'];

            return $this->render('checkout/confirm.html.twig',[
                'cart' => $cart,

                //On met les données récuperées dans notre Template 
                'address' => $address,
                'carrier' => $carrier,
                'informations' => $information,
                'checkout' => $form->createView()
                
            ]);

        }
        //Sinon on retourne à la page du checkout
        return $this->redirectToRoute('checkout');
    }
}
