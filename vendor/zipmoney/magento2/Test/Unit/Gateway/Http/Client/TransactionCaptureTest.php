<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  ZipMoney\ZipMoneyPayment\Test\Unit\Gateway\Http\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\RequestInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Framework\Message\ManagerInterface;
use ZipMoney\ZipMoneyPayment\Model\Charge;
use ZipMoney\ZipMoneyPayment\Model\Config;
use ZipMoney\ZipMoneyPayment\Helper\Payload;
use ZipMoney\ZipMoneyPayment\Helper\Logger;
use ZipMoney\ZipMoneyPayment\Helper\Data as ZipMoneyDataHelper; 

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */


class TransactionCaptureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected $messageManager;

    public function setUp()
    {
       
        $objManager = new ObjectManager($this);
        
        $config = $this->getMockBuilder(Config::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $config->expects(static::any())->method('getLogSetting')->willReturn(10);  

        $this->_chargesApiMock = $this->getMockBuilder(\zipMoney\Api\ChargesApi::class)->getMock();
        
        $this->_clientMock = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Gateway\Http\Client\TransactionCapture", 
            [ '_service' => $this->_chargesApiMock]);
        
    }
    /**
     * @param array $expectedRequest
     * @param array $expectedResponse
     *
     * @dataProvider placeRequestDataProvider
     */
    public function testPlaceRequest( $expectedRequest, $expectedResponse)
    {          

        $transferObject = $this->getMockBuilder("\Magento\Payment\Gateway\Http\TransferInterface")->getMock();

        $transferObject->expects(static::any())->method('getBody')->willReturn($expectedRequest);
        
        $this->_chargesApiMock->expects(static::any())->method('chargesCapture')->willReturn( $expectedResponse   );

        static::assertEquals(
            [ 'api_response' => $expectedResponse ],
            $this->_clientMock->placeRequest($transferObject)
        );
    }


    public function placeRequestDataProvider()
    {   
        $chargeResponse = new \zipMoney\Model\Charge;
      
        $chargeResponse->setId("112343");
        $chargeResponse->setState("captured");
        return [
            'success' => [
                'expectedRequest' => [
                    'payload' => array(),
                    'zipmoney_checkout_id' => 123
                ],
                $chargeResponse
            ]
        ];
    }

}
