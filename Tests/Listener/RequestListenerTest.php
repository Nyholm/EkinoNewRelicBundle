<?php

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ekino\Bundle\NewRelicBundle\Tests\Listener;

use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelic;
use Ekino\Bundle\NewRelicBundle\Listener\RequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSubRequest()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->never())->method('setTransactionName');

        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array(), array(), $namingStrategy);
        $listener->setApplicationName($event);
    }

    public function testMasterRequest()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('setTransactionName');


        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array(), array(), $namingStrategy);
        $listener->setTransactionName($event);
    }

    public function testPathIsIgnored ()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('ignoreTransaction');


        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/ignored_path'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array(), array('/ignored_path'), $namingStrategy);
        $listener->setIgnoreTransaction($event);
    }

    public function testRouteIsIgnored ()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('ignoreTransaction');


        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request(array(), array(), array('_route' => 'ignored_route'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array('ignored_route'), array(), $namingStrategy);
        $listener->setIgnoreTransaction($event);
    }

    public function testSymfonyCacheEnabled()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->once())->method('startTransaction');

        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array(), array(), $namingStrategy, true);
        $listener->setApplicationName($event);
    }

    public function testSymfonyCacheDisabled()
    {
        $interactor = $this->getMock('Ekino\Bundle\NewRelicBundle\NewRelic\NewRelicInteractorInterface');
        $interactor->expects($this->never())->method('startTransaction');

        $namingStrategy = $this->getMock('Ekino\Bundle\NewRelicBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface');

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener(new NewRelic('App name', 'Token'), $interactor, array(), array(), $namingStrategy, false);
        $listener->setApplicationName($event);
    }
}
