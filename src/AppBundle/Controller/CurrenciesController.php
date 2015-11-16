<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Currency;


class CurrenciesController extends Controller
{
    /**
     * @Route("/")
     */
	
    public function indexAction()
    {
        $currencies = $this->loadCurrenciesFromLB(); //Load currencies from Latvijas Banka
        $total_count = $this->getTotalCount(); //Get count of rows in currency table
        
        if($total_count == 0)
        {	
        	$this->saveCurrencies($currencies); //first insert to empty DB
        } else {
        	$date_count = $this->getDateCount(); //get count of rows where current date is not equal to row date
        	if($date_count > 0) 
        		$this->updateCurrencies($currencies); //update currency rates in currency table
        }

        return $this->render(
                'currencies/currencies.html.twig',
                array('currencies'=>$currencies, 'current_date'=>date('d.m.Y'))
            );
    }

	/*
		Load XML data with currencies and rates from Latvijas Banka and processed to array
  	*/
    private function loadCurrenciesFromLB()
    {
        $target =  'http://bank.lv/vk/ecb.xml?date='.date('Ymd');

        $doc = new \DOMDocument();
        $doc->load($target);
        $root = $doc->firstChild;

        if (!$root || $root->nodeName == 'Error')
        {
        	throw $this->createNotFoundException(
		            'No currency node found!'
		        );
        }

        $rateDate = $root->getElementsByTagName('Date')->item(0)->nodeValue;
        $curList = $root->getElementsByTagName('Currency');

        foreach ($curList as $item)
        {
            $name = $item->getElementsByTagName('ID')->item(0)->nodeValue;
            $rate = $item->getElementsByTagName('Rate')->item(0)->nodeValue;
            $currencies[] = array('name'=>$name, 'rate'=>$rate);
            
        }

        return $currencies;   
    }

	/*
		Save currencies and rates to DB
	*/
    private function saveCurrencies($currencies = array())
    {
    	if(!is_array($currencies)) return false;
    	
    	foreach($currencies as $currency)
    	{
	    	$entity = new Currency();
	    	$entity->setName($currency['name']);
	    	$entity->setRate($currency['rate']);
	    	$entity->setDate();
	    	
	    	$em = $this->getDoctrine()->getManager();

		    $em->persist($entity);
		    $em->flush();
	    }

	    return $this->redirectToRoute('homepage');
    }    

    /*
		Update currency rates with new ones
    */
    private function updateCurrencies($currencies = array())
    {
    	if(!is_array($currencies)) return false;
    	
    	foreach($currencies as $currency)
    	{
		    $em = $this->getDoctrine()->getManager();
		    $entity = $em->getRepository('AppBundle:Currency')->findByName($currency['name']);

		    if (!$entity) {
		        throw $this->createNotFoundException(
		            "No currency found for name ".$currency['name']
		        );
		    }

		    $entity[0]->setRate($currency['rate']);
		    $entity[0]->setDate();
		    $em->flush();

	    }

	    return $this->redirectToRoute('homepage');
    }

    /*
		Get total count of rows in currency table
	*/
    private function getTotalCount()
    {
		$em = $this->getDoctrine()->getManager();
		$count = $em->getRepository('AppBundle:Currency')->findTotalCount();

		return $count;
    }

    /*
		Get total count of rows where current date is not equal to row date in currency table
	*/
    private function getDateCount()
    {
		$em = $this->getDoctrine()->getManager();
		$count = $em->getRepository('AppBundle:Currency')->findDateCount();

		return $count;
    }
}
