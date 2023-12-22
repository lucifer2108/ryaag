<?php

namespace Thelia\Api\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Api\Bridge\Propel\Event\ItemProviderQueryEvent;
use Thelia\Model\StateQuery;

class StateByIsoProviderListener implements EventSubscriberInterface
{
    public function stateByIsoProvider(ItemProviderQueryEvent $event)
    {
        if (
            $event->getOperation()->getName() !== "state_by_iso"
            ||
            !isset($event->getUriVariables()['stateIso'])
            ||
            !isset($event->getUriVariables()['countryIso3'])
        ) {
            return;
        }

        $query = $event->getQuery();

        if (!$query instanceof StateQuery) {
            return;
        }

        $query->filterByIsocode($event->getUriVariables()['stateIso'])
            ->useCountryQuery()
                ->filterByIsoalpha3($event->getUriVariables()['countryIso3'])
            ->endUse();

        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ItemProviderQueryEvent::class => [
                ['stateByIsoProvider', 1234]
            ],
        ];
    }
}
