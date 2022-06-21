<?php

class CRM_Cctreasurer_Helper {
  public static function addTreasurerInCc(&$params, $context) {
    if (self::isContributionReceipt($params, $context)) {
      self::addTreasurer($params);
    }
  }

  private static function isContributionReceipt(&$params, $context) {
    if ($context == 'messageTemplate' && $params['workflow'] == 'contribution_offline_receipt') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function addTreasurer(&$params) {
    $contactIdReceipient = $params['contactId'];
    $emailTreasurer = self::getTreasurerEmail($contactIdReceipient);
    if ($emailTreasurer) {
      $params['cc'] = $emailTreasurer;
    }
  }

  private static function getTreasurerEmail($orgId) {
    $sql = "
      select
        e.email
      from
        civicrm_contact c
      inner join
        civicrm_email e on e.contact_id = c.id and e.is_primary = 1
      inner join
        civicrm_entity_tag et on et.entity_id = c.id and et.entity_table = 'civicrm_contact'
      inner join
        civicrm_tag t on et.tag_id = t.id 
      where
        c.employer_id = $orgId
      and
        c.is_deleted = 0
      and
        t.name = 'Treasurer'
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    if ($dao->fetch()) {
      return $dao->email;
    }
    else {
      return FALSE;
    }
  }
}
