<?php
/**
 * Copyright (C) 2018 MissionAssist GPL2 license
 * 
 * Allow relationship data to be included in mailings
 * 
 * Tested with CiviCRM 5.19.2
 * 
 * This is where you put all the hook functions for CiviCRM under Joomla.  Hooks are a way
 * of intercepting actions in CiviCRM and doing custom things.
 */
/**
 *  This function creates custom mailing tokens in CiviCRM.  It allows us to*
 * expose these tokens to the user creating a mailing.
 *
 *
 */

function joomla_civicrm_tokens(&$tokens) {
  $tokens['Relationship'] = array(
    'Relationship.relationships_HTML' => 'List of relationships in HTML format',
    'Relationship.relationships_Text' => 'List of relationships in text format',
  );

  $tokens['Skill'] = array(
    'Skill.skills_HTML' => 'List of skills in HTML format',
    'Skill.skills_Text' => 'List of skills in text format',
  );
  $tokens['Group'] = array(
    'Group.Mailing_list_HTML' => 'List of mailing lists in HTML format',
    'Group.Mailing_list_Text' => 'List of mailing lists in text format',
    'Group.Mailing_list_removed_HTML' => 'List of mailing lists from which the contact was removed in HTML format',
    'Group.Mailing_list_removed_Text' => 'List of mailing lists from which the contact was removed in text format',
  );
  $location_types = civicrm_api3('LocationType', 'get', array(
  'sequential' => 1,
  'return' => array("display_name", "description"),
));
  $tokens['Email']['Email.Primary'] = 'Primary email address';
  foreach($location_types['values'] as $key => $value) {
    $tokens['Email']['Email.'. $value['display_name']] = $value['description']; 
  };

}
/*
  * This function is called at various times, many times.  If no tokens are
  * being passed to the function, we should do no more.
  */
