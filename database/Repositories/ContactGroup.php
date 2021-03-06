<?php

/*
 * Copyright (C) 2009 - 2019 Internet Neutral Exchange Association Company Limited By Guarantee.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GpNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * ContactGroup
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContactGroup extends EntityRepository
{
    /**
     * Get contact group names as an array grouped by group type.
     *
     * Returned array structure:
     *
     *     $arr = [
     *         'ROLE' => [
     *              [ 'id' => 1, 'name' => 'Billing' ],
     *              [ 'id' => 2, 'name' => 'Admin']
     *         ]
     *         'OTHER' => [
     *              [ 'id' => n, 'name' => 'Other group' ]
     *         ]
     *     ];
     *
     * @param bool $type Optionally limit to a specific type
     * @param bool $cid Contact id to filter for a particular contact
     *
     * @return array
     */
    public function getGroupNamesTypeArray( $type = false, $cid = false, $active = false )
    {
        $dql =  "SELECT cg.id AS id, cg.type AS type, cg.name AS name
             FROM \\Entities\\ContactGroup cg ";
             
        if( $cid )
            $dql .= " LEFT JOIN cg.Contacts c";

        $dql .= " WHERE 1 = 1";

        if( $active )
            $dql .= " AND cg.active = 1";
            
        if( $type )
            $dql .= " AND cg.type = ?1";
        
        if( $cid )
            $dql .= " AND c.id = ?2";
        
        $q = $this->getEntityManager()->createQuery( $dql );
            
        if( $type  )
           $q->setParameter( 1, $type );
        
        if( $cid )
            $q->setParameter( 2, $cid );
            
        $tmpGroups = $q->getArrayResult();

        $groups = [];
        foreach( $tmpGroups as $g )
            $groups[ $g['type'] ][ $g[ 'id' ] ] = [ 'id' => $g['id'], 'name' => $g['name'] ];

        return $groups;
    }

    /**
     * Get the number of contacts with a contact group for a given customer.
     *
     * @param \Entities\Customer $customer The customer to count the contact groups of
     * @param int $id Contact group id
     *
     * @return int The number of contacts with a contact group for a given customer
     *
     * @throws
     */
    public function countForCustomer( $customer, $id )
    {
        return $this->getEntityManager()->createQuery(
            "SELECT COUNT( cg.id )
                FROM \\Entities\\ContactGroup cg
                    LEFT JOIN cg.Contacts c
                    LEFT JOIN c.Customer cust
                WHERE
                    cg.id = ?1
                    AND cust = ?2"
            )
            ->setParameter( 1, $id )
            ->setParameter( 2, $customer )
            ->getSingleScalarResult();
    }


    /**
     * Get all Contacts Group for listing on the frontend CRUD
     *
     * @see \IXP\Http\Controllers\Doctrine2Frontend
     *
     *
     * @param \stdClass $feParams
     * @param $id
     *
     * @return array Array of Contacts (as associated arrays) (or single element if `$id` passed)
     *
     */
    public function getAllForFeList( \stdClass $feParams, $id )
    {
        $dql = "SELECT  cg.id AS id, 
                        cg.name AS name, 
                        cg.type AS type,
                        cg.created AS created, 
                        cg.description AS description,
                        cg.active AS active, 
                        cg.limited_to AS limit_to
                  FROM Entities\\ContactGroup cg
                  WHERE 1 = 1";



        if( $types = config( "contact_group.types" ) ) {
            $dql .= " AND cg.type IN('" . implode( "','", array_keys( $types ) ) . "')";
        }

        if( $id ) {
            $dql .= " AND cg.id = " . (int)$id;
        }

        if( isset( $feParams->listOrderBy ) ) {
            $dql .= " ORDER BY " . $feParams->listOrderBy . ' ';
            $dql .= isset( $feParams->listOrderByDir ) ? $feParams->listOrderByDir : 'ASC';
        }

        return $this->getEntityManager()->createQuery( $dql )->getArrayResult();
    }
}
