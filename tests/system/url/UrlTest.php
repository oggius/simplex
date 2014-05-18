<?php
namespace system\url;


class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testMainUrl()
    {
        $url = Url::getMainUrl();
        $this->assertEquals('http://' . $_SERVER['SERVER_NAME'] . '/', $url);
    }

    public function testBuildUrl()
    {
        $urlFake = Url::_('fake', 'fake');
        $this->assertStringEndsWith('/fake/fake/', $urlFake);
        $urlFakeWithParams = Url::_('fake', 'fake', array('fakevar' => 'fakevalue'));
        $this->assertStringEndsWith('/fake/fake/fakevar/fakevalue/', $urlFakeWithParams);
    }

    public function testBuildUrlWithRouting()
    {
        $mockRoutes = array(
            '/routedfake/[any1]/'   => '/fakecontroller/fakeaction/name/$1/',
            '/routedfake/'          => '/fakecontroller/fakeaction/',
        );
        Url::setRoutes($mockRoutes);
        $url = Url::_('fakecontroller', 'fakeaction');
        $this->assertStringEndsWith('/routedfake/', $url);
    }
} 