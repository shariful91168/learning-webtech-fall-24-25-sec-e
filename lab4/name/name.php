<?php
$nameError = "";
$name = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $validCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ .-";
 
    if ($name === "") {
        $nameError = "Name cannot be empty.";
    }
 
    elseif (!ctype_alpha($name[0])) {
        $nameError = "Name must start with a letter.";
    }
 
    else {
        $isValid = true;
        for ($i = 0; $i < strlen($name); $i++) {
            if (strpos($validCharacters, $name[$i]) === false) {
                $isValid = false;
                break;
            }
        }
        if (!$isValid) {
            $nameError = "Name can only contain letters, periods, and dashes.";
        }
    }
 
    if (!$nameError && str_word_count($name) < 2) {
        $nameError = "Name must contain at least two words.";
    }
 
 
    if ($nameError) {
        echo "<p style='color: red;'>$nameError</p>";
        echo "<a href='name.html'>Go Back</a>";
    } else {
        echo "<p style='color: green;'>Name is valid: $name</p>";
    }
}
?>
 
 