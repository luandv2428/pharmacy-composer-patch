<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Staging\Test\Unit\Plugin\Framework\App;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Staging\Model\Preview\RequestSigner;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for front controller interface plugin.
 */
class FrontControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Staging\Plugin\Framework\App\FrontController
     */
    private $subject;

    /**
     * @var \Magento\Backend\Model\Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Staging\Model\VersionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $versionManagerMock;

    /**
     * @var RequestSigner|MockObject
     */
    private $requestSigner;

    protected function setUp()
    {
        $this->authMock = $this->createMock(\Magento\Backend\Model\Auth::class);
        $this->requestSigner = $this->createMock(RequestSigner::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->objectManager = new ObjectManager($this);

        $this->versionManagerMock = $this->createMock(\Magento\Staging\Model\VersionManager::class);

        $this->subject = $this->objectManager->getObject(
            \Magento\Staging\Plugin\Framework\App\FrontController::class,
            [
                'auth' => $this->authMock,
                'versionManager' => $this->versionManagerMock,
                'requestSigner' => $this->requestSigner
            ]
        );
    }

    /**
     * @param bool $isPreview
     * @param $requestIsValid
     * @param $shouldForward
     * @dataProvider dataProviderBeforeDispatch
     */
    public function testBeforeDispatch($isPreview, $requestIsValid, $shouldForward)
    {
        $frontControllerMock = $this->createMock(\Magento\Framework\App\FrontControllerInterface::class);

        $this->versionManagerMock->expects($this->any())
            ->method('isPreviewVersion')
            ->willReturn($isPreview);

        $this->requestSigner->method('validateUrl')
            ->willReturn($requestIsValid);

        $this->requestMock->method('getRequestString')
            ->willReturn('foo');

        if ($shouldForward) {
            $this->requestMock->expects($this->once())
                ->method('setActionName')
                ->with('noroute');
        } else {
            $this->requestMock->expects($this->never())
                ->method('setActionName');
        }

        $this->subject->beforeDispatch($frontControllerMock, $this->requestMock);
    }

    /**
     * @return array
     */
    public function dataProviderBeforeDispatch()
    {
        return [
            [false, false, false],
            [true, false, true],
            [true, true, false],
        ];
    }

    /**
     * @param bool $isUserExists
     *
     * @return \Magento\Backend\Model\Auth\Credential\StorageInterface|\PHPUnit_Framework_MockObject_MockObject|null
     */
    public function getUserMock($isUserExists)
    {
        if ($isUserExists) {
            return $this->createMock(\Magento\Backend\Model\Auth\Credential\StorageInterface::class);
        }

        return null;
    }
}
