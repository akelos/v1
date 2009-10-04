<?php

$db = Ak::db();

$charset="utf8";
$collation="utf8_unicode_ci";

$rs = $db->execute('SHOW FULL TABLES');
echo "\n";
while (!$rs->EOF) {
    $table_name = array_shift($rs->fields);
    $table_type = array_shift($rs->fields);
    if('BASE TABLE' == $table_type){
        echo "Checking table $table_name ";

        $tcs = $db->execute("SHOW CREATE TABLE $table_name");
        $sql = array_pop($tcs->fields);
        if(!preg_match('/ENGINE=.+ (DEFAULT CHARSET='.$charset.' COLLATE='.$collation.')/', $sql, $matches)){
            echo " fixing charset ";
            $db->execute('ALTER TABLE '.$table_name.' DEFAULT CHARACTER SET '.$charset.' COLLATE '.$collation);
        }else{
            echo " charset OK ";
        }
        $rst = $db->execute('SHOW FULL COLUMNS FROM '.$table_name);
        while (!$rst->EOF) {
            if(!empty($rst->fields['Collation']) && !strstr($rst->fields['Collation'], $collation)){
                echo " fixing column {$rst->fields['Field']} ";
                $db->execute("ALTER TABLE $table_name MODIFY {$rst->fields['Field']} {$rst->fields['Type']} character set $charset collate $collation ".($rst->fields['Null'] == 'Yes' ? '' : ' not null '). (empty($rst->fields['Default']) ? ($rst->fields['Null'] == 'Yes'?' default NULL':''):' default '.$rst->fields['Default']));
            }
            $rst->MoveNext();
        }
        echo "\n";
    }
    $rs->MoveNext();
}

?>