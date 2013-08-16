
<?php
$lines = file('log.txt');

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($lines as $line_num => $line) {
    $date = substr($line,0,19);
    $text = substr($line,strpos($line,"-->"));
    if(strpos($line,"ERROR")){
        echo "<span style='color:red'><strong>" . $date ."</strong> - " . htmlspecialchars($text) . "</span><br>";
    }
    if(strpos($line,"INFO")){
        echo "<span style='color:blue'><strong>" . $date ."</strong> - " . htmlspecialchars($text) . "</span><br>";
    }
    if(strpos($line,"DEBUG")){
        echo "<span style='color:Orange'><strong>" . $date ."</strong> - " . htmlspecialchars($text) . "</span><br>";
    }
    if(strpos($line,"WARN")){
        echo "<span style='color:#254117'><strong>" . $date ."</strong> - " . htmlspecialchars($text) . "</span><br>"; 
    }
}
?>
