<?php

namespace App\Model\Security;

use Nette\Security\Permission;

class Authorizator extends Permission
{
    const GUEST = 'guest';
    const USER = 'user';
    const ROOT = 'root';


    public function __construct()
    {
        $this->addRoles();
        $this->addResources();
        $this->addPriviliges();
    }

    private function addRoles()
    {
        $this->addRole(self::GUEST);
        $this->addRole('user', 'guest'); //poptavajici
        $this->addRole('controlor', 'user'); //kontrolor
        $this->addRole('handyman', 'user'); // udrzabar
        $this->addRole('manager', 'user'); //manazer
        $this->addRole('redactor', 'user'); //manazer
        $this->addRole('admin', ['handyman', 'controlor', 'manager', 'redactor']);
        $this->addRole('root', 'admin');
    }

    private function addResources()
    {
        // Driver
        $this->addResource('Driver:Home');
        $this->addResource('Driver:Profile');
        $this->addResource('Driver:Places');
        $this->addResource('Driver:Reservation');
        $this->addResource('Driver:Sign');
        $this->addResource('Driver:Gopay');
        $this->addResource('Driver:PasswordReset');

        // Admin
        $this->addResource('Admin:Places');
        $this->addResource('Admin:UseofPlaces');
        $this->addResource('Admin:Users');
        $this->addResource('Admin:Organizations');
        $this->addResource('Admin:Home');
        $this->addResource('Admin:BankDetail');
        $this->addResource('Admin:Maintenances');
        $this->addResource('Admin:Inspections');
        $this->addResource('Admin:Orders');
        $this->addResource('Admin:Cards');
        $this->addResource('Admin:Credits');
        $this->addResource('Admin:Organizations:detail');
        $this->addResource('Admin:Organizations:editLongText');

        // API
        $this->addResource('Api:Checks');
        $this->addResource('Api:ApiToken');

        // Rpmapi
        $this->addResource('Rpmapi:Token');
        $this->addResource('Rpmapi:Places');
        $this->addResource('Rpmapi:Geo');
        $this->addResource('Rpmapi:Reservations');
        $this->addResource('Rpmapi:Prolong');
        $this->addResource('Rpmapi:Release');
        $this->addResource('Rpmapi:Extend');
        $this->addResource('Rpmapi:Profile');
        $this->addResource('Rpmapi:History');
        $this->addResource('Rpmapi:Credits');
        $this->addResource('Rpmapi:Password');
        $this->addResource('Rpmapi:Organization');
        $this->addResource('Rpmapi:PlaceOffering');
        $this->addResource('Rpmapi:TimeWindows');
        $this->addResource('Rpmapi:Shareable');
        $this->addResource('Rpmapi:Ocr');
        $this->addResource('Rpmapi:PaymentCard');
        $this->addResource('Rpmapi:Prescriptions');
    }

    private function addPriviliges()
    {
        $this->allow(self::GUEST, [
            'Driver:Sign',
            'Driver:Gopay',
            'Driver:PasswordReset',
        ], Permission::ALL);

        $this->allow('user', [
            'Driver:Home',
            'Driver:Profile',
            'Driver:Places',
            'Driver:Reservation',
            'Rpmapi:Token',
            'Rpmapi:Geo',
            'Rpmapi:Reservations',
            'Rpmapi:Prolong',
            'Rpmapi:Release',
            'Rpmapi:Extend',
            'Rpmapi:Profile',
            'Rpmapi:History',
            'Rpmapi:Credits',
            'Rpmapi:Places',
            'Rpmapi:Password',
            'Rpmapi:Organization',
            'Rpmapi:PlaceOffering',
            'Rpmapi:TimeWindows',
            'Rpmapi:Shareable',
            'Rpmapi:Ocr',
            'Rpmapi:PaymentCard',
            'Rpmapi:Prescriptions',
        ], Permission::ALL);

        $this->allow('controlor', [
            'Api:ApiToken',
            'Api:Checks',
        ], Permission::ALL);

        $this->allow('redactor', [
            'Api:ApiToken',
            'Api:Checks',
        ], Permission::ALL);

        $this->allow('admin', [
            'Admin:Users',
            'Admin:Places',
            'Admin:UseofPlaces',
            'Admin:Home',
            'Admin:BankDetail',
            'Admin:Maintenances',
            'Admin:Inspections',
            'Admin:Orders',
            'Admin:Cards',
            'Admin:Credits',
            'Admin:Organizations:detail',
            'Admin:Organizations:editLongText',
        ], Permission::ALL);

        $this->allow('root', [
            'Admin:Organizations',
        ], Permission::ALL);
    }

    /**
     * @param string $role
     * @return array
     */
    public function getParentRoles($role)
    {
        $roles = [$role];
        foreach ($this->getRoleParents($role) as $_role) {
            $roles = array_merge($roles, $this->getParentRoles($_role));
        }

        $roles = array_unique($roles);

        return $roles;
    }

}
