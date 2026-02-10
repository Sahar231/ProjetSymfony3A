param (
    [string]$filePath,
    [string]$searchPattern,
    [string]$replacementText
)

$absPath = Resolve-Path $filePath
if (Test-Path $absPath) {
    $content = [System.IO.File]::ReadAllText($absPath)
    if ($content.Contains($searchPattern)) {
        $newFragment = $replacementText + "`r`n`t`t`t" + $searchPattern
        $newContent = $content.Replace($searchPattern, $newFragment)
        [System.IO.File]::WriteAllText($absPath, $newContent)
        Write-Output "Successfully updated $absPath"
    } else {
        Write-Error "Pattern not found in $absPath"
    }
} else {
    Write-Error "File not found: $filePath"
}
