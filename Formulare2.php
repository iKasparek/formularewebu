<?php

class Formulare2
{

    private $classKontaktni;
    private $classNewsletter;
    private $classFakturace;

    public function __construct()
    {
        $this->classKontaktni     = new OdesilacZprav();
        $this->classNewsletter    = new OdesilacNewsletteru();
        $this->classFakturace     = new OdesilacFaktury();
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

}
