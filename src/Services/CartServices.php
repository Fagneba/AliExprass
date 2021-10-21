<?php
namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class CartServices{

    private $session;
    private $repoProduct;
    private $tva = 0.2;

    public function __construct(SessionInterface $session, ProductRepository $repoProduct)
    {
        $this->session = $session;
        $this->repoProduct = $repoProduct;
    }

    public function addToCart($id){
        $cart = $this->getCart(); //on récupère le panier

        if(isset($cart[$id])){ //si c'est définit, c'est que le produit existe
               $cart[$id]++;   // alors on incrément le produit
        }else{ // sinon on y met 1 produit
            $cart[$id] = 1;
        }
        $this->updateCart($cart); //on procèce à la mise à jour du panier
    }

    public function deleteFromCart($id){
        $cart = $this->getCart(); // On recupére le panier
        
        if(isset($cart[$id])){  //si c'est définit, c'est que le produit existe déjà dans le panier

            if($cart[$id] > 1){ // Nous allons poser la condition pour savoir si le produit existe plus d'une fois ds le panier
                $cart[$id]--;
            }else{
                unset($cart[$id]); // Si on a un seul produit , on le retire purement et sinplement
            }
            $this->updateCart($cart); //on procèce à la mise à jour du panier, donc mise à jour de la session
        }
    }

    public function deleteAllToCart($id){  // Ici on supprime tout les produits du panier
        $cart = $this->getCart(); // On recupére le panier
        
        if(isset($cart[$id])){  //si c'est définit, c'est que le produit existe déjà dans le panier

                unset($cart[$id]); // On supprime ici tous les produits du panier
            
            $this->updateCart($cart); //on procèce à la mise à jour du panier, donc mise à jour de la session
        }

    }

    public function deleteCart(){
        $this->updateCart([]); // Ici il nous vide vraiment le panier

    }

    public function updateCart($cart){
        $this->session->set('cart',$cart);
        $this->session->set('cartData', $this->getFullCart()); //variable de session contenant les données du produit
    }

    public function getCart(){
        return $this->session->get('cart',[]);
    }

    // Pour finir on va creer une methode pour recupere tous les produis du panier
    public function getFullCart(){
        $cart = $this->getCart();

        $fullCart = [];
        $quantity_cart = 0;
        $subTotal = 0;

        foreach($cart as $id => $quantity){
            $product = $this->repoProduct->find($id);

            if($product){ // si produit recupérer avec succes
                $fullCart['products'][]=
                [
                    "quantity" => $quantity,
                    "product" => $product
                ];
                $quantity_cart +=$quantity;
                $subTotal += $quantity * $product->getPrice()/100;

            }else{// le produit n'a pas été recuperer donc id incorrect
                $this->deleteFromCart($id);
            }

        }
        //Avant de retourner la quantité et le prix total, on va les mettre dans le fullCart
        $fuullCart['data'] = [
            "quantity_cart" => $quantity_cart,
            "subTotalHT" => $subTotal,
            "Taxe" => round($subTotal * $this->tva,2),
            "subTotalTTC" => round(($subTotal + ($subTotal * $this->tva)), 2)
        ];
        return $fullCart;
    }
}

