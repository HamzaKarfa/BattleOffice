<?php

namespace App\Controller;

use App\Entity\DeliveryOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\DeliveryOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Form\OrderType;
use App\Form\ProductType;
use App\Manager\OrderManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request)
    {
        //Your code here
        $allForm = [
            'product',
            'order' => new Order(),
            'deliveryOrder' => new DeliveryOrder(),
        ];
        
        $form = $this->createFormBuilder($allForm)
                    ->add('product', ProductType::class)
                    ->add('order', OrderType::class)
                    ->add('deliveryOrder', DeliveryOrderType::class)
        ->getForm();
        
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $this->getDoctrine()
                            ->getRepository(Product::class)
                            ->findOneBy(array( 'id' => intval($form['product']->getData()->getName())));
            dd($product, $allForm);
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($order);
            // $entityManager->flush();

            return $this->redirectToRoute('confirmation');
        }

        return $this->render('landing_page/index_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/confirmation", name="confirmation")
     */
    public function confirmation()
    {
        return $this->render('landing_page/confirmation.html.twig', [

        ]);
    }

}
