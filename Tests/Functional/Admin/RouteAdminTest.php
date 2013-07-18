<?php
/**
 * User: avasilenko
 * Date: 27.5.13
 * Time: 23:43
 */
namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Admin;

use Symfony\Cmf\Bundle\RoutingBundle\Admin\RouteAdmin;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;
use Symfony\Component\Routing\Route;

class RouteAdminTest extends BaseTestCase
{
    /**
     * @var RouteAdmin
     */
    private static $routeAdmin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $errorElement;

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setUpBeforeClass($options, $routebase);
        self::$routeAdmin = self::$kernel->getContainer()->get('cmf_routing.route_admin');
    }

    protected function setUp()
    {
        $this->errorElement = $this->getMockBuilder('Sonata\AdminBundle\Validator\ErrorElement')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCorrectControllerPath()
    {
        $route = new Route('/', array('_controller' => 'FrameworkBundle:Redirect:redirect'));
        self::$routeAdmin->validate($this->errorElement, $route);
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

        self::$routeAdmin->validate($this->errorElement, $route);
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

        self::$routeAdmin->validate($this->errorElement, $route);
    }

    public function testCorrectTemplate()
    {
        $route = new Route('/', array('_template' => 'TwigBundle::layout.html.twig'));
        self::$routeAdmin->validate($this->errorElement, $route);
    }
}
