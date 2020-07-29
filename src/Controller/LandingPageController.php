<?php

namespace App\Controller;

use App\Entity\DeliveryOrder;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Product;
use App\Form\DeliveryOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\OrderType;
use App\Form\PaymentType;
use App\Form\ProductType;
use App\Repository\OrderRepository;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;


class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, OrderRepository $orderRepository)
    {
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

            $allForm['order']->setDeliveryOrder($allForm['deliveryOrder']);
            $allForm['order']->setProduct($product);

            if ($allForm['deliveryOrder']->getFirstname() === null) {
                $this->setDeliveryOrder($allForm['order'], $allForm['deliveryOrder']);
            }
            
            $allForm['order']->setMethodPayment($request->request->get('payment'));
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($allForm['order']);
            $entityManager->flush();
            
            $order =  $orderRepository->getOrder($allForm['order']->getEmail());

            $array = [
                'order' => [
                    'id' => $order[0]->getProduct()->getId(),
                    'product' => $order[0]->getProduct()->getName(),
                    "payment_method"=> $order[0]->getMethodPayment(),
                    "status" => "WAITING",
                    "client" => [
                        "firstname"=> $order[0]->getFirstname(),
                        "lastname"=> $order[0]->getLastname(),
                        "email" => $order[0]->getEmail(),
                    ],
                    "addresses" => [
                        "billing" => [
                          "address_line1"=> $order[0]->getAdress(),
                          "address_line2"=> $order[0]->getAdressComplement(),
                          "city"=> $order[0]->getCity(),
                          "zipcode"=> strval($order[0]->getZipCode()),
                          "country"=> $order[0]->getCountry(),
                          "phone"=> $order[0]->getPhoneNumber(),
                        ],
                        "shipping" => [
                            "address_line1"=> $order[0]->getDeliveryOrder()->getAdress(),
                            "address_line2"=> $order[0]->getDeliveryOrder()->getAdressComplement(),
                            "city"=> $order[0]->getDeliveryOrder()->getCity(),
                            "zipcode"=>  strval ($order[0]->getDeliveryOrder()->getZipCode()),
                            "country"=> $order[0]->getDeliveryOrder()->getCountry(),
                            "phone"=> $order[0]->getDeliveryOrder()->getPhoneNumber(),
                        ]
                    ]
                ]
            ];
            $json = json_encode($array);

               $test = $this->APICreateOrder($json);

            return $this->redirectToRoute('payment',[
                'id' => $order[0]->getId(),
                'order_id' => $test['order_id']
            ]);
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

    //setter address livraison 
    public function setDeliveryOrder( Order $order , DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->setFirstname($order->getFirstname());
        $deliveryOrder->setLastname($order->getLastname());
        $deliveryOrder->setAdress($order->getAdress());
        if ($order->getAdressComplement() !== null) {
            $deliveryOrder->setAdressComplement($order->getAdressComplement());
        }
        $deliveryOrder->setCity($order->getCity());
        $deliveryOrder->setZipCode($order->getZipCode());
        $deliveryOrder->setCountry($order->getCountry());
        $deliveryOrder->setPhoneNumber($order->getPhoneNumber());
    }

    /**
     * @Route("/payment/{id}/{order_id}", name="payment")
     */
    public function payment(Order $order, $order_id, Request $request,OrderRepository $orderRepository)
    {

        $fullOrder  = $orderRepository->getOrder($order->getEmail());
        $order = $fullOrder[0];
        $payment = new Payment();
        
        $form = $this->createForm(PaymentType::class, $payment);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setClient($order);
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($payment);
            $entityManager->flush();
            
            $this->stripeProcessing($order);
            $this->APIUpdateOrder($order_id);


            return $this->redirectToRoute('confirmation');
        }


        return $this->render('landing_page/payment.html.twig', [
                    'form' => $form->createView(),
                    'order' => $order,
                    'amount' => floatval($order->getProduct()->getPrice())
            ]);
    }
    public function stripeProcessing($order)
    {
        try{
                
            Stripe::setApiKey('sk_test_51H1pgUELWEJ2P8yhcW3i8WyQdJFlx0HeBo4FS5AZcotnzcAR9VJV1PBLV870yK8GvgetgqopG1FKeo7Ei8lbOQA900S8TFpHi5');

            $charge = \Stripe\PaymentIntent::create([
                'amount' => floatval($order->getProduct()->getPrice())*100,
                'currency' => 'eur',
                // Verify your integration in this guide by including this parameter
                'metadata' => ['integration_check' => 'accept_a_payment'],
            ]);
            echo 'Merci pour votre participation';
            $order->setStatusPayment('PAID');
      
        }
        catch(\Exception $e){
            dd('erreur payment',$e,$e->getMessage());
        }
    }
    public function APICreateOrder($json)
    {

        $url = 'https://api-commerce.simplon-roanne.com/order';
         try {
             // $httpClient = new CurlHttpClient();
             $httpClient = HttpClient::create();
    
             $response = $httpClient->request( 'POST' , $url, [
                     'headers' => [
                         'accept' => 'application/json',
                         'Authorization' => 'Bearer mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX',
                         'Content-Type' => 'application/json',
                     ],
                     'body' => $json
             ]);
    
             $statusCode = $response->getStatusCode();
             // $statusCode = 200
             $contentType = $response->getHeaders()['content-type'][0];
             // $contentType = 'application/json'
             $content = $response->getContent();
             // $content = '{"id":521583, "name":"symfony-docs", ...}'
              $content = $response->toArray();
            
              return $content;

         } catch (\Exception $e) {
            dd($e);
         }
    }
    public function APIUpdateOrder($orderId)
    {
        $arrayStatus = [
            'status' => 'PAID'
        ];

        $url = 'https://api-commerce.simplon-roanne.com/order/'.$orderId.'/status';
         try {
             // $httpClient = new CurlHttpClient();
             $httpClient = HttpClient::create();
    
             $response = $httpClient->request( 'POST' , $url, [
                     'headers' => [
                         'accept' => 'application/json',
                         'Authorization' => 'Bearer mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX',
                         'Content-Type' => 'application/json',
                     ],
                     'body' => json_encode($arrayStatus)
             ]);
    
             $statusCode = $response->getStatusCode();
             // $statusCode = 200
             $contentType = $response->getHeaders()['content-type'][0];
             // $contentType = 'application/json'
             $content = $response->getContent();
             // $content = '{"id":521583, "name":"symfony-docs", ...}'
              $content = $response->toArray();
         } catch (\Exception $e) {
            dd($e);
         }

    }

}
