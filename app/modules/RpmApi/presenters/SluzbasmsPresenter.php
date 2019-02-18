<?php

namespace App\Modules\Rpmapi;

use App\Model\Exceptions\Traits\IApiMessageException;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Places\PlacesRepository;
use App\Model\Reservations\PlaceReservator;
use App\Model\Reservations\PlaceReservatorContext;
use App\Model\Reservations\PlaceReservatorResult;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\Strings;
use Tracy\Debugger;

class SluzbasmsPresenter extends BasePresenter
{
    /** @var array - čas je v minutách */
    private $priceToTime = [
        '9002003' => 15,
        '9002006' => 30,
        '9002009' => 45,
        '9002020' => 100,
        '9079950' => 250,
        '9002099' => 600,
    ];

    /** @var PlacesRepository @inject */
    public $placesRepository;

    /** @var PlaceReservator @inject */
    public $placeReservator;


    public function actionReadAll(array $query)
    {
        if (!$this->isQueryValid($query)) {

            Debugger::log($query, Debugger::DEBUG);
            $text = 'Byla odeslána neplatná SMS zpráva.';

        } else {

            list($identifier, $keyword) = $this->prepareMessageText($query['text']);

            /** @var Place $place */
            $place = $this->placesRepository->findByWithoutOrganization([
                'identifier' => $identifier,
                'smsKeyword' => $keyword,
            ])->fetch();

            if (!$place) {

                $text = 'Byla odeslána zpráva v nesprávném formátu. Zadané místo nebylo nalezeno';

            } else {

                $time = $this->priceToTime[$query['recipient']];

                $context = new PlaceReservatorContext;
                $context->placeId = $place->id;
                $context->parkingTime = $time;
                $context->user = NULL;
                $context->rz = '';
                $context->presenter = $this->presenter;
                $context->isCheck = FALSE;

                try {
                    /** @var PlaceReservatorResult $result */
                    $result = $this->placeReservator->reserve($context, Order::PAYMENT_TYPE_SMS);

                    $text = sprintf(
                        'SMS parkovací lístek. %s, %s, Platnost od: %s do %s.',
                        $place->organization->name,
                        $place->streetName,
                        $result->reservation->from->format('d.m.Y H:i'),
                        $result->reservation->to->format('d.m.Y H:i')
                    );
                } catch (IApiMessageException $e) {
                    Debugger::log($e, Debugger::DEBUG);
                    $text = 'Zaplacení parkovacího místa selhalo, prosím kontaktujte správce.';
                }
            }
        }

        $this->sendResponse(new TextResponse($text));
    }


    /**
     * @return bool
     */
    private function isQueryValid(array $query)
    {
        if (isset($query['sender'])
            && isset($query['recipient'])
            && isset($query['identifier'])
            && isset($query['text'])
            && isset($query['smsid'])
            && isset($query['time'])
        ) {
            return TRUE;
        }

        return FALSE;
    }


    /**
     * @param string
     * @return array
     */
    private function prepareMessageText($text)
    {
        $data = explode(' ', $text);

        return [
            $data[0], // identifier
            Strings::upper($data[1]), // keyword
        ];
    }

}
