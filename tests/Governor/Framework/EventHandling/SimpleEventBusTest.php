<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The software is based on the Axon Framework project which is
 * licensed under the Apache 2.0 license. For more information on the Axon Framework
 * see <http://www.axonframework.org/>.
 * 
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.governor-framework.org/>.
 */

namespace Governor\Framework\EventHandling;

use Governor\Framework\Domain\GenericEventMessage;

/**
 * Description of SimpleEventBusTest
 *
 * @author    "David Kalosi" <david.kalosi@gmail.com>  
 * @license   <a href="http://www.opensource.org/licenses/mit-license.php">MIT License</a> 
 */
class SimpleEventBusTest extends \PHPUnit_Framework_TestCase
{

    private $listener1;
    private $listener2;
    private $testSubject;
    private $listener3;

    public function setUp()
    {
        $this->listener1 = $this->getMock('Governor\Framework\EventHandling\EventListenerInterface',
            array('handle'));
        $this->listener2 = $this->getMock('Governor\Framework\EventHandling\EventListenerInterface',
            array('handle'));
        $this->listener3 = $this->getMock('Governor\Framework\EventHandling\EventListenerInterface',
            array('handle'));

        $this->testSubject = new SimpleEventBus();
        $this->testSubject->setLogger($this->getMock('Psr\Log\LoggerInterface'));
    }

    public function testEventIsDispatchedToSubscribedListeners()
    {
        $this->assertNotSame($this->listener1, $this->listener2);
        $this->assertNotSame($this->listener1, $this->listener3);

        $this->listener1->expects($this->exactly(2))
            ->method('handle');

        $this->listener2->expects($this->exactly(2))
            ->method('handle');

        $this->listener3->expects($this->exactly(2))
            ->method('handle');

        $this->testSubject->publish($this->newEvent());
        $this->testSubject->subscribe($this->listener1);

        // subscribing twice should not make a difference
        $this->testSubject->subscribe($this->listener1);
        $this->testSubject->publish($this->newEvent());
        $this->testSubject->subscribe($this->listener2);
        $this->testSubject->subscribe($this->listener3);
        $this->testSubject->publish($this->newEvent());
        $this->testSubject->unsubscribe($this->listener1);
        $this->testSubject->publish($this->newEvent());
        $this->testSubject->unsubscribe($this->listener2);
        $this->testSubject->unsubscribe($this->listener3);
        // unsubscribe a non-subscribed listener should not fail
        $this->testSubject->unsubscribe($this->listener3);
        $this->testSubject->publish($this->newEvent());
    }

    private function newEvent()
    {
        return array(new GenericEventMessage(new StubEventMessage()));
    }

}

class StubEventMessage
{
    
}
