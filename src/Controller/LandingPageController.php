<?php

namespace App\Controller;

use App\Entity\DeliveryOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\DeliveryOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\OrderType;
use App\Form\ProductType;
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
                            $allForm['order']->setDeliveryOrder($allForm['deliveryOrder']);
                            $allForm['order']->setProduct($product);

            if ($allForm['deliveryOrder']->getFirstname() === null) {
                $this->setDeliveryOrder($allForm['order'], $allForm['deliveryOrder']);
            }
            // dd($product, $allForm);
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($allForm['order']);
            // $entityManager->flush();
            $array = [
                'order' => [
                    'id' => $allForm['order']->getProduct()->getId(),
                    'product' => $allForm['order']->getProduct()->getName(),
                    "payment_method"=> "card",
                    "status"=> "WAITING",
                    "client"=> [
                        "firstname"=> $allForm['order']->getFirstname(),
                        "lastname"=> $allForm['order']->getLastname(),
                        "email" => $allForm['order']->getEmail()
                    ],
                    "addresses"=> [
                        "billing"=> [
                          "address_line1"=> $allForm['order']->getAdress(),
                          "address_line2"=> $allForm['order']->getAdressComplement(),
                          "city"=> $allForm['order']->getCity(),
                          "zipcode"=> strval($allForm['order']->getZipCode()),
                          "country"=> $allForm['order']->getCountry(),
                          "phone"=> $allForm['order']->getPhoneNumber()
                        ],
                        "shipping"=> [
                            "address_line1"=> $allForm['order']->getDeliveryOrder()->getAdress(),
                            "address_line2"=> $allForm['order']->getDeliveryOrder()->getAdressComplement(),
                            "city"=> $allForm['order']->getDeliveryOrder()->getCity(),
                            "zipcode"=>  strval ($allForm['order']->getDeliveryOrder()->getZipCode()),
                            "country"=> $allForm['order']->getDeliveryOrder()->getCountry(),
                            "phone"=> $allForm['order']->getDeliveryOrder()->getPhoneNumber()
                        ]
                    ],
                ],
            ];
            $json = json_encode($array);
            dd($json);
            //Contact à l'API
            $url = 'https://api-commerce.simplon-roanne.com/order';
            try {
                // $httpClient = new CurlHttpClient();
                $httpClient = HttpClient::create();

                $response = $httpClient->request( 'POST' , $url, [
                        'headers' => [
                            'accept' => 'application/json',
                            // ,
                            'Authorization' => 'Bearer mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX',
                            'Content-Type' => 'application/json',
                        ],
                        'body' => '{
                            "order": {
                              "id": 1,
                              "product": "Nerf Elite Jolt",
                              "payment_method": "paypal",
                              "status": "WAITING",
                              "client": {
                                "firstname": "François",
                                "lastname": "Dupont",
                                "email": "francois.dupont@gmail.com"
                              },
                              "addresses": {
                                "billing": {
                                  "address_line1": "1, rue du test",
                                  "address_line2": "3ème étage",
                                  "city": "Lyon",
                                  "zipcode": "69000",
                                  "country": "France",
                                  "phone": "string"
                                },
                                "shipping": {
                                  "address_line1": "1, rue du test",
                                  "address_line2": "3ème étage",
                                  "city": "Lyon",
                                  "zipcode": "69000",
                                  "country": "France",
                                  "phone": "string"
                                }
                              }
                            }
                          }'
                ]);

                $statusCode = $response->getStatusCode();
                // $statusCode = 200
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                // $content = $response->toArray();
                dd($statusCode, $content, $response);
            } catch (\Exception $e) {
               dd($e);
            }
            

            return $this->redirectToRoute('');
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

    // //stripe payement 
    // public function stripeProcessing($payment, $request)
    // {
    //     try{
    //         \Stripe\Stripe::setApiKey('sk_test_51H1pgUELWEJ2P8yhcW3i8WyQdJFlx0HeBo4FS5AZcotnzcAR9VJV1PBLV870yK8GvgetgqopG1FKeo7Ei8lbOQA900S8TFpHi5');


    //         $charge = \Stripe\PaymentIntent::create([
    //             'amount' => $payment->getAmount()*100,
    //             'currency' => 'eur',
    //             // Verify your integration in this guide by including this parameter
    //             'metadata' => ['integration_check' => 'accept_a_payment'],
    //         ]);
    //         echo 'Merci pour votre participation';
    //     }
    //     catch(\Exception $e){
    //         dd('erreur payment',$e,$e->getMessage());
    //     }
    // }
}
