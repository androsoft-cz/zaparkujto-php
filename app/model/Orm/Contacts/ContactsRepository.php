<?php

namespace App\Model\Orm\Contacts;

use App\Model\Orm\AbstractRepository;

class ContactsRepository extends AbstractRepository
{

    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [
            Contact::class,
        ];
    }
}
