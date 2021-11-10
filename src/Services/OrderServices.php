<?php
namespace App\Services;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\CartDetails;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;

class OrderServices{

     private $manager;

     public function __construct(EntityManagerInterface $manager)
     {
         $this->manager = $manager;
     }
     
     // Methode qui nous permets de creer une commande qui premt en parametre un panier
     public function createOrder($cart){

        $order = new Order();

        //Modification des attributs
        $order->setReference($cart->getReference)
              ->setCarrierName($cart->getCarrierName())
              ->setCarrierPrice($cart->getCarrierPrice())
              ->setFullName($cart->getFullName())
              ->setDeliveryAddress($cart->getDeliveryAddress())
              ->setMoreInformations($cart->getMoreInformations())
              ->setQuantity($cart->getQuantity())
              ->setsubTotalHT($cart->getSubTotalHT())
              ->setTaxe($cart->getTaxe())
              ->setSubTotalTTC($cart->getSubTotalTTC())
              ->setUser($cart->getUser())
              ->setCreatedAt($cart->getCreatedAt());
        $this->manager->persist($order);

        //On creer aussi les details pour cette commande
        $products = $cart->getCartDetails()->getValues();

        foreach($products as $cart_product){
            $orderDetails = new OrderDetails();
            
            $orderDetails->setOrders($order)
                        ->setProductName($cart_product->getProductName())
                        ->setProductPrice($cart_product->getProductPrice())
                        ->setQuantity($cart_product->getQuantity())
                        ->setSubTotalHT($cart_product->getSubTotalHT())
                        ->setSubTotalTTC($cart_product->getSubTotalTTC())
                        ->setTaxe($cart_product->getTaxe());
            $this->manager->persist($orderDetails);          
        }

        $this->manager->flush();

        return $order;
    }

     //Cette methode nous permet de sauvegarder un panier  en base de Donnée pour nos equipe de marcheting qui pourra le contacter après
     public function saveCart($data, $user){

         // Ici on a notre variable $data
        /* [
             'product' => [],
             'data' => [
                  'quantity_cart' => $quantity_cart,
                  'subTotalHT' => $subTotal,
                  'taxe' => round($subTotal * $this->tva,2),
                  'subTotalTTC' => round(($subTotal + ($subTotal * $this->tva)), 2) 
             ],
             'checkout' => [
                 'address' => Objet,
                 'carrier' => Objet
                 'informations' => String
             ]
         ]*/

        $cart = new Cart();
        $reference = $this->generateUuid();
        $address = $data['checkout']['address'];
        $carrier = $data['checkout']['carrier'];
        $informations = $data['checkout']['informations'];

        $cart->setReference($reference)
             ->setCarrierName($carrier->getName())
             ->setCarrierPrice($carrier->getPrice()/100)
             ->setFullName($address->getFullName())
             ->setDeliveryAddress($address)
             ->setMoreInformations($informations)
             ->setQuantity($data['data']['quantity_cart'])
             ->setsubTotalHT($data['data']['subTotalHT'])
             ->setTaxe($data['data']['taxe'])
             ->setSubTotalTTC(round(($data['data']['subTotalTTC']+$carrier->getprice()/100),2))
             ->setUser($user)
             ->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($cart);

        $cart_details_array = [];

        foreach($data['products'] as $products){
            $cartDetails = new CartDetails();

            $subtotal = $products['quantity'] * $products['product']->getPrice()/100;
            
            $cartDetails->setCarts($cart)
                        ->setProductName($products['product']->getName())
                        ->setProductPrice($products['product']->getPrice()/100)
                        ->setQuantity($products['quantity'])
                        ->setSubTotalHT($subtotal)
                        ->setSubTotalTTC($subtotal*1.2)
                        ->setTaxe($subtotal*0.2);
            $this->manager->persist($cartDetails);
            $cart_details_array[] = $cartDetails;            
        }

        $this->manager->flush();

        return $reference;


     }

     // Methode pour sauvegarder une Reference c'est à dire un panier à travers un identifiant unique
     public function generateUuid(){

         // Initialise le generateur de nombres aléatoire Mersenne Twister
         mt_srand((double)microtime()*100000);

         //strtoupper : Renvoie une chaine en majuscule
         //uniqid : Génère un identifiant unique
         $charid = strtoupper(md5(uniqid(rand(), true)));

         //Générer une chaine d'un octet à partir d'un nombre
         $hyphen = chr(45);

         //substr retourne un segment de chaine
         $uuid = ""
         .substr($charid, 0, 8).$hyphen
         .substr($charid, 8, 4).$hyphen
         .substr($charid, 12, 4).$hyphen
         .substr($charid, 16, 4).$hyphen
         .substr($charid, 0, 8);
         return $uuid;
     }


}