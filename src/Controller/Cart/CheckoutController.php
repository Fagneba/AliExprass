<?php

namespace App\Controller\Cart;

use App\Form\CheckoutType;
use App\Services\CartServices;
use App\Services\OrderServices;
use SessionIdInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CheckoutController extends AbstractController
{
    private $cartServices;
    private $session;
    function __construct(CartServices $cartServices, SessionInterface $session)
    {
        $this->cartServices = $cartServices;
        $this->session = $session;
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();//On recupère l'utilisateur 
        $cart = $this->cartServices->getFullCart(); //On recupère le panier
       
        if(!isset($cart['products'])){//Si le panier n'est pas definit c'est unitile de l'afficher
            return $this->redirectToRoute("home"); // On le redirige vers la page d'acceuil
        }
       

        if(!$user->getAddresses()->getValues()){ // Nous allons encore verifier si l'utilisateur connecté a definit son ou ses adresses, sinon inutile de continuer
            $this->addFlash('checkout_message', 'Please add an address to your account without continuing !');// On Ajoute un message flash pour lui dire d'ajouter une adresse avant de continuer .
            return $this->redirectToRoute("address_new"); // On le redirige vers la page de création d'addresse
        }
         
        //Nous allons faire une verif pour voir s'il y a quelque chose dans la session
        if($this->session->get('checkout_data')){
            return $this->redirectToRoute('checkout_confirm');
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
    public function confirm(Request $request, OrderServices $orderServices ): Response
    {
        $user = $this->getUser();//On recupère l'utilisateur 
        $cart = $this->cartServices->getFullCart(); //On recupère le panier
        
        if(!isset($cart['products'])){//Si le panier n'est pas definit c'est unitile de l'afficher
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
        if($form->isSubmitted() && $form->isValid() || $this->session->get('checkout_data')){ // Si le formulaire est valide ou bien on a quelque chose dans la session

            if($this->session->get('checkout_data')){
                 $data = $this->session->get('checkout_data');
            }else{
                $data = $form->getData(); // On recupère les données issues du formulaire
                $this->session->set('checkout_data', $data);//Quand on vient du formulaire, on va sauver les données dans la session
            }
           
            //On recupère les données qu'on a sur notre pages checkout notamment l'adresse et le transport
            $address = $data['address'];
            $carrier = $data['carrier']; 
            $information = $data['informations'];

            //Ici on va sauvegarder le panier
            $cart['checkout'] = $data;
            $reference = $orderServices->saveCart($cart,$user);
            
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
    
    /**
     * @Route("/checkout/edit", name="checkout_edit")
     */
    public function checkoutEdit():Response{
        $this->session->get('checkout_data',[]);
        return $this->redirectToRoute("checkout");
    }
}
