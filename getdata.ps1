$User = "email@domain.onmicrosoft.com"
$PWord = ConvertTo-SecureString -String "PASSWORD" -AsPlainText -Force
$tenant = "000000-0000-0000-000000"
$subscription = "000000-0000-0000-000000"
$Credential = New-Object -TypeName "System.Management.Automation.PSCredential" -ArgumentList $User,$PWord
Connect-AzAccount -Credential $Credential -Tenant $tenant -Subscription $subscription

Get-AzADUser | export-csv users.csv
Get-AzADGroup | export-csv groups.csv
$Groups = Get-AzADGroup 
$test = 1
Foreach($G In $Groups)
	{
		$Filename = $G.DisplayName
		$path = $Filename + ".csv"
		Write-Host $path
		$MemberGroups = Get-AzADGroupMember -objectID $G.Id | export-csv $path
	}
php postdata.php