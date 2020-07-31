<?php

namespace App\Controller;

use App\Entity\DeliveryOrder;
use App\Entity\Order;
use App\Entity\Payment;
use App\Form\DeliveryOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\OrderType;
use App\Form\PaymentType;
use App\Repository\OrderRepository;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;




class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, OrderRepository $orderRepository)
    {
        $allForm = [
            'order' => new Order(),
            'deliveryOrder' => new DeliveryOrder()
        ];
        
        $form = $this->createFormBuilder($allForm)
                     ->add('order', OrderType::class)
                     ->add('deliveryOrder', DeliveryOrderType::class)
                     ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $allForm['order']->setDeliveryOrder($allForm['deliveryOrder']);

            if ($allForm['deliveryOrder']->getFirstname() === null && $allForm['deliveryOrder']->getAdress()=== null) {
                $this->setDeliveryOrder($allForm['order'], $allForm['deliveryOrder']);
            }
            $allForm['order']->setMethodPayment($request->request->get('payment'));
            
            

            
            $order =  $allForm['order'];

            $array = [
                'order' => [
                    'id' => $order->getId(),
                    'product' => $order->getProduct()->getName(),
                    "payment_method"=> $order->getMethodPayment(),
                    "status" => "WAITING",
                    "client" => [
                        "firstname"=> $order->getFirstname(),
                        "lastname"=> $order->getLastname(),
                        "email" => $order->getEmail(),
                    ],
                    "addresses" => [
                        "billing" => [
                          "address_line1"=> $order->getAdress(),
                          "address_line2"=> $order->getAdressComplement(),
                          "city"=> $order->getCity(),
                          "zipcode"=> strval($order->getZipCode()),
                          "country"=> $order->getCountry(),
                          "phone"=> $order->getPhoneNumber(),
                        ],
                        "shipping" => [
                            "address_line1"=> $order->getDeliveryOrder()->getAdress(),
                            "address_line2"=> $order->getDeliveryOrder()->getAdressComplement(),
                            "city"=> $order->getDeliveryOrder()->getCity(),
                            "zipcode"=>  strval ($order->getDeliveryOrder()->getZipCode()),
                            "country"=> $order->getDeliveryOrder()->getCountry(),
                            "phone"=> $order->getDeliveryOrder()->getPhoneNumber(),
                        ]
                    ]
                ]
            ];
            $json = json_encode($array);

                $responseApi = $this->APICreateOrder($json);
                $order->setIdOrderApi($responseApi['order_id']);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($order);
                $entityManager->persist($order->getDeliveryOrder());
                $entityManager->flush();

            return $this->redirectToRoute('payment',[
                'id' => $order->getId(),
                'order_id' => $responseApi['order_id']
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
    public function payment(Order $order, $order_id, Request $request,OrderRepository $orderRepository,MailerInterface $mailer)
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
            $this->sendEmail($mailer , $order);


            return $this->redirectToRoute('confirmation');
        }


        return $this->render('landing_page/payment.html.twig', [
                    'form' => $form->createView(),
                    'order' => $order,
                    'amount' => number_format($order->getProduct()->getPrice()/100, 2, ',', '')
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
    /**
     * @Route("/email")
     */
    public function sendEmail(MailerInterface $mailer, Order $order )
    {
        $email = (new TemplatedEmail())
                    ->from(new Address('Hamza.karfa@gmail.com', 'Hamza Mail Bot'))
                    ->to('Hamza.karfa@gmail.com')
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('emails/confirmation.html.twig')
                    ->context([
                        'name' => $order->getFirstname(),
                        'product' => $order->getProduct()
                    ]);

        $mailer->send($email);
    }
}
