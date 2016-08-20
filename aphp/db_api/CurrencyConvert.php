<?php


/** Class that handles conversion between currencies.  */
class CurrencyConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbCurrencyData )
    {
		if ( null != $dbCurrencyData ) $this->initialiseFromTableDb( $dbCurrencyData );
    }

    function 	initialiseFromTableDb( $dbCurrencyData )
    {
		$aFromEuroRows = $dbCurrencyData->getBaseDataRows(0,0);	// Get all rows
		foreach ( $aFromEuroRows as $aFromEuroCross ) {
			$currency_id 	= $aFromEuroCross['currency_name'];
			$from_euro		= (double)$aFromEuroCross['from_euro'];
			$to_euro		= 1.0 / $from_euro;
			$this->m_aFromEuro[$currency_id] = $from_euro;
			$this->m_aToEuro[$currency_id] = $to_euro;
		}
    }

    function 	fromEuro( $currency_id, $price_local )
    {
		$fac = (double)$this->m_aFromEuro[$currency_id];
		return $fac * $price_local;
    }
    
    function 	toEuro( $currency_id, $price_local )
    {
		$fac = (double)$this->m_aToEuro[$currency_id];
		return $fac * $price_local;
    }
    
    // ---------------------
    // --- PUBLIC: Data --- 
    // ---------------------
	// We have made these public on purpose!
    
    public     $m_aFromEuro 	= array ();
    public     $m_aToEuro 		= array ();

    
}



?>