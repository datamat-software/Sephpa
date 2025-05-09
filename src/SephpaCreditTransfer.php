<?php
/*
 * Sephpa
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2025 Alexander Schickedanz
 * @link      https://github.com/AbcAeffchen/Sephpa
 *
 * @author  Alexander Schickedanz <abcaeffchen@gmail.com>
 */

namespace AbcAeffchen\Sephpa;
use AbcAeffchen\SepaUtilities\SepaUtilities;

require_once __DIR__ . '/Sephpa.php';

/**
 * Base class for both credit transfer and direct debit
 */
class SephpaCreditTransfer extends Sephpa
{
    // credit transfers versions
    public const SEPA_PAIN_001_001_03 = SepaUtilities::SEPA_PAIN_001_001_03;
    public const SEPA_PAIN_001_002_03 = SepaUtilities::SEPA_PAIN_001_002_03;
    public const SEPA_PAIN_001_003_03 = SepaUtilities::SEPA_PAIN_001_003_03;
    public const SEPA_PAIN_001_001_09 = SepaUtilities::SEPA_PAIN_001_001_09;

    private const INITIAL_STRING_PAIN_001_001_03 = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03 pain.001.001.03.xsd"></Document>';
    private const INITIAL_STRING_PAIN_001_002_03 = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.002.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.002.03 pain.001.002.03.xsd"></Document>';
    private const INITIAL_STRING_PAIN_001_003_03 = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.003.03" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.003.03 pain.001.003.03.xsd"></Document>';
    private const INITIAL_STRING_PAIN_001_001_09 = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09 pain.001.001.09.xsd"></Document>';

    private const VERSIONS = [self::SEPA_PAIN_001_001_03 => ['class'   => '00100103',
                                                             'initStr' => self::INITIAL_STRING_PAIN_001_001_03],
                              self::SEPA_PAIN_001_002_03 => ['class'   => '00100203',
                                                             'initStr' => self::INITIAL_STRING_PAIN_001_002_03],
                              self::SEPA_PAIN_001_003_03 => ['class'   => '00100303',
                                                             'initStr' => self::INITIAL_STRING_PAIN_001_003_03],
                              self::SEPA_PAIN_001_001_09 => ['class'   => '00100109',
                                                             'initStr' => self::INITIAL_STRING_PAIN_001_001_09]];

    /**
     * Creates a SepaXmlFile object and sets the head data
     *
     * @param string   $initgPty   The name of the initiating party
     * @param string   $msgId      The unique id of the file (max. 70 characters)
     * @param int      $version    Sets the type and version of the sepa file. Use the SEPA_PAIN_*
     *                             constants
     * @param string[] $orgId      It is not recommended to use this at all. If you have to use
     *                             this, the standard only allows one of the two. If you provide
     *                             both, options, both will be included in the SEPA file. So
     *                             only use this if you know what you do. Available keys:
     *                             - `id`: An Identifier of the organisation.
     *                             - `bob`: A BIC or BEI that identifies the organisation.
     *                             - `scheme_name`: max. 35 characters.
     * @param string   $initgPtyId An ID of the initiating party (max. 35 characters)
     * @param bool     $checkAndSanitize
     * @throws SephpaInputException
     */
    public function __construct($initgPty, $msgId, $version, array $orgId = [], $initgPtyId = null, $checkAndSanitize = true)
    {
        parent::__construct($initgPty, $msgId, $orgId, $initgPtyId, $checkAndSanitize);

        $this->orgIdBicTag = $version === self::SEPA_PAIN_001_001_09 ? 'AnyBIC' : 'BICOrBEI';

        $this->paymentType = 'CstmrCdtTrfInitn';

        if(!isset(self::VERSIONS[$version]))
            throw new SephpaInputException('You choose an invalid SEPA file version. Please use the SEPA_PAIN_001_* constants.');

        $this->version = $version;
        $this->xmlInitString = self::VERSIONS[$version]['initStr'];
    }

    /**
     * Adds a new payment to the SEPA file.
     *
     * @param array $collectionInfo @see \Sephpa\SepaCreditTransfer*::addPayment() for details.
     * @return PaymentCollections\SepaPaymentCollection
     */
    public function addCollection(array $collectionInfo) : PaymentCollections\SepaPaymentCollection
    {
        $class = 'AbcAeffchen\Sephpa\PaymentCollections\SepaCreditTransfer00' . $this->version;
        $this->paymentCollections[] = new $class($collectionInfo, $this->checkAndSanitize, $this->sanitizeFlags);
        return end($this->paymentCollections);
    }
}
