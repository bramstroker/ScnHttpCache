<?php

namespace ScnHttpCacheTest\View\Helper;

use Mockery;
use ScnHttpCache\Service\EsiApplicationConfigProvider;
use ScnHttpCache\View\Helper\Esi;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Uri\Http;

class EsiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Esi
     */
    protected $viewHelper;

    public function setUp()
    {
        $this->viewHelper = new Esi();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testGetSurrogateCapbilityDefaultsToFalse()
    {
        $this->assertFalse($this->viewHelper->getSurrogateCapability());
    }

    public function testGetEsiApplicationConfigProviderException()
    {
        $this->setExpectedException('ScnHttpCache\View\Exception\RuntimeException');
        $this->viewHelper->getEsiApplicationConfigProvider();
    }

    public function testGetSurrogateCapbilityTrue()
    {
        $this->viewHelper->setSurrogateCapability(true);
        $this->assertTrue($this->viewHelper->getSurrogateCapability());
    }

    public function testGetEsiApplicationConfigProvider()
    {
        $esiApplicationConfigProvider = new EsiApplicationConfigProvider();
        $this->viewHelper->setEsiApplicationConfigProvider($esiApplicationConfigProvider);
        $this->assertSame($esiApplicationConfigProvider, $this->viewHelper->getEsiApplicationConfigProvider());
    }

    public function testGetApplication()
    {
        $application = Mockery::mock('Zend\Mvc\Application');
        $application->shouldReceive('getRequest')
            ->once()
            ->andReturn(new Request());
        $this->viewHelper->setApplication($application);
        $this->assertSame($application, $this->viewHelper->getApplication(new Http()));
    }

    public function testInvokeReturnsSelfWithoutParam()
    {
        $this->assertSame($this->viewHelper, call_user_func_array($this->viewHelper, array()));
    }

    public function testInvokeReturnsEsiWithParamAndSurrogateCapability()
    {
        $this->viewHelper->setSurrogateCapability(true);
        $this->assertEquals('<esi:include src="test" onerror="continue" />' . "\n", call_user_func_array($this->viewHelper, array('url'=>'test')));
    }

    public function testDoEsiWithSurrogateCapability()
    {
        $this->viewHelper->setSurrogateCapability(true);
        $this->assertEquals('<esi:include src="test" onerror="continue" />' . "\n", $this->viewHelper->doEsi('test'));
    }

    public function testDoEsiWithoutSurrogateCapability()
    {
        $response = new Response();
        $response->setContent('test');
        $application = Mockery::mock('Zend\Mvc\Application');
        $application->shouldReceive('getRequest')
            ->once()
            ->andReturn(new Request());
        $application->shouldReceive('run')
            ->once()
            ->andReturn($application);
        $application->shouldReceive('getResponse')
            ->once()
            ->andReturn($response);
        $this->viewHelper->setApplication($application);
        $response = $this->viewHelper->doEsi('http://test.local/test');
        $this->assertEquals('test', $response);
    }
}
