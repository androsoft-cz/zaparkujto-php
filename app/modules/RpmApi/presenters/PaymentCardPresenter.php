<?php

namespace App\Modules\Rpmapi;

use App\Model\Facade\PaymentCardFacade;
use Nette\Http\Response;
use Nextras\Orm\Entity\IEntity;

class PaymentCardPresenter extends SecurePresenter
{
    /** @var PaymentCardFacade @inject */
    public $paymentCardFacade;


    public function actionReadAll()
    {
        try {
            $userId = $this->user->id;
            $paymentCards = $this->paymentCardFacade->findUsersPaymentCards($userId)->fetchPairs('id', NULL);
            foreach ($paymentCards as $key => $paymentCard) {
                $paymentCards[$key] = $paymentCards[$key]->toArray(IEntity::TO_ARRAY_RELATIONSHIP_AS_ID);
                // mask
                unset($paymentCards[$key]['referencePayId']);
                $paymentCards[$key]['createdAt'] = $paymentCards[$key]['createdAt']->format('c');
                $paymentCards[$key]['deletedAt'] = $paymentCards[$key]['deletedAt'] ? $paymentCards[$key]['deletedAt']->format('c') : NULL;
            }

            $this->sendApiResponse(array_values($paymentCards));
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }


    public function actionRead(int $id)
    {
        try {
            $paymentCard = $this->paymentCardFacade->getPaymentCard($id);
            $result = $paymentCard->toArray(IEntity::TO_ARRAY_RELATIONSHIP_AS_ID);
            // mask
            unset($result['referencePayId']);
            $result['createdAt'] = $result['createdAt']->format('c');
            $result['deletedAt'] = $result['deletedAt'] ? $result['deletedAt']->format('c') : NULL;
            $this->sendApiResponse($result);
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }


    public function actionCreate()
    {
        $this->sendError('Credit card creation is allowed only via new_reservation endpoint.', Response::S400_BAD_REQUEST);
    }


    public function actionUpdate(int $id, array $associations, $data)
    {
        try {
            $data = json_decode($data);
            if (!$data || !isset($data->name)) {
                $this->sendError('Unsupported request body format.', Response::S400_BAD_REQUEST);
            }

            $paymentCard = $this->paymentCardFacade->updatePaymentCardName($id, $data->name);
            $result = $paymentCard->toArray(IEntity::TO_ARRAY_RELATIONSHIP_AS_ID);
            // mask
            unset($result['referencePayId']);
            $result['createdAt'] = $result['createdAt']->format('c');
            $result['deletedAt'] = $result['deletedAt'] ? $result['deletedAt']->format('c') : NULL;
            $this->sendApiResponse($result);
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }


    public function actionDelete(int $id)
    {
        try {
            $paymentCard = $this->paymentCardFacade->deletePaymentCard($id);
            $paymentCards = $this->paymentCardFacade->findUsersPaymentCards($paymentCard->user->id)->fetchPairs('id', NULL);
            foreach ($paymentCards as $key => $paymentCard) {
                $paymentCards[$key] = $paymentCards[$key]->toArray(IEntity::TO_ARRAY_RELATIONSHIP_AS_ID);
                // mask
                unset($paymentCards[$key]['referencePayId']);
                $paymentCards[$key]['createdAt'] = $paymentCards[$key]['createdAt']->format('c');
                $paymentCards[$key]['deletedAt'] = $paymentCards[$key]['deletedAt'] ? $paymentCards[$key]['deletedAt']->format('c') : NULL;
            }

            $this->sendApiResponse(array_values($paymentCards));
        } catch (\ErrorException $e) {
            $this->sendError($e->getMessage(), Response::S400_BAD_REQUEST);
        }
    }
}
