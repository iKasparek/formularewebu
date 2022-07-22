<?php

class Formulare
{

    private $classKontaktni;
    private $classNewsletter;
    private $classFakturace;
    private $classPoptavky;

    public function __construct()
    {
        $this->classKontaktni     = new OdesilacZprav();
        $this->classNewsletter    = new OdesilacNewsletteru();
        $this->classFakturace     = new OdesilacFaktury();
        $this->classPoptavky      = new OdesilacPoptavky();
    }

    public function vypisKontaktni()
    {
        $this->classKontaktni->vypisKontaktni();
    }

    public function pridejKontaktni()
    {
        $this->classKontaktni->pridejKontaktni();
    }

    public function vypisNewsletter()
    {
        $this->classNewsletter->vypisNewsletter();
    }
    public function pridejNewsletter()
    {
        $this->classNewsletter->pridejNewsletter();
    }

    public function vypisFakturaci()
    {
        $this->classFakturace->vypisFakturu();
    }
    public function pridejFakturaci()
    {
        $this->classFakturace->pridejFakturu();
    }

    public function vypisPoptavku()
    {
        $this->classPoptavky->vypisPoptavku();
    }
    public function pridejPoptavku()
    {
        $this->classPoptavky->pridejPoptavku();
    }

}
