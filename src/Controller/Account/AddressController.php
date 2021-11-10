<?php

namespace App\Controller\Account;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use App\Services\CartServices;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/address")
 */
class AddressController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session){
         $this->session = $session;
    }

    /**
     * @Route("/new", name="address_new", methods={"GET","POST"})
     */
    public function new(Request $request, CartServices $cartServices): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser(); //en faisant ceci il va nous envoyer l'utilisateur qui est connecté, donc s'il ne l'est pas on ne pourra pas le recuperer.
            $address->setUser($user); // Ici on recupère les donnees qui sont bien dans adresse .Donc on a une adresse avec l'utilisateur qui est connecté
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($address);
            $entityManager->flush();

            if($cartServices->getFullCart()){ // Si son panier contient des produits
                return $this->redirectToRoute('checkout');
            }

            $this->addFlash('address_message', 'Your address has been saved');
            return $this->redirectToRoute('account');
        }

        return $this->render('address/new.html.twig', [
            'address' => $address,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="address_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Address $address): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if($this->session->get('checkout_data')){ // Si notre session est definit , cela veut dire qu'on vient de la page confirm
                $data = $this->session->get('checkout_data');//On va donc chercher à mettre à jour les données qu'on a dans notre session
                $data['address'] = $address; // On met ici les adresses à jour, c'est à dire les données saisis par les utilisateurs.
                $this->session->set('checkout_data', $data);
                return $this->redirectToRoute("checkout_confirm"); // Donc on retourne sur la page confirme
            }

            $this->addFlash('address_message', 'Your address has been edited');
            return $this->redirectToRoute('account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('address/edit.html.twig', [
            'address' => $address,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="address_delete", methods={"POST"})
     */
    public function delete(Request $request, Address $address): Response
    {
        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('address_message', 'Your address has been deleted');
        }

        return $this->redirectToRoute('account', [], Response::HTTP_SEE_OTHER);
    }
}
