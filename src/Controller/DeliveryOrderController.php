<?php

namespace App\Controller;

use App\Entity\DeliveryOrder;
use App\Form\DeliveryOrderType;
use App\Repository\DeliveryOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/delivery/order")
 */
class DeliveryOrderController extends AbstractController
{
    /**
     * @Route("/", name="delivery_order_index", methods={"GET"})
     */
    public function index(DeliveryOrderRepository $deliveryOrderRepository): Response
    {
        return $this->render('delivery_order/index.html.twig', [
            'delivery_orders' => $deliveryOrderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="delivery_order_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $deliveryOrder = new DeliveryOrder();
        $form = $this->createForm(DeliveryOrderType::class, $deliveryOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($deliveryOrder);
            $entityManager->flush();

            return $this->redirectToRoute('delivery_order_index');
        }

        return $this->render('delivery_order/new.html.twig', [
            'delivery_order' => $deliveryOrder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_order_show", methods={"GET"})
     */
    public function show(DeliveryOrder $deliveryOrder): Response
    {
        return $this->render('delivery_order/show.html.twig', [
            'delivery_order' => $deliveryOrder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="delivery_order_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryOrder $deliveryOrder): Response
    {
        $form = $this->createForm(DeliveryOrderType::class, $deliveryOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('delivery_order_index');
        }

        return $this->render('delivery_order/edit.html.twig', [
            'delivery_order' => $deliveryOrder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_order_delete", methods={"DELETE"})
     */
    public function delete(Request $request, DeliveryOrder $deliveryOrder): Response
    {
        if ($this->isCsrfTokenValid('delete'.$deliveryOrder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('delivery_order_index');
    }
}
