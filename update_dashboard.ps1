param (
    [string]$filePath,
    [string]$searchPattern,
    [string]$replacementText
)

if (Test-Path $filePath) {
    $content = Get-Content $filePath -Raw
    if ($content -contains $searchPattern) {
        $newContent = $content -replace [regex]::Escape($searchPattern), ($replacementText + "`r`n`t`t`t" + $searchPattern)
        $newContent | Set-Content $filePath -Encoding UTF8
        Write-Output "Successfully updated $filePath"
    } else {
        Write-Error "Pattern not found in $filePath"
    }
} else {
    Write-Error "File not found: $filePath"
}
