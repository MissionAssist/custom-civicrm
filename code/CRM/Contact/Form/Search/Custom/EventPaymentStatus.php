<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                               |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2021                             |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 * 
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */
/*
 * 
 */
require_once 'CRM/Contact/Form/Search/Interface.php';

class CRM_Contact_Form_Search_Custom_EventPaymentStatus extends CRM_Contact_Form_Search_Custom_Base
 
implements CRM_Contact_Form_Search_Interface {

   protected $_formValues;
   
   function __construct(&$formValues) {
    $this->_formValues = $formValues;
         /**
         * Define the columns for search result rows
         */
        $this->_columns = array( ts('Contact ID')		  => 'contact_id',
        			 ts('Event')                      => 'event_name',
                                 ts('Type')                       => 'event_type',
                                 ts('Last Name')                  => 'last_name',
                                 ts('First Name')                 => 'first_name',
                                 ts('Registered by')              => 'registered_by',
                                 ts('Registration Date')          => 'register_date',
                                 ts('Fee Level')		  => 'fee_level',
                                 ts('Fee')                	  => 'total' ,
                                 ts('Paid')                       => 'paid' ,
                                 ts('To Pay')               	  => 'balance' ,
                                 ts('Status')                     => 'status',
                                 ts('Note')			  => 'note',
                                 ts('Deleted')                    => 'deleted',
                                 );
        /*
        $this->_groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', 
                                                       'activity_status', 
                                                       'id', 
                                                       'name' );
	*/
        //Add custom fields to columns array for inclusion in export
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Participant', null , null,
                                                         null, '', null );
        
        
        //use simplified formatted groupTree
        $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $form );
        
        //cycle through custom fields and assign to _columns array
        foreach ( $groupTree as $key ) {
            foreach ( $key['fields'] as $field ) {
                //$fieldlabel = $key['title'].": ".$field['label'];
                $fieldlabel = $field['label'];
                $this->_columns [ $fieldlabel ] = $field['column_name'] ;
            }
        }
        //end custom fields
	}
    //}            
    function buildForm( &$form ) {
        /**
         * You can define a custom title for the search form
         */
        $this->setTitle('Event Participant Payment Status');

        /**
         * Define the search form fields here
         */
        require_once "CRM/Event/BAO/Event.php";
 
        $events = CRM_Event_BAO_Event::getEvents( 1, NULL, TRUE, TRUE);
        $form->add('select', 'event_id',  ts( 'Event Name' ), array( '' => ts( '- select -' ) ) + $events ) ;
        
        $form->addDate('start_date', ts('Payments Date From'), false, array( 'formatType' => 'custom' ) );
        $form->addDate('end_date', ts('...through'), false, array( 'formatType' => 'custom' ) );

        $event_type = CRM_Core_OptionGroup::values('event_type', FALSE);

        foreach ($event_type as $eventId => $eventName) {
            $form->addElement('checkbox', "event_type_id[$eventId]", 'Event Type', $eventName);
        }
        
        // Fetch the participant status types
        $statusTypes = array();
        $dao = new CRM_Event_DAO_ParticipantStatusType();
        $dao->find();
         while ($dao->fetch())
         {
            $participant_status_id = $dao->id;
            $participant_status_name = $dao->label;
            $form->addElement('checkbox', "participant_status_id[$participant_status_id]", 'Status Type', $participant_status_name);
         }
        
        $form->assign( 'elements', array( 'start_date', 'end_date', /*'event_type_id',*/ 'event_id' ) );

       
    }

    /**
     * Define the smarty template used to layout the search form and results listings.
     */
    function templateFile( ) {
        return 'CRM/Contact/Form/Search/Custom/EventPaymentStatus.tpl';
    }
       
    /**
     * Construct the search query
     */       
    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = TRUE, $justIDs = FALSE ) 
                {
        // SELECT clause must include contact_id as an alias for civicrm_contact.id if you are going to use "tasks" like export etc.
        //$paymentInfo = CRM_Contribute_BAO_Contribution::getPaymentInfo($participantID, 'event', FALSE, TRUE);
        if ($justIDs)
        {
            // Just return the contact ID and sort name as they are needed for page displays,
            $select  = "contact_a.id as contact_id ";
        }
        else
        {
            
          $select  = "
            contact_a.id contact_id,
            e.title event_name,
            (SELECT ov.name FROM civicrm_option_value  ov where ov.option_group_id = (select id from civicrm_option_group og where og.name = 'event_type') and ov.value = e.event_type_id) event_type,
            p.id participant_id,
           contact_a.first_name first_name,
           contact_a.last_name last_name,
           contact_a.is_deleted deleted,
            st.name status,
            p.register_date register_date,
            li.label fee_level,
            (select cr.display_name from civicrm_participant pr 
                inner join civicrm_contact cr on cr.id = pr.contact_id
                where pr.id = p.registered_by_id) registered_by, 
            if( p.registered_by_id is null,  p.fee_amount + ifnull((select sum(pr.fee_amount) 
                from civicrm_participant pr where pr.registered_by_id = p.id), 0) 
		, 0 ) total,
            if(p.registered_by_id is null, ifnull(sum(ft.total_amount), 0), 0) paid,
            if( p.registered_by_id is null,  p.fee_amount + ifnull((select sum(pr.fee_amount) from civicrm_participant pr where pr.registered_by_id = p.id), 0) 
		, 0 ) 
                - if(p.registered_by_id is null, ifnull(sum(ft.total_amount), 0), 0)  balance,
            note.note note
                ";
        }   
        // add custom group fields to SELECT and FROM clause
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Participant', $from, null, null, '', null );
  
        $from  = $this->from( );
  
        foreach ($groupTree as $key) {
            if ($key['extends'] == 'Participant') {
              if (!$justIDs) {
                // Add custom group fields.
                $select .= ", " . $key['table_name'] . ".*";
              }
              $from   .= " LEFT JOIN " . $key['table_name'] . " ON " . $key['table_name'] . ".entity_id = p.id";
            }
        }
        // end custom groups add

        
        $where = $this->where( $includeContactIDs );

 
        $where = $this->where();
        if ( $where == "") (
        $where = "true = true");  // put something in the WHERE clause
        $groupBy = "e.id, p.contact_id";
        if ( ! empty($this->_formValues['event_type_id'] ) ) {
            $groupBy = "e.id, p.id";
        }
        $sql = "
        SELECT $select
        FROM   $from
        WHERE  $where
        GROUP BY $groupBy
        ";
        // Define ORDER BY for query in $sort, with default value

        if ( ! empty( $sort ) ) {
            if ( is_string( $sort ) ) {
                $orderBy = " ORDER BY $sort ";
            } else {
                $orderBy = " ORDER BY " . trim( $sort->orderBy() );
 
                
            }
        } else {
            $orderBy = "ORDER BY e.id desc,contact_a.last_name asc,contact_a.first_name asc";
        }
        $sql .= $orderBy;
        if ( $rowcount > 0 && $offset >= 0 ) {
            $sql .= " LIMIT $offset, $rowcount ";
        }
        
        return $sql ;
     

    }
 
    function from( ) {
        return "
            civicrm_event e
            left join civicrm_participant p on p.event_id = e.id
            inner join civicrm_participant_status_type st on st.id = p.status_id
            inner join civicrm_contact contact_a on contact_a.id = p.contact_id
            left join civicrm_note note on note.entity_id = p.id and note.entity_table = 'civicrm_participant'
            left join civicrm_participant_payment pay on pay.participant_id = p.id
            left join civicrm_contribution cont on cont.financial_type_id = e.financial_type_id and cont.contact_id = contact_a.id and cont.id = pay.contribution_id
            left join civicrm_line_item li on li.entity_id = p.id and p.registered_by_id is null 
              and li.contribution_id = cont.id and li.entity_table = 'civicrm_participant' and li.line_total != 0
            left join civicrm_entity_financial_trxn eft on eft.entity_id = cont.id and eft.entity_table = 'civicrm_contribution'
            left join civicrm_financial_trxn ft on eft.financial_trxn_id = ft.id and 
            (ft.from_financial_account_id = (select id from civicrm_financial_account ac where ac.name = 'Accounts Receivable') or ft.from_financial_account_id is null)
            and ft.to_financial_account_id = (select id from civicrm_financial_account ac where ac.name = 'Deposit Bank Account')

            " ;
        
    }
    
        /*
     * WHERE clause is an array built from any required JOINS plus conditional filters based on search criteria field values
     *
     */
    function where( $includeContactIDs = false ) {
        $clauses = array( );
        
 /*       $clauses[] = p.status_id in ( 1 )";
        $clauses[] = "civicrm_contribution.is_test = 0";*/
        
        /*$onLine = CRM_Utils_Array::value( 'paid_online',
                                          $this->_formValues );
         */
        $startDate = CRM_Utils_Date::processDate( $this->_formValues['start_date'] );
        if ( $startDate ) {
            $clauses[] = "cont.receive_date >= $startDate";
        }
        
        $endDate = CRM_Utils_Date::processDate( $this->_formValues['end_date'] );
        if ( $endDate ) {
            $clauses[] = "cont.receive_date <= {$endDate}235959";
        }
        
        if ( !empty( $this->_formValues['event_id'] ) ) { 
            $clauses[] = "e.id = {$this->_formValues['event_id']}";
        }
        
        if ( $includeContactIDs ) {
            $contactIDs = array( );
            foreach ( $this->_formValues as $id => $value ) {
                if ( $value &&
                     substr( $id, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    $contactIDs[] = substr( $id, CRM_Core_Form::CB_PREFIX_LEN );
                }
            }
            
            if ( ! empty( $contactIDs ) ) {
                $contactIDs = implode( ', ', $contactIDs );
                $clauses[] = "c.id IN ( $contactIDs )";
            }
        }

        if ( ! empty($this->_formValues['event_type_id'] ) ) {
            $event_type_ids = implode(',', array_keys($this->_formValues['event_type_id']));
            $clauses[] = "e.event_type_id IN ( $event_type_ids )";
        }
        if ( ! empty($this->_formValues['participant_status_id'] ) ) {
                 $participant_status_ids = implode(',', array_keys($this->_formValues['participant_status_id']));
                 $clauses[] = "p.status_id NOT IN ( $participant_status_ids )";
             }
        return implode( ' AND ', $clauses );
    }
    

  

    /* 
     * Functions below generally don't need to be modified
     */
    function count( ) {
        $sql = $this->all( );
           
        $dao = CRM_Core_DAO::executeQuery( $sql,
                                           CRM_Core_DAO::$_nullArray );
        return $dao->N;
    }
       
    function contactIDs( $offset = 0, $rowcount = 0, $sort = null, $returnSQL = false ) {
        return $this->all( $offset, $rowcount, $sort,  false, true );
    }
       
    function &columns( ) {
        return $this->_columns;
    }

    function setTitle( $title ) {
        if ( $title ) {
            CRM_Utils_System::setTitle( $title );
        } else {
            CRM_Utils_System::setTitle(ts('Search'));
        }
    }

    function summary( ) {
        return null;
    }

    public function buildTaskList(CRM_Core_Form_Search $form) {
       
        return $form->getVar('_taskList'); 
    
        
    }
}
