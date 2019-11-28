<?php
/**
 * @author Sergey Hashimov
 * Date: 12.07.19
 * Time: 16:35
 */

namespace Slmder\SlmderFilterBundle\Filtration\EventListener;

use Slmder\SlmderFilterBundle\Filtration\QueryBuilderManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class KnpPaginationSubscriber
 * 
 */
class KnpPaginationSubscriber implements EventSubscriberInterface
{
    const KNP_PAGER_ITEMS_EVENT = 'knp_pager.items';

    /**
     * @var QueryBuilderManagerInterface
     */
    private $manager;
    /**
     * @var bool
     */
    private $triggerOnItems;

    public function __construct(QueryBuilderManagerInterface $manager, bool $triggerOnItems = true)
    {
        $this->manager = $manager;
        $this->triggerOnItems = $triggerOnItems;
    }

    /**
     * @param ItemsEvent $event
     */
    public function onItems(ItemsEvent $event)
    {
        if(!$this->triggerOnItems){
            return;
        }
        if (!$event->target instanceof QueryBuilder) {
            return;
        }
        try {
            $this->manager->apply($event->target);
        } catch (\Throwable $exception) {
            $message = "Bad filter request.";
            if (getenv('APP_ENV') === 'dev'){
                dump($exception);
                $message.=" Caused by: ".$exception->getMessage();
            }
            throw new BadRequestHttpException($message);
        }

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            self::KNP_PAGER_ITEMS_EVENT => ['onItems', 10],
        );
    }
}