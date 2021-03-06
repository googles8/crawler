<?php

namespace Crawler\Components\SpiderController;

use Crawler\Components\ConfigSetting\ConfigSetting;
use Crawler\Components\Spider\MultiSpider;
use Crawler\Container\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Crawler\EventTag;

/**
 * 基于多进程实现的爬虫控制器
 *
 * @author LL
 */
class MultiProcessSpiderController implements SpiderControllerInterface
{
    /**
     * 爬虫引擎实例
     *
     * @var MultiSpider
     */
    private $spider;

    /**
     * 事件派发
     *
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * 爬虫的休眠时间
     *
     * @var int
     */
    private $sleepTime;

    public function __construct(MultiSpider $spider, EventDispatcher $event, int $sleepTime = 1)
    {
        $this->spider = $spider;
        $this->eventDispatcher = $event;
        $this->sleepTime = $sleepTime;
    }

    /**
     * 启动爬虫
     *
     * @return void
     */
    public function start(): void
    {
        //爬虫启动事件派发
        $this->dispatch(EventTag::SPIDER_START);

        while (true) {
            if ($link = $this->spider->next()) {
                try {
                    $parser = $this->spider->getContent($link);
                    $this->spider->filterData($parser);
                } catch (\Exception $e) {
                    //TODO:触发一个抓取失败的事件
                } finally {
                    //TODO:触发一个本次抓取结束的事件
                }
            } else {
                $this->stop();
            }

            $this->sleepSpider();
        }
    }

    /**
     * 停止爬虫
     *
     * @return void
     */
    public function stop(): void
    {
        $this->spider->end();
    }

    /**
     * 事件派发
     *
     * @param string $eventTag 事件名称
     */
    private function dispatch(string $eventTag): void
    {
        $this->eventDispatcher->dispatch($eventTag, Container::getInstance()->make('SpiderEvent', [
            'spider' => $this->spider,
            'params' => []
        ]));
    }

    /**
     * 使爬虫进入休眠时间
     */
    private function sleepSpider(): void
    {
        sleep($this->sleepTime);
    }
}