function joomla_civicrm_tokenValues(&$values, $cids, $job = NULL, $passed_tokens = array(), $context = NULL) {
  if ($passed_tokens == NULL)
    {
 
    return NULL;
  }
  /*
   * It seems that when you use Contacts => New Email, the tokens are passed as
   *  follows:
   *  $tokens[<token group>][<numeric index>][<token value>]
   *
   * but, when using the mailing template and CiviMail, in this form.
   *
   *  $tokens[<token group>][<token value>]
   *
   * We must therefore make them consisten before we can process them.
   *
   *
   */
  $tokens = array();
  foreach ($passed_tokens as $group => $token)
  {
    foreach ($token as $key => $value)
    {
        if (!is_numeric($key))
        {
            $value = $key;
        }
        $tokens[$group][$value] = 1;
    }
  }

    /*
     * Look at the tokens and see if there are any in which we are interested.
     */
    if (array_key_exists('Relationship', $tokens))
    {

      if (is_array($tokens['Relationship']))
      {
          /*
           * There are tokens concerned with relationships.  Look thorough them and take action depending on
           * what the token is.
           */
          foreach($tokens['Relationship'] as $token => $value)
          {
              switch($token)
              {
                  case 'relationships_HTML':
                      /*
                       * Get a list of the relationships for each contact being emailed in HTML format.
                       */
                      $relationship_group = get_values_for_relationships($cids);
                      /*
                       * Now insert the information into the message.
                       */
                      foreach($relationship_group as $contactID => $relationships)
                      {
                          /*
                           * If the contact has a relationship, build the table
                           */
                          if (count($relationships) > 1)
                          {
                              $relationship_list = '<table cellspacing = "10"><tbody>';
                              foreach($relationships as $cid => $data)
                              {
                                  if ($cid === 'header')
                                  {
                                      /*
                                       * This is the table header
                                       */
                                      $fieldstart = '<th>';
                                      $fieldend = '</th>';
                                  } else
                                  {
                                      /*
                                       * This is the table body
                                       */
                                      $fieldstart = '<td>';
                                      $fieldend = '</td>'; 
                                  }
                                  $relationship_list .= '<tr>';  // Row start
                                  foreach($data as $field)
                                  {
                                      /*
                                       * The information for each relationship
                                       */
                                      $relationship_list .= $fieldstart . $field . $fieldend;
                                  }
                                  $relationship_list .= '</tr>';  // Row end
                              }
                              $relationship_list .= '</tbody></table>';  //Table end
                              $values[$contactID]['Relationship.relationships_HTML'] = $relationship_list;  // Insert into the mailing.

                          }
                          else
                          {
                              /*
                               * No known relationships
                               */
                              $values[$contactID]['Relationship.relationships_HTML'] = "None";
                          }

                      }
                      break;
                  case 'relationships_Text':
                      /*
                       * Get a list of the relationships for each contact being emailed in HTML format.
                       */
                      $relationship_group = get_values_for_relationships($cids);
                      /*
                       * Now insert the information into the message.
                       */

                      foreach($relationship_group as $contactID => $relationships)
                      {
                          if (count($relationships) > 1)
                          {
                              /*
                               * If the contact has a relationship, build a text table.
                               */
                              $relationship_list = '';
                              foreach($relationships as $cid => $data)
                              {

                                  foreach($data as $field)
                                  {
                                          $relationship_list .= str_pad($field, 20);  // pad it.
                                  }
                                  $relationship_list .= "\n";  // end of line

                              }
                              $values[$contactID]['Relationship.relationships_Text'] = $relationship_list;  // write to the email.
                          }
                          else
                          {
                              /*
                               * The contact has no known relationships.
                               */
                              $values[$contactID]['Relationship.relationships_Text'] = "None";
                          }

                     }
                     break;

              }


          }
      }
    }
    if (is_array($tokens['Skill']))
    {
        /*
         * If we have tokens associated with skills, so loop through them.
         */
        foreach($tokens['Skill'] as $token => $value)
        {
            switch($token)
            {
                case 'skills_HTML':
                    /*
                     * Get the skills for contacts being emailed in HTML format.
                     */
                    $skillstable = get_values_for_skills_tokens($cids);
                    foreach($skillstable as $contactID => $skills)
                    {
                        if (count($skills) > 1)
                        {
                            /*
                             * There will always be a header.
                             */
                            $skills_list = '<table cellspacing = "10"><tbody>';
                            foreach($skills as $skillid => $data)
                            {
                                if ($skillid === 'header')
                                {
                                    $fieldstart = '<th>';
                                    $fieldend = '</th>';
                                } else
                                {
                                    $fieldstart = '<td>';
                                    $fieldend = '</td>'; 
                                }
                                $skills_list .= '<tr>';
                                foreach($data as $field)
                                {
                                    $skills_list .= $fieldstart . $field . $fieldend;
                                }
                                $skills_list .= '</tr>';
                            }
                            $skills_list .= '</tbody></table>';
                            $values[$contactID]['Skill.skills_HTML'] = $skills_list;

                        }
                        else
                        {
                                $values[$contactID]['Skills.skills_HTML'] = "You don't seem to have any skills noted by MissionAssist";
                        }

                    }
                    break;
                case 'skills_Text':
                    $skillstable = get_values_for_skills_tokens($cids);

                    foreach($skillstable as $contactID => $skills)
                    {
                        if (count($skills) > 1)
                        {
                            /*
                             * There will always be a header
                             */
                            $skills_list = '';
                            foreach($skills as $cid => $data)
                            {

                                foreach($data as $field)
                                {
                                    $skills_list .= str_pad($field, 25);
                                }
                                $skills_list .= "\n";

                            }
                            $values[$contactID]['Skill.skills_Text'] = $skills_list;
                        }
                        else
                        {
                            $values[$contactID]['Skill.skills_Text'] = "You don't seem to have any skills noted by MissionAssist";
                        }

                   }
                   break;

            }


        }

    }

    if (is_array($tokens['Group']))
    {
        /*
         * Get details of groups to which contacts belong.
         */
        foreach($tokens['Group'] as $token => $value)
        {
            switch($token)
            {
                case 'Mailing_list_HTML':
                   $group_list = get_group_data_html($cids, "Added");
                   break;
                case 'Mailing_list_Text':
                   $group_list = get_group_data_text($cids, "Added");
                   break;
                case 'Mailing_list_removed_HTML':
                   $group_list = get_group_data_html($cids, "Removed");
                   break;
                case 'Mailing_list_removed_Text':
                   $group_list = get_group_data_text($cids, "Removed");
                   break;

            }
            foreach($group_list as $contactID => $data)
            {
                    $values[$contactID]['Group.' .$token] = $data;
            }
        }
    }
    if (is_array($tokens['Email'])) {
      //$values[$contactID]['Contact.' .$token] = "";
      foreach($tokens['Email'] as $token => $value)
      {
            $email_list = get_values_for_emails($cids, $token);
            foreach($cids as $cidvalue => $contactID) {
              $values[$contactID]['Email.'.$token] = $email_list[$contactID];
            }
  
        
      }
    }
}
function get_group_data_html($cids,  $status)
{
    /*
     * We can get information on groups that a contact has left, and is marked
     *  as remvoed.
     */
	
    $groupstable = get_values_for_groups($cids, $status);
    foreach($groupstable as $contactID => $group)
    {
        if (count($group) > 0)
        {
            /*
             * The group list doesn't have a header.
             */
            $group_list = '<table cellspacing = "10"><tbody>';
            foreach($group as $groupid => $data)
            {
                $group_list .= '<tr>';
                $group_list .= '<td>' . $data . '</td>';
                $group_list .= '</tr>';
            }
            $group_list .= '</tbody></table>';
        }
        else
        {
            switch($status)
            {
                case 'Added':
                    $group_list  = "None";
                    break;
                case 'Removed':
                    $group_list  = "You have not been removed from any MissionAssist mailing list";
                    break;
            }
        }
         $group_result[$contactID] = $group_list;
    }
    return $group_result;
}
function get_group_data_text($cids,  $status)
{
    $grouptable = get_values_for_groups($cids, $status);

    foreach($grouptable as $contactID => $group)
    {
        if (count($group) > 0)
        {
        /*
         * The group list doesn't have a header.
         */
            $group_list = '';
            foreach($group as $cid => $data)
            {

                $group_list .= $data;
                $group_list .= "\n";
            }

        }
        else
        {
            switch($status)
            {
                case 'Added':
                    $group_list  = "You don't seem to be on any MissionAssist mailing list";
                    break;
                case 'Removed':
                    $group_list  = "You have not been removed from any MissionAssist mailing list";
                    break;
            }
        }
        $group_result[$contactID] = $group_list;
     }
     return $group_result;
}
function get_values_for_relationships($cids)
{
    /*
     * This is where we get the list of relationships.
     *
     * Now get a list of the relationships.
     */

    $today = date("Y-m-d");


    foreach($cids as $contactID)
    {
        $relationshiplist[$contactID] = array();
        $relationshiplist[$contactID]['header'] = array('Type', 'Name', 'Description'); // First create the header.
        $relationship_values = civicrm_api3('Relationship', 'get', array(
          'sequential' => 1,
          'return' => array("contact_id_a.display_name",
          "relationship_type_id.label_b_a", 
          "description",
          "start_date",
          "end_date"),
          'contact_id_b' => $contactID,
          'is_active' => 1,
          'contact_id_a.is_deceased' => 0,
          'contact_id_b.is_deceased' => 0,
          'contact_id_a.is_deleted' => 0,
          'contact_id_b.is_deleted' => 0,
        ));

        foreach($relationship_values['values'] as $relationship)
        {
          /*
           * Check that the relationship is within start and and end dates
           */
          $is_valid = true; // assume the relationship is valid
            // The start date must be today or earlier
            if (array_key_exists("start_date", $relationship))
            {
              $is_valid = $is_valid && ($relationship['start_date'] <= $today);
            };
            // The end date must be in the future
            if (array_key_exists("end_date", $relationship))
              {
                $is_valid = $is_valid && ($relationship['end_date'] >= $today);
              };

            /*
             * Build the relationship table.
             */
            if ($is_valid) {
              $relationshiplist[$contactID][$relationship['id']] = array(
                  'type' => $relationship['relationship_type_id.label_b_a'] . ':',
                  'display_name' => $relationship['contact_id_a.display_name'],
                  'description' => $relationship['description'],
                  );
            }

          }
        }
    return $relationshiplist;
}
function get_values_for_emails($cids, $token)
{
    /*
     * This is where we get the list of email addresses
     *
    */
    /*
     * Now get a list of emails.
     */
  if ($token == 'Primary') {
          $result = civicrm_api3('Email', 'get', array(
        'sequential' => 1,
        'is_primary' => 1,
        ));
      } else {
        $result = civicrm_api3('Email', 'get', array(
        'sequential' => 1,
        'contact_id' => array('IN' => $cids),
        'location_type_id' => $token,
        ));
  }
  foreach($result['values'] as $value) {
      $list[$value['contact_id']] = "";
      $spacer[$value['contact_id']] = "";
      $list[$value['contact_id']] .= $spacer[$value['contact_id']] . $value['email'];
      $spacer[$value['contact_id']] = ", ";
  }

    return $list;
}
function get_values_for_skills_tokens($cids)
{
    /*
     * Get the list of skills
     */
    /*
     * First look up the custom fields associated with Skill
     * 
     */
    require_once 'CRM/Core/BAO/CustomField.php';

    $skillFieldID = CRM_Core_BAO_CustomField::getCustomFieldID( 'Skill', 'Skills' );
    $noteFieldID = CRM_Core_BAO_CustomField::getCustomFieldID( 'Note', 'Skills' );

    $custom_fields = array('Skill' => "custom_" . $skillFieldID,
        'Note' => "custom_" . $noteFieldID );

    $skill_options  = civicrm_api3('Contact', 'getoptions', array(
        'sequential' => 1,
        'field' => $custom_fields['Skill'],
      ));
    foreach($cids as $contactID)
    {
        /*
         * Now get the values for each contact.
         */
        $skillslist[$contactID] = array();
        $skillslist[$contactID]['header'] = array('Skill', 'Note');

        $params = array(
            'entityID' => $contactID,
            $custom_fields['Skill'] => 1,
            $custom_fields['Note'] => 1,
           );
        $skills_table = array();  // set up a skeels table
        $skills_values = CRM_Core_BAO_CustomValueTable::getValues($params);
        // We need to sort the fields into two columns based on their field ids and rows by idividual id
        if (!$skills_values['is_error'])
        {
            foreach($skills_values as $key => $value)
            {
               if(preg_match("/custom/", $key))
               {
                    $ids = preg_split('/_/', $key);
                    $skills_table[$ids[2]][$ids[1]] = $value;
               }
            }
        }


        foreach($skills_table as $id => $fields)
        {

            $skillslist[$contactID][$id] = $fields;

        }

    }

    return $skillslist;
}

