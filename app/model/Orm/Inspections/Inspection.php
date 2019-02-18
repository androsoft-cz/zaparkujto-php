<?php

namespace App\Model\Orm\Inspections;

use App\Model\Orm\AbstractEntity;
use App\Model\Orm\Places\Place;
use App\Model\Orm\Users\User;
use Nette\Utils\DateTime;

/**
 * @property int $id                        {primary}
 * @property DateTime|NULL $inspectedAt
 * @property string|NULL $result            {enum self::RESULT_*}
 * @property string|NULL $note
 * @property DateTime|NULL $deletedAt
 *
 * @property User $user                     {m:1 User::$inspections}
 * @property Place $place                   {m:1 Place::$inspections}
 */
class Inspection extends AbstractEntity
{

    const RESULT_EQUAL = 'equal';
    const RESULT_NOTEQUAL = 'notequal';
    const RESULT_EMPTY = 'empty';
}
