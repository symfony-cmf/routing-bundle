<?php
/**
 * User: avasilenko
 * Date: 27.5.13
 * Time: 23:43
 */
namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Admin;

use Symfony\Cmf\Bundle\RoutingBundle\Admin\RouteAdmin;
use Symfony\Component\Routing\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

class RouteAdminTest extends BaseTestCase
{
    /**
     * @var RouteAdmin
     */
    private $routeAdmin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $errorElement;

    protected function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->routeAdmin = $this->getContainer()->get('cmf_routing.route_admin');
        $this->errorElement = $this->getMockBuilder('Sonata\AdminBundle\Validator\ErrorElement')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCorrectControllerPath()
    {
        $route = new Route('/', array('_controller' => 'FrameworkBundle:Redirect:redirect'));

        $this->errorElement->expects($this->never())
            ->method('with')
        ;

        $this->routeAdmin->validate($this->errorElement, $route);
    }

    public function testControllerPathViolation()
    {
        $route = new Route('/', array('_controller' => 'NotExistingBundle:Foo:bar'));
        $this->errorElement->expects($this->once())
            ->method('with')
            ->with('defaults')
            ->will($this->returnSelf());
        $this->errorElement->expects($this->once())
            ->method('addViolation')
            ->with($this->anything())
            ->will($this->returnSelf());
        $this->errorElement->expects($this->once())
            ->method('end');

        $this->routeAdmin->validate($this->errorElement, $route);
    }

    public function testTemplateViolation()
    {
        $route = new Route('/', array('_template' => 'NotExistingBundle:Foo:bar.html.twig'));
        $this->errorElement->expects($this->once())
            ->method('with')
            ->with('defaults')
            ->will($this->returnSelf());
        $this->errorElement->expects($this->once())
            ->method('addViolation')
            ->with($this->anything())
            ->will($this->returnSelf());
        $this->errorElement->expects($this->once())
            ->method('end');

        $this->routeAdmin->validate($this->errorElement, $route);
    }

    public function testCorrectTemplate()
    {
        // the template 'TwigBundle::layout.html.twig' is not found in the test setup...
        $mockTemplate = $this->getMockBuilder('Twig_Template')->disableOriginalConstructor()->getMockForAbstractClass();
        $route = new Route('/', array('_template' => $mockTemplate));
        $this->errorElement->expects($this->never())
            ->method('with')
        ;

        $this->routeAdmin->validate($this->errorElement, $route);
    }
}
