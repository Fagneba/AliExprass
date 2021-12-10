<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cart::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=>'DESC']);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), //Pour ne pas l'afficher dans le formulaire
            TextField::new('user.FullName','Client'),
            TextField::new('carrierName','Carrier Name'),
            MoneyField::new('CarrierPrice','Shipping')->setCurrency('USD'), //Pour la monaie
            MoneyField::new('subTotalHT','Subtotal HT')->setCurrency('USD'),
            MoneyField::new('taxe','TVA')->setCurrency('USD'),
            MoneyField::new('subTotalTTC','Subtotal TTC')->setCurrency('USD'),
            BooleanField::new('isPaid')
        ];
    }
    
}
