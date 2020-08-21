{*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                              |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
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
 Modified by Stephen Palmstrom 22 December 2014
 Modify for CiviCRM 4.5
*}
{* Template for "EventPaymentStatus" custom search component. *}
{*debug*}
<div class="crm-form-block crm-search-form-block">
  <div class="crm-accordion-wrapper crm-activity_search-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      <div class="icon crm-accordion-pointer"></div> 
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <div id="searchForm" class="crm-search-results">
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

            <table class="form-layout-compressed">
          {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
          {foreach from=$elements item=element}
            <tr class="crm-search-results-{$element}">
              <td class="label">{$form.$element.label}</td>
              <td width="400">
                {if $element eq 'start_date' OR $element eq 'end_date'}
                  {include file="CRM/common/jcalendar.tpl" elementName=$element}
                {else}
                  {$form.$element.html}
                {/if}
              </td>
            </tr>
          {/foreach}

            <tr>
                <td rowspan="2"><label>{ts}Event Type{/ts}</label>
            <div class="listing-box">
                {foreach from=$form.event_type_id item="event_val"}
                <div class="{cycle values="odd-row,even-row"}">
                    {$event_val.html}
                </div>
                {/foreach}
            </div>
        </td>
                            <td rowspan="2"><label>{ts}Exclude Participant Status{/ts}</label>
            <div class="listing-box">
                {foreach from=$form.participant_status_id item="status_val"}
                <div class="{cycle values="odd-row,even-row"}">
                    {$status_val.html}
                </div>
                {/foreach}
            </div>
        </td>
            </tr>
       
                
            </table>

 
        
      	<div class="crm-submit-buttons">
      		{include file="CRM/common/formButtons.tpl" location="bottom"}
      	</div>
      </div><!-- #searchForm -->
    </div><!-- .crm-accordion-body -->
  </div><!-- .crm-accordion-wrapper -->
</div><!-- .crm-form-block -->

{if $rowsEmpty || $rows}
  <div class="crm-content-block">
  	{if $rowsEmpty}
  		<div class="crm-results-block crm-results-block-empty">
      	{include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
      </div>
  	{/if}
  
  	{if $rows}
  		<div class="crm-results-block">
      	{* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}    
      	{* This section handles form elements for action task select and submit *}
               
    <table class="form-layout-compressed">
  <tr>
    {* Search criteria are passed to tpl in the $qill array *}
    <td class="nowrap">
    {if $qill}
      {include file="CRM/common/displaySearchCriteria.tpl"}
    {/if}
    </td>
  </table>
  			</div>

      	{* This section displays the rows along and includes the paging controls *}
<div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
        {* This section handles form elements for action task select and submit *}
       <div class="crm-search-tasks">
         <table class="form-layout-compressed">
  <tr>
    <td class="font-size12pt" style="width: 30%;" hidden>
        {if $savedSearch.name}{$savedSearch.name} ({ts}smart group{/ts}) - {/if}
        {ts count=$pager->_totalItems plural='%count Contacts'}%count Contact{/ts}
    </td>
    
    {* Search criteria are passed to tpl in the $qill array *}
    <td class="nowrap">
    {if $qill}
      {include file="CRM/common/displaySearchCriteria.tpl"}
    {/if}
    </td>
  </tr>
</table>
<table>
  <tr>
      {*I was unable to make record selections work, so have had to program the actions to be done on all records.*}
    <td class="font-size11pt" hidden>
      {assign var="checked" value=$selectedContactIds|@count}
      {$form.radio_ts.ts_all.html}
    </td>
    <td class="font-size11pt"> {ts}Take action on {/ts}{ts count=$pager->_totalItems plural='all %count records'}The found record{/ts}
   </tr>

  
    <tr>
      <td colspan="3">{* Hide export button in 'Add Members to Group' context. *}
     {if $context NEQ 'amtg'}
        {$form.task.html}
     {/if}
     {if $action eq 512}
       {$form._qf_Advanced_next_action.html}
     {elseif $action eq 8192}
       {$form._qf_Builder_next_action.html}&nbsp;&nbsp;
     {elseif $action eq 16384}
       {$form._qf_Custom_next_action.html}&nbsp;&nbsp;
     {else}
       {$form._qf_Basic_next_action.html}
     {/if}
     </td>
  </tr>
  </table>
 </div>

    </div>
        {* This section displays the rows along and includes the paging controls *}
      <div class="crm-search-results">

        {include file="CRM/common/pager.tpl" location="top"}

        {* Include alpha pager if defined. *}
        {if $atoZ}
            {include file="CRM/common/pagerAToZ.tpl"}
        {/if}

        {strip}
                <table class="selector row-highlight" summary="{ts}Search results listings.{/ts}">
                <thead class="sticky">
                    {*<th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>*}
                    {if $context eq 'smog'}
                        <th scope="col">
                          {ts}Status{/ts}
                        </th>
                    {/if}
                </th>
                {foreach from=$columnHeaders item=header}
                {if $header.name ne 'Contact ID' and $header.name ne 'Deleted'}
                <th scope="col">
                {if $header.sort}
                {assign var='key' value=$header.sort}
                {$sort->_response.$key.link}
              {else}
                $header.name
              {/if}
            </th>
          {/if}
        {/foreach}
        <th>&nbsp;</th>
                </thead>
        {counter start=0 skip=1 print=false}
        {foreach from=$rows item=row}
                <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                    {assign var=cbName value=$row.checkbox}
                   {* <td>{$form.$cbName.html}</td>*}
               {foreach from=$columnHeaders item=header}
                {assign var=fName value=$header.sort}
                {if $fName ne 'contact_id' and $fName ne 'deleted'}               
                    <td>
                    {if $row.deleted eq '1'}
                        <s>
                    {/if}

                 {/if}
                {if $fName eq 'last_name'}
                     <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.last_name}</a>
                {elseif $fName eq 'first_name'}
                    <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.first_name}</a>
                {elseif $fName eq 'payment_amount' || $fName eq 'total' || $fName eq 'net_payment' || $fName eq 'balance' || $fName eq 'paid'}
                  {$row.$fName|crmMoney}
                {elseif $fName eq 'participant_count'}
                   {$row.$fName}
                {elseif $fName ne 'contact_id' and $fName ne 'deleted'} 
                  {$row.$fName}
                {/if}
                 {if $fName ne 'contact_id' and $fName ne 'deleted'}               
                    {if $row.deleted eq '1'}
                        </s>
                    {/if}
                    </td>
                 {/if}
          {/foreach}
          <td>
              {if $row.deleted eq '1'}
                <s>
              {/if}
              {$row.action}
              {if $row.deleted eq '1'}
                </s>
              {/if}
          </td>


        </tr>
        {/foreach}
        </table>
        {/strip}

                        <script type="text/javascript">
                                {* this function is called to change the color of selected row(s) *}
                var fname = "{$form.formName}";	
                on_load_init_checkboxes(fname);
                        </script>

                        {include file="CRM/common/pager.tpl" location="bottom"}
                </div><!-- .crm-search-results -->

{* END Actions/Results section *}
        </div><!-- .crm-results-block -->
{/if}
  </div><!-- .crm-content-block -->
{/if}

{literal}
	<script type="text/javascript">
 
		cj(function() {
   	cj().crmaccordions(); 
		});
 
	</script>
{/literal}    
