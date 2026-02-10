<?php
$f = 'templates/admin/admin-dashboard.html.twig';
if (file_exists($f)) {
    $c = file_get_contents($f);
    
    // Change all collapse toggles to use data-bs-target which is safer for Bootstrap 5
    $c = str_replace('href="#collapsepage"', 'href="#" data-bs-target="#collapsepage"', $c);
    $c = str_replace('href="#collapseclubs"', 'href="#" data-bs-target="#collapseclubs"', $c);
    $c = str_replace('href="#collapseinstructors"', 'href="#" data-bs-target="#collapseinstructors"', $c);
    $c = str_replace('href="#collapseformations"', 'href="#" data-bs-target="#collapseformations"', $c);
    $c = str_replace('href="#collapsequizzes"', 'href="#" data-bs-target="#collapsequizzes"', $c);
    $c = str_replace('href="#collapseauthentication"', 'href="#" data-bs-target="#collapseauthentication"', $c);

    file_put_contents($f, $c);
    echo "Standardized toggles in Admin Dashboard.\n";
}
