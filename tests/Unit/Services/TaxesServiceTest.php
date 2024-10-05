<?php

namespace Tests\Unit\Services;

use App\Services\Store\TaxesService;
use Tests\TestCase;

class TaxesServiceTest extends TestCase
{
    public function test_taxes_with_tax_included()
    {
        // on s'attend à ce que le montant de la taxe soit de 20 (20% de 100)
        $this->assertEquals(20, TaxesService::getTaxAmount(100, 20, TaxesService::MODE_TAX_INCLUDED));
        // on s'attend à ce que le montant total soit de 80 (100 - 20)
        $this->assertEquals(100, TaxesService::getAmount(100, 20, TaxesService::MODE_TAX_INCLUDED));
    }

    public function test_taxes_with_tax_excluded()
    {
        $this->assertEquals(20, TaxesService::getTaxAmount(100, 20, TaxesService::MODE_TAX_EXCLUDED));
        $this->assertEquals(80, TaxesService::getAmount(100, 20, TaxesService::MODE_TAX_EXCLUDED));
    }

    public function test_taxe_with_null_mode()
    {
        $this->assertEquals(20, TaxesService::getTaxAmount(100, 20));
        $this->assertEquals(100, TaxesService::getAmount(100, 20));
    }

    public function test_taxe_arround_excluded()
    {
        $this->assertEquals(20.01, TaxesService::getTaxAmount(100.05, 20));
        $this->assertEquals(100.05, TaxesService::getAmount(100.05, 20));
    }

    public function test_taxe_arround_included()
    {
        $this->assertEquals(20.01, TaxesService::getTaxAmount(100.05, 20, TaxesService::MODE_TAX_INCLUDED));
        $this->assertEquals(100.5, TaxesService::getAmount(100.05, 20, TaxesService::MODE_TAX_INCLUDED));
    }

}
