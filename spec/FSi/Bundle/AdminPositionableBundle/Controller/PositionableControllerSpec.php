<?php

namespace spec\FSi\Bundle\AdminPositionableBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use FSi\Bundle\AdminPositionableBundle\Model\PositionableInterface;
use FSi\Component\DataIndexer\DoctrineDataIndexer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

/**
 * @mixin \FSi\Bundle\AdminPositionableBundle\Controller\PositionableController
 */
class PositionableControllerSpec extends ObjectBehavior
{
    function let(
        RouterInterface $router,
        CRUDElement $element,
        DoctrineDataIndexer $indexer,
        ObjectManager $om
    ) {
        $element->getId()->willReturn('slides');
        $element->getDataIndexer()->willReturn($indexer);
        $element->getObjectManager()->willReturn($om);
        $element->getRoute()->willReturn('fsi_admin_crud_list');
        $element->getRouteParameters()->willReturn(array('element' => 'slides'));

        $this->beConstructedWith($router);
    }

    function it_throws_runtime_exception_when_entity_doesnt_implement_proper_interface(
        CRUDElement $element,
        DoctrineDataIndexer $indexer
    ) {
        $indexer->getData(666)->willReturn(new \StdClass());

        $this->shouldThrow('\RuntimeException')
            ->duringIncreasePositionAction($element, 666);

        $this->shouldThrow('\RuntimeException')
            ->duringDecreasePositionAction($element, 666);
    }

    function it_throws_runtime_exception_when_specified_entity_doesnt_exist(
        CRUDElement $element,
        DoctrineDataIndexer $indexer
    ) {
        $indexer->getData(666)->willThrow('FSi\Component\DataIndexer\Exception\RuntimeException');

        $this->shouldThrow('FSi\Component\DataIndexer\Exception\RuntimeException')
            ->duringIncreasePositionAction($element, 666);

        $this->shouldThrow('FSi\Component\DataIndexer\Exception\RuntimeException')
            ->duringDecreasePositionAction($element, 666);
    }

    function it_decrease_position_when_decrease_position_action_called(
        CRUDElement $element,
        DoctrineDataIndexer $indexer,
        PositionableInterface $positionableEntity,
        ObjectManager $om,
        RouterInterface $router
    ) {
        $indexer->getData(1)->willReturn($positionableEntity);

        $positionableEntity->decreasePosition()->shouldBeCalled();

        $om->persist($positionableEntity)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $router->generate('fsi_admin_crud_list', array('element' => 'slides'))
            ->willReturn('sample-path');

        $response = $this->decreasePositionAction($element, 1);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('sample-path');
    }

    function it_increase_position_when_increase_position_action_called(
        CRUDElement $element,
        DoctrineDataIndexer $indexer,
        PositionableInterface $positionableEntity,
        ObjectManager $om,
        RouterInterface $router
    ) {
        $indexer->getData(1)->willReturn($positionableEntity);

        $positionableEntity->increasePosition()->shouldBeCalled();

        $om->persist($positionableEntity)->shouldBeCalled();
        $om->flush()->shouldBeCalled();

        $router->generate('fsi_admin_crud_list', array('element' => 'slides'))
            ->willReturn('sample-path');

        $response = $this->increasePositionAction($element, 1);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('sample-path');
    }
}
