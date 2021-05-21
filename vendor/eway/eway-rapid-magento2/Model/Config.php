<?php
namespace Eway\EwayRapid\Model;

class Config
{
    const ERRORS = 'Errors';

    const CUSTOMER        = 'Customer';
    const REFERENCE       = 'Reference';
    const TITLE           = 'Title';
    const FIRST_NAME      = 'FirstName';
    const LAST_NAME       = 'LastName';
    const COMPANY_NAME    = 'CompanyName';
    const JOB_DESCRIPTION = 'JobDescription';
    const STREET_1        = 'Street1';
    const STREET_2        = 'Street2';
    const CITY            = 'City';
    const STATE           = 'State';
    const POSTAL_CODE     = 'PostalCode';
    const COUNTRY         = 'Country';
    const EMAIL           = 'Email';
    const PHONE           = 'Phone';
    const MOBILE          = 'Mobile';
    const FAX             = 'Fax';
    const URL             = 'Url';
    const CUSTOMER_READ_ONLY = 'CustomerReadOnly';

    const CARD_DETAILS      = 'CardDetails';
    const CARD_NUMBER       = 'Number';
    const CARD_NAME         = 'Name';
    const CARD_EXPIRY_MONTH = 'ExpiryMonth';
    const CARD_EXPIRY_YEAR  = 'ExpiryYear';
    const CARD_CVN          = 'CVN';
    const CARD_START_MONTH  = 'StartMonth';
    const CARD_START_YEAR   = 'StartYear';
    const CARD_ISSUE_NUMBER = 'IssueNumber';

    const PAYMENT             = 'Payment';
    const TOTAL_AMOUNT        = 'TotalAmount';
    const INVOICE_NUMBER      = 'InvoiceNumber';
    const INVOICE_DESCRIPTION = 'InvoiceDescription';
    const INVOICE_REFERENCE   = 'InvoiceReference';
    const CURRENCY_CODE       = 'CurrencyCode';

    const SHIPPING_ADDRESS = 'ShippingAddress';
    const SHIPPING_METHOD  = 'ShippingMethod';

    const ITEMS       = 'Items';
    const SKU         = 'SKU';
    const DESCRIPTION = 'Description';
    const QUANTITY    = 'Quantity';
    const UNIT_COST   = 'UnitCost';
    const TAX         = 'Tax';
    const TOTAL       = 'Total';

    const TRANSACTION_TYPE     = 'TransactionType';
    const TRANSACTION_STATUS   = 'TransactionStatus';
    const TRANSACTION_ID       = 'TransactionID';
    const RESPONSE_MESSAGE     = 'ResponseMessage';
    const RESPONSE_CODE        = 'ResponseCode';
    const RESPONSE_CODE_ACCEPT = '00';
    const RESPONSE_CODE_ACCEPT_2 = '08';
    const AUTHORISATION_CODE   = 'AuthorisationCode';
    const ACCESS_CODE          = 'AccessCode';
    const FORM_ACTION_URL      = 'FormActionURL';
    const DEVICE_ID            = 'DeviceID';
    const CUSTOMER_IP          = 'CustomerIP';
    const PARTNER_ID           = 'PartnerID';
    const PURCHASE             = 'Purchase';
    const MOTO                 = 'MOTO';
    const RECURRING            = 'Recurring';
    const CAPTURE              = 'Capture';
    const REFUND               = 'Refund';
    const SHARED_PAYMENT_URL   = 'SharedPaymentUrl';
    const SECURED_CARD_DATA    = 'SecuredCardData';
    const TOKEN_CUSTOMER_ID    = 'TokenCustomerID';
    const REDIRECT_URL         = 'RedirectUrl';
    const CANCEL_URL           = 'CancelUrl';
    const SAVE_CUSTOMER        = 'SaveCustomer';
    const FRAUD_ACTION         = 'FraudAction';
    const TRANSACTION_CAPTURED = 'TransactionCaptured';
    const BEAGLE_SCORE         = 'BeagleScore';
    const BEAGLE_VERIFICATION  = 'BeagleVerification';

    const TOKEN_ACTION        = 'TokenAction';
    const TOKEN_ACTION_NEW    = 'new';
    const TOKEN_ACTION_UPDATE = 'update';
    const TOKEN_ACTION_USE    = 'use';
    const TOKEN_ID            = 'TokenID';

    const VISA_CHECKOUT_SANDBOX = 'https://sandbox-assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js'; //@codingStandardsIgnoreLine
    const VISA_CHECKOUT_LIVE    = 'https://assets.secure.checkout.visa.com/checkout-widget/resources/js/integration/v1/sdk.js'; //@codingStandardsIgnoreLine

    /**
     * Get card type name by card number
     * @param string $num Card number
     * @return string Card type name
     */
    public static function getCardType($num)
    {
        if (preg_match('/^(4026|417500|4508|4844|4913|4917)/', $num)) {
            return 'VE';
        }
        if (preg_match('/^4/', $num)) {
            return 'VI';
        }
        if (preg_match('/^(34|37)/', $num)) {
            return 'AE';
        }
        if (preg_match('/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[0-1]|2720)/', $num)) {
            return 'MC';
        }
        if (preg_match('/^(2131|1800)/', $num)) {
            return 'JCB';
        }
        if (preg_match('/^36/', $num)) {
            return 'DC';
        }
        if (preg_match('/^(5018|5020|5038|5893|6304|6759|6761|6762|6763)/', $num)) {
            return 'ME';
        }

        return 'Unknown';
    }

    public static function getAvailableTitles()
    {
        return ['', 'Mr.', 'Ms.', 'Mrs.', 'Miss', 'Dr.', 'Sir.', 'Prof.'];
    }

    public static function getVisaCheckoutSdkUrl($mode)
    {
        return $mode == \Eway\EwayRapid\Model\Config\Source\Mode::SANDBOX ?
            self::VISA_CHECKOUT_SANDBOX :
            self::VISA_CHECKOUT_LIVE;
    }
}
