# viarail-sync
VIA Rail AD sync

by Alice Media 2021

# Install

## Configs

Set up a virtual machine with Windows Server 2019 or 2022

Install PHP 8.0, Azure AD Connect, Azure AD Powershell and add them to the PATH environment variable

Install Composer and run "composer require cakemail/cakemail"

Run getdata.ps1

## Cakemail Account Login

Edit config.php with your login information (email and password)

## Azure Connect Account Login

Edit the 4 first lines of getdata.ps1 with your login information, your tenant ID and your subscription ID:

$User = "email@domain.onmicrosoft.com"

$PWord = ConvertTo-SecureString -String "PASSWORD" -AsPlainText -Force

$tenant = "000000-0000-0000-000000"

$subscription = "000000-0000-0000-000000"

# Use

Run getdata.ps1 in a PowerShell

getdata.ps1 will generate the csv files in the folder it is in 
getdata.ps1 will automatically run postdata.php after it is done

Set up a schedule task to run getdata.ps1 every hour/day...