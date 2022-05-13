<?php


function array_print($value)
{
    echo "<pre>" ;
    var_export($value) ;
    echo "</pre>" ;
}


function cleanLine(string $line)
{
    // remove whitespace
    return preg_replace('/\s/', '', $line) ;
}

function cleanReferences(array $references)
{
    foreach ( $references as &$file_name ) :
        $file_name = preg_replace("/['\"?]/", '', $file_name) ;
    endforeach ;

    return $references ;
}

function fastReferenceFilter(string $line)
{
    if (strlen($line) < 3) {
        return false ;
    }
    elseif (strlen($line) < 11) {
        return false ;
    }
    elseif (!preg_match('/\.(js|php)/', $line) ) {
        return false ;
    }

    return true ;
}

function findInlineFileRefs(string $line)
{
    (string) $file_pattern = "/['\"](\.){0,2}[a-z-_\/1-9]+\.(php|js)['\"?]/i" ;

    (array)$matches = [] ;

    $search_result = preg_match_all(
        $file_pattern,
        $line,
        $matches
    ) ;

    if ($search_result == false) {
        return [] ;
    }

    $matches = $matches[0] ;

    return $matches ;
}


function searchFileForRefs(string $file_path)
{
    if ( preg_match('/\.js$/', $file_path) ) {
        return [] ;
    }
    if ( preg_match('/\/index/', $file_path) ) {
        return [] ;
    }
    if ( preg_match('/e\/.*\//', $file_path) ) {
        return [] ;
    }

    @$file_handle = fopen($file_path, 'r') ;

    if (boolval($file_handle) == false) {
        return [] ;
    }

    (array) $all_refs = [] ;

    while (! feof($file_handle) ) :

        $line = fgets($file_handle) ;
        $line = cleanLine($line) ;

        if (! fastReferenceFilter($line) ) {
            continue ;
        }

        $line_refs = findInlineFileRefs($line) ;

        if ( $line_refs == null ) {
            continue ;
        }

        $line_refs = cleanReferences($line_refs) ;
        $all_refs = array_merge($all_refs, $line_refs) ;

    endwhile ;

    fclose($file_handle) ;

    $all_refs = array_unique($all_refs) ;
    return $all_refs ;
}

function recursiveFileSearch($file_path)
{
    
}

function searchFilesForRefs(
    array $file_list,
    string $path_prefix,
    $checked_files = []
) {
    (array) $all_refs = [] ;

    foreach ($file_list as $file_name) :

        if ( in_array($file_name, $checked_files) ) {
            continue ;
        }

        $file_path = $path_prefix . '/' . $file_name ;
        $file_refs = searchFileForRefs($file_path) ;

        $checked_files[] = $file_name ;

        if (boolval($file_refs) == false) {
            continue ;
        }

        // $recursive_refs = searchFilesForRefs($file_refs, $path_prefix, $checked_files) ;

        $all_refs = array_merge($all_refs, $file_refs) ;
        $all_refs = array_unique($all_refs) ;

    endforeach ;

    return $all_refs ;
}

function recursiveSearch(
    array $file_list,
    string $path_prefix,
    $checked_files = []
) {
    (array) $all_refs = [] ;

    $found_refs = searchFilesForRefs($file_list, $path_prefix, $checked_files) ;

    $all_refs = array_merge($all_refs, $found_refs) ;

    $

}

// =============================================================

$path_base = '../WindsorHomesNow.Com/e' ;
$audit_files = [
    'add_unregistered_visitors.php' ,
    'buyer_purge.php' ,
    'community_management.php' ,
    'contract_tracking.php' ,
    'document_viewer.php' ,
    'email_updater.php' ,
    'followup.php' ,
    'go_to_contract.php' ,
    'guest_wo_email.php' ,
    'homes_ready_now.php' ,
    'list_buyers.php' ,
    'list_by_prospect.php' ,
    'mls_convert_triad.php' ,
    'mls_convert_triangle.php' ,
    'mls_convert_coastal.php' ,
    'php_info.php' ,
    'process_order.php' ,
    'released_buyers.php' ,
    'report_view.php' ,
    'report_year_over_year.php' ,
    'send_unsent_items.php' ,
    'stats.php' ,
    'tool_find_missing_demo.php' ,
    'tool_report_error_on_page.php'
] ;



$file_references = searchFilesForRefs($audit_files, $path_base) ;
sort($file_references) ;
array_print($file_references) ;
