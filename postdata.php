<?php
require_once (__DIR__ . '/vendor/autoload.php');
require ('config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
session_start();

//On a system with "composer require cakemail/cakemail"
require_once (__DIR__ . '/vendor/autoload.php');
use Cakemail\Api;
$api = new \Cakemail\Api($cakemail_user, $cakemail_password);

$senders = $api
    ->sender
    ->list(['page' => 1, 'per_page' => 50, 'with_count' => 0]);

$getsender = $senders['data'][0]['id'];

$myList = $api
    ->list
    ->list(['page' => 1, 'per_page' => 50, 'with_count' => 0]);
	
//All Users Sync with list all_azure_users
echo "Synch with list all_azure_users \n";
$listexist = 0;
foreach ($myList as $list)
{
    if ($list['name'] == 'all_azure_users')
    {
        $list_id = $list['id'];
        $listexist++;
    }
}

if ($listexist == 0)
{
    $myList = $api
        ->list
        ->create(['model_list' => new \Cakemail\Lib\Model\ModelList(['name' => 'all_azure_users', 'language' => 'en_US', 'default_sender' => new \Cakemail\Lib\Model\Sender(['id' => $getsender]) ]) ]);

    $ValidateList = $api
        ->list
        ->acceptPolicy(['list_id' => $myList['id']]);

    $list_id = $myList['id'];

}

$row = 0;
$rowone = 0;

echo "Extrating users from Azure Active Directory \n";
if (($handleone = fopen($csv_path . "users.csv", "r")) !== false)
{
    while (($dataone = fgetcsv($handleone, 1000, ",")) !== false)
    {
        $rowone++;
        if (($rowone != 1) && ($rowone != 2))
        {
            $myContact = $api
                ->contact
                ->create(['list_id' => $list_id, 'contact' => new \Cakemail\Lib\Model\Contact(['email' => $dataone[0]]) ]);

        }
    }
    fclose($handleone);
}

//All Users Unsubscription Check
echo "Unsubscribed users verification \n";
$contacts = $api
    ->contact
    ->listContactsOfList(['list_id' => $list_id, 'page' => 1, 'per_page' => 50, 'with_count' => 0]);

foreach ($contacts as $contact)
{
    $email = $contact['email'];
    $emailid = $contact['id'];
    $handle = fopen($csv_path . 'users.csv', 'r');
    $deletecount = 0;
    if ($handle)
    {
        while (($line = fgetcsv($handle)) !== false)
        {
            if (strpos($line[0], $email) !== false)
            {
                $deletecount++;
            }
        }
        fclose($handle);
    }
    if ($deletecount == 0)
    {
        echo $email . " retire \n";
        $api
            ->contact
            ->delete(['list_id' => $list_id, 'contact_id' => $emailid]);
    }    
}
//Groups and Lists Sync
$row = 0;
$groupexist = 0;
if (($handle = fopen($csv_path . "groups.csv", "r")) !== false)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== false)
    {
        $row++;
        if (($row != 1) && ($row != 2))
        {
            $listname = $data[5];
            $listid = $data[6];
            echo "Synch with list " . $listname . " \n";
            $AllLists = $api
                ->list
                ->list(['page' => 1, 'per_page' => 50, 'with_count' => 0]);
            foreach ($AllLists as $group)
            {
                if ($group['name'] == $listname)
                {
                    $group_id = $group['id'];
                    $groupexist++;
                }
            }
            if ($groupexist == 0)
            {

                $myList = $api
                    ->list
                    ->create(['model_list' => new \Cakemail\Lib\Model\ModelList(['name' => $listname, 'language' => 'en_US', 'default_sender' => new \Cakemail\Lib\Model\Sender(['id' => $getsender]) ]) ]);

                $ValidateList = $api
                    ->list
                    ->acceptPolicy(['list_id' => $myList['id']]);

                $group_id = $myList['id'];

            }
            $newrow = 0;
            if (($handletwo = fopen($csv_path . $listname . ".csv", "r")) !== false)
            {
                while (($datatwo = fgetcsv($handletwo, 1000, ",")) !== false)
                {
                    $newrow++;
                    if (($newrow != 1) && ($newrow != 2))
                    {
                        echo $datatwo[0] . " \n";
                        $myContact = $api
                            ->contact
                            ->create(['list_id' => $group_id, 'contact' => new \Cakemail\Lib\Model\Contact(['email' => $datatwo[0]]) ]);

                    }

                }
            }

            //Unsubscribed users verification
            $contacts = $api
                ->contact
                ->listContactsOfList(['list_id' => $group_id, 'page' => 1, 'per_page' => 50, 'with_count' => 0]);
            echo "Unsubscribed users verification for list " . $listname . "\n";
            foreach ($contacts as $contact)
            {
                $email = $contact['email'];

                $emailid = $contact['id'];
                $grouphandle = fopen($csv_path . $listname . '.csv', 'r');
                $deletecount = 0;
                if ($grouphandle)
                {
                    while (($line = fgetcsv($grouphandle)) !== false)
                    {
                        if (strpos($line[0], $email) !== false)
                        {
                            $deletecount++;
                        }
                    }
                    fclose($grouphandle);
                }
                if ($deletecount == 0)
                {
                    echo $email . " retire \n";
                    $api
                        ->contact
                        ->delete(['list_id' => $group_id, 'contact_id' => $emailid]);
                }
            }
        }
    }
    fclose($handle);

}

echo "Contacts updated successfully \n";
?>
