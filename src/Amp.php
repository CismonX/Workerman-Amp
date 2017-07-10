<?php
namespace Workerman\Events;
use Amp\Loop;
use Workerman\Worker;

class Amp implements EventInterface
{

    protected $_allEvents = [];

    protected $_eventSignal = [];

    protected $_eventTimer = [];

    protected static $_timerId = 1;

    public function add($fd, $flag, $func, $args = array())
    {
        $args = (array)$args;
        switch ($flag) {
            case EventInterface::EV_READ:
                $fd_key = intval($fd);
                $event = Loop::onReadable($fd, $func);
                $this->_allEvents[$fd_key][$flag] = $event;
                return true;
            case EventInterface::EV_WRITE:
                $fd_key = intval($fd);
                $event = Loop::onWritable($fd, $func);
                $this->_allEvents[$fd_key][$flag] = $event;
                return true;
            case EventInterface::EV_SIGNAL:
                $fd_key = intval($fd);
                $event = Loop::onSignal($fd, $func);
                $this->_eventSignal[$fd_key] = $event;
                return true;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                $param = [$func, (array)$args, $flag, self::$_timerId];
                $event = Loop::repeat($fd * 1000, \Closure::bind(function () use ($param) {
                    $timer_id = $param[3];
                    if ($param[2] === self::EV_TIMER_ONCE) {
                        Loop::cancel($this->_eventTimer[$timer_id]);
                        unset($this->_eventTimer[$timer_id]);
                    }
                    try {
                        call_user_func_array($param[0], $param[1]);
                    } catch (\Exception $e) {
                        Worker::log($e);
                        exit(250);
                    } catch (\Error $e) {
                        Worker::log($e);
                        exit(250);
                    }
                }, $this, __CLASS__));
                $this->_eventTimer[self::$_timerId] = $event;
                return self::$_timerId++;
            default:
                break;
        }
        return false;
    }

    public function del($fd, $flag)
    {
        switch ($flag) {
            case EventInterface::EV_READ:
            case EventInterface::EV_WRITE:
                $fd_key = intval($fd);
                if (isset($this->_allEvents[$fd_key][$flag])) {
                    Loop::cancel($this->_allEvents[$fd_key][$flag]);
                    unset($this->_allEvents[$fd_key][$flag]);
                }
                if (empty($this->_allEvents[$fd_key]))
                    unset($this->_allEvents[$fd_key]);
                break;
            case EventInterface::EV_SIGNAL:
                $fd_key = intval($fd);
                if (isset($this->_eventSignal[$fd_key])) {
                    Loop::cancel($this->_eventSignal[$fd_key]);
                    unset($this->_eventSignal[$fd_key]);
                }
                break;
            case EventInterface::EV_TIMER:
            case EventInterface::EV_TIMER_ONCE:
                if (isset($this->_eventTimer[$fd])) {
                    Loop::cancel($this->_eventTimer[$fd]);
                    unset($this->_eventTimer[$fd]);
                }
                break;
        }
        return true;
    }

    public function loop()
    {
        Loop::run();
    }

    public function clearAllTimer()
    {
        foreach ($this->_eventTimer as $event)
            Loop::cancel($event);
        $this->_eventTimer = [];
    }

    public function destroy()
    {
        foreach ($this->_eventSignal as $event)
            Loop::cancel($event);
    }
}