function get_values_for_groups($cids, $status="Added")
{
    /*
     * Get the mailing list groups that are publicly visible
     */
    /*
     * First look up the numeric group type associated with
     * Mailing List.
     * 
     * The status is either Added or Removed.
     */
    $option_value = 0;
    $result = civicrm_api3('OptionValue', 'get', array(
            'sequential' => 1,
            'return' => array("value"),
            'option_group_id.name' => "group_type",
            'name' => "Mailing List",
    ));
    if ($result['is_error'] || $result['count'] == 0)
    {
            return;
    }
    else
    {
        $option_value = $result['values'][0]['value'];
    }
    /*
     * First determine the ID of the group type 'Mailing List'
     */
    $grouplist = array();
    foreach($cids as $contactID)
    {
        /*
         * For each contact, get the mailing list groups that are publicly visible.
         * These you can subscribe to yourselef.
         */
        $result = civicrm_api3('GroupContact', 'get', array(
                'sequential' => 1,
                'return' => array("group_id.title"),
                'status' => $status,
                'group_id.visibility' => "Public Pages",
                'group_id.group_type' => $option_value,
                'contact_id.id' => $contactID,
        ));

        if($result['is_error'])
        {
                $grouplist[$contactID] = array();
        } else
        {
                $grouplist[$contactID] = array();
                foreach ($result['values'] as $index => $data)
                {
                        $grouplist[$contactID][$index] = $data['group_id.title']; // get the group title
                }
        }
    }
	
  return $grouplist;

}

