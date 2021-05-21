<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleStaging\Test\Unit\Model\Operation;

use Magento\CatalogRuleStaging\Model\Operation\Create;

/**
 * Test for Create model
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $versionManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $updateRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationUpdateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $updateFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationCreateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $updateMock;

    /**
     * @var \Magento\CatalogRuleStaging\Model\Operation\Create
     */
    private $operation;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    protected function setUp()
    {
        $this->versionManagerMock = $this->createMock(\Magento\Staging\Model\VersionManager::class);
        $this->updateRepositoryMock = $this->createMock(\Magento\Staging\Api\UpdateRepositoryInterface::class);
        $this->operationUpdateMock = $this->createMock(\Magento\Staging\Model\Operation\Update::class);
        $this->updateFactoryMock = $this->createPartialMock(
            \Magento\Staging\Api\Data\UpdateInterfaceFactory::class,
            ['create']
        );
        $this->operationCreateMock = $this->createMock(\Magento\Staging\Model\Operation\Create::class);

        $this->updateMock = $this->createMock(\Magento\Staging\Api\Data\UpdateInterface::class);
        $this->entityMock = $this->createMock(\Magento\CatalogRule\Api\Data\RuleInterface::class);
        $this->operation = new Create(
            $this->versionManagerMock,
            $this->updateRepositoryMock,
            $this->operationUpdateMock,
            $this->updateFactoryMock,
            $this->operationCreateMock
        );
    }

    public function testExecute()
    {
        //execute create operation
        $this->operationCreateMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->entityMock)
            ->willReturn($this->entityMock);

        $this->assertEquals($this->entityMock, $this->operation->execute($this->entityMock));
    }
}
