<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\{Customer, Address, EventType, Event};

use App\Form\CustomerType;

use App\Service\EventCreator;

class CRMController extends AbstractController
{

    /**
     * @Route("/crm/customers", name="crm_customer_list", methods={"GET"})
     * @Route("/crm/prospectives", name="crm_prospective_list", methods={"GET"})
     */
    public function customerList()
    {
        $customerRepository = $this->getDoctrine()->getRepository(Customer::class);
        
        $allCustomers = $customerRepository->findAll();
        $prospectiveCustomers = $customerRepository->findByIsProspectiveCustomer(true);
        $onlyCustomers = $customerRepository->findByIsProspectiveCustomer(false);

        return $this->render('CRM/customer-list.html.twig', [
            'AllCustomers' => $allCustomers,
            'ProspectiveCustomers' => $prospectiveCustomers,
            'OnlyCustomers' => $onlyCustomers,
        ]);
    }

    /**
     * @Route("/crm/customers/{id}", name="crm_customer_view", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function customerViewResume(Customer $customer)
    {
        //$eventCreator->setDescription('Encore une nouvelle info ! ');
        //$eventCreator->createEvent($customer, EventType::TYPE_COMMENT_ADD);

        $events = $this->getDoctrine()
                       ->getRepository(Event::class)
                       ->findByCustomer($customer,
                                        ['dateCreate'=>'DESC'],
                                        10 // Limit
                                    ); 
        return $this->render('CRM/customer-view.html.twig', [
            'Customer' => $customer,
            'Events' => $events,
            ]);
    }

    /**
     * @Route("/crm/customers/{id}/detail", name="crm_customer_detail", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function customerUpdate(Request $request, Customer $customer)
    {
        $form =  $this->createForm(CustomerType::class, $customer)
                      ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()
                                  ->getManager();

            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success','Le client a bien été modifié !');

            return $this->redirectToRoute('crm_customer_detail', ['id' => $customer->getId()]);
        }

        return $this->render('CRM/customer-detail.html.twig', [
            'Customer' => $customer,
            'CustomerForm' => $form->createView(),
            ]);
    }

    /**
     * @Route("/crm/customers/add", name="crm_customer_add", methods={"GET", "POST"})
     */
    public function customerAdd(Request $request)
    {
        $customer = new Customer();
        $form =  $this->createForm(CustomerType::class, $customer)
                      ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()
                                  ->getManager();

            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success','Le client a bien été ajouté !');

            return $this->redirectToRoute('crm_customer_detail', ['id' => $customer->getId()]);
        }

        return $this->render('CRM/customer-add.html.twig', [
            'CustomerForm' => $form->createView(),
            ]);
    }
    
    /**
     * @Route("/crm/customers/delete/{id}", name="crm_customer_delete", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function customerDelete(Customer $customer){

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($customer);
        $entityManager->flush();

        $this->addFlash('info','Le client / prospect a bien été supprimé.');
        
        return $this->redirectToRoute('crm_customer_list');
    }

    /**
     * @Route("/crm/address-book/{page}", name="crm_addressbook", 
     *                                    requirements={"page"="\d+"},
     *                                    defaults={"page"=1},
     *                                    methods={"GET"})
     */
    public function addressBook($page){
        if ($page < 1) {
            throw $this->createNotFoundException('La page '.$page.' n\'existe pas.');
          }

        $nbPerPage = 12;

        $customers = $this->getDoctrine()
                          ->getRepository(Customer::class)
                          ->getAddressBookCustomers($page, $nbPerPage);

        $nbPages = ceil(count($customers) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }
  
        return $this->render('CRM/address-book.html.twig', [
            'Customers'   => $customers,
            'NbPages'     => $nbPages,
            'Page'        => $page,
            ]);
    }
}
