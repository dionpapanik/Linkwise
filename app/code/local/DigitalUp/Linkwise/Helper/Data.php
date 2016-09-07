<?php

class DigitalUp_Linkwise_Helper_Data extends Mage_Core_Helper_Abstract
{
    const GET_ENABLED = 'linkwise/general/enabled';
    const GET_LINKWISE_ID = 'linkwise/general/linkwise_id';
    const GET_DECIMAL_SEPARATOR = 'linkwise/general/decimal_separator';
    const GET_PRICE_TAX_CONFIG = 'linkwise/general/price_tax_config';

    protected $_enabled = null;
    protected $_linkwiseId = null;
    protected $_decimalSep = null;
    protected $_taxConfig = null;

    /**
     * Checks if module is enabled
     *
     * @return  bool
     */
    public function isEnabled()
    {
        if (is_null($this->_enabled)) {
            $this->_enabled = (bool)Mage::getStoreConfig(self::GET_ENABLED);
        }
        return $this->_enabled;

    }

    /**
     * Get Linkwise ID
     *
     * @return  string
     */
    public function getLinkwiseId()
    {
        if (is_null($this->_linkwiseId)) {
            $this->_linkwiseId = (string)Mage::getStoreConfig(self::GET_LINKWISE_ID);
        }
        return $this->_linkwiseId;
    }

    /**
     * Get Decimal Separator
     *
     * @return  string
     */
    public function getDecimalSeparator()
    {
        if (is_null($this->_decimalSep)) {
            $this->_decimalSep = (string)Mage::getStoreConfig(self::GET_DECIMAL_SEPARATOR);
        }
        return $this->_decimalSep;
    }

    /**
     * Show product prices with or without tax
     *
     * @return  float
     */
    public function getTaxConfig()
    {
        if (is_null($this->_taxConfig)) {
            $this->_taxConfig = floatval(Mage::getStoreConfig(self::GET_PRICE_TAX_CONFIG));
        }
        return $this->_taxConfig;
    }

}
