<?php

namespace ChurchCRM\Service;

class PersonService
{
  private $baseURL;

  public function __construct()
  {
    $this->baseURL = $_SESSION['sRootPath'];
  }

  function search($searchTerm)
  {
    $fetch = 'SELECT per_ID, per_FirstName, per_LastName, CONCAT_WS(" ",per_FirstName,per_LastName) AS fullname, per_fam_ID  FROM person_per WHERE per_FirstName LIKE \'%' . $searchTerm . '%\' OR per_LastName LIKE \'%' . $searchTerm . '%\' OR per_Email LIKE \'%' . $searchTerm . '%\' OR CONCAT_WS(" ",per_FirstName,per_LastName) LIKE \'%' . $searchTerm . '%\' order by per_FirstName LIMIT 15';
    $result = mysql_query($fetch);

    $return = array();
    while ($row = mysql_fetch_array($result)) {
      $values['id'] = $row['per_ID'];
      $values['familyID'] = $row['per_fam_ID'];
      $values['firstName'] = $row['per_FirstName'];
      $values['lastName'] = $row['per_LastName'];
      $values['displayName'] = $row['per_FirstName'] . " " . $row['per_LastName'];
      $values['uri'] = $this->baseURL . "/PersonView.php?PersonID=". $row['per_ID'];

      array_push($return, $values);
    }

    return $return;
  }

  function getPersonsJSON($persons)
  {
    if ($persons) {
      return '{"persons": ' . json_encode($persons) . '}';
    } else {
      return false;
    }
  }


  function getPeopleEmailsAndGroups()
  {
    $sSQL = "SELECT per_FirstName, per_LastName, per_Email, per_ID, group_grp.grp_Name, lst_OptionName
	            from person_per
    		        left JOIN person2group2role_p2g2r on
                  person2group2role_p2g2r.p2g2r_per_ID = person_per.per_id

                left JOIN group_grp ON
                  person2group2role_p2g2r.p2g2r_grp_ID = group_grp.grp_ID

                left JOIN list_lst ON
                  group_grp.grp_RoleListID = list_lst.lst_ID AND
                  person2group2role_p2g2r.p2g2r_rle_ID =  list_lst.lst_OptionID

              where per_email != ''

              order by per_id;";
    $rsPeopleWithEmails = RunQuery($sSQL);
    $people = array();
    $lastPersonId = 0;
    $person = array();
    while ($row = mysql_fetch_array($rsPeopleWithEmails)) {
      if ($lastPersonId != $row["per_ID"]) {
        if ($lastPersonId != 0) {
          array_push($people, $person);
        }
        $person = array();
        $person["id"] = $row["per_ID"];
        $person["email"] = $row["per_Email"];
        $person["firstName"] = $row["per_FirstName"];
        $person["lastName"] = $row["per_LastName"];
      }

      $person[$row["grp_Name"]] = $row["lst_OptionName"];

      if ($lastPersonId != $row["per_ID"]) {
        $lastPersonId = $row["per_ID"];
      }
    }
    array_push($people, $person);
    return $people;
  }

}

?>
