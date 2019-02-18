<?php

namespace App\Modules\Rpmapi;

use Nette\Application\Responses\JsonResponse;
use Tracy\Debugger;

class ConfigPresenter extends BasePresenter
{

    public function actionReadAll(array $query)
    {
        $result = [];
        $valmez = [];

        $valmez_localities = [
            [
                'name' => 'Oranžová',
                'code' => 'O',
                'lengths' => [
                    [
                        'count' => 60,
                        'price' => 20,
                        'unit' => 'minutes'
                    ],
                    [
                        'count' => 120,
                        'price' => 40,
                        'unit' => 'minutes'
                    ],
                ]
            ],
            [
                'name' => 'Zelená',
                'code' => 'Z',
                'lengths' => [
                    [
                        'count' => 75,
                        'price' => 13,
                        'unit' => 'minutes'
                    ],
                    [
                        'count' => 120,
                        'price' => 20,
                        'unit' => 'minutes'
                    ],
                ],
            ],
            [
                'name' => 'Žlutá',
                'code' => 'L',
                'lengths' => [
                    [
                        'count' => 75,
                        'price' => 13,
                        'unit' => 'minutes'
                    ],
                    [
                        'count' => 120,
                        'price' => 20,
                        'unit' => 'minutes'
                    ],
                ],
            ],
            [
                'name' => 'Bílá',
                'code' => 'B',
                'lengths' => [
                    [
                        'count' => 75,
                        'price' => 13,
                        'unit' => 'minutes'
                    ],
                    [
                        'count' => 120,
                        'price' => 20,
                        'unit' => 'minutes'
                    ],
                    [
                        'count' => 1,
                        'price' => 30,
                        'unit' => 'days',
                    ],
                ],
            ],

        ];

        $valmez = [
            'name' => 'Valašské Meziříčí',
            'code' => 'VM',
            'phone_number' => '90206',
            'localities' => $valmez_localities
        ];

        $ostrava = [
            'name' => 'Ostrava',
            'code' => 'OV',
            'phone_number' => '90206',
            'localities' => $valmez_localities
        ];

        $result = [
            'sms_organizations' => [
                $valmez
                //,$ostrava
            ]
        ];

        $this->sendResponse(new JsonResponse($result));
    }
}
